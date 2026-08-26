@extends('layouts.admin')

@section('title', 'Gestión de Staff y Entrenadores')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="users" class="w-8 h-8 text-lime-400"></i>
                Staff de Entrenadores
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Administra al equipo de entrenadores, asignación de atletas, salarios de nómina, especialidades y expedientes técnicos.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasPermission('staff.manage'))
                <button type="button" onclick="openCreateStaffModal()" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="user-plus" class="w-4 h-4 stroke-[3px]"></i> Reclutar Entrenador
                </button>
            @endif
        </div>
    </div>

    @php
        $totalTrainers = $trainers->count();
        $activeTrainers = $trainers->where('is_active', 1);
        $totalActive = $activeTrainers->count();
        $totalInactive = $trainers->where('is_active', 0)->count();
        $totalPayroll = $activeTrainers->sum('salary');
        $totalAssignedClients = $trainers->sum(function($t) {
            return $t->assignedClients ? $t->assignedClients->count() : 0;
        });
        $totalCapacity = $trainers->sum('max_clients') ?: 1;
        $globalCapacityPct = min(100, (int) round(($totalAssignedClients / max(1, $totalCapacity)) * 100));
    @endphp

    <!-- Quick Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Total Personal Staff</span>
                <h3 class="text-2xl font-black text-slate-100"><span id="stat_total">{{ $totalTrainers }}</span> <span class="text-xs font-normal text-slate-400">entrenadores</span></h3>
            </div>
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-400">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Entrenadores Activos</span>
                <h3 class="text-2xl font-black text-emerald-400"><span id="stat_active">{{ $totalActive }}</span> <span class="text-xs font-normal text-slate-400">en servicio</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-cyan-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Atletas Asignados</span>
                <h3 class="text-2xl font-black text-cyan-400"><span>{{ $totalAssignedClients }}</span> <span class="text-xs font-normal text-slate-400">/ {{ $totalCapacity }} cupos ({{ $globalCapacityPct }}%)</span></h3>
            </div>
            <div class="p-3 bg-cyan-500/10 border border-cyan-500/20 rounded-2xl text-cyan-400">
                <i data-lucide="target" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Nómina Mensual</span>
                <h3 class="text-2xl font-black text-amber-400">$ <span id="stat_payroll">{{ number_format($totalPayroll, 2) }}</span></h3>
            </div>
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
        <div class="flex flex-wrap items-center gap-3">
            <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-300 mr-2 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Filtro por Estado:
            </h3>

            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                <button type="button" onclick="setStaffStatusFilter('all')" id="staff-status-filter-all" class="staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                    Todos (<span id="count-status-all">{{ $totalTrainers }}</span>)
                </button>
                <button type="button" onclick="setStaffStatusFilter('1')" id="staff-status-filter-1" class="staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Activos (<span id="count-status-active">{{ $totalActive }}</span>)
                </button>
                <button type="button" onclick="setStaffStatusFilter('0')" id="staff-status-filter-0" class="staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Inactivos (<span id="count-status-inactive">{{ $totalInactive }}</span>)
                </button>
            </div>
        </div>

        <!-- Live Search Bar -->
        <div class="relative w-full xl:w-80">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-staff-input" oninput="onStaffFilterChange()" placeholder="Buscar por nombre, DNI, especialidad..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 font-medium">
        </div>
    </div>

    <!-- Trainers Grid Container -->
    <div id="trainers-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($trainers as $trainer)
            @php
                $fullName = trim(($trainer->first_name ?? '') . ' ' . ($trainer->last_name ?? ''));
                $dni = $trainer->user && $trainer->user->profile ? ($trainer->user->profile->dni ?? 'Sin DNI') : 'Sin DNI';
                $photoUrl = $trainer->photo_url 
                    ? asset($trainer->photo_url) 
                    : ($trainer->user && $trainer->user->profile && $trainer->user->profile->profile_photo ? asset($trainer->user->profile->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($fullName ?: 'Coach') . '&background=0f172a&color=a3e635&size=150');
                $assignedCount = $trainer->assignedClients ? $trainer->assignedClients->count() : 0;
                $maxClients = max(1, (int) ($trainer->max_clients ?? 20));
                $capacityPct = min(100, (int) round(($assignedCount / $maxClients) * 100));
                $routinesCount = $trainer->routines ? $trainer->routines->count() : 0;
                $gymName = $trainer->gym ? $trainer->gym->name : 'Sede Principal';
                $hireDateFormatted = $trainer->hire_date ? \Carbon\Carbon::parse($trainer->hire_date)->translatedFormat('M Y') : null;
            @endphp
            <div id="trainer_card_{{ $trainer->id }}"
                 data-trainer-card
                 data-name="{{ strtolower($fullName) }}"
                 data-dni="{{ strtolower($dni) }}"
                 data-email="{{ strtolower($trainer->email) }}"
                 data-specialty="{{ strtolower($trainer->specialty ?? '') }}"
                 data-active="{{ $trainer->is_active ? 1 : 0 }}"
                 class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm {{ $trainer->is_active ? '' : 'opacity-60 bg-slate-950/40 border-slate-850' }}">
                
                <div class="space-y-4">
                    
                    <!-- Card Top: Sucursal Badge (Superadmin only) & Status -->
                    <div class="flex items-center justify-between gap-2 border-b border-slate-800/60 pb-3">
                        @if(auth()->user()->role === 'superadmin')
                            <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-[10px] font-extrabold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="building" class="w-3 h-3 text-lime-400"></i>
                                <span id="trainer_gym_{{ $trainer->id }}">{{ $gymName }}</span>
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-lime-400"></i> Entrenador Staff
                            </span>
                        @endif

                        <span id="trainer_status_badge_{{ $trainer->id }}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 {{ $trainer->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                            {{ $trainer->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- Profile Avatar & Identity -->
                    <div class="flex items-start gap-3.5">
                        <div class="relative shrink-0">
                            <img src="{{ $photoUrl }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=Coach&background=0f172a&color=a3e635&size=150'" id="trainer_photo_img_{{ $trainer->id }}" class="w-14 h-14 rounded-2xl object-cover border border-slate-700 shadow-md group-hover:scale-105 transition-transform">
                            <span id="trainer_dot_{{ $trainer->id }}" class="w-3.5 h-3.5 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 {{ $trainer->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-base text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="trainer_name_{{ $trainer->id }}">{{ $fullName }}</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-mono font-bold rounded" id="trainer_dni_{{ $trainer->id }}">DNI: {{ $dni }}</span>
                                <span class="text-[10px] text-slate-400 truncate" id="trainer_email_{{ $trainer->id }}">{{ $trainer->email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Specialty & Experience Info Pill -->
                    <div class="p-3.5 bg-slate-950/70 border border-slate-850 rounded-2xl space-y-2 text-xs font-semibold">
                        <div class="flex items-center justify-between text-slate-300">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Especialidad:</span>
                            <span class="font-black text-lime-400" id="trainer_specialty_{{ $trainer->id }}">{{ $trainer->specialty ?? 'Entrenador General' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Certificación:</span>
                            <span class="font-bold text-slate-300 truncate max-w-[170px]" id="trainer_cert_{{ $trainer->id }}" title="{{ $trainer->certification ?? 'Sin datos' }}">{{ $trainer->certification ?? 'Sin datos' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300 border-t border-slate-850/80 pt-2">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Experiencia / Ingreso:</span>
                            <span class="font-extrabold text-amber-400" id="trainer_exp_{{ $trainer->id }}">
                                {{ $trainer->total_experience_years }} años exp. @if($trainer->hire_date) • {{ $trainer->tenure }} en el staff @endif
                            </span>
                        </div>
                    </div>

                    <!-- Client Capacity Progress Bar -->
                    <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400 text-[10px] font-extrabold uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-cyan-400"></i> Cupo de Atletas:
                            </span>
                            <span class="font-black text-slate-200 text-xs">
                                <strong class="text-cyan-400" id="trainer_clients_assigned_{{ $trainer->id }}">{{ $assignedCount }}</strong> / <span id="trainer_clients_max_{{ $trainer->id }}">{{ $maxClients }}</span>
                                <span class="text-slate-400 font-medium">({{ $capacityPct }}%)</span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-900 rounded-full h-2 border border-slate-800 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $capacityPct >= 100 ? 'bg-rose-500' : ($capacityPct >= 80 ? 'bg-amber-500' : 'bg-gradient-to-r from-lime-500 to-emerald-500') }}" 
                                 id="trainer_capacity_bar_{{ $trainer->id }}"
                                 style="width: {{ $capacityPct }}%;"></div>
                        </div>
                    </div>

                    <!-- Salary & Routines Summary Pills -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-center">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Salario Nómina</span>
                            <span class="font-black text-emerald-400 text-sm" id="trainer_salary_{{ $trainer->id }}">$ {{ number_format($trainer->salary, 2) }}</span>
                        </div>
                        <div class="p-2.5 bg-purple-500/10 border border-purple-500/20 rounded-xl text-center">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Rutinas Creadas</span>
                            <span class="font-black text-purple-400 text-sm" id="trainer_routines_count_{{ $trainer->id }}">{{ $routinesCount }} planes</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold">
                    <button type="button" 
                            onclick="openTrainerDetailsModal({{ $trainer->id }})" 
                            class="px-3 py-1.5 bg-lime-500/10 hover:bg-lime-500 text-lime-400 hover:text-slate-950 border border-lime-500/25 rounded-xl transition-all font-bold flex items-center gap-1.5 shadow-sm" 
                            title="Ver Ficha y Atletas Asignados">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Ver Expediente</span>
                    </button>
                    @if(auth()->user()->hasPermission('staff.manage'))
                        <div class="flex items-center gap-2">
                            <!-- Edit Button -->
                            <button type="button" onclick='openEditStaffModal({{ json_encode($trainer->load("user.profile", "gym")) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Datos del Entrenador">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>

                            <!-- Toggle Active Status Button -->
                            <button type="button" onclick="openToggleStaffModal({{ $trainer->id }}, '{{ addslashes($fullName) }}', {{ $trainer->is_active ? 1 : 0 }})" 
                                    id="trainer_toggle_btn_{{ $trainer->id }}"
                                    class="p-2 {{ $trainer->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                    title="{{ $trainer->is_active ? 'Inhabilitar Entrenador' : 'Reactivar Entrenador' }}">
                                <i data-lucide="{{ $trainer->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                            </button>

                            <!-- Permanent Delete Button -->
                            <button type="button" onclick="openDeleteStaffModal({{ $trainer->id }}, '{{ addslashes($fullName) }}')" class="p-2 bg-slate-950 hover:bg-rose-600 text-slate-400 hover:text-white border border-slate-800 hover:border-rose-600 rounded-xl transition-all shadow-sm" title="Eliminar del Staff">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div id="no_staff_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl">
                <i data-lucide="users" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                <p class="font-bold text-slate-400">No hay entrenadores registrados en el staff</p>
                <p class="text-xs text-slate-500 mt-1">Registra tu primer entrenador haciendo clic en "Reclutar Entrenador".</p>
            </div>
        @endforelse

        <div id="no_staff_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl hidden">
            <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
            <p class="font-bold text-slate-400 text-sm">No se encontraron entrenadores que coincidan con la búsqueda.</p>
        </div>
    </div>

    <!-- Staff Pagination Controls Footer -->
    <div id="staff_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
        <span id="staff_pagination_info">Mostrando entrenadores...</span>
        <div class="flex items-center gap-2">
            <button type="button" id="staff_prev_page_btn" onclick="changeStaffPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Anterior
            </button>
            <span id="staff_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
            <button type="button" id="staff_next_page_btn" onclick="changeStaffPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Siguiente
            </button>
        </div>
    </div>

</div>

<!-- ================= MODAL: EXPEDIENTE / DETALLE COMPLETO DEL ENTRENADOR ================= -->
<div id="modal-trainer-details" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-3xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] flex flex-col">
        
        <!-- Modal Top Bar -->
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-lime-500/10 border border-lime-500/20 text-lime-400">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-black text-base text-slate-100" id="details_modal_title">Expediente del Entrenador</h3>
                    <p class="text-xs text-slate-400 font-medium" id="details_modal_subtitle">Ficha técnica y atletas asignados</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-trainer-details')" class="p-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800 rounded-xl transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
            
            <!-- Trainer Hero Profile Header -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 p-5 bg-slate-950/80 border border-slate-850 rounded-3xl">
                <img src="" id="details_photo_img" class="w-20 h-20 rounded-2xl object-cover border border-slate-700 shadow-xl shrink-0">
                
                <div class="space-y-2 text-center sm:text-left flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-xl font-black text-white" id="details_name">Nombre Entrenador</h2>
                            <span class="text-xs text-lime-400 font-bold" id="details_specialty_badge">Especialidad</span>
                        </div>
                        <span id="details_status_pill" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border w-fit mx-auto sm:mx-0">
                            Activo
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-slate-400 text-xs pt-1">
                        <span class="flex items-center gap-1.5"><i data-lucide="id-card" class="w-4 h-4 text-slate-500"></i> <strong id="details_dni" class="text-slate-300">DNI</strong></span>
                        <span class="flex items-center gap-1.5"><i data-lucide="mail" class="w-4 h-4 text-slate-500"></i> <span id="details_email" class="text-slate-300">correo@gym.com</span></span>
                        <span class="flex items-center gap-1.5"><i data-lucide="phone" class="w-4 h-4 text-slate-500"></i> <span id="details_phone" class="text-slate-300">+51 987654321</span></span>
                        @if(auth()->user()->role === 'superadmin')
                            <span class="flex items-center gap-1.5"><i data-lucide="building" class="w-4 h-4 text-slate-500"></i> <span id="details_gym" class="text-slate-300">Sede</span></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detail Tabs Switcher -->
            <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                <button type="button" onclick="switchDetailsTab('profile')" id="tab-btn-profile" class="px-4 py-2 rounded-xl font-black text-xs bg-lime-500/10 text-lime-400 border border-lime-500/30 transition-all flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Perfil & Nómina
                </button>
                <button type="button" onclick="switchDetailsTab('clients')" id="tab-btn-clients" class="px-4 py-2 rounded-xl font-bold text-xs bg-slate-950 text-slate-400 border border-slate-850 hover:text-slate-200 transition-all flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4"></i> Atletas Asignados (<span id="details_clients_count_badge">0</span>)
                </button>
                <button type="button" onclick="switchDetailsTab('routines')" id="tab-btn-routines" class="px-4 py-2 rounded-xl font-bold text-xs bg-slate-950 text-slate-400 border border-slate-850 hover:text-slate-200 transition-all flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i> Rutinas Diseñadas (<span id="details_routines_count_badge">0</span>)
                </button>
            </div>

            <!-- TAB 1: PROFILE & PAYROLL -->
            <div id="details-tab-profile" class="space-y-4">
                
                <!-- Quick Stats row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Salario Mensual</span>
                        <span class="text-base font-black text-emerald-400" id="details_stat_salary">$0.00</span>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Experiencia</span>
                        <span class="text-base font-black text-amber-400" id="details_stat_exp">0 años</span>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Cupo Máximo</span>
                        <span class="text-base font-black text-cyan-400" id="details_stat_capacity">20 atletas</span>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Fecha Contratación</span>
                        <span class="text-base font-black text-slate-200" id="details_stat_hire_date">01/01/2025</span>
                    </div>
                </div>

                <!-- Professional Certifications & Bio -->
                <div class="p-4 bg-slate-950/70 border border-slate-850 rounded-2xl space-y-3">
                    <div>
                        <span class="block text-[10px] text-slate-400 uppercase font-extrabold tracking-wider mb-1">Certificaciones y Grados:</span>
                        <p class="text-slate-200 font-bold" id="details_certification_text">Certificación Fitness Internacional</p>
                    </div>
                    <div class="border-t border-slate-850 pt-3">
                        <span class="block text-[10px] text-slate-400 uppercase font-extrabold tracking-wider mb-1">Biografía / Reseña Profesional:</span>
                        <p class="text-slate-300 leading-relaxed font-medium" id="details_bio_text">Sin descripción registrada.</p>
                    </div>
                </div>

            </div>

            <!-- TAB 2: ASSIGNED CLIENTS -->
            <div id="details-tab-clients" class="space-y-3 hidden">
                <div id="details_clients_list_container" class="space-y-2">
                    <!-- Populated via AJAX -->
                </div>
            </div>

            <!-- TAB 3: CREATED ROUTINES -->
            <div id="details-tab-routines" class="space-y-3 hidden">
                <div id="details_routines_list_container" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Populated via AJAX -->
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900 flex justify-end shrink-0">
            <button type="button" onclick="toggleModal('modal-trainer-details')" class="px-5 py-2 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-slate-100 rounded-xl font-bold transition-all">
                Cerrar Expediente
            </button>
        </div>

    </div>
</div>

<!-- ================= MODAL: RECLUTAR / CREAR ENTRENADOR ================= -->
<div id="modal-create-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-lime-400"></i> Reclutar Nuevo Entrenador
            </h3>
            <button type="button" onclick="toggleModal('modal-create-staff')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-staff-form" action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateStaff(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_first_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombres *</label>
                    <input type="text" name="first_name" id="create_first_name" required placeholder="Ej: Carlos Eduardo" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_last_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Apellidos *</label>
                    <input type="text" name="last_name" id="create_last_name" required placeholder="Ej: Mendoza Pérez" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_dni" class="block text-slate-400 uppercase tracking-wider mb-1.5">DNI / Documento *</label>
                    <input type="text" name="dni" id="create_dni" required placeholder="Ej: 74859612" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" id="create_phone" placeholder="Ej: +51 987654321" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo Electrónico (Acceso) *</label>
                    <input type="email" name="email" id="create_email" required placeholder="carlos@gym.com" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Contraseña de Acceso *</label>
                    <input type="password" name="password" id="create_password" required minlength="6" placeholder="Mínimo 6 caracteres" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_specialty" class="block text-slate-400 uppercase tracking-wider mb-1.5">Especialidad Principal</label>
                    <input type="text" name="specialty" id="create_specialty" placeholder="Ej: Musculación, CrossFit, Hipertrofia..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_certification" class="block text-slate-400 uppercase tracking-wider mb-1.5">Certificación / Grado</label>
                    <input type="text" name="certification" id="create_certification" placeholder="Ej: Lic. Educación Física, IFBB..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label for="create_experience_years" class="block text-slate-400 uppercase tracking-wider mb-1.5">Exp. Previa (Años)</label>
                    <input type="number" name="experience_years" id="create_experience_years" min="0" value="0" oninput="updateExperiencePreview('create')" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_hire_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Contratación</label>
                    <input type="date" name="hire_date" id="create_hire_date" value="{{ date('Y-m-d') }}" onchange="updateExperiencePreview('create')" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
                <div>
                    <label for="create_salary" class="block text-slate-400 uppercase tracking-wider mb-1.5">Salario ($) *</label>
                    <input type="number" step="0.01" name="salary" id="create_salary" required min="0" placeholder="1500.00" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_max_clients" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cupo Atletas</label>
                    <input type="number" name="max_clients" id="create_max_clients" min="1" value="20" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <!-- Dynamic Experience Preview in Create Modal -->
            <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-850 flex items-center justify-between text-xs font-semibold">
                <span class="text-slate-400 font-medium flex items-center gap-1.5">
                    <i data-lucide="calculator" class="w-4 h-4 text-amber-400"></i> Exp. Total Dinámica:
                </span>
                <span class="font-extrabold text-amber-400" id="create_experience_preview_text">0 años exp. (Ingreso reciente / hoy)</span>
            </div>

            <div>
                <label for="create_photo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto de Perfil</label>
                <input type="file" name="photo" id="create_photo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2 text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
            </div>

            <div>
                <label for="create_bio" class="block text-slate-400 uppercase tracking-wider mb-1.5">Biografía / Reseña Profesional</label>
                <textarea name="bio" id="create_bio" rows="2" placeholder="Experiencia con atletas, logros y metodología de entrenamiento..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-staff')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-staff-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Reclutar Entrenador</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR ENTRENADOR ================= -->
<div id="modal-edit-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Datos de Entrenador
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-staff')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-staff-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditStaff(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_first_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombres *</label>
                    <input type="text" name="first_name" id="edit_first_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_last_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Apellidos *</label>
                    <input type="text" name="last_name" id="edit_last_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_dni" class="block text-slate-400 uppercase tracking-wider mb-1.5">DNI / Documento *</label>
                    <input type="text" name="dni" id="edit_dni" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo Electrónico (Acceso) *</label>
                    <input type="email" name="email" id="edit_email" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cambiar Contraseña (Opcional)</label>
                    <input type="password" name="password" id="edit_password" placeholder="Dejar en blanco para no cambiar" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_specialty" class="block text-slate-400 uppercase tracking-wider mb-1.5">Especialidad Principal</label>
                    <input type="text" name="specialty" id="edit_specialty" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_certification" class="block text-slate-400 uppercase tracking-wider mb-1.5">Certificación / Grado</label>
                    <input type="text" name="certification" id="edit_certification" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label for="edit_experience_years" class="block text-slate-400 uppercase tracking-wider mb-1.5">Exp. Previa (Años)</label>
                    <input type="number" name="experience_years" id="edit_experience_years" min="0" oninput="updateExperiencePreview('edit')" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_hire_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Contratación</label>
                    <input type="date" name="hire_date" id="edit_hire_date" onchange="updateExperiencePreview('edit')" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
                <div>
                    <label for="edit_salary" class="block text-slate-400 uppercase tracking-wider mb-1.5">Salario ($) *</label>
                    <input type="number" step="0.01" name="salary" id="edit_salary" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_max_clients" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cupo Atletas</label>
                    <input type="number" name="max_clients" id="edit_max_clients" min="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <!-- Dynamic Experience Preview in Edit Modal -->
            <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-850 flex items-center justify-between text-xs font-semibold">
                <span class="text-slate-400 font-medium flex items-center gap-1.5">
                    <i data-lucide="calculator" class="w-4 h-4 text-amber-400"></i> Exp. Total Calculada al Día de Hoy:
                </span>
                <span class="font-extrabold text-amber-400" id="edit_experience_preview_text">0 años exp. (0 meses en el staff)</span>
            </div>

            <div>
                <label for="edit_photo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nueva Foto de Perfil (Opcional)</label>
                <input type="file" name="photo" id="edit_photo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2 text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
            </div>

            <div>
                <label for="edit_bio" class="block text-slate-400 uppercase tracking-wider mb-1.5">Biografía / Reseña Profesional</label>
                <textarea name="bio" id="edit_bio" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-staff')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-staff-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: INHABILITAR / REACTIVAR ENTRENADOR ================= -->
<div id="modal-toggle-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-sm mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto flex items-center justify-center">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        <div class="space-y-1">
            <h3 class="font-extrabold text-base text-slate-100" id="modal-staff-status-title">Cambiar Estado de Entrenador</h3>
            <p class="text-xs text-slate-400" id="modal-staff-status-desc">¿Estás seguro de realizar esta acción?</p>
        </div>
        <form id="toggle-staff-form" action="" method="POST" onsubmit="submitToggleStaff(event)" class="pt-2 flex items-center gap-3">
            @csrf
            <button type="button" onclick="toggleModal('modal-toggle-staff')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-slate-300 font-bold text-xs rounded-xl transition-all">Cancelar</button>
            <button type="submit" id="toggle-staff-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <span id="modal-staff-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: ELIMINAR PERMANENTE ENTRENADOR ================= -->
<div id="modal-delete-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-sm mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center">
            <i data-lucide="trash-2" class="w-6 h-6"></i>
        </div>
        <div class="space-y-1">
            <h3 class="font-extrabold text-base text-slate-100">Eliminar del Staff</h3>
            <p class="text-xs text-slate-400" id="modal-delete-staff-desc">Esta acción eliminará al entrenador y su cuenta de forma definitiva.</p>
        </div>
        <form id="delete-staff-form" action="" method="POST" onsubmit="submitDeleteStaff(event)" class="pt-2 flex items-center gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-staff')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-slate-300 font-bold text-xs rounded-xl transition-all">Cancelar</button>
            <button type="submit" id="delete-staff-submit-btn" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                Eliminar
            </button>
        </form>
</div>

<script>
    (function() {
        // Global State & Pagination
        let currentStaffStatusFilter = 'all';
        let currentStaffSearchQuery = '';
        let currentStaffPage = 1;
        const staffPerPage = 9;

        function initStaffView() {
            if (window.lucide) window.lucide.createIcons();
            renderStaffPage();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initStaffView);
        } else {
            setTimeout(initStaffView, 10);
        }

    function toggleModal(id) {
        if (typeof window.toggleModal === 'function') {
            window.toggleModal(id);
        } else {
            const modal = document.getElementById(id);
            if (modal) modal.classList.toggle('hidden');
        }
    }

    // Modal Details Tab Switcher
    function switchDetailsTab(tabName) {
        const tabs = ['profile', 'clients', 'routines'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const content = document.getElementById(`details-tab-${t}`);
            if (t === tabName) {
                if (btn) {
                    btn.className = 'px-4 py-2 rounded-xl font-black text-xs bg-lime-500/10 text-lime-400 border border-lime-500/30 transition-all flex items-center gap-2';
                }
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.className = 'px-4 py-2 rounded-xl font-bold text-xs bg-slate-950 text-slate-400 border border-slate-850 hover:text-slate-200 transition-all flex items-center gap-2';
                }
                if (content) content.classList.add('hidden');
            }
        });
        if (window.lucide) window.lucide.createIcons();
    }

    // Open Trainer Details Modal (AJAX)
    async function openTrainerDetailsModal(trainerId) {
        toggleModal('modal-trainer-details');
        switchDetailsTab('profile');

        const titleEl = document.getElementById('details_modal_title');
        const nameEl = document.getElementById('details_name');
        const specEl = document.getElementById('details_specialty_badge');
        const statusPill = document.getElementById('details_status_pill');
        const dniEl = document.getElementById('details_dni');
        const emailEl = document.getElementById('details_email');
        const phoneEl = document.getElementById('details_phone');
        const gymEl = document.getElementById('details_gym');
        const photoImg = document.getElementById('details_photo_img');

        const salaryEl = document.getElementById('details_stat_salary');
        const expEl = document.getElementById('details_stat_exp');
        const capacityEl = document.getElementById('details_stat_capacity');
        const hireDateEl = document.getElementById('details_stat_hire_date');
        const certEl = document.getElementById('details_certification_text');
        const bioEl = document.getElementById('details_bio_text');

        const clientsCountBadge = document.getElementById('details_clients_count_badge');
        const clientsContainer = document.getElementById('details_clients_list_container');
        const routinesCountBadge = document.getElementById('details_routines_count_badge');
        const routinesContainer = document.getElementById('details_routines_list_container');

        if (nameEl) nameEl.textContent = 'Cargando expediente...';
        if (clientsContainer) clientsContainer.innerHTML = '<div class="p-8 text-center text-slate-500"><i data-lucide="loader-2" class="w-6 h-6 mx-auto animate-spin mb-2"></i>Cargando atletas asignados...</div>';
        if (routinesContainer) routinesContainer.innerHTML = '<div class="p-8 text-center text-slate-500"><i data-lucide="loader-2" class="w-6 h-6 mx-auto animate-spin mb-2"></i>Cargando catálogo de rutinas...</div>';
        if (window.lucide) window.lucide.createIcons();

        try {
            const response = await fetch(`/staff/${trainerId}/detalles`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const t = data.trainer;
                const p = t.user?.profile;
                const fullName = `${t.first_name || ''} ${t.last_name || ''}`.trim();
                const photoSrc = t.photo_url ? `/${t.photo_url}` : (p?.profile_photo ? `/${p.profile_photo}` : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop');

                if (titleEl) titleEl.textContent = `Expediente: ${fullName}`;
                if (nameEl) nameEl.textContent = fullName;
                if (specEl) specEl.textContent = t.specialty || 'Entrenador General';
                if (dniEl) dniEl.textContent = p?.dni || 'Sin DNI';
                if (emailEl) emailEl.textContent = t.email || 'Sin correo';
                if (phoneEl) phoneEl.textContent = t.phone || p?.phone || 'Sin teléfono';
                if (gymEl) gymEl.textContent = t.gym?.name || 'Sede Principal';
                if (photoImg) photoImg.src = photoSrc;

                if (statusPill) {
                    if (t.is_active) {
                        statusPill.className = 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                        statusPill.textContent = 'En Servicio (Activo)';
                    } else {
                        statusPill.className = 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border bg-rose-500/10 text-rose-400 border-rose-500/20';
                        statusPill.textContent = 'Inactivo / Licencia';
                    }
                }

                if (salaryEl) salaryEl.textContent = `$${parseFloat(t.salary || 0).toFixed(2)}`;
                if (expEl) expEl.textContent = `${t.experience_years || 0} años`;
                if (capacityEl) capacityEl.textContent = `${t.max_clients || 20} atletas`;
                if (hireDateEl) hireDateEl.textContent = t.hire_date || 'No registrado';
                if (certEl) certEl.textContent = t.certification || 'Sin certificaciones especificadas.';
                if (bioEl) bioEl.textContent = t.bio || 'Sin biografía profesional disponible.';

                // Render Assigned Clients
                const clients = data.assigned_clients || [];
                if (clientsCountBadge) clientsCountBadge.textContent = clients.length;

                if (clientsContainer) {
                    if (clients.length === 0) {
                        clientsContainer.innerHTML = `
                            <div class="p-8 text-center bg-slate-950/40 border border-slate-850 rounded-2xl">
                                <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400">Este entrenador no tiene atletas asignados actualmente</p>
                                <p class="text-xs text-slate-500 mt-0.5">Puedes asignarle atletas desde el expediente de clientes.</p>
                            </div>
                        `;
                    } else {
                        clientsContainer.innerHTML = clients.map(c => `
                            <div class="flex items-center justify-between p-3.5 bg-slate-950/70 border border-slate-850 rounded-2xl hover:border-lime-500/30 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center font-black text-lime-400 shrink-0 overflow-hidden">
                                        ${c.photo ? `<img src="${c.photo}" class="w-full h-full object-cover">` : `<i data-lucide="user" class="w-5 h-5"></i>`}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-200 text-sm">${escapeHtml(c.full_name)}</h4>
                                        <span class="text-[10px] text-slate-400 font-medium">${escapeHtml(c.email)} • DNI: ${escapeHtml(c.dni)}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-[10px] font-bold">Desde: ${c.assigned_at}</span>
                                    <span class="block text-[10px] text-slate-500 mt-1">${escapeHtml(c.phone)}</span>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                // Render Created Routines
                const routines = data.routines || [];
                if (routinesCountBadge) routinesCountBadge.textContent = routines.length;

                if (routinesContainer) {
                    if (routines.length === 0) {
                        routinesContainer.innerHTML = `
                            <div class="col-span-full p-8 text-center bg-slate-950/40 border border-slate-850 rounded-2xl">
                                <i data-lucide="clipboard-x" class="w-8 h-8 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400">No ha diseñado planes de rutinas en el sistema</p>
                            </div>
                        `;
                    } else {
                        routinesContainer.innerHTML = routines.map(r => `
                            <div class="p-3.5 bg-slate-950/70 border border-slate-850 rounded-2xl space-y-2">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-black text-slate-100 text-xs truncate max-w-[170px]">${escapeHtml(r.name)}</h4>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider ${r.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-400'}">${r.difficulty}</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold border-t border-slate-850/80 pt-1.5">
                                    <span>${r.duration_weeks} semanas</span>
                                    <span>${r.days_per_week} días/sem</span>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                if (window.lucide) window.lucide.createIcons();
            } else {
                showToast(data.message || 'Error al cargar expediente.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al consultar el expediente.', 'error');
        }
    }

    // Filter Handling
    function setStaffStatusFilter(status) {
        currentStaffStatusFilter = status;
        document.querySelectorAll('.staff-status-tab-btn').forEach(btn => {
            btn.className = 'staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all';
        });

        const activeBtn = document.getElementById(`staff-status-filter-${status}`);
        if (activeBtn) {
            activeBtn.className = 'staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all';
        }

        currentStaffPage = 1;
        renderStaffPage();
    }

    function onStaffFilterChange() {
        const searchInput = document.getElementById('search-staff-input');
        currentStaffSearchQuery = (searchInput ? searchInput.value : '').toLowerCase().trim();
        currentStaffPage = 1;
        renderStaffPage();
    }

    function renderStaffPage() {
        const cards = Array.from(document.querySelectorAll('[data-trainer-card]'));
        const searchRow = document.getElementById('no_staff_search_row');

        const filtered = cards.filter(card => {
            const name = card.getAttribute('data-name') || '';
            const dni = card.getAttribute('data-dni') || '';
            const email = card.getAttribute('data-email') || '';
            const spec = card.getAttribute('data-specialty') || '';
            const active = card.getAttribute('data-active') || '';

            // Status filter
            if (currentStaffStatusFilter !== 'all' && active !== currentStaffStatusFilter) {
                return false;
            }

            // Search query filter
            if (currentStaffSearchQuery) {
                const matchName = name.includes(currentStaffSearchQuery);
                const matchDni = dni.includes(currentStaffSearchQuery);
                const matchEmail = email.includes(currentStaffSearchQuery);
                const matchSpec = spec.includes(currentStaffSearchQuery);
                if (!matchName && !matchDni && !matchEmail && !matchSpec) {
                    return false;
                }
            }

            return true;
        });

        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / staffPerPage) || 1;
        if (currentStaffPage > totalPages) currentStaffPage = totalPages;
        if (currentStaffPage < 1) currentStaffPage = 1;

        const startIndex = (currentStaffPage - 1) * staffPerPage;
        const endIndex = startIndex + staffPerPage;

        cards.forEach(card => card.classList.add('hidden'));

        if (totalItems === 0) {
            if (searchRow) searchRow.classList.remove('hidden');
        } else {
            if (searchRow) searchRow.classList.add('hidden');
            filtered.slice(startIndex, endIndex).forEach(card => {
                card.classList.remove('hidden');
            });
        }

        // Update Pagination controls
        const paginationInfo = document.getElementById('staff_pagination_info');
        const pageNumberDisplay = document.getElementById('staff_page_number_display');
        const prevBtn = document.getElementById('staff_prev_page_btn');
        const nextBtn = document.getElementById('staff_next_page_btn');

        if (paginationInfo) {
            paginationInfo.textContent = totalItems > 0 
                ? `Mostrando ${startIndex + 1} - ${Math.min(endIndex, totalItems)} de ${totalItems} entrenadores`
                : 'No hay resultados que mostrar';
        }

        if (pageNumberDisplay) {
            pageNumberDisplay.textContent = `Página ${currentStaffPage} de ${totalPages}`;
        }

        if (prevBtn) prevBtn.disabled = (currentStaffPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentStaffPage >= totalPages);
    }

    function changeStaffPage(direction) {
        currentStaffPage += direction;
        renderStaffPage();
    }

    // Interactive Experience Calculation Preview
    function updateExperiencePreview(prefix) {
        const yearsInput = document.getElementById(`${prefix}_experience_years`);
        const hireDateInput = document.getElementById(`${prefix}_hire_date`);
        const previewEl = document.getElementById(`${prefix}_experience_preview_text`);

        if (!previewEl) return;

        const baseYears = parseInt(yearsInput ? yearsInput.value : 0) || 0;
        const hireVal = hireDateInput ? hireDateInput.value : null;

        if (!hireVal) {
            previewEl.textContent = `${baseYears} años de experiencia previa`;
            return;
        }

        const hireDate = new Date(hireVal);
        const now = new Date();

        if (isNaN(hireDate.getTime()) || hireDate > now) {
            previewEl.textContent = `${baseYears} años de exp. total (Ingreso reciente / hoy)`;
            return;
        }

        let diffMonths = (now.getFullYear() - hireDate.getFullYear()) * 12 + (now.getMonth() - hireDate.getMonth());
        if (now.getDate() < hireDate.getDate()) {
            diffMonths--;
        }
        if (diffMonths < 0) diffMonths = 0;

        const yearsInGym = Math.floor(diffMonths / 12);
        const remMonths = diffMonths % 12;
        const totalExp = baseYears + yearsInGym;

        let tenureText = '';
        if (yearsInGym > 0 && remMonths > 0) {
            tenureText = `${yearsInGym} ${yearsInGym === 1 ? 'año' : 'años'} y ${remMonths} ${remMonths === 1 ? 'mes' : 'meses'}`;
        } else if (yearsInGym > 0) {
            tenureText = `${yearsInGym} ${yearsInGym === 1 ? 'año' : 'años'}`;
        } else if (remMonths > 0) {
            tenureText = `${remMonths} ${remMonths === 1 ? 'mes' : 'meses'}`;
        } else {
            tenureText = 'Menos de 1 mes';
        }

        previewEl.textContent = `${totalExp} años de exp. total (${tenureText} en el staff)`;
    }

    // Modal Helpers (Staff)
    function openCreateStaffModal() {
        const form = document.getElementById('create-staff-form');
        if (form) form.reset();
        const todayStr = new Date().toISOString().split('T')[0];
        const hireInput = document.getElementById('create_hire_date');
        if (hireInput) hireInput.value = todayStr;
        updateExperiencePreview('create');
        toggleModal('modal-create-staff');
    }

    function openEditStaffModal(trainer) {
        if (!trainer) return;
        const form = document.getElementById('edit-staff-form');
        if (form) form.action = `/staff/${trainer.id}`;
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal('edit_first_name', trainer.first_name || (trainer.user?.profile?.first_name || ''));
        setVal('edit_last_name', trainer.last_name || (trainer.user?.profile?.last_name || ''));
        setVal('edit_dni', trainer.user?.profile?.dni || '');
        setVal('edit_phone', trainer.phone || (trainer.user?.profile?.phone || ''));
        setVal('edit_email', trainer.email || (trainer.user?.email || ''));
        setVal('edit_password', '');
        setVal('edit_specialty', trainer.specialty || '');
        setVal('edit_certification', trainer.certification || '');
        setVal('edit_experience_years', trainer.experience_years ?? 0);
        setVal('edit_hire_date', trainer.hire_date ? trainer.hire_date.split('T')[0] : '');
        setVal('edit_salary', trainer.salary ?? 0);
        setVal('edit_max_clients', trainer.max_clients ?? 20);
        setVal('edit_bio', trainer.bio || '');

        updateExperiencePreview('edit');
        toggleModal('modal-edit-staff');
    }

    function openToggleStaffModal(id, fullName, isActive) {
        const form = document.getElementById('toggle-staff-form');
        if (form) form.action = `/staff/${id}/toggle`;
        const titleEl = document.getElementById('modal-staff-status-title');
        const descEl = document.getElementById('modal-staff-status-desc');
        const btnTextEl = document.getElementById('modal-staff-status-btn-text');
        const submitBtn = document.getElementById('toggle-staff-submit-btn');

        if (isActive) {
            if (titleEl) titleEl.textContent = 'Inhabilitar Entrenador';
            if (descEl) descEl.innerHTML = `¿Estás seguro de que deseas inhabilitar al entrenador (<strong class="text-slate-100">${escapeHtml(fullName)}</strong>)? Sus accesos quedarán suspendidos.`;
            if (btnTextEl) btnTextEl.textContent = 'Sí, Inhabilitar';
            if (submitBtn) submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            if (titleEl) titleEl.textContent = 'Reactivar Entrenador';
            if (descEl) descEl.innerHTML = `¿Deseas reactivar al entrenador (<strong class="text-slate-100">${escapeHtml(fullName)}</strong>) para restaurar su servicio?`;
            if (btnTextEl) btnTextEl.textContent = 'Sí, Reactivar';
            if (submitBtn) submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-toggle-staff');
    }

    function openDeleteStaffModal(id, fullName) {
        const form = document.getElementById('delete-staff-form');
        if (form) form.action = `/staff/${id}`;
        const descEl = document.getElementById('modal-delete-staff-desc');
        if (descEl) descEl.innerHTML = `¿Estás seguro de que deseas eliminar permanentemente al entrenador (<strong class="text-slate-100">${escapeHtml(fullName)}</strong>)? Esta acción eliminará su cuenta de acceso.`;
        toggleModal('modal-delete-staff');
    }

    // AJAX Form Submissions
    async function submitCreateStaff(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-staff-submit-btn');

        setBtnLoading(submitBtn, true, 'Reclutando...');

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
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showToast(data.message || 'Error al reclutar entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al registrar el entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitEditStaff(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-staff-submit-btn');

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
                const t = data.trainer;
                const p = t.user?.profile;
                const fullName = `${t.first_name || ''} ${t.last_name || ''}`.trim();
                const dni = p?.dni || 'Sin DNI';
                const card = document.getElementById(`trainer_card_${t.id}`);

                if (card) {
                    card.setAttribute('data-name', fullName.toLowerCase());
                    card.setAttribute('data-dni', dni.toLowerCase());
                    card.setAttribute('data-email', (t.email || '').toLowerCase());
                    card.setAttribute('data-specialty', (t.specialty || '').toLowerCase());

                    const nameEl = document.getElementById(`trainer_name_${t.id}`);
                    const dniEl = document.getElementById(`trainer_dni_${t.id}`);
                    const emailEl = document.getElementById(`trainer_email_${t.id}`);
                    const specEl = document.getElementById(`trainer_specialty_${t.id}`);
                    const certEl = document.getElementById(`trainer_cert_${t.id}`);
                    const expEl = document.getElementById(`trainer_exp_${t.id}`);
                    const salaryEl = document.getElementById(`trainer_salary_${t.id}`);
                    const maxClientsEl = document.getElementById(`trainer_clients_max_${t.id}`);
                    const photoImg = document.getElementById(`trainer_photo_img_${t.id}`);

                    if (nameEl) nameEl.textContent = fullName;
                    if (dniEl) dniEl.textContent = `DNI: ${dni}`;
                    if (emailEl) emailEl.textContent = t.email;
                    if (specEl) specEl.textContent = t.specialty || 'Entrenador General';
                    if (certEl) certEl.textContent = t.certification || 'Sin datos';
                    const totalExp = t.total_experience_years !== undefined ? t.total_experience_years : (t.experience_years || 0);
                    const tenureStr = t.tenure ? ` • ${t.tenure} en el staff` : '';
                    if (expEl) expEl.textContent = `${totalExp} años exp.${tenureStr}`;
                    if (salaryEl) salaryEl.textContent = `$ ${parseFloat(t.salary || 0).toFixed(2)}`;
                    if (maxClientsEl) maxClientsEl.textContent = t.max_clients || 20;

                    if (t.photo_url && photoImg) {
                        photoImg.src = `/${t.photo_url}`;
                    } else if (p?.profile_photo && photoImg) {
                        photoImg.src = `/${p.profile_photo}`;
                    }
                }

                toggleModal('modal-edit-staff');
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitToggleStaff(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('toggle-staff-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const card = document.getElementById(`trainer_card_${data.trainer_id}`);
                const badge = document.getElementById(`trainer_status_badge_${data.trainer_id}`);
                const dot = document.getElementById(`trainer_dot_${data.trainer_id}`);
                const toggleBtn = document.getElementById(`trainer_toggle_btn_${data.trainer_id}`);

                if (card) {
                    card.setAttribute('data-active', data.is_active ? '1' : '0');
                    if (data.is_active) {
                        card.className = card.className.replace('opacity-60 bg-slate-950/40 border-slate-850', '');
                    } else {
                        card.className += ' opacity-60 bg-slate-950/40 border-slate-850';
                    }
                }

                if (badge) {
                    badge.className = data.is_active
                        ? 'px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                        : 'px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-rose-500/10 text-rose-400 border-rose-500/20';
                    badge.textContent = data.is_active ? 'Activo' : 'Inactivo';
                }

                if (dot) {
                    dot.className = `w-3.5 h-3.5 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 ${data.is_active ? 'bg-emerald-500' : 'bg-rose-500'}`;
                }

                if (toggleBtn) {
                    toggleBtn.className = data.is_active
                        ? 'p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm'
                        : 'p-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm';
                    toggleBtn.innerHTML = `<i data-lucide="${data.is_active ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-toggle-staff');
                renderStaffPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar estado del entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitDeleteStaff(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-staff-submit-btn');

        setBtnLoading(submitBtn, true, 'Eliminando...');

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
                const card = document.getElementById(`trainer_card_${data.trainer_id}`);
                if (card) card.remove();

                toggleModal('modal-delete-staff');
                renderStaffPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al eliminar entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al eliminar al entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Utilities using universal global toast
    function showToast(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type === 'danger' ? 'error' : type);
        }
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

    // Expose all handlers to window for HTML onclick attributes & PJAX execution
    window.toggleModal = toggleModal;
    window.switchDetailsTab = switchDetailsTab;
    window.openTrainerDetailsModal = openTrainerDetailsModal;
    window.updateExperiencePreview = updateExperiencePreview;
    window.openCreateStaffModal = openCreateStaffModal;
    window.openEditStaffModal = openEditStaffModal;
    window.openToggleStaffModal = openToggleStaffModal;
    window.openDeleteStaffModal = openDeleteStaffModal;
    window.setStaffStatusFilter = setStaffStatusFilter;
    window.onStaffFilterChange = onStaffFilterChange;
    window.renderStaffPage = renderStaffPage;
    window.changeStaffPage = changeStaffPage;
    window.submitCreateStaff = submitCreateStaff;
    window.submitEditStaff = submitEditStaff;
    window.submitToggleStaff = submitToggleStaff;
    window.submitDeleteStaff = submitDeleteStaff;
    window.showToast = showToast;
    })();
</script>
@endsection
