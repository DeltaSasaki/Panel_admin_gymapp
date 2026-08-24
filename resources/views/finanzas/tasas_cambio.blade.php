@extends('layouts.admin')

@section('title', 'Tasa de Cambio y Factor VES')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-widest bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-full">
                    Venezuela • Multimoneda USD / VES
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-100 mt-1">Factor y Tasa de Cambio del Día</h1>
            <p class="text-xs md:text-sm text-slate-400">
                Administración del factor de conversión oficial y registro histórico de variaciones cambiarias.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" 
                    id="btn-sync-bcv"
                    onclick="triggerBcvSync()" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-850 text-slate-200 hover:text-white rounded-xl border border-slate-800 hover:border-slate-700 text-xs font-bold transition-all shadow-sm flex items-center gap-2 cursor-pointer">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-lime-400" id="sync-icon"></i>
                <span id="sync-text">Sincronizar BCV Ahora</span>
            </button>

            <button type="button" 
                    onclick="window.toggleModal('modal-manual-rate')" 
                    class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 rounded-xl text-xs font-extrabold transition-all shadow-md shadow-lime-500/20 flex items-center gap-2 cursor-pointer">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>Ajustar Tasa Manual</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards & Live Converter -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Card 1: Active Rate Widget -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 relative overflow-hidden group card-hover-effect">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-lime-500/5 rounded-full blur-2xl group-hover:bg-lime-500/10 transition-all"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="coins" class="w-4 h-4 text-lime-400"></i> Tasa Activa Vigente
                </span>
                @if($activeRecord)
                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md border {{ $activeRecord->rate_source === 'bcv' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }}">
                        {{ $activeRecord->source_label }}
                    </span>
                @endif
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-white tracking-tight">
                    Bs. {{ number_format($currentRate, 4, ',', '.') }}
                </span>
                <span class="text-xs font-bold text-slate-500">/ 1 USD</span>
            </div>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-800/60 text-xs">
                <div class="flex items-center gap-1.5">
                    @php
                        $var = $activeRecord ? (float)$activeRecord->variation_percent : 0.00;
                    @endphp
                    @if($var > 0)
                        <span class="text-rose-400 font-extrabold flex items-center gap-0.5 text-[11px]">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> +{{ $var }}%
                        </span>
                    @elseif($var < 0)
                        <span class="text-emerald-400 font-extrabold flex items-center gap-0.5 text-[11px]">
                            <i data-lucide="trending-down" class="w-3.5 h-3.5"></i> {{ $var }}%
                        </span>
                    @else
                        <span class="text-slate-400 font-bold text-[11px]">0.00%</span>
                    @endif
                    <span class="text-[10px] text-slate-500">vs tasa previa</span>
                </div>
                <span class="text-[10px] text-slate-400 font-medium">
                    {{ $activeRecord && $activeRecord->effective_at ? \Carbon\Carbon::parse($activeRecord->effective_at)->format('d/m/Y H:i') : date('d/m/Y') }}
                </span>
            </div>
        </div>

        <!-- Card 2: Interactive Live Currency Calculator -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 relative overflow-hidden card-hover-effect">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="calculator" class="w-4 h-4 text-emerald-400"></i> Calculadora Rápida
                </span>
                <span class="text-[10px] text-slate-500 font-medium">Tiempo Real</span>
            </div>

            <div class="grid grid-cols-2 gap-2.5 mt-2">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Monto (USD $)</label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">$</span>
                        <input type="number" 
                               id="calc-usd" 
                               step="0.01" 
                               value="10.00" 
                               oninput="calcUsdToVes(this.value)" 
                               class="w-full pl-6 pr-2 py-1.5 text-xs font-bold bg-slate-950 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Equivalente (VES Bs.)</label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-500">Bs.</span>
                        <input type="number" 
                               id="calc-ves" 
                               step="0.01" 
                               value="{{ number_format(10 * $currentRate, 2, '.', '') }}" 
                               oninput="calcVesToUsd(this.value)" 
                               class="w-full pl-8 pr-2 py-1.5 text-xs font-bold bg-slate-950 border border-slate-800 rounded-xl text-lime-400 focus:outline-none focus:border-lime-500">
                    </div>
                </div>
            </div>

            <p class="text-[10px] text-slate-500 mt-3 pt-2 border-t border-slate-800/60 truncate">
                Fórmula: <span class="text-slate-300 font-mono">USD × {{ number_format($currentRate, 2, ',', '.') }} Bs</span>
            </p>
        </div>

        <!-- Card 3: Sucursal Configuration Mode -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 relative overflow-hidden card-hover-effect">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-purple-400"></i> Modo de Tasa
                </span>
                <button type="button" onclick="window.toggleModal('modal-config-rate')" class="text-[10px] text-lime-400 hover:text-lime-300 font-bold uppercase transition-colors">
                    Configurar
                </button>
            </div>

            <div class="space-y-1.5">
                <div class="text-xs font-bold text-slate-200">
                    {{ $activeGym ? $activeGym->name : 'Contexto Global' }}
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $gymRateType = $activeGym ? $activeGym->dollar_rate_type : 'bcv';
                    @endphp
                    <span class="w-2 h-2 rounded-full {{ $gymRateType === 'bcv' ? 'bg-emerald-400' : 'bg-purple-400' }} animate-pulse"></span>
                    <span class="text-xs text-slate-300 font-medium">
                        {{ $gymRateType === 'bcv' ? 'Sincronización Oficial BCV' : 'Tasa Fija Personalizada' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-800/60 text-[10px] text-slate-400">
                <span>Cron Automático: <strong class="text-slate-300">09:00 AM y 05:00 PM</strong></span>
                <span class="text-emerald-400 font-bold">Activo</span>
            </div>
        </div>
    </div>

    <!-- Historical Audit Log Section -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5 space-y-4">
        
        <!-- Table Filter Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-800/60">
            <div>
                <h3 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-lime-400"></i> Historial y Auditoría de Variación Cambiaria
                </h3>
                <p class="text-[11px] text-slate-400">Registro cronológico de todas las actualizaciones automáticas y manuales.</p>
            </div>

            <!-- Filters Form -->
            <form action="{{ route('tasas_cambio.index') }}" method="GET" class="flex items-center gap-2 flex-wrap m-0">
                <select name="source" onchange="this.form.submit()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-slate-300 focus:outline-none focus:border-lime-500 cursor-pointer">
                    <option value="">Todas las Fuentes</option>
                    <option value="bcv" {{ request('source') === 'bcv' ? 'selected' : '' }}>BCV Oficial</option>
                    <option value="custom" {{ request('source') === 'custom' ? 'selected' : '' }}>Personalizado (Manual)</option>
                    <option value="enparalelovzla" {{ request('source') === 'enparalelovzla' ? 'selected' : '' }}>Paralelo</option>
                </select>

                <select name="type" onchange="this.form.submit()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-slate-300 focus:outline-none focus:border-lime-500 cursor-pointer">
                    <option value="">Todo Tipo</option>
                    <option value="auto_job" {{ request('type') === 'auto_job' ? 'selected' : '' }}>Automático (API)</option>
                    <option value="manual_override" {{ request('type') === 'manual_override' ? 'selected' : '' }}>Manual</option>
                </select>

                @if(request()->hasAny(['source', 'type', 'date']))
                    <a href="{{ route('tasas_cambio.index') }}" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs transition-colors" title="Limpiar Filtros">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- History Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[10px] uppercase font-bold text-slate-400 bg-slate-950/60 border-b border-slate-800/80">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Estado</th>
                        <th class="py-3 px-4">Tasa (Factor VES)</th>
                        <th class="py-3 px-4">Variación</th>
                        <th class="py-3 px-4">Origen / Fuente</th>
                        <th class="py-3 px-4">Tipo de Cambio</th>
                        <th class="py-3 px-4">Fecha & Hora</th>
                        <th class="py-3 px-4">Responsable / IP</th>
                        <th class="py-3 px-4 rounded-r-xl">Notas / Justificación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($history as $item)
                        <tr class="hover:bg-slate-850/40 transition-colors {{ $item->is_active ? 'bg-lime-500/[0.02]' : '' }}">
                            
                            <!-- Estado -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-lime-500/10 text-lime-400 border border-lime-500/30 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span> Vigente
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[9px] font-bold text-slate-500 bg-slate-950 rounded-full border border-slate-850">
                                        Histórico
                                    </span>
                                @endif
                            </td>

                            <!-- Tasa -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-100 text-sm">
                                    Bs. {{ number_format($item->rate, 4, ',', '.') }}
                                </span>
                            </td>

                            <!-- Variación % -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @php
                                    $v = (float)$item->variation_percent;
                                @endphp
                                @if($v > 0)
                                    <span class="text-rose-400 font-extrabold text-xs flex items-center gap-0.5">
                                        <i data-lucide="trending-up" class="w-3 h-3"></i> +{{ $v }}%
                                    </span>
                                @elseif($v < 0)
                                    <span class="text-emerald-400 font-extrabold text-xs flex items-center gap-0.5">
                                        <i data-lucide="trending-down" class="w-3 h-3"></i> {{ $v }}%
                                    </span>
                                @else
                                    <span class="text-slate-500 font-medium">0.00%</span>
                                @endif
                            </td>

                            <!-- Fuente -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded border {{ $item->rate_source === 'bcv' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }}">
                                    {{ $item->source_label }}
                                </span>
                            </td>

                            <!-- Tipo de Cambio -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($item->change_type === 'auto_job')
                                    <span class="inline-flex items-center gap-1 text-[10px] text-blue-400 font-semibold">
                                        <i data-lucide="bot" class="w-3 h-3"></i> Auto API
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] text-amber-400 font-semibold">
                                        <i data-lucide="user" class="w-3 h-3"></i> Manual
                                    </span>
                                @endif
                            </td>

                            <!-- Fecha & Hora -->
                            <td class="py-3 px-4 whitespace-nowrap text-slate-400 text-[11px]">
                                {{ $item->effective_at ? \Carbon\Carbon::parse($item->effective_at)->format('d/m/Y H:i') : \Carbon\Carbon::parse($item->effective_date)->format('d/m/Y') }}
                            </td>

                            <!-- Responsable / IP -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($item->updater)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-semibold text-slate-200">{{ $item->updater->profile ? $item->updater->profile->first_name : $item->updater->email }}</span>
                                        @if($item->ip_address)
                                            <span class="text-[9px] text-slate-500 font-mono">({{ $item->ip_address }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-500 text-[10px] italic">Sistema (Job Sincronizador)</span>
                                @endif
                            </td>

                            <!-- Notas -->
                            <td class="py-3 px-4 max-w-xs truncate text-[11px] text-slate-400" title="{{ $item->notes }}">
                                {{ $item->notes ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500 text-xs italic">
                                No se encontraron registros de tasas de cambio en el historial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($history->hasPages())
            <div class="pt-3 border-t border-slate-800/60">
                {{ $history->links() }}
            </div>
        @endif

    </div>

</div>

<!-- Modal: Ajuste Manual de Tasa -->
<div id="modal-manual-rate" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-lime-500/10 text-lime-400 rounded-xl border border-lime-500/20">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-100">Modificar Tasa Manualmente</h3>
                    <p class="text-[10px] text-slate-400">Actualiza el factor cambiario aplicado en la plataforma.</p>
                </div>
            </div>
            <button type="button" onclick="window.toggleModal('modal-manual-rate')" class="p-1 rounded-lg text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('tasas_cambio.store_manual') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs uppercase font-bold text-slate-400 mb-1">
                    Nuevo Factor Cambiario (Bs. por 1 USD) <span class="text-lime-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Bs.</span>
                    <input type="number" 
                           name="rate" 
                           step="0.0001" 
                           min="0.0001" 
                           required 
                           value="{{ $currentRate }}" 
                           placeholder="Ej: 45.5000" 
                           class="w-full pl-9 pr-4 py-2.5 text-sm font-bold bg-slate-950 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500">
                </div>
                <p class="text-[10px] text-slate-500 mt-1">Tasa actual de referencia: <strong>Bs. {{ number_format($currentRate, 4, ',', '.') }}</strong></p>
            </div>

            <div>
                <label class="block text-xs uppercase font-bold text-slate-400 mb-1">
                    Motivo o Justificación del Ajuste <span class="text-slate-500">(Opcional)</span>
                </label>
                <input type="text" 
                       name="notes" 
                       maxlength="255" 
                       placeholder="Ej: Ajuste de fin de semana / Tasa bancaria especial" 
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-950 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500">
            </div>

            @if(auth()->user()->role === 'superadmin')
                <div class="flex items-center gap-2 p-3 bg-slate-950/60 rounded-xl border border-slate-850">
                    <input type="checkbox" name="apply_to_all_gyms" value="1" id="apply_all" class="rounded border-slate-800 text-lime-500 focus:ring-lime-500">
                    <label for="apply_all" class="text-xs text-slate-300 font-medium cursor-pointer">
                        Aplicar como tasa global a todas las sucursales
                    </label>
                </div>
            @endif

            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-[11px] text-amber-300 flex items-start gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <span>Este cambio modificará inmediatamente el cálculo en Bolívares en el punto de venta (POS) y las cuotas de membresías.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="window.toggleModal('modal-manual-rate')" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 text-slate-950 font-black rounded-xl text-xs hover:from-lime-400 hover:to-emerald-400 transition-all shadow-md shadow-lime-500/20">
                    Guardar y Aplicar Tasa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Configuración de Modo de Tasa por Sucursal -->
<div id="modal-config-rate" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl border border-purple-500/20">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-100">Configuración de Tasa por Sucursal</h3>
                    <p class="text-[10px] text-slate-400">{{ $activeGym ? $activeGym->name : 'Todas las sucursales' }}</p>
                </div>
            </div>
            <button type="button" onclick="window.toggleModal('modal-config-rate')" class="p-1 rounded-lg text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('tasas_cambio.update_config') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-3">
                <label class="block text-xs uppercase font-bold text-slate-400">Modalidad de Actualización</label>
                
                <label class="flex items-start gap-3 p-3.5 bg-slate-950 rounded-xl border border-slate-800 hover:border-slate-700 cursor-pointer">
                    <input type="radio" name="dollar_rate_type" value="bcv" {{ ($activeGym && $activeGym->dollar_rate_type === 'bcv') || !$activeGym ? 'checked' : '' }} class="mt-0.5 text-lime-500 focus:ring-lime-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-200">Sincronización Oficial BCV (Recomendado)</span>
                        <span class="block text-[10px] text-slate-400 mt-0.5">El sistema consultará automáticamente el Banco Central de Venezuela dos veces al día.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 bg-slate-950 rounded-xl border border-slate-800 hover:border-slate-700 cursor-pointer">
                    <input type="radio" name="dollar_rate_type" value="custom" {{ $activeGym && $activeGym->dollar_rate_type === 'custom' ? 'checked' : '' }} class="mt-0.5 text-lime-500 focus:ring-lime-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-200">Tasa Fija Personalizada</span>
                        <span class="block text-[10px] text-slate-400 mt-0.5">El gimnasio operará con un factor fijo definido manualmente por la administración.</span>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="window.toggleModal('modal-config-rate')" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 text-slate-950 font-black rounded-xl text-xs hover:from-lime-400 hover:to-emerald-400 transition-all shadow-md shadow-lime-500/20">
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const currentExchangeRate = {{ $currentRate }};

    function calcUsdToVes(val) {
        const usd = parseFloat(val) || 0;
        const ves = (usd * currentExchangeRate).toFixed(2);
        document.getElementById('calc-ves').value = ves;
    }

    function calcVesToUsd(val) {
        const ves = parseFloat(val) || 0;
        const usd = currentExchangeRate > 0 ? (ves / currentExchangeRate).toFixed(2) : 0;
        document.getElementById('calc-usd').value = usd;
    }

    function triggerBcvSync() {
        const btn = document.getElementById('btn-sync-bcv');
        const icon = document.getElementById('sync-icon');
        const text = document.getElementById('sync-text');

        if (icon) icon.classList.add('animate-spin');
        if (text) text.textContent = 'Sincronizando BCV...';
        if (btn) btn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('tasas_cambio.sync_now') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (icon) icon.classList.remove('animate-spin');
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Sincronizar BCV Ahora';

            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Sincronización Exitosa!',
                        text: data.message,
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#84cc16'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Sincronización',
                        text: data.message,
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#84cc16'
                    });
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(err => {
            if (icon) icon.classList.remove('animate-spin');
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Sincronizar BCV Ahora';
            console.error('Error al sincronizar BCV:', err);
        });
    }
</script>
@endsection
