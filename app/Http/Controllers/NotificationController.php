<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserMembership;
use App\Models\AttendanceLog;
use App\Models\AdminAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display Notification Center dashboard & send forms.
     */
    public function index(Request $request)
    {
        $gymId = $this->getActiveGymId();

        // Base query with gym scoping
        $baseQuery = Notification::query();
        if ($gymId !== 'all') {
            $baseQuery->whereHas('user', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }

        // Accurate Global KPI counts across database for this gym
        $totalSent = (clone $baseQuery)->count();
        $unreadCount = (clone $baseQuery)->where('is_read', 0)->count();
        $readCount = (clone $baseQuery)->where('is_read', 1)->count();
        $manualBroadcasts = (clone $baseQuery)->where('type', 'general')->count();

        // Type breakdown stats
        $typeCounts = (clone $baseQuery)
            ->select('type', DB::raw('count(*) as aggregate'))
            ->groupBy('type')
            ->pluck('aggregate', 'type')
            ->toArray();

        // Fetch recent notifications for bitácora table (with eager relations)
        $notifications = (clone $baseQuery)
            ->with(['user.profile', 'user.activeMembership.plan'])
            ->orderBy('id', 'desc')
            ->take(150)
            ->get();

        // Members list for target selection
        $membersQuery = User::where('role', 'member');
        if ($gymId !== 'all') {
            $membersQuery->where('gym_id', $gymId);
        }
        $members = $membersQuery->with(['profile', 'activeMembership.plan'])
            ->orderBy('id', 'desc')
            ->get();

        return view('notificaciones.index', compact(
            'notifications',
            'members',
            'totalSent',
            'unreadCount',
            'readCount',
            'manualBroadcasts',
            'typeCounts'
        ));
    }

    /**
     * Send manual notification (Broadcast or Direct).
     */
    public function sendManual(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string|in:all,active_membership,expiring_soon,inactive,specific',
            'title' => 'required|string|max:150',
            'body' => 'required|string|max:2000',
            'type' => 'nullable|string|in:general,membership_expiry,payment_reminder,new_routine,achievement',
            'user_id' => 'required_if:target_type,specific|nullable|integer|exists:users,id',
        ]);

        $gymId = $this->getActiveGymId();
        $targetType = $request->target_type;
        $notificationType = $request->input('type', 'general');
        $now = Carbon::now()->toDateTimeString();

        $recipientsCount = 0;

        if ($targetType === 'specific') {
            $user = User::findOrFail($request->user_id);
            Notification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'body' => $request->body,
                'type' => $notificationType,
                'is_read' => 0,
                'createdAt' => $now,
            ]);
            $recipientsCount = 1;
        } else {
            $usersQuery = User::where('role', 'member');
            if ($gymId !== 'all') {
                $usersQuery->where('gym_id', $gymId);
            }

            if ($targetType === 'all') {
                $userIds = $usersQuery->pluck('id')->toArray();
            } elseif ($targetType === 'active_membership') {
                $userIds = $usersQuery->whereHas('memberships', function ($q) {
                    $q->where('status', 'active');
                })->pluck('id')->toArray();
            } elseif ($targetType === 'expiring_soon') {
                $userIds = $usersQuery->whereHas('memberships', function ($q) {
                    $q->where('status', 'active')
                      ->whereBetween('end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(7)->toDateString()]);
                })->pluck('id')->toArray();
            } elseif ($targetType === 'inactive') {
                $activeUserIds = AttendanceLog::where('check_in', '>=', Carbon::now()->subDays(5))->pluck('user_id')->toArray();
                $userIds = $usersQuery->whereNotIn('id', $activeUserIds)->pluck('id')->toArray();
            } else {
                $userIds = [];
            }

            if (!empty($userIds)) {
                $insertData = [];
                foreach ($userIds as $uId) {
                    $insertData[] = [
                        'user_id' => $uId,
                        'title' => $request->title,
                        'body' => $request->body,
                        'type' => $notificationType,
                        'is_read' => 0,
                        'createdAt' => $now,
                    ];
                }

                // Chunk inserts to handle large member bases efficiently
                foreach (array_chunk($insertData, 200) as $chunk) {
                    Notification::insert($chunk);
                }
                $recipientsCount = count($userIds);
            }
        }

        // Audit Log
        AdminAuditLog::logAction(
            'CREATE',
            'notifications',
            null,
            null,
            [
                'target_type' => $targetType,
                'type' => $notificationType,
                'title' => $request->title,
                'recipients_count' => $recipientsCount
            ],
            ($gymId === 'all') ? null : $gymId
        );

        $msg = "¡Notificación emitida exitosamente a {$recipientsCount} socio(s)!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'recipients_count' => $recipientsCount
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => 1]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída.'
            ]);
        }

        return redirect()->back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Mark all notifications as read for current active gym.
     */
    public function markAllAsRead(Request $request)
    {
        $gymId = $this->getActiveGymId();

        $query = Notification::where('is_read', 0);
        if ($gymId !== 'all') {
            $query->whereHas('user', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }

        $affected = $query->update(['is_read' => 1]);

        $msg = "¡{$affected} notificaciones marcadas como leídas!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'affected_count' => $affected
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada de la bitácora.'
            ]);
        }

        return redirect()->back()->with('success', 'Notificación eliminada.');
    }

    /**
     * Bulk clean old notifications (> 30 days).
     */
    public function cleanupOld(Request $request)
    {
        $days = (int) $request->input('days', 30);
        if ($days < 7) $days = 7;

        $gymId = $this->getActiveGymId();
        $cutoff = Carbon::now()->subDays($days);

        $query = Notification::where('createdAt', '<', $cutoff);
        if ($gymId !== 'all') {
            $query->whereHas('user', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }

        $deleted = $query->delete();
        $msg = "Se eliminaron {$deleted} notificaciones con más de {$days} días de antigüedad.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'deleted_count' => $deleted
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Execute Automated Notification Triggers (Gym Schedule, Expiry, Inactivity).
     */
    public function runAutoTriggers(Request $request)
    {
        $gymId = $this->getActiveGymId();
        $createdCount = 0;
        $now = Carbon::now()->toDateTimeString();
        $today = Carbon::today();

        // 1. Memberships expiring in 3 days
        $expiringMemberships = UserMembership::where('status', 'active')
            ->whereBetween('end_date', [$today->toDateString(), $today->copy()->addDays(3)->toDateString()])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->with(['user.profile'])
            ->get();

        foreach ($expiringMemberships as $m) {
            $exists = Notification::where('user_id', $m->user_id)
                ->where('type', 'membership_expiry')
                ->whereDate('createdAt', $today)
                ->exists();

            if (!$exists) {
                $clientName = $m->user->profile->first_name ?? 'Socio';
                $endDateFormatted = Carbon::parse($m->end_date)->format('d/m/Y');
                Notification::create([
                    'user_id' => $m->user_id,
                    'title' => '💳 Membresía por vencer pronto',
                    'body' => "Hola {$clientName}, tu plan vence el {$endDateFormatted}. ¡Renuévalo hoy en recepción o app para mantener tu acceso sin interrupciones!",
                    'type' => 'membership_expiry',
                    'is_read' => 0,
                    'createdAt' => $now,
                ]);
                $createdCount++;
            }
        }

        // 2. Training Schedule Reminders for Active Members
        $activeMembers = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->with('profile')
            ->get();

        foreach ($activeMembers as $member) {
            $exists = Notification::where('user_id', $member->id)
                ->where('type', 'payment_reminder')
                ->whereDate('createdAt', $today)
                ->exists();

            if (!$exists && rand(1, 10) > 7) {
                $firstName = $member->profile->first_name ?? 'Socio';
                Notification::create([
                    'user_id' => $member->id,
                    'title' => '🏋️ Recordatorio: ¡Hora de Entrenar!',
                    'body' => "¡Hola {$firstName}! Recuerda que hoy es un excelente día para cumplir tu meta en el gym. ¡Te esperamos!",
                    'type' => 'payment_reminder',
                    'is_read' => 0,
                    'createdAt' => $now,
                ]);
                $createdCount++;
            }
        }

        // 3. Reactivation for Inactive Members (> 5 days)
        $activeUserIds = AttendanceLog::where('check_in', '>=', Carbon::now()->subDays(5))->pluck('user_id')->toArray();
        $inactiveMembers = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->whereNotIn('id', $activeUserIds)
            ->with('profile')
            ->take(20)
            ->get();

        foreach ($inactiveMembers as $inactive) {
            $exists = Notification::where('user_id', $inactive->id)
                ->where('type', 'achievement')
                ->whereDate('createdAt', $today)
                ->exists();

            if (!$exists) {
                $name = $inactive->profile->first_name ?? 'Campeón';
                Notification::create([
                    'user_id' => $inactive->id,
                    'title' => '🔥 ¡Te extrañamos en el Gym!',
                    'body' => "¡Hola {$name}! Notamos que hace unos días no vienes a entrenar. ¡Retoma tu ritmo hoy mismo!",
                    'type' => 'achievement',
                    'is_read' => 0,
                    'createdAt' => $now,
                ]);
                $createdCount++;
            }
        }

        $msg = "¡Se generaron {$createdCount} notificaciones automáticas del sistema con éxito!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'created_count' => $createdCount
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
