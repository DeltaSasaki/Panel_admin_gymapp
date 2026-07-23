@extends('layouts.admin')

@section('title', 'Gestión de Sucursales')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="building-2" class="w-8 h-8 text-lime-400"></i>
                Gestión de Sucursales (Gimnasios)
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Supervisa sucursales, activa o suspende servicios SaaS y gestiona planes de acceso a la plataforma.</p>
        </div>
        <button type="button" onclick="openCreateGymModal()" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
            Nueva Sucursal
        </button>
    </div>

    <!-- Metrics Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Sucursales Registradas</span>
                <h3 class="text-2xl font-black text-slate-100"><span id="stat_total_gyms">{{ $gyms->count() }}</span> <span class="text-xs font-normal text-slate-400">sedes</span></h3>
            </div>
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-400">
                <i data-lucide="building" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Servicio Activo</span>
                <h3 class="text-2xl font-black text-emerald-400"><span id="stat_active_gyms">{{ $gyms->where('is_active', 1)->count() }}</span> <span class="text-xs font-normal text-slate-400">activas</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-rose-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Suspendidas / Inactivas</span>
                <h3 class="text-2xl font-black text-rose-400"><span id="stat_suspended_gyms">{{ $gyms->where('is_active', 0)->count() }}</span> <span class="text-xs font-normal text-slate-400">inactivas</span></h3>
            </div>
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Atletas en Plataforma</span>
                <h3 class="text-2xl font-black text-amber-400"><span id="stat_total_members">{{ $gyms->sum('members_count') }}</span> <span class="text-xs font-normal text-slate-400">atletas</span></h3>
            </div>
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Live Search Bar Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
        <div class="flex flex-wrap items-center gap-3">
            <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-300 mr-2 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Filtro por Estado:
            </h3>

            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                <button type="button" onclick="setGymStatusFilter('all')" id="gym-status-filter-all" class="gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                    Todas (<span id="count-gym-all">{{ $gyms->count() }}</span>)
                </button>
                <button type="button" onclick="setGymStatusFilter('1')" id="gym-status-filter-1" class="gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Activas (<span id="count-gym-active">{{ $gyms->where('is_active', 1)->count() }}</span>)
                </button>
                <button type="button" onclick="setGymStatusFilter('0')" id="gym-status-filter-0" class="gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Suspendidas (<span id="count-gym-suspended">{{ $gyms->where('is_active', 0)->count() }}</span>)
                </button>
            </div>
        </div>

        <!-- Live Search Bar -->
        <div class="relative w-full xl:w-72">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-gym-input" oninput="onGymFilterChange()" placeholder="Buscar por nombre, slug o correo..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
        </div>
    </div>

    <!-- Gyms Table Card -->
    <div class="bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-extrabold tracking-wider">
                        <th class="py-3 px-4">Sucursal / Gimnasio</th>
                        <th class="py-3 px-4">Plan SaaS & Suscripción</th>
                        <th class="py-3 px-4 text-center">Usuarios</th>
                        <th class="py-3 px-4 text-center">Estado Servicio</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="gyms-table-body" class="divide-y divide-slate-800/40 text-xs font-semibold">
                    @forelse($gyms as $gym)
                        @php
                            $logo = $gym->logo_url ? asset($gym->logo_url) : null;
                            $planName = $gym->plan->name ?? 'Sin Plan Asignado';
                            $subStatus = $gym->subscription_status ?? 'trialing';

                            $statusClass = 'bg-purple-500/10 text-purple-400 border-purple-500/20';
                            $statusText = 'Prueba (Trial)';
                            if ($subStatus === 'active') {
                                $statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                $statusText = 'Activa';
                            } elseif ($subStatus === 'past_due') {
                                $statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                                $statusText = 'Pago Pendiente';
                            } elseif ($subStatus === 'canceled') {
                                $statusClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                                $statusText = 'Cancelada';
                            }
                        @endphp
                        <tr id="gym_row_{{ $gym->id }}"
                            data-gym-row
                            data-name="{{ strtolower($gym->name) }}"
                            data-slug="{{ strtolower($gym->slug ?? '') }}"
                            data-email="{{ strtolower($gym->email ?? '') }}"
                            data-plan="{{ strtolower($planName) }}"
                            data-active="{{ $gym->is_active ? 1 : 0 }}"
                            data-members="{{ $gym->members_count ?? 0 }}"
                            class="hover:bg-slate-850/40 transition-colors {{ $gym->is_active ? '' : 'opacity-60 bg-slate-950/20' }}">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    @if($logo)
                                        <img src="{{ $logo }}" id="gym_logo_img_{{ $gym->id }}" class="w-10 h-10 rounded-xl object-cover border border-slate-700 shrink-0">
                                    @else
                                        <div id="gym_logo_img_{{ $gym->id }}" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                            <i data-lucide="building-2" class="w-5 h-5"></i>
                                        </div>
                                    @endif
                                    <div class="overflow-hidden min-w-0">
                                        <h3 class="font-bold text-slate-100 text-sm truncate" id="gym_name_{{ $gym->id }}">{{ $gym->name }}</h3>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.5 bg-slate-950 text-lime-400 border border-slate-800 text-[9px] font-mono font-bold rounded" id="gym_slug_{{ $gym->id }}">slug: {{ $gym->slug }}</span>
                                            <span class="inline-flex items-center gap-1 border border-slate-800 bg-slate-950 px-1.5 py-0.5 rounded text-[9px]" title="Colores Primario / Secundario">
                                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $gym->primary_color ?? '#000' }}"></span>
                                                <span class="w-2 h-2 rounded-full border border-slate-700" style="background-color: {{ $gym->secondary_color ?? '#FFF' }}"></span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 truncate" id="gym_email_{{ $gym->id }}">{{ $gym->email ?? 'Sin correo' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <span class="inline-block px-2.5 py-0.5 bg-slate-950 text-slate-200 border border-slate-800 text-[10px] font-extrabold rounded-lg" id="gym_plan_{{ $gym->id }}">
                                        Plan: {{ $planName }}
                                    </span>
                                    <div class="block">
                                        <span id="gym_sub_badge_{{ $gym->id }}" class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold rounded-lg" title="Atletas Miembros">
                                        ⚡ <strong id="gym_members_count_{{ $gym->id }}">{{ $gym->members_count ?? 0 }}</strong> Atletas
                                    </span>
                                    <span class="px-2 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-bold rounded-lg" title="Personal de Staff">
                                        🏋️ <strong>{{ $gym->staff_count ?? 0 }}</strong> Staff
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span id="gym_status_badge_{{ $gym->id }}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider {{ $gym->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                    {{ $gym->is_active ? 'Activa' : 'Suspendida' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" onclick='openEditGymModal({{ json_encode($gym) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Sucursal">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Toggle Active Status Button (Inhabilitar / Reactivar) -->
                                    <button type="button" onclick="openToggleGymModal({{ $gym->id }}, '{{ addslashes($gym->name) }}', {{ $gym->is_active ? 1 : 0 }})" 
                                            id="gym_toggle_btn_{{ $gym->id }}"
                                            class="p-2 {{ $gym->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                            title="{{ $gym->is_active ? 'Suspender Sucursal' : 'Reactivar Sucursal' }}">
                                        <i data-lucide="{{ $gym->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no_gyms_empty">
                            <td colspan="5" class="py-16 text-center text-slate-500">
                                <i data-lucide="building-2" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                <p class="font-bold text-slate-400">No hay sucursales de gimnasio registradas</p>
                                <p class="text-xs text-slate-500 mt-1">Crea tu primera sucursal haciendo clic en "Nueva Sucursal".</p>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_gyms_search_row" class="hidden">
                        <td colspan="5" class="py-12 text-center text-slate-500">
                            <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                            <p class="font-bold text-slate-400 text-sm">No se encontraron sucursales que coincidan con la búsqueda.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div id="gym_pagination_container" class="bg-slate-950/60 border border-slate-850 rounded-2xl p-4 mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="gym_pagination_info">Mostrando sucursales...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="gym_prev_page_btn" onclick="changeGymPage(-1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="gym_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="gym_next_page_btn" onclick="changeGymPage(1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ================= MODAL: NUEVA SUCURSAL ================= -->
<div id="modal-create-gym" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i> Registrar Nueva Sucursal
            </h3>
            <button type="button" onclick="toggleModal('modal-create-gym')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-gym-form" action="{{ route('superadmin.gyms.store') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateGym(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_gym_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Sucursal *</label>
                    <input type="text" name="name" id="create_gym_name" required placeholder="Ej: Gym Central Lima" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_gym_slug" class="block text-slate-400 uppercase tracking-wider mb-1.5">Identificador (Slug URL)</label>
                    <input type="text" name="slug" id="create_gym_slug" placeholder="Ej: gym-central-lima" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_current_plan_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Plan de Suscripción SaaS</label>
                    <select name="current_plan_id" id="create_current_plan_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">-- Sin Plan Inicial --</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} (${{ number_format($plan->monthly_price, 2) }}/mes)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="create_subscription_status" class="block text-slate-400 uppercase tracking-wider mb-1.5">Estado de Suscripción</label>
                    <select name="subscription_status" id="create_subscription_status" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="trialing" selected>Prueba Gratuita (Trialing)</option>
                        <option value="active">Activa (Al Día)</option>
                        <option value="past_due">Pago Pendiente (Past Due)</option>
                        <option value="canceled">Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_gym_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto</label>
                    <input type="email" name="email" id="create_gym_email" placeholder="contacto@gymcentral.com" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_gym_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" id="create_gym_phone" placeholder="+51 987654321" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="create_gym_address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física</label>
                <input type="text" name="address" id="create_gym_address" placeholder="Av. Principal 123, Miraflores, Lima" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>

            <!-- Branding Colors (Primario y Secundario) -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_primary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Primario (Branding)</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="primary_color" id="create_primary_color" value="#000000" class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-850 cursor-pointer">
                        <span class="text-xs text-slate-400 font-mono font-bold">Hex Primario</span>
                    </div>
                </div>
                <div>
                    <label for="create_secondary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Secundario (Branding)</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="secondary_color" id="create_secondary_color" value="#FFFFFF" class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-850 cursor-pointer">
                        <span class="text-xs text-slate-400 font-mono font-bold">Hex Secundario</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_timezone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Zona Horaria</label>
                    <input type="text" name="timezone" id="create_timezone" value="America/Lima" placeholder="America/Lima" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_gym_logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Logo de la Sucursal</label>
                    <input type="file" name="logo" id="create_gym_logo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-1.5 text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 cursor-pointer">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-gym')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-gym-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Sucursal</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR SUCURSAL ================= -->
<div id="modal-edit-gym" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Sucursal
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-gym')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-gym-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditGym(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_gym_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Sucursal *</label>
                    <input type="text" name="name" id="edit_gym_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_gym_slug" class="block text-slate-400 uppercase tracking-wider mb-1.5">Identificador (Slug URL)</label>
                    <input type="text" name="slug" id="edit_gym_slug" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_current_plan_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Plan de Suscripción SaaS</label>
                    <select name="current_plan_id" id="edit_current_plan_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">-- Sin Plan Asignado --</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} (${{ number_format($plan->monthly_price, 2) }}/mes)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_subscription_status" class="block text-slate-400 uppercase tracking-wider mb-1.5">Estado de Suscripción</label>
                    <select name="subscription_status" id="edit_subscription_status" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="trialing">Prueba Gratuita (Trialing)</option>
                        <option value="active">Activa (Al Día)</option>
                        <option value="past_due">Pago Pendiente (Past Due)</option>
                        <option value="canceled">Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_gym_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto</label>
                    <input type="email" name="email" id="edit_gym_email" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_gym_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" id="edit_gym_phone" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="edit_gym_address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física</label>
                <input type="text" name="address" id="edit_gym_address" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <!-- Branding Colors (Primario y Secundario) -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_primary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Primario (Branding)</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="primary_color" id="edit_primary_color" class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-850 cursor-pointer">
                        <span class="text-xs text-slate-400 font-mono font-bold">Hex Primario</span>
                    </div>
                </div>
                <div>
                    <label for="edit_secondary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Secundario (Branding)</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="secondary_color" id="edit_secondary_color" class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-850 cursor-pointer">
                        <span class="text-xs text-slate-400 font-mono font-bold">Hex Secundario</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_timezone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Zona Horaria</label>
                    <input type="text" name="timezone" id="edit_timezone" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_gym_logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nuevo Logo (Opcional)</label>
                    <input type="file" name="logo" id="edit_gym_logo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-1.5 text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 cursor-pointer">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-gym')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-gym-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO / SUSPENDER ================= -->
<div id="modal-toggle-gym" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-gym-status-title">Cambiar Estado de Sucursal</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación de servicio
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-toggle-gym')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-gym-status-desc">
            ¿Estás seguro de que deseas modificar el estado de servicio de esta sucursal?
        </p>

        <form id="toggle-gym-form" action="" method="POST" onsubmit="submitToggleGym(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            <button type="button" onclick="toggleModal('modal-toggle-gym')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="toggle-gym-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-gym-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Floating Toast Notifications System
    function showToast(message, type = 'success') {
        let container = document.getElementById('gym-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'gym-toast-container';
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

    function openCreateGymModal() {
        document.getElementById('create-gym-form').reset();
        toggleModal('modal-create-gym');
    }

    function openEditGymModal(gym) {
        document.getElementById('edit-gym-form').action = `/superadmin/gyms/${gym.id}`;
        document.getElementById('edit_gym_name').value = gym.name || '';
        document.getElementById('edit_gym_slug').value = gym.slug || '';
        document.getElementById('edit_current_plan_id').value = gym.current_plan_id || '';
        document.getElementById('edit_subscription_status').value = gym.subscription_status || 'trialing';
        document.getElementById('edit_gym_email').value = gym.email || '';
        document.getElementById('edit_gym_phone').value = gym.phone || '';
        document.getElementById('edit_gym_address').value = gym.address || '';
        document.getElementById('edit_primary_color').value = gym.primary_color || '#000000';
        document.getElementById('edit_secondary_color').value = gym.secondary_color || '#FFFFFF';
        document.getElementById('edit_timezone').value = gym.timezone || 'America/Lima';

        toggleModal('modal-edit-gym');
    }

    function openToggleGymModal(id, gymName, isActive) {
        document.getElementById('toggle-gym-form').action = `/superadmin/gyms/${id}/toggle`;
        const titleEl = document.getElementById('modal-gym-status-title');
        const descEl = document.getElementById('modal-gym-status-desc');
        const btnTextEl = document.getElementById('modal-gym-status-btn-text');
        const submitBtn = document.getElementById('toggle-gym-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Suspender / Inhabilitar Sucursal';
            descEl.innerHTML = `¿Estás seguro de que deseas suspender el servicio para la sucursal <strong class="text-slate-100">${escapeHtml(gymName)}</strong>? El acceso a la plataforma será bloqueado.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Sucursal';
            descEl.innerHTML = `¿Deseas reactivar el servicio para la sucursal <strong class="text-slate-100">${escapeHtml(gymName)}</strong> para restaurar el acceso total?`;
            btnTextEl.textContent = 'Sí, Reactivar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-toggle-gym');
    }

    // AJAX Submission: Create Gym
    async function submitCreateGym(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-gym-submit-btn');

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
                const g = data.gym;
                const tbody = document.getElementById('gyms-table-body');
                const emptyMsg = document.getElementById('no_gyms_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const gJsonStr = JSON.stringify(g).replace(/'/g, "&#39;");
                const logoUrl = g.logo_url ? `/${g.logo_url}` : null;
                const planName = g.plan ? g.plan.name : 'Sin Plan Asignado';

                let statusClass = 'bg-purple-500/10 text-purple-400 border-purple-500/20';
                let statusText = 'Prueba (Trial)';
                if (g.subscription_status === 'active') {
                    statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                    statusText = 'Activa';
                } else if (g.subscription_status === 'past_due') {
                    statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                    statusText = 'Pago Pendiente';
                } else if (g.subscription_status === 'canceled') {
                    statusClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                    statusText = 'Cancelada';
                }

                const tr = document.createElement('tr');
                tr.id = `gym_row_${g.id}`;
                tr.setAttribute('data-gym-row', '');
                tr.setAttribute('data-name', (g.name || '').toLowerCase());
                tr.setAttribute('data-slug', (g.slug || '').toLowerCase());
                tr.setAttribute('data-email', (g.email || '').toLowerCase());
                tr.setAttribute('data-plan', planName.toLowerCase());
                tr.setAttribute('data-active', '1');
                tr.setAttribute('data-members', '0');
                tr.className = 'hover:bg-slate-850/40 transition-colors';

                tr.innerHTML = `
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-3">
                            ${logoUrl ? `<img src="${logoUrl}" id="gym_logo_img_${g.id}" class="w-10 h-10 rounded-xl object-cover border border-slate-700 shrink-0">` : `<div id="gym_logo_img_${g.id}" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0"><i data-lucide="building-2" class="w-5 h-5"></i></div>`}
                            <div class="overflow-hidden min-w-0">
                                <h3 class="font-bold text-slate-100 text-sm truncate" id="gym_name_${g.id}">${escapeHtml(g.name)}</h3>
                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                    <span class="px-1.5 py-0.5 bg-slate-950 text-lime-400 border border-slate-800 text-[9px] font-mono font-bold rounded" id="gym_slug_${g.id}">slug: ${escapeHtml(g.slug)}</span>
                                    <span class="inline-flex items-center gap-1 border border-slate-800 bg-slate-950 px-1.5 py-0.5 rounded text-[9px]">
                                        <span class="w-2 h-2 rounded-full" style="background-color: ${g.primary_color || '#000'}"></span>
                                        <span class="w-2 h-2 rounded-full border border-slate-700" style="background-color: ${g.secondary_color || '#FFF'}"></span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 truncate" id="gym_email_${g.id}">${escapeHtml(g.email || 'Sin correo')}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4">
                        <div class="space-y-1">
                            <span class="inline-block px-2.5 py-0.5 bg-slate-950 text-slate-200 border border-slate-800 text-[10px] font-extrabold rounded-lg" id="gym_plan_${g.id}">
                                Plan: ${escapeHtml(planName)}
                            </span>
                            <div class="block">
                                <span id="gym_sub_badge_${g.id}" class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider ${statusClass}">
                                    ${statusText}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold rounded-lg">
                                ⚡ <strong id="gym_members_count_${g.id}">0</strong> Atletas
                            </span>
                            <span class="px-2 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-bold rounded-lg">
                                🏋️ <strong>0</strong> Staff
                            </span>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <span id="gym_status_badge_${g.id}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                            Activa
                        </span>
                    </td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" onclick='openEditGymModal(${gJsonStr})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Sucursal">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openToggleGymModal(${g.id}, '${g.name.replace(/'/g, "\\'")}', 1)" id="gym_toggle_btn_${g.id}" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Suspender Sucursal">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;

                const searchRow = document.getElementById('no_gyms_search_row');
                if (searchRow) {
                    tbody.insertBefore(tr, searchRow);
                } else {
                    tbody.appendChild(tr);
                }

                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-gym');
                updateCounters();
                renderGymPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al crear la sucursal.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al crear la sucursal.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Gym
    async function submitEditGym(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-gym-submit-btn');

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
                const g = data.gym;
                const row = document.getElementById(`gym_row_${g.id}`);
                const planName = g.plan ? g.plan.name : 'Sin Plan Asignado';

                if (row) {
                    row.setAttribute('data-name', (g.name || '').toLowerCase());
                    row.setAttribute('data-slug', (g.slug || '').toLowerCase());
                    row.setAttribute('data-email', (g.email || '').toLowerCase());
                    row.setAttribute('data-plan', planName.toLowerCase());

                    const nameEl = document.getElementById(`gym_name_${g.id}`);
                    const slugEl = document.getElementById(`gym_slug_${g.id}`);
                    const emailEl = document.getElementById(`gym_email_${g.id}`);
                    const planEl = document.getElementById(`gym_plan_${g.id}`);
                    const subBadge = document.getElementById(`gym_sub_badge_${g.id}`);
                    const logoImg = document.getElementById(`gym_logo_img_${g.id}`);

                    if (nameEl) nameEl.textContent = g.name;
                    if (slugEl) slugEl.textContent = `slug: ${g.slug}`;
                    if (emailEl) emailEl.textContent = g.email || 'Sin correo';
                    if (planEl) planEl.textContent = `Plan: ${planName}`;

                    if (subBadge) {
                        let statusClass = 'bg-purple-500/10 text-purple-400 border-purple-500/20';
                        let statusText = 'Prueba (Trial)';
                        if (g.subscription_status === 'active') {
                            statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                            statusText = 'Activa';
                        } else if (g.subscription_status === 'past_due') {
                            statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                            statusText = 'Pago Pendiente';
                        } else if (g.subscription_status === 'canceled') {
                            statusClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                            statusText = 'Cancelada';
                        }
                        subBadge.className = `px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider ${statusClass}`;
                        subBadge.textContent = statusText;
                    }

                    if (logoImg && g.logo_url && logoImg.tagName === 'IMG') {
                        logoImg.src = `/${g.logo_url}`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-edit-gym');
                updateCounters();
                renderGymPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar la sucursal.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar los datos de la sucursal.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status
    async function submitToggleGym(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('toggle-gym-submit-btn');

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
                const gId = data.gym_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const row = document.getElementById(`gym_row_${gId}`);

                if (row) {
                    row.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        row.classList.remove('opacity-60', 'bg-slate-950/20');
                    } else {
                        row.classList.add('opacity-60', 'bg-slate-950/20');
                    }

                    const badge = document.getElementById(`gym_status_badge_${gId}`);
                    if (badge) {
                        badge.className = `px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider ${newActiveStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'}`;
                        badge.textContent = newActiveStatus ? 'Activa' : 'Suspendida';
                    }

                    const toggleBtn = document.getElementById(`gym_toggle_btn_${gId}`);
                    const nameText = document.getElementById(`gym_name_${gId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openToggleGymModal(gId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Suspender Sucursal' : 'Reactivar Sucursal';
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-toggle-gym');
                updateCounters();
                renderGymPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar el estado de la sucursal.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado de la sucursal.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Update Summary Counters
    function updateCounters() {
        const rows = document.querySelectorAll('[data-gym-row]');
        let countActive = 0;
        let countSuspended = 0;
        let totalMembers = 0;

        rows.forEach(r => {
            const isActive = r.getAttribute('data-active') === '1';
            const members = parseInt(r.getAttribute('data-members') || '0', 10);
            totalMembers += members;

            if (isActive) {
                countActive++;
            } else {
                countSuspended++;
            }
        });

        const statTotal = document.getElementById('stat_total_gyms');
        const statActive = document.getElementById('stat_active_gyms');
        const statSuspended = document.getElementById('stat_suspended_gyms');
        const statMembers = document.getElementById('stat_total_members');

        if (statTotal) statTotal.textContent = rows.length;
        if (statActive) statActive.textContent = countActive;
        if (statSuspended) statSuspended.textContent = countSuspended;
        if (statMembers) statMembers.textContent = totalMembers;

        const cAll = document.getElementById('count-gym-all');
        const cAct = document.getElementById('count-gym-active');
        const cSus = document.getElementById('count-gym-suspended');

        if (cAll) cAll.textContent = rows.length;
        if (cAct) cAct.textContent = countActive;
        if (cSus) cSus.textContent = countSuspended;
    }

    // Gym Filtering & Pagination System (8 rows per page)
    let currentGymPage = 1;
    let currentGymStatusFilter = 'all';
    const gymItemsPerPage = 8;

    function setGymStatusFilter(status) {
        currentGymStatusFilter = status;

        const tabs = document.querySelectorAll('.gym-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
        });

        const activeTab = document.getElementById('gym-status-filter-' + status);
        if (activeTab) {
            activeTab.className = "gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all";
        }

        currentGymPage = 1;
        renderGymPage();
    }

    function onGymFilterChange() {
        currentGymPage = 1;
        renderGymPage();
    }

    function renderGymPage() {
        const searchVal = (document.getElementById('search-gym-input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-gym-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            const slug = r.getAttribute('data-slug') || '';
            const email = r.getAttribute('data-email') || '';
            const plan = r.getAttribute('data-plan') || '';
            const isActive = r.getAttribute('data-active') || '1';

            const matchesStatus = (currentGymStatusFilter === 'all') || (isActive === currentGymStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || slug.includes(searchVal) || email.includes(searchVal) || plan.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / gymItemsPerPage) || 1;

        if (currentGymPage > totalPages) currentGymPage = totalPages;
        if (currentGymPage < 1) currentGymPage = 1;

        const startIndex = (currentGymPage - 1) * gymItemsPerPage;
        const endIndex = startIndex + gymItemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_gyms_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('gym_pagination_info');
        const pageSpan = document.getElementById('gym_page_number_display');
        const prevBtn = document.getElementById('gym_prev_page_btn');
        const nextBtn = document.getElementById('gym_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay sucursales para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} sucursales`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentGymPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentGymPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentGymPage >= totalPages);
    }

    function changeGymPage(delta) {
        currentGymPage += delta;
        renderGymPage();
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
        renderGymPage();
    });
</script>
@endsection
