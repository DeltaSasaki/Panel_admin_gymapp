<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('cashiers')) {
            Schema::table('cashiers', function (Blueprint $table) {
                if (!Schema::hasColumn('cashiers', 'assigned_register')) {
                    $table->string('assigned_register', 50)->default('all')->after('shift');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cashiers')) {
            Schema::table('cashiers', function (Blueprint $table) {
                if (Schema::hasColumn('cashiers', 'assigned_register')) {
                    $table->dropColumn('assigned_register');
                }
            });
        }
    }
};
