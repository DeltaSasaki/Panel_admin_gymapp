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
        if (Schema::hasTable('exchange_rates')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                if (!Schema::hasColumn('exchange_rates', 'previous_rate')) {
                    $table->decimal('previous_rate', 12, 4)->nullable()->after('rate')->comment('Tasa inmediatamente anterior');
                }
                if (!Schema::hasColumn('exchange_rates', 'variation_percent')) {
                    $table->decimal('variation_percent', 6, 2)->default(0.00)->after('previous_rate')->comment('Variación porcentual respecto a la tasa anterior');
                }
                if (!Schema::hasColumn('exchange_rates', 'effective_at')) {
                    $table->dateTime('effective_at')->nullable()->after('effective_date')->comment('Fecha y hora exacta de entrada en vigencia');
                }
                if (!Schema::hasColumn('exchange_rates', 'change_type')) {
                    $table->enum('change_type', ['auto_job', 'manual_override', 'emergency_update'])->default('auto_job')->after('effective_at')->comment('Tipo de actualización: automática o manual');
                }
                if (!Schema::hasColumn('exchange_rates', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('updated_by')->comment('IP del usuario que realizó la modificación');
                }
                if (!Schema::hasColumn('exchange_rates', 'api_provider')) {
                    $table->string('api_provider', 80)->nullable()->after('ip_address')->comment('Proveedor de API de origen');
                }
                if (!Schema::hasColumn('exchange_rates', 'raw_payload')) {
                    $table->text('raw_payload')->nullable()->after('api_provider')->comment('Respuesta JSON pura de la API');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('exchange_rates')) {
            Schema::table('exchange_rates', function (Blueprint $table) {
                $columns = ['previous_rate', 'variation_percent', 'effective_at', 'change_type', 'ip_address', 'api_provider', 'raw_payload'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('exchange_rates', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
