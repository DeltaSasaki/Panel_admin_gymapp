<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\AchievementDefinition;
use App\Models\UserAchievement;
use App\Models\UserGamificationStat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationController extends Controller
{
    /**
     * View challenges, achievement catalog, and leaderboard.
     */
    public function index()
    {
        $gymId = $this->getActiveGymId();

        // 1. Fetch Challenges
        $challengesQuery = Challenge::query();
        if ($gymId !== 'all') {
            $challengesQuery->where('gym_id', $gymId);
        }
        $challenges = $challengesQuery->orderBy('end_date', 'asc')->get();

        // 2. Fetch Achievements
        $achievementsQuery = AchievementDefinition::query();
        if ($gymId !== 'all') {
            $achievementsQuery->where(function($q) use ($gymId) {
                $q->where('gym_id', $gymId)->orWhereNull('gym_id');
            });
        }
        $achievements = $achievementsQuery->orderBy('id', 'desc')->get();

        // 3. Fetch Leaderboard (Top 10 athletes)
        $leaderboardQuery = UserGamificationStat::with('user.profile');
        if ($gymId !== 'all') {
            $leaderboardQuery->where('gym_id', $gymId);
        }
        $leaderboard = $leaderboardQuery->orderBy('total_xp', 'desc')->take(10)->get();

        // 4. Fetch Clients for quick modal operations
        $clientsQuery = User::where('role', 'member')->where('is_active', 1)->with('profile');
        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }
        $clients = $clientsQuery->get();

        return view('retos.index', compact('challenges', 'achievements', 'leaderboard', 'clients'));
    }

    /**
     * Create a new gym challenge.
     */
    public function storeChallenge(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'xp_reward' => 'required|integer|min:0',
            'token_reward' => 'required|numeric|min:0',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $errorMsg = 'Debes seleccionar una sucursal específica para crear un reto.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        $challenge = Challenge::create([
            'gym_id' => $gymId,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'xp_reward' => $request->xp_reward,
            'token_reward' => $request->token_reward,
            'is_active' => 1,
        ]);

        $message = 'Reto de gimnasio creado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'challenge' => $challenge
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an existing challenge.
     */
    public function updateChallenge(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'xp_reward' => 'required|integer|min:0',
            'token_reward' => 'required|numeric|min:0',
        ]);

        $gymId = $this->getActiveGymId();
        $challengeQuery = Challenge::query();
        if ($gymId !== 'all') {
            $challengeQuery->where('gym_id', $gymId);
        }
        $challenge = $challengeQuery->findOrFail($id);

        $challenge->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'xp_reward' => $request->xp_reward,
            'token_reward' => $request->token_reward,
        ]);

        $message = 'Reto de gimnasio actualizado exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'challenge' => $challenge
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle active status of a challenge.
     */
    public function deleteChallenge($id)
    {
        $gymId = $this->getActiveGymId();
        $challengeQuery = Challenge::query();
        if ($gymId !== 'all') {
            $challengeQuery->where('gym_id', $gymId);
        }
        $challenge = $challengeQuery->findOrFail($id);

        $newStatus = $challenge->is_active ? 0 : 1;
        $challenge->update(['is_active' => $newStatus]);

        $message = $newStatus 
            ? "Reto '{$challenge->title}' reactivado con éxito."
            : "Reto '{$challenge->title}' inhabilitado con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'challenge_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Create a new badge/achievement definition.
     */
    public function storeAchievement(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'xp_reward' => 'required|integer|min:0',
            'token_reward' => 'required|numeric|min:0',
            'condition_type' => 'required|string|max:100',
            'target_value' => 'required|integer|min:1',
        ]);

        $gymId = $this->getActiveGymId();
        
        $targetGymId = $gymId;
        if ($gymId === 'all') {
            if (auth()->user()->role === 'superadmin') {
                $targetGymId = null; // Global system badge
            } else {
                $errorMsg = 'Debes seleccionar una sucursal específica para crear una medalla.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
            }
        }

        $achievement = AchievementDefinition::create([
            'gym_id' => $targetGymId,
            'name' => $request->name,
            'description' => $request->description,
            'xp_reward' => $request->xp_reward,
            'token_reward' => $request->token_reward,
            'condition_type' => $request->condition_type,
            'target_value' => $request->target_value,
            'icon_url' => $request->icon_url ?? 'award',
            'is_active' => 1,
        ]);

        $message = 'Definición de logro creada exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'achievement' => $achievement
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an achievement definition.
     */
    public function updateAchievement(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'xp_reward' => 'required|integer|min:0',
            'token_reward' => 'required|numeric|min:0',
            'condition_type' => 'required|string|max:100',
            'target_value' => 'required|integer|min:1',
        ]);

        $achievement = AchievementDefinition::findOrFail($id);

        $achievement->update([
            'name' => $request->name,
            'description' => $request->description,
            'xp_reward' => $request->xp_reward,
            'token_reward' => $request->token_reward,
            'condition_type' => $request->condition_type,
            'target_value' => $request->target_value,
            'icon_url' => $request->icon_url ?? $achievement->icon_url ?? 'award',
        ]);

        $message = 'Definición de logro actualizada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'achievement' => $achievement
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle active status of an achievement definition.
     */
    public function deleteAchievement($id)
    {
        $achievement = AchievementDefinition::findOrFail($id);

        $newStatus = $achievement->is_active ? 0 : 1;
        $achievement->update(['is_active' => $newStatus]);

        $message = $newStatus 
            ? "Logro '{$achievement->name}' reactivado con éxito."
            : "Logro '{$achievement->name}' inhabilitado con éxito.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'achievement_id' => $id,
                'is_active' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * View participants enrolled in a challenge.
     */
    public function challengeParticipants($id)
    {
        $gymId = $this->getActiveGymId();
        $challenge = Challenge::findOrFail($id);

        if ($gymId !== 'all' && $challenge->gym_id != $gymId) {
            abort(403, 'No tienes permisos para ver este reto.');
        }

        $participants = UserChallenge::where('challenge_id', $id)
            ->with('user.profile')
            ->get();

        // Find gym clients not already enrolled in this challenge
        $enrolledIds = $participants->pluck('user_id')->toArray();
        $clientsQuery = User::where('role', 'member')->where('is_active', 1)->with('profile');
        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }
        $availableClients = $clientsQuery->whereNotIn('id', $enrolledIds)->get();

        return view('retos.participants', compact('challenge', 'participants', 'availableClients'));
    }

    /**
     * Enroll client to challenge manually.
     */
    public function enrollParticipant(Request $request)
    {
        $request->validate([
            'challenge_id' => 'required|exists:challenges,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $exists = UserChallenge::where('challenge_id', $request->challenge_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            $errorMsg = 'El atleta ya está inscrito en este reto.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }

        $participant = UserChallenge::create([
            'challenge_id' => $request->challenge_id,
            'user_id' => $request->user_id,
            'progress_value' => 0,
            'status' => 'active',
        ])->load('user.profile');

        $message = 'Atleta inscrito al reto exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'participant' => $participant
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update participant progress / complete challenge.
     */
    public function updateParticipant(Request $request, $id)
    {
        $request->validate([
            'progress_value' => 'required|integer|min:0',
            'status' => 'required|in:active,completed,failed',
        ]);

        $userChallenge = UserChallenge::findOrFail($id);
        $challenge = Challenge::findOrFail($userChallenge->challenge_id);
        $gymId = $this->getActiveGymId();
        $targetGymId = ($gymId === 'all') ? $challenge->gym_id : $gymId;

        try {
            DB::beginTransaction();

            $oldStatus = $userChallenge->status;
            $newStatus = $request->status;

            $updateData = [
                'progress_value' => $request->progress_value,
                'status' => $newStatus,
            ];

            if ($oldStatus !== 'completed' && $newStatus === 'completed') {
                $updateData['completed_at'] = Carbon::now();

                // Reward the user: total_xp and token_balance
                $stats = UserGamificationStat::firstOrCreate(
                    ['user_id' => $userChallenge->user_id],
                    ['gym_id' => $targetGymId, 'total_xp' => 0, 'token_balance' => 0.00]
                );

                $stats->increment('total_xp', $challenge->xp_reward);
                $stats->increment('token_balance', $challenge->token_reward);
            }

            $userChallenge->update($updateData);

            DB::commit();
            $message = 'Progreso de reto actualizado y recompensas aplicadas si corresponde.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'participant' => $userChallenge->fresh('user.profile')
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Error al guardar el progreso: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }
    }

    /**
     * Award achievement manually to user.
     */
    public function awardAchievementToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'achievement_definition_id' => 'required|exists:achievement_definitions,id',
        ]);

        $def = AchievementDefinition::findOrFail($request->achievement_definition_id);
        $user = User::findOrFail($request->user_id);
        $gymId = $this->getActiveGymId();
        $targetGymId = ($gymId === 'all') ? $user->gym_id : $gymId;

        try {
            DB::beginTransaction();

            // Record user achievement
            UserAchievement::create([
                'user_id' => $request->user_id,
                'achievement_type' => $def->name,
                'description' => $def->description ?? "Otorgado manualmente: {$def->name}",
                'achieved_at' => Carbon::now(),
            ]);

            // Reward the user: total_xp and token_balance
            $stats = UserGamificationStat::firstOrCreate(
                ['user_id' => $request->user_id],
                ['gym_id' => $targetGymId, 'total_xp' => 0, 'token_balance' => 0.00]
            );

            $stats->increment('total_xp', $def->xp_reward);
            $stats->increment('token_balance', $def->token_reward);

            DB::commit();
            $message = "¡Logro '{$def->name}' otorgado al atleta con éxito!";

            if ($request->ajax() || $request->wantsJson()) {
                // Fetch updated leaderboard
                $leaderboardQuery = UserGamificationStat::with('user.profile');
                if ($gymId !== 'all') {
                    $leaderboardQuery->where('gym_id', $gymId);
                }
                $leaderboard = $leaderboardQuery->orderBy('total_xp', 'desc')->take(10)->get()->map(function($st) {
                    return [
                        'id' => $st->id,
                        'user_id' => $st->user_id,
                        'name' => trim(($st->user->profile->first_name ?? 'Atleta') . ' ' . ($st->user->profile->last_name ?? '')),
                        'email' => $st->user->email ?? '',
                        'photo' => ($st->user->profile && $st->user->profile->profile_photo)
                            ? asset($st->user->profile->profile_photo)
                            : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
                        'total_xp' => (int)$st->total_xp,
                        'token_balance' => (float)$st->token_balance,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'leaderboard' => $leaderboard
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Error al otorgar medalla: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }
    }
}
