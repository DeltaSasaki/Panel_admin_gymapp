@extends('layouts.admin')

@section('title', 'Planes de Rutinas')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Planes de Rutinas</h1>
            <p class="text-slate-400 text-xs mt-1">Crea, edita y asigna programas de entrenamiento de fuerza y acondicionamiento.</p>
        </div>
        <a href="{{ route('rutinas.crear') }}" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4 stroke-[3px]"></i>
            Crear Plan de Rutina
        </a>
    </div>

    <!-- Stats & Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 p-4 rounded-xl border border-slate-800 flex items-center gap-4">
            <div class="p-3 bg-lime-500/10 text-lime-400 rounded-xl">
                <i data-lucide="dumbbell" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block text-slate-400 text-xs font-semibold">Total Plantillas</span>
                <span class="block text-xl font-bold text-slate-100">{{ $rutinas->count() }} Rutinas</span>
            </div>
        </div>
        <div class="bg-slate-900/40 p-4 rounded-xl border border-slate-800 flex items-center gap-4">
            <div class="p-3 bg-purple-500/10 text-purple-400 rounded-xl">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block text-slate-400 text-xs font-semibold">Clientes Entrenando</span>
                <span class="block text-xl font-bold text-slate-100">{{ $activeAssignmentsCount }} Atletas</span>
            </div>
        </div>
        <div class="bg-slate-900/40 p-4 rounded-xl border border-slate-800 flex items-center gap-4">
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block text-slate-400 text-xs font-semibold">Más Popular</span>
                <span class="block text-xl font-bold text-slate-100 truncate max-w-[200px]" title="{{ $popularRoutineName }}">{{ $popularRoutineName }}</span>
            </div>
        </div>
    </div>

    <!-- Routine Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        @forelse($rutinas as $rutina)
            <!-- Routine Card -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 hover:border-slate-700/80 transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        @if($rutina->difficulty === 'advanced')
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-purple-500/10 text-purple-400 rounded-full border border-purple-500/20">Avanzado</span>
                        @elseif($rutina->difficulty === 'intermediate')
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-lime-500/10 text-lime-400 rounded-full border border-lime-500/20">Intermedio</span>
                        @else
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20">Principiante</span>
                        @endif

                        <span class="text-xs text-slate-500 font-semibold flex items-center gap-1">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i> {{ $rutina->active_assignments_count }} activos
                        </span>
                    </div>
                    <h3 class="font-bold text-lg text-slate-100">{{ $rutina->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $rutina->description ?? 'Sin descripción disponible.' }}</p>
                    
                    <div class="mt-4 grid grid-cols-2 gap-3 bg-slate-950/40 p-3 rounded-xl border border-slate-850/60 text-xs">
                        <div>
                            <span class="block text-slate-500 font-medium">Frecuencia</span>
                            <span class="font-bold text-slate-300">{{ $rutina->days_per_week }}x por semana</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Duración</span>
                            <span class="font-bold text-slate-300">{{ $rutina->duration_weeks }} Semanas</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-850/50 flex gap-2">
                    <a href="{{ route('rutinas.ejercicios', $rutina->id) }}" class="flex-1 py-2 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-lg border border-slate-850 hover:border-slate-700 text-slate-300 transition-colors text-center block">
                        Editar Ejercicios
                    </a>
                    <button onclick="openAssignRoutineModal('{{ route('rutinas.assign', $rutina->id) }}', '{{ $rutina->name }}')" class="px-3 py-2 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                        <i data-lucide="link" class="w-3.5 h-3.5"></i> Asignar
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                <i data-lucide="dumbbell" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                <p>No se encontraron rutinas registradas.</p>
            </div>
        @endforelse

    </div>
</div>

<!-- ================= MODAL: ASIGNAR RUTINA ================= -->
<div id="assign-routine-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 animate-scale-up space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Asignar Rutina</h3>
                <span id="modal-routine-name" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button onclick="toggleModal('assign-routine-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="assign-routine-form" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Cliente</label>
                <select name="user_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" disabled selected>Selecciona un atleta...</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->profile->first_name }} {{ $cliente->profile->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('assign-routine-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar Rutina
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openAssignRoutineModal(actionUrl, routineName) {
        document.getElementById('assign-routine-form').action = actionUrl;
        document.getElementById('modal-routine-name').innerText = routineName;
        toggleModal('assign-routine-modal');
    }
</script>
@endsection
