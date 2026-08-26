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
        // 1. gym_classes columns
        if (Schema::hasTable('gym_classes')) {
            Schema::table('gym_classes', function (Blueprint $table) {
                if (!Schema::hasColumn('gym_classes', 'category_type')) {
                    $table->string('category_type', 30)->default('clase')->after('description');
                }
                if (!Schema::hasColumn('gym_classes', 'location')) {
                    $table->string('location', 150)->nullable()->after('capacity');
                }
            });
        }

        // 2. achievement_definitions columns
        if (Schema::hasTable('achievement_definitions')) {
            Schema::table('achievement_definitions', function (Blueprint $table) {
                if (!Schema::hasColumn('achievement_definitions', 'is_active')) {
                    $table->boolean('is_active')->default(1)->after('target_value');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gym_classes')) {
            Schema::table('gym_classes', function (Blueprint $table) {
                $dropCols = [];
                if (Schema::hasColumn('gym_classes', 'category_type')) {
                    $dropCols[] = 'category_type';
                }
                if (Schema::hasColumn('gym_classes', 'location')) {
                    $dropCols[] = 'location';
                }
                if (!empty($dropCols)) {
                    $table->dropColumn($dropCols);
                }
            });
        }

        if (Schema::hasTable('achievement_definitions')) {
            Schema::table('achievement_definitions', function (Blueprint $table) {
                if (Schema::hasColumn('achievement_definitions', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
