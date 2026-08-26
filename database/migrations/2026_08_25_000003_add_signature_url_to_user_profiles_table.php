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
        if (Schema::hasTable('user_profiles')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('user_profiles', 'signature_url')) {
                    $table->text('signature_url')->nullable()->after('profile_photo');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_profiles')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('user_profiles', 'signature_url')) {
                    $table->dropColumn('signature_url');
                }
            });
        }
    }
};
