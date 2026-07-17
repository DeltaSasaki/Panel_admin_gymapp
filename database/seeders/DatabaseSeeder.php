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
use App\Models\MembershipPlan;
use App\Models\UserMembership;
use App\Models\ProductCategory;
use App\Models\InventoryProduct;
use App\Models\InventoryMovement;
use App\Models\ProductSale;
use App\Models\SaleItem;
use App\Models\Equipment;
use App\Models\Ingredient;
use App\Models\UserGoal;
use App\Models\UserAchievement;
use App\Models\Notification;

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
        DB::table('users')->truncate();
        DB::table('user_memberships')->truncate();
        DB::table('membership_plans')->truncate();
        DB::table('product_categories')->truncate();
        DB::table('inventory_products')->truncate();
        DB::table('inventory_movements')->truncate();
        DB::table('product_sales')->truncate();
        DB::table('sale_items')->truncate();
        DB::table('equipment')->truncate();
        DB::table('ingredients')->truncate();
        DB::table('user_goals')->truncate();
        DB::table('user_achievements')->truncate();
        DB::table('notifications')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ==========================================
        // 1. CREATE GYM 1 (GymFlow HQ) & GYM 2 (PowerHouse Studio)
        // ==========================================
        $gym1 = Gym::create([
            'name' => 'GymFlow HQ',
            'address' => 'Av. de los Deportes 450, Madrid',
            'phone' => '+34 912 345 678',
            'email' => 'info@gymflowhq.com',
            'logo_url' => null,
            'timezone' => 'Europe/Madrid',
            'is_active' => 1,
        ]);

        $gym2 = Gym::create([
            'name' => 'PowerHouse Studio',
            'address' => 'Calle Gran Vía 88, Barcelona',
            'phone' => '+34 931 999 888',
            'email' => 'contact@powerhousestudio.com',
            'logo_url' => null,
            'timezone' => 'Europe/Madrid',
            'is_active' => 1,
        ]);

        // ==========================================
        // 2. SEED GYM 1 (GymFlow HQ) DATA
        // ==========================================

        // Trainer Gym 1
        $trainerUser1 = User::create([
            'gym_id' => $gym1->id,
            'email' => 'coach@gymflow.com',
            'password_hash' => Hash::make('password'),
            'role' => 'trainer',
            'is_active' => 1,
            'email_verified' => 1,
        ]);

        UserProfile::create([
            'user_id' => $trainerUser1->id,
            'first_name' => 'Carlos',
            'last_name' => 'Ruiz',
            'phone' => '+34 600 111 222',
            'birth_date' => '1990-05-15',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop',
        ]);

        $trainer1 = Trainer::create([
            'user_id' => $trainerUser1->id,
            'gym_id' => $gym1->id,
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

        // Gym 1 Admin (Owner)
        $adminUser1 = User::create([
            'gym_id' => $gym1->id,
            'email' => 'admin@gymflow.com',
            'password_hash' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $adminUser1->id,
            'first_name' => 'Geraldo',
            'last_name' => 'Mendoza (Owner)',
            'phone' => '+34 600 222 333',
            'birth_date' => '1985-04-12',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=150&auto=format&fit=crop',
        ]);

        // Super Admin (Support Team)
        $superAdminUser = User::create([
            'gym_id' => $gym1->id,
            'email' => 'support@gymflow.com',
            'password_hash' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $superAdminUser->id,
            'first_name' => 'Soporte Técnica',
            'last_name' => 'GymFlow',
            'phone' => '+34 600 999 999',
            'birth_date' => '1998-01-01',
            'gender' => 'other',
            'profile_photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=150&auto=format&fit=crop',
        ]);

        // Recipe Categories and Recipes - Gym 1
        $rcatId1 = DB::table('recipe_categories')->insertGetId([
            'gym_id' => $gym1->id,
            'name' => 'Nutrición Deportiva G1'
        ]);

        $recipeBreakfast1 = Recipe::create([
            'gym_id' => $gym1->id,
            'category_id' => $rcatId1,
            'name' => 'Tortilla de Avena y Claras de Huevo',
            'description' => 'Desayuno pre-entrenamiento de absorción limpia.',
            'instructions' => '1. Mezclar claras con avena. 2. Cocinar a fuego lento.',
            'preparation_min' => 10,
            'goal_type' => 'gain_muscle',
            'calories_total' => 450.00,
            'protein_g' => 30.00,
            'carbs_g' => 55.00,
            'fat_g' => 10.00,
        ]);

        $recipeLunch1 = Recipe::create([
            'gym_id' => $gym1->id,
            'category_id' => $rcatId1,
            'name' => 'Pollo con Arroz Jazmín',
            'description' => 'El almuerzo de los campeones.',
            'instructions' => 'Cocinar 150g de pechuga con 80g de arroz.',
            'preparation_min' => 20,
            'goal_type' => 'gain_muscle',
            'calories_total' => 600.00,
            'protein_g' => 45.00,
            'carbs_g' => 70.00,
            'fat_g' => 8.00,
        ]);

        // Exercise Categories & Exercises - Gym 1
        $exCatId1 = DB::table('exercise_categories')->insertGetId([
            'gym_id' => $gym1->id,
            'name' => 'Fuerza & Musculación G1',
            'description' => 'Ejercicios con pesas y barras libres de GymFlow.'
        ]);

        $exSquat1 = Exercise::create([
            'gym_id' => $gym1->id,
            'category_id' => $exCatId1,
            'name' => 'Sentadilla con Barra Trasera',
            'description' => 'Sentadilla clásica.',
            'muscle_group' => 'Cuádriceps',
            'difficulty' => 'intermediate',
            'requires_equipment' => 1,
        ]);

        $exPress1 = Exercise::create([
            'gym_id' => $gym1->id,
            'category_id' => $exCatId1,
            'name' => 'Prensa de Piernas 45°',
            'description' => 'Prensa pesada.',
            'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner',
            'requires_equipment' => 1,
        ]);

        // Workout Routines - Gym 1
        $routineLegs1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id,
            'name' => 'Pierna & Glúteo Avanzado G1',
            'description' => 'Plan de alta intensidad RPE.',
            'goal_type' => 'gain_muscle',
            'difficulty' => 'advanced',
            'duration_weeks' => 12,
            'days_per_week' => 2,
            'requires_gym' => 1,
            'is_active' => 1,
            'created_by' => $trainer1->id,
        ]);

        $day1_g1 = RoutineDay::create(['routine_id' => $routineLegs1->id, 'day_number' => 1, 'day_name' => 'Día 1: Fuerza Cuádriceps', 'focus_area' => 'Piernas']);
        $day2_g1 = RoutineDay::create(['routine_id' => $routineLegs1->id, 'day_number' => 2, 'day_name' => 'Día 2: Auxiliares de Pierna', 'focus_area' => 'Isquiotibiales']);

        RoutineExercise::create(['routine_day_id' => $day1_g1->id, 'exercise_id' => $exSquat1->id, 'sets' => 4, 'reps' => '6-8', 'rest_seconds' => 120, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $day2_g1->id, 'exercise_id' => $exPress1->id, 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 90, 'order_index' => 1]);

        // Meal Plans - Gym 1
        $mealPlanBulking1 = MealPlan::create([
            'gym_id' => $gym1->id,
            'name' => 'Volumen G1 2500 kcal',
            'description' => 'Plan hipercalórico base para ganar masa muscular.',
            'goal_type' => 'gain_muscle',
            'duration_weeks' => 12,
            'daily_calories' => 2500.00,
            'is_active' => 1,
        ]);

        for ($day = 1; $day <= 2; $day++) {
            MealPlanDay::create([
                'meal_plan_id' => $mealPlanBulking1->id,
                'day_number' => $day,
                'breakfast_recipe_id' => $recipeBreakfast1->id,
                'lunch_recipe_id' => $recipeLunch1->id,
            ]);
        }

        // Members - Gym 1
        $user1 = User::create([
            'gym_id' => $gym1->id,
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
        ]);
        BodyMeasurement::create([
            'user_id' => $user1->id,
            'weight_kg' => 64.0,
            'height_cm' => 165.0,
            'bmi' => 23.51,
            'bmi_category' => 'normal',
            'measured_at' => Carbon::now()->subWeeks(1),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserAssignedRoutine::create([
            'user_id' => $user1->id,
            'routine_id' => $routineLegs1->id,
            'assigned_by' => $trainer1->id,
            'start_date' => Carbon::now()->subWeeks(2),
            'is_active' => 1,
        ]);
        UserMealPlan::create([
            'user_id' => $user1->id,
            'meal_plan_id' => $mealPlanBulking1->id,
            'assigned_by' => $trainer1->id,
            'start_date' => Carbon::now()->subWeeks(2),
            'is_active' => 1,
        ]);

        // Client 2 (Juan) - Gym 1
        $user2 = User::create([
            'gym_id' => $gym1->id,
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
        ]);
        BodyMeasurement::create([
            'user_id' => $user2->id,
            'weight_kg' => 85.0,
            'height_cm' => 178.0,
            'bmi' => 26.83,
            'bmi_category' => 'overweight',
            'measured_at' => Carbon::now()->subWeeks(1),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        // Client 4 (Mateo Inactive) - Gym 1
        $user4 = User::create([
            'gym_id' => $gym1->id,
            'email' => 'mateo@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'member',
            'is_active' => 0,
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
        ]);


        // ==========================================
        // 3. SEED GYM 2 (PowerHouse Studio) DATA
        // ==========================================

        // Gym 2 Admin (Owner)
        $adminUser2 = User::create([
            'gym_id' => $gym2->id,
            'email' => 'admin2@powerhouse.com',
            'password_hash' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $adminUser2->id,
            'first_name' => 'Eduardo',
            'last_name' => 'Valenzuela',
            'phone' => '+34 622 333 444',
            'birth_date' => '1980-08-30',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=150&auto=format&fit=crop',
        ]);

        // Gym 2 Super Admin (Support Team)
        $superAdminUser2 = User::create([
            'gym_id' => $gym2->id,
            'email' => 'support2@powerhouse.com',
            'password_hash' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $superAdminUser2->id,
            'first_name' => 'Soporte Técnica 2',
            'last_name' => 'PowerHouse',
            'phone' => '+34 600 888 888',
            'birth_date' => '1998-01-01',
            'gender' => 'other',
            'profile_photo' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=150&auto=format&fit=crop',
        ]);
        $trainerUser2 = User::create([
            'gym_id' => $gym2->id,
            'email' => 'coach2@powerhouse.com',
            'password_hash' => Hash::make('password'),
            'role' => 'trainer',
            'is_active' => 1,
            'email_verified' => 1,
        ]);

        UserProfile::create([
            'user_id' => $trainerUser2->id,
            'first_name' => 'Laura',
            'last_name' => 'Blanco',
            'phone' => '+34 611 222 333',
            'birth_date' => '1993-10-12',
            'gender' => 'female',
            'profile_photo' => 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop',
        ]);

        $trainer2 = Trainer::create([
            'user_id' => $trainerUser2->id,
            'gym_id' => $gym2->id,
            'first_name' => 'Laura',
            'last_name' => 'Blanco',
            'email' => 'coach2@powerhouse.com',
            'phone' => '+34 611 222 333',
            'specialty' => 'Entrenamiento de Resistencia y Funcional',
            'certification' => 'FIBO-CPT, Kettlebell Trainer L2',
            'experience_years' => 5,
            'photo_url' => 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop',
            'bio' => 'Comprometida con la salud y resistencia de larga duración.',
            'is_active' => 1,
            'hire_date' => '2023-03-15',
            'salary' => 2200.00,
        ]);

        // Recipe Categories and Recipes - Gym 2
        $rcatId2 = DB::table('recipe_categories')->insertGetId([
            'gym_id' => $gym2->id,
            'name' => 'Alimentación Saludable G2'
        ]);

        $recipeBreakfast2 = Recipe::create([
            'gym_id' => $gym2->id,
            'category_id' => $rcatId2,
            'name' => 'Ensalada de Atún Proteica',
            'description' => 'Comida baja en carbos y rica en grasas saludables.',
            'instructions' => 'Mezclar atún con aguacate y rúcula.',
            'preparation_min' => 10,
            'goal_type' => 'lose_weight',
            'calories_total' => 380.00,
            'protein_g' => 35.00,
            'carbs_g' => 10.00,
            'fat_g' => 22.00,
        ]);

        // Exercise Categories & Exercises - Gym 2
        $exCatId2 = DB::table('exercise_categories')->insertGetId([
            'gym_id' => $gym2->id,
            'name' => 'Acondicionamiento G2',
            'description' => 'Ejercicios corporales y funcionales de PowerHouse.'
        ]);

        $exSquat2 = Exercise::create([
            'gym_id' => $gym2->id,
            'category_id' => $exCatId2,
            'name' => 'Sentadilla Goblet',
            'description' => 'Sentadilla con mancuerna al pecho.',
            'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner',
            'requires_equipment' => 1,
        ]);

        // Workout Routines - Gym 2
        $routineLegs2 = WorkoutRoutine::create([
            'gym_id' => $gym2->id,
            'name' => 'Hipertrofia Funcional G2',
            'description' => 'Plan de desarrollo muscular integral.',
            'goal_type' => 'gain_muscle',
            'difficulty' => 'intermediate',
            'duration_weeks' => 8,
            'days_per_week' => 1,
            'requires_gym' => 1,
            'is_active' => 1,
            'created_by' => $trainer2->id,
        ]);

        $day1_g2 = RoutineDay::create(['routine_id' => $routineLegs2->id, 'day_number' => 1, 'day_name' => 'Día 1: Fuerza Corporal', 'focus_area' => 'Piernas']);
        RoutineExercise::create(['routine_day_id' => $day1_g2->id, 'exercise_id' => $exSquat2->id, 'sets' => 3, 'reps' => '12', 'rest_seconds' => 60, 'order_index' => 1]);

        // Meal Plans - Gym 2
        $mealPlanBulking2 = MealPlan::create([
            'gym_id' => $gym2->id,
            'name' => 'Keto Adaptada G2 2000 kcal',
            'description' => 'Plan nutricional bajo en carbohidratos.',
            'goal_type' => 'lose_weight',
            'duration_weeks' => 8,
            'daily_calories' => 2000.00,
            'is_active' => 1,
        ]);

        MealPlanDay::create([
            'meal_plan_id' => $mealPlanBulking2->id,
            'day_number' => 1,
            'breakfast_recipe_id' => $recipeBreakfast2->id,
        ]);

        // Members - Gym 2
        $user3 = User::create([
            'gym_id' => $gym2->id,
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
        ]);
        BodyMeasurement::create([
            'user_id' => $user3->id,
            'weight_kg' => 58.0,
            'height_cm' => 168.0,
            'bmi' => 20.55,
            'bmi_category' => 'normal',
            'measured_at' => Carbon::now()->subWeeks(1),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserMealPlan::create([
            'user_id' => $user3->id,
            'meal_plan_id' => $mealPlanBulking2->id,
            'assigned_by' => $trainer2->id,
            'start_date' => Carbon::now()->subDays(2),
            'is_active' => 1,
        ]);

        // Member 5 (Andres) - Gym 2
        $user5 = User::create([
            'gym_id' => $gym2->id,
            'email' => 'andres@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'member',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user5->id,
            'first_name' => 'Andrés',
            'last_name' => 'Silva',
            'phone' => '+34 600 999 888',
            'birth_date' => '1994-01-20',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop',
        ]);
        BodyMeasurement::create([
            'user_id' => $user5->id,
            'weight_kg' => 78.5,
            'height_cm' => 176.0,
            'bmi' => 25.34,
            'bmi_category' => 'overweight',
            'measured_at' => Carbon::now()->subDays(1),
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);
        UserAssignedRoutine::create([
            'user_id' => $user5->id,
            'routine_id' => $routineLegs2->id,
            'assigned_by' => $trainer2->id,
            'start_date' => Carbon::now()->subDays(1),
            'is_active' => 1,
        ]);

        // ==========================================
        // 4. WORKOUT SESSIONS FOR ATTENDANCE STATS
        // ==========================================
        $startOfWeek = Carbon::now()->startOfWeek();

        // Attendance stats for Gym 1 (User 1 & User 2)
        for ($i = 0; $i < 6; $i++) {
            WorkoutSession::create([
                'user_id' => $user1->id,
                'routine_id' => $routineLegs1->id,
                'session_date' => $startOfWeek->copy()->addDays(0), // Monday
                'started_at' => $startOfWeek->copy()->addDays(0)->hour(8 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(0)->hour(9 + $i),
                'duration_minutes' => 60,
                'calories_burned' => 400.00,
            ]);
        }

        // Attendance stats for Gym 2 (User 3 & User 5)
        for ($i = 0; $i < 5; $i++) {
            WorkoutSession::create([
                'user_id' => $user5->id,
                'routine_id' => $routineLegs2->id,
                'session_date' => $startOfWeek->copy()->addDays(1), // Tuesday
                'started_at' => $startOfWeek->copy()->addDays(1)->hour(9 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(1)->hour(10 + $i),
                'duration_minutes' => 50,
                'calories_burned' => 350.00,
            ]);
        }

        // ==========================================
        // 5. MEMBERSHIP PLANS AND USER MEMBERSHIPS
        // ==========================================

        // Plans for Gym 1
        $planVipG1 = MembershipPlan::create([
            'gym_id' => $gym1->id,
            'name' => 'Plan VIP Mensual',
            'description' => 'Acceso libre y entrenador personalizado.',
            'duration_days' => 30,
            'price' => 50.00,
            'currency' => 'USD',
            'includes_trainer' => 1,
            'is_active' => 1,
        ]);

        $planBasicG1 = MembershipPlan::create([
            'gym_id' => $gym1->id,
            'name' => 'Plan Básico Mensual',
            'description' => 'Acceso libre a máquinas de musculación.',
            'duration_days' => 30,
            'price' => 30.00,
            'currency' => 'USD',
            'includes_trainer' => 0,
            'is_active' => 1,
        ]);

        // Memberships for Gym 1 members
        UserMembership::create([
            'user_id' => $user1->id,
            'gym_id' => $gym1->id,
            'plan_id' => $planVipG1->id,
            'start_date' => Carbon::now()->subDays(15),
            'end_date' => Carbon::now()->addDays(15),
            'status' => 'active',
            'payment_status' => 'paid',
            'notes' => 'Atleta muy disciplinada.',
        ]);

        UserMembership::create([
            'user_id' => $user2->id,
            'gym_id' => $gym1->id,
            'plan_id' => $planBasicG1->id,
            'start_date' => Carbon::now()->subDays(25),
            'end_date' => Carbon::now()->addDays(5),
            'status' => 'active',
            'payment_status' => 'pending',
            'notes' => 'Pendiente pago del mes.',
        ]);

        // Plans for Gym 2
        $planPowerG2 = MembershipPlan::create([
            'gym_id' => $gym2->id,
            'name' => 'Power Pass Mensual',
            'description' => 'Pase completo con clases dirigidas.',
            'duration_days' => 30,
            'price' => 60.00,
            'currency' => 'USD',
            'includes_trainer' => 1,
            'is_active' => 1,
        ]);

        // Memberships for Gym 2 members
        UserMembership::create([
            'user_id' => $user3->id,
            'gym_id' => $gym2->id,
            'plan_id' => $planPowerG2->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
            'status' => 'active',
            'payment_status' => 'paid',
        ]);

        UserMembership::create([
            'user_id' => $user5->id,
            'gym_id' => $gym2->id,
            'plan_id' => $planPowerG2->id,
            'start_date' => Carbon::now()->subDays(35),
            'end_date' => Carbon::now()->subDays(5),
            'status' => 'expired',
            'payment_status' => 'overdue',
            'notes' => 'Recordatorio de pago enviado.',
        ]);

        // ==========================================
        // 6. INVENTORY PRODUCTS & CATEGORIES
        // ==========================================
        
        // Gym 1
        $catAccG1 = ProductCategory::create(['gym_id' => $gym1->id, 'name' => 'Accesorios', 'description' => 'Shakers, straps y vendas']);
        $catSupG1 = ProductCategory::create(['gym_id' => $gym1->id, 'name' => 'Suplementos', 'description' => 'Proteínas y creatinas']);
        $catBebG1 = ProductCategory::create(['gym_id' => $gym1->id, 'name' => 'Bebidas', 'description' => 'Agua e hidratantes']);

        $shaker = InventoryProduct::create([
            'gym_id' => $gym1->id, 'category_id' => $catAccG1->id, 'name' => 'Vaso Mezclador 500ml',
            'description' => 'Shaker clásico hermético', 'price' => 10.00, 'cost_price' => 4.00,
            'stock_quantity' => 15, 'min_stock' => 3, 'is_available' => 1
        ]);
        $whey = InventoryProduct::create([
            'gym_id' => $gym1->id, 'category_id' => $catSupG1->id, 'name' => 'Whey Protein 1kg (Fresa)',
            'description' => 'Concentrado de suero de leche de alta calidad', 'price' => 45.00, 'cost_price' => 28.00,
            'stock_quantity' => 8, 'min_stock' => 2, 'is_available' => 1
        ]);
        $bar = InventoryProduct::create([
            'gym_id' => $gym1->id, 'category_id' => $catSupG1->id, 'name' => 'Barra de Proteínas 60g',
            'description' => 'Aperitivo con 20g de proteína', 'price' => 3.50, 'cost_price' => 1.50,
            'stock_quantity' => 2, 'min_stock' => 5, 'is_available' => 1 // Understocked!
        ]);

        // Gym 2
        $catSupG2 = ProductCategory::create(['gym_id' => $gym2->id, 'name' => 'Suplementación G2', 'description' => 'Suplementos deportivos']);
        $catBebG2 = ProductCategory::create(['gym_id' => $gym2->id, 'name' => 'Bebidas G2', 'description' => 'Hidratantes y energizantes']);

        $iso = InventoryProduct::create([
            'gym_id' => $gym2->id, 'category_id' => $catSupG2->id, 'name' => 'Iso Protein 900g (Vainilla)',
            'description' => 'Proteína aislada premium', 'price' => 55.00, 'cost_price' => 35.00,
            'stock_quantity' => 12, 'min_stock' => 3, 'is_available' => 1
        ]);

        // ==========================================
        // 7. GYM EQUIPMENT
        // ==========================================
        
        // Gym 1
        Equipment::create(['gym_id' => $gym1->id, 'name' => 'Cinta de Correr Pro Series', 'description' => 'Cinta de correr motorizada Matrix Pro', 'requires_gym' => 1]);
        Equipment::create(['gym_id' => $gym1->id, 'name' => 'Prensa de Piernas 45° Matrix', 'description' => 'Prensa de piernas Matrix 45 grados', 'requires_gym' => 1]);
        Equipment::create(['gym_id' => $gym1->id, 'name' => 'Rack de Sentadillas Smith', 'description' => 'Soporte rack Smith multipower', 'requires_gym' => 1]);

        // Gym 2
        Equipment::create(['gym_id' => $gym2->id, 'name' => 'Bicicleta de Spinning Matrix', 'description' => 'Bicicleta estática indoor', 'requires_gym' => 1]);
        Equipment::create(['gym_id' => $gym2->id, 'name' => 'Banco de Pecho Plano Matrix', 'description' => 'Banco plano ajustable musculación', 'requires_gym' => 1]);

        // ==========================================
        // 8. RAW INGREDIENTS
        // ==========================================
        
        // Gym 1
        Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Pechuga de Pollo', 'protein_g' => 23.00, 'carbs_g' => 0.00, 'fat_g' => 2.50, 'calories_per_100g' => 120.00, 'unit' => 'g']);
        Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Arroz Blanco Cocido', 'protein_g' => 2.70, 'carbs_g' => 28.00, 'fat_g' => 0.30, 'calories_per_100g' => 130.00, 'unit' => 'g']);
        Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Huevo Entero', 'protein_g' => 6.00, 'carbs_g' => 0.60, 'fat_g' => 5.00, 'calories_per_100g' => 70.00, 'unit' => 'unit']);

        // Gym 2
        Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Atún en lata al natural', 'protein_g' => 25.00, 'carbs_g' => 0.00, 'fat_g' => 1.00, 'calories_per_100g' => 110.00, 'unit' => 'unit']);
        Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Aguacate Maduro', 'protein_g' => 2.00, 'carbs_g' => 9.00, 'fat_g' => 15.00, 'calories_per_100g' => 160.00, 'unit' => 'unit']);

        // ==========================================
        // 9. CLIENT GOALS & ACHIEVEMENTS
        // ==========================================
        
        // Gym 1
        UserGoal::create(['user_id' => $user1->id, 'goal_type' => 'lose_weight', 'target_weight' => 60.0, 'target_date' => Carbon::now()->addWeeks(6), 'is_active' => 1]);
        UserAchievement::create(['user_id' => $user1->id, 'achievement_type' => 'first_workout', 'description' => 'Completaste tu primera sesión de entrenamiento.', 'achieved_at' => Carbon::now()->subWeeks(2)]);

        // Gym 2
        UserGoal::create(['user_id' => $user5->id, 'goal_type' => 'gain_muscle', 'target_weight' => 82.0, 'target_date' => Carbon::now()->addWeeks(8), 'is_active' => 1]);
        UserAchievement::create(['user_id' => $user5->id, 'achievement_type' => '10k_calories', 'description' => 'Quemaste más de 10,000 kcal en sesiones registradas.', 'achieved_at' => Carbon::now()->subDays(2)]);
    }
}
