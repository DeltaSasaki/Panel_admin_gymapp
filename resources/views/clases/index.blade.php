@extends('layouts.admin')

@section('title', 'Clases Grupales')

@section('content')
<div class="space-y-8 animate-fade-in" x-data="{ activeTab: 'horarios' }">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Clases Grupales & Sesiones</h1>
            <p class="text-slate-400 text-xs mt-1">Organiza las disciplinas grupales, programa sesiones específicas y supervisa las reservas.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            <button onclick="openModal('modal-create-class')" class="px-4 py-2 bg-slate-900 border border-slate-800 text-slate-200 hover:text-slate-100 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i>
                Nueva Clase
            </button>
            
            <button onclick="openModal('modal-create-schedule')" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 rounded-xl text-xs font-bold shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 stroke-[3px]"></i>
                Programar Sesión
            </button>
        </div>
    </div>

    <!-- Alerts -->
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
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-900">
        <button 
            @click="activeTab = 'horarios'" 
            :class="activeTab === 'horarios' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Próximas Clases
        </button>
        <button 
            @click="activeTab = 'clases'" 
            :class="activeTab === 'clases' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Clases Disponibles
        </button>
    </div>

    <!-- Pestaña 1: Horarios Semanales -->
    <div x-show="activeTab === 'horarios'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($schedules->groupBy('scheduled_date') as $date => $daySchedules)
                @php
                    $formattedDate = \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd D [de] MMMM');
                @endphp
                <div class="bg-slate-900/20 border border-slate-900/60 rounded-2xl p-4 space-y-4 flex flex-col">
                    <div class="border-b border-slate-900/80 pb-2.5">
                        <h3 class="font-extrabold text-xs uppercase tracking-widest text-lime-400 capitalize">{{ $formattedDate }}</h3>
                        <span class="text-[9px] text-slate-500 font-bold uppercase">{{ $daySchedules->count() }} Sesiones</span>
                    </div>

                    <div class="space-y-3 flex-1">
                        @foreach($daySchedules as $sched)
                            <div class="bg-slate-900/40 border border-slate-800/80 rounded-xl p-3.5 space-y-3 hover:border-slate-700/50 hover:bg-slate-900/70 transition-all group">
                                <div>
                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="font-black text-xs text-slate-200 group-hover:text-lime-400 transition-colors truncate">{{ $sched->gymClass->name }}</h4>
                                        <span class="px-1.5 py-0.5 text-[8px] font-bold rounded uppercase shrink-0 {{ $sched->status === 'scheduled' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500' }}">
                                            {{ $sched->status === 'scheduled' ? 'Activo' : $sched->status }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1 font-semibold">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-500"></i>
                                        {{ \Carbon\Carbon::parse($sched->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sched->end_time)->format('H:i') }}
                                    </p>
                                    <p class="text-[10px] text-slate-500 mt-0.5 truncate font-semibold">
                                        Coach: {{ $sched->trainer->user->profile->first_name ?? 'Entrenador' }}
                                    </p>
                                </div>

                                <a href="{{ route('clases.bookings', $sched->id) }}" class="w-full py-1.5 bg-slate-950 hover:bg-slate-900 text-slate-300 hover:text-slate-100 border border-slate-850 hover:border-slate-800 text-[10px] font-bold rounded-lg transition-all flex items-center justify-center gap-1.5">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    Reservaciones
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-slate-500 bg-slate-900/10 border border-slate-900/60 rounded-2xl">
                    <i data-lucide="calendar-days" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <p class="font-bold text-slate-400">No hay sesiones programadas para los próximos días</p>
                    <p class="text-xs text-slate-500 mt-1">Programa una nueva clase grupal haciendo clic en "Programar Sesión".</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pestaña 2: Clases Disponibles -->
    <div x-show="activeTab === 'clases'">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($classes as $class)
                <div class="bg-slate-900/30 border border-slate-850 rounded-2xl p-5 hover:border-slate-850 hover:bg-slate-900/60 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group">
                    <div class="space-y-2">
                        <div class="flex justify-between items-start">
                            <h3 class="font-extrabold text-sm text-slate-100 group-hover:text-lime-400 transition-colors">{{ $class->name }}</h3>
                            <span class="px-2 py-0.5 text-[9px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded">
                                {{ $class->duration_minutes }} min
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3">{{ $class->description ?? 'Sin descripción disponible.' }}</p>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-850/60 pt-4 text-xs font-semibold text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="users-2" class="w-4 h-4 text-slate-500"></i>
                            Capacidad: {{ $class->capacity }}
                        </span>
                        @if(session('superadmin_gym_id') === 'all')
                            <span class="text-[9px] bg-slate-800 px-2 py-0.5 rounded text-slate-400 font-bold uppercase">
                                {{ $class->gym->name }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-slate-500 bg-slate-900/10 border border-slate-900/60 rounded-2xl">
                    <i data-lucide="award" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <p class="font-bold text-slate-400">No hay clases configuradas</p>
                    <p class="text-xs text-slate-500 mt-1">Crea tu primera clase grupal haciendo clic en "Nueva Clase".</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Modal: Crear Clase -->
<div id="modal-create-class" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden animate-scale-up">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-black text-sm text-slate-100 uppercase tracking-widest">Crear Nueva Clase Grupal</h3>
            <button onclick="closeModal('modal-create-class')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clases.store') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Clase</label>
                <input type="text" name="name" id="name" required placeholder="Ej: Spinning, CrossFit, Yoga..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="description" rows="3" placeholder="Describe brevemente la dinámica de la clase..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="capacity" class="block text-slate-400 uppercase tracking-wider mb-1.5">Capacidad Máxima</label>
                    <input type="number" name="capacity" id="capacity" required min="1" placeholder="Ej: 15" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="duration_minutes" class="block text-slate-400 uppercase tracking-wider mb-1.5">Duración (Minutos)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" required min="5" placeholder="Ej: 45" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-create-class')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Clase</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Programar Horario -->
<div id="modal-create-schedule" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden animate-scale-up">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-black text-sm text-slate-100 uppercase tracking-widest">Programar Nueva Sesión</h3>
            <button onclick="closeModal('modal-create-schedule')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clases.store_schedule') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="gym_class_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Clase</label>
                <select name="gym_class_id" id="gym_class_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona una clase...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->duration_minutes }} min - Max: {{ $class->capacity }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="trainer_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Entrenador Responsable</label>
                <select name="trainer_id" id="trainer_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un entrenador...</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->user->profile->first_name ?? 'Coach' }} {{ $trainer->user->profile->last_name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="scheduled_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha de la Sesión</label>
                <input type="date" name="scheduled_date" id="scheduled_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-slate-400 uppercase tracking-wider mb-1.5">Hora de Inicio</label>
                    <input type="time" name="start_time" id="start_time" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="end_time" class="block text-slate-400 uppercase tracking-wider mb-1.5">Hora de Fin</label>
                    <input type="time" name="end_time" id="end_time" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-create-schedule')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Programar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
</script>
@endsection
