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
        // 1. Tabla exchange_rates
        if (!Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('gym_id')->nullable()->index();
                $table->enum('rate_source', ['bcv', 'enparalelovzla', 'custom'])->default('bcv');
                $table->decimal('rate', 12, 4)->comment('Tasa de cambio VES por 1 USD (Factor)');
                $table->date('effective_date');
                $table->boolean('is_active')->default(true);
                $table->string('notes', 255)->nullable();
                $table->integer('updated_by')->nullable()->index();
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
            });
        }

        // 2. Columnas en gyms
        if (Schema::hasTable('gyms')) {
            Schema::table('gyms', function (Blueprint $table) {
                if (!Schema::hasColumn('gyms', 'dollar_rate')) {
                    $table->decimal('dollar_rate', 12, 4)->default(45.0000)->after('subscription_status')->comment('Tasa/Factor del dólar activa para el gimnasio');
                }
                if (!Schema::hasColumn('gyms', 'dollar_rate_type')) {
                    $table->enum('dollar_rate_type', ['bcv', 'enparalelovzla', 'custom'])->default('bcv')->after('dollar_rate');
                }
                if (!Schema::hasColumn('gyms', 'dollar_rate_updated_at')) {
                    $table->dateTime('dollar_rate_updated_at')->nullable()->after('dollar_rate_type');
                }
                if (!Schema::hasColumn('gyms', 'currency_symbol_primary')) {
                    $table->string('currency_symbol_primary', 5)->default('$')->after('dollar_rate_updated_at');
                }
                if (!Schema::hasColumn('gyms', 'currency_symbol_secondary')) {
                    $table->string('currency_symbol_secondary', 5)->default('Bs.')->after('currency_symbol_primary');
                }
            });
        }

        // 3. Columnas en membership_payments
        if (Schema::hasTable('membership_payments')) {
            Schema::table('membership_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('membership_payments', 'amount_ves')) {
                    $table->decimal('amount_ves', 14, 2)->default(0.00)->after('amount')->comment('Monto equivalente o pagado en Bolívares VES');
                }
                if (!Schema::hasColumn('membership_payments', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 12, 4)->default(1.0000)->after('amount_ves')->comment('Tasa/Factor de cambio USD/VES aplicada al pagar');
                }
                if (!Schema::hasColumn('membership_payments', 'payment_currency')) {
                    $table->enum('payment_currency', ['USD', 'VES', 'EUR', 'USDT'])->default('USD')->after('currency')->comment('Moneda física/digital en la que se efectuó el pago');
                }
            });
        }

        // 4. Columnas en product_sales
        if (Schema::hasTable('product_sales')) {
            Schema::table('product_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('product_sales', 'total_amount_ves')) {
                    $table->decimal('total_amount_ves', 14, 2)->default(0.00)->after('total_amount')->comment('Total cobrado en Bolívares VES');
                }
                if (!Schema::hasColumn('product_sales', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 12, 4)->default(1.0000)->after('total_amount_ves')->comment('Tasa/Factor del dólar aplicada en la venta');
                }
                if (!Schema::hasColumn('product_sales', 'payment_currency')) {
                    $table->enum('payment_currency', ['USD', 'VES', 'EUR', 'USDT'])->default('USD')->after('payment_method')->comment('Moneda de cobro');
                }
            });
        }

        // 5. Columnas en sale_items
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_items', 'unit_price_ves')) {
                    $table->decimal('unit_price_ves', 14, 2)->default(0.00)->after('unit_price');
                }
                if (!Schema::hasColumn('sale_items', 'subtotal_ves')) {
                    $table->decimal('subtotal_ves', 14, 2)->default(0.00)->after('subtotal');
                }
            });
        }

        // 6. Columnas en user_credit_logs
        if (Schema::hasTable('user_credit_logs')) {
            Schema::table('user_credit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('user_credit_logs', 'amount_ves')) {
                    $table->decimal('amount_ves', 14, 2)->default(0.00)->after('amount')->comment('Monto equivalente o pagado en Bolívares VES');
                }
                if (!Schema::hasColumn('user_credit_logs', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 12, 4)->nullable()->after('amount_ves')->comment('Tasa/Factor USD/VES aplicada al abono');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_credit_logs')) {
            Schema::table('user_credit_logs', function (Blueprint $table) {
                $table->dropColumn(['amount_ves', 'exchange_rate']);
            });
        }

        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn(['unit_price_ves', 'subtotal_ves']);
            });
        }

        if (Schema::hasTable('product_sales')) {
            Schema::table('product_sales', function (Blueprint $table) {
                $table->dropColumn(['total_amount_ves', 'exchange_rate', 'payment_currency']);
            });
        }

        if (Schema::hasTable('membership_payments')) {
            Schema::table('membership_payments', function (Blueprint $table) {
                $table->dropColumn(['amount_ves', 'exchange_rate', 'payment_currency']);
            });
        }

        if (Schema::hasTable('gyms')) {
            Schema::table('gyms', function (Blueprint $table) {
                $table->dropColumn([
                    'dollar_rate',
                    'dollar_rate_type',
                    'dollar_rate_updated_at',
                    'currency_symbol_primary',
                    'currency_symbol_secondary'
                ]);
            });
        }

        Schema::dropIfExists('exchange_rates');
    }
};
