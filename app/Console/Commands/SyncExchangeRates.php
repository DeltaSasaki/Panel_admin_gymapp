<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExchangeRateService;

class SyncExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:sync-rates {--source=bcv : Fuente de la tasa (bcv)} {--gym= : ID de la sucursal específica}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza la tasa de cambio oficial del BCV para Venezuela desde las APIs públicas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando sincronización de tasa de cambio oficial BCV...');

        $gymId = $this->option('gym');
        $result = ExchangeRateService::syncFromBcvApi($gymId);

        if ($result['success']) {
            $this->info("✔ {$result['message']}");
            $this->table(
                ['Tasa Actual', 'Tasa Anterior', 'Variación', 'Fecha Efectiva', 'Proveedor API'],
                [[
                    'Bs. ' . number_format($result['rate'], 4, ',', '.'),
                    $result['previous_rate'] ? 'Bs. ' . number_format($result['previous_rate'], 4, ',', '.') : 'N/A',
                    ($result['variation_percent'] >= 0 ? '+' : '') . $result['variation_percent'] . '%',
                    $result['effective_date'],
                    $result['api_provider'],
                ]]
            );
            return Command::SUCCESS;
        }

        $this->error("✖ Error: {$result['message']}");
        return Command::FAILURE;
    }
}
