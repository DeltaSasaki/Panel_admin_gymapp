<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to add all relational foreign keys for phpMyAdmin inspection.
     */
    public function up(): void
    {
        // 1. product_sales
        try {
            DB::statement("UPDATE product_sales SET user_id = NULL WHERE user_id = 0 OR user_id NOT IN (SELECT id FROM users)");
            DB::statement("ALTER TABLE `product_sales` ADD KEY IF NOT EXISTS `psale_gym_fk` (`gym_id`)");
            DB::statement("ALTER TABLE `product_sales` ADD KEY IF NOT EXISTS `psale_user_fk` (`user_id`)");
            DB::statement("ALTER TABLE `product_sales` ADD KEY IF NOT EXISTS `psale_sold_by_fk` (`sold_by`)");
            DB::statement("ALTER TABLE `product_sales` ADD CONSTRAINT `psale_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
            DB::statement("ALTER TABLE `product_sales` ADD CONSTRAINT `psale_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
            DB::statement("ALTER TABLE `product_sales` ADD CONSTRAINT `psale_sold_by_fk` FOREIGN KEY (`sold_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 2. membership_payments
        try {
            DB::statement("UPDATE membership_payments SET received_by = NULL WHERE received_by = 0 OR received_by NOT IN (SELECT id FROM users)");
            DB::statement("ALTER TABLE `membership_payments` ADD KEY IF NOT EXISTS `mpay_user_fk` (`user_id`)");
            DB::statement("ALTER TABLE `membership_payments` ADD KEY IF NOT EXISTS `mpay_received_by_fk` (`received_by`)");
            DB::statement("ALTER TABLE `membership_payments` ADD CONSTRAINT `mpay_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
            DB::statement("ALTER TABLE `membership_payments` ADD CONSTRAINT `mpay_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 3. user_memberships
        try {
            DB::statement("ALTER TABLE `user_memberships` ADD KEY IF NOT EXISTS `um_gym_fk` (`gym_id`)");
            DB::statement("ALTER TABLE `user_memberships` ADD CONSTRAINT `um_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 4. user_credit_logs
        if (Schema::hasTable('user_credit_logs')) {
            try {
                DB::statement("UPDATE user_credit_logs SET membership_id = NULL WHERE membership_id = 0 OR membership_id NOT IN (SELECT id FROM user_memberships)");
                DB::statement("UPDATE user_credit_logs SET payment_id = NULL WHERE payment_id = 0 OR payment_id NOT IN (SELECT id FROM membership_payments)");
                DB::statement("UPDATE user_credit_logs SET received_by = NULL WHERE received_by = 0 OR received_by NOT IN (SELECT id FROM users)");
                DB::statement("ALTER TABLE `user_credit_logs` ADD CONSTRAINT `ucl_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
                DB::statement("ALTER TABLE `user_credit_logs` ADD CONSTRAINT `ucl_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
                DB::statement("ALTER TABLE `user_credit_logs` ADD CONSTRAINT `ucl_membership_fk` FOREIGN KEY (`membership_id`) REFERENCES `user_memberships` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
                DB::statement("ALTER TABLE `user_credit_logs` ADD CONSTRAINT `ucl_payment_fk` FOREIGN KEY (`payment_id`) REFERENCES `membership_payments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
                DB::statement("ALTER TABLE `user_credit_logs` ADD CONSTRAINT `ucl_received_by_fk` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
            } catch (\Exception $e) {}
        }

        // 5. inventory_movements
        try {
            DB::statement("ALTER TABLE `inventory_movements` ADD KEY IF NOT EXISTS `im_performed_by_fk` (`performed_by`)");
            DB::statement("ALTER TABLE `inventory_movements` ADD CONSTRAINT `im_performed_by_fk` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 6. user_assigned_routines
        try {
            DB::statement("UPDATE user_assigned_routines SET assigned_by = NULL WHERE assigned_by = 0 OR assigned_by NOT IN (SELECT id FROM users)");
            DB::statement("ALTER TABLE `user_assigned_routines` ADD KEY IF NOT EXISTS `uar_assigned_by_fk` (`assigned_by`)");
            DB::statement("ALTER TABLE `user_assigned_routines` ADD CONSTRAINT `uar_assigned_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 7. user_meal_plans
        try {
            DB::statement("UPDATE user_meal_plans SET assigned_by = NULL WHERE assigned_by = 0 OR assigned_by NOT IN (SELECT id FROM users)");
            DB::statement("ALTER TABLE `user_meal_plans` ADD KEY IF NOT EXISTS `ump_assigned_by_fk` (`assigned_by`)");
            DB::statement("ALTER TABLE `user_meal_plans` ADD CONSTRAINT `ump_assigned_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 8. workout_routines
        try {
            DB::statement("UPDATE workout_routines SET created_by = NULL WHERE created_by = 0 OR created_by NOT IN (SELECT id FROM users)");
            DB::statement("ALTER TABLE `workout_routines` ADD KEY IF NOT EXISTS `wr_created_by_fk` (`created_by`)");
            DB::statement("ALTER TABLE `workout_routines` ADD CONSTRAINT `wr_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 9. workout_sessions
        try {
            DB::statement("UPDATE workout_sessions SET routine_day_id = NULL WHERE routine_day_id = 0 OR routine_day_id NOT IN (SELECT id FROM routine_days)");
            DB::statement("ALTER TABLE `workout_sessions` ADD KEY IF NOT EXISTS `ws_routine_day_fk` (`routine_day_id`)");
            DB::statement("ALTER TABLE `workout_sessions` ADD CONSTRAINT `ws_routine_day_fk` FOREIGN KEY (`routine_day_id`) REFERENCES `routine_days` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 10. challenges
        try {
            DB::statement("UPDATE challenges SET routine_id = NULL WHERE routine_id = 0 OR routine_id NOT IN (SELECT id FROM workout_routines)");
            DB::statement("UPDATE challenges SET exercise_id = NULL WHERE exercise_id = 0 OR exercise_id NOT IN (SELECT id FROM exercises)");
            DB::statement("UPDATE challenges SET badge_id = NULL WHERE badge_id = 0 OR badge_id NOT IN (SELECT id FROM achievement_definitions)");
            DB::statement("ALTER TABLE `challenges` ADD KEY IF NOT EXISTS `chal_routine_fk` (`routine_id`)");
            DB::statement("ALTER TABLE `challenges` ADD KEY IF NOT EXISTS `chal_exercise_fk` (`exercise_id`)");
            DB::statement("ALTER TABLE `challenges` ADD KEY IF NOT EXISTS `chal_badge_fk` (`badge_id`)");
            DB::statement("ALTER TABLE `challenges` ADD CONSTRAINT `chal_routine_fk` FOREIGN KEY (`routine_id`) REFERENCES `workout_routines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
            DB::statement("ALTER TABLE `challenges` ADD CONSTRAINT `chal_exercise_fk` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
            DB::statement("ALTER TABLE `challenges` ADD CONSTRAINT `chal_badge_fk` FOREIGN KEY (`badge_id`) REFERENCES `achievement_definitions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
        } catch (\Exception $e) {}

        // 11. user_gamification_stats
        try {
            DB::statement("ALTER TABLE `user_gamification_stats` ADD KEY IF NOT EXISTS `ugs_gym_fk` (`gym_id`)");
            DB::statement("ALTER TABLE `user_gamification_stats` ADD CONSTRAINT `ugs_gym_fk` FOREIGN KEY (`gym_id`) REFERENCES `gyms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down
    }
};
