<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks for clean truncation across all tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'saas_subscription_plans',
            'saas_modules',
            'saas_plan_modules',
            'gyms',
            'gym_subscriptions',
            'gym_promotions',
            'users',
            'user_profiles',
            'trainers',
            'user_trainer_assignments',
            'membership_plans',
            'user_memberships',
            'membership_payments',
            'promo_codes',
            'exercise_categories',
            'equipment',
            'exercises',
            'exercise_equipment',
            'workout_routines',
            'routine_days',
            'routine_exercises',
            'user_assigned_routines',
            'workout_sessions',
            'session_exercises',
            'recipe_categories',
            'ingredients',
            'recipes',
            'recipe_ingredients',
            'meal_plans',
            'meal_plan_days',
            'user_meal_plans',
            'user_food_logs',
            'body_measurements',
            'user_goals',
            'fitness_assessments',
            'user_medical_notes',
            'gym_classes',
            'class_schedules',
            'class_bookings',
            'attendance_logs',
            'product_categories',
            'inventory_products',
            'inventory_movements',
            'product_sales',
            'sale_items',
            'satisfaction_surveys',
            'user_referrals',
            'achievement_definitions',
            'user_achievements',
            'user_gamification_stats',
            'challenges',
            'user_challenges',
            'notifications',
            'admin_audit_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Execute modular seeders in sequential relational order
        $this->call([
            SaasAndGymSeeder::class,
            UserAndStaffSeeder::class,
            MembershipAndBillingSeeder::class,
            ExerciseAndRoutineSeeder::class,
            NutritionSeeder::class,
            ClassAndAttendanceSeeder::class,
            StoreAndInventorySeeder::class,
            GamificationAndSocialSeeder::class,
            HealthAndAssessmentSeeder::class,
        ]);
    }
}
