@extends('layouts.admin')

@section('title', 'Clases Grupales')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Clases Grupales & Sesiones</h1>
            <p class="text-slate-400 text-xs mt-1">Organiza las disciplinas grupales, programa sesiones específicas y supervisa las reservas.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="openCreateClassModal()" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-200 hover:text-slate-100 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i>
                Nueva Clase
            </button>
            
            <button type="button" onclick="openCreateScheduleModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 rounded-xl text-xs font-bold shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 stroke-[3px]"></i>
                Programar Sesión
            </button>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-400 text-[10px] font-bold uppercase mb-1">Sesiones Programadas</span>
            <h3 class="text-xl font-black text-slate-100"><span id="stat-total-schedules">{{ $schedules->count() }}</span> Sesiones</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Disciplinas Configuradas</span>
            <h3 class="text-xl font-black text-lime-400"><span id="stat-total-classes">{{ $classes->count() }}</span> Clases</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-emerald-400 text-[10px] font-bold uppercase mb-1">Coaches Asignables</span>
            <h3 class="text-xl font-black text-emerald-400"><span>{{ $trainers->count() }}</span> Entrenadores</h3>
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="flex border-b border-slate-800/80 gap-2">
        <button 
            type="button"
            id="tab-btn-horarios"
            onclick="switchClassTab('horarios')" 
            class="class-tab-btn px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold focus:outline-none transition-all border-lime-500 text-lime-400">
            <i data-lucide="calendar-days" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
            Próximas Clases (<span id="count-tab-schedules">{{ $schedules->count() }}</span>)
        </button>
        <button 
            type="button"
            id="tab-btn-clases"
            onclick="switchClassTab('clases')" 
            class="class-tab-btn px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold focus:outline-none transition-all border-transparent text-slate-400 hover:text-slate-200">
            <i data-lucide="award" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
            Clases Disponibles (<span id="count-tab-classes">{{ $classes->count() }}</span>)
        </button>
    </div>

    <!-- ================= PESTAÑA 1: PRÓXIMAS CLASES / HORARIOS ================= -->
    <div id="tab-content-horarios" class="space-y-6">
        <!-- Header Filters Bar for Schedules -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="font-bold text-sm text-slate-200 mr-2 flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Filtros de Horario:
                </h3>

                <!-- Filter by Trainer -->
                <select id="filter-schedule-trainer" onchange="onScheduleFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">Todos los Coaches</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->user->profile->first_name ?? '' }}">{{ $t->user->profile->first_name ?? 'Coach' }} {{ $t->user->profile->last_name ?? '' }}</option>
                    @endforeach
                </select>

                <!-- Filter by Class Discipline -->
                <select id="filter-schedule-class" onchange="onScheduleFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">Todas las Clases</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                    @endforeach
                </select>

                <!-- Filter by Status -->
                <select id="filter-schedule-status" onchange="onScheduleFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">Todos los Estados</option>
                    <option value="scheduled">Programados</option>
                    <option value="completed">Completados</option>
                    <option value="cancelled">Cancelados</option>
                </select>
            </div>

            <!-- Search Bar for Schedules -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-schedule-input" oninput="onScheduleFilterChange()" placeholder="Buscar clase o entrenador..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <!-- Schedules Grid grouped by day -->
        <div id="schedules-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($schedules->groupBy('scheduled_date') as $date => $daySchedules)
                @php
                    $formattedDate = \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd D [de] MMMM');
                @endphp
                <div id="schedule_day_card_{{ $date }}" data-schedule-day-card="{{ $date }}" class="bg-slate-900/40 border border-slate-800/90 rounded-3xl p-5 space-y-4 flex flex-col shadow-xl hover:border-slate-700/60 transition-all">
                    <!-- Day Header -->
                    <div class="border-b border-slate-800/80 pb-3.5 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-2xl bg-lime-500/10 border border-lime-500/20 text-lime-400 shrink-0">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div class="overflow-hidden">
                                <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-100 capitalize truncate">{{ $formattedDate }}</h3>
                                <span class="text-[10px] text-slate-400 font-bold uppercase day-sessions-count mt-0.5 block" id="day_count_{{ $date }}">{{ $daySchedules->count() }} Sesiones</span>
                            </div>
                        </div>
                    </div>

                    <!-- Day Sessions List -->
                    <div class="space-y-3.5 flex-1 day-sessions-list" id="day_sessions_list_{{ $date }}">
                        @foreach($daySchedules as $sched)
                            @php
                                $trainerName = ($sched->trainer && $sched->trainer->user && $sched->trainer->user->profile) 
                                    ? trim($sched->trainer->user->profile->first_name . ' ' . $sched->trainer->user->profile->last_name) 
                                    : 'Coach Desconocido';
                                $photoUrl = ($sched->trainer && $sched->trainer->user && $sched->trainer->user->profile && $sched->trainer->user->profile->profile_photo)
                                    ? asset($sched->trainer->user->profile->profile_photo)
                                    : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
                            @endphp
                            <div id="schedule_item_{{ $sched->id }}" 
                                 data-schedule-item
                                 data-classname="{{ strtolower($sched->gymClass->name ?? '') }}"
                                 data-trainer="{{ strtolower($trainerName) }}"
                                 data-status="{{ $sched->status }}"
                                 class="bg-slate-950/80 border border-slate-800/90 rounded-2xl p-4 space-y-3.5 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all duration-300 group shadow-md">
                                
                                <!-- Card Header & Badge -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1 min-w-0 flex-1">
                                        <h4 class="font-extrabold text-sm text-slate-100 group-hover:text-lime-400 transition-colors leading-snug break-words" id="sched_title_{{ $sched->id }}">{{ $sched->gymClass->name }}</h4>
                                        <div class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-semibold">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-lime-400 shrink-0"></i>
                                            <span id="sched_time_{{ $sched->id }}">{{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}</span>
                                        </div>
                                    </div>
                                    <span id="sched_status_badge_{{ $sched->id }}" class="px-2.5 py-1 text-[9px] font-extrabold rounded-lg uppercase tracking-wider shrink-0 whitespace-nowrap border {{ $sched->status === 'scheduled' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($sched->status === 'completed' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20') }}">
                                        {{ $sched->status === 'scheduled' ? 'Programado' : ($sched->status === 'completed' ? 'Completada' : 'Cancelada') }}
                                    </span>
                                </div>

                                <!-- Coach Info Pill -->
                                <div class="flex items-center gap-3 p-2.5 bg-slate-900/80 border border-slate-800/80 rounded-xl">
                                    <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0 shadow-sm">
                                    <div class="overflow-hidden min-w-0">
                                        <span class="block text-[9px] text-slate-500 uppercase font-black tracking-widest leading-none">Coach Responsable</span>
                                        <span class="block text-xs font-extrabold text-slate-200 truncate mt-1" id="sched_trainer_{{ $sched->id }}">{{ $trainerName }}</span>
                                    </div>
                                </div>

                                <!-- Card Actions -->
                                <div class="flex items-center gap-2 pt-2 border-t border-slate-850/80">
                                    <a href="{{ route('clases.bookings', $sched->id) }}" class="flex-1 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-slate-100 border border-slate-800 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm whitespace-nowrap overflow-hidden">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-lime-400 shrink-0"></i>
                                        <span class="truncate">Reservaciones</span>
                                    </a>
                                    <button type="button" onclick='openEditScheduleModal({{ json_encode($sched) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shrink-0" title="Editar Sesión">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" onclick="openDeleteScheduleModal({{ $sched->id }}, '{{ addslashes($sched->gymClass->name) }}')" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shrink-0" title="Eliminar Sesión">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div id="no_schedules_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl">
                    <i data-lucide="calendar-days" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <p class="font-bold text-slate-400">No hay sesiones programadas para los próximos días</p>
                    <p class="text-xs text-slate-500 mt-1">Programa una nueva clase grupal haciendo clic en "Programar Sesión".</p>
                </div>
            @endforelse

            <div id="no_schedules_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl hidden">
                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                <p class="font-bold text-slate-400 text-sm">No se encontraron sesiones que coincidan con la búsqueda.</p>
            </div>
        </div>

        <!-- Schedule Pagination Controls Footer -->
        <div id="schedule_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="schedule_pagination_info">Mostrando días programados...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="sched_prev_page_btn" onclick="changeSchedulePage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="sched_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="sched_next_page_btn" onclick="changeSchedulePage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- ================= PESTAÑA 2: CLASES DISPONIBLES ================= -->
    <div id="tab-content-clases" class="space-y-6 hidden">
        <!-- Header Filters Bar for Classes -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="font-bold text-sm text-slate-200 mr-2 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-lime-400"></i> Plantillas de Disciplina:
                </h3>

                <!-- Status Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setClassStatusFilter('all')" id="class-status-filter-btn-all" class="class-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todas (<span id="count-class-status-all">{{ $classes->count() }}</span>)
                    </button>
                    <button type="button" onclick="setClassStatusFilter('1')" id="class-status-filter-btn-1" class="class-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activas (<span id="count-class-status-active">{{ $classes->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setClassStatusFilter('0')" id="class-status-filter-btn-0" class="class-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivas (<span id="count-class-status-inactive">{{ $classes->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>
            </div>

            <!-- Search Input for Classes -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-class-input" oninput="onClassFilterChange()" placeholder="Buscar por nombre o descripción..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <!-- Classes Template Cards Grid -->
        <div id="classes-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($classes as $class)
                <div id="class_card_{{ $class->id }}" 
                     data-class-card
                     data-name="{{ strtolower($class->name) }}"
                     data-desc="{{ strtolower($class->description ?? '') }}"
                     data-active="{{ $class->is_active ? 1 : 0 }}"
                     class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 hover:border-slate-700/60 hover:bg-slate-900/60 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl {{ $class->is_active ? '' : 'opacity-60 bg-slate-950/30' }}">
                    <div class="space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <h3 class="font-extrabold text-base text-slate-100 group-hover:text-lime-400 transition-colors" id="class_name_{{ $class->id }}">{{ $class->name }}</h3>
                            <div class="flex items-center gap-2 shrink-0">
                                <span id="class_status_badge_{{ $class->id }}" class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-lg border {{ $class->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                    {{ $class->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                                <span class="px-2.5 py-0.5 text-[10px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-lg" id="class_duration_{{ $class->id }}">
                                    {{ $class->duration_minutes }} min
                                </span>
                            </div>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3" id="class_desc_{{ $class->id }}">{{ $class->description ?? 'Sin descripción disponible.' }}</p>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold text-slate-400">
                        <span class="flex items-center gap-1.5 text-slate-300 font-bold" id="class_capacity_{{ $class->id }}">
                            <i data-lucide="users-2" class="w-4 h-4 text-lime-400"></i>
                            Capacidad: {{ $class->capacity }} atletas
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditClassModal({{ json_encode($class) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Clase">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteClassModal({{ $class->id }}, '{{ addslashes($class->name) }}', {{ $class->is_active ? 1 : 0 }})" 
                                    id="class_toggle_btn_{{ $class->id }}"
                                    class="p-2 {{ $class->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                    title="{{ $class->is_active ? 'Inhabilitar Clase' : 'Reactivar Clase' }}">
                                <i data-lucide="{{ $class->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div id="no_classes_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl">
                    <i data-lucide="award" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <p class="font-bold text-slate-400">No hay clases configuradas</p>
                    <p class="text-xs text-slate-500 mt-1">Crea tu primera clase grupal haciendo clic en "Nueva Clase".</p>
                </div>
            @endforelse

            <div id="no_classes_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl hidden">
                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                <p class="font-bold text-slate-400 text-sm">No se encontraron clases que coincidan con la búsqueda.</p>
            </div>
        </div>

        <!-- Class Pagination Controls Footer -->
        <div id="class_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="class_pagination_info">Mostrando clases...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="class_prev_page_btn" onclick="changeClassPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="class_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="class_next_page_btn" onclick="changeClassPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ================= MODAL: CREAR CLASE ================= -->
<div id="modal-create-class" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest">Crear Nueva Clase Grupal</h3>
            <button type="button" onclick="toggleModal('modal-create-class')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-class-form" action="{{ route('clases.store') }}" method="POST" onsubmit="submitCreateClass(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="create_class_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Clase *</label>
                <input type="text" name="name" id="create_class_name" required placeholder="Ej: Spinning, CrossFit, Yoga..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="create_class_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="create_class_description" rows="3" placeholder="Describe brevemente la dinámica de la clase..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_class_capacity" class="block text-slate-400 uppercase tracking-wider mb-1.5">Capacidad Máxima *</label>
                    <input type="number" name="capacity" id="create_class_capacity" required min="1" placeholder="Ej: 15" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_class_duration" class="block text-slate-400 uppercase tracking-wider mb-1.5">Duración (Minutos) *</label>
                    <input type="number" name="duration_minutes" id="create_class_duration" required min="5" placeholder="Ej: 45" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-class')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-class-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Clase</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR CLASE ================= -->
<div id="modal-edit-class" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest">Editar Clase Grupal</h3>
            <button type="button" onclick="toggleModal('modal-edit-class')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-class-form" action="" method="POST" onsubmit="submitEditClass(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_class_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Clase *</label>
                <input type="text" name="name" id="edit_class_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="edit_class_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="edit_class_description" rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_class_capacity" class="block text-slate-400 uppercase tracking-wider mb-1.5">Capacidad Máxima *</label>
                    <input type="number" name="capacity" id="edit_class_capacity" required min="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_class_duration" class="block text-slate-400 uppercase tracking-wider mb-1.5">Duración (Minutos) *</label>
                    <input type="number" name="duration_minutes" id="edit_class_duration" required min="5" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-class')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-class-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE CLASE ================= -->
<div id="modal-delete-class" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-class-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-class-status-title">Cambiar Estado de la Clase</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-delete-class')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-class-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de esta clase?
        </p>

        <form id="delete-class-form" action="" method="POST" onsubmit="submitDeleteClass(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-class')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-class-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-class-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: PROGRAMAR SESIÓN ================= -->
<div id="modal-create-schedule" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest">Programar Nueva Sesión</h3>
            <button type="button" onclick="toggleModal('modal-create-schedule')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-schedule-form" action="{{ route('clases.store_schedule') }}" method="POST" onsubmit="submitCreateSchedule(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="create_gym_class_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Clase *</label>
                <select name="gym_class_id" id="create_gym_class_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona una clase...</option>
                    @foreach($classes as $class)
                        @if($class->is_active)
                            <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->duration_minutes }} min - Max: {{ $class->capacity }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="create_trainer_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Entrenador Responsable *</label>
                <select name="trainer_id" id="create_trainer_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un entrenador...</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->user->profile->first_name ?? 'Coach' }} {{ $trainer->user->profile->last_name ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Date Selector Buttons -->
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha de la Sesión *</label>
                <input type="date" name="scheduled_date" id="create_scheduled_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span class="text-[10px] text-slate-500 self-center mr-1">Atajos rápidos:</span>
                    <button type="button" onclick="setQuickDate('create_scheduled_date', 0)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">Hoy</button>
                    <button type="button" onclick="setQuickDate('create_scheduled_date', 1)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">Mañana</button>
                    <button type="button" onclick="setQuickDate('create_scheduled_date', 2)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">En 2 días</button>
                    <button type="button" onclick="setQuickDate('create_scheduled_date', 7)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">En 1 semana</button>
                </div>
            </div>

            <!-- Quick Time Slot Presets -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_start_time" class="block text-slate-400 uppercase tracking-wider mb-1.5">Hora de Inicio *</label>
                    <input type="time" name="start_time" id="create_start_time" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_end_time" class="block text-slate-400 uppercase tracking-wider mb-1.5">Hora de Fin *</label>
                    <input type="time" name="end_time" id="create_end_time" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <span class="block text-[10px] text-slate-400 uppercase tracking-wider mb-1.5">Atajos Horarios Frecuentes:</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                    <button type="button" onclick="setQuickTimeSlot('create_start_time', 'create_end_time', '07:00', '08:00')" class="px-2 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-850 text-[10px] font-bold text-slate-300 hover:text-lime-400 rounded-lg transition-colors text-center">07:00 - 08:00</button>
                    <button type="button" onclick="setQuickTimeSlot('create_start_time', 'create_end_time', '09:00', '10:00')" class="px-2 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-850 text-[10px] font-bold text-slate-300 hover:text-lime-400 rounded-lg transition-colors text-center">09:00 - 10:00</button>
                    <button type="button" onclick="setQuickTimeSlot('create_start_time', 'create_end_time', '17:00', '18:00')" class="px-2 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-850 text-[10px] font-bold text-slate-300 hover:text-lime-400 rounded-lg transition-colors text-center">17:00 - 18:00</button>
                    <button type="button" onclick="setQuickTimeSlot('create_start_time', 'create_end_time', '19:00', '20:00')" class="px-2 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-850 text-[10px] font-bold text-slate-300 hover:text-lime-400 rounded-lg transition-colors text-center">19:00 - 20:00</button>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-schedule')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-schedule-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Programar Sesión</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR SESIÓN ================= -->
<div id="modal-edit-schedule" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest">Editar Sesión Programada</h3>
            <button type="button" onclick="toggleModal('modal-edit-schedule')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-schedule-form" action="" method="POST" onsubmit="submitEditSchedule(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_gym_class_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Clase *</label>
                <select name="gym_class_id" id="edit_gym_class_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->duration_minutes }} min - Max: {{ $class->capacity }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="edit_trainer_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Entrenador Responsable *</label>
                <select name="trainer_id" id="edit_trainer_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->user->profile->first_name ?? 'Coach' }} {{ $trainer->user->profile->last_name ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit_scheduled_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha de la Sesión *</label>
                <input type="date" name="scheduled_date" id="edit_scheduled_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span class="text-[10px] text-slate-500 self-center mr-1">Atajos rápidos:</span>
                    <button type="button" onclick="setQuickDate('edit_scheduled_date', 0)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">Hoy</button>
                    <button type="button" onclick="setQuickDate('edit_scheduled_date', 1)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">Mañana</button>
                    <button type="button" onclick="setQuickDate('edit_scheduled_date', 2)" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-[10px] font-bold text-lime-400 rounded-lg transition-colors">En 2 días</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_start_time" class="block text-slate-400 uppercase tracking-wider mb-1.5">Hora de Inicio *</label>
                    <input type="time" name="start_time" id="edit_start_time" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_end_time" class="block text-slate-400 uppercase tracking-wider mb-1.5">Hora de Fin *</label>
                    <input type="time" name="end_time" id="edit_end_time" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="edit_schedule_status" class="block text-slate-400 uppercase tracking-wider mb-1.5">Estado de la Sesión *</label>
                <select name="status" id="edit_schedule_status" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="scheduled">Programado</option>
                    <option value="completed">Completada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-schedule')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-schedule-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ELIMINAR SESIÓN ================= -->
<div id="modal-delete-schedule" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 shrink-0">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Eliminar Sesión Programada</h3>
                    <span class="text-xs text-rose-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Acción irreversible
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-delete-schedule')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-delete-schedule-desc">
            ¿Estás seguro de que deseas eliminar esta sesión del horario?
        </p>

        <form id="delete-schedule-form" action="" method="POST" onsubmit="submitDeleteSchedule(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-schedule')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-schedule-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Sí, Eliminar Sesión</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Quick Date Presets Helper
    function setQuickDate(inputId, daysFromToday) {
        const d = new Date();
        d.setDate(d.getDate() + daysFromToday);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        document.getElementById(inputId).value = `${year}-${month}-${day}`;
    }

    // Quick Time Slot Presets Helper
    function setQuickTimeSlot(startInputId, endInputId, startTime, endTime) {
        document.getElementById(startInputId).value = startTime;
        document.getElementById(endInputId).value = endTime;
    }

    // Toast Alerts System
    function showToast(message, type = 'success') {
        let container = document.getElementById('class-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'class-toast-container';
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

    // Tabs Switcher
    function switchClassTab(tabName) {
        const btnHorarios = document.getElementById('tab-btn-horarios');
        const btnClases = document.getElementById('tab-btn-clases');
        const contentHorarios = document.getElementById('tab-content-horarios');
        const contentClases = document.getElementById('tab-content-clases');

        if (tabName === 'horarios') {
            btnHorarios.className = "class-tab-btn px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold focus:outline-none transition-all border-lime-500 text-lime-400";
            btnClases.className = "class-tab-btn px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold focus:outline-none transition-all border-transparent text-slate-400 hover:text-slate-200";
            contentHorarios.classList.remove('hidden');
            contentClases.classList.add('hidden');
            renderSchedulesPage();
        } else {
            btnClases.className = "class-tab-btn px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold focus:outline-none transition-all border-lime-500 text-lime-400";
            btnHorarios.className = "class-tab-btn px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold focus:outline-none transition-all border-transparent text-slate-400 hover:text-slate-200";
            contentClases.classList.remove('hidden');
            contentHorarios.classList.add('hidden');
            renderClassesPage();
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

    function openCreateClassModal() {
        document.getElementById('create-class-form').reset();
        toggleModal('modal-create-class');
    }

    function openEditClassModal(item) {
        document.getElementById('edit-class-form').action = `/clases/${item.id}`;
        document.getElementById('edit_class_name').value = item.name;
        document.getElementById('edit_class_description').value = item.description || '';
        document.getElementById('edit_class_capacity').value = item.capacity;
        document.getElementById('edit_class_duration').value = item.duration_minutes;

        toggleModal('modal-edit-class');
    }

    function openDeleteClassModal(classId, className, isActive) {
        document.getElementById('delete-class-form').action = `/clases/${classId}`;
        const titleEl = document.getElementById('modal-class-status-title');
        const descEl = document.getElementById('modal-class-status-desc');
        const btnTextEl = document.getElementById('modal-class-status-btn-text');
        const submitBtn = document.getElementById('delete-class-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Clase Grupal';
            descEl.innerHTML = `¿Estás seguro de que deseas marcar como <strong>inactiva</strong> la clase (<strong class="text-slate-100">${escapeHtml(className)}</strong>)? Ya no podrá seleccionarse para nuevos horarios.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Clase Grupal';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> la clase (<strong class="text-slate-100">${escapeHtml(className)}</strong>) para habilitar su programación?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-delete-class');
    }

    function openCreateScheduleModal() {
        document.getElementById('create-schedule-form').reset();
        setQuickDate('create_scheduled_date', 0); // default to Today
        toggleModal('modal-create-schedule');
    }

    function openEditScheduleModal(item) {
        document.getElementById('edit-schedule-form').action = `/clases/horarios/${item.id}`;
        document.getElementById('edit_gym_class_id').value = item.gym_class_id;
        document.getElementById('edit_trainer_id').value = item.trainer_id;
        document.getElementById('edit_scheduled_date').value = item.scheduled_date;
        document.getElementById('edit_start_time').value = item.start_time.substring(0, 5);
        document.getElementById('edit_end_time').value = item.end_time.substring(0, 5);
        document.getElementById('edit_schedule_status').value = item.status;

        toggleModal('modal-edit-schedule');
    }

    function openDeleteScheduleModal(schedId, schedName) {
        document.getElementById('delete-schedule-form').action = `/clases/horarios/${schedId}`;
        document.getElementById('modal-delete-schedule-desc').innerHTML = `¿Estás seguro de que deseas eliminar permanentemente la sesión programada de <strong>${escapeHtml(schedName)}</strong>?`;

        toggleModal('modal-delete-schedule');
    }

    // AJAX Submission: Create Class Template
    async function submitCreateClass(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-class-submit-btn');

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
                const gc = data.gym_class;
                const container = document.getElementById('classes-grid-container');
                const emptyMsg = document.getElementById('no_classes_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const gcJsonStr = JSON.stringify(gc).replace(/'/g, "&#39;");
                const safeName = escapeHtml(gc.name);
                const safeDesc = escapeHtml(gc.description || 'Sin descripción disponible.');

                const card = document.createElement('div');
                card.id = `class_card_${gc.id}`;
                card.setAttribute('data-class-card', '');
                card.setAttribute('data-name', (gc.name || '').toLowerCase());
                card.setAttribute('data-desc', (gc.description || '').toLowerCase());
                card.setAttribute('data-active', '1');
                card.className = 'bg-slate-900/40 border border-slate-800 rounded-3xl p-6 hover:border-slate-700/60 hover:bg-slate-900/60 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl';

                card.innerHTML = `
                    <div class="space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <h3 class="font-extrabold text-base text-slate-100 group-hover:text-lime-400 transition-colors" id="class_name_${gc.id}">${safeName}</h3>
                            <div class="flex items-center gap-2 shrink-0">
                                <span id="class_status_badge_${gc.id}" class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-lg border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                    Activa
                                </span>
                                <span class="px-2.5 py-0.5 text-[10px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-lg" id="class_duration_${gc.id}">
                                    ${gc.duration_minutes} min
                                </span>
                            </div>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3" id="class_desc_${gc.id}">${safeDesc}</p>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold text-slate-400">
                        <span class="flex items-center gap-1.5 text-slate-300 font-bold" id="class_capacity_${gc.id}">
                            <i data-lucide="users-2" class="w-4 h-4 text-lime-400"></i>
                            Capacidad: ${gc.capacity} atletas
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditClassModal(${gcJsonStr})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Clase">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteClassModal(${gc.id}, '${safeName.replace(/'/g, "\\'")}', 1)" id="class_toggle_btn_${gc.id}" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Clase">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;

                container.prepend(card);

                // Append to Select dropdowns in schedule creation/editing and filter header
                const selCreate = document.getElementById('create_gym_class_id');
                const selEdit = document.getElementById('edit_gym_class_id');
                const filterSel = document.getElementById('filter-schedule-class');
                
                [selCreate, selEdit].forEach(sel => {
                    if (sel) {
                        const opt = document.createElement('option');
                        opt.value = gc.id;
                        opt.textContent = `${gc.name} (${gc.duration_minutes} min - Max: ${gc.capacity})`;
                        sel.appendChild(opt);
                    }
                });
                if (filterSel) {
                    const opt = document.createElement('option');
                    opt.value = gc.name;
                    opt.textContent = gc.name;
                    filterSel.appendChild(opt);
                }

                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-class');
                updateCounters();
                renderClassesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al crear la clase.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar crear la clase.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Class Template
    async function submitEditClass(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-class-submit-btn');

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
                const gc = data.gym_class;
                const card = document.getElementById(`class_card_${gc.id}`);

                if (card) {
                    card.setAttribute('data-name', (gc.name || '').toLowerCase());
                    card.setAttribute('data-desc', (gc.description || '').toLowerCase());

                    const nameEl = document.getElementById(`class_name_${gc.id}`);
                    const descEl = document.getElementById(`class_desc_${gc.id}`);
                    const durEl = document.getElementById(`class_duration_${gc.id}`);
                    const capEl = document.getElementById(`class_capacity_${gc.id}`);

                    if (nameEl) nameEl.textContent = gc.name;
                    if (descEl) descEl.textContent = gc.description || 'Sin descripción disponible.';
                    if (durEl) durEl.textContent = `${gc.duration_minutes} min`;
                    if (capEl) capEl.innerHTML = `<i data-lucide="users-2" class="w-4 h-4 text-lime-400"></i> Capacidad: ${gc.capacity} atletas`;
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-edit-class');
                renderClassesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar la clase.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar la clase.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Class Active Status
    async function submitDeleteClass(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-class-submit-btn');

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
                const gcId = data.gym_class_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const card = document.getElementById(`class_card_${gcId}`);

                if (card) {
                    card.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        card.classList.remove('opacity-60', 'bg-slate-950/30');
                    } else {
                        card.classList.add('opacity-60', 'bg-slate-950/30');
                    }

                    const badge = document.getElementById(`class_status_badge_${gcId}`);
                    if (badge) {
                        badge.className = `px-2 py-0.5 text-[9px] font-bold uppercase rounded-lg border ${newActiveStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'}`;
                        badge.textContent = newActiveStatus ? 'Activa' : 'Inactiva';
                    }

                    const toggleBtn = document.getElementById(`class_toggle_btn_${gcId}`);
                    const nameText = document.getElementById(`class_name_${gcId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openDeleteClassModal(gcId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Clase' : 'Reactivar Clase';
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                    }
                }

                if (data.cancelled_schedule_ids && data.cancelled_schedule_ids.length > 0) {
                    data.cancelled_schedule_ids.forEach(schedId => {
                        const item = document.getElementById(`schedule_item_${schedId}`);
                        if (item) {
                            item.setAttribute('data-status', 'cancelled');
                            const badge = document.getElementById(`sched_status_badge_${schedId}`);
                            if (badge) {
                                badge.className = 'px-2 py-0.5 text-[9px] font-bold rounded-lg uppercase shrink-0 tracking-wide bg-rose-500/10 text-rose-400 border border-rose-500/20';
                                badge.textContent = 'Cancelada';
                            }
                        }
                    });
                    renderSchedulesPage();
                }

                if (data.reactivated_schedule_ids && data.reactivated_schedule_ids.length > 0) {
                    data.reactivated_schedule_ids.forEach(schedId => {
                        const item = document.getElementById(`schedule_item_${schedId}`);
                        if (item) {
                            item.setAttribute('data-status', 'scheduled');
                            const badge = document.getElementById(`sched_status_badge_${schedId}`);
                            if (badge) {
                                badge.className = 'px-2 py-0.5 text-[9px] font-bold rounded-lg uppercase shrink-0 tracking-wide bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                                badge.textContent = 'Programado';
                            }
                        }
                    });
                    renderSchedulesPage();
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-delete-class');
                updateCounters();
                renderClassesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado de la clase.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado de la clase.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Create Schedule Session (No page reload)
    async function submitCreateSchedule(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-schedule-submit-btn');

        setBtnLoading(submitBtn, true, 'Programando...');

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
                const sched = data.schedule;
                const schedDate = sched.scheduled_date;
                const emptyMsg = document.getElementById('no_schedules_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const schedJsonStr = JSON.stringify(sched).replace(/'/g, "&#39;");
                const className = sched.gym_class ? escapeHtml(sched.gym_class.name) : 'Clase';
                const trainerProfile = sched.trainer && sched.trainer.user ? sched.trainer.user.profile : null;
                const trainerName = trainerProfile ? escapeHtml(`${trainerProfile.first_name || ''} ${trainerProfile.last_name || ''}`) : 'Coach';
                const photoUrl = (trainerProfile && trainerProfile.profile_photo) ? `/${trainerProfile.profile_photo}` : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
                const timeText = `${sched.start_time.substring(0, 5)} - ${sched.end_time.substring(0, 5)}`;

                let dayCard = document.getElementById(`schedule_day_card_${schedDate}`);

                if (!dayCard) {
                    // Create new Day Card if not exists
                    const gridContainer = document.getElementById('schedules-grid-container');
                    dayCard = document.createElement('div');
                    dayCard.id = `schedule_day_card_${schedDate}`;
                    dayCard.setAttribute('data-schedule-day-card', schedDate);
                    dayCard.className = 'bg-slate-900/40 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col shadow-xl hover:border-slate-700/60 transition-all';

                    // Format Spanish Date label
                    const dParts = schedDate.split('-');
                    const dObj = new Date(dParts[0], dParts[1] - 1, dParts[2]);
                    const daysEs = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    const monthsEs = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    const formattedDateText = `${daysEs[dObj.getDay()]} ${dObj.getDate()} de ${monthsEs[dObj.getMonth()]}`;

                    dayCard.innerHTML = `
                        <div class="border-b border-slate-800/80 pb-3 flex justify-between items-center">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-xl bg-lime-500/10 border border-lime-500/20 text-lime-400">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-100 capitalize">${formattedDateText}</h3>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase day-sessions-count" id="day_count_${schedDate}">1 Sesiones</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3.5 flex-1 day-sessions-list" id="day_sessions_list_${schedDate}"></div>
                    `;

                    gridContainer.prepend(dayCard);
                }

                const sessionsList = document.getElementById(`day_sessions_list_${schedDate}`);

                const itemDiv = document.createElement('div');
                itemDiv.id = `schedule_item_${sched.id}`;
                itemDiv.setAttribute('data-schedule-item', '');
                itemDiv.setAttribute('data-classname', className.toLowerCase());
                itemDiv.setAttribute('data-trainer', trainerName.toLowerCase());
                itemDiv.className = 'bg-slate-950/80 border border-slate-800/90 rounded-2xl p-4 space-y-3.5 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all duration-300 group shadow-md';

                itemDiv.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1 min-w-0 flex-1">
                            <h4 class="font-extrabold text-sm text-slate-100 group-hover:text-lime-400 transition-colors leading-snug break-words" id="sched_title_${sched.id}">${className}</h4>
                            <div class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-semibold">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-lime-400 shrink-0"></i>
                                <span id="sched_time_${sched.id}">${timeText}</span>
                            </div>
                        </div>
                        <span id="sched_status_badge_${sched.id}" class="px-2.5 py-1 text-[9px] font-extrabold rounded-lg uppercase tracking-wider shrink-0 whitespace-nowrap border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                            Programado
                        </span>
                    </div>

                    <div class="flex items-center gap-3 p-2.5 bg-slate-900/80 border border-slate-800/80 rounded-xl">
                        <img src="${photoUrl}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0 shadow-sm">
                        <div class="overflow-hidden min-w-0">
                            <span class="block text-[9px] text-slate-500 uppercase font-black tracking-widest leading-none">Coach Responsable</span>
                            <span class="block text-xs font-extrabold text-slate-200 truncate mt-1" id="sched_trainer_${sched.id}">${trainerName}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-slate-855/80">
                        <a href="/clases/horarios/${sched.id}/reservas" class="flex-1 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-slate-100 border border-slate-800 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-sm whitespace-nowrap overflow-hidden">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-lime-400 shrink-0"></i>
                            <span class="truncate">Reservaciones</span>
                        </a>
                        <button type="button" onclick='openEditScheduleModal(${schedJsonStr})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shrink-0" title="Editar Sesión">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" onclick="openDeleteScheduleModal(${sched.id}, '${className.replace(/'/g, "\\'")}')" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shrink-0" title="Eliminar Sesión">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                `;

                if (sessionsList) sessionsList.prepend(itemDiv);

                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-schedule');
                updateCounters();
                renderSchedulesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al programar la sesión.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar programar la sesión.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Schedule Session
    async function submitEditSchedule(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-schedule-submit-btn');

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
                const sched = data.schedule;
                const titleEl = document.getElementById(`sched_title_${sched.id}`);
                const timeEl = document.getElementById(`sched_time_${sched.id}`);
                const trainerEl = document.getElementById(`sched_trainer_${sched.id}`);
                const statusBadge = document.getElementById(`sched_status_badge_${sched.id}`);

                const className = sched.gym_class ? sched.gym_class.name : '';
                const trainerProfile = sched.trainer && sched.trainer.user ? sched.trainer.user.profile : null;
                const trainerName = trainerProfile ? `${trainerProfile.first_name || ''} ${trainerProfile.last_name || ''}` : 'Coach';

                const item = document.getElementById(`schedule_item_${sched.id}`);
                if (item) {
                    item.setAttribute('data-classname', className.toLowerCase());
                    item.setAttribute('data-trainer', trainerName.toLowerCase());
                    item.setAttribute('data-status', sched.status);
                }

                if (titleEl) titleEl.textContent = className;
                if (timeEl) timeEl.textContent = `${sched.start_time.substring(0,5)} - ${sched.end_time.substring(0,5)}`;
                if (trainerEl) trainerEl.textContent = trainerName;

                if (statusBadge) {
                    let badgeClass = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                    let badgeText = 'Programado';
                    if (sched.status === 'completed') {
                        badgeClass = 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
                        badgeText = 'Completada';
                    } else if (sched.status === 'cancelled') {
                        badgeClass = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
                        badgeText = 'Cancelada';
                    }
                    statusBadge.className = `px-2 py-0.5 text-[9px] font-bold rounded-lg uppercase shrink-0 tracking-wide ${badgeClass}`;
                    statusBadge.textContent = badgeText;
                }

                toggleModal('modal-edit-schedule');
                renderSchedulesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar la sesión.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar la sesión.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Delete Schedule Session
    async function submitDeleteSchedule(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-schedule-submit-btn');

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
                const schedId = data.schedule_id;
                const item = document.getElementById(`schedule_item_${schedId}`);
                if (item) {
                    const dayList = item.parentElement;
                    item.remove();
                    if (dayList && dayList.children.length === 0) {
                        const dayCard = dayList.closest('[id^="schedule_day_card_"]');
                        if (dayCard) dayCard.remove();
                    }
                }

                toggleModal('modal-delete-schedule');
                updateCounters();
                renderSchedulesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al eliminar la sesión.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al eliminar la sesión.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Dynamic Counters Update
    function updateCounters() {
        const schedItems = document.querySelectorAll('[data-schedule-item]');
        const classCards = document.querySelectorAll('[data-class-card]');
        let countActiveClasses = 0;
        let countInactiveClasses = 0;

        classCards.forEach(c => {
            if (c.getAttribute('data-active') === '1') countActiveClasses++;
            else countInactiveClasses++;
        });

        const countTabSchedules = document.getElementById('count-tab-schedules');
        const countTabClasses = document.getElementById('count-tab-classes');
        const countClassAll = document.getElementById('count-class-status-all');
        const countClassActive = document.getElementById('count-class-status-active');
        const countClassInactive = document.getElementById('count-class-status-inactive');
        
        const statSchedules = document.getElementById('stat-total-schedules');
        const statClasses = document.getElementById('stat-total-classes');

        if (countTabSchedules) countTabSchedules.textContent = schedItems.length;
        if (countTabClasses) countTabClasses.textContent = classCards.length;
        if (countClassAll) countClassAll.textContent = classCards.length;
        if (countClassActive) countClassActive.textContent = countActiveClasses;
        if (countClassInactive) countClassInactive.textContent = countInactiveClasses;
        
        if (statSchedules) statSchedules.textContent = schedItems.length;
        if (statClasses) statClasses.textContent = classCards.length;
    }

    // Schedule Tab Filtering & Pagination (4 day cards per page)
    let currentSchedulePage = 1;
    const scheduleItemsPerPage = 4;

    function onScheduleFilterChange() {
        currentSchedulePage = 1;
        renderSchedulesPage();
    }

    function renderSchedulesPage() {
        const searchVal = (document.getElementById('search-schedule-input')?.value || '').toLowerCase().trim();
        const trainerVal = (document.getElementById('filter-schedule-trainer')?.value || '').toLowerCase();
        const classVal = (document.getElementById('filter-schedule-class')?.value || '').toLowerCase();
        const statusVal = document.getElementById('filter-schedule-status')?.value || '';

        const dayCards = Array.from(document.querySelectorAll('[data-schedule-day-card]'));

        let totalVisibleDays = 0;

        dayCards.forEach(dayCard => {
            const items = Array.from(dayCard.querySelectorAll('[data-schedule-item]'));
            let visibleItemsCount = 0;

            items.forEach(item => {
                const className = item.getAttribute('data-classname') || '';
                const trainer = item.getAttribute('data-trainer') || '';
                const status = item.getAttribute('data-status') || '';

                const matchesSearch = !searchVal || className.includes(searchVal) || trainer.includes(searchVal);
                const matchesTrainer = !trainerVal || trainer.includes(trainerVal);
                const matchesClass = !classVal || className.includes(classVal);
                const matchesStatus = !statusVal || status === statusVal;

                if (matchesSearch && matchesTrainer && matchesClass && matchesStatus) {
                    item.classList.remove('hidden');
                    visibleItemsCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (visibleItemsCount > 0) {
                dayCard.setAttribute('data-day-visible', 'true');
                totalVisibleDays++;
            } else {
                dayCard.setAttribute('data-day-visible', 'false');
            }
        });

        const visibleDayCards = dayCards.filter(d => d.getAttribute('data-day-visible') === 'true');
        const totalPages = Math.ceil(totalVisibleDays / scheduleItemsPerPage) || 1;

        if (currentSchedulePage > totalPages) currentSchedulePage = totalPages;
        if (currentSchedulePage < 1) currentSchedulePage = 1;

        const startIndex = (currentSchedulePage - 1) * scheduleItemsPerPage;
        const endIndex = startIndex + scheduleItemsPerPage;

        dayCards.forEach(d => d.classList.add('hidden'));

        visibleDayCards.slice(startIndex, endIndex).forEach(d => d.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_schedules_search_row');
        if (noSearchRow) {
            if (totalVisibleDays === 0 && dayCards.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('schedule_pagination_info');
        const pageSpan = document.getElementById('sched_page_number_display');
        const prevBtn = document.getElementById('sched_prev_page_btn');
        const nextBtn = document.getElementById('sched_next_page_btn');

        if (infoSpan) {
            if (totalVisibleDays === 0) {
                infoSpan.textContent = "No hay sesiones que mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalVisibleDays);
                infoSpan.textContent = `Mostrando días ${fromNum}-${toNum} de ${totalVisibleDays} días programados`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentSchedulePage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentSchedulePage <= 1);
        if (nextBtn) nextBtn.disabled = (currentSchedulePage >= totalPages);
    }

    function changeSchedulePage(delta) {
        currentSchedulePage += delta;
        renderSchedulesPage();
    }

    // Classes Tab Filtering & Pagination (6 cards per page)
    let currentClassPage = 1;
    let currentClassStatusFilter = 'all';
    const classItemsPerPage = 6;

    function setClassStatusFilter(status) {
        currentClassStatusFilter = status;

        const tabs = document.querySelectorAll('.class-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "class-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('class-status-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "class-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentClassPage = 1;
        renderClassesPage();
    }

    function onClassFilterChange() {
        currentClassPage = 1;
        renderClassesPage();
    }

    function renderClassesPage() {
        const searchVal = (document.getElementById('search-class-input')?.value || '').toLowerCase().trim();
        const classCards = Array.from(document.querySelectorAll('[data-class-card]'));

        const filtered = classCards.filter(c => {
            const name = c.getAttribute('data-name') || '';
            const desc = c.getAttribute('data-desc') || '';
            const isActive = c.getAttribute('data-active') || '1';

            const matchesStatus = (currentClassStatusFilter === 'all') || (isActive === currentClassStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || desc.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / classItemsPerPage) || 1;

        if (currentClassPage > totalPages) currentClassPage = totalPages;
        if (currentClassPage < 1) currentClassPage = 1;

        const startIndex = (currentClassPage - 1) * classItemsPerPage;
        const endIndex = startIndex + classItemsPerPage;

        classCards.forEach(c => c.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(c => c.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_classes_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && classCards.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('class_pagination_info');
        const pageSpan = document.getElementById('class_page_number_display');
        const prevBtn = document.getElementById('class_prev_page_btn');
        const nextBtn = document.getElementById('class_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay clases para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} clases`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentClassPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentClassPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentClassPage >= totalPages);
    }

    function changeClassPage(delta) {
        currentClassPage += delta;
        renderClassesPage();
    }

    // Flash messages on load
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
        renderSchedulesPage();
        renderClassesPage();
    });
</script>
@endsection
