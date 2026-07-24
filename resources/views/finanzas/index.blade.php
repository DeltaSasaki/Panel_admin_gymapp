@extends('layouts.admin')

@section('title', 'Finanzas y Membresías')

@section('content')
<div class="space-y-8 animate-fade-in" x-data="{ activeTab: 'membresias' }">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Finanzas y Membresías</h1>
            <p class="text-xs text-slate-400 mt-1">Administra los planes de suscripción, cobros, cupones y estados de facturación.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="toggleModal('plan-modal')" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-lime-400"></i> Crear Plan de Membresía
            </button>
            <button onclick="toggleModal('membership-modal')" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-lime-400"></i> Asignar Plan a Socio
            </button>
            <button onclick="toggleModal('promo-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="ticket" class="w-4 h-4"></i> Crear Cupón
            </button>
        </div>
    </div>

    <!-- Error/Success Alerts -->
    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-4 rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Financial Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Total Recaudado</span>
                <div class="p-2 bg-lime-500/10 text-lime-400 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 id="stat_total_collected" data-value="{{ $totalCollected }}" class="text-2xl font-black text-slate-100">${{ number_format($totalCollected, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-2">Suma acumulada de cobros registrados</p>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Monto por Cobrar</span>
                <div class="p-2 bg-amber-500/10 text-amber-400 rounded-lg">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 id="stat_pending_amount" data-value="{{ $pendingAmount }}" class="text-2xl font-black text-slate-100">${{ number_format($pendingAmount, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-2">Membresías activas pendientes de pago</p>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Cupones Activos</span>
                <div class="p-2 bg-purple-500/10 text-purple-400 rounded-lg">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 id="stat_active_promos" class="text-2xl font-black text-slate-100">{{ $promos->filter(fn($p) => (int)$p->is_active === 1)->count() }} Activos</h3>
            <p class="text-[10px] text-slate-500 mt-2">Promociones habilitadas en la sucursal</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-850 pb-0.5">
        <button 
            type="button"
            onclick="switchFinanceTab('membresias')" 
            id="tab-btn-membresias"
            class="finance-tab-btn px-5 py-3 border-b-2 text-xs font-bold uppercase tracking-wider transition-all border-lime-500 text-lime-400 flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>Membresías de Socios</span>
        </button>
        <button 
            type="button"
            onclick="switchFinanceTab('planes')" 
            id="tab-btn-planes"
            class="finance-tab-btn px-5 py-3 border-b-2 text-xs font-bold uppercase tracking-wider transition-all border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4"></i>
            <span>Planes de Suscripción</span>
        </button>
        <button 
            type="button"
            onclick="switchFinanceTab('cupones')" 
            id="tab-btn-cupones"
            class="finance-tab-btn px-5 py-3 border-b-2 text-xs font-bold uppercase tracking-wider transition-all border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2">
            <i data-lucide="ticket" class="w-4 h-4"></i>
            <span>Cupones (Promo Codes)</span>
        </button>
    </div>

    <!-- Tab 1: Membresías de Socios -->
    <div id="tab-content-membresias" class="finance-tab-content bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <h3 class="font-bold text-lg text-slate-100">Historial y Estado de Suscripciones</h3>
                
                <!-- Status Filter Tabs (Todos | Pagados | Pendientes) -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setMembershipStatusFilter('all')" id="m-filter-btn-all" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-all-memberships">{{ $memberships->count() }}</span>)
                    </button>
                    <button type="button" onclick="setMembershipStatusFilter('paid')" id="m-filter-btn-paid" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Pagados (<span id="count-paid-memberships">{{ $memberships->where('payment_status', 'paid')->count() }}</span>)
                    </button>
                    <button type="button" onclick="setMembershipStatusFilter('pending')" id="m-filter-btn-pending" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Pendientes (<span id="count-pending-memberships">{{ $memberships->where('payment_status', 'pending')->count() }}</span>)
                    </button>
                </div>
            </div>

            <!-- Live Search Bar -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="membership_search_input" oninput="onMembershipSearchInput()" placeholder="Buscar socio, correo o plan..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Socio</th>
                        <th class="p-4">Plan contratado</th>
                        <th class="p-4">Vigencia</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-center">Pago</th>
                        <th class="p-4 text-right pr-6">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($memberships as $m)
                        @php
                            $fullName = ($m->user && $m->user->profile) ? $m->user->profile->first_name . ' ' . $m->user->profile->last_name : ($m->user->name ?? 'Socio');
                            $userEmail = $m->user->email ?? '';
                            $planName = $m->plan->name ?? '';
                            $searchKey = strtolower($fullName . ' ' . $userEmail . ' ' . $planName);
                        @endphp
                        <tr id="membership_row_{{ $m->id }}"
                            data-membership-row
                            data-status="{{ $m->payment_status }}"
                            data-search="{{ $searchKey }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img src="{{ ($m->user && $m->user->profile && $m->user->profile->profile_photo) ? asset($m->user->profile->profile_photo) : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-800">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $fullName }}</span>
                                    <span class="block text-[10px] text-slate-500">{{ $userEmail }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-slate-300">{{ $planName }}</span>
                                <span class="block text-[10px] text-slate-500">${{ number_format($m->plan->price ?? 0, 2) }}</span>
                            </td>
                            <td class="p-4 text-slate-400">
                                <span>{{ \Carbon\Carbon::parse($m->start_date)->format('d/m/Y') }}</span>
                                <span class="text-slate-600 font-bold mx-1">al</span>
                                <span>{{ \Carbon\Carbon::parse($m->end_date)->format('d/m/Y') }}</span>
                            </td>
                            <td class="p-4 text-center" id="membership_status_cell_{{ $m->id }}">
                                @if($m->status === 'active')
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                                @elseif($m->status === 'expired')
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Expirado</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-slate-800 text-slate-400 text-[9px] font-bold uppercase rounded-full border border-slate-700">{{ __($m->status) }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center" id="membership_payment_cell_{{ $m->id }}">
                                @if($m->payment_status === 'paid')
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Pagado</span>
                                @elseif($m->payment_status === 'pending')
                                    <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 text-[9px] font-bold uppercase rounded-full border border-amber-500/20">Pendiente</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Deuda</span>
                                @endif
                            </td>
                            <td class="p-4 text-right pr-6" id="membership_action_cell_{{ $m->id }}">
                                @if($m->payment_status !== 'paid')
                                    <button onclick="openPaymentModal({{ $m->id }}, {{ $m->plan->price ?? 0 }})" class="px-3 py-1.5 bg-lime-500 hover:bg-lime-400 text-slate-950 font-extrabold text-[10px] rounded-xl transition-all shadow-sm">
                                        Registrar Pago
                                    </button>
                                @else
                                    <span class="text-slate-500 font-bold text-[10px] uppercase">Facturado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                Ningún socio se ha suscrito a una membresía todavía.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_memberships_search_row" class="hidden">
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            <i data-lucide="user-x" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron membresías que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer for Membresías -->
        <div id="membership_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="membership_pagination_info">Mostrando membresías...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="m_prev_page_btn" onclick="changeMembershipPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="m_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="m_next_page_btn" onclick="changeMembershipPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- Tab 2: Planes de Suscripción -->
    <div id="tab-content-planes" class="finance-tab-content hidden bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl">
        <div class="p-6 border-b border-slate-850 flex flex-col md:flex-row md:items-center justify-between gap-4 -mx-6 -mt-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-100">Planes de Suscripción Disponibles</h3>
                    <span class="text-xs text-slate-400">Planes configurados para ventas de membresías</span>
                </div>
                
                <!-- Status Filter Tabs (Todos | Activos | Inactivos) -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setPlanStatusFilter('all')" id="p-filter-btn-all" class="p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-all-plans">{{ $plans->count() }}</span>)
                    </button>
                    <button type="button" onclick="setPlanStatusFilter('active')" id="p-filter-btn-active" class="p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-active-plans">{{ $plans->filter(fn($p) => (int)$p->is_active === 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setPlanStatusFilter('disabled')" id="p-filter-btn-disabled" class="p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivos (<span id="count-disabled-plans">{{ $plans->filter(fn($p) => (int)$p->is_active === 0)->count() }}</span>)
                    </button>
                </div>
            </div>

            <!-- Live Search Bar -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="plan_search_input" oninput="onPlanSearchInput()" placeholder="Buscar por nombre o descripción..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="plans_grid_container">
            @forelse($plans as $plan)
                <div id="plan_card_{{ $plan->id }}" data-plan-card 
                     data-is-active="{{ $plan->is_active ? '1' : '0' }}"
                     data-search="{{ strtolower($plan->name . ' ' . ($plan->description ?? '')) }}"
                     class="bg-slate-950/40 border border-slate-850 p-5 rounded-2xl flex flex-col justify-between hover:border-slate-700 transition-colors shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-2 gap-2">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-100 text-sm plan-title-text">{{ $plan->name }}</h4>
                                <span id="plan_status_badge_{{ $plan->id }}">
                                    @if($plan->is_active)
                                        <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 text-[9px] font-bold uppercase rounded-full border border-lime-500/20 plan-duration-text">
                                    {{ $plan->duration_days }} Días
                                </span>
                                <button type="button" onclick="openEditPlanModal({{ json_encode($plan) }})" class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm flex items-center justify-center shrink-0" title="Editar Plan">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 line-clamp-2 mb-4 plan-desc-text">{{ $plan->description ?? 'Sin descripción.' }}</p>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-850 pt-3">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-extrabold block leading-none">Precio</span>
                            <span class="text-base font-black text-lime-400 plan-price-text">${{ number_format($plan->price, 2) }} {{ $plan->currency }}</span>
                        </div>

                        <form action="{{ route('finanzas.toggle_plan', $plan->id) }}" method="POST" onsubmit="togglePlanAjax(event, {{ $plan->id }})" class="inline m-0">
                            @csrf
                            <button type="submit" id="plan_toggle_btn_{{ $plan->id }}" class="px-3 py-1.5 rounded-xl text-[10px] font-bold border transition-colors shadow-sm {{ $plan->is_active ? 'bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15' : 'bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400' }}">
                                {{ $plan->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-500 text-sm italic">
                    No hay planes de membresía creados en este gimnasio.
                </div>
            @endforelse
        </div>

        <div id="no_plans_search_container" class="hidden py-10 text-center text-slate-500">
            <i data-lucide="package-search" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
            No se encontraron planes que coincidan con la búsqueda.
        </div>

        <!-- Pagination Footer for Planes -->
        <div id="plan_pagination_container" class="mt-6 pt-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="plan_pagination_info">Mostrando planes...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="p_prev_page_btn" onclick="changePlanPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="p_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="p_next_page_btn" onclick="changePlanPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- Tab 3: Cupones de Descuento -->
    <div id="tab-content-cupones" class="finance-tab-content hidden bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <h3 class="font-bold text-lg text-slate-100">Códigos Promocionales y Descuentos</h3>
                
                <!-- Status Filter Tabs (Todos | Activos | Inactivos) -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setPromoStatusFilter('all')" id="promo-filter-btn-all" class="promo-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-all-promos">{{ $promos->count() }}</span>)
                    </button>
                    <button type="button" onclick="setPromoStatusFilter('active')" id="promo-filter-btn-active" class="promo-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-active-promos">{{ $promos->filter(fn($p) => (int)$p->is_active === 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setPromoStatusFilter('disabled')" id="promo-filter-btn-disabled" class="promo-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivos (<span id="count-disabled-promos">{{ $promos->filter(fn($p) => (int)$p->is_active === 0)->count() }}</span>)
                    </button>
                </div>
            </div>

            <!-- Live Search Bar -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="promo_search_input" oninput="onPromoSearchInput()" placeholder="Buscar código promocional..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Código</th>
                        <th class="p-4">Descuento</th>
                        <th class="p-4">Vigencia</th>
                        <th class="p-4 text-center">Usos</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-right pr-6">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($promos as $promo)
                        <tr id="promo_row_{{ $promo->id }}"
                            data-promo-row
                            data-is-active="{{ $promo->is_active ? '1' : '0' }}"
                            data-search="{{ strtolower($promo->code) }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors">
                            <td class="p-4 pl-6">
                                <span class="px-3 py-1 bg-slate-950 border border-slate-855 rounded-lg text-xs font-black tracking-wide text-lime-400 uppercase">
                                    {{ $promo->code }}
                                </span>
                            </td>
                            <td class="p-4 font-semibold">
                                @if($promo->discount_type === 'percentage')
                                    {{ (float)$promo->discount_value }}% de descuento
                                @else
                                    ${{ number_format($promo->discount_value, 2) }} USD de descuento
                                @endif
                            </td>
                            <td class="p-4 text-slate-400 font-semibold">
                                @if($promo->valid_from || $promo->valid_until)
                                    <span>{{ $promo->valid_from ? \Carbon\Carbon::parse($promo->valid_from)->format('d/m/Y') : 'Inicio' }}</span>
                                    <span class="text-slate-600 font-bold mx-1">al</span>
                                    <span>{{ $promo->valid_until ? \Carbon\Carbon::parse($promo->valid_until)->format('d/m/Y') : 'Siempre' }}</span>
                                @else
                                    <span class="italic text-slate-500">Sin límite</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-slate-300">
                                {{ $promo->current_uses }} / {{ $promo->max_uses ?? '∞' }}
                            </td>
                            <td class="p-4 text-center" id="promo_status_{{ $promo->id }}">
                                @if($promo->is_active)
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/25">Activo</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/25">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4 text-right pr-6">
                                <form action="{{ route('finanzas.toggle_promo', $promo->id) }}" method="POST" onsubmit="togglePromoAjax(event, {{ $promo->id }})" class="inline m-0">
                                    @csrf
                                    <button type="submit" id="promo_btn_{{ $promo->id }}" class="px-3 py-1.5 rounded-lg text-[10px] font-bold border transition-colors shadow-sm {{ $promo->is_active ? 'bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15' : 'bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400' }}">
                                        {{ $promo->is_active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                No se han creado códigos promocionales todavía.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_promos_search_row" class="hidden">
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            <i data-lucide="ticket-slash" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron códigos promocionales que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer for Cupones -->
        <div id="promo_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="promo_pagination_info">Mostrando cupones...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="promo_prev_page_btn" onclick="changePromoPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="promo_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="promo_next_page_btn" onclick="changePromoPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ================= MODAL: CREAR PLAN ================= -->
<div id="plan-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Nuevo Plan de Membresía</h3>
            <button type="button" onclick="toggleModal('plan-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="plan-form" action="{{ route('finanzas.store_plan') }}" method="POST" onsubmit="submitPlanForm(event)" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre del Plan</label>
                <input type="text" name="name" required placeholder="Ej: Plan Anual VIP" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción</label>
                <textarea name="description" placeholder="Incluye acceso libre a máquinas, entrenadores..." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Duración (Días)</label>
                    <input type="number" name="duration_days" required min="1" placeholder="30" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Precio</label>
                    <input type="number" step="0.01" name="price" required placeholder="50.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Moneda</label>
                <input type="text" name="currency" required value="USD" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="includes_trainer" id="includes_trainer" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500">
                <label for="includes_trainer" class="text-xs text-slate-300 font-medium cursor-pointer">¿Incluye servicio de entrenador personal?</label>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('plan-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="plan-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Plan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR PLAN DE MEMBRESÍA ================= -->
<div id="edit-plan-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Plan de Membresía</h3>
            <button type="button" onclick="toggleModal('edit-plan-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-plan-form" method="POST" onsubmit="submitEditPlanForm(event)" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_plan_id" name="plan_id">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre del Plan</label>
                <input type="text" id="edit_plan_name" name="name" required placeholder="Ej: Plan Anual VIP" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción</label>
                <textarea id="edit_plan_description" name="description" placeholder="Incluye acceso libre a máquinas, entrenadores..." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Duración (Días)</label>
                    <input type="number" id="edit_plan_duration_days" name="duration_days" required min="1" placeholder="30" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Precio</label>
                    <input type="number" id="edit_plan_price" step="0.01" name="price" required placeholder="50.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Moneda</label>
                <input type="text" id="edit_plan_currency" name="currency" required value="USD" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="includes_trainer" id="edit_includes_trainer" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500">
                <label for="edit_includes_trainer" class="text-xs text-slate-300 font-medium cursor-pointer">¿Incluye servicio de entrenador personal?</label>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-plan-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-plan-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Actualizar Plan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ASIGNAR PLAN ================= -->
<div id="membership-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Asignar Membresía a Socio</h3>
            <button type="button" onclick="toggleModal('membership-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="membership-form" action="{{ route('finanzas.renew_membership') }}" method="POST" onsubmit="submitMembershipForm(event)" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Socio</label>
                <select name="user_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un atleta...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->profile->first_name }} {{ $client->profile->last_name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Plan</label>
                <select name="plan_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un plan de precios...</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" data-price="{{ $plan->price }}">{{ $plan->name }} (${{ number_format($plan->price, 2) }} - {{ $plan->duration_days }} días)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('membership-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="membership-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar y Pre-Facturar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR PAGO ================= -->
<div id="payment-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Cobro Facturado</h3>
            <button type="button" onclick="toggleModal('payment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="payment-form" action="{{ route('finanzas.record_payment') }}" method="POST" onsubmit="submitPaymentForm(event)" class="space-y-4">
            @csrf
            <input type="hidden" name="user_membership_id" id="payment_membership_id">
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Monto Final a Pagar ($)</label>
                <input type="number" step="0.01" name="amount" id="payment_amount" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Código Promocional (Opcional)</label>
                <div class="flex gap-2">
                    <input type="text" name="promo_code" id="payment_promo_code" placeholder="Ej: VERANO10" class="flex-1 px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 uppercase focus:outline-none focus:border-lime-500/50">
                    <button type="button" onclick="applyPromoCode()" class="px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-slate-100 text-xs font-bold rounded-xl border border-slate-750 transition-colors">
                        Aplicar
                    </button>
                </div>
                <span id="promo-feedback" class="block text-[10px] font-bold mt-1.5 hidden"></span>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Método de Pago</label>
                <select name="payment_method" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="cash" selected>Efectivo</option>
                    <option value="card">Tarjeta de Crédito/Débito</option>
                    <option value="transfer">Transferencia Bancaria</option>
                    <option value="other">Otro</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Número de Referencia (Opcional)</label>
                <input type="text" name="reference_number" placeholder="Ej: TXN-998877" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('payment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="payment-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Cobro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CREAR CUPÓN (PROMO) ================= -->
<div id="promo-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Código Promocional</h3>
            <button type="button" onclick="toggleModal('promo-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="promo-form" action="{{ route('finanzas.store_promo') }}" method="POST" onsubmit="submitPromoForm(event)" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Código (Ej: VERANO25)</label>
                <input type="text" name="code" required placeholder="Ej: APERTURA" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 uppercase focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tipo de Descuento</label>
                    <select name="discount_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="percentage">Porcentaje (%)</option>
                        <option value="fixed">Valor Fijo ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Valor del Descuento</label>
                    <input type="number" step="0.01" name="discount_value" required placeholder="10.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Válido Desde (Opcional)</label>
                    <input type="date" name="valid_from" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Válido Hasta (Opcional)</label>
                    <input type="date" name="valid_until" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Límite de Usos Totales (Opcional)</label>
                <input type="number" name="max_uses" placeholder="Dejar vacío si es ilimitado" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('promo-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="promo-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Crear Cupón
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const isOpening = modal.classList.contains('hidden');
        modal.classList.toggle('hidden');

        if (isOpening) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    }

    let originalAmount = 0;

    function openPaymentModal(membershipId, amount) {
        document.getElementById('payment_membership_id').value = membershipId;
        document.getElementById('payment_amount').value = amount;
        originalAmount = parseFloat(amount);
        
        // Reset promo values in form
        document.getElementById('payment_promo_code').value = '';
        const feedback = document.getElementById('promo-feedback');
        feedback.classList.add('hidden');
        feedback.innerText = '';
        
        toggleModal('payment-modal');
    }

    async function applyPromoCode() {
        const codeInput = document.getElementById('payment_promo_code');
        const code = codeInput.value.trim().toUpperCase();
        const feedback = document.getElementById('promo-feedback');
        const amountInput = document.getElementById('payment_amount');

        if (!code) {
            feedback.className = "block text-[10px] font-bold mt-1.5 text-rose-400";
            feedback.innerText = "Por favor ingresa un código.";
            feedback.classList.remove('hidden');
            return;
        }

        feedback.className = "block text-[10px] font-bold mt-1.5 text-slate-400";
        feedback.innerText = "Validando código...";
        feedback.classList.remove('hidden');

        try {
            const response = await fetch(`/api/promos/validate?code=${encodeURIComponent(code)}`);
            const data = await response.json();

            if (data.valid) {
                let discountAmount = 0;
                if (data.discount_type === 'percentage') {
                    discountAmount = originalAmount * (data.discount_value / 100);
                } else {
                    discountAmount = data.discount_value;
                }

                const finalAmount = Math.max(0, originalAmount - discountAmount);
                amountInput.value = finalAmount.toFixed(2);

                feedback.className = "block text-[10px] font-bold mt-1.5 text-emerald-400";
                feedback.innerText = `¡Código aplicado! Descuento: ${data.discount_type === 'percentage' ? data.discount_value + '%' : '$' + data.discount_value}. Nuevo total: $${finalAmount.toFixed(2)}`;
            } else {
                amountInput.value = originalAmount.toFixed(2);
                feedback.className = "block text-[10px] font-bold mt-1.5 text-rose-400";
                feedback.innerText = data.message;
            }
        } catch (error) {
            console.error(error);
            feedback.className = "block text-[10px] font-bold mt-1.5 text-rose-400";
            feedback.innerText = "Error de servidor al validar el cupón.";
        }
    }

    function setBtnLoading(btn, isLoading, text = 'Procesando...') {
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.classList.add('opacity-80', 'cursor-wait');
            btn.innerHTML = `
                <span class="inline-flex items-center justify-center gap-2 animate-pulse">
                    <svg class="animate-spin h-3.5 w-3.5 text-current shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>${text}</span>
                </span>
            `;
        } else {
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-wait');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }
    }

    function showFinanceToast(message, type = 'success') {
        let container = document.getElementById('finance-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'finance-toast-container';
            container.className = 'fixed top-24 right-6 z-50 flex flex-col gap-2.5 pointer-events-none max-w-xs sm:max-w-sm w-full';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        const isDanger = type === 'danger' || type === 'error';

        let iconName = isDanger ? 'alert-circle' : 'check-circle';
        let borderColor = isDanger ? 'border-rose-500/30' : 'border-emerald-500/30';
        let iconColor = isDanger ? 'text-rose-400' : 'text-emerald-400';
        let glowColor = isDanger ? 'shadow-rose-500/10' : 'shadow-emerald-500/10';

        toast.className = `pointer-events-auto flex items-center gap-3 p-3.5 pr-4 bg-slate-900 border ${borderColor} text-slate-100 text-xs font-semibold rounded-2xl shadow-xl ${glowColor} transition-all duration-300 transform translate-x-10 opacity-0`;

        toast.innerHTML = `
            <div class="p-1.5 rounded-xl bg-slate-950/60 shrink-0 ${iconColor}">
                <i data-lucide="${iconName}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 leading-tight">${message}</div>
            <button type="button" onclick="this.parentElement.remove()" class="p-1 text-slate-400 hover:text-slate-100 text-xs ml-1 shrink-0">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        `;

        container.appendChild(toast);
        if (window.lucide) window.lucide.createIcons();

        setTimeout(() => {
            toast.classList.remove('translate-x-10', 'opacity-0');
        }, 10);

        setTimeout(() => {
            toast.classList.add('translate-x-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    async function togglePromoAjax(e, promoId) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById(`promo_btn_${promoId}`);

        setBtnLoading(submitBtn, true, 'Guardando...');

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
                const promoRow = document.getElementById(`promo_row_${promoId}`);
                if (promoRow) {
                    promoRow.setAttribute('data-is-active', data.is_active ? '1' : '0');
                }

                const statusContainer = document.getElementById(`promo_status_${promoId}`);
                if (statusContainer) {
                    if (data.is_active) {
                        statusContainer.innerHTML = `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/25">Activo</span>`;
                    } else {
                        statusContainer.innerHTML = `<span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/25">Inactivo</span>`;
                    }
                }

                if (submitBtn) {
                    setBtnLoading(submitBtn, false);
                    if (data.is_active) {
                        submitBtn.className = "px-3 py-1.5 rounded-lg text-[10px] font-bold border transition-colors shadow-sm bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15";
                        submitBtn.innerText = "Desactivar";
                    } else {
                        submitBtn.className = "px-3 py-1.5 rounded-lg text-[10px] font-bold border transition-colors shadow-sm bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400";
                        submitBtn.innerText = "Activar";
                    }
                }

                updatePromoTabCounters();
                renderPromoPage();
                showFinanceToast(data.message, 'success');
            } else {
                setBtnLoading(submitBtn, false);
                const errMsg = data.error || data.message || 'Error al cambiar estado del cupón.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            setBtnLoading(submitBtn, false);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        }
    }

    function updateMembershipCounters() {
        const allRows = document.querySelectorAll('[data-membership-row]');
        let paidCount = 0;
        let pendingCount = 0;

        allRows.forEach(row => {
            if (row.getAttribute('data-status') === 'paid') paidCount++;
            else if (row.getAttribute('data-status') === 'pending') pendingCount++;
        });

        const cAll = document.getElementById('count-all-memberships');
        const cPaid = document.getElementById('count-paid-memberships');
        const cPending = document.getElementById('count-pending-memberships');

        if (cAll) cAll.textContent = allRows.length;
        if (cPaid) cPaid.textContent = paidCount;
        if (cPending) cPending.textContent = pendingCount;
    }

    function updateStatCollected(delta) {
        const el = document.getElementById('stat_total_collected');
        if (!el) return;
        let cur = parseFloat(el.getAttribute('data-value') || '0');
        cur = Math.max(0, cur + delta);
        el.setAttribute('data-value', cur.toString());
        el.textContent = `$${cur.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function updateStatPending(delta) {
        const el = document.getElementById('stat_pending_amount');
        if (!el) return;
        let cur = parseFloat(el.getAttribute('data-value') || '0');
        cur = Math.max(0, cur + delta);
        el.setAttribute('data-value', cur.toString());
        el.textContent = `$${cur.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function updatePromoTabCounters() {
        const allRows = document.querySelectorAll('[data-promo-row]');
        let activeCount = 0;
        let disabledCount = 0;

        allRows.forEach(row => {
            if (row.getAttribute('data-is-active') === '1') activeCount++;
            else disabledCount++;
        });

        const cAll = document.getElementById('count-all-promos');
        const cActive = document.getElementById('count-active-promos');
        const cDisabled = document.getElementById('count-disabled-promos');

        if (cAll) cAll.textContent = allRows.length;
        if (cActive) cActive.textContent = activeCount;
        if (cDisabled) cDisabled.textContent = disabledCount;

        const statPromoEl = document.getElementById('stat_active_promos');
        if (statPromoEl) statPromoEl.textContent = `${activeCount} Activos`;
    }

    function updatePlanCounters() {
        const allCards = document.querySelectorAll('[data-plan-card]');
        let activeCount = 0;
        let disabledCount = 0;

        allCards.forEach(card => {
            const val = card.getAttribute('data-is-active');
            if (val === '1' || val === 'true') {
                activeCount++;
            } else {
                disabledCount++;
            }
        });

        const cAll = document.getElementById('count-all-plans');
        const cActive = document.getElementById('count-active-plans');
        const cDisabled = document.getElementById('count-disabled-plans');

        if (cAll) cAll.textContent = allCards.length;
        if (cActive) cActive.textContent = activeCount;
        if (cDisabled) cDisabled.textContent = disabledCount;
    }

    async function togglePlanAjax(e, planId) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById(`plan_toggle_btn_${planId}`);

        setBtnLoading(submitBtn, true, 'Guardando...');

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
                const planCard = document.getElementById(`plan_card_${planId}`);
                if (planCard) {
                    planCard.setAttribute('data-is-active', data.is_active ? '1' : '0');
                }

                const badgeContainer = document.getElementById(`plan_status_badge_${planId}`);
                if (badgeContainer) {
                    if (data.is_active) {
                        badgeContainer.innerHTML = `<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>`;
                    } else {
                        badgeContainer.innerHTML = `<span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`;
                    }
                }

                if (submitBtn) {
                    setBtnLoading(submitBtn, false);
                    if (data.is_active) {
                        submitBtn.className = "px-3 py-1.5 rounded-xl text-[10px] font-bold border transition-colors shadow-sm bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15";
                        submitBtn.innerText = "Desactivar";
                    } else {
                        submitBtn.className = "px-3 py-1.5 rounded-xl text-[10px] font-bold border transition-colors shadow-sm bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400";
                        submitBtn.innerText = "Activar";
                    }
                }

                updatePlanCounters();
                renderPlanPage();
                showFinanceToast(data.message, 'success');
            } else {
                setBtnLoading(submitBtn, false);
                const errMsg = data.error || data.message || 'Error al cambiar estado del plan.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            setBtnLoading(submitBtn, false);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        }
    }

    function openEditPlanModal(plan) {
        document.getElementById('edit_plan_id').value = plan.id;
        document.getElementById('edit_plan_name').value = plan.name;
        document.getElementById('edit_plan_duration_days').value = plan.duration_days;
        document.getElementById('edit_plan_price').value = plan.price;
        document.getElementById('edit_plan_currency').value = plan.currency;
        document.getElementById('edit_plan_description').value = plan.description || '';
        
        const checkBtn = document.getElementById('edit_includes_trainer');
        if (checkBtn) checkBtn.checked = !!plan.includes_trainer;

        const form = document.getElementById('edit-plan-form');
        form.action = `/finanzas/planes/${plan.id}`;
        toggleModal('edit-plan-modal');
    }

    async function submitPlanForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('plan-submit-btn');

        setBtnLoading(submitBtn, true, 'Guardando...');

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
                toggleModal('plan-modal');
                form.reset();
                showFinanceToast(data.message, 'success');

                const plan = data.plan;
                const container = document.getElementById('plans_grid_container');
                const emptyMsg = container.querySelector('.col-span-full');
                if (emptyMsg) emptyMsg.remove();

                const card = document.createElement('div');
                card.id = `plan_card_${plan.id}`;
                card.setAttribute('data-plan-card', '');
                card.setAttribute('data-is-active', plan.is_active ? '1' : '0');
                card.setAttribute('data-search', `${plan.name} ${plan.description || ''}`.toLowerCase());
                card.className = "bg-slate-950/40 border border-slate-850 p-5 rounded-2xl flex flex-col justify-between hover:border-slate-700 transition-colors shadow-sm";

                const formattedPrice = parseFloat(plan.price).toFixed(2);
                const planJson = JSON.stringify(plan).replace(/"/g, '&quot;');

                card.innerHTML = `
                    <div>
                        <div class="flex items-center justify-between mb-2 gap-2">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-100 text-sm plan-title-text">${plan.name}</h4>
                                <span id="plan_status_badge_${plan.id}">
                                    ${plan.is_active ? 
                                        `<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>` : 
                                        `<span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 text-[9px] font-bold uppercase rounded-full border border-lime-500/20 plan-duration-text">
                                    ${plan.duration_days} Días
                                </span>
                                <button type="button" onclick='openEditPlanModal(${planJson})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm flex items-center justify-center shrink-0" title="Editar Plan">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 line-clamp-2 mb-4 plan-desc-text">${plan.description || 'Sin descripción.'}</p>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-850 pt-3">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-extrabold block leading-none">Precio</span>
                            <span class="text-base font-black text-lime-400 plan-price-text">$${formattedPrice} ${plan.currency}</span>
                        </div>

                        <form action="/finanzas/planes/${plan.id}/toggle" method="POST" onsubmit="togglePlanAjax(event, ${plan.id})" class="inline m-0">
                            <button type="submit" id="plan_toggle_btn_${plan.id}" class="px-3 py-1.5 rounded-xl text-[10px] font-bold border transition-colors shadow-sm ${plan.is_active ? 'bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15' : 'bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400'}">
                                ${plan.is_active ? 'Desactivar' : 'Activar'}
                            </button>
                        </form>
                    </div>
                `;

                container.prepend(card);
                updatePlanCounters();
                renderPlanPage();
            } else {
                const errMsg = data.error || data.message || 'Error al crear el plan.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitEditPlanForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-plan-submit-btn');

        setBtnLoading(submitBtn, true, 'Actualizando...');

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
                toggleModal('edit-plan-modal');
                showFinanceToast(data.message, 'success');

                const plan = data.plan;
                const card = document.getElementById(`plan_card_${plan.id}`);
                if (card) {
                    const formattedPrice = parseFloat(plan.price).toFixed(2);
                    const planJson = JSON.stringify(plan).replace(/"/g, '&quot;');
                    
                    card.setAttribute('data-is-active', plan.is_active ? '1' : '0');
                    card.setAttribute('data-search', `${plan.name} ${plan.description || ''}`.toLowerCase());
                    card.innerHTML = `
                        <div>
                            <div class="flex items-center justify-between mb-2 gap-2">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-100 text-sm plan-title-text">${plan.name}</h4>
                                    <span id="plan_status_badge_${plan.id}">
                                        ${plan.is_active ? 
                                            `<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>` : 
                                            `<span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 text-[9px] font-bold uppercase rounded-full border border-lime-500/20 plan-duration-text">
                                        ${plan.duration_days} Días
                                    </span>
                                    <button type="button" onclick='openEditPlanModal(${planJson})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm flex items-center justify-center shrink-0" title="Editar Plan">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 line-clamp-2 mb-4 plan-desc-text">${plan.description || 'Sin descripción.'}</p>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-850 pt-3">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-extrabold block leading-none">Precio</span>
                                <span class="text-base font-black text-lime-400 plan-price-text">$${formattedPrice} ${plan.currency}</span>
                            </div>

                            <form action="/finanzas/planes/${plan.id}/toggle" method="POST" onsubmit="togglePlanAjax(event, ${plan.id})" class="inline m-0">
                                <button type="submit" id="plan_toggle_btn_${plan.id}" class="px-3 py-1.5 rounded-xl text-[10px] font-bold border transition-colors shadow-sm ${plan.is_active ? 'bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15' : 'bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400'}">
                                    ${plan.is_active ? 'Desactivar' : 'Activar'}
                                </button>
                            </form>
                        </div>
                    `;
                }
                updatePlanCounters();
                renderPlanPage();
            } else {
                const errMsg = data.error || data.message || 'Error al actualizar el plan.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitMembershipForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('membership-submit-btn');

        setBtnLoading(submitBtn, true, 'Asignando...');

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
                toggleModal('membership-modal');
                showFinanceToast(data.message, 'success');

                const m = data.membership;
                const tbody = document.querySelector('#tab-content-membresias tbody');
                const emptyRow = tbody.querySelector('tr:not([data-membership-row]):not(#no_memberships_search_row)');
                if (emptyRow) emptyRow.remove();

                const clientSelect = form.querySelector('[name="user_id"]');
                const clientName = clientSelect ? clientSelect.options[clientSelect.selectedIndex].text : 'Socio';
                const planSelect = form.querySelector('[name="plan_id"]');
                const selectedOpt = planSelect ? planSelect.options[planSelect.selectedIndex] : null;
                const planName = selectedOpt ? selectedOpt.text : 'Plan';
                const planPrice = parseFloat(selectedOpt?.getAttribute('data-price') || '0');

                if (planPrice > 0) {
                    updateStatPending(planPrice);
                }

                const newRow = document.createElement('tr');
                newRow.id = `membership_row_${m.id}`;
                newRow.setAttribute('data-membership-row', '');
                newRow.setAttribute('data-status', m.payment_status || 'pending');
                newRow.setAttribute('data-search', `${clientName} ${planName}`.toLowerCase());
                newRow.className = "hover:bg-slate-900/20 text-slate-200 transition-colors";

                newRow.innerHTML = `
                    <td class="p-4 pl-6 flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-800">
                        <div>
                            <span class="block font-bold text-slate-100">${clientName}</span>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="font-bold text-slate-300">${planName}</span>
                    </td>
                    <td class="p-4 text-slate-400">
                        <span>${m.start_date ? m.start_date.substring(0, 10) : ''}</span>
                        <span class="text-slate-600 font-bold mx-1">al</span>
                        <span>${m.end_date ? m.end_date.substring(0, 10) : ''}</span>
                    </td>
                    <td class="p-4 text-center" id="membership_status_cell_${m.id}">
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                    </td>
                    <td class="p-4 text-center" id="membership_payment_cell_${m.id}">
                        <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 text-[9px] font-bold uppercase rounded-full border border-amber-500/20">Pendiente</span>
                    </td>
                    <td class="p-4 text-right pr-6" id="membership_action_cell_${m.id}">
                        <button onclick="openPaymentModal(${m.id}, ${planPrice})" class="px-3 py-1.5 bg-lime-500 hover:bg-lime-400 text-slate-950 font-extrabold text-[10px] rounded-xl transition-all shadow-sm">
                            Registrar Pago
                        </button>
                    </td>
                `;

                tbody.prepend(newRow);
                form.reset();
                updateMembershipCounters();
                renderMembershipPage();
            } else {
                const errMsg = data.error || data.message || 'Error al asignar membresía.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitPaymentForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('payment-submit-btn');

        setBtnLoading(submitBtn, true, 'Registrando...');

        try {
            const paidAmount = parseFloat(form.querySelector('[name="amount"]')?.value || '0');

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
                toggleModal('payment-modal');
                form.reset();
                showFinanceToast(data.message, 'success');

                if (paidAmount > 0) {
                    updateStatCollected(paidAmount);
                    updateStatPending(-paidAmount);
                }

                const m = data.membership;
                const row = document.getElementById(`membership_row_${m.id}`);
                if (row) {
                    row.setAttribute('data-status', 'paid');
                    const paymentCell = document.getElementById(`membership_payment_cell_${m.id}`);
                    if (paymentCell) {
                        paymentCell.innerHTML = `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Pagado</span>`;
                    }
                    const actionCell = document.getElementById(`membership_action_cell_${m.id}`);
                    if (actionCell) {
                        actionCell.innerHTML = `<span class="text-slate-500 font-bold text-[10px] uppercase">Facturado</span>`;
                    }
                }

                updateMembershipCounters();
                renderMembershipPage();
            } else {
                const errMsg = data.error || data.message || 'Error al registrar el cobro.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitPromoForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('promo-submit-btn');

        setBtnLoading(submitBtn, true, 'Creando...');

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
                toggleModal('promo-modal');
                form.reset();
                showFinanceToast(data.message, 'success');

                const promo = data.promo;
                const tbody = document.querySelector('#tab-content-cupones tbody');
                const emptyRow = tbody.querySelector('tr:not([data-promo-row]):not(#no_promos_search_row)');
                if (emptyRow) emptyRow.remove();

                const newRow = document.createElement('tr');
                newRow.id = `promo_row_${promo.id}`;
                newRow.setAttribute('data-promo-row', '');
                newRow.setAttribute('data-is-active', promo.is_active ? '1' : '0');
                newRow.setAttribute('data-search', promo.code.toLowerCase());
                newRow.className = "hover:bg-slate-900/20 text-slate-200 transition-colors";

                const discountText = promo.discount_type === 'percentage' 
                    ? `${parseFloat(promo.discount_value)}% de descuento`
                    : `$${parseFloat(promo.discount_value).toFixed(2)} USD de descuento`;

                newRow.innerHTML = `
                    <td class="p-4 pl-6">
                        <span class="px-3 py-1 bg-slate-950 border border-slate-855 rounded-lg text-xs font-black tracking-wide text-lime-400 uppercase">
                            ${promo.code}
                        </span>
                    </td>
                    <td class="p-4 font-semibold">${discountText}</td>
                    <td class="p-4 text-slate-400 font-semibold">
                        <span class="italic text-slate-500">Sin límite</span>
                    </td>
                    <td class="p-4 text-center font-bold text-slate-300">
                        ${promo.current_uses} / ${promo.max_uses || '∞'}
                    </td>
                    <td class="p-4 text-center" id="promo_status_${promo.id}">
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/25">Activo</span>
                    </td>
                    <td class="p-4 text-right pr-6">
                        <form action="/finanzas/promos/${promo.id}/toggle" method="POST" onsubmit="togglePromoAjax(event, ${promo.id})" class="inline m-0">
                            <button type="submit" id="promo_btn_${promo.id}" class="px-3 py-1.5 rounded-lg text-[10px] font-bold border transition-colors shadow-sm bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15">
                                Desactivar
                            </button>
                        </form>
                    </td>
                `;

                tbody.prepend(newRow);
                form.reset();
                updatePromoTabCounters();
                renderPromoPage();
            } else {
                const errMsg = data.error || data.message || 'Error al crear el cupón.';
                showFinanceToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showFinanceToast('Ocurrió un error al procesar la solicitud.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // TAB SWITCHING LOGIC
    let currentFinanceTab = 'membresias';

    function switchFinanceTab(tabName) {
        currentFinanceTab = tabName;

        // Hide all tab contents
        document.querySelectorAll('.finance-tab-content').forEach(el => el.classList.add('hidden'));

        // Reset all tab button styles
        document.querySelectorAll('.finance-tab-btn').forEach(btn => {
            btn.className = "finance-tab-btn px-5 py-3 border-b-2 text-xs font-bold uppercase tracking-wider transition-all border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2";
        });

        // Show active content & style active button
        const activeContent = document.getElementById('tab-content-' + tabName);
        if (activeContent) activeContent.classList.remove('hidden');

        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.className = "finance-tab-btn px-5 py-3 border-b-2 text-xs font-bold uppercase tracking-wider transition-all border-lime-500 text-lime-400 flex items-center gap-2";
        }

        if (tabName === 'membresias') renderMembershipPage();
        else if (tabName === 'planes') renderPlanPage();
        else if (tabName === 'cupones') renderPromoPage();

        if (window.lucide) window.lucide.createIcons();
    }

    // MEMBRESIAS FILTER & PAGINATION
    var currentMembershipPage = 1;
    var currentMembershipStatusFilter = 'all';
    var itemsPerPage = 10;

    function setMembershipStatusFilter(status) {
        currentMembershipStatusFilter = status;
        document.querySelectorAll('.m-status-tab-btn').forEach(btn => {
            btn.className = "m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });
        const activeBtn = document.getElementById('m-filter-btn-' + status);
        if (activeBtn) {
            activeBtn.className = "m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }
        currentMembershipPage = 1;
        renderMembershipPage();
    }

    function renderMembershipPage() {
        const query = (document.getElementById('membership_search_input')?.value || '').toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('[data-membership-row]'));

        const matchingRows = allRows.filter(row => {
            const status = row.getAttribute('data-status') || '';
            const search = row.getAttribute('data-search') || '';

            let matchesStatus = true;
            if (currentMembershipStatusFilter === 'paid') matchesStatus = (status === 'paid');
            else if (currentMembershipStatusFilter === 'pending') matchesStatus = (status === 'pending');

            let matchesSearch = true;
            if (query) matchesSearch = search.includes(query);

            return matchesStatus && matchesSearch;
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / itemsPerPage) || 1;
        if (currentMembershipPage > totalPages) currentMembershipPage = totalPages;
        if (currentMembershipPage < 1) currentMembershipPage = 1;

        allRows.forEach(row => row.classList.add('hidden'));

        const startIndex = (currentMembershipPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageRows = matchingRows.slice(startIndex, endIndex);

        pageRows.forEach(row => row.classList.remove('hidden'));

        const emptyRow = document.getElementById('no_memberships_search_row');
        if (emptyRow) {
            if (totalMatching === 0 && allRows.length > 0) emptyRow.classList.remove('hidden');
            else emptyRow.classList.add('hidden');
        }

        const infoSpan = document.getElementById('membership_pagination_info');
        if (infoSpan) {
            const from = totalMatching === 0 ? 0 : startIndex + 1;
            const to = Math.min(endIndex, totalMatching);
            infoSpan.textContent = `Mostrando ${from} a ${to} de ${totalMatching} membresías`;
        }

        const pageDisplay = document.getElementById('m_page_number_display');
        if (pageDisplay) pageDisplay.textContent = `Página ${currentMembershipPage} de ${totalPages}`;

        const prevBtn = document.getElementById('m_prev_page_btn');
        if (prevBtn) prevBtn.disabled = (currentMembershipPage <= 1);

        const nextBtn = document.getElementById('m_next_page_btn');
        if (nextBtn) nextBtn.disabled = (currentMembershipPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function onMembershipSearchInput() {
        currentMembershipPage = 1;
        renderMembershipPage();
    }

    function changeMembershipPage(delta) {
        currentMembershipPage += delta;
        renderMembershipPage();
    }

    // PLANES FILTER & PAGINATION
    let currentPlanPage = 1;
    let currentPlanStatusFilter = 'all';

    function setPlanStatusFilter(status) {
        currentPlanStatusFilter = status;
        document.querySelectorAll('.p-status-tab-btn').forEach(btn => {
            btn.className = "p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });
        const activeBtn = document.getElementById('p-filter-btn-' + status);
        if (activeBtn) {
            activeBtn.className = "p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }
        currentPlanPage = 1;
        renderPlanPage();
    }

    function renderPlanPage() {
        const query = (document.getElementById('plan_search_input')?.value || '').toLowerCase().trim();
        const allCards = Array.from(document.querySelectorAll('[data-plan-card]'));

        const matchingCards = allCards.filter(card => {
            const isActive = card.getAttribute('data-is-active') === '1';
            const search = card.getAttribute('data-search') || '';

            let matchesStatus = true;
            if (currentPlanStatusFilter === 'active') matchesStatus = isActive;
            else if (currentPlanStatusFilter === 'disabled') matchesStatus = !isActive;

            let matchesSearch = true;
            if (query) matchesSearch = search.includes(query);

            return matchesStatus && matchesSearch;
        });

        const totalMatching = matchingCards.length;
        const totalPages = Math.ceil(totalMatching / itemsPerPage) || 1;
        if (currentPlanPage > totalPages) currentPlanPage = totalPages;
        if (currentPlanPage < 1) currentPlanPage = 1;

        allCards.forEach(card => card.classList.add('hidden'));

        const startIndex = (currentPlanPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageCards = matchingCards.slice(startIndex, endIndex);

        pageCards.forEach(card => card.classList.remove('hidden'));

        const emptyContainer = document.getElementById('no_plans_search_container');
        if (emptyContainer) {
            if (totalMatching === 0 && allCards.length > 0) emptyContainer.classList.remove('hidden');
            else emptyContainer.classList.add('hidden');
        }

        const infoSpan = document.getElementById('plan_pagination_info');
        if (infoSpan) {
            const from = totalMatching === 0 ? 0 : startIndex + 1;
            const to = Math.min(endIndex, totalMatching);
            infoSpan.textContent = `Mostrando ${from} a ${to} de ${totalMatching} planes`;
        }

        const pageDisplay = document.getElementById('p_page_number_display');
        if (pageDisplay) pageDisplay.textContent = `Página ${currentPlanPage} de ${totalPages}`;

        const prevBtn = document.getElementById('p_prev_page_btn');
        if (prevBtn) prevBtn.disabled = (currentPlanPage <= 1);

        const nextBtn = document.getElementById('p_next_page_btn');
        if (nextBtn) nextBtn.disabled = (currentPlanPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function onPlanSearchInput() {
        currentPlanPage = 1;
        renderPlanPage();
    }

    function changePlanPage(delta) {
        currentPlanPage += delta;
        renderPlanPage();
    }

    // CUPONES (PROMOS) FILTER & PAGINATION
    let currentPromoPage = 1;
    let currentPromoStatusFilter = 'all';

    function setPromoStatusFilter(status) {
        currentPromoStatusFilter = status;
        document.querySelectorAll('.promo-status-tab-btn').forEach(btn => {
            btn.className = "promo-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });
        const activeBtn = document.getElementById('promo-filter-btn-' + status);
        if (activeBtn) {
            activeBtn.className = "promo-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }
        currentPromoPage = 1;
        renderPromoPage();
    }

    function renderPromoPage() {
        const query = (document.getElementById('promo_search_input')?.value || '').toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('[data-promo-row]'));

        const matchingRows = allRows.filter(row => {
            const isActive = row.getAttribute('data-is-active') === '1';
            const search = row.getAttribute('data-search') || '';

            let matchesStatus = true;
            if (currentPromoStatusFilter === 'active') matchesStatus = isActive;
            else if (currentPromoStatusFilter === 'disabled') matchesStatus = !isActive;

            let matchesSearch = true;
            if (query) matchesSearch = search.includes(query);

            return matchesStatus && matchesSearch;
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / itemsPerPage) || 1;
        if (currentPromoPage > totalPages) currentPromoPage = totalPages;
        if (currentPromoPage < 1) currentPromoPage = 1;

        allRows.forEach(row => row.classList.add('hidden'));

        const startIndex = (currentPromoPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageRows = matchingRows.slice(startIndex, endIndex);

        pageRows.forEach(row => row.classList.remove('hidden'));

        const emptyRow = document.getElementById('no_promos_search_row');
        if (emptyRow) {
            if (totalMatching === 0 && allRows.length > 0) emptyRow.classList.remove('hidden');
            else emptyRow.classList.add('hidden');
        }

        const infoSpan = document.getElementById('promo_pagination_info');
        if (infoSpan) {
            const from = totalMatching === 0 ? 0 : startIndex + 1;
            const to = Math.min(endIndex, totalMatching);
            infoSpan.textContent = `Mostrando ${from} a ${to} de ${totalMatching} cupones`;
        }

        const pageDisplay = document.getElementById('promo_page_number_display');
        if (pageDisplay) pageDisplay.textContent = `Página ${currentPromoPage} de ${totalPages}`;

        const prevBtn = document.getElementById('promo_prev_page_btn');
        if (prevBtn) prevBtn.disabled = (currentPromoPage <= 1);

        const nextBtn = document.getElementById('promo_next_page_btn');
        if (nextBtn) nextBtn.disabled = (currentPromoPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function onPromoSearchInput() {
        currentPromoPage = 1;
        renderPromoPage();
    }

    function changePromoPage(delta) {
        currentPromoPage += delta;
        renderPromoPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateMembershipCounters();
        updatePlanCounters();
        updatePromoTabCounters();
        switchFinanceTab('membresias');
    });
</script>
@endsection
