<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Models\User;
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
            AttendanceLog::create([
                'gym_id' => $gymId,
                'user_id' => $user->id,
                'check_in' => Carbon::now(),
                'entry_method' => 'admin',
                'status' => 'valid',
            ]);

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

        $log->update([
            'check_out' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', '¡Salida registrada con éxito!');
    }
}
