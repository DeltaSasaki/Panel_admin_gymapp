<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GymClass;
use App\Models\ClassSchedule;
use App\Models\ClassBooking;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;

class ClassController extends Controller
{
    /**
     * Display classes list and upcoming schedules.
     */
    public function index()
    {
        $gymId = $this->getActiveGymId();

        // 1. Fetch Gym Classes templates
        $classesQuery = GymClass::query();
        if ($gymId !== 'all') {
            $classesQuery->where('gym_id', $gymId);
        }
        $classes = $classesQuery->get();

        // 2. Fetch Upcoming Schedules (Today onwards)
        $schedulesQuery = ClassSchedule::with(['gymClass', 'trainer.user.profile'])
            ->where('scheduled_date', '>=', Carbon::today()->toDateString())
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('start_time', 'asc');

        if ($gymId !== 'all') {
            $schedulesQuery->where('gym_id', $gymId);
        }
        
        $schedules = $schedulesQuery->get();

        // 3. Fetch Trainers for dropdown
        $trainersQuery = Trainer::with('user.profile')->where('is_active', 1);
        if ($gymId !== 'all') {
            $trainersQuery->where('gym_id', $gymId);
        }
        $trainers = $trainersQuery->get();

        return view('clases.index', compact('classes', 'schedules', 'trainers'));
    }

    /**
     * Store a new class type.
     */
    public function storeClass(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:5',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $errorMsg = 'Debes seleccionar una sucursal específica para crear una clase.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        $gymClass = GymClass::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => 1,
        ]);

        $message = 'Clase grupal creada exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gym_class' => $gymClass
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update existing gym class template.
     */
    public function updateClass(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:5',
        ]);

        $gymId = $this->getActiveGymId();
        $gymClass = GymClass::where('gym_id', $gymId)->findOrFail($id);

        $gymClass->update([
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity,
            'duration_minutes' => $request->duration_minutes,
        ]);

        $message = 'Clase grupal actualizada exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gym_class' => $gymClass
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle active status of a gym class template.
     */
    public function deleteClass($id)
    {
        $gymId = $this->getActiveGymId();
        $gymClass = GymClass::where('gym_id', $gymId)->findOrFail($id);

        $newStatus = $gymClass->is_active ? 0 : 1;
        $gymClass->update(['is_active' => $newStatus]);

        $cancelledScheduleIds = [];
        $reactivatedScheduleIds = [];

        // If disabling the class template, automatically cancel all upcoming scheduled sessions for this class
        if ($newStatus == 0) {
            $cancelledScheduleIds = ClassSchedule::where('gym_class_id', $id)
                ->where('scheduled_date', '>=', Carbon::today()->toDateString())
                ->where('status', 'scheduled')
                ->pluck('id')
                ->toArray();

            if (!empty($cancelledScheduleIds)) {
                ClassSchedule::whereIn('id', $cancelledScheduleIds)->update(['status' => 'cancelled']);
            }
        } else {
            // If reactivating the class template, automatically restore upcoming cancelled sessions to scheduled status
            $reactivatedScheduleIds = ClassSchedule::where('gym_class_id', $id)
                ->where('scheduled_date', '>=', Carbon::today()->toDateString())
                ->where('status', 'cancelled')
                ->pluck('id')
                ->toArray();

            if (!empty($reactivatedScheduleIds)) {
                ClassSchedule::whereIn('id', $reactivatedScheduleIds)->update(['status' => 'scheduled']);
            }
        }

        $message = $newStatus 
            ? "Clase grupal '{$gymClass->name}' reactivada con éxito. " . (count($reactivatedScheduleIds) > 0 ? count($reactivatedScheduleIds) . " sesión(es) próxima(s) volvieron a estar programadas de forma automática." : "")
            : "Clase grupal '{$gymClass->name}' inhabilitada. " . (count($cancelledScheduleIds) > 0 ? count($cancelledScheduleIds) . " sesión(es) próxima(s) fueron canceladas de forma automática." : "");

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'gym_class_id' => $id,
                'is_active' => $newStatus,
                'cancelled_schedule_ids' => $cancelledScheduleIds,
                'reactivated_schedule_ids' => $reactivatedScheduleIds
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Program a new class session date-time.
     */
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'gym_class_id' => 'required|exists:gym_classes,id',
            'trainer_id' => 'required|exists:trainers,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            $errorMsg = 'Debes seleccionar una sucursal específica para programar un horario.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        // 1. Verify if class template is active
        $gymClass = GymClass::findOrFail($request->gym_class_id);
        if ($gymClass->gym_id != $gymId) {
            $errorMsg = 'La clase seleccionada no corresponde a la sucursal activa.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        if (!$gymClass->is_active) {
            $errorMsg = "No se pueden programar sesiones para la clase '{$gymClass->name}' porque se encuentra inhabilitada.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        // 2. Verify if trainer is active
        $trainer = Trainer::with('user.profile')->findOrFail($request->trainer_id);
        if (!$trainer->is_active) {
            $errorMsg = 'El entrenador seleccionado se encuentra inactivo.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        // 3. Overlap Check for Trainer
        $overlap = ClassSchedule::where('gym_id', $gymId)
            ->where('trainer_id', $request->trainer_id)
            ->where('scheduled_date', $request->scheduled_date)
            ->where('status', 'scheduled')
            ->where(function($q) use ($request) {
                $q->where('start_time', '<', $request->end_time)
                  ->where('end_time', '>', $request->start_time);
            })
            ->with('gymClass')
            ->first();

        if ($overlap) {
            $trainerName = $trainer->user->profile->first_name ?? 'El entrenador';
            $overlapClass = $overlap->gymClass->name ?? 'otra clase';
            $overlapTime = substr($overlap->start_time, 0, 5) . ' - ' . substr($overlap->end_time, 0, 5);
            $errorMsg = "El Coach {$trainerName} ya tiene programada la sesión '{$overlapClass}' en ese mismo horario ({$overlapTime}).";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        $schedule = ClassSchedule::create([
            'gym_id' => $gymId,
            'gym_class_id' => $request->gym_class_id,
            'trainer_id' => $request->trainer_id,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'scheduled',
        ]);

        $schedule->load(['gymClass', 'trainer.user.profile']);

        $message = 'Clase programada exitosamente.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'schedule' => $schedule
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update an existing scheduled session.
     */
    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'gym_class_id' => 'required|exists:gym_classes,id',
            'trainer_id' => 'required|exists:trainers,id',
            'scheduled_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:scheduled,cancelled,completed',
        ]);

        $gymId = $this->getActiveGymId();
        $schedule = ClassSchedule::where('gym_id', $gymId)->findOrFail($id);

        // 1. Verify if class template is active
        $gymClass = GymClass::findOrFail($request->gym_class_id);
        if (!$gymClass->is_active) {
            $errorMsg = "La clase '{$gymClass->name}' está inhabilitada y no puede ser programada.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        // 2. Overlap Check for Trainer if status is scheduled
        if ($request->status === 'scheduled') {
            $overlap = ClassSchedule::where('gym_id', $gymId)
                ->where('id', '<>', $id)
                ->where('trainer_id', $request->trainer_id)
                ->where('scheduled_date', $request->scheduled_date)
                ->where('status', 'scheduled')
                ->where(function($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
                })
                ->with('gymClass')
                ->first();

            if ($overlap) {
                $trainer = Trainer::with('user.profile')->find($request->trainer_id);
                $trainerName = $trainer->user->profile->first_name ?? 'El entrenador';
                $overlapClass = $overlap->gymClass->name ?? 'otra clase';
                $overlapTime = substr($overlap->start_time, 0, 5) . ' - ' . substr($overlap->end_time, 0, 5);
                $errorMsg = "El Coach {$trainerName} ya tiene programada la sesión '{$overlapClass}' en ese mismo horario ({$overlapTime}).";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
            }
        }

        $schedule->update([
            'gym_class_id' => $request->gym_class_id,
            'trainer_id' => $request->trainer_id,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status,
        ]);

        $schedule->load(['gymClass', 'trainer.user.profile']);

        $message = 'Sesión de clase actualizada con éxito.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'schedule' => $schedule
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Delete or cancel a scheduled session.
     */
    public function deleteSchedule($id)
    {
        $gymId = $this->getActiveGymId();
        $schedule = ClassSchedule::where('gym_id', $gymId)->findOrFail($id);

        // Delete schedule session
        $schedule->delete();

        $message = 'Sesión de clase eliminada exitosamente del horario.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'schedule_id' => $id
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * View bookings for a specific class schedule session.
     */
    public function bookings($id)
    {
        $gymId = $this->getActiveGymId();

        // Find schedule
        $schedule = ClassSchedule::with(['gymClass', 'trainer.user.profile'])->findOrFail($id);

        // Verify gym context scope
        if ($gymId !== 'all' && $schedule->gym_id != $gymId) {
            abort(403, 'No tienes permiso para ver esta clase.');
        }

        // Fetch bookings for this schedule session
        $bookings = ClassBooking::where('class_schedule_id', $id)
            ->with('user.profile')
            ->get();

        // Clients dropdown for reception manual booking
        $clientsQuery = User::where('role', 'member')->where('is_active', 1)->with('profile');
        if ($gymId !== 'all') {
            $clientsQuery->where('gym_id', $gymId);
        }
        $clients = $clientsQuery->get();

        return view('clases.bookings', compact('schedule', 'bookings', 'clients'));
    }

    /**
     * Book a client manually.
     */
    public function bookClient(Request $request)
    {
        $request->validate([
            'class_schedule_id' => 'required|exists:class_schedules,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $schedule = ClassSchedule::with('gymClass')->findOrFail($request->class_schedule_id);

        if ($schedule->status === 'cancelled') {
            $errorMsg = 'Esta sesión se encuentra cancelada y no acepta reservaciones.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        if (!$schedule->gymClass || !$schedule->gymClass->is_active) {
            $errorMsg = 'La clase asociada a esta sesión se encuentra inhabilitada.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        // Duplicate booking validation
        $exists = ClassBooking::where('class_schedule_id', $request->class_schedule_id)
            ->where('user_id', $request->user_id)
            ->where('status', '<>', 'cancelled')
            ->exists();

        if ($exists) {
            $errorMsg = 'El atleta ya cuenta con una reserva activa para esta sesión.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorMsg]);
        }

        try {
            // Note: The database trigger `trg_class_capacity_check` will automatically
            // change status to 'waitlisted' if capacity is exceeded on insert.
            $booking = ClassBooking::create([
                'class_schedule_id' => $request->class_schedule_id,
                'user_id' => $request->user_id,
                'status' => 'booked', // Trigger overrides to waitlisted if full
            ]);

            $booking->load('user.profile');

            $allBookings = ClassBooking::where('class_schedule_id', $request->class_schedule_id)->get();
            $counts = [
                'booked' => $allBookings->where('status', 'booked')->count(),
                'waitlisted' => $allBookings->where('status', 'waitlisted')->count(),
                'attended' => $allBookings->where('status', 'attended')->count(),
            ];

            $message = '¡Inscripción registrada exitosamente!';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'booking' => $booking,
                    'counts' => $counts
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al inscribir: ' . $errorMessage;
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
     * Update booking status (cancelling, attendance marking).
     */
    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:booked,cancelled,attended,waitlisted,no_show',
        ]);

        $booking = ClassBooking::findOrFail($id);

        try {
            $oldStatus = $booking->status;
            $newStatus = $request->status;

            $booking->update(['status' => $newStatus]);
            $booking->load('user.profile');

            $promotedBookingId = null;

            // Auto promotion logic: if previous status was 'booked' (active slot) or 'waitlisted' (waitlist slot)
            // and is now 'cancelled', we check if there are people in the waitlist and promote the oldest one!
            if ($oldStatus === 'booked' && $newStatus === 'cancelled') {
                $oldestWaitlisted = ClassBooking::where('class_schedule_id', $booking->class_schedule_id)
                    ->where('status', 'waitlisted')
                    ->orderBy('booked_at', 'asc') // order by creation time
                    ->first();

                if ($oldestWaitlisted) {
                    $oldestWaitlisted->update(['status' => 'booked']);
                    $promotedBookingId = $oldestWaitlisted->id;
                }
            }

            $allBookings = ClassBooking::where('class_schedule_id', $booking->class_schedule_id)->get();
            $counts = [
                'booked' => $allBookings->where('status', 'booked')->count(),
                'waitlisted' => $allBookings->where('status', 'waitlisted')->count(),
                'attended' => $allBookings->where('status', 'attended')->count(),
            ];

            $message = 'Estado de la reservación actualizado con éxito.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'booking' => $booking,
                    'promoted_booking_id' => $promotedBookingId,
                    'counts' => $counts
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            $errorMsg = 'Error al actualizar estado: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }
            return redirect()->back()->withErrors(['error' => $errorMsg]);
        }
    }
}
