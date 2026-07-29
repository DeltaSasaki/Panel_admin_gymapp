<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\GymClass;
use App\Models\ClassSchedule;
use App\Models\ClassBooking;
use App\Models\AttendanceLog;
use App\Models\Trainer;
use App\Models\User;

class ClassAndAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $hasCategoryType = Schema::hasColumn('gym_classes', 'category_type');
        $hasLocation = Schema::hasColumn('gym_classes', 'location');

        // 1. Gym Classes per Gym
        $classesTemplates = [
            ['name' => 'Crossfit WOD', 'desc' => 'Entrenamiento de alta intensidad con levantamientos y gimnásticos.', 'dur' => 60, 'cap' => 15, 'color' => '#EF4444'],
            ['name' => 'Yoga Vinyasa Flow', 'desc' => 'Conexión de cuerpo y mente a través de la respiración.', 'dur' => 60, 'cap' => 20, 'color' => '#10B981'],
            ['name' => 'Spinning / Indoor Cycling', 'desc' => 'Sesión intensiva sobre bicicleta estática al ritmo de la música.', 'dur' => 45, 'cap' => 25, 'color' => '#F59E0B'],
            ['name' => 'HIIT Functional Fit', 'desc' => 'Circuito metabólico quema grasa.', 'dur' => 45, 'cap' => 15, 'color' => '#3B82F6'],
            ['name' => 'Boxeo & Kickboxing', 'desc' => 'Técnica de golpeo, sacos y acondicionamiento aeróbico.', 'dur' => 60, 'cap' => 12, 'color' => '#8B5CF6'],
        ];

        $gymClasses = [];
        for ($gymId = 1; $gymId <= 5; $gymId++) {
            foreach ($classesTemplates as $ct) {
                $classData = [
                    'gym_id' => $gymId,
                    'name' => $ct['name'],
                    'description' => $ct['desc'],
                    'duration_minutes' => $ct['dur'],
                    'capacity' => $ct['cap'],
                    'color_code' => $ct['color'],
                    'is_active' => 1,
                ];

                if ($hasCategoryType) {
                    $classData['category_type'] = 'clase';
                }
                if ($hasLocation) {
                    $classData['location'] = 'Sala Principal G' . $gymId;
                }

                $gc = GymClass::create($classData);
                $gymClasses[$gymId][] = $gc;
            }
        }

        // 2. Class Schedules (100+ schedules across past and future days)
        $schedules = [];
        $schedId = 1;

        for ($gymId = 1; $gymId <= 5; $gymId++) {
            $trainers = Trainer::where('gym_id', $gymId)->get();
            $classes = $gymClasses[$gymId];

            // Schedules for past 20 days and next 10 days
            for ($dayOffset = -20; $dayOffset <= 10; $dayOffset++) {
                $schedDate = $now->copy()->addDays($dayOffset);

                // Create 2 classes per day per gym
                for ($slot = 1; $slot <= 2; $slot++) {
                    $selectedClass = $classes[($dayOffset + $slot + 20) % count($classes)];
                    $trainer = $trainers[$slot % count($trainers)] ?? null;

                    $startTime = ($slot == 1) ? '08:00:00' : '19:00:00';
                    $endTime = ($slot == 1) ? '09:00:00' : '20:00:00';
                    $status = ($dayOffset < 0) ? 'completed' : 'scheduled';

                    $schedule = ClassSchedule::create([
                        'id' => $schedId,
                        'gym_id' => $gymId,
                        'gym_class_id' => $selectedClass->id,
                        'trainer_id' => $trainer ? $trainer->id : null,
                        'scheduled_date' => $schedDate->toDateString(),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'status' => $status,
                    ]);

                    $schedules[] = $schedule;
                    $schedId++;
                }
            }
        }

        // 3. Class Bookings (500+ bookings)
        $members = User::where('role', 'member')->get();

        foreach ($schedules as $index => $sched) {
            $gymMembers = $members->where('gym_id', $sched->gym_id)->values();

            // Book 4-6 members per schedule
            $bookingCount = min(5, count($gymMembers));
            for ($b = 0; $b < $bookingCount; $b++) {
                $member = $gymMembers[($index + $b) % count($gymMembers)];

                $status = 'booked';
                if ($sched->status === 'completed') {
                    $status = ($b % 4 == 0) ? 'no_show' : 'attended';
                }

                ClassBooking::create([
                    'class_schedule_id' => $sched->id,
                    'user_id' => $member->id,
                    'status' => $status,
                    'booked_at' => Carbon::parse($sched->scheduled_date)->subDays(rand(1, 3))->toDateTimeString(),
                ]);
            }
        }

        // 4. Attendance Logs (1,000+ attendance check-ins within active membership dates)
        $entryMethods = ['biometric', 'app_manual', 'rfid', 'admin'];

        foreach ($members as $idx => $m) {
            $gymId = $m->gym_id;

            // Generate 3-4 check-ins for each member over the last 45 days
            for ($att = 1; $att <= 4; $att++) {
                $checkInDate = $now->copy()->subDays($att * 10 + ($idx % 5));

                AttendanceLog::create([
                    'gym_id' => $gymId,
                    'user_id' => $m->id,
                    'check_in' => $checkInDate->copy()->setTime(rand(7, 19), rand(0, 59))->toDateTimeString(),
                    'check_out' => $checkInDate->copy()->setTime(rand(20, 21), rand(0, 59))->toDateTimeString(),
                    'entry_method' => $entryMethods[$idx % count($entryMethods)],
                    'status' => 'valid',
                    'createdAt' => $checkInDate,
                ]);
            }
        }
    }
}
