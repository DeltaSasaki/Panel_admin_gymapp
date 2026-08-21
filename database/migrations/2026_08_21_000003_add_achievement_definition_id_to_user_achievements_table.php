<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_achievements')) {
            if (!Schema::hasColumn('user_achievements', 'achievement_definition_id')) {
                Schema::table('user_achievements', function (Blueprint $table) {
                    $table->integer('achievement_definition_id')->nullable()->after('user_id');
                    $table->index('achievement_definition_id');
                });
            }

            // Add Foreign Key Constraint if not already present
            try {
                Schema::table('user_achievements', function (Blueprint $table) {
                    $table->foreign('achievement_definition_id', 'fk_user_achievements_definition')
                        ->references('id')
                        ->on('achievement_definitions')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                });
            } catch (\Exception $e) {
                // Constraint may already exist
            }

            // Backfill existing user_achievements with their matching achievement_definition_id
            if (Schema::hasTable('achievement_definitions')) {
                $definitions = DB::table('achievement_definitions')->get();
                foreach ($definitions as $def) {
                    DB::table('user_achievements')
                        ->whereNull('achievement_definition_id')
                        ->where(function ($q) use ($def) {
                            $q->where('description', 'LIKE', "%{$def->name}%")
                              ->orWhere('achievement_type', $def->condition_type)
                              ->orWhere('achievement_type', $def->name);
                        })
                        ->update(['achievement_definition_id' => $def->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_achievements')) {
            if (Schema::hasColumn('user_achievements', 'achievement_definition_id')) {
                Schema::table('user_achievements', function (Blueprint $table) {
                    try {
                        $table->dropForeign('fk_user_achievements_definition');
                    } catch (\Exception $e) {}
                    
                    try {
                        $table->dropIndex(['achievement_definition_id']);
                    } catch (\Exception $e) {}

                    $table->dropColumn('achievement_definition_id');
                });
            }
        }
    }
};
