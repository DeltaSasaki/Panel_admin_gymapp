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
            <p class="text-xs text-slate-400 mt-1 font-medium">Gestiona el personal de entrenamiento, salarios de nómina, especialidades y credenciales de acceso.</p>
        </div>
        <div>
            <button type="button" onclick="openCreateModal()" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4 stroke-[3px]"></i> Reclutar Entrenador
            </button>
        </div>
    </div>

    <!-- Quick Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Total Personal Staff</span>
                <h3 class="text-2xl font-black text-slate-100"><span id="stat_total">{{ $trainers->count() }}</span> <span class="text-xs font-normal text-slate-400">entrenadores</span></h3>
            </div>
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-400">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Entrenadores Activos</span>
                <h3 class="text-2xl font-black text-emerald-400"><span id="stat_active">{{ $trainers->where('is_active', 1)->count() }}</span> <span class="text-xs font-normal text-slate-400">activos</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-rose-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Inactivos / Licencia</span>
                <h3 class="text-2xl font-black text-rose-400"><span id="stat_inactive">{{ $trainers->where('is_active', 0)->count() }}</span> <span class="text-xs font-normal text-slate-400">inactivos</span></h3>
            </div>
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400">
                <i data-lucide="user-x" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Nómina Estimada</span>
                <h3 class="text-2xl font-black text-amber-400">$ <span id="stat_payroll">{{ number_format($trainers->where('is_active', 1)->sum('salary'), 2) }}</span></h3>
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
                    Todos (<span id="count-status-all">{{ $trainers->count() }}</span>)
                </button>
                <button type="button" onclick="setStaffStatusFilter('1')" id="staff-status-filter-1" class="staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Activos (<span id="count-status-active">{{ $trainers->where('is_active', 1)->count() }}</span>)
                </button>
                <button type="button" onclick="setStaffStatusFilter('0')" id="staff-status-filter-0" class="staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Inactivos (<span id="count-status-inactive">{{ $trainers->where('is_active', 0)->count() }}</span>)
                </button>
            </div>
        </div>

        <!-- Live Search Bar -->
        <div class="relative w-full xl:w-72">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-staff-input" oninput="onStaffFilterChange()" placeholder="Buscar por nombre, DNI o especialidad..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
        </div>
    </div>

    <!-- Trainers Grid Container -->
    <div id="trainers-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($trainers as $trainer)
            @php
                $fullName = trim(($trainer->first_name ?? '') . ' ' . ($trainer->last_name ?? ''));
                $dni = $trainer->user->profile->dni ?? 'Sin DNI';
                $photoUrl = $trainer->photo_url 
                    ? asset($trainer->photo_url) 
                    : ($trainer->user->profile->profile_photo ? asset($trainer->user->profile->profile_photo) : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop');
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
                    <!-- Card Top Profile Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="relative shrink-0">
                                <img src="{{ $photoUrl }}" id="trainer_photo_img_{{ $trainer->id }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-700 shadow-md">
                                <span id="trainer_dot_{{ $trainer->id }}" class="w-3 h-3 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 {{ $trainer->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-black text-base text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="trainer_name_{{ $trainer->id }}">{{ $fullName }}</h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-mono font-bold rounded" id="trainer_dni_{{ $trainer->id }}">DNI: {{ $dni }}</span>
                                    <span class="text-[10px] text-slate-400 truncate" id="trainer_email_{{ $trainer->id }}">{{ $trainer->email }}</span>
                                </div>
                            </div>
                        </div>

                        <span id="trainer_status_badge_{{ $trainer->id }}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 {{ $trainer->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                            {{ $trainer->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- Specialty & Experience Info Pill -->
                    <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl space-y-2 text-xs font-semibold">
                        <div class="flex items-center justify-between text-slate-300">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Especialidad:</span>
                            <span class="font-black text-slate-200" id="trainer_specialty_{{ $trainer->id }}">{{ $trainer->specialty ?? 'Entrenador General' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Certificación:</span>
                            <span class="font-bold text-slate-300 truncate" id="trainer_cert_{{ $trainer->id }}">{{ $trainer->certification ?? 'Sin datos' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300 border-t border-slate-850/80 pt-2">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Experiencia:</span>
                            <span class="font-extrabold text-amber-400" id="trainer_exp_{{ $trainer->id }}">{{ $trainer->experience_years ?? 0 }} años de exp.</span>
                        </div>
                    </div>

                    <!-- Salary & Max Clients Summary -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-center">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Salario Nómina</span>
                            <span class="font-black text-emerald-400 text-sm" id="trainer_salary_{{ $trainer->id }}">$ {{ number_format($trainer->salary, 2) }}</span>
                        </div>
                        <div class="p-2.5 bg-blue-500/10 border border-blue-500/20 rounded-xl text-center">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Cupo de Atletas</span>
                            <span class="font-black text-blue-400 text-sm" id="trainer_clients_{{ $trainer->id }}">{{ $trainer->max_clients ?? 20 }} máx.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold">
                    <span class="text-[10px] text-slate-500 font-bold uppercase">
                        Tel: <strong class="text-slate-300 font-semibold" id="trainer_phone_{{ $trainer->id }}">{{ $trainer->phone ?? 'Sin Teléfono' }}</strong>
                    </span>

                    <div class="flex items-center gap-2">
                        <!-- Edit Button -->
                        <button type="button" onclick='openEditModal({{ json_encode($trainer->load("user.profile")) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Datos del Entrenador">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>

                        <!-- Toggle Active Status Button -->
                        <button type="button" onclick="openToggleModal({{ $trainer->id }}, '{{ addslashes($fullName) }}', {{ $trainer->is_active ? 1 : 0 }})" 
                                id="trainer_toggle_btn_{{ $trainer->id }}"
                                class="p-2 {{ $trainer->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                title="{{ $trainer->is_active ? 'Inhabilitar Entrenador' : 'Reactivar Entrenador' }}">
                            <i data-lucide="{{ $trainer->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                        </button>

                        <!-- Permanent Delete Button -->
                        <button type="button" onclick="openDeleteModal({{ $trainer->id }}, '{{ addslashes($fullName) }}')" class="p-2 bg-slate-950 hover:bg-rose-600 text-slate-400 hover:text-white border border-slate-800 hover:border-rose-600 rounded-xl transition-all shadow-sm" title="Eliminar del Staff">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
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

<!-- ================= MODAL: RECLUTAR / CREAR ENTRENADOR ================= -->
<div id="modal-create-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
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

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="create_experience_years" class="block text-slate-400 uppercase tracking-wider mb-1.5">Años Exp.</label>
                    <input type="number" name="experience_years" id="create_experience_years" min="0" value="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_salary" class="block text-slate-400 uppercase tracking-wider mb-1.5">Salario ($) *</label>
                    <input type="number" step="0.01" name="salary" id="create_salary" required min="0" placeholder="1500.00" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_max_clients" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cupo Atletas</label>
                    <input type="number" name="max_clients" id="create_max_clients" min="1" value="20" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="create_photo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto de Perfil</label>
                <input type="file" name="photo" id="create_photo" accept="image/*" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2 text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
            </div>

            <div>
                <label for="create_bio" class="block text-slate-400 uppercase tracking-wider mb-1.5">Biografía / Reseña Profesional</label>
                <textarea name="bio" id="create_bio" rows="2" placeholder="Resumen de trayectoria y objetivos como entrenador..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-staff')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-staff-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Registrar Entrenador</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR ENTRENADOR ================= -->
<div id="modal-edit-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Entrenador
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
                    <label for="edit_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" id="edit_password" minlength="6" placeholder="Dejar en blanco para mantener" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
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

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="edit_experience_years" class="block text-slate-400 uppercase tracking-wider mb-1.5">Años Exp.</label>
                    <input type="number" name="experience_years" id="edit_experience_years" min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_salary" class="block text-slate-400 uppercase tracking-wider mb-1.5">Salario ($) *</label>
                    <input type="number" step="0.01" name="salary" id="edit_salary" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_max_clients" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cupo Atletas</label>
                    <input type="number" name="max_clients" id="edit_max_clients" min="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="edit_photo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nueva Foto (Opcional)</label>
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

<!-- ================= MODAL: CAMBIAR ESTADO / INHABILITAR ================= -->
<div id="modal-toggle-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-staff-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-staff-status-title">Cambiar Estado de Entrenador</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-toggle-staff')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-staff-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de este entrenador?
        </p>

        <form id="toggle-staff-form" action="" method="POST" onsubmit="submitToggleStaff(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            <button type="button" onclick="toggleModal('modal-toggle-staff')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="toggle-staff-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-staff-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: ELIMINAR PERMANENTE ================= -->
<div id="modal-delete-staff" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 shrink-0">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Eliminar Entrenador</h3>
                    <span class="text-xs text-rose-400 font-semibold flex items-center gap-1">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i> Acción irreversible
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-delete-staff')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-delete-staff-desc">
            ¿Estás seguro de que deseas eliminar permanentemente a este entrenador del sistema?
        </p>

        <form id="delete-staff-form" action="" method="POST" onsubmit="submitDeleteStaff(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-staff')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-staff-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Sí, Eliminar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Floating Toast Notifications System
    function showToast(message, type = 'success') {
        let container = document.getElementById('staff-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'staff-toast-container';
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

    function openCreateModal() {
        document.getElementById('create-staff-form').reset();
        toggleModal('modal-create-staff');
    }

    function openEditModal(trainer) {
        document.getElementById('edit-staff-form').action = `/staff/${trainer.id}`;
        document.getElementById('edit_first_name').value = trainer.first_name || (trainer.user?.profile?.first_name || '');
        document.getElementById('edit_last_name').value = trainer.last_name || (trainer.user?.profile?.last_name || '');
        document.getElementById('edit_dni').value = trainer.user?.profile?.dni || '';
        document.getElementById('edit_phone').value = trainer.phone || (trainer.user?.profile?.phone || '');
        document.getElementById('edit_email').value = trainer.email || (trainer.user?.email || '');
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_specialty').value = trainer.specialty || '';
        document.getElementById('edit_certification').value = trainer.certification || '';
        document.getElementById('edit_experience_years').value = trainer.experience_years ?? 0;
        document.getElementById('edit_salary').value = trainer.salary ?? 0;
        document.getElementById('edit_max_clients').value = trainer.max_clients ?? 20;
        document.getElementById('edit_bio').value = trainer.bio || '';

        toggleModal('modal-edit-staff');
    }

    function openToggleModal(id, fullName, isActive) {
        document.getElementById('toggle-staff-form').action = `/staff/${id}/toggle`;
        const titleEl = document.getElementById('modal-staff-status-title');
        const descEl = document.getElementById('modal-staff-status-desc');
        const btnTextEl = document.getElementById('modal-staff-status-btn-text');
        const submitBtn = document.getElementById('toggle-staff-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Entrenador';
            descEl.innerHTML = `¿Estás seguro de que deseas deshabilitar a <strong class="text-slate-100">${escapeHtml(fullName)}</strong>? Sus accesos serán suspendidos temporalmente.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Entrenador';
            descEl.innerHTML = `¿Deseas reactivar al entrenador <strong class="text-slate-100">${escapeHtml(fullName)}</strong> para restaurar su acceso al sistema?`;
            btnTextEl.textContent = 'Sí, Reactivar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-toggle-staff');
    }

    function openDeleteModal(id, fullName) {
        document.getElementById('delete-staff-form').action = `/staff/${id}`;
        document.getElementById('modal-delete-staff-desc').innerHTML = `¿Estás seguro de que deseas eliminar permanentemente al entrenador <strong class="text-slate-100">${escapeHtml(fullName)}</strong> del sistema? Esta acción no se puede deshacer.`;
        toggleModal('modal-delete-staff');
    }

    // AJAX Submission: Create Staff Trainer
    async function submitCreateStaff(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-staff-submit-btn');

        setBtnLoading(submitBtn, true, 'Registrando...');

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
                const container = document.getElementById('trainers-grid-container');
                const emptyMsg = document.getElementById('no_staff_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const tJsonStr = JSON.stringify(t).replace(/'/g, "&#39;");
                const fullName = `${t.first_name || ''} ${t.last_name || ''}`.trim();
                const dni = (t.user && t.user.profile && t.user.profile.dni) ? t.user.profile.dni : 'Sin DNI';
                const photoUrl = t.photo_url 
                    ? `/${t.photo_url}` 
                    : (t.user && t.user.profile && t.user.profile.profile_photo ? `/${t.user.profile.profile_photo}` : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop');

                const card = document.createElement('div');
                card.id = `trainer_card_${t.id}`;
                card.setAttribute('data-trainer-card', '');
                card.setAttribute('data-name', fullName.toLowerCase());
                card.setAttribute('data-dni', dni.toLowerCase());
                card.setAttribute('data-email', (t.email || '').toLowerCase());
                card.setAttribute('data-specialty', (t.specialty || '').toLowerCase());
                card.setAttribute('data-active', '1');
                card.className = 'bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm';

                card.innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="relative shrink-0">
                                    <img src="${photoUrl}" id="trainer_photo_img_${t.id}" class="w-12 h-12 rounded-2xl object-cover border border-slate-700 shadow-md">
                                    <span id="trainer_dot_${t.id}" class="w-3 h-3 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 bg-emerald-500"></span>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-base text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="trainer_name_${t.id}">${escapeHtml(fullName)}</h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-mono font-bold rounded" id="trainer_dni_${t.id}">DNI: ${escapeHtml(dni)}</span>
                                        <span class="text-[10px] text-slate-400 truncate" id="trainer_email_${t.id}">${escapeHtml(t.email)}</span>
                                    </div>
                                </div>
                            </div>

                            <span id="trainer_status_badge_${t.id}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                Activo
                            </span>
                        </div>

                        <div class="p-3 bg-slate-950/70 border border-slate-850 rounded-2xl space-y-2 text-xs font-semibold">
                            <div class="flex items-center justify-between text-slate-300">
                                <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Especialidad:</span>
                                <span class="font-black text-slate-200" id="trainer_specialty_${t.id}">${escapeHtml(t.specialty || 'Entrenador General')}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Certificación:</span>
                                <span class="font-bold text-slate-300 truncate" id="trainer_cert_${t.id}">${escapeHtml(t.certification || 'Sin datos')}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300 border-t border-slate-850/80 pt-2">
                                <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Experiencia:</span>
                                <span class="font-extrabold text-amber-400" id="trainer_exp_${t.id}">${t.experience_years || 0} años de exp.</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                            <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-center">
                                <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Salario Nómina</span>
                                <span class="font-black text-emerald-400 text-sm" id="trainer_salary_${t.id}">$ ${parseFloat(t.salary).toFixed(2)}</span>
                            </div>
                            <div class="p-2.5 bg-blue-500/10 border border-blue-500/20 rounded-xl text-center">
                                <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Cupo Atletas</span>
                                <span class="font-black text-blue-400 text-sm" id="trainer_clients_${t.id}">${t.max_clients || 20} máx.</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold">
                        <span class="text-[10px] text-slate-500 font-bold uppercase">
                            Tel: <strong class="text-slate-300 font-semibold" id="trainer_phone_${t.id}">${escapeHtml(t.phone || 'Sin Teléfono')}</strong>
                        </span>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditModal(${tJsonStr})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Datos del Entrenador">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openToggleModal(${t.id}, '${fullName.replace(/'/g, "\\'")}', 1)" id="trainer_toggle_btn_${t.id}" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Entrenador">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteModal(${t.id}, '${fullName.replace(/'/g, "\\'")}')" class="p-2 bg-slate-950 hover:bg-rose-600 text-slate-400 hover:text-white border border-slate-800 hover:border-rose-600 rounded-xl transition-all shadow-sm" title="Eliminar del Staff">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;

                container.prepend(card);
                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-staff');
                updateCounters();
                renderStaffPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al registrar el entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al registrar el entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Staff Trainer
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
                const card = document.getElementById(`trainer_card_${t.id}`);
                const fullName = `${t.first_name || ''} ${t.last_name || ''}`.trim();
                const dni = (t.user && t.user.profile && t.user.profile.dni) ? t.user.profile.dni : 'Sin DNI';

                if (card) {
                    card.setAttribute('data-name', fullName.toLowerCase());
                    card.setAttribute('data-dni', dni.toLowerCase());
                    card.setAttribute('data-email', (t.email || '').toLowerCase());
                    card.setAttribute('data-specialty', (t.specialty || '').toLowerCase());

                    const nameEl = document.getElementById(`trainer_name_${t.id}`);
                    const dniEl = document.getElementById(`trainer_dni_${t.id}`);
                    const emailEl = document.getElementById(`trainer_email_${t.id}`);
                    const specialtyEl = document.getElementById(`trainer_specialty_${t.id}`);
                    const certEl = document.getElementById(`trainer_cert_${t.id}`);
                    const expEl = document.getElementById(`trainer_exp_${t.id}`);
                    const salaryEl = document.getElementById(`trainer_salary_${t.id}`);
                    const clientsEl = document.getElementById(`trainer_clients_${t.id}`);
                    const phoneEl = document.getElementById(`trainer_phone_${t.id}`);
                    const photoImg = document.getElementById(`trainer_photo_img_${t.id}`);

                    if (nameEl) nameEl.textContent = fullName;
                    if (dniEl) dniEl.textContent = `DNI: ${dni}`;
                    if (emailEl) emailEl.textContent = t.email;
                    if (specialtyEl) specialtyEl.textContent = t.specialty || 'Entrenador General';
                    if (certEl) certEl.textContent = t.certification || 'Sin datos';
                    if (expEl) expEl.textContent = `${t.experience_years || 0} años de exp.`;
                    if (salaryEl) salaryEl.textContent = `$ ${parseFloat(t.salary).toFixed(2)}`;
                    if (clientsEl) clientsEl.textContent = `${t.max_clients || 20} máx.`;
                    if (phoneEl) phoneEl.textContent = t.phone || 'Sin Teléfono';

                    if (photoImg && t.photo_url) {
                        photoImg.src = `/${t.photo_url}`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-edit-staff');
                updateCounters();
                renderStaffPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar el entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar los datos del entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status
    async function submitToggleStaff(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('toggle-staff-submit-btn');

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
                const tId = data.trainer_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const card = document.getElementById(`trainer_card_${tId}`);

                if (card) {
                    card.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        card.classList.remove('opacity-60', 'bg-slate-950/40', 'border-slate-850');
                    } else {
                        card.classList.add('opacity-60', 'bg-slate-950/40', 'border-slate-850');
                    }

                    const badge = document.getElementById(`trainer_status_badge_${tId}`);
                    if (badge) {
                        badge.className = `px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 ${newActiveStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'}`;
                        badge.textContent = newActiveStatus ? 'Activo' : 'Inactivo';
                    }

                    const dot = document.getElementById(`trainer_dot_${tId}`);
                    if (dot) {
                        dot.className = `w-3 h-3 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 ${newActiveStatus ? 'bg-emerald-500' : 'bg-rose-500'}`;
                    }

                    const toggleBtn = document.getElementById(`trainer_toggle_btn_${tId}`);
                    const nameText = document.getElementById(`trainer_name_${tId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openToggleModal(tId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Entrenador' : 'Reactivar Entrenador';
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-toggle-staff');
                updateCounters();
                renderStaffPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar el estado del entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Destroy Staff Trainer
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
                const tId = data.trainer_id;
                const card = document.getElementById(`trainer_card_${tId}`);
                if (card) card.remove();

                toggleModal('modal-delete-staff');
                updateCounters();
                renderStaffPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al eliminar el entrenador.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al eliminar el entrenador.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Update Summary Counters
    function updateCounters() {
        const cards = document.querySelectorAll('[data-trainer-card]');
        let countActive = 0;
        let countInactive = 0;
        let totalPayroll = 0.00;

        cards.forEach(c => {
            const isActive = c.getAttribute('data-active') === '1';
            if (isActive) {
                countActive++;
                const salaryStr = c.querySelector('[id^="trainer_salary_"]')?.textContent || '0';
                const salaryVal = parseFloat(salaryStr.replace(/[^0-9.]/g, '')) || 0;
                totalPayroll += salaryVal;
            } else {
                countInactive++;
            }
        });

        const statTotal = document.getElementById('stat_total');
        const statActive = document.getElementById('stat_active');
        const statInactive = document.getElementById('stat_inactive');
        const statPayroll = document.getElementById('stat_payroll');

        if (statTotal) statTotal.textContent = cards.length;
        if (statActive) statActive.textContent = countActive;
        if (statInactive) statInactive.textContent = countInactive;
        if (statPayroll) statPayroll.textContent = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPayroll);

        const cAll = document.getElementById('count-status-all');
        const cAct = document.getElementById('count-status-active');
        const cInact = document.getElementById('count-status-inactive');

        if (cAll) cAll.textContent = cards.length;
        if (cAct) cAct.textContent = countActive;
        if (cInact) cInact.textContent = countInactive;
    }

    // Staff Filtering & Pagination System (6 cards per page)
    let currentStaffPage = 1;
    let currentStaffStatusFilter = 'all';
    const staffItemsPerPage = 6;

    function setStaffStatusFilter(status) {
        currentStaffStatusFilter = status;

        const tabs = document.querySelectorAll('.staff-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
        });

        const activeTab = document.getElementById('staff-status-filter-' + status);
        if (activeTab) {
            activeTab.className = "staff-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all";
        }

        currentStaffPage = 1;
        renderStaffPage();
    }

    function onStaffFilterChange() {
        currentStaffPage = 1;
        renderStaffPage();
    }

    function renderStaffPage() {
        const searchVal = (document.getElementById('search-staff-input')?.value || '').toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('[data-trainer-card]'));

        const filtered = cards.filter(c => {
            const name = c.getAttribute('data-name') || '';
            const dni = c.getAttribute('data-dni') || '';
            const email = c.getAttribute('data-email') || '';
            const specialty = c.getAttribute('data-specialty') || '';
            const isActive = c.getAttribute('data-active') || '1';

            const matchesStatus = (currentStaffStatusFilter === 'all') || (isActive === currentStaffStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || dni.includes(searchVal) || email.includes(searchVal) || specialty.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / staffItemsPerPage) || 1;

        if (currentStaffPage > totalPages) currentStaffPage = totalPages;
        if (currentStaffPage < 1) currentStaffPage = 1;

        const startIndex = (currentStaffPage - 1) * staffItemsPerPage;
        const endIndex = startIndex + staffItemsPerPage;

        cards.forEach(c => c.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(c => c.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_staff_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && cards.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('staff_pagination_info');
        const pageSpan = document.getElementById('staff_page_number_display');
        const prevBtn = document.getElementById('staff_prev_page_btn');
        const nextBtn = document.getElementById('staff_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay entrenadores para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} entrenadores`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentStaffPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentStaffPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentStaffPage >= totalPages);
    }

    function changeStaffPage(delta) {
        currentStaffPage += delta;
        renderStaffPage();
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
        renderStaffPage();
    });
</script>
@endsection
