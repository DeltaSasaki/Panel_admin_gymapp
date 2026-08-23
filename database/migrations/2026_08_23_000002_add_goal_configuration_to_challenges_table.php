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
        if (Schema::hasTable('challenges')) {
            Schema::table('challenges', function (Blueprint $table) {
                if (!Schema::hasColumn('challenges', 'goal_type')) {
                    $table->enum('goal_type', ['routine', 'exercise', 'attendance', 'custom'])->default('custom')->after('description');
                }
                if (!Schema::hasColumn('challenges', 'routine_id')) {
                    $table->integer('routine_id')->unsigned()->nullable()->after('goal_type');
                }
                if (!Schema::hasColumn('challenges', 'exercise_id')) {
                    $table->integer('exercise_id')->unsigned()->nullable()->after('routine_id');
                }
                if (!Schema::hasColumn('challenges', 'target_value')) {
                    $table->integer('target_value')->default(1)->after('exercise_id');
                }
                if (!Schema::hasColumn('challenges', 'target_unit')) {
                    $table->string('target_unit', 50)->default('sesiones')->after('target_value');
                }
                if (!Schema::hasColumn('challenges', 'badge_id')) {
                    $table->integer('badge_id')->unsigned()->nullable()->after('token_reward');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('challenges')) {
            Schema::table('challenges', function (Blueprint $table) {
                $columns = ['goal_type', 'routine_id', 'exercise_id', 'target_value', 'target_unit', 'badge_id'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('challenges', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
