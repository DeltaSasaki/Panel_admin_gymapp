<?php

namespace App\Services;

use App\Models\AchievementDefinition;
use App\Models\UserAchievement;
use App\Models\UserGamificationStat;
use App\Models\AttendanceLog;
use App\Models\UserChallenge;
use App\Models\Notification;
use App\Models\User;
use App\Models\AdminAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    /**
     * Evaluate and award automatic achievements for a specific user.
     *
     * @param int $userId
     * @param int|string|null $gymId
     * @return array List of newly awarded AchievementDefinition models
     */
    public static function evaluateUserAchievements(int $userId, $gymId = null): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        $targetGymId = ($gymId && $gymId !== 'all') ? $gymId : $user->gym_id;

        // 1. Fetch all active achievement definitions applicable to this user
        $definitionsQuery = AchievementDefinition::where('is_active', 1);
        if ($targetGymId) {
            $definitionsQuery->where(function ($q) use ($targetGymId) {
                $q->where('gym_id', $targetGymId)->orWhereNull('gym_id');
            });
        }
        $definitions = $definitionsQuery->get();

        if ($definitions->isEmpty()) {
            return [];
        }

        // 2. Fetch IDs of achievements already unlocked by this user
        $existingDefinitionIds = UserAchievement::where('user_id', $userId)
            ->whereNotNull('achievement_definition_id')
            ->pluck('achievement_definition_id')
            ->toArray();

        // 3. Compute User Activity Metrics
        $consecutiveDaysStreak = self::calculateConsecutiveDaysStreak($userId);

        $totalWorkouts = AttendanceLog::where('user_id', $userId)
            ->selectRaw('DISTINCT DATE(check_in)')
            ->count();

        $earlyBirdCount = AttendanceLog::where('user_id', $userId)
            ->whereTime('check_in', '<', '08:00:00')
            ->selectRaw('DISTINCT DATE(check_in)')
            ->count();

        $nightOwlCount = AttendanceLog::where('user_id', $userId)
            ->whereTime('check_in', '>=', '20:00:00')
            ->selectRaw('DISTINCT DATE(check_in)')
            ->count();

        $challengesWonCount = UserChallenge::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $classesAttendedCount = DB::table('class_bookings')
            ->where('user_id', $userId)
            ->where('status', 'attended')
            ->count();

        $newlyAwarded = [];

        foreach ($definitions as $def) {
            // Skip if already unlocked
            if (in_array($def->id, $existingDefinitionIds)) {
                continue;
            }

            $conditionMet = false;
            $target = (int)$def->target_value;

            switch (strtolower(trim($def->condition_type))) {
                case 'consecutive_days':
                case 'consecutive_attendance':
                case 'streak':
                    $conditionMet = ($consecutiveDaysStreak >= $target);
                    break;

                case 'workouts_completed':
                case 'total_attendance':
                case 'workouts':
                case 'attendance':
                    $conditionMet = ($totalWorkouts >= $target);
                    break;

                case 'early_bird':
                case 'early_attendance':
                case 'madrugador':
                    $conditionMet = ($earlyBirdCount >= $target);
                    break;

                case 'night_owl':
                case 'nocturno':
                    $conditionMet = ($nightOwlCount >= $target);
                    break;

                case 'challenges_won':
                case 'challenges_completed':
                case 'challenges':
                case 'retos':
                    $conditionMet = ($challengesWonCount >= $target);
                    break;

                case 'classes_attended':
                case 'classes':
                case 'clases':
                    $conditionMet = ($classesAttendedCount >= $target);
                    break;

                default:
                    // Custom or manual conditions will not trigger automatically
                    $conditionMet = false;
                    break;
            }

            if ($conditionMet) {
                try {
                    DB::beginTransaction();

                    // Create UserAchievement record with direct FK
                    $userAchievement = UserAchievement::create([
                        'user_id' => $userId,
                        'achievement_definition_id' => $def->id,
                        'achievement_type' => $def->condition_type ?? $def->name,
                        'description' => $def->description ?? "Completó con éxito: {$def->name}.",
                        'achieved_at' => Carbon::now(),
                    ]);

                    // Reward XP and Token balance
                    $stats = UserGamificationStat::firstOrCreate(
                        ['user_id' => $userId],
                        ['gym_id' => $targetGymId, 'total_xp' => 0, 'token_balance' => 0.00]
                    );

                    $stats->increment('total_xp', (int)$def->xp_reward);
                    $stats->increment('token_balance', (float)$def->token_reward);

                    // Send push notification to user
                    Notification::create([
                        'user_id' => $userId,
                        'title' => "🏆 ¡Logro Desbloqueado: {$def->name}!",
                        'body' => "¡Felicitaciones! Has obtenido la medalla '{$def->name}'. Ganaste +{$def->xp_reward} XP y +{$def->token_reward} Monedas en tu cuenta.",
                        'type' => 'achievement',
                        'is_read' => 0,
                        'createdAt' => Carbon::now(),
                    ]);

                    DB::commit();

                    $newlyAwarded[] = $def;
                    $existingDefinitionIds[] = $def->id;

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Error auto-awarding achievement #{$def->id} to user #{$userId}: " . $e->getMessage());
                }
            }
        }

        return $newlyAwarded;
    }

    /**
     * Manually award an achievement to a user with XP, tokens, and instant notification.
     *
     * @param int $userId
     * @param int $achievementDefinitionId
     * @param int|null $adminId
     * @return array
     */
    public static function awardManually(int $userId, int $achievementDefinitionId, ?int $adminId = null): array
    {
        $user = User::findOrFail($userId);
        $def = AchievementDefinition::findOrFail($achievementDefinitionId);
        $targetGymId = $user->gym_id;

        // Check if already awarded
        $alreadyAwarded = UserAchievement::where('user_id', $userId)
            ->where('achievement_definition_id', $achievementDefinitionId)
            ->exists();

        if ($alreadyAwarded) {
            return [
                'success' => false,
                'message' => "El atleta ya cuenta con la medalla '{$def->name}'.",
                'already_awarded' => true
            ];
        }

        try {
            DB::beginTransaction();

            // 1. Record UserAchievement
            $userAchievement = UserAchievement::create([
                'user_id' => $userId,
                'achievement_definition_id' => $def->id,
                'achievement_type' => $def->condition_type ?? $def->name,
                'description' => $def->description ?? "Otorgado manualmente: {$def->name}.",
                'achieved_at' => Carbon::now(),
            ]);

            // 2. Increment stats
            $stats = UserGamificationStat::firstOrCreate(
                ['user_id' => $userId],
                ['gym_id' => $targetGymId, 'total_xp' => 0, 'token_balance' => 0.00]
            );

            $stats->increment('total_xp', (int)$def->xp_reward);
            $stats->increment('token_balance', (float)$def->token_reward);

            // 3. Send Notification (Requested by User)
            Notification::create([
                'user_id' => $userId,
                'title' => "🏆 ¡Medalla Otorgada: {$def->name}!",
                'body' => "¡Felicitaciones! Tu entrenador te ha otorgado la medalla '{$def->name}'. Has recibido +{$def->xp_reward} XP y +{$def->token_reward} Monedas.",
                'type' => 'achievement',
                'is_read' => 0,
                'createdAt' => Carbon::now(),
            ]);

            // 4. Record in Audit Log
            if ($adminId) {
                AdminAuditLog::record('INSERT', 'user_achievements', $userAchievement->id, null, $userAchievement->toArray(), $targetGymId);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "¡Medalla '{$def->name}' otorgada exitosamente al atleta!",
                'achievement' => $def,
                'user_achievement' => $userAchievement
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al otorgar medalla: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Batch evaluate automatic achievements for all active members in a gym.
     *
     * @param int|string|null $gymId
     * @return array
     */
    public static function evaluateGymAchievements($gymId = null): array
    {
        $membersQuery = User::where('role', 'member')->where('is_active', 1);
        if ($gymId && $gymId !== 'all') {
            $membersQuery->where('gym_id', $gymId);
        }
        $members = $membersQuery->get();

        $totalEvaluated = 0;
        $totalAwardedCount = 0;
        $awardedDetails = [];

        foreach ($members as $member) {
            $awarded = self::evaluateUserAchievements($member->id, $gymId);
            $totalEvaluated++;
            if (!empty($awarded)) {
                $totalAwardedCount += count($awarded);
                $awardedDetails[] = [
                    'user_id' => $member->id,
                    'name' => trim(($member->profile->first_name ?? '') . ' ' . ($member->profile->last_name ?? '')),
                    'achievements' => array_map(fn($d) => $d->name, $awarded)
                ];
            }
        }

        return [
            'evaluated_members' => $totalEvaluated,
            'awarded_count' => $totalAwardedCount,
            'details' => $awardedDetails
        ];
    }

    /**
     * Calculate the maximum consecutive days streak of attendance for a user.
     *
     * @param int $userId
     * @return int
     */
    public static function calculateConsecutiveDaysStreak(int $userId): int
    {
        $dates = DB::table('attendance_logs')
            ->where('user_id', $userId)
            ->selectRaw('DISTINCT DATE(check_in) as log_date')
            ->orderBy('log_date', 'asc')
            ->get()
            ->pluck('log_date')
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $maxStreak = 0;
        $currentStreak = 0;
        $prevDate = null;

        foreach ($dates as $dateStr) {
            $currDate = Carbon::parse($dateStr)->startOfDay();
            if ($prevDate === null) {
                $currentStreak = 1;
            } else {
                $diffInDays = (int)round($prevDate->diffInDays($currDate));
                if ($diffInDays === 1) {
                    $currentStreak++;
                } elseif ($diffInDays > 1) {
                    $currentStreak = 1;
                }
            }

            if ($currentStreak > $maxStreak) {
                $maxStreak = $currentStreak;
            }

            $prevDate = $currDate;
        }

        return $maxStreak;
    }
}
