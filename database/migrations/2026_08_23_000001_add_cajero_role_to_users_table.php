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
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('member', 'trainer', 'admin', 'superadmin', 'cajero') DEFAULT 'member'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('member', 'trainer', 'admin', 'superadmin') DEFAULT 'member'");
        }
    }
};
