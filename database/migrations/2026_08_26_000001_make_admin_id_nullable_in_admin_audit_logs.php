<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('admin_audit_logs')) {
            try {
                // Modify admin_id to allow NULL for automated background jobs and system crons
                DB::statement("ALTER TABLE `admin_audit_logs` MODIFY `admin_id` INT(11) NULL DEFAULT NULL COMMENT 'Quién hizo el cambio (NULL si fue el sistema/cron)'");
            } catch (\Exception $e) {
                // Fallback in case table or column state differs
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admin_audit_logs')) {
            try {
                DB::statement("UPDATE `admin_audit_logs` SET `admin_id` = 1 WHERE `admin_id` IS NULL");
                DB::statement("ALTER TABLE `admin_audit_logs` MODIFY `admin_id` INT(11) NOT NULL COMMENT 'Quién hizo el cambio'");
            } catch (\Exception $e) {}
        }
    }
};
