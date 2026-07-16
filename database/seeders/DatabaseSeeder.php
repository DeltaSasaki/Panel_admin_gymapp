<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Gym;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Trainer;
use App\Models\WorkoutRoutine;
use App\Models\UserAssignedRoutine;
use App\Models\MealPlan;
use App\Models\UserMealPlan;
use App\Models\BodyMeasurement;
use App\Models\WorkoutSession;
use App\Models\Recipe;
use App\Models\MealPlanDay;
use App\Models\Exercise;
use App\Models\RoutineDay;
use App\Models\RoutineExercise;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks for clean seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('routine_exercises')->truncate();
        DB::table('routine_days')->truncate();
        DB::table('exercises')->truncate();
        DB::table('exercise_categories')->truncate();
        DB::table('workout_sessions')->truncate();
        DB::table('body_measurements')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('trainers')->truncate();
        DB::table('user_assigned_routines')->truncate();
        DB::table('user_meal_plans')->truncate();
        DB::table('workout_routines')->truncate();
        DB::table('meal_plan_days')->truncate();
        DB::table('meal_plans')->truncate();
        DB::table('recipes')->truncate();
        DB::table('recipe_categories')->truncate();
        DB::table('gyms')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Gym
        $gym = Gym::create([
            'name' => 'GymFlow HQ',
            'address' => 'Av. de los Deportes 450, Ciudad Deportiva',
            'phone' => '+34 912 345 678',
            'email' => 'info@gymflowhq.com',
            'logo_url' => null,
            'timezone' => 'America/Caracas',
            'is_active' => 1,
        ]);

        // 2. Create Trainer User & Profile
        $trainerUser = User::create([
            'email' => 'coach@gymflow.com',
            'password_hash' => Hash::make('password'),
            'role' => 'trainer',
            'is_active' => 1,
            'email_verified' => 1,
        ]);

        UserProfile::create([
            'user_id' => $trainerUser->id,
            'first_name' => 'Carlos',
            'last_name' => 'Ruiz',
            'phone' => '+34 600 111 222',
            'birth_date' => '1990-05-15',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
            'gym_id' => $gym->id,
        ]);

        $trainer = Trainer::create([
            'user_id' => $trainerUser->id,
            'gym_id' => $gym->id,
            'first_name' => 'Carlos',
            'last_name' => 'Ruiz',
            'email' => 'coach@gymflow.com',
            'phone' => '+34 600 111 222',
            'specialty' => 'Entrenamiento de Fuerza e Hipertrofia',
            'certification' => 'NSCA-CPT, Precision Nutrition L1',
            'experience_years' => 8,
            'photo_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
            'bio' => 'Apasionado por la fuerza y el acondicionamiento metabólico.',
            'is_active' => 1,
            'hire_date' => '2022-01-10',
            'salary' => 2500.00,
        ]);

        // 3. Create Recipe Categories and Recipes
        $catId = DB::table('recipe_categories')->insertGetId([
            'name' => 'Nutrición Deportiva'
        ]);

        $recipeBreakfast = Recipe::create([
            'category_id' => $catId,
            'name' => 'Tortilla de Avena y Claras de Huevo',
            'description' => 'Un desayuno cargado de carbohidratos complejos y proteína de rápida absorción, ideal para antes de entrenar.',
            'instructions' => '1. Licuar 50g de avena con 4 claras de huevo y 1 huevo entero. 2. Cocinar a fuego medio en una sartén antiadherente. 3. Servir con rodajas de plátano.',
            'preparation_min' => 10,
            'goal_type' => 'gain_muscle',
            'calories_total' => 450.00,
            'protein_g' => 30.00,
            'carbs_g' => 55.00,
            'fat_g' => 10.00,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $recipeSnack1 = Recipe::create([
            'category_id' => $catId,
            'name' => 'Batido Proteico Recuperador',
            'description' => 'Un batido post-entrenamiento rápido para estimular la síntesis de proteína.',
            'instructions' => 'Mezclar 1 cacito de proteína whey en agua o leche de almendras, añadir un plátano maduro y licuar con hielo.',
            'preparation_min' => 5,
            'goal_type' => 'gain_muscle',
            'calories_total' => 350.00,
            'protein_g' => 30.00,
            'carbs_g' => 45.00,
            'fat_g' => 5.00,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $recipeLunch = Recipe::create([
            'category_id' => $catId,
            'name' => 'Pechuga de Pollo con Arroz Jazmín y Brócoli',
            'description' => 'El almuerzo clásico de la vieja escuela del fitness. Limpio, saciante y lleno de nutrientes.',
            'instructions' => '1. Cocinar 150g de pechuga de pollo a la plancha con especias al gusto. 2. Hervir 80g de arroz jazmín. 3. Cocinar al vapor 100g de brócoli.',
            'preparation_min' => 20,
            'goal_type' => 'gain_muscle',
            'calories_total' => 650.00,
            'protein_g' => 45.00,
            'carbs_g' => 80.00,
            'fat_g' => 12.00,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $recipeSnack2 = Recipe::create([
            'category_id' => $catId,
            'name' => 'Yogur Griego con Nueces y Arándanos',
            'description' => 'Merienda saciante con grasas cardiosaludables y proteínas de absorción lenta (caseína).',
            'instructions' => 'Servir 200g de yogur griego natural desnatado, añadir 20g de nueces picadas y un puñado de arándanos frescos.',
            'preparation_min' => 5,
            'goal_type' => 'general',
            'calories_total' => 300.00,
            'protein_g' => 15.00,
            'carbs_g' => 20.00,
            'fat_g' => 15.00,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $recipeDinner = Recipe::create([
            'category_id' => $catId,
            'name' => 'Salmón a la Plancha con Patata al Horno',
            'description' => 'Cena rica en Omega-3 y micronutrientes, ideal para optimizar la recuperación hormonal nocturna.',
            'instructions' => '1. Hornear una patata mediana con sal y romero. 2. Sellar 150g de filete de salmón por ambos lados en una plancha caliente.',
            'preparation_min' => 25,
            'goal_type' => 'gain_muscle',
            'calories_total' => 550.00,
            'protein_g' => 40.00,
            'carbs_g' => 45.00,
            'fat_g' => 22.00,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        // 4. Create Exercise Categories & Exercises
        $exCatId = DB::table('exercise_categories')->insertGetId([
            'name' => 'Fuerza & Musculación',
            'description' => 'Ejercicios con peso libre, poleas y máquinas para desarrollo muscular.'
        ]);

        $exSquat = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Sentadilla con Barra Trasera',
            'description' => 'El rey de los ejercicios para el desarrollo de piernas y glúteos.',
            'instructions' => '1. Colocar barra sobre trapecios. 2. Bajar cadera rompiendo paralelo con rodillas hacia afuera. 3. Subir empujando el suelo.',
            'muscle_group' => 'Cuádriceps',
            'difficulty' => 'intermediate',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exPress = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Prensa de Piernas inclinada 45°',
            'description' => 'Excelente para sobrecargar el tren inferior de forma segura sin tensión lumbar.',
            'instructions' => '1. Apoyar pies al ancho de hombros. 2. Desactivar seguros y bajar plataforma de forma controlada. 3. Empujar plataforma sin bloquear rodillas.',
            'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exRdl = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Peso Muerto Rumano con Barra',
            'description' => 'Enfoque masivo en cadena posterior (isquiotibiales y glúteos).',
            'instructions' => '1. Sujetar barra frente a muslos. 2. Llevar cadera hacia atrás manteniendo espalda recta y rodillas ligeramente flexionadas. 3. Volver al inicio contrayendo glúteos.',
            'muscle_group' => 'Isquiotibiales',
            'difficulty' => 'intermediate',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exBench = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Press de Banca Plano con Barra',
            'description' => 'Ejercicio compuesto principal para el pectoral, hombro anterior y tríceps.',
            'instructions' => '1. Acostarse en banco plano, pies al suelo. 2. Retraer escápulas, bajar barra al pecho y empujar verticalmente.',
            'muscle_group' => 'Pectoral',
            'difficulty' => 'intermediate',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exPullups = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Dominadas Pronas (Pull-ups)',
            'description' => 'Gran ejercicio de tracción corporal para el desarrollo del dorsal ancho y bíceps.',
            'instructions' => '1. Colgarse de barra con agarre prono ancho. 2. Elevar el pecho hacia la barra contrayendo la espalda. 3. Bajar controlado.',
            'muscle_group' => 'Espalda',
            'difficulty' => 'advanced',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exMilitary = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Press Militar con Barra de Pie',
            'description' => 'Desarrollo de fuerza general del hombro y core.',
            'instructions' => '1. Sujetar barra a nivel de clavículas. 2. Contraer abdomen e impulsar la barra por encima de la cabeza.',
            'muscle_group' => 'Hombros',
            'difficulty' => 'intermediate',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exBicep = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Curl de Bíceps con Barra',
            'description' => 'Flexión de codo aislada para el desarrollo de los bíceps.',
            'instructions' => '1. Sujetar barra en supinación. 2. Flexionar codos subiendo la barra sin balancear el torso. 3. Bajar despacio.',
            'muscle_group' => 'Bíceps',
            'difficulty' => 'beginner',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $exLunges = Exercise::create([
            'category_id' => $exCatId,
            'name' => 'Zancadas con Mancuernas',
            'description' => 'Excelente ejercicio unilateral para cuadríceps, glúteos y estabilidad unilateral.',
            'instructions' => '1. Sujetar mancuernas a los lados. 2. Dar paso largo adelante y bajar cadera hasta que rodilla trasera roce el suelo. 3. Volver al inicio.',
            'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner',
            'requires_equipment' => 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        // 5. Create Routines
        $routineLegs = WorkoutRoutine::create([
            'name' => 'Pierna & Glúteo Avanzado',
            'description' => 'Enfocado en desarrollo de tren inferior con técnica RPE y cargas progresivas de alta intensidad.',
            'goal_type' => 'gain_muscle',
            'bmi_min' => 18.5,
            'bmi_max' => 29.9,
            'bmi_category' => 'all',
            'difficulty' => 'advanced',
            'duration_weeks' => 16,
            'days_per_week' => 3,
            'requires_gym' => 1,
            'is_active' => 1,
            'created_by' => $trainer->id,
        ]);

        $routineFullBody = WorkoutRoutine::create([
            'name' => 'Full Body Fuerza / HIIT',
            'description' => 'Combinación de movimientos multiarticulares pesados seguidos de acondicionamiento metabólico corto y explosivo.',
            'goal_type' => 'lose_weight',
            'bmi_min' => 20.0,
            'bmi_max' => 35.0,
            'bmi_category' => 'all',
            'difficulty' => 'intermediate',
            'duration_weeks' => 8,
            'days_per_week' => 4,
            'requires_gym' => 1,
            'is_active' => 1,
            'created_by' => $trainer->id,
        ]);

        $routinePower = WorkoutRoutine::create([
            'name' => 'Powerlifting 5/3/1 Simplificado',
            'description' => 'Plan estructurado para incrementar 1RM en Sentadilla, Press de Banca, Peso Muerto y Press Militar.',
            'goal_type' => 'gain_muscle',
            'bmi_min' => 22.0,
            'bmi_max' => 40.0,
            'bmi_category' => 'all',
            'difficulty' => 'advanced',
            'duration_weeks' => 12,
            'days_per_week' => 4,
            'requires_gym' => 1,
            'is_active' => 1,
            'created_by' => $trainer->id,
        ]);

        // 6. Create Routine Days and Exercises
        // Routine 1 (Pierna Avanzado)
        $day1 = RoutineDay::create(['routine_id' => $routineLegs->id, 'day_number' => 1, 'day_name' => 'Día 1: Enfoque Cuádriceps', 'focus_area' => 'Cuádriceps & Glúteos']);
        $day2 = RoutineDay::create(['routine_id' => $routineLegs->id, 'day_number' => 2, 'day_name' => 'Día 2: Enfoque Femoral', 'focus_area' => 'Isquiotibiales & Gemelos']);
        
        RoutineExercise::create(['routine_day_id' => $day1->id, 'exercise_id' => $exSquat->id, 'sets' => 4, 'reps' => '6-8 (RPE 8)', 'rest_seconds' => 120, 'order_index' => 1, 'notes' => 'Enfocarse en rango completo de movimiento.']);
        RoutineExercise::create(['routine_day_id' => $day1->id, 'exercise_id' => $exPress->id, 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 90, 'order_index' => 2, 'notes' => 'Bajar despacio controlado.']);
        RoutineExercise::create(['routine_day_id' => $day1->id, 'exercise_id' => $exLunges->id, 'sets' => 3, 'reps' => '12 pasos', 'rest_seconds' => 60, 'order_index' => 3, 'notes' => 'Cuerpo ligeramente inclinado adelante.']);
        
        RoutineExercise::create(['routine_day_id' => $day2->id, 'exercise_id' => $exRdl->id, 'sets' => 4, 'reps' => '8-10', 'rest_seconds' => 90, 'order_index' => 1, 'notes' => 'Mantener barra pegada a las piernas.']);

        // Routine 2 (Full Body)
        $fbDay1 = RoutineDay::create(['routine_id' => $routineFullBody->id, 'day_number' => 1, 'day_name' => 'Día 1: Empuje & Tracción', 'focus_area' => 'Torso y Fuerza General']);
        RoutineExercise::create(['routine_day_id' => $fbDay1->id, 'exercise_id' => $exBench->id, 'sets' => 4, 'reps' => '8', 'rest_seconds' => 90, 'order_index' => 1, 'notes' => 'Mantener tensión constante.']);
        RoutineExercise::create(['routine_day_id' => $fbDay1->id, 'exercise_id' => $exPullups->id, 'sets' => 4, 'reps' => 'Fallo - 1', 'rest_seconds' => 90, 'order_index' => 2, 'notes' => 'Usar banda de asistencia si es necesario.']);

        // Routine 3 (Powerlifting)
        $plDay1 = RoutineDay::create(['routine_id' => $routinePower->id, 'day_number' => 1, 'day_name' => 'Día 1: Sentadilla Primaria', 'focus_area' => 'Fuerza Máxima Cuádriceps']);
        RoutineExercise::create(['routine_day_id' => $plDay1->id, 'exercise_id' => $exSquat->id, 'sets' => 5, 'reps' => '5 (5/3/1)', 'rest_seconds' => 180, 'order_index' => 1, 'notes' => 'Descansar bien entre series.']);

        // 7. Create Meal Plans
        $mealPlanBulking = MealPlan::create([
            'name' => 'Volumen Limpio 2500 kcal',
            'description' => 'Plan hipercalórico moderado con alto aporte de carbohidratos complejos y grasas saludables para maximizar masa muscular.',
            'goal_type' => 'gain_muscle',
            'bmi_category' => 'all',
            'duration_weeks' => 16,
            'daily_calories' => 2500.00,
            'is_active' => 1,
        ]);

        $mealPlanCutting = MealPlan::create([
            'name' => 'Déficit Definición 1800 kcal',
            'description' => 'Plan hipocalórico alto en proteínas para preservar masa magra mientras se estimula la oxidación de grasa corporal.',
            'goal_type' => 'lose_weight',
            'bmi_category' => 'all',
            'duration_weeks' => 10,
            'daily_calories' => 1800.00,
            'is_active' => 1,
        ]);

        $mealPlanRecomp = MealPlan::create([
            'name' => 'Recomposición Corporal 2100 kcal',
            'description' => 'Plan isocalórico balanceado ideal para atletas intermedios que buscan perder grasa y ganar músculo simultáneamente.',
            'goal_type' => 'maintain',
            'bmi_category' => 'all',
            'duration_weeks' => 12,
            'daily_calories' => 2100.00,
            'is_active' => 1,
        ]);

        // Link Menu days to Meal Plans
        foreach ([$mealPlanBulking->id, $mealPlanCutting->id, $mealPlanRecomp->id] as $planId) {
            for ($day = 1; $day <= 3; $day++) {
                MealPlanDay::create([
                    'meal_plan_id' => $planId,
                    'day_number' => $day,
                    'breakfast_recipe_id' => $recipeBreakfast->id,
                    'snack1_recipe_id' => $recipeSnack1->id,
                    'lunch_recipe_id' => $recipeLunch->id,
                    'snack2_recipe_id' => $recipeSnack2->id,
                    'dinner_recipe_id' => $recipeDinner->id,
                ]);
            }
        }

        // 8. Create Clients (Members)
        $user1 = User::create([
            'email' => 'maria@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'member',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user1->id,
            'first_name' => 'María Inés',
            'last_name' => 'Silva',
            'phone' => '+34 655 444 333',
            'birth_date' => '1995-08-20',
            'gender' => 'female',
            'profile_photo' => 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop',
            'gym_id' => $gym->id,
        ]);
        BodyMeasurement::create([
            'user_id' => $user1->id,
            'weight_kg' => 64.5,
            'height_cm' => 165.0,
            'bmi' => 23.69,
            'bmi_category' => 'normal',
            'body_fat_pct' => 24.2,
            'muscle_mass_kg' => 44.0,
            'measured_at' => Carbon::now()->subWeeks(3),
            'createdAt' => Carbon::now()->subWeeks(3),
            'updatedAt' => Carbon::now()->subWeeks(3),
        ]);
        BodyMeasurement::create([
            'user_id' => $user1->id,
            'weight_kg' => 63.8,
            'height_cm' => 165.0,
            'bmi' => 23.43,
            'bmi_category' => 'normal',
            'body_fat_pct' => 23.5,
            'muscle_mass_kg' => 44.5,
            'measured_at' => Carbon::now()->subWeeks(2),
            'createdAt' => Carbon::now()->subWeeks(2),
            'updatedAt' => Carbon::now()->subWeeks(2),
        ]);
        BodyMeasurement::create([
            'user_id' => $user1->id,
            'weight_kg' => 63.0,
            'height_cm' => 165.0,
            'bmi' => 23.14,
            'bmi_category' => 'normal',
            'body_fat_pct' => 23.0,
            'muscle_mass_kg' => 45.0,
            'measured_at' => Carbon::now()->subWeeks(1),
            'createdAt' => Carbon::now()->subWeeks(1),
            'updatedAt' => Carbon::now()->subWeeks(1),
        ]);
        BodyMeasurement::create([
            'user_id' => $user1->id,
            'weight_kg' => 62.5,
            'height_cm' => 165.0,
            'bmi' => 22.96,
            'bmi_category' => 'normal',
            'body_fat_pct' => 22.4,
            'muscle_mass_kg' => 45.2,
            'measured_at' => Carbon::now()->subDays(2),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserAssignedRoutine::create([
            'user_id' => $user1->id,
            'routine_id' => $routineLegs->id,
            'assigned_by' => $trainer->id,
            'start_date' => Carbon::now()->subWeeks(12),
            'end_date' => Carbon::now()->addWeeks(4),
            'is_active' => 1,
        ]);
        UserMealPlan::create([
            'user_id' => $user1->id,
            'meal_plan_id' => $mealPlanBulking->id,
            'assigned_by' => $trainer->id,
            'start_date' => Carbon::now()->subWeeks(12),
            'end_date' => Carbon::now()->addWeeks(4),
            'is_active' => 1,
        ]);

        // Client 2: Juan
        $user2 = User::create([
            'email' => 'juan@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'member',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user2->id,
            'first_name' => 'Juan Pablo',
            'last_name' => 'Torres',
            'phone' => '+34 677 888 999',
            'birth_date' => '1992-03-12',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=100&auto=format&fit=crop',
            'gym_id' => $gym->id,
        ]);
        BodyMeasurement::create([
            'user_id' => $user2->id,
            'weight_kg' => 87.0,
            'height_cm' => 178.0,
            'bmi' => 27.46,
            'bmi_category' => 'overweight',
            'body_fat_pct' => 21.0,
            'muscle_mass_kg' => 61.5,
            'measured_at' => Carbon::now()->subWeeks(3),
            'createdAt' => Carbon::now()->subWeeks(3),
            'updatedAt' => Carbon::now()->subWeeks(3),
        ]);
        BodyMeasurement::create([
            'user_id' => $user2->id,
            'weight_kg' => 85.8,
            'height_cm' => 178.0,
            'bmi' => 27.08,
            'bmi_category' => 'overweight',
            'body_fat_pct' => 20.1,
            'muscle_mass_kg' => 62.8,
            'measured_at' => Carbon::now()->subWeeks(2),
            'createdAt' => Carbon::now()->subWeeks(2),
            'updatedAt' => Carbon::now()->subWeeks(2),
        ]);
        BodyMeasurement::create([
            'user_id' => $user2->id,
            'weight_kg' => 85.0,
            'height_cm' => 178.0,
            'bmi' => 26.83,
            'bmi_category' => 'overweight',
            'body_fat_pct' => 19.5,
            'muscle_mass_kg' => 63.2,
            'measured_at' => Carbon::now()->subWeeks(1),
            'createdAt' => Carbon::now()->subWeeks(1),
            'updatedAt' => Carbon::now()->subWeeks(1),
        ]);
        BodyMeasurement::create([
            'user_id' => $user2->id,
            'weight_kg' => 84.2,
            'height_cm' => 178.0,
            'bmi' => 26.57,
            'bmi_category' => 'overweight',
            'body_fat_pct' => 18.9,
            'muscle_mass_kg' => 64.0,
            'measured_at' => Carbon::now()->subDays(5),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserAssignedRoutine::create([
            'user_id' => $user2->id,
            'routine_id' => $routineFullBody->id,
            'assigned_by' => $trainer->id,
            'start_date' => Carbon::now()->subWeeks(4),
            'end_date' => Carbon::now()->addWeeks(4),
            'is_active' => 1,
        ]);
        UserMealPlan::create([
            'user_id' => $user2->id,
            'meal_plan_id' => $mealPlanCutting->id,
            'assigned_by' => $trainer->id,
            'start_date' => Carbon::now()->subWeeks(4),
            'end_date' => Carbon::now()->addWeeks(6),
            'is_active' => 1,
        ]);

        // Client 3: Sofia
        $user3 = User::create([
            'email' => 'sofia@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'member',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user3->id,
            'first_name' => 'Sofía',
            'last_name' => 'Vergara G.',
            'phone' => '+34 688 555 444',
            'birth_date' => '1997-11-25',
            'gender' => 'female',
            'profile_photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=100&auto=format&fit=crop',
            'gym_id' => $gym->id,
        ]);
        BodyMeasurement::create([
            'user_id' => $user3->id,
            'weight_kg' => 59.5,
            'height_cm' => 168.0,
            'bmi' => 21.08,
            'bmi_category' => 'normal',
            'body_fat_pct' => 25.5,
            'muscle_mass_kg' => 40.5,
            'measured_at' => Carbon::now()->subWeeks(2),
            'createdAt' => Carbon::now()->subWeeks(2),
            'updatedAt' => Carbon::now()->subWeeks(2),
        ]);
        BodyMeasurement::create([
            'user_id' => $user3->id,
            'weight_kg' => 58.0,
            'height_cm' => 168.0,
            'bmi' => 20.55,
            'bmi_category' => 'normal',
            'body_fat_pct' => 24.1,
            'muscle_mass_kg' => 41.5,
            'measured_at' => Carbon::now()->subDays(10),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserMealPlan::create([
            'user_id' => $user3->id,
            'meal_plan_id' => $mealPlanRecomp->id,
            'assigned_by' => $trainer->id,
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now()->addWeeks(12),
            'is_active' => 1,
        ]);

        // Client 4: Mateo (Inactive user test)
        $user4 = User::create([
            'email' => 'mateo@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'member',
            'is_active' => 0, // Set to 0 to test the inactive filtering tab!
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user4->id,
            'first_name' => 'Mateo',
            'last_name' => 'Mendoza',
            'phone' => '+34 699 000 111',
            'birth_date' => '1988-09-02',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop',
            'gym_id' => $gym->id,
        ]);
        BodyMeasurement::create([
            'user_id' => $user4->id,
            'weight_kg' => 93.5,
            'height_cm' => 182.0,
            'bmi' => 28.23,
            'bmi_category' => 'overweight',
            'body_fat_pct' => 17.5,
            'muscle_mass_kg' => 70.0,
            'measured_at' => Carbon::now()->subWeeks(2),
            'createdAt' => Carbon::now()->subWeeks(2),
            'updatedAt' => Carbon::now()->subWeeks(2),
        ]);
        BodyMeasurement::create([
            'user_id' => $user4->id,
            'weight_kg' => 92.0,
            'height_cm' => 182.0,
            'bmi' => 27.77,
            'bmi_category' => 'overweight',
            'body_fat_pct' => 16.5,
            'muscle_mass_kg' => 71.2,
            'measured_at' => Carbon::now()->subDays(1),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserAssignedRoutine::create([
            'user_id' => $user4->id,
            'routine_id' => $routinePower->id,
            'assigned_by' => $trainer->id,
            'start_date' => Carbon::now()->subWeeks(2),
            'end_date' => Carbon::now()->addWeeks(10),
            'is_active' => 1,
        ]);

        // 9. Create Workout Sessions for attendance statistics (past week)
        $startOfWeek = Carbon::now()->startOfWeek();
        
        for ($i = 0; $i < 8; $i++) {
            WorkoutSession::create([
                'user_id' => $user1->id,
                'session_date' => $startOfWeek->copy()->addDays(0), // Monday
                'started_at' => $startOfWeek->copy()->addDays(0)->hour(8 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(0)->hour(9 + $i),
                'duration_minutes' => 60,
                'calories_burned' => 450,
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            WorkoutSession::create([
                'user_id' => $user2->id,
                'session_date' => $startOfWeek->copy()->addDays(1), // Tuesday
                'started_at' => $startOfWeek->copy()->addDays(1)->hour(7 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(1)->hour(8 + $i),
                'duration_minutes' => 65,
                'calories_burned' => 500,
            ]);
        }

        for ($i = 0; $i < 9; $i++) {
            WorkoutSession::create([
                'user_id' => $user3->id,
                'session_date' => $startOfWeek->copy()->addDays(2), // Wednesday
                'started_at' => $startOfWeek->copy()->addDays(2)->hour(9 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(2)->hour(10 + $i),
                'duration_minutes' => 55,
                'calories_burned' => 400,
            ]);
        }

        for ($i = 0; $i < 15; $i++) {
            WorkoutSession::create([
                'user_id' => $user4->id,
                'session_date' => $startOfWeek->copy()->addDays(3), // Thursday
                'started_at' => $startOfWeek->copy()->addDays(3)->hour(8 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(3)->hour(9 + $i),
                'duration_minutes' => 60,
                'calories_burned' => 480,
            ]);
        }

        for ($i = 0; $i < 11; $i++) {
            WorkoutSession::create([
                'user_id' => $user1->id,
                'session_date' => $startOfWeek->copy()->addDays(4), // Friday
                'started_at' => $startOfWeek->copy()->addDays(4)->hour(7 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(4)->hour(8 + $i),
                'duration_minutes' => 70,
                'calories_burned' => 520,
            ]);
        }

        for ($i = 0; $i < 6; $i++) {
            WorkoutSession::create([
                'user_id' => $user2->id,
                'session_date' => $startOfWeek->copy()->addDays(5), // Saturday
                'started_at' => $startOfWeek->copy()->addDays(5)->hour(9 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(5)->hour(10 + $i),
                'duration_minutes' => 60,
                'calories_burned' => 450,
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            WorkoutSession::create([
                'user_id' => $user4->id,
                'session_date' => $startOfWeek->copy()->addDays(6), // Sunday
                'started_at' => $startOfWeek->copy()->addDays(6)->hour(10 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(6)->hour(11 + $i),
                'duration_minutes' => 50,
                'calories_burned' => 380,
            ]);
        }
    }
}
