@extends('layouts.admin')

@section('title', 'Cierre de Caja y Balance Diario')

@section('content')
<div class="space-y-6">

    <!-- Header Section (Screen view) -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 border-b border-slate-850 pb-5 print:hidden">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Finanzas</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-lime-400 font-semibold">Cierre de Caja &amp; Arqueo</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-2.5">
                <i data-lucide="calculator" class="w-7 h-7 text-lime-400"></i> Cierre de Caja y Balance Diario
            </h1>
            <p class="text-xs text-slate-400 mt-1">Auditoría completa de recaudación, membresías, ventas de tienda y asistencias del gimnasio.</p>
        </div>

        <!-- Date Selector & Action Controls (Re-positioned & Enhanced) -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Enhanced Period Selector Bar -->
            <div class="flex flex-wrap items-center gap-2 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                @php
                    $reqPeriod = request('period', 'today');
                @endphp
                
                <select id="cierre_period_select" onchange="onCierrePeriodSelectChange(this.value)" class="text-xs bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-lime-400 font-bold focus:outline-none focus:border-lime-500 cursor-pointer">
                    <option value="today" {{ $reqPeriod === 'today' ? 'selected' : '' }}>Hoy</option>
                    <option value="yesterday" {{ $reqPeriod === 'yesterday' ? 'selected' : '' }}>Ayer</option>
                    <option value="this_week" {{ $reqPeriod === 'this_week' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="last_week" {{ $reqPeriod === 'last_week' ? 'selected' : '' }}>Semana Anterior</option>
                    <option value="this_month" {{ $reqPeriod === 'this_month' ? 'selected' : '' }}>Mes Actual</option>
                    <option value="specific" {{ $reqPeriod === 'specific' || request('date') ? 'selected' : '' }}>Fecha Específica...</option>
                    <option value="custom" {{ $reqPeriod === 'custom' ? 'selected' : '' }}>Rango Personalizado...</option>
                </select>

                <!-- Specific Single Date Input -->
                <div id="cierre_single_date_container" class="{{ ($reqPeriod === 'specific' || request('date')) && $reqPeriod !== 'custom' ? '' : 'hidden' }}">
                    <input type="date" id="cierre_date_picker" value="{{ $parsedDate }}" onchange="changeAuditDate(this.value)" class="px-3 py-1 bg-slate-900 border border-slate-800 rounded-xl text-xs font-extrabold text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>

                <!-- Custom Range Date Inputs -->
                <div id="cierre_custom_range_container" class="{{ $reqPeriod === 'custom' ? '' : 'hidden' }} flex items-center gap-2">
                    <input type="date" id="cierre_start_date" value="{{ request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}" class="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold text-slate-100">
                    <span class="text-slate-500 text-xs">-</span>
                    <input type="date" id="cierre_end_date" value="{{ request('end_date', \Carbon\Carbon::now()->format('Y-m-d')) }}" class="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold text-slate-100">
                    <button type="button" onclick="applyCustomRangeCierre()" class="px-2.5 py-1 bg-lime-500 text-slate-950 font-bold text-xs rounded-xl hover:bg-lime-400 transition-colors">
                        Filtrar
                    </button>
                </div>
            </div>

            <!-- Action Buttons Group (Imprimir & Cierre de Caja) -->
            <div class="flex items-center gap-2">
                <a href="{{ route('cierre_caja.export_pdf', request()->all()) }}" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-850 text-slate-200 border border-slate-800 font-bold text-xs rounded-xl transition-colors flex items-center gap-2 cursor-pointer shadow-sm">
                    <i data-lucide="file-text" class="w-4 h-4 text-lime-400"></i> Imprimir Cierre (PDF)
                </a>

                @if(!$isClosed)
                    <button type="button" onclick="toggleModal('close-cash-modal')" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <i data-lucide="lock" class="w-4 h-4"></i> Cerrar Caja
                    </button>
                @else
                    <span class="px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-extrabold rounded-xl flex items-center gap-2">
                        <i data-lucide="check-check" class="w-4 h-4"></i> Caja Cerrada
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Printable Header Banner (Only visible on print) -->
    <div class="hidden print:block border-b-2 border-slate-900 pb-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-black uppercase tracking-wider">{{ $gym->name ?? 'REPORTE DE CIERRE DE CAJA' }}</h1>
                <p class="text-sm font-bold text-gray-700">AUDITORÍA FINANCIERA Y DIARIA DE OPERACIONES</p>
                <p class="text-xs text-gray-500 mt-1">Periodo del Arqueo: {{ $periodLabel ?? $parsedDate }}</p>
            </div>
            <div class="text-right text-xs">
                <p class="font-bold text-black">Impreso por: {{ auth()->user()->name }}</p>
                <p class="text-gray-500">Fecha de Impresión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
                <p class="font-bold uppercase text-emerald-600 mt-1">{{ $isClosed ? 'ESTADO: CIERRE COMPLETADO' : 'ESTADO: EN PROCESO' }}</p>
            </div>
        </div>
    </div>

    <!-- Banner Status Alert -->
    <div class="p-4 rounded-2xl border flex items-center justify-between gap-4 print:hidden {{ $isClosed ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-400' }}">
        <div class="flex items-center gap-3">
            <i data-lucide="{{ $isClosed ? 'shield-check' : 'alert-circle' }}" class="w-6 h-6 shrink-0"></i>
            <div>
                <span class="font-bold text-sm block">
                    {{ $isClosed ? 'Cierre de Caja Completado y Cuadrado' : 'Arqueo de Caja en Proceso' }}
                </span>
                <span class="text-xs opacity-80">
                    @if($isClosed && $closingLog)
                        Caja del {{ \Carbon\Carbon::parse($parsedDate)->format('d/m/Y') }} cerrada formalmente por {{ $closingLog->new_data['closed_by'] ?? 'Administrador' }} a las {{ \Carbon\Carbon::parse($closingLog->createdAt)->format('H:i') }} hrs.
                    @else
                        Consolidado de ingresos y movimientos para el periodo <strong>{{ $periodLabel ?? $parsedDate }}</strong>.
                    @endif
                </span>
            </div>
        </div>
        <span class="text-xs font-mono font-bold uppercase px-3 py-1 rounded-lg bg-slate-950/60 border border-slate-800 shrink-0">
            {{ $periodLabel ?? \Carbon\Carbon::parse($parsedDate)->format('d/m/Y') }}
        </span>
    </div>

    <!-- Financial Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Recaudado Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase">Total Recaudado</span>
                <div class="p-2.5 bg-lime-500/10 border border-lime-500/20 rounded-xl text-lime-400">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-100 tracking-tight block leading-none">${{ number_format($grandTotal, 2) }}</span>
                <span class="text-xs font-extrabold text-lime-400 font-mono block mt-1.5">≈ Bs. {{ number_format($grandTotalVes, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 mt-3 pt-3 border-t border-slate-850">
                <span>Membresías: <strong class="text-slate-200">${{ number_format($membershipTotal, 2) }}</strong></span>
                <span>Tienda: <strong class="text-slate-200">${{ number_format($productSalesTotal, 2) }}</strong></span>
            </div>
        </div>

        <!-- Efectivo Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase">Efectivo en Caja</span>
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>
            <div>
                <span class="text-2xl font-black text-emerald-400 tracking-tight block leading-none">${{ number_format($cashTotal, 2) }}</span>
                <span class="text-xs font-extrabold text-emerald-400/90 font-mono block mt-1.5">≈ Bs. {{ number_format($cashTotalVes, 2, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-850">Dinero físico a entregar en caja</p>
        </div>

        <!-- Tarjeta Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase">Puntos / Tarjeta</span>
                <div class="p-2.5 bg-sky-500/10 border border-sky-500/20 rounded-xl text-sky-400">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
            </div>
            <div>
                <span class="text-2xl font-black text-sky-400 tracking-tight block leading-none">${{ number_format($cardTotal, 2) }}</span>
                <span class="text-xs font-extrabold text-sky-400/90 font-mono block mt-1.5">≈ Bs. {{ number_format($cardTotalVes, 2, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-850">Procesado vía punto de venta POS</p>
        </div>

        <!-- Transferencias Card -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase">Transferencias / Pago Móvil</span>
                <div class="p-2.5 bg-purple-500/10 border border-purple-500/20 rounded-xl text-purple-400">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
            </div>
            <div>
                <span class="text-2xl font-black text-purple-400 tracking-tight block leading-none">${{ number_format($transferTotal, 2) }}</span>
                <span class="text-xs font-extrabold text-purple-400/90 font-mono block mt-1.5">≈ Bs. {{ number_format($transferTotalVes, 2, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-3 pt-3 border-t border-slate-850">Depósitos directos a cuenta bancaria</p>
        </div>

    </div>

    <!-- Operational Activity Indicators -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-lime-500/10 text-lime-400 rounded-xl">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400">Membresías del Periodo</span>
                    <span class="text-base font-black text-slate-100">{{ $newMemberships->count() }} Nuevas / Renovaciones</span>
                </div>
            </div>
            <span class="text-xs font-extrabold text-lime-400 bg-lime-500/10 px-2.5 py-1 rounded-lg">#{{ $newMemberships->count() }}</span>
        </div>

        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-500/10 text-amber-400 rounded-xl">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400">Ventas en Tienda POS</span>
                    <span class="text-base font-black text-slate-100">{{ $productSales->count() }} Transacciones</span>
                </div>
            </div>
            <span class="text-xs font-extrabold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg">#{{ $productSales->count() }}</span>
        </div>

        <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400">Asistencias al Gym</span>
                    <span class="text-base font-black text-slate-100">{{ $attendances->count() }} Check-ins</span>
                </div>
            </div>
            <span class="text-xs font-extrabold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg">#{{ $attendances->count() }}</span>
        </div>
    </div>

    <!-- Detailed Section 1: Cobros de Membresías y Abonos (Paginated Max 10) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl space-y-4">
        <div class="p-6 border-b border-slate-850 flex items-center justify-between">
            <h3 class="font-bold text-base text-slate-100 flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5 text-lime-400"></i> Cobros de Membresías y Abonos ({{ $membershipPayments->count() }})
            </h3>
            <div class="text-right">
                <span class="text-xs font-black text-lime-400 bg-lime-500/10 px-3 py-1 rounded-full border border-lime-500/20 inline-block">
                    Total: ${{ number_format($membershipTotal, 2) }}
                </span>
                <span class="block text-[10px] font-bold text-emerald-400 font-mono mt-1">
                    ≈ {{ \App\Services\ExchangeRateService::formatVES($membershipTotalVes ?? ($membershipTotal * ($dollarRate ?? 1))) }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Hora / Referencia</th>
                        <th class="p-4">Socio / Cliente</th>
                        <th class="p-4">Plan de Membresía</th>
                        <th class="p-4 text-center">Método de Pago</th>
                        <th class="p-4 text-right">Monto Recaudado ($ / Bs.)</th>
                        <th class="p-4 pr-6 text-right">Receptor / Operador</th>
                    </tr>
                </thead>
                <tbody id="mpay_table_body" class="divide-y divide-slate-850">
                    @forelse($membershipPayments as $pay)
                        @php
                            $user = $pay->membership->user ?? null;
                            $userName = $user ? trim(($user->profile->first_name ?? '') . ' ' . ($user->profile->last_name ?? '')) : 'Socio Desconocido';
                            if (empty($userName)) $userName = $user->email ?? 'Socio ID #' . $pay->user_id;
                            $planName = $pay->membership->plan->name ?? 'Membresía / Abono';
                        @endphp
                        @php
                            $effectivePayRate = ($dollarRate && (float)$dollarRate > 1.0001) ? (float)$dollarRate : (float)\App\Services\ExchangeRateService::getCurrentRate();
                            $payVes = (float)$pay->amount * $effectivePayRate;
                        @endphp
                        <tr data-mpay-row class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 pl-6">
                                <span class="block font-bold text-slate-200">{{ \Carbon\Carbon::parse($pay->payment_date)->format('H:i A') }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">Ref: {{ $pay->reference_code ?: 'N/A' }}</span>
                            </td>
                            <td class="p-4 font-bold text-slate-100">
                                {{ $userName }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-slate-950 border border-slate-850 rounded-lg text-slate-300 font-semibold">
                                    {{ $planName }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($pay->payment_method === 'cash')
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold uppercase rounded-full border border-emerald-500/20">Efectivo 💵</span>
                                @elseif($pay->payment_method === 'card')
                                    <span class="px-2.5 py-0.5 bg-sky-500/10 text-sky-400 text-[10px] font-extrabold uppercase rounded-full border border-sky-500/20">Tarjeta 💳</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-purple-500/10 text-purple-400 text-[10px] font-extrabold uppercase rounded-full border border-purple-500/20">Transferencia 🏦</span>
                                @endif
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <span class="block font-black text-lime-400 text-sm font-mono">${{ number_format($pay->amount, 2) }}</span>
                                <span class="block text-[10px] font-bold text-emerald-400 font-mono mt-0.5">Bs. {{ number_format($payVes, 2, ',', '.') }}</span>
                            </td>
                            <td class="p-4 pr-6 text-right text-slate-400 font-medium">
                                {{ $pay->receivedBy->name ?? 'Administrador' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                No se registraron cobros de membresías en esta fecha o periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table 1 Pagination Controls -->
        <div id="mpay_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-medium text-slate-400 print:hidden">
            <span id="mpay_pagination_info">Mostrando cobros...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="mpay_prev_btn" onclick="changeMPayPage(-1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    Anterior
                </button>
                <span id="mpay_page_display" class="px-3.5 py-1.5 bg-slate-950 rounded-xl font-bold text-lime-400 border border-slate-850">Página 1</span>
                <button type="button" id="mpay_next_btn" onclick="changeMPayPage(1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    Siguiente
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Detailed Section 2: Ventas de Tienda POS (Paginated Max 10) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl space-y-4">
        <div class="p-6 border-b border-slate-850 flex items-center justify-between">
            <h3 class="font-bold text-base text-slate-100 flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-amber-400"></i> Ventas de Tienda y Mostrador ({{ $productSales->count() }})
            </h3>
            <div class="text-right">
                <span class="text-xs font-black text-amber-400 bg-amber-500/10 px-3 py-1 rounded-full border border-amber-500/20 inline-block">
                    Total: ${{ number_format($productSalesTotal, 2) }}
                </span>
                <span class="block text-[10px] font-bold text-emerald-400 font-mono mt-1">
                    ≈ {{ \App\Services\ExchangeRateService::formatVES($productSalesTotalVes ?? ($productSalesTotal * ($dollarRate ?? 1))) }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Hora</th>
                        <th class="p-4">Cliente / Comprador</th>
                        <th class="p-4">Productos Vendidos</th>
                        <th class="p-4 text-center">Método de Pago</th>
                        <th class="p-4 text-right">Monto Total ($ / Bs.)</th>
                        <th class="p-4 pr-6 text-right">Vendedor</th>
                    </tr>
                </thead>
                <tbody id="psales_table_body" class="divide-y divide-slate-850">
                    @forelse($productSales as $sale)
                        @php
                            $buyer = $sale->user;
                            $buyerName = $buyer ? trim(($buyer->profile->first_name ?? '') . ' ' . ($buyer->profile->last_name ?? '')) : 'Cliente Mostrador / Invitado';
                            if (empty($buyerName)) $buyerName = 'Cliente Mostrador';
                            $itemSummary = $sale->items->map(fn($item) => ($item->product->name ?? 'Producto') . ' (' . $item->quantity . ')')->join(', ');
                            
                            $effectiveSaleRate = ($sale->exchange_rate && (float)$sale->exchange_rate > 1.0001) 
                                ? (float)$sale->exchange_rate 
                                : (float)($dollarRate > 1.0001 ? $dollarRate : \App\Services\ExchangeRateService::getCurrentRate($sale->gym_id));

                            $effectiveSaleVes = ($sale->total_amount_ves && (float)$sale->total_amount_ves > ((float)$sale->total_amount * 1.0001))
                                ? (float)$sale->total_amount_ves
                                : ((float)$sale->total_amount * $effectiveSaleRate);
                        @endphp
                        <tr data-psales-row class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 pl-6 font-bold text-slate-200">
                                {{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i A') }}
                            </td>
                            <td class="p-4 font-bold text-slate-100">
                                {{ $buyerName }}
                            </td>
                            <td class="p-4 max-w-xs truncate text-slate-300">
                                {{ $itemSummary ?: 'Venta de mostrador' }}
                            </td>
                            <td class="p-4 text-center">
                                @if($sale->payment_method === 'cash')
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold uppercase rounded-full border border-emerald-500/20">Efectivo 💵</span>
                                @elseif($sale->payment_method === 'card')
                                    <span class="px-2.5 py-0.5 bg-sky-500/10 text-sky-400 text-[10px] font-extrabold uppercase rounded-full border border-sky-500/20">Tarjeta 💳</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-purple-500/10 text-purple-400 text-[10px] font-extrabold uppercase rounded-full border border-purple-500/20">Transferencia 🏦</span>
                                @endif
                            </td>
                            <td class="p-4 text-right whitespace-nowrap">
                                <span class="block font-black text-amber-400 text-sm font-mono">${{ number_format($sale->total_amount, 2) }}</span>
                                <span class="block text-[10px] font-bold text-emerald-400 font-mono mt-0.5">Bs. {{ number_format($effectiveSaleVes, 2, ',', '.') }}</span>
                            </td>
                            <td class="p-4 pr-6 text-right text-slate-400 font-medium">
                                {{ $sale->soldBy->name ?? 'Cajero' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                No se registraron ventas de productos en tienda en esta fecha o periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table 2 Pagination Controls -->
        <div id="psales_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-medium text-slate-400 print:hidden">
            <span id="psales_pagination_info">Mostrando ventas...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="psales_prev_btn" onclick="changePSalesPage(-1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    Anterior
                </button>
                <span id="psales_page_display" class="px-3.5 py-1.5 bg-slate-950 rounded-xl font-bold text-lime-400 border border-slate-850">Página 1</span>
                <button type="button" id="psales_next_btn" onclick="changePSalesPage(1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    Siguiente
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Detailed Section 3: Registro de Asistencias del Día (Paginated Max 10) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl space-y-4">
        <div class="p-6 border-b border-slate-850 flex items-center justify-between">
            <h3 class="font-bold text-base text-slate-100 flex items-center gap-2">
                <i data-lucide="user-check" class="w-5 h-5 text-emerald-400"></i> Asistencias y Accesos de Socios ({{ $attendances->count() }})
            </h3>
            <span class="text-xs font-black text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20">
                {{ $attendances->count() }} Socios Presentes
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Hora Entrada</th>
                        <th class="p-4">Socio</th>
                        <th class="p-4 text-center">Método de Acceso</th>
                        <th class="p-4 pr-6 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody id="att_table_body" class="divide-y divide-slate-850">
                    @forelse($attendances as $att)
                        @php
                            $member = $att->user;
                            $memberName = $member ? trim(($member->profile->first_name ?? '') . ' ' . ($member->profile->last_name ?? '')) : 'Socio ID #' . $att->user_id;
                        @endphp
                        <tr data-att-row class="hover:bg-slate-900/40 transition-colors">
                            <td class="p-4 pl-6 font-bold text-slate-200">
                                {{ \Carbon\Carbon::parse($att->check_in)->format('H:i:s A') }}
                            </td>
                            <td class="p-4 font-bold text-slate-100">
                                {{ $memberName }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-0.5 bg-slate-950 border border-slate-850 text-slate-300 font-mono text-[10px] uppercase rounded-lg">
                                    {{ $att->entry_method ?: 'Biométrico' }}
                                </span>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold uppercase rounded-full border border-emerald-500/20">
                                    {{ $att->status === 'valid' ? 'Acceso Válido' : 'Revisar' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500 font-semibold italic">
                                No se registraron accesos de socios en esta fecha o periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table 3 Pagination Controls -->
        <div id="att_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-medium text-slate-400 print:hidden">
            <span id="att_pagination_info">Mostrando accesos...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="att_prev_btn" onclick="changeAttPage(-1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    Anterior
                </button>
                <span id="att_page_display" class="px-3.5 py-1.5 bg-slate-950 rounded-xl font-bold text-lime-400 border border-slate-850">Página 1</span>
                <button type="button" id="att_next_btn" onclick="changeAttPage(1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    Siguiente
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Confirmar Cierre Formal de Caja -->
<div id="close-cash-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-7 w-full max-w-md space-y-5 shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
            <h3 class="font-extrabold text-base text-slate-100 flex items-center gap-2">
                <i data-lucide="lock" class="w-5 h-5 text-lime-400"></i> Realizar Cierre de Caja
            </h3>
            <button type="button" onclick="toggleModal('close-cash-modal')" class="text-slate-400 hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="close-cash-form" action="{{ route('cierre_caja.close_day') }}" method="POST" onsubmit="submitCashCloseForm(event)" class="space-y-4">
            @csrf
            <input type="hidden" name="date" value="{{ $parsedDate }}">

            <div class="p-4 bg-slate-950 rounded-2xl border border-slate-850 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span>Periodo / Fecha:</span>
                    <span class="font-black text-slate-100">{{ $periodLabel ?? $parsedDate }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400">
                    <span>Efectivo Físico en Caja:</span>
                    <span class="font-black text-emerald-400 text-sm">${{ number_format($cashTotal, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 border-t border-slate-850 pt-2">
                    <span>Gran Total Recaudado:</span>
                    <span class="font-black text-lime-400 text-sm">${{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Observaciones de Auditoría (Opcional)</label>
                <textarea name="notes" rows="2" placeholder="Ej: Dinero entregado al dueño. Sin novedades en el turno." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-850">
                <button type="button" onclick="toggleModal('close-cash-modal')" class="px-4 py-2 bg-slate-950 border border-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all cursor-pointer">
                    Confirmar Cierre Formal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Period & Date Change Handler
    function onCierrePeriodSelectChange(val) {
        const singleContainer = document.getElementById('cierre_single_date_container');
        const customContainer = document.getElementById('cierre_custom_range_container');

        if (val === 'specific') {
            singleContainer.classList.remove('hidden');
            customContainer.classList.add('hidden');
        } else if (val === 'custom') {
            singleContainer.classList.add('hidden');
            customContainer.classList.remove('hidden');
        } else {
            singleContainer.classList.add('hidden');
            customContainer.classList.add('hidden');
            changeAuditPeriod(val);
        }
    }

    function changeAuditDate(dateStr) {
        if (!dateStr) return;
        changeAuditPeriod('specific', dateStr);
    }

    function applyCustomRangeCierre() {
        const start = document.getElementById('cierre_start_date')?.value;
        const end = document.getElementById('cierre_end_date')?.value;
        if (!start || !end) return;

        window.location.href = `/cierre-caja?period=custom&start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`;
    }

    function changeAuditPeriod(periodStr, dateStr = '') {
        let targetUrl = `/cierre-caja?period=${encodeURIComponent(periodStr)}`;
        if (dateStr) {
            targetUrl += `&date=${encodeURIComponent(dateStr)}`;
        }
        window.location.href = targetUrl;
    }

    // Table 1: Membership Payments Pagination (10 per page)
    let currentMPayPage = 1;
    const mPayPerPage = 10;

    function renderMPayPage() {
        const rows = Array.from(document.querySelectorAll('[data-mpay-row]'));
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / mPayPerPage) || 1;

        if (currentMPayPage > totalPages) currentMPayPage = totalPages;
        if (currentMPayPage < 1) currentMPayPage = 1;

        rows.forEach(r => r.style.display = 'none');

        const startIndex = (currentMPayPage - 1) * mPayPerPage;
        const endIndex = startIndex + mPayPerPage;
        const pageSlice = rows.slice(startIndex, endIndex);

        pageSlice.forEach(r => r.style.display = '');

        const infoSpan = document.getElementById('mpay_pagination_info');
        const pageSpan = document.getElementById('mpay_page_display');
        const prevBtn = document.getElementById('mpay_prev_btn');
        const nextBtn = document.getElementById('mpay_next_btn');

        if (infoSpan) {
            if (totalRows === 0) {
                infoSpan.textContent = "No hay cobros registrados.";
            } else {
                infoSpan.textContent = `Mostrando ${startIndex + 1}-${Math.min(endIndex, totalRows)} de ${totalRows} cobros`;
            }
        }
        if (pageSpan) pageSpan.textContent = `Página ${currentMPayPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentMPayPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentMPayPage >= totalPages);
    }

    function changeMPayPage(delta) {
        currentMPayPage += delta;
        renderMPayPage();
    }

    // Table 2: Product Sales Pagination (10 per page)
    let currentPSalesPage = 1;
    const pSalesPerPage = 10;

    function renderPSalesPage() {
        const rows = Array.from(document.querySelectorAll('[data-psales-row]'));
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / pSalesPerPage) || 1;

        if (currentPSalesPage > totalPages) currentPSalesPage = totalPages;
        if (currentPSalesPage < 1) currentPSalesPage = 1;

        rows.forEach(r => r.style.display = 'none');

        const startIndex = (currentPSalesPage - 1) * pSalesPerPage;
        const endIndex = startIndex + pSalesPerPage;
        const pageSlice = rows.slice(startIndex, endIndex);

        pageSlice.forEach(r => r.style.display = '');

        const infoSpan = document.getElementById('psales_pagination_info');
        const pageSpan = document.getElementById('psales_page_display');
        const prevBtn = document.getElementById('psales_prev_btn');
        const nextBtn = document.getElementById('psales_next_btn');

        if (infoSpan) {
            if (totalRows === 0) {
                infoSpan.textContent = "No hay ventas registradas.";
            } else {
                infoSpan.textContent = `Mostrando ${startIndex + 1}-${Math.min(endIndex, totalRows)} de ${totalRows} ventas`;
            }
        }
        if (pageSpan) pageSpan.textContent = `Página ${currentPSalesPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentPSalesPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPSalesPage >= totalPages);
    }

    function changePSalesPage(delta) {
        currentPSalesPage += delta;
        renderPSalesPage();
    }

    // Table 3: Attendance Logs Pagination (10 per page)
    let currentAttPage = 1;
    const attPerPage = 10;

    function renderAttPage() {
        const rows = Array.from(document.querySelectorAll('[data-att-row]'));
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / attPerPage) || 1;

        if (currentAttPage > totalPages) currentAttPage = totalPages;
        if (currentAttPage < 1) currentAttPage = 1;

        rows.forEach(r => r.style.display = 'none');

        const startIndex = (currentAttPage - 1) * attPerPage;
        const endIndex = startIndex + attPerPage;
        const pageSlice = rows.slice(startIndex, endIndex);

        pageSlice.forEach(r => r.style.display = '');

        const infoSpan = document.getElementById('att_pagination_info');
        const pageSpan = document.getElementById('att_page_display');
        const prevBtn = document.getElementById('att_prev_btn');
        const nextBtn = document.getElementById('att_next_btn');

        if (infoSpan) {
            if (totalRows === 0) {
                infoSpan.textContent = "No hay asistencias registradas.";
            } else {
                infoSpan.textContent = `Mostrando ${startIndex + 1}-${Math.min(endIndex, totalRows)} de ${totalRows} accesos`;
            }
        }
        if (pageSpan) pageSpan.textContent = `Página ${currentAttPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentAttPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentAttPage >= totalPages);
    }

    function changeAttPage(delta) {
        currentAttPage += delta;
        renderAttPage();
    }

    // Submit Cash Close Form AJAX
    async function submitCashCloseForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                toggleModal('close-cash-modal');
                if (typeof showNotification === 'function') {
                    showNotification('¡Cierre Exitoso!', data.message, 'success');
                }
                window.location.reload();
            } else {
                alert(data.error || data.message || 'Error al procesar el cierre.');
            }
        } catch (err) {
            console.error(err);
            alert('Ocurrió un error al procesar la solicitud.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmar Cierre Formal';
        }
    }

    function initAllCierrePaginations() {
        currentMPayPage = 1;
        currentPSalesPage = 1;
        currentAttPage = 1;

        renderMPayPage();
        renderPSalesPage();
        renderAttPage();

        if (typeof flatpickr !== 'undefined') {
            flatpickr("#cierre_date_picker", {
                locale: "es",
                dateFormat: "Y-m-d",
                theme: "dark",
                onChange: function(selectedDates, dateStr) {
                    if (typeof changeAuditDate === 'function') changeAuditDate(dateStr);
                }
            });

            flatpickr("#cierre_start_date", {
                locale: "es",
                dateFormat: "Y-m-d",
                theme: "dark"
            });

            flatpickr("#cierre_end_date", {
                locale: "es",
                dateFormat: "Y-m-d",
                theme: "dark"
            });
        }
    }

    // Run pagination immediately on script evaluation
    initAllCierrePaginations();

    document.addEventListener('DOMContentLoaded', initAllCierrePaginations);
    window.addEventListener('page:loaded', initAllCierrePaginations);
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
@endsection
