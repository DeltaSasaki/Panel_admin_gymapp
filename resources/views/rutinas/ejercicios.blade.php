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

    @php
        $difficultyLabels = [
            'beginner' => 'Principiante',
            'intermediate' => 'Intermedio',
            'advanced' => 'Avanzado',
        ];
        $goalLabels = [
            'gain_muscle' => 'Hipertrofia / Ganar Músculo',
            'lose_weight' => 'Pérdida de Grasa',
            'gain_weight' => 'Aumento de Peso',
            'maintain' => 'Mantenimiento',
            'improve_endurance' => 'Resistencia Aeróbica',
            'improve_flexibility' => 'Flexibilidad y Movilidad',
        ];
        $bmiLabels = [
            'all' => 'Todos los IMC',
            'underweight' => 'Bajo Peso',
            'normal' => 'IMC Normal',
            'overweight' => 'Sobrepeso',
            'obese' => 'Obesidad',
        ];
    @endphp

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent p-6 rounded-3xl border border-slate-800/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 id="routine-header-name" class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight">{{ $routine->name }}</h1>
                <span id="routine-header-status">
                    @if($routine->is_active)
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activa</span>
                    @else
                        <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactiva</span>
                    @endif
                </span>
            </div>
            <p id="routine-header-desc" class="text-slate-400 text-sm mt-1">{{ $routine->description ?? 'Listado de ejercicios por día.' }}</p>
            <div class="flex items-center gap-4 mt-4 text-xs font-bold text-slate-400 flex-wrap">
                <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-lime-500"></i> <span id="routine-header-weeks">{{ $routine->duration_weeks }}</span> Semanas</span>
                <span class="flex items-center gap-1.5"><i data-lucide="dumbbell" class="w-4 h-4 text-purple-500"></i> <span id="routine-header-days">{{ $routine->days_per_week }}</span> Días por Semana</span>
                <span class="flex items-center gap-1.5"><i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i> Nivel: <span id="routine-header-difficulty">{{ $difficultyLabels[$routine->difficulty] ?? $routine->difficulty }}</span></span>
                <span class="flex items-center gap-1.5"><i data-lucide="target" class="w-4 h-4 text-cyan-400"></i> Objetivo: <span id="routine-header-goal">{{ $goalLabels[$routine->goal_type] ?? $routine->goal_type }}</span></span>
                <span class="flex items-center gap-1.5"><i data-lucide="activity" class="w-4 h-4 text-rose-400"></i> IMC: <span id="routine-header-bmi">{{ $bmiLabels[$routine->bmi_category] ?? $routine->bmi_category }}</span></span>
                <span class="flex items-center gap-1.5"><i data-lucide="building-2" class="w-4 h-4 text-sky-400"></i> Equipamiento: <span id="routine-header-gym">{{ $routine->requires_gym ? 'Si' : 'No' }}</span></span>
            </div>
        </div>

        <button type="button" onclick="openEditRoutineModal()" class="px-4 py-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 shrink-0 self-start md:self-center">
            <i data-lucide="edit-3" class="w-4 h-4"></i>
            <span>Editar Información</span>
        </button>
    </div>

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
                        @forelse($day->exercises as $index => $ex)
                            <!-- Exercise Block -->
                            <div id="exercise_card_{{ $ex->id }}" class="group bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700/80 hover:bg-slate-900/60 transition-all duration-300 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative overflow-hidden">
                                
                                <div class="space-y-3.5 flex-1 min-w-0">
                                    <!-- Header: Image, Title, Order & Badges -->
                                    <div class="flex items-start gap-4">
                                        <div class="relative shrink-0">
                                            <img src="{{ $ex->exercise->image_url ? asset($ex->exercise->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}" 
                                                 alt="{{ $ex->exercise->name }}" 
                                                 class="w-14 h-14 rounded-2xl object-cover border border-slate-800 shadow-md group-hover:scale-105 transition-transform duration-300">
                                            <span class="absolute -top-1.5 -left-1.5 px-2 py-0.5 bg-slate-950/90 text-lime-400 border border-lime-500/30 text-[9px] font-black font-mono rounded-lg shadow">
                                                #{{ $index + 1 }}
                                            </span>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-extrabold text-slate-100 text-base group-hover:text-lime-400 transition-colors truncate">{{ $ex->exercise->name }}</h4>
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] font-semibold mt-1 flex-wrap">
                                                <span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-bold uppercase tracking-wider">
                                                    {{ $ex->exercise->muscle_group }}
                                                </span>
                                                <span class="px-2 py-0.5 bg-slate-950/60 text-slate-400 border border-slate-850 rounded-md font-bold uppercase tracking-wider">
                                                    {{ __($ex->exercise->difficulty) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sets, Reps, Rest Pills Grid -->
                                    <div class="grid grid-cols-3 gap-3 max-w-md bg-slate-950/50 p-3 rounded-2xl border border-slate-850/80 text-xs">
                                        <div class="flex items-center gap-2">
                                            <div class="p-1.5 bg-slate-900 rounded-lg text-slate-400 border border-slate-800 shrink-0">
                                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <div>
                                                <span class="block text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Series</span>
                                                <span class="font-black text-slate-100 text-sm leading-tight">{{ $ex->sets }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="p-1.5 bg-lime-500/10 rounded-lg text-lime-400 border border-lime-500/20 shrink-0">
                                                <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <div>
                                                <span class="block text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Repeticiones</span>
                                                <span class="font-black text-lime-400 text-sm leading-tight">{{ $ex->reps }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="p-1.5 bg-emerald-500/10 rounded-lg text-emerald-400 border border-emerald-500/20 shrink-0">
                                                <i data-lucide="timer" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <div>
                                                <span class="block text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Descanso</span>
                                                <span class="font-bold text-slate-200 text-sm leading-tight">{{ $ex->rest_seconds }}s</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Coach Notes Block -->
                                    @if($ex->notes)
                                        <div class="text-xs text-slate-300 bg-slate-950/50 p-3 rounded-2xl border-l-2 border-lime-500/60 border-y border-r border-slate-850/60 flex items-start gap-2.5">
                                            <i data-lucide="message-square" class="w-4 h-4 text-lime-400 shrink-0 mt-0.5"></i>
                                            <div class="leading-relaxed">
                                                <span class="font-extrabold text-slate-400 uppercase text-[9px] block tracking-wider mb-0.5">Indicación del Coach:</span>
                                                {{ $ex->notes }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Actions Buttons Side Column -->
                                <div class="flex lg:flex-col gap-2 shrink-0 self-stretch justify-center pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-850/60">
                                    <button onclick="openEditModal('{{ route('rutinas.update_ejercicio', [$routine->id, $ex->id]) }}', {{ $ex->sets }}, '{{ addslashes($ex->reps) }}', {{ $ex->rest_seconds }}, '{{ addslashes($ex->notes ?? '') }}', '{{ addslashes($ex->exercise->name) }}', '{{ $ex->exercise->image_url ? asset($ex->exercise->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}')" 
                                            class="flex-1 lg:flex-none px-4 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 hover:border-slate-700 text-slate-200 transition-all flex items-center justify-center gap-2 shadow-sm active:scale-95">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Editar</span>
                                    </button>
                                    <button type="button" onclick="openDeleteModal('{{ route('rutinas.remove_ejercicio', [$routine->id, $ex->id]) }}', '{{ addslashes($ex->exercise->name) }}', {{ $ex->id }})" 
                                            class="flex-1 lg:flex-none px-4 py-2.5 bg-slate-950 hover:bg-rose-500/10 text-xs font-bold rounded-xl border border-slate-850 hover:border-rose-500/30 text-rose-400 transition-all flex items-center justify-center gap-2 shadow-sm active:scale-95">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Quitar</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="bg-slate-900/20 border border-dashed border-slate-800/80 rounded-3xl py-12 px-6 text-center text-slate-500">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 mb-4 shadow-inner">
                                    <i data-lucide="dumbbell" class="w-7 h-7"></i>
                                </div>
                                <h4 class="font-extrabold text-slate-300 text-sm mb-1">Día sin ejercicios asignados</h4>
                                <p class="text-xs text-slate-500 mb-4 max-w-sm mx-auto">Añade los ejercicios que formarán parte de la rutina para este día de entrenamiento.</p>
                                <button onclick="openAddModal({{ $day->id }}, '{{ addslashes($day->day_name) }}')" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all inline-flex items-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
                                    <span>Agregar Primer Ejercicio</span>
                                </button>
                            </div>
                        @endforelse
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>

<!-- ================= MODAL: EDITAR INFORMACIÓN DE RUTINA ================= -->
<div id="edit-routine-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Editar Información de Rutina</h3>
                <span class="text-xs text-slate-400">Modifica los parámetros generales de la plantilla</span>
            </div>
            <button type="button" onclick="toggleModal('edit-routine-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-routine-form" action="{{ route('rutinas.update_info', $routine->id) }}" method="POST" onsubmit="submitEditRoutine(event)" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre de la Rutina *</label>
                <input type="text" name="name" id="routine_edit_name" required value="{{ $routine->name }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción</label>
                <textarea name="description" id="routine_edit_description" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">{{ $routine->description }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Objetivo Principal *</label>
                    <select name="goal_type" id="routine_edit_goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="gain_muscle" {{ $routine->goal_type === 'gain_muscle' ? 'selected' : '' }}>Hipertrofia / Ganar Músculo</option>
                        <option value="lose_weight" {{ $routine->goal_type === 'lose_weight' ? 'selected' : '' }}>Pérdida de Grasa</option>
                        <option value="gain_weight" {{ $routine->goal_type === 'gain_weight' ? 'selected' : '' }}>Aumento de Peso</option>
                        <option value="maintain" {{ $routine->goal_type === 'maintain' ? 'selected' : '' }}>Mantenimiento</option>
                        <option value="improve_endurance" {{ $routine->goal_type === 'improve_endurance' ? 'selected' : '' }}>Resistencia Aeróbica</option>
                        <option value="improve_flexibility" {{ $routine->goal_type === 'improve_flexibility' ? 'selected' : '' }}>Flexibilidad y Movilidad</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nivel de Dificultad *</label>
                    <select name="difficulty" id="routine_edit_difficulty" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="beginner" {{ $routine->difficulty === 'beginner' ? 'selected' : '' }}>Principiante</option>
                        <option value="intermediate" {{ $routine->difficulty === 'intermediate' ? 'selected' : '' }}>Intermedio</option>
                        <option value="advanced" {{ $routine->difficulty === 'advanced' ? 'selected' : '' }}>Avanzado</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Duración (Semanas) *</label>
                    <input type="number" name="duration_weeks" id="routine_edit_duration_weeks" min="1" required value="{{ $routine->duration_weeks }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Días por Semana *</label>
                    <input type="number" name="days_per_week" id="routine_edit_days_per_week" min="1" max="7" required value="{{ $routine->days_per_week }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Categoría IMC Recomendada *</label>
                <select name="bmi_category" id="routine_edit_bmi_category" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all" {{ $routine->bmi_category === 'all' ? 'selected' : '' }}>Para todos los IMC</option>
                    <option value="underweight" {{ $routine->bmi_category === 'underweight' ? 'selected' : '' }}>Bajo Peso</option>
                    <option value="normal" {{ $routine->bmi_category === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="overweight" {{ $routine->bmi_category === 'overweight' ? 'selected' : '' }}>Sobrepeso</option>
                    <option value="obese" {{ $routine->bmi_category === 'obese' ? 'selected' : '' }}>Obesidad</option>
                </select>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="requires_gym" id="routine_edit_requires_gym" value="1" {{ $routine->requires_gym ? 'checked' : '' }} class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500">
                    <label for="routine_edit_requires_gym" class="text-xs text-slate-300 font-medium cursor-pointer">Requiere Equipamiento</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="routine_edit_is_active" value="1" {{ $routine->is_active ? 'checked' : '' }} class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500">
                    <label for="routine_edit_is_active" class="text-xs text-slate-300 font-medium cursor-pointer">Rutina Activa</label>
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-routine-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-routine-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: AÑADIR EJERCICIO ================= -->
<div id="add-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Añadir Ejercicio</h3>
                <span id="add-modal-day-title" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button type="button" onclick="toggleModal('add-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="add-exercise-form" action="{{ route('rutinas.add_ejercicio', $routine->id) }}" method="POST" onsubmit="submitAddExercise(event)" class="space-y-4">
            @csrf
            <input type="hidden" name="routine_day_id" id="add-routine-day-id">
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Ejercicio *</label>
                <select name="exercise_id" id="select-add-exercise" onchange="updateAddExercisePreview()" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un ejercicio...</option>
                    @foreach($exercises as $exercise)
                        <option value="{{ $exercise->id }}" data-image="{{ $exercise->image_url ? asset($exercise->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}">{{ $exercise->name }} ({{ $exercise->muscle_group }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mt-2 hidden text-center" id="add-exercise-preview-container">
                <img src="" id="add-exercise-preview-img" class="w-full h-32 object-cover rounded-2xl border border-slate-800">
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

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('add-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="add-exercise-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Ejercicio
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR EJERCICIO ================= -->
<div id="edit-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Editar Parámetros</h3>
                <span id="edit-exercise-name" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button type="button" onclick="toggleModal('edit-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="text-center hidden" id="edit-exercise-preview-container">
            <img src="" id="edit-exercise-preview-img" class="w-full h-32 object-cover rounded-2xl border border-slate-800">
        </div>

        <form id="edit-form" method="POST" onsubmit="submitEditExercise(event)" class="space-y-4">
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

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-exercise-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: VERIFICAR ELIMINACIÓN ================= -->
<div id="delete-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 shadow-lg shadow-rose-500/10 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Remover Ejercicio</h3>
                    <span class="text-xs text-rose-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Target Exercise Preview Box -->
        <div class="bg-slate-950/70 border border-slate-850 p-4 rounded-2xl flex items-center gap-3.5 shadow-inner">
            <div class="p-2.5 bg-rose-500/10 rounded-xl border border-rose-500/20 text-rose-400 shrink-0">
                <i data-lucide="dumbbell" class="w-5 h-5"></i>
            </div>
            <div class="overflow-hidden min-w-0">
                <span class="block text-[9px] text-slate-500 uppercase font-extrabold tracking-wider">Ejercicio a quitar:</span>
                <h4 id="delete-exercise-name-text" class="font-extrabold text-slate-100 text-sm truncate leading-tight"></h4>
            </div>
        </div>

        <p class="text-xs text-slate-400 leading-relaxed">
            ¿Estás seguro de que deseas quitar este ejercicio de la rutina? Se eliminarán los parámetros de series y repeticiones configurados para este día.
        </p>

        <form id="delete-exercise-form" method="POST" onsubmit="submitDeleteExercise(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            <input type="hidden" id="delete-exercise-target-id" value="">
            <button type="button" onclick="toggleModal('delete-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-exercise-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-rose-600/20 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Sí, Quitar</span>
            </button>
        </form>
    </div>
</div>

<script>
    let currentRoutineData = @json($routine);

    function translateDifficulty(d) {
        const map = { 'beginner': 'Principiante', 'intermediate': 'Intermedio', 'advanced': 'Avanzado' };
        return map[d] || d;
    }

    function translateGoal(g) {
        const map = {
            'gain_muscle': 'Hipertrofia / Ganar Músculo',
            'lose_weight': 'Pérdida de Grasa',
            'gain_weight': 'Aumento de Peso',
            'maintain': 'Mantenimiento',
            'improve_endurance': 'Resistencia Aeróbica',
            'improve_flexibility': 'Flexibilidad y Movilidad'
        };
        return map[g] || g;
    }

    function translateBmi(b) {
        const map = {
            'all': 'Todos los IMC',
            'underweight': 'Bajo Peso',
            'normal': 'IMC Normal',
            'overweight': 'Sobrepeso',
            'obese': 'Obesidad'
        };
        return map[b] || b;
    }

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

    function openAddModal(dayId, dayName) {
        document.getElementById('add-routine-day-id').value = dayId;
        document.getElementById('add-modal-day-title').innerText = dayName;
        document.getElementById('add-exercise-form').reset();
        updateAddExercisePreview();
        toggleModal('add-exercise-modal');
    }

    function openEditModal(actionUrl, sets, reps, rest, notes, exerciseName, imageUrl) {
        document.getElementById('edit-form').action = actionUrl;
        document.getElementById('edit-exercise-name').innerText = exerciseName;
        document.getElementById('edit-sets').value = sets;
        document.getElementById('edit-reps').value = reps;
        document.getElementById('edit-rest').value = rest;
        document.getElementById('edit-notes').value = notes;
        
        const previewImg = document.getElementById('edit-exercise-preview-img');
        if (imageUrl) {
            previewImg.src = imageUrl;
            document.getElementById('edit-exercise-preview-container').classList.remove('hidden');
        } else {
            document.getElementById('edit-exercise-preview-container').classList.add('hidden');
        }
        
        toggleModal('edit-exercise-modal');
    }

    function openDeleteModal(actionUrl, exerciseName, elementId) {
        document.getElementById('delete-exercise-form').action = actionUrl;
        document.getElementById('delete-exercise-name-text').innerText = exerciseName;
        document.getElementById('delete-exercise-target-id').value = elementId;
        toggleModal('delete-exercise-modal');
    }

    function updateAddExercisePreview() {
        const select = document.getElementById('select-add-exercise');
        const container = document.getElementById('add-exercise-preview-container');
        const img = document.getElementById('add-exercise-preview-img');
        
        if (select && select.selectedIndex > 0) {
            const option = select.options[select.selectedIndex];
            const imgUrl = option.getAttribute('data-image');
            if (imgUrl) {
                img.src = imgUrl;
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        } else if (container) {
            container.classList.add('hidden');
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

    function openEditRoutineModal() {
        const r = currentRoutineData;
        document.getElementById('routine_edit_name').value = r.name;
        document.getElementById('routine_edit_description').value = r.description || '';
        document.getElementById('routine_edit_goal_type').value = r.goal_type;
        document.getElementById('routine_edit_difficulty').value = r.difficulty;
        document.getElementById('routine_edit_duration_weeks').value = r.duration_weeks;
        document.getElementById('routine_edit_days_per_week').value = r.days_per_week;
        document.getElementById('routine_edit_bmi_category').value = r.bmi_category;
        document.getElementById('routine_edit_requires_gym').checked = !!r.requires_gym;
        document.getElementById('routine_edit_is_active').checked = !!r.is_active;

        toggleModal('edit-routine-modal');
    }

    function syncRoutineDaysDOM(days) {
        const daysTabsContainer = document.getElementById('days-tabs');
        const exercisesListContainer = daysTabsContainer ? daysTabsContainer.closest('.grid').querySelector('.md\\:col-span-3') : null;

        if (!daysTabsContainer || !exercisesListContainer) return;

        // Remove extra tabs and exercise list containers if days decreased
        const existingTabs = Array.from(daysTabsContainer.querySelectorAll('.day-tab-btn'));
        existingTabs.forEach(tab => {
            const dayNum = parseInt(tab.id.replace('tab-day-', ''));
            const existsInNewDays = days.some(d => d.day_number === dayNum);
            if (!existsInNewDays) {
                tab.remove();
                const dayContent = document.getElementById(`exercises-day-${dayNum}`);
                if (dayContent) dayContent.remove();
            }
        });

        // Add missing tabs and exercise list containers if days increased
        days.forEach((day, index) => {
            let tab = document.getElementById(`tab-day-${day.day_number}`);
            if (!tab) {
                tab = document.createElement('button');
                tab.onclick = () => showDayExercises(day.day_number);
                tab.id = `tab-day-${day.day_number}`;
                tab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200";
                tab.innerHTML = `
                    <div class="font-bold">Día ${day.day_number}</div>
                    <div class="text-[10px] font-normal text-slate-450 mt-0.5 truncate">${day.day_name}</div>
                `;
                daysTabsContainer.appendChild(tab);
            } else {
                const dayNameDiv = tab.querySelector('.text-\\[10px\\]');
                if (dayNameDiv) dayNameDiv.textContent = day.day_name;
            }

            let dayContent = document.getElementById(`exercises-day-${day.day_number}`);
            if (!dayContent) {
                dayContent = document.createElement('div');
                dayContent.id = `exercises-day-${day.day_number}`;
                dayContent.className = "day-exercises-content space-y-6 hidden";
                dayContent.innerHTML = `
                    <div class="flex items-center justify-between border-b border-slate-880 pb-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">${day.day_name}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Enfoque: ${day.focus_area || 'General'}</p>
                        </div>
                        <button onclick="openAddModal(${day.id}, '${day.day_name.replace(/'/g, "\\'")}')" class="px-3.5 py-1.5 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i> Añadir Ejercicio
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-slate-900/20 border border-dashed border-slate-800/80 rounded-3xl py-12 px-6 text-center text-slate-500">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 mb-4 shadow-inner">
                                <i data-lucide="dumbbell" class="w-7 h-7"></i>
                            </div>
                            <h4 class="font-extrabold text-slate-300 text-sm mb-1">Día sin ejercicios asignados</h4>
                            <p class="text-xs text-slate-500 mb-4 max-w-sm mx-auto">Añade los ejercicios que formarán parte de la rutina para este día de entrenamiento.</p>
                            <button onclick="openAddModal(${day.id}, '${day.day_name.replace(/'/g, "\\'")}')" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all inline-flex items-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
                                <span>Agregar Primer Ejercicio</span>
                            </button>
                        </div>
                    </div>
                `;
                exercisesListContainer.appendChild(dayContent);
            }
        });

        // Activate first available tab if current selection disappeared
        const visibleTabs = Array.from(daysTabsContainer.querySelectorAll('.day-tab-btn'));
        const activeTab = daysTabsContainer.querySelector('.border-lime-500\\/30');
        if (!activeTab && visibleTabs.length > 0) {
            const firstTabNum = parseInt(visibleTabs[0].id.replace('tab-day-', ''));
            showDayExercises(firstTabNum);
        }

        if (window.lucide) window.lucide.createIcons();
    }

    async function submitEditRoutine(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-routine-submit-btn');

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
                toggleModal('edit-routine-modal');
                showExerciseToast(data.message, 'success');

                currentRoutineData = data.routine;
                const r = data.routine;

                document.getElementById('routine-header-name').textContent = r.name;
                document.getElementById('routine-header-desc').textContent = r.description || 'Listado de ejercicios por día.';
                document.getElementById('routine-header-weeks').textContent = r.duration_weeks;
                document.getElementById('routine-header-days').textContent = r.days_per_week;
                document.getElementById('routine-header-difficulty').textContent = translateDifficulty(r.difficulty);
                document.getElementById('routine-header-goal').textContent = translateGoal(r.goal_type);
                document.getElementById('routine-header-bmi').textContent = translateBmi(r.bmi_category);
                document.getElementById('routine-header-gym').textContent = r.requires_gym ? 'Requiere Gimnasio' : 'Sin Equipamiento';

                const statusEl = document.getElementById('routine-header-status');
                if (statusEl) {
                    if (r.is_active) {
                        statusEl.innerHTML = `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activa</span>`;
                    } else {
                        statusEl.innerHTML = `<span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactiva</span>`;
                    }
                }

                syncRoutineDaysDOM(r.days);
            } else {
                const errMsg = data.message || 'Error al actualizar la rutina.';
                showExerciseToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showExerciseToast('Ocurrió un error al procesar la solicitud.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitAddExercise(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('add-exercise-submit-btn');

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
                toggleModal('add-exercise-modal');
                showExerciseToast(data.message, 'success');

                const ex = data.routine_exercise;
                const dayId = form.querySelector('[name="routine_day_id"]').value;
                const dayContent = document.querySelector(`#exercises-day-${dayId} .space-y-4`);

                if (dayContent && ex) {
                    const emptyState = dayContent.querySelector('.border-dashed');
                    if (emptyState) emptyState.remove();

                    const exerciseCount = dayContent.querySelectorAll('[id^="exercise_card_"]').length;

                    const newCard = document.createElement('div');
                    newCard.id = `exercise_card_${ex.id}`;
                    newCard.className = "group bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700/80 hover:bg-slate-900/60 transition-all duration-300 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative overflow-hidden";

                    const imgUrl = ex.exercise && ex.exercise.image_url 
                        ? `/storage/${ex.exercise.image_url}` 
                        : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop';

                    const exName = ex.exercise ? ex.exercise.name : 'Ejercicio';
                    const muscleGroup = ex.exercise ? ex.exercise.muscle_group : '';
                    const difficulty = ex.exercise ? ex.exercise.difficulty : '';
                    const escapedExName = exName.replace(/'/g, "\\'");
                    const escapedNotes = (ex.notes || '').replace(/'/g, "\\'").replace(/\n/g, ' ');
                    const escapedReps = (ex.reps || '').replace(/'/g, "\\'");

                    newCard.innerHTML = `
                        <div class="space-y-3.5 flex-1 min-w-0">
                            <div class="flex items-start gap-4">
                                <div class="relative shrink-0">
                                    <img src="${imgUrl}" alt="${exName}" class="w-14 h-14 rounded-2xl object-cover border border-slate-800 shadow-md group-hover:scale-105 transition-transform duration-300">
                                    <span class="absolute -top-1.5 -left-1.5 px-2 py-0.5 bg-slate-950/90 text-lime-400 border border-lime-500/30 text-[9px] font-black font-mono rounded-lg shadow">
                                        #${exerciseCount + 1}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-extrabold text-slate-100 text-base group-hover:text-lime-400 transition-colors truncate">${exName}</h4>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] font-semibold mt-1 flex-wrap">
                                        <span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-bold uppercase tracking-wider">
                                            ${muscleGroup}
                                        </span>
                                        <span class="px-2 py-0.5 bg-slate-950/60 text-slate-400 border border-slate-850 rounded-md font-bold uppercase tracking-wider">
                                            ${difficulty}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3 max-w-md bg-slate-950/50 p-3 rounded-2xl border border-slate-850/80 text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-slate-900 rounded-lg text-slate-400 border border-slate-800 shrink-0">
                                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Series</span>
                                        <span class="font-black text-slate-100 text-sm leading-tight card-sets-text">${ex.sets}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-lime-500/10 rounded-lg text-lime-400 border border-lime-500/20 shrink-0">
                                        <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Repeticiones</span>
                                        <span class="font-black text-lime-400 text-sm leading-tight card-reps-text">${ex.reps}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-emerald-500/10 rounded-lg text-emerald-400 border border-emerald-500/20 shrink-0">
                                        <i data-lucide="timer" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[9px] text-slate-500 font-extrabold uppercase tracking-wider">Descanso</span>
                                        <span class="font-bold text-slate-200 text-sm leading-tight card-rest-text">${ex.rest_seconds}s</span>
                                    </div>
                                </div>
                            </div>
                            ${ex.notes ? `
                                <div class="text-xs text-slate-300 bg-slate-950/50 p-3 rounded-2xl border-l-2 border-lime-500/60 border-y border-r border-slate-850/60 flex items-start gap-2.5 card-notes-block">
                                    <i data-lucide="message-square" class="w-4 h-4 text-lime-400 shrink-0 mt-0.5"></i>
                                    <div class="leading-relaxed">
                                        <span class="font-extrabold text-slate-400 uppercase text-[9px] block tracking-wider mb-0.5">Indicación del Coach:</span>
                                        <span class="card-notes-text">${ex.notes}</span>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                        <div class="flex lg:flex-col gap-2 shrink-0 self-stretch justify-center pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-850/60">
                            <button onclick="openEditModal('/rutinas/{{ $routine->id }}/ejercicios/${ex.id}/update', ${ex.sets}, '${escapedReps}', ${ex.rest_seconds}, '${escapedNotes}', '${escapedExName}', '${imgUrl}')" 
                                    class="flex-1 lg:flex-none px-4 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 hover:border-slate-700 text-slate-200 transition-all flex items-center justify-center gap-2 shadow-sm active:scale-95">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>Editar</span>
                            </button>
                            <button type="button" onclick="openDeleteModal('/rutinas/{{ $routine->id }}/ejercicios/${ex.id}/remove', '${escapedExName}', ${ex.id})" 
                                    class="flex-1 lg:flex-none px-4 py-2.5 bg-slate-950 hover:bg-rose-500/10 text-xs font-bold rounded-xl border border-slate-850 hover:border-rose-500/30 text-rose-400 transition-all flex items-center justify-center gap-2 shadow-sm active:scale-95">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Quitar</span>
                            </button>
                        </div>
                    `;

                    dayContent.appendChild(newCard);
                    if (window.lucide) window.lucide.createIcons();
                }
                form.reset();
            } else {
                const errMsg = data.message || 'Error al añadir el ejercicio.';
                showExerciseToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showExerciseToast('Ocurrió un error al intentar guardar el ejercicio.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitEditExercise(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-exercise-submit-btn');

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
                toggleModal('edit-exercise-modal');
                showExerciseToast(data.message, 'success');

                const ex = data.routine_exercise;
                const card = document.getElementById(`exercise_card_${ex.id}`);
                if (card) {
                    const setsEl = card.querySelector('.card-sets-text');
                    const repsEl = card.querySelector('.card-reps-text');
                    const restEl = card.querySelector('.card-rest-text');
                    
                    if (setsEl) setsEl.textContent = ex.sets;
                    if (repsEl) repsEl.textContent = ex.reps;
                    if (restEl) restEl.textContent = `${ex.rest_seconds}s`;

                    let notesBlock = card.querySelector('.card-notes-block');
                    if (ex.notes) {
                        if (notesBlock) {
                            const notesText = notesBlock.querySelector('.card-notes-text');
                            if (notesText) notesText.textContent = ex.notes;
                        } else {
                            const leftCol = card.querySelector('.space-y-3\\.5');
                            if (leftCol) {
                                const newNotesBlock = document.createElement('div');
                                newNotesBlock.className = "text-xs text-slate-300 bg-slate-950/50 p-3 rounded-2xl border-l-2 border-lime-500/60 border-y border-r border-slate-850/60 flex items-start gap-2.5 card-notes-block";
                                newNotesBlock.innerHTML = `
                                    <i data-lucide="message-square" class="w-4 h-4 text-lime-400 shrink-0 mt-0.5"></i>
                                    <div class="leading-relaxed">
                                        <span class="font-extrabold text-slate-400 uppercase text-[9px] block tracking-wider mb-0.5">Indicación del Coach:</span>
                                        <span class="card-notes-text">${ex.notes}</span>
                                    </div>
                                `;
                                leftCol.appendChild(newNotesBlock);
                                if (window.lucide) window.lucide.createIcons();
                            }
                        }
                    } else if (notesBlock) {
                        notesBlock.remove();
                    }
                }
            } else {
                const errMsg = data.message || 'Error al actualizar el ejercicio.';
                showExerciseToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showExerciseToast('Ocurrió un error al intentar actualizar el ejercicio.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitDeleteExercise(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-exercise-submit-btn');
        const elementId = document.getElementById('delete-exercise-target-id').value;

        setBtnLoading(submitBtn, true, 'Quitando...');

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
                toggleModal('delete-exercise-modal');
                showExerciseToast(data.message, 'success');

                if (elementId) {
                    const card = document.getElementById(`exercise_card_${elementId}`);
                    if (card) {
                        const parent = card.closest('.space-y-4');
                        const dayContainer = card.closest('.day-exercises-content');
                        let dayId = 0;
                        let dayName = 'Entrenamiento';

                        if (dayContainer) {
                            const headerTitle = dayContainer.querySelector('h2');
                            if (headerTitle) dayName = headerTitle.textContent;

                            const addBtn = dayContainer.querySelector('button[onclick*="openAddModal"]');
                            if (addBtn) {
                                const match = addBtn.getAttribute('onclick').match(/openAddModal\((\d+)/);
                                if (match) {
                                    dayId = match[1];
                                }
                            }
                        }

                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            if (parent) {
                                const remainingCards = parent.querySelectorAll('[id^="exercise_card_"]').length;
                                if (remainingCards === 0) {
                                    parent.innerHTML = `
                                        <div class="bg-slate-900/20 border border-dashed border-slate-800/80 rounded-3xl py-12 px-6 text-center text-slate-500">
                                            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600 mb-4 shadow-inner">
                                                <i data-lucide="dumbbell" class="w-7 h-7"></i>
                                            </div>
                                            <h4 class="font-extrabold text-slate-300 text-sm mb-1">Día sin ejercicios asignados</h4>
                                            <p class="text-xs text-slate-500 mb-4 max-w-sm mx-auto">Añade los ejercicios que formarán parte de la rutina para este día de entrenamiento.</p>
                                            <button onclick="openAddModal(${dayId}, '${dayName.replace(/'/g, "\\'")}')" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all inline-flex items-center gap-2">
                                                <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
                                                <span>Agregar Primer Ejercicio</span>
                                            </button>
                                        </div>
                                    `;
                                    if (window.lucide) window.lucide.createIcons();
                                }
                            }
                        }, 300);
                    }
                }
            } else {
                const errMsg = data.message || 'Error al quitar el ejercicio.';
                showExerciseToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showExerciseToast('Ocurrió un error al intentar quitar el ejercicio.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    function showExerciseToast(message, type = 'success') {
        let container = document.getElementById('exercise-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'exercise-toast-container';
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
</script>
@endsection
