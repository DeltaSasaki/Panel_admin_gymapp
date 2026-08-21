<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance_logs')) {
            DB::statement("ALTER TABLE `attendance_logs` MODIFY COLUMN `entry_method` ENUM('biometric', 'app_manual', 'rfid', 'admin', 'qr') DEFAULT 'app_manual'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendance_logs')) {
            DB::statement("ALTER TABLE `attendance_logs` MODIFY COLUMN `entry_method` ENUM('biometric', 'app_manual', 'rfid', 'admin') DEFAULT 'biometric'");
        }
    }
};
