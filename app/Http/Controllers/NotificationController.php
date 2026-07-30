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

        // Fetch notifications list
        $notificationsQuery = Notification::with('user.profile')
            ->orderBy('id', 'desc');

        if ($gymId !== 'all') {
            $notificationsQuery->whereHas('user', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            });
        }

        $notifications = $notificationsQuery->take(100)->get();

        // Members list for target selection
        $membersQuery = User::where('role', 'member');
        if ($gymId !== 'all') {
            $membersQuery->where('gym_id', $gymId);
        }
        $members = $membersQuery->with('profile')->orderBy('id', 'desc')->get();

        // Metrics calculations
        $totalSent = $notifications->count();
        $unreadCount = $notifications->where('is_read', 0)->count();
        $readCount = $notifications->where('is_read', 1)->count();
        $manualBroadcasts = $notifications->where('type', 'general')->count();

        return view('notificaciones.index', compact(
            'notifications',
            'members',
            'totalSent',
            'unreadCount',
            'readCount',
            'manualBroadcasts'
        ));
    }

    /**
     * Send manual notification (Broadcast or Direct).
     */
    public function sendManual(Request $request)
    {
        $request->validate([
            'target_type' => 'required|string|in:all,inactive,specific',
            'title' => 'required|string|max:150',
            'body' => 'required|string',
            'type' => 'nullable|string|in:general,membership_expiry,payment_reminder,new_routine,achievement',
            'user_id' => 'required_if:target_type,specific|nullable|integer|exists:users,id',
        ]);

        $gymId = session('active_gym_id', 'all');
        $targetType = $request->target_type;
        $notificationType = $request->input('type', 'general');

        $recipientsCount = 0;

        if ($targetType === 'specific') {
            Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'body' => $request->body,
                'type' => $notificationType,
                'is_read' => 0,
            ]);
            $recipientsCount = 1;
        } elseif ($targetType === 'all') {
            $usersQuery = User::where('role', 'member');
            if ($gymId !== 'all') {
                $usersQuery->where('gym_id', $gymId);
            }
            $userIds = $usersQuery->pluck('id')->toArray();

            $insertData = [];
            $now = Carbon::now()->toDateTimeString();
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

            if (!empty($insertData)) {
                Notification::insert($insertData);
            }
            $recipientsCount = count($userIds);
        } elseif ($targetType === 'inactive') {
            // Find members without attendance in last 5 days
            $activeUserIds = AttendanceLog::where('check_in', '>=', Carbon::now()->subDays(5))->pluck('user_id')->toArray();
            
            $inactiveUsersQuery = User::where('role', 'member')
                ->whereNotIn('id', $activeUserIds);

            if ($gymId !== 'all') {
                $inactiveUsersQuery->where('gym_id', $gymId);
            }

            $inactiveUserIds = $inactiveUsersQuery->pluck('id')->toArray();

            $insertData = [];
            $now = Carbon::now()->toDateTimeString();
            foreach ($inactiveUserIds as $uId) {
                $insertData[] = [
                    'user_id' => $uId,
                    'title' => $request->title,
                    'body' => $request->body,
                    'type' => $notificationType,
                    'is_read' => 0,
                    'createdAt' => $now,
                ];
            }

            if (!empty($insertData)) {
                Notification::insert($insertData);
            }
            $recipientsCount = count($inactiveUserIds);
        }

        // Audit Log
        AdminAuditLog::logAction(
            'CREATE',
            'notifications',
            null,
            null,
            [
                'target_type' => $targetType,
                'title' => $request->title,
                'recipients_count' => $recipientsCount
            ],
            ($gymId === 'all') ? null : $gymId
        );

        $msg = "¡Notificación enviada con éxito a {$recipientsCount} socio(s)!";

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
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('is_read', 0)->update(['is_read' => 1]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas.']);
        }

        return redirect()->back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }

    /**
     * Execute Automated Notification Triggers (Gym Schedule, Expiry, Inactivity).
     */
    public function runAutoTriggers(Request $request)
    {
        $gymId = session('active_gym_id', 'all');
        $createdCount = 0;
        $now = Carbon::now()->toDateTimeString();

        // 1. Memberships expiring in 3 days
        $expiringMemberships = UserMembership::where('status', 'active')
            ->whereBetween('end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(3)->toDateString()])
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->get();

        foreach ($expiringMemberships as $m) {
            // Avoid duplicate notification today
            $exists = Notification::where('user_id', $m->user_id)
                ->where('type', 'membership_expiry')
                ->whereDate('createdAt', Carbon::today())
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $m->user_id,
                    'title' => '💳 Membresía por vencer pronto',
                    'body' => "Hola, tu membresía vence el " . Carbon::parse($m->end_date)->format('d/m/Y') . ". ¡Renuévala hoy para mantener tu acceso sin interrupciones!",
                    'type' => 'membership_expiry',
                    'is_read' => 0,
                ]);
                $createdCount++;
            }
        }

        // 2. Training Schedule Auto Reminders
        $activeMembers = User::where('role', 'member')
            ->when($gymId !== 'all', function ($q) use ($gymId) {
                $q->where('gym_id', $gymId);
            })
            ->get();

        foreach ($activeMembers as $member) {
            $exists = Notification::where('user_id', $member->id)
                ->where('type', 'payment_reminder')
                ->whereDate('createdAt', Carbon::today())
                ->exists();

            if (!$exists && rand(1, 10) > 7) { // Sample automated schedule reminder
                Notification::create([
                    'user_id' => $member->id,
                    'title' => '🏋️ Recordatorio: ¡Hora de Entrenar!',
                    'body' => "¡Hola " . ($member->profile->first_name ?? 'Socio') . "! Recuerda que hoy es un gran día para entrenar en el gym. ¡Te esperamos!",
                    'type' => 'payment_reminder',
                    'is_read' => 0,
                ]);
                $createdCount++;
            }
        }

        $msg = "¡Se generaron {$createdCount} notificaciones automáticas del sistema!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'created_count' => $createdCount]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
