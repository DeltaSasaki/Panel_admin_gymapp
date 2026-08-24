<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Gym;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExchangeRateService
{
    /**
     * Get the active exchange rate value (Factor VES per 1 USD) for a specific gym or global context.
     */
    public static function getCurrentRate($gymId = null): float
    {
        // 1. If gym ID provided, check if gym has specific custom rate configured
        if (!empty($gymId) && $gymId !== 'all') {
            $gym = Gym::find($gymId);
            if ($gym && $gym->dollar_rate_type === 'custom' && $gym->dollar_rate > 0) {
                return (float)$gym->dollar_rate;
            }

            // Check if there is an active custom ExchangeRate record for this gym
            $gymRateRecord = ExchangeRate::where('gym_id', $gymId)
                ->where('is_active', 1)
                ->latest('id')
                ->first();

            if ($gymRateRecord && $gymRateRecord->rate > 0) {
                return (float)$gymRateRecord->rate;
            }
        }

        // 2. Global active ExchangeRate record (rate_source = 'bcv' or general)
        $globalRecord = ExchangeRate::whereNull('gym_id')
            ->where('is_active', 1)
            ->latest('id')
            ->first();

        if ($globalRecord && $globalRecord->rate > 0) {
            return (float)$globalRecord->rate;
        }

        // 3. Fallback from any Gym record or configuration default
        if (!empty($gymId) && $gymId !== 'all' && isset($gym) && $gym->dollar_rate > 0) {
            return (float)$gym->dollar_rate;
        }

        return (float)config('app.default_dollar_rate', 45.0000);
    }

    /**
     * Get the full active ExchangeRate record model.
     */
    public static function getActiveRecord($gymId = null): ?ExchangeRate
    {
        if (!empty($gymId) && $gymId !== 'all') {
            $gymRate = ExchangeRate::where('gym_id', $gymId)
                ->where('is_active', 1)
                ->latest('id')
                ->first();
            if ($gymRate) {
                return $gymRate;
            }
        }

        return ExchangeRate::whereNull('gym_id')
            ->where('is_active', 1)
            ->latest('id')
            ->first();
    }

    /**
     * Convert an amount in USD to Bolívares (VES).
     */
    public static function toVES($amountUSD, $gymId = null, int $decimals = 2): float
    {
        $rate = self::getCurrentRate($gymId);
        return round(((float)$amountUSD) * $rate, $decimals);
    }

    /**
     * Convert an amount in Bolívares (VES) to USD.
     */
    public static function toUSD($amountVES, $gymId = null, int $decimals = 2): float
    {
        $rate = self::getCurrentRate($gymId);
        if ($rate <= 0) return 0.00;
        return round(((float)$amountVES) / $rate, $decimals);
    }

    /**
     * Format Bolívares amount as commercial string: "Bs. 1.250,00".
     */
    public static function formatVES($amountVES): string
    {
        return 'Bs. ' . number_format((float)$amountVES, 2, ',', '.');
    }

    /**
     * Format USD amount as commercial string: "$10.00".
     */
    public static function formatUSD($amountUSD): string
    {
        return '$' . number_format((float)$amountUSD, 2, '.', ',');
    }

    /**
     * Format dual price ready for UI: "$10.00 (Bs. 450,00)".
     */
    public static function formatDual($amountUSD, $gymId = null): string
    {
        $ves = self::toVES($amountUSD, $gymId);
        return self::formatUSD($amountUSD) . ' (' . self::formatVES($ves) . ')';
    }

    /**
     * Synchronize official rate from Venezuelan Public BCV APIs.
     * Primary: ve.dolarapi.com | Fallback: pydolarve.org
     */
    public static function syncFromBcvApi($targetGymId = null): array
    {
        $newRate = null;
        $apiProvider = null;
        $rawPayload = null;
        $effectiveDate = Carbon::today()->toDateString();
        $effectiveAt = Carbon::now();

        // 1. Primary API: DolarApi Oficial VE
        try {
            $response = Http::timeout(10)->get('https://ve.dolarapi.com/v1/dolares/oficial');
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['promedio']) && is_numeric($data['promedio']) && $data['promedio'] > 0) {
                    $newRate = (float)$data['promedio'];
                    $apiProvider = 'DolarApi_Oficial_VE';
                    $rawPayload = $response->body();
                    if (isset($data['fechaActualizacion'])) {
                        try {
                            $effectiveAt = Carbon::parse($data['fechaActualizacion']);
                            $effectiveDate = $effectiveAt->toDateString();
                        } catch (\Exception $e) {}
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Primary BCV API failed: " . $e->getMessage());
        }

        // 2. Secondary API Fallback: PyDolarVE BCV
        if (!$newRate) {
            try {
                $response = Http::timeout(10)->get('https://pydolarve.org/api/v1/dollar?page=bcv');
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['monitors']['usd']['price']) && is_numeric($data['monitors']['usd']['price'])) {
                        $newRate = (float)$data['monitors']['usd']['price'];
                        $apiProvider = 'PyDolarVE_BCV';
                        $rawPayload = $response->body();
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Secondary BCV API fallback failed: " . $e->getMessage());
            }
        }

        // 3. Third API Fallback: CriptoYA VE
        if (!$newRate) {
            try {
                $response = Http::timeout(10)->get('https://criptoya.com/api/dolar');
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['oficial']['price']) && is_numeric($data['oficial']['price'])) {
                        $newRate = (float)$data['oficial']['price'];
                        $apiProvider = 'CriptoYA_Oficial_VE';
                        $rawPayload = $response->body();
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Third BCV API fallback failed: " . $e->getMessage());
            }
        }

        if (!$newRate || $newRate <= 0) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener la tasa desde los servicios de API del BCV en este momento.',
            ];
        }

        // Fetch current active rate to compute variation
        $currentActive = self::getActiveRecord($targetGymId);
        $previousRate = $currentActive ? (float)$currentActive->rate : null;
        $variationPercent = 0.00;

        if ($previousRate && $previousRate > 0) {
            $variationPercent = round((($newRate - $previousRate) / $previousRate) * 100, 2);
        }

        // Deactivate previous active rates for this scope
        if ($targetGymId) {
            ExchangeRate::where('gym_id', $targetGymId)->update(['is_active' => 0]);
        } else {
            ExchangeRate::whereNull('gym_id')->update(['is_active' => 0]);
        }

        // Create new active exchange rate record
        $record = ExchangeRate::create([
            'gym_id' => $targetGymId,
            'rate_source' => 'bcv',
            'rate' => $newRate,
            'previous_rate' => $previousRate,
            'variation_percent' => $variationPercent,
            'effective_date' => $effectiveDate,
            'effective_at' => $effectiveAt,
            'change_type' => 'auto_job',
            'notes' => 'Actualización automática sincronizada con API Oficial BCV',
            'updated_by' => auth()->check() ? auth()->id() : null,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'api_provider' => $apiProvider,
            'raw_payload' => $rawPayload,
            'is_active' => 1,
        ]);

        // Sync dollar_rate in gyms table
        if ($targetGymId) {
            Gym::where('id', $targetGymId)->update([
                'dollar_rate' => $newRate,
                'dollar_rate_type' => 'bcv',
                'dollar_rate_updated_at' => Carbon::now(),
            ]);
        } else {
            Gym::where('dollar_rate_type', 'bcv')->orWhereNull('dollar_rate_type')->update([
                'dollar_rate' => $newRate,
                'dollar_rate_updated_at' => Carbon::now(),
            ]);
        }

        // Audit Log
        AdminAuditLog::record(
            'INSERT',
            'exchange_rates',
            $record->id,
            $currentActive ? $currentActive->toArray() : null,
            $record->toArray(),
            $targetGymId
        );

        return [
            'success' => true,
            'message' => "Tasa BCV sincronizada exitosamente: 1 USD = {$newRate} Bs.",
            'rate' => $newRate,
            'previous_rate' => $previousRate,
            'variation_percent' => $variationPercent,
            'api_provider' => $apiProvider,
            'effective_date' => $effectiveDate,
            'record' => $record,
        ];
    }

    /**
     * Manually set/override the exchange rate with full audit logging.
     */
    public static function setManualRate(float $newRate, ?string $notes = null, $gymId = null, $userId = null, ?string $ip = null): ExchangeRate
    {
        $targetGymId = (!empty($gymId) && $gymId !== 'all') ? (int)$gymId : null;
        $userId = $userId ?? (auth()->check() ? auth()->id() : null);
        $ip = $ip ?? (request()->ip() ?? '127.0.0.1');

        $currentActive = self::getActiveRecord($targetGymId);
        $previousRate = $currentActive ? (float)$currentActive->rate : null;
        $variationPercent = 0.00;

        if ($previousRate && $previousRate > 0) {
            $variationPercent = round((($newRate - $previousRate) / $previousRate) * 100, 2);
        }

        // Deactivate previous active rates for this scope
        if ($targetGymId) {
            ExchangeRate::where('gym_id', $targetGymId)->update(['is_active' => 0]);
        } else {
            ExchangeRate::whereNull('gym_id')->update(['is_active' => 0]);
        }

        $record = ExchangeRate::create([
            'gym_id' => $targetGymId,
            'rate_source' => 'custom',
            'rate' => $newRate,
            'previous_rate' => $previousRate,
            'variation_percent' => $variationPercent,
            'effective_date' => Carbon::today()->toDateString(),
            'effective_at' => Carbon::now(),
            'change_type' => 'manual_override',
            'notes' => $notes ?: 'Ajuste manual del factor cambiario',
            'updated_by' => $userId,
            'ip_address' => $ip,
            'api_provider' => 'manual_admin',
            'raw_payload' => null,
            'is_active' => 1,
        ]);

        // Sync gym record
        if ($targetGymId) {
            Gym::where('id', $targetGymId)->update([
                'dollar_rate' => $newRate,
                'dollar_rate_type' => 'custom',
                'dollar_rate_updated_at' => Carbon::now(),
            ]);
        } else {
            Gym::where('dollar_rate_type', 'custom')->update([
                'dollar_rate' => $newRate,
                'dollar_rate_updated_at' => Carbon::now(),
            ]);
        }

        AdminAuditLog::record(
            'UPDATE',
            'exchange_rates',
            $record->id,
            $currentActive ? $currentActive->toArray() : null,
            $record->toArray(),
            $targetGymId
        );

        return $record;
    }
}
