@extends('layouts.admin')

@section('title', 'Editar Ejercicios - ' . $routine->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('rutinas.index') }}" class="hover:text-lime-400 transition-colors">Planes de Rutinas</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-200">Editar Ejercicios</span>
        </div>
        <a href="{{ route('rutinas.index') }}" class="px-3.5 py-1.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-xs font-bold rounded-xl text-slate-300 transition-colors flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Rutinas
        </a>
    </div>

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent p-6 rounded-3xl border border-slate-800/40">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight">{{ $routine->name }}</h1>
        <p class="text-slate-400 text-sm mt-1">{{ $routine->description ?? 'Listado de ejercicios por día.' }}</p>
        <div class="flex items-center gap-4 mt-4 text-xs font-bold text-slate-400">
            <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-lime-500"></i> {{ $routine->duration_weeks }} Semanas</span>
            <span class="flex items-center gap-1.5"><i data-lucide="dumbbell" class="w-4 h-4 text-purple-500"></i> {{ $routine->days_per_week }} Días por Semana</span>
            <span class="flex items-center gap-1.5"><i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i> Nivel: {{ ucfirst($routine->difficulty) }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <!-- Left Days Navigation (Tabs) -->
        <div class="md:col-span-1 space-y-2">
            <h3 class="text-xs uppercase font-extrabold tracking-wider text-slate-500 px-2 mb-3">Días de Entrenamiento</h3>
            <div class="flex flex-row md:flex-col overflow-x-auto md:overflow-x-visible gap-2 pb-2 md:pb-0" id="days-tabs">
                @foreach($routine->days as $index => $day)
                    <button onclick="showDayExercises({{ $day->day_number }})" 
                            id="tab-day-{{ $day->day_number }}" 
                            class="day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border {{ $index === 0 ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30' : 'bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200' }}">
                        <div class="font-bold">Día {{ $day->day_number }}</div>
                        <div class="text-[10px] font-normal text-slate-450 mt-0.5 truncate">{{ $day->day_name }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Exercises List Area -->
        <div class="md:col-span-3">
            @foreach($routine->days as $index => $day)
                <div id="exercises-day-{{ $day->day_number }}" class="day-exercises-content space-y-6 {{ $index === 0 ? '' : 'hidden' }}">
                    
                    <div class="flex items-center justify-between border-b border-slate-880 pb-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">{{ $day->day_name }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Enfoque: {{ $day->focus_area ?? 'General' }}</p>
                        </div>
                        <button onclick="openAddModal({{ $day->id }}, '{{ $day->day_name }}')" class="px-3.5 py-1.5 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i> Añadir Ejercicio
                        </button>
                    </div>

                    <!-- Exercises List -->
                    <div class="space-y-4">
                        @forelse($day->exercises as $ex)
                            <!-- Exercise Block -->
                            <div class="bg-slate-900/30 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700/60 transition-all flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="space-y-3 flex-1">
                                    <!-- Title & Meta -->
                                    <div class="flex items-start gap-3">
                                        <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-400">
                                            <i data-lucide="dumbbell" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-100 text-base">{{ $ex->exercise->name }}</h4>
                                            <div class="flex items-center gap-3 text-xs text-slate-400 mt-0.5 font-semibold">
                                                <span class="text-purple-400">{{ $ex->exercise->muscle_group }}</span>
                                                <span class="text-slate-650">•</span>
                                                <span class="capitalize text-slate-500">{{ __($ex->exercise->difficulty) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sets, Reps, Rest -->
                                    <div class="grid grid-cols-3 gap-4 max-w-sm bg-slate-950/40 p-2.5 rounded-xl border border-slate-850/60 text-xs">
                                        <div>
                                            <span class="block text-[10px] text-slate-500 font-bold uppercase">Series</span>
                                            <span class="font-extrabold text-slate-200">{{ $ex->sets }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] text-slate-500 font-bold uppercase">Reps</span>
                                            <span class="font-extrabold text-lime-400">{{ $ex->reps }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] text-slate-500 font-bold uppercase">Descanso</span>
                                            <span class="font-extrabold text-slate-300">{{ $ex->rest_seconds }}s</span>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    @if($ex->notes)
                                        <div class="text-xs text-slate-450 bg-slate-950/20 p-2 rounded-lg border border-slate-850/30">
                                            <span class="font-bold text-slate-500 uppercase text-[9px] block mb-0.5">Notas del Coach:</span>
                                            {{ $ex->notes }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions Buttons -->
                                <div class="flex sm:flex-col gap-2 self-end sm:self-start">
                                    <button onclick="openEditModal('{{ route('rutinas.update_ejercicio', [$routine->id, $ex->id]) }}', {{ $ex->sets }}, '{{ $ex->reps }}', {{ $ex->rest_seconds }}, '{{ $ex->notes }}', '{{ $ex->exercise->name }}')" 
                                            class="flex-1 sm:flex-none px-3 py-2 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-lg border border-slate-850 hover:border-slate-700 text-slate-300 transition-colors flex items-center justify-center gap-1.5">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Editar
                                    </button>
                                    <form action="{{ route('rutinas.remove_ejercicio', [$routine->id, $ex->id]) }}" method="POST" onsubmit="return confirm('¿Remover este ejercicio del día?')" class="flex-1 sm:flex-none">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-2 bg-slate-950 hover:bg-red-500/10 text-xs font-bold rounded-lg border border-slate-850 hover:border-red-500/25 text-red-400 transition-colors flex items-center justify-center gap-1.5">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Quitar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="bg-slate-950/25 border border-dashed border-slate-850 rounded-2xl py-12 text-center text-slate-500 text-sm">
                                <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                <p class="mb-3">Aún no hay ejercicios programados para este día.</p>
                                <button onclick="openAddModal({{ $day->id }}, '{{ $day->day_name }}')" class="px-3 py-1.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-xs font-bold rounded-lg text-slate-300 transition-colors">
                                    Agregar Primer Ejercicio
                                </button>
                            </div>
                        @endforelse
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>

<!-- ================= MODAL: AÑADIR EJERCICIO ================= -->
<div id="add-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 animate-scale-up space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Añadir Ejercicio</h3>
                <span id="add-modal-day-title" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button onclick="toggleModal('add-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('rutinas.add_ejercicio', $routine->id) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="routine_day_id" id="add-routine-day-id">
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Ejercicio *</label>
                <select name="exercise_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" disabled selected>Selecciona un ejercicio...</option>
                    @foreach($exercises as $exercise)
                        <option value="{{ $exercise->id }}">{{ $exercise->name }} ({{ $exercise->muscle_group }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Series *</label>
                    <input type="number" name="sets" required min="1" value="4" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Reps *</label>
                    <input type="text" name="reps" required placeholder="Ej. 10-12 / RPE 8" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descanso (Segundos)</label>
                <input type="number" name="rest_seconds" min="0" value="90" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Notas / Consejos de técnica</label>
                <textarea name="notes" rows="2" placeholder="Ej. Mantener codo pegado al torso..." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('add-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR EJERCICIO ================= -->
<div id="edit-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 animate-scale-up space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Editar Parámetros</h3>
                <span id="edit-exercise-name" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button onclick="toggleModal('edit-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-form" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Series *</label>
                    <input type="number" name="sets" id="edit-sets" required min="1" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Reps *</label>
                    <input type="text" name="reps" id="edit-reps" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descanso (Segundos)</label>
                <input type="number" name="rest_seconds" id="edit-rest" min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Notas / Consejos de técnica</label>
                <textarea name="notes" id="edit-notes" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('edit-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showDayExercises(dayNumber) {
        const contents = document.querySelectorAll('.day-exercises-content');
        contents.forEach(content => content.classList.add('hidden'));

        const tabs = document.querySelectorAll('.day-tab-btn');
        tabs.forEach(tab => {
            tab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200";
        });

        const activeContent = document.getElementById('exercises-day-' + dayNumber);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        const activeTab = document.getElementById('tab-day-' + dayNumber);
        if (activeTab) {
            activeTab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30";
        }
    }

    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openAddModal(dayId, dayName) {
        document.getElementById('add-routine-day-id').value = dayId;
        document.getElementById('add-modal-day-title').innerText = dayName;
        toggleModal('add-exercise-modal');
    }

    function openEditModal(actionUrl, sets, reps, rest, notes, exerciseName) {
        document.getElementById('edit-form').action = actionUrl;
        document.getElementById('edit-exercise-name').innerText = exerciseName;
        document.getElementById('edit-sets').value = sets;
        document.getElementById('edit-reps').value = reps;
        document.getElementById('edit-rest').value = rest;
        document.getElementById('edit-notes').value = notes;
        toggleModal('edit-exercise-modal');
    }
</script>
@endsection
