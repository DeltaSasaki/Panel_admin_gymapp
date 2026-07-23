@extends('layouts.admin')

@section('title', 'Planes de Suscripción SaaS')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="credit-card" class="w-8 h-8 text-lime-400"></i>
                Planes de Suscripción SaaS
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Administra las tarifas mensuales, moneda y límites de usuarios/entrenadores para las sucursales.</p>
        </div>
        <button type="button" onclick="openCreatePlanModal()" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
            Nuevo Plan SaaS
        </button>
    </div>

    <!-- Metrics Summary Cards (Optimized without GPU-heavy blur) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Planes Configurados</span>
                <h3 class="text-2xl font-black text-slate-100"><span id="stat_total_plans">{{ $plans->count() }}</span> <span class="text-xs font-normal text-slate-400">planes</span></h3>
            </div>
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-400">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Planes Activos</span>
                <h3 class="text-2xl font-black text-emerald-400"><span id="stat_active_plans">{{ $plans->where('is_active', 1)->count() }}</span> <span class="text-xs font-normal text-slate-400">disponibles</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-rose-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Planes Deshabilitados</span>
                <h3 class="text-2xl font-black text-rose-400"><span id="stat_inactive_plans">{{ $plans->where('is_active', 0)->count() }}</span> <span class="text-xs font-normal text-slate-400">inactivos</span></h3>
            </div>
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400">
                <i data-lucide="eye-off" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Sucursales Adheridas</span>
                <h3 class="text-2xl font-black text-amber-400"><span id="stat_total_gyms_assigned">{{ $plans->sum('gyms_count') }}</span> <span class="text-xs font-normal text-slate-400">sedes</span></h3>
            </div>
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400">
                <i data-lucide="building" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Live Search Bar Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
        <div class="flex flex-wrap items-center gap-3">
            <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-300 mr-2 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Estado del Plan:
            </h3>

            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                <button type="button" onclick="setPlanStatusFilter('all')" id="plan-status-filter-all" class="plan-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                    Todos (<span id="count-plan-all">{{ $plans->count() }}</span>)
                </button>
                <button type="button" onclick="setPlanStatusFilter('1')" id="plan-status-filter-1" class="plan-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Activos (<span id="count-plan-active">{{ $plans->where('is_active', 1)->count() }}</span>)
                </button>
                <button type="button" onclick="setPlanStatusFilter('0')" id="plan-status-filter-0" class="plan-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Deshabilitados (<span id="count-plan-inactive">{{ $plans->where('is_active', 0)->count() }}</span>)
                </button>
            </div>
        </div>

        <!-- Live Search Bar -->
        <div class="relative w-full xl:w-72">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-plan-input" oninput="onPlanFilterChange()" placeholder="Buscar por nombre o descripción..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
        </div>
    </div>

    <!-- SaaS Plans Grid Container (Performance-optimized, 6 per page) -->
    <div id="plans-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            @php
                $pJsonStr = json_encode($plan);
                $currSymbol = ($plan->currency === 'USD') ? '$' : (($plan->currency === 'EUR') ? '€' : $plan->currency);
            @endphp
            <div id="plan_card_{{ $plan->id }}"
                 data-plan-card
                 data-name="{{ strtolower($plan->name) }}"
                 data-desc="{{ strtolower($plan->description ?? '') }}"
                 data-active="{{ $plan->is_active ? 1 : 0 }}"
                 class="bg-slate-900 border border-slate-800 rounded-3xl p-6 hover:border-lime-500/40 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl {{ $plan->is_active ? '' : 'opacity-60 bg-slate-950/60 border-slate-850' }}">
                
                <div class="space-y-4">
                    <!-- Top Card Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-black text-lg text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="plan_name_{{ $plan->id }}">{{ $plan->name }}</h3>
                            <span id="plan_gyms_badge_{{ $plan->id }}" class="inline-block mt-1 px-2.5 py-0.5 bg-slate-950 text-slate-300 border border-slate-800 text-[10px] font-extrabold rounded-lg">
                                <i data-lucide="building" class="w-3 h-3 inline text-lime-400 -mt-0.5"></i> <span id="plan_gyms_count_{{ $plan->id }}">{{ $plan->gyms_count }}</span> Sucursal(es) Asignada(s)
                            </span>
                        </div>

                        <span id="plan_status_badge_{{ $plan->id }}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 {{ $plan->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                            {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-slate-400 line-clamp-2 min-h-[2rem]" id="plan_desc_{{ $plan->id }}">{{ $plan->description ?? 'Sin descripción detallada.' }}</p>

                    <!-- Price Tag -->
                    <div class="p-4 bg-slate-950 border border-slate-850 rounded-2xl flex items-baseline justify-between">
                        <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Tarifa Mensual</span>
                        <div class="text-right">
                            <span class="text-2xl font-black text-lime-400" id="plan_price_{{ $plan->id }}">{{ $currSymbol }} {{ number_format($plan->monthly_price, 2) }}</span>
                            <span class="text-[10px] text-slate-500 font-bold block">/ mes por sucursal</span>
                        </div>
                    </div>

                    <!-- Cupos & Limits grid -->
                    <div class="grid grid-cols-2 gap-3 text-xs font-semibold">
                        <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Límite Atletas</span>
                            <span class="font-black text-slate-200 text-sm" id="plan_max_users_{{ $plan->id }}">
                                {{ $plan->max_users ? number_format($plan->max_users) . ' máx.' : 'Ilimitados ✨' }}
                            </span>
                        </div>
                        <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Límite Staff / Entrenadores</span>
                            <span class="font-black text-slate-200 text-sm" id="plan_max_trainers_{{ $plan->id }}">
                                {{ $plan->max_trainers ? number_format($plan->max_trainers) . ' máx.' : 'Ilimitados ✨' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Footer Action Buttons -->
                <div class="flex justify-between items-center border-t border-slate-800 pt-4 text-xs font-semibold">
                    <span class="text-[10px] text-slate-500 font-mono font-bold" id="plan_curr_code_{{ $plan->id }}">Moneda: {{ $plan->currency }}</span>

                    <div class="flex items-center gap-2">
                        <!-- Edit Button -->
                        <button type="button" onclick='openEditPlanModal({{ $pJsonStr }})' class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm flex items-center gap-1.5" title="Editar Plan SaaS">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Editar</span>
                        </button>

                        <!-- Toggle Active Status Button (Inhabilitar / Reactivar) -->
                        <button type="button" onclick="openTogglePlanModal({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->is_active ? 1 : 0 }})" 
                                id="plan_toggle_btn_{{ $plan->id }}"
                                class="p-2 {{ $plan->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                title="{{ $plan->is_active ? 'Deshabilitar Plan' : 'Reactivar Plan' }}">
                            <i data-lucide="{{ $plan->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        @empty
            <div id="no_plans_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900 border border-slate-800 rounded-3xl">
                <i data-lucide="credit-card" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                <p class="font-bold text-slate-400">No hay planes de suscripción SaaS creados</p>
                <p class="text-xs text-slate-500 mt-1">Crea tu primer plan haciendo clic en "Nuevo Plan SaaS".</p>
            </div>
        @endforelse

        <div id="no_plans_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900 border border-slate-800 rounded-3xl hidden">
            <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
            <p class="font-bold text-slate-400 text-sm">No se encontraron planes SaaS que coincidan con la búsqueda.</p>
        </div>
    </div>

    <!-- Plans Pagination Controls Footer -->
    <div id="plan_pagination_container" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
        <span id="plan_pagination_info">Mostrando planes...</span>
        <div class="flex items-center gap-2">
            <button type="button" id="plan_prev_page_btn" onclick="changePlanPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Anterior
            </button>
            <span id="plan_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
            <button type="button" id="plan_next_page_btn" onclick="changePlanPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Siguiente
            </button>
        </div>
    </div>

</div>

<!-- ================= MODAL: NUEVO PLAN SAAS ================= -->
<div id="modal-create-plan" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i> Crear Nuevo Plan SaaS
            </h3>
            <button type="button" onclick="toggleModal('modal-create-plan')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-plan-form" action="{{ route('superadmin.plans.store') }}" method="POST" onsubmit="submitCreatePlan(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="create_plan_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plan *</label>
                <input type="text" name="name" id="create_plan_name" required placeholder="Ej: Plan Pro Sucursales" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_monthly_price" class="block text-slate-400 uppercase tracking-wider mb-1.5">Precio Mensual *</label>
                    <input type="number" step="0.01" name="monthly_price" id="create_monthly_price" required min="0" placeholder="49.99" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_currency" class="block text-slate-400 uppercase tracking-wider mb-1.5">Moneda *</label>
                    <select name="currency" id="create_currency" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="USD" selected>USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="PEN">PEN (S/)</option>
                        <option value="MXN">MXN ($)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_max_users" class="block text-slate-400 uppercase tracking-wider mb-1.5">Límite Atletas (Miembros)</label>
                    <input type="number" name="max_users" id="create_max_users" min="1" placeholder="Ej: 500 (Vacio = Ilimitado)" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_max_trainers" class="block text-slate-400 uppercase tracking-wider mb-1.5">Límite Staff / Entrenadores</label>
                    <input type="number" name="max_trainers" id="create_max_trainers" min="1" placeholder="Ej: 10 (Vacio = Ilimitado)" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="create_plan_desc" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción del Plan</label>
                <textarea name="description" id="create_plan_desc" rows="3" placeholder="Detalles de beneficios, soporte y características..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-plan')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-plan-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Plan SaaS</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR PLAN SAAS ================= -->
<div id="modal-edit-plan" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Plan SaaS
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-plan')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-plan-form" action="" method="POST" onsubmit="submitEditPlan(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_plan_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plan *</label>
                <input type="text" name="name" id="edit_plan_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_monthly_price" class="block text-slate-400 uppercase tracking-wider mb-1.5">Precio Mensual *</label>
                    <input type="number" step="0.01" name="monthly_price" id="edit_monthly_price" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_currency" class="block text-slate-400 uppercase tracking-wider mb-1.5">Moneda *</label>
                    <select name="currency" id="edit_currency" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="PEN">PEN (S/)</option>
                        <option value="MXN">MXN ($)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_max_users" class="block text-slate-400 uppercase tracking-wider mb-1.5">Límite Atletas (Miembros)</label>
                    <input type="number" name="max_users" id="edit_max_users" min="1" placeholder="Vacio = Ilimitados" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_max_trainers" class="block text-slate-400 uppercase tracking-wider mb-1.5">Límite Staff / Entrenadores</label>
                    <input type="number" name="max_trainers" id="edit_max_trainers" min="1" placeholder="Vacio = Ilimitados" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="edit_plan_desc" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción del Plan</label>
                <textarea name="description" id="edit_plan_desc" rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-plan')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-plan-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO / DESHABILITAR ================= -->
<div id="modal-toggle-plan" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-plan-status-title">Cambiar Estado del Plan</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Gestión de disponibilidad
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-toggle-plan')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-plan-status-desc">
            ¿Estás seguro de que deseas modificar la disponibilidad de este plan SaaS?
        </p>

        <form id="toggle-plan-form" action="" method="POST" onsubmit="submitTogglePlan(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            <button type="button" onclick="toggleModal('modal-toggle-plan')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="toggle-plan-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-plan-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Floating Toast Notifications System
    function showToast(message, type = 'success') {
        let container = document.getElementById('plan-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'plan-toast-container';
            container.className = 'fixed top-24 right-6 z-50 flex flex-col gap-2.5 pointer-events-none max-w-xs sm:max-w-sm w-full';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const isDanger = type === 'danger' || type === 'error';

        let iconName = 'check-circle';
        let borderColor = 'border-emerald-500/30';
        let iconColor = 'text-emerald-400';
        let glowColor = 'shadow-emerald-500/10';

        if (isDanger) {
            iconName = 'alert-circle';
            borderColor = 'border-rose-500/30';
            iconColor = 'text-rose-400';
            glowColor = 'shadow-rose-500/10';
        } else if (type === 'warning') {
            iconName = 'alert-triangle';
            borderColor = 'border-amber-500/30';
            iconColor = 'text-amber-400';
            glowColor = 'shadow-amber-500/10';
        }

        toast.className = `pointer-events-auto flex items-center gap-3 p-3.5 pr-4 bg-slate-900 border ${borderColor} text-slate-100 text-xs font-semibold rounded-2xl shadow-xl ${glowColor} transition-all duration-300 transform translate-x-10 opacity-0`;

        toast.innerHTML = `
            <div class="p-1.5 rounded-xl bg-slate-950/60 shrink-0 ${iconColor}">
                <i data-lucide="${iconName}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 leading-tight">${escapeHtml(message)}</div>
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
        }, 3800);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
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

    // Centered Static Modal Handler
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

    function openCreatePlanModal() {
        document.getElementById('create-plan-form').reset();
        toggleModal('modal-create-plan');
    }

    function openEditPlanModal(plan) {
        document.getElementById('edit-plan-form').action = `/superadmin/planes/${plan.id}`;
        document.getElementById('edit_plan_name').value = plan.name || '';
        document.getElementById('edit_monthly_price').value = plan.monthly_price || 0;
        document.getElementById('edit_currency').value = plan.currency || 'USD';
        document.getElementById('edit_max_users').value = plan.max_users || '';
        document.getElementById('edit_max_trainers').value = plan.max_trainers || '';
        document.getElementById('edit_plan_desc').value = plan.description || '';

        toggleModal('modal-edit-plan');
    }

    function openTogglePlanModal(id, planName, isActive) {
        document.getElementById('toggle-plan-form').action = `/superadmin/planes/${id}/toggle`;
        const titleEl = document.getElementById('modal-plan-status-title');
        const descEl = document.getElementById('modal-plan-status-desc');
        const btnTextEl = document.getElementById('modal-plan-status-btn-text');
        const submitBtn = document.getElementById('toggle-plan-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Deshabilitar Plan SaaS';
            descEl.innerHTML = `¿Estás seguro de que deseas deshabilitar el plan <strong class="text-slate-100">${escapeHtml(planName)}</strong>? No podrá asignarse a nuevas sucursales.`;
            btnTextEl.textContent = 'Sí, Deshabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Plan SaaS';
            descEl.innerHTML = `¿Deseas reactivar el plan <strong class="text-slate-100">${escapeHtml(planName)}</strong> para permitir nuevas asignaciones?`;
            btnTextEl.textContent = 'Sí, Reactivar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-toggle-plan');
    }

    // AJAX Submission: Create Plan
    async function submitCreatePlan(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-plan-submit-btn');

        setBtnLoading(submitBtn, true, 'Creando...');

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const p = data.plan;
                const container = document.getElementById('plans-grid-container');
                const emptyMsg = document.getElementById('no_plans_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const pJsonStr = JSON.stringify(p).replace(/'/g, "&#39;");
                const currSymbol = (p.currency === 'USD') ? '$' : ((p.currency === 'EUR') ? '€' : p.currency);
                const maxUsersText = p.max_users ? `${parseInt(p.max_users).toLocaleString()} máx.` : 'Ilimitados ✨';
                const maxTrainersText = p.max_trainers ? `${parseInt(p.max_trainers).toLocaleString()} máx.` : 'Ilimitados ✨';

                const card = document.createElement('div');
                card.id = `plan_card_${p.id}`;
                card.setAttribute('data-plan-card', '');
                card.setAttribute('data-name', (p.name || '').toLowerCase());
                card.setAttribute('data-desc', (p.description || '').toLowerCase());
                card.setAttribute('data-active', '1');
                card.className = 'bg-slate-900 border border-slate-800 rounded-3xl p-6 hover:border-lime-500/40 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl';

                card.innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-black text-lg text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="plan_name_${p.id}">${escapeHtml(p.name)}</h3>
                                <span id="plan_gyms_badge_${p.id}" class="inline-block mt-1 px-2.5 py-0.5 bg-slate-950 text-slate-300 border border-slate-800 text-[10px] font-extrabold rounded-lg">
                                    <i data-lucide="building" class="w-3 h-3 inline text-lime-400 -mt-0.5"></i> <span id="plan_gyms_count_${p.id}">0</span> Sucursal(es) Asignada(s)
                                </span>
                            </div>

                            <span id="plan_status_badge_${p.id}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                Activo
                            </span>
                        </div>

                        <p class="text-xs text-slate-400 line-clamp-2 min-h-[2rem]" id="plan_desc_${p.id}">${escapeHtml(p.description || 'Sin descripción detallada.')}</p>

                        <div class="p-4 bg-slate-950 border border-slate-850 rounded-2xl flex items-baseline justify-between">
                            <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Tarifa Mensual</span>
                            <div class="text-right">
                                <span class="text-2xl font-black text-lime-400" id="plan_price_${p.id}">${currSymbol} ${parseFloat(p.monthly_price).toFixed(2)}</span>
                                <span class="text-[10px] text-slate-500 font-bold block">/ mes por sucursal</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs font-semibold">
                            <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                                <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Límite Atletas</span>
                                <span class="font-black text-slate-200 text-sm" id="plan_max_users_${p.id}">${maxUsersText}</span>
                            </div>
                            <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                                <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Límite Staff / Entrenadores</span>
                                <span class="font-black text-slate-200 text-sm" id="plan_max_trainers_${p.id}">${maxTrainersText}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800 pt-4 text-xs font-semibold">
                        <span class="text-[10px] text-slate-500 font-mono font-bold" id="plan_curr_code_${p.id}">Moneda: ${escapeHtml(p.currency)}</span>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditPlanModal(${pJsonStr})' class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm flex items-center gap-1.5" title="Editar Plan SaaS">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Editar</span>
                            </button>
                            <button type="button" onclick="openTogglePlanModal(${p.id}, '${p.name.replace(/'/g, "\\'")}', 1)" id="plan_toggle_btn_${p.id}" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Deshabilitar Plan">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;

                const searchRow = document.getElementById('no_plans_search_row');
                if (searchRow) {
                    container.insertBefore(card, searchRow);
                } else {
                    container.prepend(card);
                }

                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-plan');
                updateCounters();
                renderPlanPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al crear el plan.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al crear el plan SaaS.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Plan
    async function submitEditPlan(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-plan-submit-btn');

        setBtnLoading(submitBtn, true, 'Guardando...');

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const p = data.plan;
                const card = document.getElementById(`plan_card_${p.id}`);

                if (card) {
                    card.setAttribute('data-name', (p.name || '').toLowerCase());
                    card.setAttribute('data-desc', (p.description || '').toLowerCase());

                    const currSymbol = (p.currency === 'USD') ? '$' : ((p.currency === 'EUR') ? '€' : p.currency);
                    const nameEl = document.getElementById(`plan_name_${p.id}`);
                    const descEl = document.getElementById(`plan_desc_${p.id}`);
                    const priceEl = document.getElementById(`plan_price_${p.id}`);
                    const usersEl = document.getElementById(`plan_max_users_${p.id}`);
                    const trainersEl = document.getElementById(`plan_max_trainers_${p.id}`);
                    const currCodeEl = document.getElementById(`plan_curr_code_${p.id}`);

                    if (nameEl) nameEl.textContent = p.name;
                    if (descEl) descEl.textContent = p.description || 'Sin descripción detallada.';
                    if (priceEl) priceEl.textContent = `${currSymbol} ${parseFloat(p.monthly_price).toFixed(2)}`;
                    if (usersEl) usersEl.textContent = p.max_users ? `${parseInt(p.max_users).toLocaleString()} máx.` : 'Ilimitados ✨';
                    if (trainersEl) trainersEl.textContent = p.max_trainers ? `${parseInt(p.max_trainers).toLocaleString()} máx.` : 'Ilimitados ✨';
                    if (currCodeEl) currCodeEl.textContent = `Moneda: ${p.currency}`;
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-edit-plan');
                updateCounters();
                renderPlanPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar el plan.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar los datos del plan.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status
    async function submitTogglePlan(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('toggle-plan-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const pId = data.plan_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const card = document.getElementById(`plan_card_${pId}`);

                if (card) {
                    card.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        card.classList.remove('opacity-60', 'bg-slate-950/60', 'border-slate-850');
                    } else {
                        card.classList.add('opacity-60', 'bg-slate-950/60', 'border-slate-850');
                    }

                    const badge = document.getElementById(`plan_status_badge_${pId}`);
                    if (badge) {
                        badge.className = `px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 ${newActiveStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'}`;
                        badge.textContent = newActiveStatus ? 'Activo' : 'Inactivo';
                    }

                    const toggleBtn = document.getElementById(`plan_toggle_btn_${pId}`);
                    const nameText = document.getElementById(`plan_name_${pId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openTogglePlanModal(pId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Deshabilitar Plan' : 'Reactivar Plan';
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-toggle-plan');
                updateCounters();
                renderPlanPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar el estado del plan.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado del plan.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Update Summary Counters
    function updateCounters() {
        const cards = document.querySelectorAll('[data-plan-card]');
        let countActive = 0;
        let countInactive = 0;

        cards.forEach(c => {
            const isActive = c.getAttribute('data-active') === '1';
            if (isActive) {
                countActive++;
            } else {
                countInactive++;
            }
        });

        const statTotal = document.getElementById('stat_total_plans');
        const statActive = document.getElementById('stat_active_plans');
        const statInactive = document.getElementById('stat_inactive_plans');

        if (statTotal) statTotal.textContent = cards.length;
        if (statActive) statActive.textContent = countActive;
        if (statInactive) statInactive.textContent = countInactive;

        const cAll = document.getElementById('count-plan-all');
        const cAct = document.getElementById('count-plan-active');
        const cInact = document.getElementById('count-plan-inactive');

        if (cAll) cAll.textContent = cards.length;
        if (cAct) cAct.textContent = countActive;
        if (cInact) cInact.textContent = countInactive;
    }

    // Plan Filtering & Pagination System (6 cards per page)
    let currentPlanPage = 1;
    let currentPlanStatusFilter = 'all';
    const planItemsPerPage = 6;

    function setPlanStatusFilter(status) {
        currentPlanStatusFilter = status;

        const tabs = document.querySelectorAll('.plan-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "plan-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
        });

        const activeTab = document.getElementById('plan-status-filter-' + status);
        if (activeTab) {
            activeTab.className = "plan-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all";
        }

        currentPlanPage = 1;
        renderPlanPage();
    }

    function onPlanFilterChange() {
        currentPlanPage = 1;
        renderPlanPage();
    }

    function renderPlanPage() {
        const searchVal = (document.getElementById('search-plan-input')?.value || '').toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('[data-plan-card]'));

        const filtered = cards.filter(c => {
            const name = c.getAttribute('data-name') || '';
            const desc = c.getAttribute('data-desc') || '';
            const isActive = c.getAttribute('data-active') || '1';

            const matchesStatus = (currentPlanStatusFilter === 'all') || (isActive === currentPlanStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || desc.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / planItemsPerPage) || 1;

        if (currentPlanPage > totalPages) currentPlanPage = totalPages;
        if (currentPlanPage < 1) currentPlanPage = 1;

        const startIndex = (currentPlanPage - 1) * planItemsPerPage;
        const endIndex = startIndex + planItemsPerPage;

        cards.forEach(c => c.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(c => c.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_plans_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && cards.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('plan_pagination_info');
        const pageSpan = document.getElementById('plan_page_number_display');
        const prevBtn = document.getElementById('plan_prev_page_btn');
        const nextBtn = document.getElementById('plan_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay planes SaaS para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} planes`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentPlanPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentPlanPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPlanPage >= totalPages);
    }

    function changePlanPage(delta) {
        currentPlanPage += delta;
        renderPlanPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif

        updateCounters();
        renderPlanPage();
    });
</script>
@endsection
