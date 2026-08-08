<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\AdminAuditLog;
use App\Models\Gym;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance logs and search panel.
     */
    public function index(Request $request)
    {
        $gymId = $this->getActiveGymId();
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $period = $request->input('period', 'today');

        // Fetch logs for the initial view
        $logsQuery = AttendanceLog::with(['user.profile', 'gym'])
            ->whereDate('check_in', $selectedDate)
            ->orderBy('check_in', 'desc');

        if ($gymId !== 'all') {
            $logsQuery->where('gym_id', $gymId);
        }

        $logs = $logsQuery->get();

        // Initial summary counts based on today
        $todayLogsQuery = AttendanceLog::whereDate('check_in', Carbon::today());
        if ($gymId !== 'all') {
            $todayLogsQuery->where('gym_id', $gymId);
        }
        $todayLogs = $todayLogsQuery->get();
        $todayEntriesCount = $todayLogs->count();
        $currentlyInGymCount = $todayLogs->whereNull('check_out')->count();
        $completedSessionsCount = $todayLogs->whereNotNull('check_out')->count();

        // Clients for dropdown check-in (only members belonging to the current gym/gyms)
        $clientsQuery = User::where('role', 'member')->where('is_active', 1)->with('profile');
        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }
        $clients = $clientsQuery->get();

        return view('asistencia.index', compact(
            'logs', 
            'clients', 
            'selectedDate', 
            'todayEntriesCount', 
            'currentlyInGymCount',
            'completedSessionsCount'
        ));
    }

    /**
     * Register manual or QR check-in.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'dni' => 'nullable|string',
            'entry_method' => 'nullable|string',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $msg = 'Debes seleccionar una sucursal específica para poder registrar una asistencia.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $msg]);
        }

        // Retrieve target user either by user_id or DNI/QR string (e.g. MEMBER:347, 347, or DNI)
        if ($request->filled('user_id')) {
            $user = User::with('profile')->find($request->user_id);
        } elseif ($request->filled('dni')) {
            $rawInput = trim($request->dni);

            // Check if string comes from Carnet Digital QR (e.g. MEMBER:347)
            if (preg_match('/^MEMBER:(\d+)$/i', $rawInput, $matches)) {
                $user = User::with('profile')->find($matches[1]);
            } elseif (is_numeric($rawInput) && User::where('id', $rawInput)->exists()) {
                $user = User::with('profile')->find($rawInput);
            } else {
                $cleanDni = preg_replace('/[^a-zA-Z0-9]/', '', $rawInput);
                $user = User::whereHas('profile', function($q) use ($rawInput, $cleanDni) {
                    $q->where('dni', $rawInput)
                      ->orWhere('dni', $cleanDni);
                })->with('profile')->first();
            }
        } else {
            $user = null;
        }

        if (!$user) {
            $msg = 'Socio no encontrado para la cédula o código QR ingresado.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $msg]);
        }

        // Prevent checking in if already checked in and not checked out today
        $alreadyCheckedIn = AttendanceLog::where('user_id', $user->id)
            ->where('gym_id', $gymId)
            ->whereNull('check_out')
            ->whereDate('check_in', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            $userName = ($user->profile->first_name ?? 'El cliente') . ' ' . ($user->profile->last_name ?? '');
            $msg = "{$userName} ya se encuentra dentro del gimnasio (marcó entrada y aún no registra salida).";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $msg]);
        }

        $entryMethod = $request->input('entry_method', 'admin');

        try {
            $log = AttendanceLog::create([
                'gym_id' => $gymId,
                'user_id' => $user->id,
                'check_in' => Carbon::now(),
                'entry_method' => $entryMethod,
                'status' => 'valid',
            ]);

            AdminAuditLog::record('INSERT', 'attendance_logs', $log->id, null, $log->toArray(), $gymId);

            if (function_exists('activity') && \Illuminate\Support\Facades\Schema::hasTable('activity_log')) {
                activity()
                    ->performedOn($log)
                    ->causedBy(auth()->user())
                    ->withProperties(['gym_id' => $gymId, 'user_id' => $user->id, 'entry_method' => $entryMethod])
                    ->log("Check-in ({$entryMethod}) registrado para socio #{$user->id}");
            }

            $userName = trim(($user->profile->first_name ?? 'Atleta') . ' ' . ($user->profile->last_name ?? ''));
            $userPhoto = $user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
            $userDni = $user->profile->dni ?? 'Sin DNI';
            $msg = "¡Check-in exitoso para {$userName}!";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'log_id' => $log->id,
                    'user_name' => $userName,
                    'user_photo' => $userPhoto,
                    'user_dni' => $userDni,
                ]);
            }

            return redirect()->route('asistencia.index')->with('success', $msg);

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            
            // Catch database trigger reject (SQLSTATE 45000)
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al realizar check-in: ' . $errorMessage;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorText], 422);
            }

            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            $errorText = 'Error inesperado: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorText], 500);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        }
    }

    /**
     * Register manual check-out.
     */
    public function checkOut($id)
    {
        $gymId = $this->getActiveGymId();

        $query = AttendanceLog::with('user.profile');
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $log = $query->findOrFail($id);

        if ($log->check_out) {
            $msg = 'Esta asistencia ya cuenta con registro de salida.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withErrors(['error' => $msg]);
        }

        $oldData = $log->toArray();

        $log->update([
            'check_out' => Carbon::now(),
        ]);

        AdminAuditLog::record('UPDATE', 'attendance_logs', $log->id, $oldData, $log->fresh()->toArray(), $gymId);

        if (function_exists('activity')) {
            activity()
                ->performedOn($log)
                ->causedBy(auth()->user())
                ->withProperties(['gym_id' => $gymId, 'user_id' => $log->user_id])
                ->log("Check-out registrado para socio #{$log->user_id}");
        }

        $userName = trim(($log->user->profile->first_name ?? 'Atleta') . ' ' . ($log->user->profile->last_name ?? ''));
        $msg = "¡Salida registrada con éxito para {$userName}!";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Search clients by DNI, name or email for AJAX check-in search.
     */
    public function searchClientsByDni(Request $request)
    {
        $rawQuery = trim($request->input('q', ''));
        $gymId = $this->getActiveGymId();

        $clientsQuery = User::where('role', 'member')
            ->where('is_active', 1)
            ->with('profile');

        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }

        if (!empty($rawQuery)) {
            $cleanDniQuery = preg_replace('/[^a-zA-Z0-9]/', '', $rawQuery);

            $clientsQuery->where(function($q) use ($rawQuery, $cleanDniQuery) {
                $q->whereHas('profile', function($pq) use ($rawQuery, $cleanDniQuery) {
                    $pq->where('dni', 'LIKE', "%{$rawQuery}%")
                       ->orWhere('first_name', 'LIKE', "%{$rawQuery}%")
                       ->orWhere('last_name', 'LIKE', "%{$rawQuery}%");

                    if (!empty($cleanDniQuery)) {
                        $pq->orWhere('dni', 'LIKE', "%{$cleanDniQuery}%");
                    }
                })->orWhere('email', 'LIKE', "%{$rawQuery}%");
            });
        }

        $clients = $clientsQuery->take(15)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => trim(($user->profile->first_name ?? 'Atleta') . ' ' . ($user->profile->last_name ?? '')),
                'dni' => $user->profile->dni ?? 'Sin DNI',
                'email' => $user->email,
                'photo' => $user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
            ];
        });

        return response()->json($clients);
    }

    /**
     * Get attendance logs by date / period via AJAX.
     */
    public function getLogsByDate(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $period = $request->input('period', 'today');
        $gymId = $this->getActiveGymId();

        $logsQuery = AttendanceLog::with(['user.profile', 'gym']);

        $periodLabel = 'Hoy';

        if ($period === 'this_week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
            $logsQuery->whereBetween('check_in', [$startDate, $endDate]);
            $periodLabel = 'Esta Semana';
        } elseif ($period === 'last_week') {
            $startDate = Carbon::now()->subWeek()->startOfWeek();
            $endDate = Carbon::now()->subWeek()->endOfWeek();
            $logsQuery->whereBetween('check_in', [$startDate, $endDate]);
            $periodLabel = 'Semana Anterior';
        } elseif ($period === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $logsQuery->whereBetween('check_in', [$startDate, $endDate]);
            $periodLabel = 'Mes Actual';
        } elseif ($period === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $logsQuery->whereBetween('check_in', [$startDate, $endDate]);
            $periodLabel = 'Periodo Personalizado';
        } else {
            $logsQuery->whereDate('check_in', $selectedDate);
            $periodLabel = Carbon::parse($selectedDate)->format('d/m/Y');
        }

        $logsQuery->orderBy('check_in', 'desc');

        if ($gymId !== 'all') {
            $logsQuery->where('gym_id', $gymId);
        }

        $allLogs = $logsQuery->get();

        // Calculate stats synchronized with the selected period
        $periodEntriesCount = $allLogs->count();
        $currentlyInGymCount = $allLogs->whereNull('check_out')->count();
        $completedSessionsCount = $allLogs->whereNotNull('check_out')->count();

        $methodMap = [
            'admin' => 'Admin',
            'biometric' => 'Biométrico',
            'rfid' => 'RFID',
            'app_manual' => 'App Móvil'
        ];
        $methodBadge = [
            'admin' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'biometric' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            'rfid' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'app_manual' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
        ];

        $logs = $allLogs->map(function($log) use ($methodMap, $methodBadge) {
            return [
                'id' => $log->id,
                'user_name' => trim(($log->user->profile->first_name ?? 'Atleta') . ' ' . ($log->user->profile->last_name ?? '')),
                'user_email' => $log->user->email ?? '',
                'user_photo' => $log->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
                'gym_name' => $log->gym->name ?? 'Sucursal Global',
                'check_in_time' => Carbon::parse($log->check_in)->format('H:i'),
                'check_in_date' => Carbon::parse($log->check_in)->format('d/m/Y'),
                'check_out' => $log->check_out ? [
                    'time' => Carbon::parse($log->check_out)->format('H:i'),
                    'date' => Carbon::parse($log->check_out)->format('d/m/Y')
                ] : null,
                'entry_method_label' => $methodMap[$log->entry_method] ?? $log->entry_method,
                'entry_method_badge' => $methodBadge[$log->entry_method] ?? 'bg-slate-950 text-slate-500 border-slate-850',
                'check_out_url' => route('asistencia.check_out', $log->id),
            ];
        });

        $getGymActivePlanHelper = function($gId) {
            if (!$gId || $gId === 'all') return null;
            if (\Illuminate\Support\Facades\Schema::hasTable('gym_subscriptions')) {
                $sub = \DB::table('gym_subscriptions')
                    ->where('gym_id', $gId)
                    ->whereIn('status', ['active', 'trialing'])
                    ->latest('id')
                    ->first();
                if ($sub && $sub->plan_id) {
                    $plan = SaasSubscriptionPlan::find($sub->plan_id);
                    if ($plan) return $plan;
                }
            }
            $gym = Gym::find($gId);
            if ($gym && $gym->current_plan_id) {
                return SaasSubscriptionPlan::find($gym->current_plan_id);
            }
            return null;
        };

        if ($gymId === 'all') {
            $aforoCurrentUsers = User::where('role', 'member')->count();
            $allGymsList = Gym::all();
            $aforoMaxUsers = 0;
            foreach ($allGymsList as $g) {
                $plan = $getGymActivePlanHelper($g->id);
                $aforoMaxUsers += ($plan ? ($plan->max_users ?? 50) : 50);
            }
        } else {
            $aforoCurrentUsers = User::where('gym_id', $gymId)->where('role', 'member')->count();
            $plan = $getGymActivePlanHelper($gymId);
            $aforoMaxUsers = $plan ? ($plan->max_users ?? 50) : 50;
        }

        return response()->json([
            'selected_date' => $selectedDate,
            'period' => $period,
            'period_label' => $periodLabel,
            'period_entries_count' => $periodEntriesCount,
            'currently_in_gym_count' => $currentlyInGymCount,
            'completed_sessions_count' => $completedSessionsCount,
            'aforo_current_users' => $aforoCurrentUsers,
            'aforo_max_users' => $aforoMaxUsers,
            'logs' => $logs
        ]);
    }
}

