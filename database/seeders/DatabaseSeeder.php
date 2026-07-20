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
use App\Models\SaasSubscriptionPlan;
use App\Models\AttendanceLog;
use App\Models\GymClass;
use App\Models\ClassSchedule;
use App\Models\ClassBooking;
use App\Models\PromoCode;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Models\AchievementDefinition;
use App\Models\UserGamificationStat;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks for clean seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('saas_subscription_plans')->truncate();
        DB::table('gyms')->truncate();
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
        DB::table('attendance_logs')->truncate();
        DB::table('gym_classes')->truncate();
        DB::table('class_schedules')->truncate();
        DB::table('class_bookings')->truncate();
        DB::table('promo_codes')->truncate();
        DB::table('challenges')->truncate();
        DB::table('user_challenges')->truncate();
        DB::table('achievement_definitions')->truncate();
        DB::table('user_gamification_stats')->truncate();
        DB::table('saas_plan_modules')->truncate();
        DB::table('recipe_ingredients')->truncate();
        DB::table('exercise_equipment')->truncate();
        DB::table('session_exercises')->truncate();
        DB::table('user_food_logs')->truncate();
        DB::table('user_trainer_assignments')->truncate();
        DB::table('fitness_assessments')->truncate();
        DB::table('satisfaction_surveys')->truncate();
        DB::table('user_referrals')->truncate();
        DB::table('membership_payments')->truncate();
        DB::table('gym_subscriptions')->truncate();
        DB::table('admin_audit_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ==========================================
        // 0. SEED SAAS SUBSCRIPTION PLANS
        // ==========================================
        $planBasic = SaasSubscriptionPlan::create([
            'id' => 1,
            'name' => 'Plan Básico',
            'description' => 'Ideal para pequeños boxes y entrenamientos personales. Límite de 50 socios y 3 entrenadores.',
            'monthly_price' => 29.99,
            'currency' => 'USD',
            'max_users' => 50,
            'max_trainers' => 3,
            'is_active' => 1,
        ]);

        $planPro = SaasSubscriptionPlan::create([
            'id' => 2,
            'name' => 'Plan Pro',
            'description' => 'Para gimnasios en crecimiento. Límite de 200 socios y 10 entrenadores.',
            'monthly_price' => 59.99,
            'currency' => 'USD',
            'max_users' => 200,
            'max_trainers' => 10,
            'is_active' => 1,
        ]);

        $planPremium = SaasSubscriptionPlan::create([
            'id' => 3,
            'name' => 'Plan Premium',
            'description' => 'Acceso total sin límites para grandes sucursales o cadenas.',
            'monthly_price' => 99.99,
            'currency' => 'USD',
            'max_users' => null,
            'max_trainers' => null,
            'is_active' => 1,
        ]);

        // ==========================================
        // 1. CREATE GYM 1 (GymFlow HQ) & GYM 2 (PowerHouse Studio)
        // ==========================================
        $gym1 = Gym::create([
            'name' => 'GymFlow HQ',
            'slug' => 'gymflow-hq',
            'current_plan_id' => $planPro->id,
            'subscription_status' => 'active',
            'address' => 'Av. de los Deportes 450, Madrid',
            'phone' => '+34 912 345 678',
            'email' => 'info@gymflowhq.com',
            'logo_url' => null,
            'timezone' => 'Europe/Madrid',
            'is_active' => 1,
        ]);

        $gym2 = Gym::create([
            'name' => 'PowerHouse Studio',
            'slug' => 'powerhouse-studio',
            'current_plan_id' => $planBasic->id,
            'subscription_status' => 'trialing',
            'address' => 'Calle Gran Vía 88, Barcelona',
            'phone' => '+34 931 999 888',
            'email' => 'contact@powerhousestudio.com',
            'logo_url' => null,
            'timezone' => 'Europe/Madrid',
            'is_active' => 1,
        ]);

        // ==========================================
        // 2. SEED STAFF / TRAINERS
        // ==========================================
        
        // Trainer Gym 1 (Carlos Ruiz)
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

        // Trainer Gym 2 (Laura Blanco)
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

        // Super Admin 2 (Root Admin)
        $superAdminUser2 = User::create([
            'gym_id' => $gym1->id,
            'email' => 'admin.root@gymflow.com',
            'password_hash' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => 1,
            'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $superAdminUser2->id,
            'first_name' => 'Root',
            'last_name' => 'GymFlow',
            'phone' => '+34 600 888 888',
            'birth_date' => '1990-05-15',
            'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=150&auto=format&fit=crop',
        ]);

        // ==========================================
        // 3. SEED INGREDIENTS (15+ Total)
        // ==========================================
        // Gym 1 Ingredients
        $ingPollo = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Pechuga de Pollo', 'protein_g' => 23.00, 'carbs_g' => 0.00, 'fat_g' => 2.50, 'calories_per_100g' => 120.00, 'unit' => 'g']);
        $ingArroz = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Arroz Blanco Cocido', 'protein_g' => 2.70, 'carbs_g' => 28.00, 'fat_g' => 0.30, 'calories_per_100g' => 130.00, 'unit' => 'g']);
        $ingHuevo = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Huevo Entero', 'protein_g' => 6.00, 'carbs_g' => 0.60, 'fat_g' => 5.00, 'calories_per_100g' => 70.00, 'unit' => 'unit']);
        $ingAvena = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Avena en Hojuelas', 'protein_g' => 13.50, 'carbs_g' => 68.70, 'fat_g' => 7.00, 'calories_per_100g' => 389.00, 'unit' => 'g']);
        $ingWhey = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Proteína de Suero (Whey)', 'protein_g' => 80.00, 'carbs_g' => 6.00, 'fat_g' => 3.00, 'calories_per_100g' => 370.00, 'unit' => 'g']);
        $ingPlatano = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Plátano Maduro', 'protein_g' => 1.20, 'carbs_g' => 23.00, 'fat_g' => 0.30, 'calories_per_100g' => 90.00, 'unit' => 'unit']);
        $ingYogur = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Yogur Griego Natural', 'protein_g' => 10.00, 'carbs_g' => 3.60, 'fat_g' => 0.40, 'calories_per_100g' => 59.00, 'unit' => 'g']);
        $ingSalmon = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Filete de Salmón Fresh', 'protein_g' => 20.00, 'carbs_g' => 0.00, 'fat_g' => 13.00, 'calories_per_100g' => 208.00, 'unit' => 'g']);
        $ingCamote = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Camote Cocido', 'protein_g' => 2.00, 'carbs_g' => 21.00, 'fat_g' => 0.10, 'calories_per_100g' => 90.00, 'unit' => 'g']);
        $ingBrocoli = Ingredient::create(['gym_id' => $gym1->id, 'name' => 'Brócoli Hervido', 'protein_g' => 2.80, 'carbs_g' => 7.00, 'fat_g' => 0.40, 'calories_per_100g' => 35.00, 'unit' => 'g']);

        // Gym 2 Ingredients
        $ingAtun = Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Atún en lata al natural', 'protein_g' => 25.00, 'carbs_g' => 0.00, 'fat_g' => 1.00, 'calories_per_100g' => 110.00, 'unit' => 'unit']);
        $ingAguacate = Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Aguacate Maduro', 'protein_g' => 2.00, 'carbs_g' => 9.00, 'fat_g' => 15.00, 'calories_per_100g' => 160.00, 'unit' => 'unit']);
        $ingRes = Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Carne de Res Magra', 'protein_g' => 26.00, 'carbs_g' => 0.00, 'fat_g' => 8.00, 'calories_per_100g' => 176.00, 'unit' => 'g']);
        $ingChampis = Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Champiñones Frescos', 'protein_g' => 3.10, 'carbs_g' => 3.30, 'fat_g' => 0.30, 'calories_per_100g' => 22.00, 'unit' => 'g']);
        $ingEspinaca = Ingredient::create(['gym_id' => $gym2->id, 'name' => 'Espinacas Baby', 'protein_g' => 2.90, 'carbs_g' => 3.60, 'fat_g' => 0.40, 'calories_per_100g' => 23.00, 'unit' => 'g']);

        // ==========================================
        // 4. RECIPES & RECIPE CATEGORIES (15 Recipes)
        // ==========================================
        
        // Recipe Categories Gym 1
        $rcatId1 = DB::table('recipe_categories')->insertGetId(['gym_id' => $gym1->id, 'name' => 'Nutrición Deportiva G1']);
        $rcatVeg1 = DB::table('recipe_categories')->insertGetId(['gym_id' => $gym1->id, 'name' => 'Nutrición Vegetariana G1']);
        
        // Recipes Gym 1
        $recipeBreakfast1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatId1, 'name' => 'Tortilla de Avena y Claras de Huevo',
            'description' => 'Desayuno pre-entrenamiento de absorción limpia.',
            'instructions' => '1. Mezclar claras con avena. 2. Cocinar a fuego lento.', 'preparation_min' => 10,
            'goal_type' => 'gain_muscle', 'calories_total' => 450.00, 'protein_g' => 30.00, 'carbs_g' => 55.00, 'fat_g' => 10.00
        ]);
        $recipeLunch1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatId1, 'name' => 'Pollo con Arroz Jazmín',
            'description' => 'El almuerzo de los campeones.',
            'instructions' => 'Cocinar 150g de pechuga con 80g de arroz.', 'preparation_min' => 20,
            'goal_type' => 'gain_muscle', 'calories_total' => 600.00, 'protein_g' => 45.00, 'carbs_g' => 70.00, 'fat_g' => 8.00
        ]);
        $recipeSnack1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatId1, 'name' => 'Batido de Proteína y Plátano',
            'description' => 'Batido rápido post-entrenamiento.',
            'instructions' => 'Licuar 1 scoop de Whey, 1 plátano y agua.', 'preparation_min' => 5,
            'goal_type' => 'gain_muscle', 'calories_total' => 350.00, 'protein_g' => 28.00, 'carbs_g' => 35.00, 'fat_g' => 2.00
        ]);
        $recipeDinner1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatId1, 'name' => 'Salmón al Horno con Camote',
            'description' => 'Cena completa alta en omega 3.',
            'instructions' => 'Hornear el salmón con camote en rodajas a 180°C.', 'preparation_min' => 25,
            'goal_type' => 'gain_muscle', 'calories_total' => 500.00, 'protein_g' => 38.00, 'carbs_g' => 40.00, 'fat_g' => 15.00
        ]);
        $recipeBreakfastVeg1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatVeg1, 'name' => 'Panqueques de Banano y Avena',
            'description' => 'Desayuno vegetariano energético.',
            'instructions' => 'Mezclar avena molida, banano triturado y leche vegetal.', 'preparation_min' => 15,
            'goal_type' => 'maintain', 'calories_total' => 400.00, 'protein_g' => 12.00, 'carbs_g' => 65.00, 'fat_g' => 6.00
        ]);
        $recipeLunchVeg1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatVeg1, 'name' => 'Quinoa con Garbanzos y Verduras',
            'description' => 'Almuerzo completo vegano.',
            'instructions' => 'Hervir quinoa y saltear con garbanzos y brócoli.', 'preparation_min' => 20,
            'goal_type' => 'lose_weight', 'calories_total' => 420.00, 'protein_g' => 16.00, 'carbs_g' => 58.00, 'fat_g' => 9.00
        ]);
        $recipeSnackVeg1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatVeg1, 'name' => 'Tazón de Yogur Griego con Berries',
            'description' => 'Snack saciante y fresco.',
            'instructions' => 'Servir yogur con fresas y arándanos frescos.', 'preparation_min' => 5,
            'goal_type' => 'maintain', 'calories_total' => 250.00, 'protein_g' => 20.00, 'carbs_g' => 18.00, 'fat_g' => 4.00
        ]);
        $recipeDinnerVeg1 = Recipe::create([
            'gym_id' => $gym1->id, 'category_id' => $rcatVeg1, 'name' => 'Tofu al Wok con Brócoli',
            'description' => 'Cena proteica baja en calorías.',
            'instructions' => 'Saltear tofu cortado en cubos con brócoli y salsa soya.', 'preparation_min' => 15,
            'goal_type' => 'lose_weight', 'calories_total' => 380.00, 'protein_g' => 22.00, 'carbs_g' => 20.00, 'fat_g' => 14.00
        ]);

        // Recipe Categories Gym 2
        $rcatId2 = DB::table('recipe_categories')->insertGetId(['gym_id' => $gym2->id, 'name' => 'Alimentación Saludable G2']);
        $rcatKeto2 = DB::table('recipe_categories')->insertGetId(['gym_id' => $gym2->id, 'name' => 'Planes Cetogénicos G2']);

        // Recipes Gym 2
        $recipeBreakfast2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatId2, 'name' => 'Ensalada de Atún Proteica',
            'description' => 'Comida baja en carbos y rica en grasas saludables.',
            'instructions' => 'Mezclar atún con aguacate y rúcula.', 'preparation_min' => 10,
            'goal_type' => 'lose_weight', 'calories_total' => 380.00, 'protein_g' => 35.00, 'carbs_g' => 10.00, 'fat_g' => 22.00
        ]);
        $recipeLunch2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatId2, 'name' => 'Pechuga de Pavo con Ensalada Verde',
            'description' => 'Almuerzo fresco y de digestión rápida.',
            'instructions' => 'Asar la pechuga de pavo y servir con espinaca y pepino.', 'preparation_min' => 15,
            'goal_type' => 'lose_weight', 'calories_total' => 350.00, 'protein_g' => 30.00, 'carbs_g' => 8.00, 'fat_g' => 12.00
        ]);
        $recipeSnack2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatId2, 'name' => 'Aguacate Relleno de Huevo',
            'description' => 'Snack cetogénico saciante.',
            'instructions' => 'Partir aguacate, agregar huevo en el centro y hornear 10 min.', 'preparation_min' => 12,
            'goal_type' => 'lose_weight', 'calories_total' => 280.00, 'protein_g' => 10.00, 'carbs_g' => 5.00, 'fat_g' => 24.00
        ]);
        $recipeBreakfastKeto2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatKeto2, 'name' => 'Huevos con Tocino y Aguacate',
            'description' => 'Desayuno clásico Keto.',
            'instructions' => 'Freír 3 huevos enteros con 2 tiras de tocino y servir con medio aguacate.', 'preparation_min' => 10,
            'goal_type' => 'lose_weight', 'calories_total' => 480.00, 'protein_g' => 24.00, 'carbs_g' => 4.00, 'fat_g' => 40.00
        ]);
        $recipeLunchKeto2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatKeto2, 'name' => 'Salmón con Mantequilla y Espárragos',
            'description' => 'Almuerzo cetogénico de alta calidad.',
            'instructions' => 'Cocinar el salmón en mantequilla y saltear los espárragos.', 'preparation_min' => 20,
            'goal_type' => 'gain_muscle', 'calories_total' => 650.00, 'protein_g' => 40.00, 'carbs_g' => 6.00, 'fat_g' => 50.00
        ]);
        $recipeDinnerKeto2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatKeto2, 'name' => 'Carne de Res Salteada con Champiñones',
            'description' => 'Cena densa en nutrientes y grasas.',
            'instructions' => 'Cortar carne en fajitas y saltear con champiñones frescos.', 'preparation_min' => 15,
            'goal_type' => 'gain_muscle', 'calories_total' => 580.00, 'protein_g' => 45.00, 'carbs_g' => 5.00, 'fat_g' => 38.00
        ]);
        $recipeBreakfastLight2 = Recipe::create([
            'gym_id' => $gym2->id, 'category_id' => $rcatId2, 'name' => 'Tortilla de Espinacas',
            'description' => 'Desayuno hipocalórico para definición.',
            'instructions' => 'Batir claras de huevo con espinacas baby y cocinar sin aceite.', 'preparation_min' => 8,
            'goal_type' => 'lose_weight', 'calories_total' => 180.00, 'protein_g' => 20.00, 'carbs_g' => 3.00, 'fat_g' => 1.50
        ]);

        // ==========================================
        // 5. EXERCISES & EXERCISE CATEGORIES (15+ Exercises)
        // ==========================================
        
        // Exercise Categories Gym 1
        $exCatId1 = DB::table('exercise_categories')->insertGetId(['gym_id' => $gym1->id, 'name' => 'Fuerza & Musculación G1', 'description' => 'Ejercicios con pesas y barras libres de GymFlow.']);
        $exCatEndG1 = DB::table('exercise_categories')->insertGetId(['gym_id' => $gym1->id, 'name' => 'Cardio & Resistencia G1', 'description' => 'Ejercicios para acondicionamiento y quema calórica.']);
        $exCatCoreG1 = DB::table('exercise_categories')->insertGetId(['gym_id' => $gym1->id, 'name' => 'Flexibilidad & Core G1', 'description' => 'Ejercicios de abdomen, lumbares y estiramientos.']);

        // Exercises Gym 1
        $exSquat1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Sentadilla con Barra Trasera',
            'description' => 'Sentadilla clásica con barra.', 'muscle_group' => 'Cuádriceps',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exPress1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Prensa de Piernas 45°',
            'description' => 'Prensa pesada para piernas.', 'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exBench1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Press de Banca Plano',
            'description' => 'Ejercicios básico multiarticular de pectoral.', 'muscle_group' => 'Pectoral',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exFly1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Aperturas con Mancuernas',
            'description' => 'Aperturas aisladas en banco plano.', 'muscle_group' => 'Pectoral',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exDeadlift1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Peso Muerto Rumano',
            'description' => 'Trabajo de la cadena posterior enfocada en femoral.', 'muscle_group' => 'Isquiotibiales',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exPullups1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Dominadas en Barra',
            'description' => 'Ejercicio de autocarga en barra de tracción.', 'muscle_group' => 'Espalda',
            'difficulty' => 'advanced', 'requires_equipment' => 1
        ]);
        $exRow1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Remo con Barra',
            'description' => 'Remo inclinado clásico con barra libre.', 'muscle_group' => 'Espalda',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exMilitary1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Press Militar con Barra',
            'description' => 'Press de hombros de pie con barra olímpica.', 'muscle_group' => 'Hombros',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exLateral1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Elevaciones Laterales',
            'description' => 'Aislamiento de la cabeza lateral del deltoides.', 'muscle_group' => 'Hombros',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exCurl1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Curl de Bíceps con Barra',
            'description' => 'Curl de pie con barra z o barra recta.', 'muscle_group' => 'Bíceps',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exPushdown1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Extensión de Tríceps en Polea',
            'description' => 'Extensión de codo con cuerda o barra recta.', 'muscle_group' => 'Tríceps',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exPlank1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatCoreG1, 'name' => 'Plancha Abdominal',
            'description' => 'Soporte isométrico de core.', 'muscle_group' => 'Abdomen',
            'difficulty' => 'beginner', 'requires_equipment' => 0
        ]);
        $exLunge1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Zancadas con Mancuernas',
            'description' => 'Zancadas caminando con carga manual.', 'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exLatPull1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Jalón al Pecho',
            'description' => 'Tracción vertical en polea alta.', 'muscle_group' => 'Espalda',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exCalf1 = Exercise::create([
            'gym_id' => $gym1->id, 'category_id' => $exCatId1, 'name' => 'Elevación de Talones de Pie',
            'description' => 'Trabajo de flexión plantar para gemelos.', 'muscle_group' => 'Pantorrillas',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);

        // Exercise Categories Gym 2
        $exCatId2 = DB::table('exercise_categories')->insertGetId(['gym_id' => $gym2->id, 'name' => 'Acondicionamiento G2', 'description' => 'Ejercicios corporales y funcionales de PowerHouse.']);
        $exCatFuncG2 = DB::table('exercise_categories')->insertGetId(['gym_id' => $gym2->id, 'name' => 'Funcional G2', 'description' => 'Ejercicios multiarticulares con Kettlebells y cajones.']);

        // Exercises Gym 2
        $exSquat2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatId2, 'name' => 'Sentadilla Goblet',
            'description' => 'Sentadilla con mancuerna al pecho.', 'muscle_group' => 'Cuádriceps',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);
        $exKettlebellSwing2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatFuncG2, 'name' => 'Swing con Pesa Rusa',
            'description' => 'Movimiento de bisagra de cadera explosivo.', 'muscle_group' => 'Glúteos',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exBurpee2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatId2, 'name' => 'Burpees',
            'description' => 'Movimiento metabólico completo.', 'muscle_group' => 'Cuerpo Completo',
            'difficulty' => 'intermediate', 'requires_equipment' => 0
        ]);
        $exPushup2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatId2, 'name' => 'Flexiones de Pecho',
            'description' => 'Push-ups clásicos.', 'muscle_group' => 'Pectoral',
            'difficulty' => 'beginner', 'requires_equipment' => 0
        ]);
        $exThruster2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatFuncG2, 'name' => 'Thrusters con Mancuernas',
            'description' => 'Sentadilla combinada con press sobre la cabeza.', 'muscle_group' => 'Cuerpo Completo',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exBoxJump2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatFuncG2, 'name' => 'Saltos al Cajón',
            'description' => 'Saltos pliométricos sobre cajón de madera.', 'muscle_group' => 'Cuádriceps',
            'difficulty' => 'intermediate', 'requires_equipment' => 1
        ]);
        $exRussianTwist2 = Exercise::create([
            'gym_id' => $gym2->id, 'category_id' => $exCatFuncG2, 'name' => 'Giro Ruso con Balón',
            'description' => 'Rotación de tronco sentado para oblicuos.', 'muscle_group' => 'Abdomen',
            'difficulty' => 'beginner', 'requires_equipment' => 1
        ]);

        // ==========================================
        // 6. WORKOUT ROUTINES (11 Workout Routines)
        // ==========================================
        
        // Gym 1 Routines (7 Routines)
        $routineLegs1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Pierna & Glúteo Avanzado G1', 'description' => 'Plan de alta intensidad RPE.',
            'goal_type' => 'gain_muscle', 'difficulty' => 'advanced', 'duration_weeks' => 12, 'days_per_week' => 2,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);
        $routinePush1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Hipertrofia Empuje (Push) G1', 'description' => 'Plan de empuje enfocado en Pectoral, Hombros y Tríceps.',
            'goal_type' => 'gain_muscle', 'difficulty' => 'intermediate', 'duration_weeks' => 8, 'days_per_week' => 3,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);
        $routinePull1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Hipertrofia Tirón (Pull) G1', 'description' => 'Plan enfocado en Espalda, Deltoides Posterior y Bíceps.',
            'goal_type' => 'gain_muscle', 'difficulty' => 'intermediate', 'duration_weeks' => 8, 'days_per_week' => 3,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);
        $routineFullBody1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Cuerpo Completo Principiante G1', 'description' => 'Rutina full body ideal para iniciarse.',
            'goal_type' => 'maintain', 'difficulty' => 'beginner', 'duration_weeks' => 6, 'days_per_week' => 3,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);
        $routineEndurance1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Resistencia Cardiovascular G1', 'description' => ' HIIT metabólico para tonificación y resistencia.',
            'goal_type' => 'improve_endurance', 'difficulty' => 'intermediate', 'duration_weeks' => 6, 'days_per_week' => 2,
            'requires_gym' => 0, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);
        $routineCore1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Definición de Core G1', 'description' => 'Ejercicios focalizados en abdomen y lumbares.',
            'goal_type' => 'lose_weight', 'difficulty' => 'beginner', 'duration_weeks' => 4, 'days_per_week' => 3,
            'requires_gym' => 0, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);
        $routineUpper1 = WorkoutRoutine::create([
            'gym_id' => $gym1->id, 'name' => 'Especialización Torso G1', 'description' => 'Especial de tracciones y empujes superiores.',
            'goal_type' => 'gain_muscle', 'difficulty' => 'advanced', 'duration_weeks' => 8, 'days_per_week' => 2,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer1->id
        ]);

        // Gym 2 Routines (4 Routines)
        $routineLegs2 = WorkoutRoutine::create([
            'gym_id' => $gym2->id, 'name' => 'Hipertrofia Funcional G2', 'description' => 'Plan de desarrollo muscular integral.',
            'goal_type' => 'gain_muscle', 'difficulty' => 'intermediate', 'duration_weeks' => 8, 'days_per_week' => 1,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer2->id
        ]);
        $routineCross2 = WorkoutRoutine::create([
            'gym_id' => $gym2->id, 'name' => 'Acondicionamiento General G2', 'description' => 'Rutina de resistencia y fuerza funcional tipo crossfit.',
            'goal_type' => 'improve_endurance', 'difficulty' => 'intermediate', 'duration_weeks' => 8, 'days_per_week' => 3,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer2->id
        ]);
        $routinePower2 = WorkoutRoutine::create([
            'gym_id' => $gym2->id, 'name' => 'Fuerza Extrema G2', 'description' => 'Rutina pesada enfocada en levantamientos primarios.',
            'goal_type' => 'gain_weight', 'difficulty' => 'advanced', 'duration_weeks' => 12, 'days_per_week' => 3,
            'requires_gym' => 1, 'is_active' => 1, 'created_by' => $trainer2->id
        ]);
        $routineDef2 = WorkoutRoutine::create([
            'gym_id' => $gym2->id, 'name' => 'Definición Express G2', 'description' => 'Rutina rápida y densa de pérdida de grasa.',
            'goal_type' => 'lose_weight', 'difficulty' => 'beginner', 'duration_weeks' => 6, 'days_per_week' => 2,
            'requires_gym' => 0, 'is_active' => 1, 'created_by' => $trainer2->id
        ]);

        // ==========================================
        // 6b. ROUTINE DAYS & ROUTINE EXERCISES
        // ==========================================
        
        // Days and Exercises for routineLegs1
        $day1_g1 = RoutineDay::create(['routine_id' => $routineLegs1->id, 'day_number' => 1, 'day_name' => 'Día 1: Fuerza Cuádriceps', 'focus_area' => 'Piernas']);
        $day2_g1 = RoutineDay::create(['routine_id' => $routineLegs1->id, 'day_number' => 2, 'day_name' => 'Día 2: Auxiliares de Pierna', 'focus_area' => 'Isquiotibiales']);
        RoutineExercise::create(['routine_day_id' => $day1_g1->id, 'exercise_id' => $exSquat1->id, 'sets' => 4, 'reps' => '6-8', 'rest_seconds' => 120, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $day2_g1->id, 'exercise_id' => $exPress1->id, 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 90, 'order_index' => 1]);

        // Days and Exercises for routinePush1
        $dayPush1 = RoutineDay::create(['routine_id' => $routinePush1->id, 'day_number' => 1, 'day_name' => 'Empuje Horizontal y Vertical', 'focus_area' => 'Empuje']);
        RoutineExercise::create(['routine_day_id' => $dayPush1->id, 'exercise_id' => $exBench1->id, 'sets' => 4, 'reps' => '8-10', 'rest_seconds' => 90, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $dayPush1->id, 'exercise_id' => $exMilitary1->id, 'sets' => 3, 'reps' => '8-10', 'rest_seconds' => 90, 'order_index' => 2]);
        RoutineExercise::create(['routine_day_id' => $dayPush1->id, 'exercise_id' => $exFly1->id, 'sets' => 3, 'reps' => '12', 'rest_seconds' => 60, 'order_index' => 3]);
        RoutineExercise::create(['routine_day_id' => $dayPush1->id, 'exercise_id' => $exPushdown1->id, 'sets' => 3, 'reps' => '12-15', 'rest_seconds' => 60, 'order_index' => 4]);

        // Days and Exercises for routinePull1
        $dayPull1 = RoutineDay::create(['routine_id' => $routinePull1->id, 'day_number' => 1, 'day_name' => 'Tirón de Espalda y Tracciones', 'focus_area' => 'Tirón']);
        RoutineExercise::create(['routine_day_id' => $dayPull1->id, 'exercise_id' => $exPullups1->id, 'sets' => 4, 'reps' => 'Fallo', 'rest_seconds' => 120, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $dayPull1->id, 'exercise_id' => $exRow1->id, 'sets' => 3, 'reps' => '10', 'rest_seconds' => 90, 'order_index' => 2]);
        RoutineExercise::create(['routine_day_id' => $dayPull1->id, 'exercise_id' => $exCurl1->id, 'sets' => 3, 'reps' => '12', 'rest_seconds' => 60, 'order_index' => 3]);

        // Days and Exercises for routineFullBody1
        $dayFB1 = RoutineDay::create(['routine_id' => $routineFullBody1->id, 'day_number' => 1, 'day_name' => 'Día 1: Cuerpo Completo A', 'focus_area' => 'Cuerpo Completo']);
        RoutineExercise::create(['routine_day_id' => $dayFB1->id, 'exercise_id' => $exSquat1->id, 'sets' => 3, 'reps' => '10', 'rest_seconds' => 90, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $dayFB1->id, 'exercise_id' => $exBench1->id, 'sets' => 3, 'reps' => '10', 'rest_seconds' => 90, 'order_index' => 2]);
        RoutineExercise::create(['routine_day_id' => $dayFB1->id, 'exercise_id' => $exRow1->id, 'sets' => 3, 'reps' => '10', 'rest_seconds' => 90, 'order_index' => 3]);

        // Days and Exercises for routineEndurance1
        $dayEnd1 = RoutineDay::create(['routine_id' => $routineEndurance1->id, 'day_number' => 1, 'day_name' => ' HIIT Circuito Cardio', 'focus_area' => 'Cardio']);
        RoutineExercise::create(['routine_day_id' => $dayEnd1->id, 'exercise_id' => $exLunge1->id, 'sets' => 4, 'reps' => '20', 'rest_seconds' => 45, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $dayEnd1->id, 'exercise_id' => $exPlank1->id, 'sets' => 3, 'reps' => '60s', 'rest_seconds' => 45, 'order_index' => 2]);

        // Days and Exercises for routineLegs2
        $day1_g2 = RoutineDay::create(['routine_id' => $routineLegs2->id, 'day_number' => 1, 'day_name' => 'Día 1: Fuerza Corporal', 'focus_area' => 'Piernas']);
        RoutineExercise::create(['routine_day_id' => $day1_g2->id, 'exercise_id' => $exSquat2->id, 'sets' => 3, 'reps' => '12', 'rest_seconds' => 60, 'order_index' => 1]);

        // Days and Exercises for routineCross2
        $dayCross1 = RoutineDay::create(['routine_id' => $routineCross2->id, 'day_number' => 1, 'day_name' => 'Circuito WOD Funcional', 'focus_area' => 'Cuerpo Completo']);
        RoutineExercise::create(['routine_day_id' => $dayCross1->id, 'exercise_id' => $exBurpee2->id, 'sets' => 4, 'reps' => '15', 'rest_seconds' => 45, 'order_index' => 1]);
        RoutineExercise::create(['routine_day_id' => $dayCross1->id, 'exercise_id' => $exKettlebellSwing2->id, 'sets' => 4, 'reps' => '20', 'rest_seconds' => 45, 'order_index' => 2]);
        RoutineExercise::create(['routine_day_id' => $dayCross1->id, 'exercise_id' => $exPushup2->id, 'sets' => 3, 'reps' => '15', 'rest_seconds' => 45, 'order_index' => 3]);

        // ==========================================
        // 7. MEAL PLANS (11 Diet / Meal Plans)
        // ==========================================
        
        // Gym 1 Meal Plans (6 Plans)
        $mealPlanBulking1 = MealPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Volumen G1 2500 kcal', 'description' => 'Plan hipercalórico base para ganar masa muscular.',
            'goal_type' => 'gain_muscle', 'duration_weeks' => 12, 'daily_calories' => 2500.00, 'is_active' => 1
        ]);
        $mealPlanCutting1 = MealPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Definición Estricta G1 1800 kcal', 'description' => 'Plan hipocalórico enfocado en la quema de grasa manteniendo músculo.',
            'goal_type' => 'lose_weight', 'duration_weeks' => 8, 'daily_calories' => 1800.00, 'is_active' => 1
        ]);
        $mealPlanMaintenance1 = MealPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Mantenimiento Equilibrado G1 2200 kcal', 'description' => 'Dieta balanceada para retener composición corporal actual.',
            'goal_type' => 'maintain', 'duration_weeks' => 6, 'daily_calories' => 2200.00, 'is_active' => 1
        ]);
        $mealPlanVegBulking1 = MealPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Volumen Vegetariano G1 2700 kcal', 'description' => 'Plan hipercalórico a base de plantas.',
            'goal_type' => 'gain_muscle', 'duration_weeks' => 8, 'daily_calories' => 2700.00, 'is_active' => 1
        ]);
        $mealPlanHiperproteico1 = MealPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Hiperproteico Avanzado G1 2800 kcal', 'description' => 'Alta concentración de proteína y carbohidratos complejos.',
            'goal_type' => 'gain_muscle', 'duration_weeks' => 12, 'daily_calories' => 2800.00, 'is_active' => 1
        ]);
        $mealPlanLowCarb1 = MealPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Bajo en Carbohidratos G1 2000 kcal', 'description' => 'Focalizado en proteínas y vegetales verdes.',
            'goal_type' => 'lose_weight', 'duration_weeks' => 10, 'daily_calories' => 2000.00, 'is_active' => 1
        ]);

        // Gym 2 Meal Plans (5 Plans)
        $mealPlanKeto2 = MealPlan::create([
            'gym_id' => $gym2->id, 'name' => 'Keto Adaptada G2 2000 kcal', 'description' => 'Plan nutricional cetogénico bajo en carbohidratos.',
            'goal_type' => 'lose_weight', 'duration_weeks' => 8, 'daily_calories' => 2000.00, 'is_active' => 1
        ]);
        $mealPlanBulking2 = MealPlan::create([
            'gym_id' => $gym2->id, 'name' => 'Volumen Limpio G2 3000 kcal', 'description' => 'Aumento de volumen con grasas y proteínas limpias.',
            'goal_type' => 'gain_muscle', 'duration_weeks' => 12, 'daily_calories' => 3000.00, 'is_active' => 1
        ]);
        $mealPlanCutting2 = MealPlan::create([
            'gym_id' => $gym2->id, 'name' => 'Déficit Calórico Moderado G2 1600 kcal', 'description' => 'Pérdida de peso sostenible para el día a día.',
            'goal_type' => 'lose_weight', 'duration_weeks' => 8, 'daily_calories' => 1600.00, 'is_active' => 1
        ]);
        $mealPlanEndurance2 = MealPlan::create([
            'gym_id' => $gym2->id, 'name' => 'Resistencia & Energía G2 2500 kcal', 'description' => 'Plan alto en carbohidratos sanos para atletas de fondo.',
            'goal_type' => 'improve_endurance', 'duration_weeks' => 6, 'daily_calories' => 2500.00, 'is_active' => 1
        ]);
        $mealPlanDetox2 = MealPlan::create([
            'gym_id' => $gym2->id, 'name' => 'Plan Detox Low-Cal G2 1400 kcal', 'description' => 'Plan desintoxicante bajo en calorías de 4 semanas.',
            'goal_type' => 'lose_weight', 'duration_weeks' => 4, 'daily_calories' => 1400.00, 'is_active' => 1
        ]);

        // ==========================================
        // 7b. MEAL PLAN DAYS
        // ==========================================
        // Mapear días para mealPlanBulking1
        for ($day = 1; $day <= 5; $day++) {
            MealPlanDay::create([
                'meal_plan_id' => $mealPlanBulking1->id, 'day_number' => $day,
                'breakfast_recipe_id' => $recipeBreakfast1->id,
                'snack1_recipe_id' => $recipeSnackVeg1->id,
                'lunch_recipe_id' => $recipeLunch1->id,
                'snack2_recipe_id' => $recipeSnack1->id,
                'dinner_recipe_id' => $recipeDinner1->id,
            ]);
        }

        // Mapear días para mealPlanVegBulking1
        for ($day = 1; $day <= 3; $day++) {
            MealPlanDay::create([
                'meal_plan_id' => $mealPlanVegBulking1->id, 'day_number' => $day,
                'breakfast_recipe_id' => $recipeBreakfastVeg1->id,
                'lunch_recipe_id' => $recipeLunchVeg1->id,
                'dinner_recipe_id' => $recipeDinnerVeg1->id,
            ]);
        }

        // Mapear días para mealPlanKeto2
        for ($day = 1; $day <= 5; $day++) {
            MealPlanDay::create([
                'meal_plan_id' => $mealPlanKeto2->id, 'day_number' => $day,
                'breakfast_recipe_id' => $recipeBreakfastKeto2->id,
                'snack1_recipe_id' => $recipeSnack2->id,
                'lunch_recipe_id' => $recipeLunchKeto2->id,
                'dinner_recipe_id' => $recipeDinnerKeto2->id,
            ]);
        }

        // Mapear días para mealPlanCutting2
        for ($day = 1; $day <= 3; $day++) {
            MealPlanDay::create([
                'meal_plan_id' => $mealPlanCutting2->id, 'day_number' => $day,
                'breakfast_recipe_id' => $recipeBreakfastLight2->id,
                'lunch_recipe_id' => $recipeBreakfast2->id,
                'dinner_recipe_id' => $recipeLunch2->id,
            ]);
        }

        // ==========================================
        // 8. MEMBERS / USERS (11+ Users/Profiles)
        // ==========================================
        
        // Members Gym 1 (Maria, Juan, Mateo)
        $user1 = User::create([
            'gym_id' => $gym1->id, 'email' => 'maria@example.com',
            'password_hash' => Hash::make('password'), 'role' => 'member', 'is_active' => 1, 'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user1->id, 'first_name' => 'María Inés', 'last_name' => 'Silva',
            'phone' => '+34 655 444 333', 'birth_date' => '1995-08-20', 'gender' => 'female',
            'profile_photo' => 'https://images.unsplash.com/photo-1548690312-e3b507d8c110?q=80&w=100&auto=format&fit=crop',
        ]);
        BodyMeasurement::create([
            'user_id' => $user1->id, 'weight_kg' => 64.0, 'height_cm' => 165.0, 'bmi' => 23.51, 'bmi_category' => 'normal',
            'measured_at' => Carbon::now()->subWeeks(1), 'createdAt' => now(), 'updatedAt' => now(),
        ]);

        $user2 = User::create([
            'gym_id' => $gym1->id, 'email' => 'juan@example.com',
            'password_hash' => Hash::make('password'), 'role' => 'member', 'is_active' => 1, 'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user2->id, 'first_name' => 'Juan Pablo', 'last_name' => 'Torres',
            'phone' => '+34 677 888 999', 'birth_date' => '1992-03-12', 'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=100&auto=format&fit=crop',
        ]);
        BodyMeasurement::create([
            'user_id' => $user2->id, 'weight_kg' => 85.0, 'height_cm' => 178.0, 'bmi' => 26.83, 'bmi_category' => 'overweight',
            'measured_at' => Carbon::now()->subWeeks(1), 'createdAt' => now(), 'updatedAt' => now(),
        ]);

        $user4 = User::create([
            'gym_id' => $gym1->id, 'email' => 'mateo@example.com',
            'password_hash' => Hash::make('password'), 'role' => 'member', 'is_active' => 0, 'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user4->id, 'first_name' => 'Mateo', 'last_name' => 'Mendoza',
            'phone' => '+34 699 000 111', 'birth_date' => '1988-09-02', 'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop',
        ]);

        // Members Gym 2 (Sofia, Andres)
        $user3 = User::create([
            'gym_id' => $gym2->id, 'email' => 'sofia@example.com',
            'password_hash' => Hash::make('password'), 'role' => 'member', 'is_active' => 1, 'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user3->id, 'first_name' => 'Sofía', 'last_name' => 'Vergara G.',
            'phone' => '+34 688 555 444', 'birth_date' => '1997-11-25', 'gender' => 'female',
            'profile_photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=100&auto=format&fit=crop',
        ]);
        BodyMeasurement::create([
            'user_id' => $user3->id, 'weight_kg' => 58.0, 'height_cm' => 168.0, 'bmi' => 20.55, 'bmi_category' => 'normal',
            'measured_at' => Carbon::now()->subWeeks(1), 'createdAt' => now(), 'updatedAt' => now(),
        ]);

        $user5 = User::create([
            'gym_id' => $gym2->id, 'email' => 'andres@example.com',
            'password_hash' => Hash::make('password'), 'role' => 'member', 'is_active' => 1, 'email_verified' => 1,
        ]);
        UserProfile::create([
            'user_id' => $user5->id, 'first_name' => 'Andrés', 'last_name' => 'Silva',
            'phone' => '+34 600 999 888', 'birth_date' => '1994-01-20', 'gender' => 'male',
            'profile_photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop',
        ]);
        BodyMeasurement::create([
            'user_id' => $user5->id, 'weight_kg' => 78.5, 'height_cm' => 176.0, 'bmi' => 25.34, 'bmi_category' => 'overweight',
            'measured_at' => Carbon::now()->subDays(1), 'createdAt' => now(), 'updatedAt' => now(),
        ]);

        // ==========================================
        // 8b. ASSIGNMENTS (Routines and Meal Plans)
        // ==========================================
        UserAssignedRoutine::create([
            'user_id' => $user1->id, 'routine_id' => $routineLegs1->id, 'assigned_by' => $trainer1->id,
            'start_date' => Carbon::now()->subWeeks(2), 'is_active' => 1,
        ]);
        UserMealPlan::create([
            'user_id' => $user1->id, 'meal_plan_id' => $mealPlanBulking1->id, 'assigned_by' => $trainer1->id,
            'start_date' => Carbon::now()->subWeeks(2), 'is_active' => 1,
        ]);

        UserAssignedRoutine::create([
            'user_id' => $user2->id, 'routine_id' => $routinePush1->id, 'assigned_by' => $trainer1->id,
            'start_date' => Carbon::now()->subDays(5), 'is_active' => 1,
        ]);
        UserMealPlan::create([
            'user_id' => $user2->id, 'meal_plan_id' => $mealPlanCutting1->id, 'assigned_by' => $trainer1->id,
            'start_date' => Carbon::now()->subDays(5), 'is_active' => 1,
        ]);

        UserAssignedRoutine::create([
            'user_id' => $user5->id, 'routine_id' => $routineCross2->id, 'assigned_by' => $trainer2->id,
            'start_date' => Carbon::now()->subDays(1), 'is_active' => 1,
        ]);
        UserMealPlan::create([
            'user_id' => $user5->id, 'meal_plan_id' => $mealPlanBulking2->id, 'assigned_by' => $trainer2->id,
            'start_date' => Carbon::now()->subDays(1), 'is_active' => 1,
        ]);

        UserMealPlan::create([
            'user_id' => $user3->id, 'meal_plan_id' => $mealPlanKeto2->id, 'assigned_by' => $trainer2->id,
            'start_date' => Carbon::now()->subDays(2), 'is_active' => 1,
        ]);

        // ==========================================
        // 9. WORKOUT SESSIONS FOR ATTENDANCE STATS
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
                'routine_id' => $routineCross2->id,
                'session_date' => $startOfWeek->copy()->addDays(1), // Tuesday
                'started_at' => $startOfWeek->copy()->addDays(1)->hour(9 + $i),
                'ended_at' => $startOfWeek->copy()->addDays(1)->hour(10 + $i),
                'duration_minutes' => 50,
                'calories_burned' => 350.00,
            ]);
        }

        // ==========================================
        // 10. MEMBERSHIP PLANS AND USER MEMBERSHIPS
        // ==========================================
        
        // Plans for Gym 1
        $planVipG1 = MembershipPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Plan VIP Mensual', 'description' => 'Acceso libre y entrenador personalizado.',
            'duration_days' => 30, 'price' => 50.00, 'currency' => 'USD', 'includes_trainer' => 1, 'is_active' => 1,
        ]);
        $planBasicG1 = MembershipPlan::create([
            'gym_id' => $gym1->id, 'name' => 'Plan Básico Mensual', 'description' => 'Acceso libre a máquinas de musculación.',
            'duration_days' => 30, 'price' => 30.00, 'currency' => 'USD', 'includes_trainer' => 0, 'is_active' => 1,
        ]);

        // Memberships for Gym 1 members
        UserMembership::create([
            'user_id' => $user1->id, 'gym_id' => $gym1->id, 'plan_id' => $planVipG1->id,
            'start_date' => Carbon::now()->subDays(15), 'end_date' => Carbon::now()->addDays(15),
            'status' => 'active', 'payment_status' => 'paid', 'notes' => 'Atleta muy disciplinada.',
        ]);
        UserMembership::create([
            'user_id' => $user2->id, 'gym_id' => $gym1->id, 'plan_id' => $planBasicG1->id,
            'start_date' => Carbon::now()->subDays(25), 'end_date' => Carbon::now()->addDays(5),
            'status' => 'active', 'payment_status' => 'pending', 'notes' => 'Pendiente pago del mes.',
        ]);

        // Plans for Gym 2
        $planPowerG2 = MembershipPlan::create([
            'gym_id' => $gym2->id, 'name' => 'Power Pass Mensual', 'description' => 'Pase completo con clases dirigidas.',
            'duration_days' => 30, 'price' => 60.00, 'currency' => 'USD', 'includes_trainer' => 1, 'is_active' => 1,
        ]);

        // Memberships for Gym 2 members
        UserMembership::create([
            'user_id' => $user3->id, 'gym_id' => $gym2->id, 'plan_id' => $planPowerG2->id,
            'start_date' => Carbon::now()->subDays(5), 'end_date' => Carbon::now()->addDays(25),
            'status' => 'active', 'payment_status' => 'paid',
        ]);
        UserMembership::create([
            'user_id' => $user5->id, 'gym_id' => $gym2->id, 'plan_id' => $planPowerG2->id,
            'start_date' => Carbon::now()->subDays(35), 'end_date' => Carbon::now()->subDays(5),
            'status' => 'expired', 'payment_status' => 'overdue', 'notes' => 'Recordatorio de pago enviado.',
        ]);

        // ==========================================
        // 11. INVENTORY PRODUCTS & CATEGORIES
        // ==========================================
        
        // Gym 1
        $catAccG1 = ProductCategory::create(['gym_id' => $gym1->id, 'name' => 'Accesorios', 'description' => 'Shakers, straps y vendas']);
        $catSupG1 = ProductCategory::create(['gym_id' => $gym1->id, 'name' => 'Suplementos', 'description' => 'Proteínas y creatinas']);
        $catBebG1 = ProductCategory::create(['gym_id' => $gym1->id, 'name' => 'Bebidas', 'description' => 'Agua e hidratantes']);

        $shaker = InventoryProduct::create([
            'gym_id' => $gym1->id, 'category_id' => $catAccG1->id, 'name' => 'Vaso Mezclador 500ml',
            'description' => 'Shaker clásico hermético', 'price' => 10.00, 'cost_price' => 4.00,
            'stock_quantity' => 0, 'min_stock' => 3, 'is_available' => 1
        ]);
        $wheyProduct = InventoryProduct::create([
            'gym_id' => $gym1->id, 'category_id' => $catSupG1->id, 'name' => 'Whey Protein 1kg (Fresa)',
            'description' => 'Concentrado de suero de leche de alta calidad', 'price' => 45.00, 'cost_price' => 28.00,
            'stock_quantity' => 0, 'min_stock' => 2, 'is_available' => 1
        ]);
        $bar = InventoryProduct::create([
            'gym_id' => $gym1->id, 'category_id' => $catSupG1->id, 'name' => 'Barra de Proteínas 60g',
            'description' => 'Aperitivo con 20g de proteína', 'price' => 3.50, 'cost_price' => 1.50,
            'stock_quantity' => 0, 'min_stock' => 5, 'is_available' => 1
        ]);

        // Gym 2
        $catSupG2 = ProductCategory::create(['gym_id' => $gym2->id, 'name' => 'Suplementación G2', 'description' => 'Suplementos deportivos']);
        $catBebG2 = ProductCategory::create(['gym_id' => $gym2->id, 'name' => 'Bebidas G2', 'description' => 'Hidratantes y energizantes']);

        $iso = InventoryProduct::create([
            'gym_id' => $gym2->id, 'category_id' => $catSupG2->id, 'name' => 'Iso Protein 900g (Vainilla)',
            'description' => 'Proteína aislada premium', 'price' => 55.00, 'cost_price' => 35.00,
            'stock_quantity' => 0, 'min_stock' => 3, 'is_available' => 1
        ]);

        // Load initial stock via movements (this triggers trg_update_stock_after_movement to update stock_quantity)
        InventoryMovement::create([
            'product_id' => $shaker->id, 'movement_type' => 'in', 'quantity' => 15,
            'reason' => 'Carga de stock inicial', 'performed_by' => $trainerUser1->id, 'createdAt' => Carbon::now()->subDays(5)
        ]);
        InventoryMovement::create([
            'product_id' => $wheyProduct->id, 'movement_type' => 'in', 'quantity' => 8,
            'reason' => 'Carga de stock inicial', 'performed_by' => $trainerUser1->id, 'createdAt' => Carbon::now()->subDays(5)
        ]);
        InventoryMovement::create([
            'product_id' => $bar->id, 'movement_type' => 'in', 'quantity' => 20,
            'reason' => 'Carga de stock inicial', 'performed_by' => $trainerUser1->id, 'createdAt' => Carbon::now()->subDays(5)
        ]);
        InventoryMovement::create([
            'product_id' => $iso->id, 'movement_type' => 'in', 'quantity' => 12,
            'reason' => 'Carga de stock inicial', 'performed_by' => $trainerUser2->id, 'createdAt' => Carbon::now()->subDays(5)
        ]);

        // ==========================================
        // 12. GYM EQUIPMENT (10 items)
        // ==========================================
        
        // Gym 1
        $eqCinta = Equipment::create(['gym_id' => $gym1->id, 'name' => 'Cinta de Correr Pro Series', 'description' => 'Cinta de correr motorizada Matrix Pro', 'requires_gym' => 1]);
        $eqPrensa = Equipment::create(['gym_id' => $gym1->id, 'name' => 'Prensa de Piernas 45° Matrix', 'description' => 'Prensa de piernas Matrix 45 grados', 'requires_gym' => 1]);
        $eqSmith = Equipment::create(['gym_id' => $gym1->id, 'name' => 'Rack de Sentadillas Smith', 'description' => 'Soporte rack Smith multipower', 'requires_gym' => 1]);
        $eqBench = Equipment::create(['gym_id' => $gym1->id, 'name' => 'Banco de Pecho Plano Matrix', 'description' => 'Banco plano para press de banca.', 'requires_gym' => 1]);
        $eqBarra = Equipment::create(['gym_id' => $gym1->id, 'name' => 'Barra Olímpica de 20kg', 'description' => 'Barra olímpica clásica de acero templado.', 'requires_gym' => 1]);
        $eqPolea = Equipment::create(['gym_id' => $gym1->id, 'name' => 'Polea Doble Regulable', 'description' => 'Crossover de poleas funcional Matrix.', 'requires_gym' => 1]);

        // Gym 2
        $eqSpinning = Equipment::create(['gym_id' => $gym2->id, 'name' => 'Bicicleta de Spinning Matrix', 'description' => 'Bicicleta estática indoor', 'requires_gym' => 1]);
        $eqBancoG2 = Equipment::create(['gym_id' => $gym2->id, 'name' => 'Banco de Pecho Plano Matrix', 'description' => 'Banco plano ajustable musculación', 'requires_gym' => 1]);
        $eqKettlebell = Equipment::create(['gym_id' => $gym2->id, 'name' => 'Set de Pesas Rusas (Kettlebells)', 'description' => 'Set de kettlebells de 8kg a 24kg.', 'requires_gym' => 1]);
        $eqCajon = Equipment::create(['gym_id' => $gym2->id, 'name' => 'Cajón Pliométrico de Madera', 'description' => 'Cajón para saltos de 50x60x70 cm.', 'requires_gym' => 1]);

        // ==========================================
        // 13. CLIENT GOALS & ACHIEVEMENTS
        // ==========================================
        UserGoal::create(['user_id' => $user1->id, 'goal_type' => 'lose_weight', 'target_weight' => 60.0, 'target_date' => Carbon::now()->addWeeks(6), 'is_active' => 1]);
        UserAchievement::create(['user_id' => $user1->id, 'achievement_type' => 'first_workout', 'description' => 'Completaste tu primera sesión de entrenamiento.', 'achieved_at' => Carbon::now()->subWeeks(2)]);

        UserGoal::create(['user_id' => $user5->id, 'goal_type' => 'gain_muscle', 'target_weight' => 82.0, 'target_date' => Carbon::now()->addWeeks(8), 'is_active' => 1]);
        UserAchievement::create(['user_id' => $user5->id, 'achievement_type' => '10k_calories', 'description' => 'Quemaste más de 10,000 kcal en sesiones registradas.', 'achieved_at' => Carbon::now()->subDays(2)]);

        // ==========================================
        // 14. PROMO CODES / CUPONES
        // ==========================================
        $promoG1 = PromoCode::create([
            'gym_id' => $gym1->id, 'code' => 'DESCUENTO10', 'discount_type' => 'percentage', 'discount_value' => 10.00,
            'valid_from' => Carbon::now()->subDays(10), 'valid_until' => Carbon::now()->addDays(30), 'max_uses' => 100, 'current_uses' => 5, 'is_active' => 1
        ]);
        $promoFixedG1 = PromoCode::create([
            'gym_id' => $gym1->id, 'code' => 'VERANO5', 'discount_type' => 'fixed', 'discount_value' => 5.00,
            'valid_from' => Carbon::now()->subDays(10), 'valid_until' => Carbon::now()->addDays(30), 'max_uses' => 50, 'current_uses' => 2, 'is_active' => 1
        ]);
        $promoG2 = PromoCode::create([
            'gym_id' => $gym2->id, 'code' => 'KETO20', 'discount_type' => 'percentage', 'discount_value' => 20.00,
            'valid_from' => Carbon::now()->subDays(5), 'valid_until' => Carbon::now()->addDays(25), 'max_uses' => 10, 'current_uses' => 1, 'is_active' => 1
        ]);

        // ==========================================
        // 15. POS SALES & SALE ITEMS
        // ==========================================
        $sale1 = ProductSale::create([
            'gym_id' => $gym1->id, 'user_id' => $user1->id, 'promo_code_id' => $promoG1->id, 'sold_by' => $trainerUser1->id,
            'total_amount' => 9.00, 'payment_method' => 'cash', 'sale_date' => Carbon::now()->subDays(1),
            'notes' => 'Venta con cupón de descuento.', 'createdAt' => now()
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id, 'product_id' => $shaker->id, 'quantity' => 1, 'unit_price' => 10.00, 'subtotal' => 10.00
        ]);

        $sale2 = ProductSale::create([
            'gym_id' => $gym1->id, 'user_id' => $user2->id, 'sold_by' => $trainerUser1->id,
            'total_amount' => 7.00, 'payment_method' => 'card', 'sale_date' => Carbon::now()->subDays(2),
            'notes' => 'Venta sin cupón.', 'createdAt' => now()
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id, 'product_id' => $bar->id, 'quantity' => 2, 'unit_price' => 3.50, 'subtotal' => 7.00
        ]);

        // ==========================================
        // 16. GYM CLASSES & SCHEDULES & BOOKINGS
        // ==========================================
        $classCrossfit = GymClass::create([
            'gym_id' => $gym1->id, 'name' => 'CrossFit WOD', 'description' => 'Acondicionamiento metabólico de alta intensidad y fuerza.',
            'duration_minutes' => 60, 'capacity' => 12, 'color_code' => '#ef4444', 'is_active' => 1
        ]);
        $classYoga = GymClass::create([
            'gym_id' => $gym1->id, 'name' => 'Yoga Vinyasa', 'description' => 'Fluidez de movimientos coordinados con la respiración.',
            'duration_minutes' => 60, 'capacity' => 15, 'color_code' => '#3b82f6', 'is_active' => 1
        ]);

        $scheduleCrossfitPast = ClassSchedule::create([
            'gym_id' => $gym1->id, 'gym_class_id' => $classCrossfit->id, 'trainer_id' => $trainer1->id,
            'scheduled_date' => Carbon::now()->subDays(1)->format('Y-m-d'), 'start_time' => '08:00:00', 'end_time' => '09:00:00', 'status' => 'completed'
        ]);
        $scheduleCrossfitFuture = ClassSchedule::create([
            'gym_id' => $gym1->id, 'gym_class_id' => $classCrossfit->id, 'trainer_id' => $trainer1->id,
            'scheduled_date' => Carbon::now()->addDays(2)->format('Y-m-d'), 'start_time' => '19:00:00', 'end_time' => '20:00:00', 'status' => 'scheduled'
        ]);

        ClassBooking::create([
            'class_schedule_id' => $scheduleCrossfitPast->id, 'user_id' => $user1->id, 'status' => 'attended', 'booked_at' => Carbon::now()->subDays(2)
        ]);
        ClassBooking::create([
            'class_schedule_id' => $scheduleCrossfitFuture->id, 'user_id' => $user1->id, 'status' => 'booked', 'booked_at' => Carbon::now()->subMinutes(30)
        ]);
        ClassBooking::create([
            'class_schedule_id' => $scheduleCrossfitFuture->id, 'user_id' => $user2->id, 'status' => 'booked', 'booked_at' => Carbon::now()->subMinutes(15)
        ]);

        $classSpinning = GymClass::create([
            'gym_id' => $gym2->id, 'name' => 'Spinning Pro', 'description' => 'Cardio de alta exigencia sobre bicicleta estática.',
            'duration_minutes' => 45, 'capacity' => 10, 'color_code' => '#f59e0b', 'is_active' => 1
        ]);
        $scheduleSpinningFuture = ClassSchedule::create([
            'gym_id' => $gym2->id, 'gym_class_id' => $classSpinning->id, 'trainer_id' => $trainer2->id,
            'scheduled_date' => Carbon::now()->addDays(1)->format('Y-m-d'), 'start_time' => '18:00:00', 'end_time' => '18:45:00', 'status' => 'scheduled'
        ]);
        ClassBooking::create([
            'class_schedule_id' => $scheduleSpinningFuture->id, 'user_id' => $user3->id, 'status' => 'booked', 'booked_at' => Carbon::now()->subHours(5)
        ]);

        // ==========================================
        // 17. ATTENDANCE LOGS
        // ==========================================
        for ($i = 0; $i < 10; $i++) {
            AttendanceLog::create([
                'gym_id' => $gym1->id, 'user_id' => $user1->id,
                'check_in' => Carbon::now()->subDays($i)->hour(8)->minute(15)->second(0),
                'check_out' => Carbon::now()->subDays($i)->hour(9)->minute(30)->second(0),
                'entry_method' => 'biometric', 'status' => 'valid'
            ]);
            AttendanceLog::create([
                'gym_id' => $gym1->id, 'user_id' => $user2->id,
                'check_in' => Carbon::now()->subDays($i)->hour(18)->minute(0)->second(0),
                'check_out' => Carbon::now()->subDays($i)->hour(19)->minute(15)->second(0),
                'entry_method' => 'rfid', 'status' => 'valid'
            ]);
        }
        for ($i = 0; $i < 4; $i++) {
            AttendanceLog::create([
                'gym_id' => $gym2->id, 'user_id' => $user3->id,
                'check_in' => Carbon::now()->subDays($i)->hour(7)->minute(30)->second(0),
                'check_out' => Carbon::now()->subDays($i)->hour(8)->minute(45)->second(0),
                'entry_method' => 'app_manual', 'status' => 'valid'
            ]);
        }

        // ==========================================
        // 18. GAMIFICATION
        // ==========================================
        $challengeStrength1 = Challenge::create([
            'gym_id' => $gym1->id, 'title' => 'Reto Fuerza Total G1', 'description' => 'Completa 10 rutinas de fuerza asignadas por tu entrenador.',
            'start_date' => Carbon::now()->subWeeks(2)->format('Y-m-d'), 'end_date' => Carbon::now()->addWeeks(2)->format('Y-m-d'), 'xp_reward' => 150, 'token_reward' => 50.00
        ]);
        $challengeCardio1 = Challenge::create([
            'gym_id' => $gym1->id, 'title' => 'Maratón de Spinning G1', 'description' => 'Asiste a 5 clases grupales de Spinning consecutivas.',
            'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'), 'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'), 'xp_reward' => 100, 'token_reward' => 30.00
        ]);
        $challengeKeto2 = Challenge::create([
            'gym_id' => $gym2->id, 'title' => 'Desafío Nutricional Keto G2', 'description' => 'Mantén tu plan alimentario keto por 7 días.',
            'start_date' => Carbon::now()->subDays(3)->format('Y-m-d'), 'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'), 'xp_reward' => 120, 'token_reward' => 40.00
        ]);

        UserChallenge::create(['user_id' => $user1->id, 'challenge_id' => $challengeStrength1->id, 'progress_value' => 6, 'status' => 'active']);
        UserChallenge::create(['user_id' => $user2->id, 'challenge_id' => $challengeStrength1->id, 'progress_value' => 10, 'status' => 'completed', 'completed_at' => Carbon::now()->subDays(2)]);

        $badgeWelcome = AchievementDefinition::create([
            'gym_id' => $gym1->id, 'name' => 'Bienvenida de Hierro', 'description' => 'Tu primer paso en la comunidad. Registra tu primer check-in.',
            'xp_reward' => 50, 'token_reward' => 10.00, 'icon_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop',
            'condition_type' => 'workouts_completed', 'target_value' => 1
        ]);
        $badgeConsistencia = AchievementDefinition::create([
            'gym_id' => $gym1->id, 'name' => 'Consistencia de Acero', 'description' => 'Completaste 10 check-ins consecutivos de asistencia.',
            'xp_reward' => 200, 'token_reward' => 50.00, 'icon_url' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?q=80&w=150&auto=format&fit=crop',
            'condition_type' => 'consecutive_days', 'target_value' => 10
        ]);

        UserAchievement::create(['user_id' => $user1->id, 'achievement_type' => 'workouts_completed', 'description' => '¡Bienvenida de Hierro! Registraste tu primer check-in.', 'achieved_at' => Carbon::now()->subWeeks(1)]);
        UserAchievement::create(['user_id' => $user2->id, 'achievement_type' => 'workouts_completed', 'description' => '¡Bienvenida de Hierro! Registraste tu primer check-in.', 'achieved_at' => Carbon::now()->subWeeks(2)]);

        UserGamificationStat::create(['user_id' => $user1->id, 'gym_id' => $gym1->id, 'total_xp' => 1250, 'current_level' => 2, 'token_balance' => 60.00, 'current_streak_days' => 3, 'longest_streak_days' => 5]);
        UserGamificationStat::create(['user_id' => $user2->id, 'gym_id' => $gym1->id, 'total_xp' => 2850, 'current_level' => 3, 'token_balance' => 110.00, 'current_streak_days' => 7, 'longest_streak_days' => 12]);
        UserGamificationStat::create(['user_id' => $user3->id, 'gym_id' => $gym2->id, 'total_xp' => 450, 'current_level' => 1, 'token_balance' => 0.00, 'current_streak_days' => 1, 'longest_streak_days' => 3]);
        UserGamificationStat::create(['user_id' => $user5->id, 'gym_id' => $gym2->id, 'total_xp' => 950, 'current_level' => 1, 'token_balance' => 25.00, 'current_streak_days' => 2, 'longest_streak_days' => 4]);

        // ==========================================
        // 19. SAAS PLAN MODULES (Pivot Mapping)
        // ==========================================
        DB::table('saas_plan_modules')->insert([
            ['plan_id' => 1, 'module_id' => 1],
            ['plan_id' => 2, 'module_id' => 1],
            ['plan_id' => 2, 'module_id' => 2],
            ['plan_id' => 2, 'module_id' => 3],
            ['plan_id' => 2, 'module_id' => 4],
            ['plan_id' => 3, 'module_id' => 1],
            ['plan_id' => 3, 'module_id' => 2],
            ['plan_id' => 3, 'module_id' => 3],
            ['plan_id' => 3, 'module_id' => 4],
            ['plan_id' => 3, 'module_id' => 5],
            ['plan_id' => 3, 'module_id' => 6],
        ]);

        // ==========================================
        // 20. RECIPE INGREDIENTS
        // ==========================================
        DB::table('recipe_ingredients')->insert([
            ['recipe_id' => $recipeBreakfast1->id, 'ingredient_id' => $ingHuevo->id, 'quantity' => 4.00, 'unit' => 'unit', 'notes' => 'Usar claras.'],
            ['recipe_id' => $recipeBreakfast1->id, 'ingredient_id' => $ingAvena->id, 'quantity' => 60.00, 'unit' => 'g', 'notes' => 'Mezclar.'],
            ['recipe_id' => $recipeLunch1->id, 'ingredient_id' => $ingPollo->id, 'quantity' => 150.00, 'unit' => 'g', 'notes' => 'A la plancha.'],
            ['recipe_id' => $recipeLunch1->id, 'ingredient_id' => $ingArroz->id, 'quantity' => 100.00, 'unit' => 'g', 'notes' => 'Hervido.'],
            ['recipe_id' => $recipeDinner1->id, 'ingredient_id' => $ingSalmon->id, 'quantity' => 150.00, 'unit' => 'g', 'notes' => 'Sin piel.'],
            ['recipe_id' => $recipeDinner1->id, 'ingredient_id' => $ingCamote->id, 'quantity' => 120.00, 'unit' => 'g', 'notes' => 'Al horno.'],
        ]);

        // ==========================================
        // 21. EXERCISE EQUIPMENT (Pivot Mapping)
        // ==========================================
        DB::table('exercise_equipment')->insert([
            ['exercise_id' => $exSquat1->id, 'equipment_id' => $eqSmith->id, 'is_optional' => 0],
            ['exercise_id' => $exPress1->id, 'equipment_id' => $eqPrensa->id, 'is_optional' => 0],
            ['exercise_id' => $exBench1->id, 'equipment_id' => $eqBench->id, 'is_optional' => 0],
            ['exercise_id' => $exBench1->id, 'equipment_id' => $eqBarra->id, 'is_optional' => 0],
            ['exercise_id' => $exPullups1->id, 'equipment_id' => $eqBarra->id, 'is_optional' => 1],
            ['exercise_id' => $exPushdown1->id, 'equipment_id' => $eqPolea->id, 'is_optional' => 0],
            ['exercise_id' => $exSquat2->id, 'equipment_id' => $eqKettlebell->id, 'is_optional' => 1],
            ['exercise_id' => $exBoxJump2->id, 'equipment_id' => $eqCajon->id, 'is_optional' => 0],
        ]);

        // ==========================================
        // 22. SESSION EXERCISES
        // ==========================================
        $session1 = WorkoutSession::where('user_id', $user1->id)->first();
        if ($session1) {
            DB::table('session_exercises')->insert([
                [
                    'session_id' => $session1->id, 'exercise_id' => $exSquat1->id, 'sets_completed' => 4,
                    'reps_completed' => '8,8,6,6', 'weight_kg' => 80.00, 'duration_seconds' => 300, 'notes' => 'RPE 8 en la última serie.'
                ]
            ]);
        }

        // ==========================================
        // 23. USER FOOD LOGS
        // ==========================================
        DB::table('user_food_logs')->insert([
            [
                'user_id' => $user1->id, 'gym_id' => $gym1->id, 'log_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'meal_type' => 'breakfast', 'recipe_id' => $recipeBreakfast1->id, 'custom_food_name' => null,
                'calories' => 450.00, 'protein_g' => 30.00, 'carbs_g' => 55.00, 'fat_g' => 10.00, 'createdAt' => Carbon::now()->subDays(1)
            ],
            [
                'user_id' => $user1->id, 'gym_id' => $gym1->id, 'log_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'meal_type' => 'snack', 'recipe_id' => null, 'custom_food_name' => 'Manzana verde con crema de cacahuate',
                'calories' => 200.00, 'protein_g' => 4.00, 'carbs_g' => 25.00, 'fat_g' => 8.00, 'createdAt' => Carbon::now()->subDays(1)
            ]
        ]);

        // ==========================================
        // 24. USER TRAINER ASSIGNMENTS
        // ==========================================
        DB::table('user_trainer_assignments')->insert([
            [
                'user_id' => $user1->id, 'trainer_id' => $trainer1->id, 'assigned_at' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(5)->format('Y-m-d'), 'is_active' => 1,
                'notes' => 'Asignación principal.', 'createdAt' => Carbon::now()->subMonths(1), 'updatedAt' => Carbon::now()->subMonths(1)
            ],
            [
                'user_id' => $user2->id, 'trainer_id' => $trainer1->id, 'assigned_at' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(5)->format('Y-m-d'), 'is_active' => 1,
                'notes' => 'Asignación secundaria.', 'createdAt' => Carbon::now()->subMonths(1), 'updatedAt' => Carbon::now()->subMonths(1)
            ]
        ]);

        // ==========================================
        // 25. FITNESS ASSESSMENTS
        // ==========================================
        DB::table('fitness_assessments')->insert([
            [
                'gym_id' => $gym1->id, 'user_id' => $user1->id, 'trainer_id' => $trainer1->id,
                'assessment_date' => Carbon::now()->subWeeks(3)->format('Y-m-d'), 'posture_notes' => 'Ligera hiperlordosis lumbar.',
                'flexibility_rating' => 'good', 'cardio_rating' => 'excellent', 'strength_notes' => 'Fuerza óptima.',
                'general_recommendations' => 'Focalizarse en core.', 'next_assessment_date' => Carbon::now()->addWeeks(5)->format('Y-m-d'),
                'createdAt' => Carbon::now()->subWeeks(3)
            ]
        ]);

        // ==========================================
        // 26. SATISFACTION SURVEYS
        // ==========================================
        DB::table('satisfaction_surveys')->insert([
            [
                'gym_id' => $gym1->id, 'user_id' => $user1->id, 'survey_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'rating' => 9, 'category' => 'general', 'feedback_text' => 'Excelente equipamiento e higiene.',
                'status' => 'resolved', 'createdAt' => Carbon::now()->subDays(3)
            ]
        ]);

        // ==========================================
        // 27. USER REFERRALS
        // ==========================================
        DB::table('user_referrals')->insert([
            [
                'gym_id' => $gym1->id, 'referrer_id' => $user2->id, 'referred_id' => $user1->id,
                'status' => 'completed', 'reward_granted' => 1, 'createdAt' => Carbon::now()->subWeeks(3), 'completedAt' => Carbon::now()->subWeeks(2)
            ]
        ]);

        // ==========================================
        // 28. MEMBERSHIP PAYMENTS
        // ==========================================
        $m_user1 = UserMembership::where('user_id', $user1->id)->first();
        if ($m_user1) {
            DB::table('membership_payments')->insert([
                'membership_id' => $m_user1->id, 'user_id' => $user1->id, 'promo_code_id' => null, 'amount' => 50.00,
                'currency' => 'USD', 'payment_method' => 'card', 'payment_date' => Carbon::now()->subDays(15),
                'reference_code' => 'PAY-XYZ-998877', 'received_by' => $adminUser1->id, 'notes' => 'Pago mensual.',
                'createdAt' => Carbon::now()->subDays(15), 'updatedAt' => Carbon::now()->subDays(15)
            ]);
        }

        // ==========================================
        // 29. GYM SUBSCRIPTIONS (SaaS Level Billing)
        // ==========================================
        DB::table('gym_subscriptions')->insert([
            [
                'gym_id' => $gym1->id, 'plan_id' => $planPro->id, 'start_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(1)->format('Y-m-d'), 'status' => 'active', 'payment_method' => 'Zelle',
                'reference_code' => 'TXN-SaaS-443322', 'createdAt' => Carbon::now()->subMonths(2)
            ]
        ]);

        // ==========================================
        // 30. ADMIN AUDIT LOGS
        // ==========================================
        DB::table('admin_audit_logs')->insert([
            [
                'gym_id' => $gym1->id, 'admin_id' => $adminUser1->id, 'action_type' => 'UPDATE', 'table_name' => 'membership_plans',
                'record_id' => '1', 'old_data' => json_encode(['price' => 45.00]), 'new_data' => json_encode(['price' => 50.00]),
                'ip_address' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)', 'createdAt' => Carbon::now()->subDays(4)
            ]
        ]);

        // ==========================================
        // 31. USER NOTIFICATIONS
        // ==========================================
        Notification::create(['user_id' => $user1->id, 'title' => '¡Nueva rutina asignada!', 'body' => 'Tienes una rutina de pierna lista.', 'type' => 'new_routine', 'is_read' => 0]);
        Notification::create(['user_id' => $user1->id, 'title' => '¡Logro desbloqueado!', 'body' => 'Has conseguido la medalla: Bienvenida de Hierro.', 'type' => 'achievement', 'is_read' => 1]);
        Notification::create(['user_id' => $user2->id, 'title' => 'Recordatorio de Pago', 'body' => 'Tu membresía vence en 5 días.', 'type' => 'payment_reminder', 'is_read' => 0]);
    }
}
