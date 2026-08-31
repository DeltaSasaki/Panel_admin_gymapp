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
            <p class="text-xs text-slate-400 mt-1 font-medium">Supervisa sucursales, vincula cuentas de administradores/dueños, activa o suspende servicios y gestiona planes SaaS.</p>
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
            <input type="text" id="search-gym-input" oninput="onGymFilterChange()" placeholder="Buscar por nombre, slug, dueño o correo..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
        </div>
    </div>

    <!-- Gyms Table Card -->
    <div class="bg-slate-900/40 border border-slate-800/80 rounded-3xl p-6 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-extrabold tracking-wider">
                        <th class="py-3 px-4">Sucursal / Gimnasio</th>
                        <th class="py-3 px-4">Dueño / Administrador</th>
                        <th class="py-3 px-4">Plan SaaS</th>
                        <th class="py-3 px-4 text-center">Usuarios</th>
                        <th class="py-3 px-4 text-center">Estado</th>
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

                            $owner = $gym->admin;
                            $ownerProfile = $owner ? $owner->profile : null;
                            $ownerName = $ownerProfile ? trim($ownerProfile->first_name . ' ' . $ownerProfile->last_name) : null;
                            $ownerEmail = $owner ? $owner->email : null;
                            $ownerPhone = $ownerProfile ? $ownerProfile->phone : null;
                            $ownerDni = $ownerProfile ? $ownerProfile->dni : null;
                        @endphp
                        <tr id="gym_row_{{ $gym->id }}"
                            data-gym-row
                            data-name="{{ strtolower($gym->name) }}"
                            data-slug="{{ strtolower($gym->slug ?? '') }}"
                            data-email="{{ strtolower($gym->email ?? '') }}"
                            data-owner="{{ strtolower(($ownerName ?? '') . ' ' . ($ownerEmail ?? '')) }}"
                            data-plan="{{ strtolower($planName) }}"
                            data-active="{{ $gym->is_active ? 1 : 0 }}"
                            data-members="{{ $gym->members_count ?? 0 }}"
                            class="hover:bg-slate-850/40 transition-colors {{ $gym->is_active ? '' : 'opacity-60 bg-slate-950/20' }}">
                            
                            <!-- Gym Info -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    @if($logo)
                                        <img src="{{ $logo }}" id="gym_logo_img_{{ $gym->id }}" class="w-11 h-11 rounded-xl object-cover border border-slate-700 shrink-0">
                                    @else
                                        <div id="gym_logo_img_{{ $gym->id }}" class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-lime-400 shrink-0">
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
                                            <span class="px-1.5 py-0.5 bg-slate-950 text-slate-400 border border-slate-800 text-[9px] rounded flex items-center gap-1" title="Zona Horaria">
                                                <i data-lucide="globe" class="w-2.5 h-2.5 text-lime-400"></i>
                                                <span id="gym_tz_{{ $gym->id }}">{{ $gym->timezone ?? 'America/Caracas' }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Owner / Admin Account -->
                            <td class="py-4 px-4">
                                <div id="gym_owner_container_{{ $gym->id }}">
                                    @if($owner)
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-lime-500/10 border border-lime-500/30 flex items-center justify-center text-lime-400 font-extrabold text-xs shrink-0">
                                                {{ strtoupper(substr($ownerName ?: $owner->email, 0, 2)) }}
                                            </div>
                                            <div class="overflow-hidden min-w-0">
                                                <div class="font-bold text-slate-200 truncate" id="gym_owner_name_{{ $gym->id }}">{{ $ownerName ?: 'Admin Dueño' }}</div>
                                                <div class="text-[10px] text-lime-400 font-mono truncate" id="gym_owner_email_{{ $gym->id }}">{{ $owner->email }}</div>
                                                @if($ownerPhone || $ownerDni)
                                                    <div class="text-[9px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                        @if($ownerDni) <span>DNI: {{ $ownerDni }}</span> @endif
                                                        @if($ownerPhone) <span>📞 {{ $ownerPhone }}</span> @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i> Sin Dueño Asignado
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Plan SaaS -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <span class="inline-block px-2.5 py-0.5 bg-slate-950 text-slate-200 border border-slate-800 text-[10px] font-extrabold rounded-lg" id="gym_plan_{{ $gym->id }}">
                                        {{ $planName }}
                                    </span>
                                    <div class="block">
                                        <span id="gym_sub_badge_{{ $gym->id }}" class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Members & Staff Count -->
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold rounded-lg" title="Atletas">
                                        ⚡ <strong id="gym_members_count_{{ $gym->id }}">{{ $gym->members_count ?? 0 }}</strong>
                                    </span>
                                    <span class="px-2 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-bold rounded-lg" title="Staff / Cajeros">
                                        🏋️ <strong>{{ $gym->staff_count ?? 0 }}</strong>
                                    </span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-4 text-center">
                                <span id="gym_status_badge_{{ $gym->id }}" class="px-2.5 py-1 text-[9px] font-black uppercase rounded-lg border tracking-wider {{ $gym->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                    {{ $gym->is_active ? 'Activa' : 'Suspendida' }}
                                </span>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" onclick='openEditGymModal({{ json_encode($gym) }})' id="gym_edit_btn_{{ $gym->id }}" class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm cursor-pointer" title="Editar Sucursal y Dueño">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Toggle Active Status Button -->
                                    <button type="button" onclick="openToggleGymModal({{ $gym->id }}, '{{ addslashes($gym->name) }}', {{ $gym->is_active ? 1 : 0 }})" 
                                            id="gym_toggle_btn_{{ $gym->id }}"
                                            class="p-2 {{ $gym->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm cursor-pointer" 
                                            title="{{ $gym->is_active ? 'Suspender Sucursal' : 'Reactivar Sucursal' }}">
                                        <i data-lucide="{{ $gym->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no_gyms_empty">
                            <td colspan="6" class="py-16 text-center text-slate-500">
                                <i data-lucide="building-2" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                <p class="font-bold text-slate-400">No hay sucursales registradas</p>
                                <p class="text-xs text-slate-500 mt-1">Crea tu primera sucursal haciendo clic en "Nueva Sucursal".</p>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_gyms_search_row" class="hidden">
                        <td colspan="6" class="py-12 text-center text-slate-500">
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

<!-- ================= MODAL: NUEVA SUCURSAL + CUENTA DUEÑO ================= -->
<div id="modal-create-gym" data-no-backdrop-close="true" data-backdrop="static" class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-2xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[92vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900/95 backdrop-blur-md z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i> Registrar Nueva Sucursal & Dueño
            </h3>
            <button type="button" onclick="toggleModal('modal-create-gym')" class="text-slate-400 hover:text-slate-100 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-gym-form" action="{{ route('superadmin.gyms.store') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateGym(event)" class="p-6 space-y-6 text-xs font-semibold">
            @csrf

            <!-- BLOQUE 1: DATOS DE LA SUCURSAL -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-800 text-slate-300 font-extrabold text-xs uppercase tracking-wider">
                    <i data-lucide="building-2" class="w-4 h-4 text-lime-400"></i>
                    1. Información de la Sucursal (Gimnasio)
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_gym_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Sucursal *</label>
                        <input type="text" name="name" id="create_gym_name" required placeholder="Ej: GymFlow Central Caracas" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="create_gym_slug" class="block text-slate-400 uppercase tracking-wider mb-1.5">Identificador (Slug URL)</label>
                        <input type="text" name="slug" id="create_gym_slug" placeholder="Ej: gymflow-central-caracas" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_current_plan_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Plan SaaS Inicial</label>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_timezone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Zona Horaria *</label>
                        <select name="timezone" id="create_timezone" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-lime-400 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            @foreach($timezones as $group => $tzList)
                                <optgroup label="{{ $group }}" class="bg-slate-900 text-slate-400 font-bold">
                                    @foreach($tzList as $tzVal => $tzLabel)
                                        <option value="{{ $tzVal }}" {{ $tzVal === 'America/Caracas' ? 'selected' : '' }} class="bg-slate-950 text-slate-100">
                                            {{ $tzLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="create_gym_logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Logo de la Sucursal</label>
                        <input type="file" name="logo" id="create_gym_logo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-1.5 text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_gym_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto del Gimnasio</label>
                        <input type="email" name="email" id="create_gym_email" placeholder="contacto@gymflow.com" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="create_gym_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp de la Sede</label>
                        <input type="text" name="phone" id="create_gym_phone" placeholder="+58 412 1234567" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div>
                    <label for="create_gym_address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física de la Sede</label>
                    <input type="text" name="address" id="create_gym_address" placeholder="Av. Principal, Torre Fitness, Piso 2" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <!-- Branding Colors -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create_primary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Primario (Marca)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" id="create_primary_color" value="#000000" class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-850 cursor-pointer">
                            <span class="text-xs text-slate-400 font-mono font-bold">Hex Primario</span>
                        </div>
                    </div>
                    <div>
                        <label for="create_secondary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Secundario (Marca)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary_color" id="create_secondary_color" value="#FFFFFF" class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-850 cursor-pointer">
                            <span class="text-xs text-slate-400 font-mono font-bold">Hex Secundario</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 2: CUENTA DEL DUEÑO / ADMIN -->
            <div class="space-y-4 pt-4 border-t border-slate-800 bg-slate-950/40 p-4 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
                    <div class="flex items-center gap-2 text-lime-400 font-extrabold text-xs uppercase tracking-wider">
                        <i data-lucide="crown" class="w-4 h-4"></i>
                        2. Cuenta del Dueño / Administrador de la Sucursal (Obligatorio)
                    </div>
                    <span class="px-2 py-0.5 rounded bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-bold uppercase">Rol Admin</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_owner_first_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombres del Dueño *</label>
                        <input type="text" name="owner_first_name" id="create_owner_first_name" required placeholder="Ej: Carlos Eduardo" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="create_owner_last_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Apellidos del Dueño *</label>
                        <input type="text" name="owner_last_name" id="create_owner_last_name" required placeholder="Ej: Mendoza Pérez" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_owner_dni" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cédula / DNI</label>
                        <input type="text" name="owner_dni" id="create_owner_dni" placeholder="Ej: V-18765432" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="create_owner_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono Personal / WhatsApp</label>
                        <input type="text" name="owner_phone" id="create_owner_phone" placeholder="Ej: +58 424 9876543" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_owner_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Acceso (Usuario de Login) *</label>
                        <input type="email" name="owner_email" id="create_owner_email" required placeholder="dueno@gymflow.com" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="create_owner_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Contraseña de Acceso * (Mín. 6 car.)</label>
                        <div class="relative">
                            <input type="password" name="owner_password" id="create_owner_password" required minlength="6" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                            <button type="button" onclick="togglePasswordVisibility('create_owner_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-gym')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all cursor-pointer">Cancelar</button>
                <button type="submit" id="create-gym-submit-btn" class="px-6 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                    Crear Sucursal & Cuenta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR SUCURSAL + DUEÑO ================= -->
<div id="modal-edit-gym" data-no-backdrop-close="true" data-backdrop="static" class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-2xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[92vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900/95 backdrop-blur-md z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Sucursal & Administrador
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-gym')" class="text-slate-400 hover:text-slate-100 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-gym-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditGym(event)" class="p-6 space-y-6 text-xs font-semibold">
            @csrf
            @method('PUT')

            <!-- BLOQUE 1: DATOS DE LA SUCURSAL -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-800 text-slate-300 font-extrabold text-xs uppercase tracking-wider">
                    <i data-lucide="building-2" class="w-4 h-4 text-amber-400"></i>
                    1. Datos de la Sucursal
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_gym_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Sucursal *</label>
                        <input type="text" name="name" id="edit_gym_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_gym_slug" class="block text-slate-400 uppercase tracking-wider mb-1.5">Identificador (Slug URL)</label>
                        <input type="text" name="slug" id="edit_gym_slug" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_current_plan_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Plan SaaS</label>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_timezone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Zona Horaria *</label>
                        <select name="timezone" id="edit_timezone" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-lime-400 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            @foreach($timezones as $group => $tzList)
                                <optgroup label="{{ $group }}" class="bg-slate-900 text-slate-400 font-bold">
                                    @foreach($tzList as $tzVal => $tzLabel)
                                        <option value="{{ $tzVal }}" class="bg-slate-950 text-slate-100">
                                            {{ $tzLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit_gym_logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nuevo Logo (Opcional)</label>
                        <input type="file" name="logo" id="edit_gym_logo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-1.5 text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                <!-- Branding Colors -->
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
            </div>

            <!-- BLOQUE 2: CUENTA DEL DUEÑO / ADMIN -->
            <div class="space-y-4 pt-4 border-t border-slate-800 bg-slate-950/40 p-4 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
                    <div class="flex items-center gap-2 text-lime-400 font-extrabold text-xs uppercase tracking-wider">
                        <i data-lucide="crown" class="w-4 h-4"></i>
                        2. Cuenta de Dueño / Administrador Asignado
                    </div>
                    <span class="px-2 py-0.5 rounded bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-bold uppercase">Rol Admin</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_owner_first_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombres del Dueño</label>
                        <input type="text" name="owner_first_name" id="edit_owner_first_name" placeholder="Nombres" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_owner_last_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Apellidos del Dueño</label>
                        <input type="text" name="owner_last_name" id="edit_owner_last_name" placeholder="Apellidos" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_owner_dni" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cédula / DNI</label>
                        <input type="text" name="owner_dni" id="edit_owner_dni" placeholder="Ej: V-18765432" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_owner_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono Personal</label>
                        <input type="text" name="owner_phone" id="edit_owner_phone" placeholder="Ej: +58 424 9876543" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_owner_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Acceso (Login)</label>
                        <input type="email" name="owner_email" id="edit_owner_email" placeholder="correo@ejemplo.com" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_owner_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nueva Contraseña (Opcional)</label>
                        <div class="relative">
                            <input type="password" name="owner_password" id="edit_owner_password" minlength="6" placeholder="Dejar en blanco para no cambiar" class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-4 pr-10 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                            <button type="button" onclick="togglePasswordVisibility('edit_owner_password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-gym')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all cursor-pointer">Cancelar</button>
                <button type="submit" id="edit-gym-submit-btn" class="px-6 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4 stroke-[3px]"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO / SUSPENDER ================= -->
<div id="modal-toggle-gym" data-no-backdrop-close="true" data-backdrop="static" class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4 hidden">
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
            <button type="button" onclick="toggleModal('modal-toggle-gym')" class="text-slate-400 hover:text-slate-100 transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed" id="modal-gym-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de esta sucursal?
        </p>

        <form id="toggle-gym-form" action="" method="POST" onsubmit="submitToggleGym(event)" class="flex items-center gap-3 pt-2">
            @csrf
            <button type="button" onclick="toggleModal('modal-toggle-gym')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-850 border border-slate-800 text-slate-300 hover:text-slate-100 font-bold text-xs rounded-xl transition-all cursor-pointer">
                Cancelar
            </button>
            <button type="submit" id="toggle-gym-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span id="modal-gym-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function toggleModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('hidden');
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl border text-xs font-bold transition-all duration-300 transform translate-y-10 opacity-0 ${
            type === 'success' 
                ? 'bg-slate-900 border-lime-500/40 text-lime-400 shadow-lime-500/10' 
                : 'bg-slate-900 border-rose-500/40 text-rose-400 shadow-rose-500/10'
        }`;
        
        const iconName = type === 'success' ? 'check-circle-2' : 'alert-circle';
        toast.innerHTML = `<i data-lucide="${iconName}" class="w-5 h-5 shrink-0"></i><span>${message}</span>`;
        
        document.body.appendChild(toast);
        if (window.lucide) window.lucide.createIcons();

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
        });

        setTimeout(() => {
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function setBtnLoading(btn, isLoading, text = 'Cargando...') {
        if (!btn) return;
        if (isLoading) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-wait');
            btn.innerHTML = `
                <span class="inline-flex items-center gap-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

    function openCreateGymModal() {
        const form = document.getElementById('create-gym-form');
        if (form) form.reset();
        document.getElementById('create_timezone').value = 'America/Caracas';
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
        document.getElementById('edit_timezone').value = gym.timezone || 'America/Caracas';

        // Load owner data if present
        const owner = gym.admin;
        const ownerProfile = owner ? owner.profile : null;
        document.getElementById('edit_owner_first_name').value = ownerProfile ? (ownerProfile.first_name || '') : '';
        document.getElementById('edit_owner_last_name').value = ownerProfile ? (ownerProfile.last_name || '') : '';
        document.getElementById('edit_owner_dni').value = ownerProfile ? (ownerProfile.dni || '') : '';
        document.getElementById('edit_owner_phone').value = ownerProfile ? (ownerProfile.phone || '') : '';
        document.getElementById('edit_owner_email').value = owner ? (owner.email || '') : '';
        document.getElementById('edit_owner_password').value = '';

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
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer";
        } else {
            titleEl.textContent = 'Reactivar Sucursal';
            descEl.innerHTML = `¿Deseas reactivar el servicio para la sucursal <strong class="text-slate-100">${escapeHtml(gymName)}</strong> para restaurar el acceso total?`;
            btnTextEl.textContent = 'Sí, Reactivar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer";
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

            if (response.ok && data.success) {
                showToast(data.message, 'success');
                // Reload to reflect newly created gym across selectors and table
                setTimeout(() => window.location.reload(), 700);
            } else {
                let errText = data.message || 'Error al crear la sucursal.';
                if (data.errors) {
                    errText = Object.values(data.errors).flat().join('<br>');
                }
                showToast(errText, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al registrar la sucursal.', 'error');
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

            if (response.ok && data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                let errText = data.message || 'Error al actualizar la sucursal.';
                if (data.errors) {
                    errText = Object.values(data.errors).flat().join('<br>');
                }
                showToast(errText, 'error');
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
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm cursor-pointer`;
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
            tab.className = "gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer";
        });

        const activeTab = document.getElementById('gym-status-filter-' + status);
        if (activeTab) {
            activeTab.className = "gym-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all cursor-pointer";
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
            const owner = r.getAttribute('data-owner') || '';
            const plan = r.getAttribute('data-plan') || '';
            const isActive = r.getAttribute('data-active') || '1';

            const matchesStatus = (currentGymStatusFilter === 'all') || (isActive === currentGymStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || slug.includes(searchVal) || email.includes(searchVal) || owner.includes(searchVal) || plan.includes(searchVal);

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

    window.openCreateGymModal = openCreateGymModal;
    window.openEditGymModal = openEditGymModal;
    window.openToggleGymModal = openToggleGymModal;
    window.submitCreateGym = submitCreateGym;
    window.submitEditGym = submitEditGym;
    window.submitToggleGym = submitToggleGym;
    window.setGymStatusFilter = setGymStatusFilter;
    window.onGymFilterChange = onGymFilterChange;
    window.changeGymPage = changeGymPage;
    window.toggleModal = toggleModal;
    window.togglePasswordVisibility = togglePasswordVisibility;

    document.addEventListener('DOMContentLoaded', function () {
        updateCounters();
        renderGymPage();

        // Prevent accidental closing on backdrop click for all modals in this screen
        ['modal-create-gym', 'modal-edit-gym', 'modal-toggle-gym'].forEach(mId => {
            const m = document.getElementById(mId);
            if (m) {
                m.addEventListener('click', function(e) {
                    if (e.target === m) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            }
        });
    });
</script>
@endsection
