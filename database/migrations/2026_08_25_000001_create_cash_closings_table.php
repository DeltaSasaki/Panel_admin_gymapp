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
        if (!Schema::hasTable('cash_closings')) {
            Schema::create('cash_closings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('gym_id');
                $table->integer('cashier_id')->nullable();
                $table->integer('closed_by');
                $table->date('closing_date');
                $table->enum('register_type', ['all', 'memberships', 'pos'])->default('all')->comment('Caja 1: memberships, Caja 2: pos, Consolidado: all');
                $table->decimal('exchange_rate', 12, 4)->default(1.0000)->comment('Tasa de cambio USD/VES congelada en el cierre');
                
                // Totales Consolidados
                $table->decimal('total_usd', 12, 2)->default(0.00);
                $table->decimal('total_ves', 14, 2)->default(0.00);
                
                // Desglose por Método de Pago en USD
                $table->decimal('cash_usd', 12, 2)->default(0.00);
                $table->decimal('card_usd', 12, 2)->default(0.00);
                $table->decimal('transfer_usd', 12, 2)->default(0.00);
                $table->decimal('other_usd', 12, 2)->default(0.00);
                
                // Desglose por Método de Pago en VES (Bs.)
                $table->decimal('cash_ves', 14, 2)->default(0.00);
                $table->decimal('card_ves', 14, 2)->default(0.00);
                $table->decimal('transfer_ves', 14, 2)->default(0.00);
                $table->decimal('other_ves', 14, 2)->default(0.00);
                
                // Arqueo Físico de Gaveta en USD
                $table->decimal('expected_cash_usd', 12, 2)->default(0.00);
                $table->decimal('actual_cash_usd', 12, 2)->default(0.00);
                $table->decimal('difference_usd', 12, 2)->default(0.00);
                
                // Arqueo Físico de Gaveta en VES (Bs.)
                $table->decimal('expected_cash_ves', 14, 2)->default(0.00);
                $table->decimal('actual_cash_ves', 14, 2)->default(0.00);
                $table->decimal('difference_ves', 14, 2)->default(0.00);
                
                // Conteos de Operaciones
                $table->integer('memberships_count')->default(0);
                $table->integer('sales_count')->default(0);
                
                $table->enum('status', ['open', 'closed'])->default('closed');
                $table->text('notes')->nullable();
                $table->dateTime('closed_at')->nullable();
                $table->dateTime('createdAt')->nullable();
                $table->dateTime('updatedAt')->nullable();

                $table->index(['gym_id', 'closing_date', 'register_type'], 'idx_closing_lookup');
                $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('closed_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closings');
    }
};
