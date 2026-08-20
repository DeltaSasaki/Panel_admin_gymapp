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
        if (!Schema::hasTable('user_credit_logs')) {
            Schema::create('user_credit_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('gym_id')->nullable()->index();
                $table->integer('user_id')->index();
                $table->integer('membership_id')->nullable()->index();
                $table->integer('payment_id')->nullable()->index();
                $table->integer('received_by')->nullable()->comment('Null si fue auto-recarga desde la App Móvil');
                $table->string('source', 30)->default('admin_panel')->comment('admin_panel, mobile_app, web_gateway');
                $table->enum('type', ['abono_payment', 'credit_applied_to_plan', 'manual_adjustment'])->default('abono_payment');
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->string('payment_method', 50)->default('cash');
                $table->string('reference_code', 100)->nullable();
                $table->decimal('daily_rate', 10, 2)->nullable();
                $table->decimal('previous_credit', 10, 2)->default(0.00);
                $table->integer('days_added')->default(0);
                $table->decimal('credit_used', 10, 2)->default(0.00);
                $table->decimal('resulting_credit', 10, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_credit_logs');
    }
};
