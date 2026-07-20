<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\AdminAuditLog;
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

        // Fetch logs for the selected date
        $logsQuery = AttendanceLog::with(['user.profile', 'gym'])
            ->whereDate('check_in', $selectedDate)
            ->orderBy('check_in', 'desc');

        if ($gymId !== 'all') {
            $logsQuery->where('gym_id', $gymId);
        }

        $logs = $logsQuery->get();

        // Count stats (always based on today for current active count)
        $todayLogsQuery = AttendanceLog::whereDate('check_in', Carbon::today());
        if ($gymId !== 'all') {
            $todayLogsQuery->where('gym_id', $gymId);
        }
        $todayLogs = $todayLogsQuery->get();
        $todayEntriesCount = $todayLogs->count();
        $currentlyInGymCount = $todayLogs->whereNull('check_out')->count();

        // Clients for dropdown check-in (only members belonging to the current gym/gyms)
        $clientsQuery = User::where('role', 'member')->where('is_active', 1)->with('profile');
        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }
        $clients = $clientsQuery->get();

        return view('asistencia.index', compact('logs', 'clients', 'selectedDate', 'todayEntriesCount', 'currentlyInGymCount'));
    }

    /**
     * Register manual check-in.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para registrar una asistencia.']);
        }

        // Get targeted user
        $user = User::findOrFail($request->user_id);

        // Prevent checking in if already checked in and not checked out today
        $alreadyCheckedIn = AttendanceLog::where('user_id', $user->id)
            ->where('gym_id', $gymId)
            ->whereNull('check_out')
            ->whereDate('check_in', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return redirect()->back()->withInput()->withErrors(['error' => 'El cliente ya se encuentra dentro de las instalaciones (marcó entrada y no salida).']);
        }

        try {
            $log = AttendanceLog::create([
                'gym_id' => $gymId,
                'user_id' => $user->id,
                'check_in' => Carbon::now(),
                'entry_method' => 'admin',
                'status' => 'valid',
            ]);

            AdminAuditLog::record('INSERT', 'attendance_logs', $log->id, null, $log->toArray(), $gymId);

            return redirect()->route('asistencia.index')->with('success', '¡Check-in exitoso para ' . ($user->profile->first_name ?? 'Cliente') . '!');

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            
            // Catch database trigger reject (SQLSTATE 45000)
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al realizar check-in: ' . $errorMessage;
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    /**
     * Register manual check-out.
     */
    public function checkOut($id)
    {
        $gymId = $this->getActiveGymId();

        $query = AttendanceLog::query();
        if ($gymId !== 'all') {
            $query->where('gym_id', $gymId);
        }

        $log = $query->findOrFail($id);

        if ($log->check_out) {
            return redirect()->back()->withErrors(['error' => 'Esta asistencia ya cuenta con registro de salida.']);
        }

        $oldData = $log->toArray();

        $log->update([
            'check_out' => Carbon::now(),
        ]);

        AdminAuditLog::record('UPDATE', 'attendance_logs', $log->id, $oldData, $log->fresh()->toArray(), $gymId);

        return redirect()->back()->with('success', '¡Salida registrada con éxito!');
    }

    /**
     * Search clients by DNI, name or email for AJAX check-in search.
     */
    public function searchClientsByDni(Request $request)
    {
        $query = trim($request->input('q', ''));
        $gymId = $this->getActiveGymId();

        $clientsQuery = User::where('role', 'member')
            ->where('is_active', 1)
            ->with('profile');

        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }

        if (!empty($query)) {
            $clientsQuery->where(function($q) use ($query) {
                $q->whereHas('profile', function($pq) use ($query) {
                    $pq->where('dni', 'LIKE', "%{$query}%")
                       ->orWhere('first_name', 'LIKE', "%{$query}%")
                       ->orWhere('last_name', 'LIKE', "%{$query}%");
                })->orWhere('email', 'LIKE', "%{$query}%");
            });
        }

        $clients = $clientsQuery->take(15)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => ($user->profile->first_name ?? 'Atleta') . ' ' . ($user->profile->last_name ?? ''),
                'dni' => $user->profile->dni ?? 'Sin DNI',
                'email' => $user->email,
                'photo' => $user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
            ];
        });

        return response()->json($clients);
    }

    /**
     * Get attendance logs by date via AJAX.
     */
    public function getLogsByDate(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $gymId = $this->getActiveGymId();

        $logsQuery = AttendanceLog::with(['user.profile', 'gym'])
            ->whereDate('check_in', $selectedDate)
            ->orderBy('check_in', 'desc');

        if ($gymId !== 'all') {
            $logsQuery->where('gym_id', $gymId);
        }

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

        $logs = $logsQuery->get()->map(function($log) use ($methodMap, $methodBadge) {
            return [
                'id' => $log->id,
                'user_name' => ($log->user->profile->first_name ?? 'Atleta') . ' ' . ($log->user->profile->last_name ?? ''),
                'user_email' => $log->user->email ?? '',
                'user_photo' => $log->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
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

        return response()->json([
            'selected_date' => $selectedDate,
            'logs' => $logs
        ]);
    }
}
