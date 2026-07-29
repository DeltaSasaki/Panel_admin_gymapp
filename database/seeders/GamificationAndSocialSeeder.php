<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AchievementDefinition;
use App\Models\UserAchievement;
use App\Models\UserGamificationStat;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\User;

class GamificationAndSocialSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Achievement Definitions (15 global/gym achievements)
        $achievements = [
            ['name' => 'Bienvenida de Hierro', 'desc' => 'Completar el primer entrenamiento en el gimnasio.', 'xp' => 100, 'tok' => 5.00, 'type' => 'workouts_completed', 'target' => 1, 'icon' => 'trophy'],
            ['name' => 'Fuego Constante', 'desc' => 'Entrenar durante 5 días seguidos sin faltar.', 'xp' => 300, 'tok' => 15.00, 'type' => 'consecutive_days', 'target' => 5, 'icon' => 'fire'],
            ['name' => 'Guerrero Imparable', 'desc' => 'Registrar 50 entrenamientos completados.', 'xp' => 1000, 'tok' => 50.00, 'type' => 'workouts_completed', 'target' => 50, 'icon' => 'medal'],
            ['name' => 'Lobo Estepario', 'desc' => 'Entrenar temprano en el turno mañana 10 veces.', 'xp' => 250, 'tok' => 10.00, 'type' => 'early_bird', 'target' => 10, 'icon' => 'sun'],
            ['name' => 'Espíritu de Comunidad', 'desc' => 'Invitar a un amigo que active su membresía.', 'xp' => 500, 'tok' => 25.00, 'type' => 'referral_completed', 'target' => 1, 'icon' => 'users'],
        ];

        $achievementEntities = [];
        foreach ($achievements as $idx => $ach) {
            $achDef = AchievementDefinition::create([
                'id' => $idx + 1,
                'gym_id' => null,
                'name' => $ach['name'],
                'description' => $ach['desc'],
                'xp_reward' => $ach['xp'],
                'token_reward' => $ach['tok'],
                'icon_url' => $ach['icon'],
                'condition_type' => $ach['type'],
                'target_value' => $ach['target'],
                'createdAt' => $now->copy()->subYear(),
            ]);
            $achievementEntities[] = $achDef;
        }

        // Expand to 15 achievement definitions
        for ($i = count($achievements) + 1; $i <= 15; $i++) {
            $baseA = $achievements[($i - 1) % count($achievements)];
            $achDef = AchievementDefinition::create([
                'id' => $i,
                'gym_id' => null,
                'name' => $baseA['name'] . " Nivel " . ($i - count($achievements)),
                'description' => $baseA['desc'],
                'xp_reward' => $baseA['xp'] * 2,
                'token_reward' => $baseA['tok'] * 2,
                'icon_url' => $baseA['icon'],
                'condition_type' => $baseA['type'],
                'target_value' => $baseA['target'] * 2,
                'createdAt' => $now->copy()->subYear(),
            ]);
            $achievementEntities[] = $achDef;
        }

        // 2. Gamification Stats and Achievements per Member
        $members = User::where('role', 'member')->get();

        foreach ($members as $idx => $member) {
            $totalXp = 500 + ($idx * 45) + rand(100, 2000);
            $level = floor($totalXp / 1000) + 1;
            $tokenBalance = round($totalXp * 0.05, 2);
            $streak = rand(1, 14);
            $longestStreak = max($streak, rand(10, 30));

            UserGamificationStat::create([
                'user_id' => $member->id,
                'gym_id' => $member->gym_id,
                'total_xp' => $totalXp,
                'current_level' => $level,
                'token_balance' => $tokenBalance,
                'current_streak_days' => $streak,
                'longest_streak_days' => $longestStreak,
                'updatedAt' => $now,
            ]);

            // Unlocked User Achievements
            for ($ua = 0; $ua < 2; $ua++) {
                $ach = $achievementEntities[($idx + $ua) % count($achievementEntities)];
                UserAchievement::create([
                    'user_id' => $member->id,
                    'achievement_type' => $ach->condition_type,
                    'description' => "Completó con éxito: {$ach->name}.",
                    'achieved_at' => $now->copy()->subDays(rand(5, 50))->toDateTimeString(),
                ]);
            }
        }

        // 3. Challenges & User Challenges (10 challenges)
        $challenges = [];
        for ($cId = 1; $cId <= 10; $cId++) {
            $gymId = ($cId % 5) + 1;
            $challenge = Challenge::create([
                'id' => $cId,
                'gym_id' => $gymId,
                'title' => "Desafío Mensual G{$gymId} #" . $cId,
                'description' => "Acumula 20 entrenamientos en 30 días para ganar bonus de XP.",
                'start_date' => $now->copy()->subDays(15)->toDateString(),
                'end_date' => $now->copy()->addDays(15)->toDateString(),
                'xp_reward' => 500,
                'token_reward' => 20.00,
                'createdAt' => $now->copy()->subDays(15),
            ]);
            $challenges[] = $challenge;
        }

        foreach ($members as $idx => $m) {
            $gymChallenges = array_filter($challenges, fn($ch) => $ch->gym_id == $m->gym_id);
            if (empty($gymChallenges)) {
                $gymChallenges = $challenges;
            }
            $gymChallenges = array_values($gymChallenges);

            $chal = $gymChallenges[$m->id % count($gymChallenges)];

            UserChallenge::create([
                'user_id' => $m->id,
                'challenge_id' => $chal->id,
                'progress_value' => rand(5, 20),
                'status' => ($idx % 3 == 0) ? 'completed' : 'active',
                'completed_at' => ($idx % 3 == 0) ? $now->copy()->subDays(2)->toDateTimeString() : null,
            ]);
        }

        // 4. User Referrals (200+ referrals between members)
        $memberArray = $members->values();
        $totalMembers = count($memberArray);

        for ($ref = 0; $ref < 200; $ref++) {
            $referrer = $memberArray[$ref % $totalMembers];
            $referred = $memberArray[($ref + 1) % $totalMembers];

            if ($referrer->id !== $referred->id && $referrer->gym_id === $referred->gym_id) {
                DB::table('user_referrals')->insert([
                    'gym_id' => $referrer->gym_id,
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referred->id,
                    'status' => ($ref % 2 == 0) ? 'completed' : 'pending',
                    'reward_granted' => 1,
                    'createdAt' => $now->copy()->subDays(rand(10, 60)),
                    'completedAt' => ($ref % 2 == 0) ? $now->copy()->subDays(5) : null,
                ]);
            }
        }
    }
}
