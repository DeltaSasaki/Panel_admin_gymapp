<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BodyMeasurement;
use App\Models\UserGoal;
use App\Models\Notification;
use App\Models\AdminAuditLog;
use App\Models\Trainer;
use App\Models\User;

class HealthAndAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $members = User::where('role', 'member')->get();

        // 1. Body Measurements (500+ measurements across members)
        foreach ($members as $idx => $m) {
            $heightCm = 165.00 + ($idx % 25);

            // Create 2-3 body measurements per member
            for ($bm = 1; $bm <= 2; $bm++) {
                $weightKg = 70.00 + ($idx % 20) - ($bm * 1.5);
                $heightM = $heightCm / 100.0;
                $bmi = round($weightKg / ($heightM * $heightM), 2);

                $category = 'normal';
                if ($bmi < 18.5) $category = 'underweight';
                elseif ($bmi >= 25.0 && $bmi < 30.0) $category = 'overweight';
                elseif ($bmi >= 30.0) $category = 'obese';

                $measuredAt = $now->copy()->subDays($bm * 30);

                BodyMeasurement::create([
                    'user_id' => $m->id,
                    'weight_kg' => $weightKg,
                    'height_cm' => $heightCm,
                    'bmi' => $bmi,
                    'bmi_category' => $category,
                    'body_fat_pct' => 18.5 - ($bm * 0.5),
                    'muscle_mass_kg' => 32.0 + ($bm * 0.8),
                    'waist_cm' => 82.0 - $bm,
                    'hip_cm' => 95.0,
                    'measured_at' => $measuredAt->toDateTimeString(),
                    'notes' => 'Control mensual de progreso corporal.',
                    'createdAt' => $measuredAt,
                    'updatedAt' => $measuredAt,
                ]);
            }
        }

        // 2. User Goals (200+ goals)
        $goalTypes = ['lose_weight', 'gain_muscle', 'maintain', 'improve_endurance'];
        foreach ($members as $idx => $m) {
            UserGoal::create([
                'user_id' => $m->id,
                'goal_type' => $goalTypes[$idx % count($goalTypes)],
                'target_weight' => 68.00 + ($idx % 15),
                'target_date' => $now->copy()->addMonths(3)->toDateString(),
                'is_active' => 1,
                'createdAt' => $now->copy()->subMonths(1),
                'updatedAt' => $now,
            ]);
        }

        // 3. Fitness Assessments (100+ assessments)
        foreach ($members as $idx => $m) {
            if ($idx % 3 == 0) {
                $trainer = Trainer::where('gym_id', $m->gym_id)->first();
                $trainerId = $trainer ? $trainer->id : null;

                DB::table('fitness_assessments')->insert([
                    'gym_id' => $m->gym_id,
                    'user_id' => $m->id,
                    'trainer_id' => $trainerId,
                    'assessment_date' => $now->copy()->subDays(20)->toDateString(),
                    'posture_notes' => 'Ligera hiperlordosis lumbar. Buena alineación de hombros.',
                    'flexibility_rating' => 'good',
                    'cardio_rating' => 'good',
                    'strength_notes' => 'Capacidad óptima en tren inferior. Trabajar fuerza en core.',
                    'general_recommendations' => 'Rutina hipertrofia 4 días/semana + 20min cardio moderado.',
                    'next_assessment_date' => $now->copy()->addDays(40)->toDateString(),
                    'createdAt' => $now->copy()->subDays(20),
                ]);
            }
        }

        // 4. User Medical Notes (50+ medical notes)
        $medicalConditions = ['injury', 'surgery', 'chronic', 'other'];
        foreach ($members as $idx => $m) {
            if ($idx % 6 == 0) {
                DB::table('user_medical_notes')->insert([
                    'user_id' => $m->id,
                    'condition_type' => $medicalConditions[$idx % count($medicalConditions)],
                    'description' => 'Antecedente de esguince de tobillo grado 1 en recuperación.',
                    'restricted_muscle_groups' => 'Espalda baja, Rodilla derecha',
                    'cleared_by_doctor' => 1,
                    'is_active' => 1,
                    'noted_by' => null,
                    'createdAt' => $now->copy()->subDays(30),
                ]);
            }
        }

        // 5. Satisfaction Surveys (150+ NPS surveys)
        $surveyCategories = ['facilities', 'trainers', 'classes', 'cleanliness', 'general'];
        foreach ($members as $idx => $m) {
            if ($idx % 2 == 0) {
                DB::table('satisfaction_surveys')->insert([
                    'gym_id' => $m->gym_id,
                    'user_id' => $m->id,
                    'survey_date' => $now->copy()->subDays(rand(1, 30))->toDateString(),
                    'rating' => rand(8, 10),
                    'category' => $surveyCategories[$idx % count($surveyCategories)],
                    'feedback_text' => 'Excelente servicio e instalaciones impecables.',
                    'status' => 'resolved',
                    'createdAt' => $now->copy()->subDays(rand(1, 30)),
                ]);
            }
        }

        // 6. Notifications (500+ notifications)
        $notifTypes = ['membership_expiry', 'payment_reminder', 'new_routine', 'achievement', 'general'];
        foreach ($members as $idx => $m) {
            for ($n = 1; $n <= 2; $n++) {
                Notification::create([
                    'user_id' => $m->id,
                    'title' => '¡Novedad en GymFlow!',
                    'body' => 'Tienes una nueva actualización disponible en tu panel personal.',
                    'type' => $notifTypes[($idx + $n) % count($notifTypes)],
                    'is_read' => ($n % 2 == 0) ? 1 : 0,
                    'createdAt' => $now->copy()->subDays($n * 5),
                ]);
            }
        }

        // 7. Admin Audit Logs (200+ audit entries)
        $admins = User::whereIn('role', ['admin', 'superadmin'])->get();

        foreach ($admins as $idx => $admin) {
            for ($al = 1; $al <= 40; $al++) {
                AdminAuditLog::create([
                    'gym_id' => $admin->gym_id,
                    'admin_id' => $admin->id,
                    'action_type' => ['INSERT', 'UPDATE', 'DELETE', 'LOGIN_FAILED', 'EXPORT_DATA'][$al % 5],
                    'table_name' => ['users', 'user_memberships', 'membership_payments', 'gyms'][$al % 4],
                    'record_id' => (string)rand(1, 100),
                    'old_data' => json_encode(['status' => 'pending']),
                    'new_data' => json_encode(['status' => 'active']),
                    'ip_address' => '192.168.1.' . (10 + $idx),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'createdAt' => $now->copy()->subDays($al),
                ]);
            }
        }
    }
}
