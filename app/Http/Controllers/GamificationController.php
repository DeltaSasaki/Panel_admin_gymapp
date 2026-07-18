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
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para crear un reto.']);
        }

        Challenge::create([
            'gym_id' => $gymId,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'xp_reward' => $request->xp_reward,
            'token_reward' => $request->token_reward,
        ]);

        return redirect()->back()->with('success', 'Reto de gimnasio creado exitosamente.');
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
                return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para crear una medalla.']);
            }
        }

        AchievementDefinition::create([
            'gym_id' => $targetGymId,
            'name' => $request->name,
            'description' => $request->description,
            'xp_reward' => $request->xp_reward,
            'token_reward' => $request->token_reward,
            'condition_type' => $request->condition_type,
            'target_value' => $request->target_value,
            'icon_url' => $request->icon_url ?? 'award',
        ]);

        return redirect()->back()->with('success', 'Definición de logro creada exitosamente.');
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
            return redirect()->back()->withErrors(['error' => 'El atleta ya está inscrito en este reto.']);
        }

        UserChallenge::create([
            'challenge_id' => $request->challenge_id,
            'user_id' => $request->user_id,
            'progress_value' => 0,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Atleta inscrito al reto exitosamente.');
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
            return redirect()->back()->with('success', 'Progreso de reto actualizado y recompensas aplicadas si corresponde.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al guardar el progreso: ' . $e->getMessage()]);
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
            return redirect()->back()->with('success', "¡Logro '{$def->name}' otorgado al atleta con éxito!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al otorgar medalla: ' . $e->getMessage()]);
        }
    }
}
