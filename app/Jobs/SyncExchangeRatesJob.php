<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Log;

class SyncExchangeRatesJob implements ShouldQueue
{
    use Queueable;

    protected $gymId;

    /**
     * Create a new job instance.
     */
    public function __construct($gymId = null)
    {
        $this->gymId = $gymId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Iniciando SyncExchangeRatesJob para sucursal: " . ($this->gymId ?: 'Global'));
        $result = ExchangeRateService::syncFromBcvApi($this->gymId);

        if ($result['success']) {
            Log::info("SyncExchangeRatesJob completado con éxito: " . $result['message']);
        } else {
            Log::error("SyncExchangeRatesJob falló: " . $result['message']);
        }
    }
}
