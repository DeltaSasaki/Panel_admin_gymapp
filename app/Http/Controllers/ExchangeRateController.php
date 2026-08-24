<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRate;
use App\Models\Gym;
use App\Services\ExchangeRateService;
use App\Models\AdminAuditLog;
use Carbon\Carbon;

class ExchangeRateController extends Controller
{
    /**
     * Check authorization: only admin and superadmin can manage exchange rates.
     */
    private function checkPermission()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Acceso Denegado. Solo administradores pueden gestionar la tasa de cambio.');
        }
    }

    /**
     * Display Exchange Rate console, live rate badge and change history table.
     */
    public function index(Request $request)
    {
        $this->checkPermission();
        $gymId = $this->getActiveGymId();

        $activeGym = ($gymId !== 'all') ? Gym::find($gymId) : null;
        $currentRate = ExchangeRateService::getCurrentRate($gymId);
        $activeRecord = ExchangeRateService::getActiveRecord($gymId);

        // Historical query
        $query = ExchangeRate::with(['gym', 'updater.profile'])
            ->orderBy('id', 'desc');

        if ($gymId !== 'all') {
            $query->where(function ($q) use ($gymId) {
                $q->where('gym_id', $gymId)->orWhereNull('gym_id');
            });
        }

        if ($request->filled('source') && in_array($request->source, ['bcv', 'enparalelovzla', 'custom'])) {
            $query->where('rate_source', $request->source);
        }

        if ($request->filled('type') && in_array($request->type, ['auto_job', 'manual_override', 'emergency_update'])) {
            $query->where('change_type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('effective_date', $request->date);
        }

        $history = $query->paginate(15)->withQueryString();

        // Metrics
        $totalChangesCount = (clone $query)->count();
        $manualChangesCount = (clone $query)->where('change_type', 'manual_override')->count();
        $autoChangesCount = (clone $query)->where('change_type', 'auto_job')->count();

        // Sparkline & Range Telemetry
        $recentRates = ExchangeRate::orderBy('id', 'desc')->take(7)->pluck('rate')->reverse()->values()->all();
        if (count($recentRates) < 2) {
            $recentRates = [round($currentRate * 0.995, 4), round($currentRate * 0.998, 4), round($currentRate, 4)];
        }
        $minRate = min($recentRates);
        $maxRate = max($recentRates);

        return view('finanzas.tasas_cambio', compact(
            'currentRate',
            'activeRecord',
            'activeGym',
            'history',
            'gymId',
            'totalChangesCount',
            'manualChangesCount',
            'autoChangesCount',
            'recentRates',
            'minRate',
            'maxRate'
        ));
    }

    /**
     * Trigger immediate synchronization from BCV API.
     */
    public function syncNow(Request $request)
    {
        $this->checkPermission();
        $gymId = $this->getActiveGymId();
        $targetGymId = ($gymId !== 'all') ? (int)$gymId : null;

        $result = ExchangeRateService::syncFromBcvApi($targetGymId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Store manual exchange rate adjustment with notes and user tracking.
     */
    public function storeManual(Request $request)
    {
        $this->checkPermission();
        $request->validate([
            'rate' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string|max:255',
            'apply_to_all_gyms' => 'nullable|boolean',
        ]);

        $gymId = $this->getActiveGymId();
        $targetGymId = ($gymId !== 'all') ? (int)$gymId : null;

        if ($request->boolean('apply_to_all_gyms') && auth()->user()->role === 'superadmin') {
            $targetGymId = null;
        }

        $record = ExchangeRateService::setManualRate(
            (float)$request->rate,
            $request->notes,
            $targetGymId,
            auth()->id(),
            $request->ip()
        );

        $formatted = number_format($record->rate, 4, ',', '.');
        $msg = "¡Factor cambiario actualizado manualmente a Bs. {$formatted} por 1 USD!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'record' => $record,
                'rate' => $record->rate,
                'rate_formatted' => 'Bs. ' . $formatted,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Update gym exchange rate configuration mode (BCV automatic vs Custom rate).
     */
    public function updateConfig(Request $request)
    {
        $this->checkPermission();
        $request->validate([
            'dollar_rate_type' => 'required|in:bcv,custom',
            'custom_rate' => 'nullable|numeric|min:0.0001',
        ]);

        $gymId = $this->getActiveGymId();
        if ($gymId === 'all') {
            return redirect()->back()->withErrors(['error' => 'Selecciona una sucursal específica para configurar su modo de tasa.']);
        }

        $gym = Gym::findOrFail($gymId);

        if ($request->dollar_rate_type === 'bcv') {
            $bcvRate = ExchangeRateService::getCurrentRate(null); // global BCV rate
            $gym->update([
                'dollar_rate_type' => 'bcv',
                'dollar_rate' => $bcvRate,
                'dollar_rate_updated_at' => Carbon::now(),
            ]);
            $msg = "La sucursal {$gym->name} ahora está configurada para sincronizarse automáticamente con la Tasa Oficial del BCV.";
        } else {
            $customRate = (float)($request->custom_rate ?: $gym->dollar_rate);
            ExchangeRateService::setManualRate($customRate, 'Activación de tasa personalizada para sucursal', $gym->id);
            $gym->update([
                'dollar_rate_type' => 'custom',
                'dollar_rate' => $customRate,
                'dollar_rate_updated_at' => Carbon::now(),
            ]);
            $msg = "La sucursal {$gym->name} ahora está configurada con Tasa Personalizada fija.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Live API Endpoint: Return current active exchange rate for AJAX (POS, calculators).
     */
    public function apiCurrentRate(Request $request)
    {
        $gymId = $request->query('gym_id', $this->getActiveGymId());
        $rate = ExchangeRateService::getCurrentRate($gymId);
        $activeRecord = ExchangeRateService::getActiveRecord($gymId);

        return response()->json([
            'success' => true,
            'rate' => $rate,
            'rate_formatted' => 'Bs. ' . number_format($rate, 4, ',', '.'),
            'rate_formatted_short' => 'Bs. ' . number_format($rate, 2, ',', '.'),
            'source' => $activeRecord ? $activeRecord->rate_source : 'bcv',
            'source_label' => $activeRecord ? $activeRecord->source_label : 'BCV Oficial',
            'variation_percent' => $activeRecord ? (float)$activeRecord->variation_percent : 0.00,
            'effective_date' => $activeRecord ? Carbon::parse($activeRecord->effective_date)->format('d/m/Y') : Carbon::today()->format('d/m/Y'),
            'last_updated' => $activeRecord ? Carbon::parse($activeRecord->updatedAt)->format('d/m/Y H:i') : Carbon::now()->format('d/m/Y H:i'),
        ]);
    }
}
