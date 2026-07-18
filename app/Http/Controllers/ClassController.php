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
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para crear una clase.']);
        }

        GymClass::create([
            'gym_id' => $gymId,
            'name' => $request->name,
            'description' => $request->description,
            'capacity' => $request->capacity,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('success', 'Clase grupal creada exitosamente.');
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
            return redirect()->back()->withInput()->withErrors(['error' => 'Debes seleccionar una sucursal específica para programar un horario.']);
        }

        // Verify if class belongs to active gym context
        $gymClass = GymClass::findOrFail($request->gym_class_id);

        if ($gymClass->gym_id != $gymId) {
            return redirect()->back()->withInput()->withErrors(['error' => 'La clase seleccionada no corresponde a la sucursal activa.']);
        }

        ClassSchedule::create([
            'gym_id' => $gymId,
            'gym_class_id' => $request->gym_class_id,
            'trainer_id' => $request->trainer_id,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'scheduled',
        ]);

        return redirect()->back()->with('success', 'Clase programada exitosamente.');
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

        // Duplicate booking validation
        $exists = ClassBooking::where('class_schedule_id', $request->class_schedule_id)
            ->where('user_id', $request->user_id)
            ->where('status', '<>', 'cancelled')
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->withErrors(['error' => 'El atleta ya cuenta con una reserva activa para esta sesión.']);
        }

        try {
            // Note: The database trigger `trg_class_capacity_check` will automatically
            // change status to 'waitlisted' if capacity is exceeded on insert.
            ClassBooking::create([
                'class_schedule_id' => $request->class_schedule_id,
                'user_id' => $request->user_id,
                'status' => 'booked', // Trigger overrides to waitlisted if full
            ]);

            return redirect()->back()->with('success', '¡Inscripción registrada exitosamente!');

        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = $e->getMessage();
            if (preg_match("/SQLSTATE\[45000\]: [^:]+: (.+)/", $errorMessage, $matches)) {
                $errorText = trim($matches[1]);
            } else {
                $errorText = 'Error de base de datos al inscribir: ' . $errorMessage;
            }
            return redirect()->back()->withInput()->withErrors(['error' => $errorText]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()]);
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

            // Auto promotion logic: if previous status was 'booked' (active slot) or 'waitlisted' (waitlist slot)
            // and is now 'cancelled', we check if there are people in the waitlist and promote the oldest one!
            if ($oldStatus === 'booked' && $newStatus === 'cancelled') {
                $oldestWaitlisted = ClassBooking::where('class_schedule_id', $booking->class_schedule_id)
                    ->where('status', 'waitlisted')
                    ->orderBy('booked_at', 'asc') // order by creation time
                    ->first();

                if ($oldestWaitlisted) {
                    $oldestWaitlisted->update(['status' => 'booked']);
                }
            }

            return redirect()->back()->with('success', 'Estado de la reservación actualizado con éxito.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error al actualizar estado: ' . $e->getMessage()]);
        }
    }
}
