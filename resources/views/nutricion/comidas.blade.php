@extends('layouts.admin')

@section('title', 'Comidas del Plan - ' . $plan->name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('nutricion.index') }}" class="hover:text-lime-400 transition-colors">Planes de Nutrición</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-200">Menú de Comidas</span>
        </div>
        <a href="{{ route('nutricion.index') }}" class="px-3.5 py-1.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-xs font-bold rounded-xl text-slate-300 transition-colors flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al listado
        </a>
    </div>

    @php
        $goalLabels = [
            'lose_weight' => 'Déficit / Pérdida de Grasa',
            'gain_muscle' => 'Hipertrofia / Ganar Músculo',
            'gain_weight' => 'Aumento de Peso',
            'maintain' => 'Mantenimiento',
            'improve_endurance' => 'Resistencia',
            'general' => 'General',
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
                <h1 id="plan-header-name" class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight">{{ $plan->name }}</h1>
                <span id="plan-header-status">
                    @if($plan->is_active)
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                    @else
                        <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>
                    @endif
                </span>
            </div>
            <p id="plan-header-desc" class="text-slate-400 text-sm mt-1">{{ $plan->description ?? 'Menú diario y desglose nutricional.' }}</p>
            
            <div class="flex items-center gap-4 mt-4 text-xs font-bold text-slate-400 flex-wrap">
                <span class="flex items-center gap-1.5"><i data-lucide="flame" class="w-4 h-4 text-amber-500"></i> <span id="plan-header-calories">{{ number_format($plan->daily_calories, 0) }}</span> kcal</span>
                <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-lime-500"></i> <span id="plan-header-weeks">{{ $plan->duration_weeks }}</span> Semanas</span>
                <span class="flex items-center gap-1.5"><i data-lucide="activity" class="w-4 h-4 text-purple-500"></i> <span id="plan-header-days-count">{{ $plan->days->count() }}</span> Días Planificados</span>
                <span class="flex items-center gap-1.5"><i data-lucide="target" class="w-4 h-4 text-cyan-400"></i> Objetivo: <span id="plan-header-goal">{{ $goalLabels[$plan->goal_type] ?? $plan->goal_type }}</span></span>
                <span class="flex items-center gap-1.5"><i data-lucide="user-check" class="w-4 h-4 text-rose-400"></i> IMC: <span id="plan-header-bmi">{{ $bmiLabels[$plan->bmi_category] ?? $plan->bmi_category }}</span></span>
            </div>
        </div>

        <button type="button" onclick="openEditPlanModal()" class="px-4 py-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 shrink-0 self-start md:self-center">
            <i data-lucide="edit-3" class="w-4 h-4"></i>
            <span>Editar Información</span>
        </button>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <!-- Days Left Navigation (Tabs) -->
        <div class="md:col-span-1 space-y-4">
            <div class="flex items-center justify-between px-2">
                <h3 class="text-xs uppercase font-extrabold tracking-wider text-slate-500">Días del Plan</h3>
                <button type="button" onclick="submitAddDay()" id="add-day-btn" class="p-1.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 rounded-lg text-lime-400 hover:text-lime-300 transition-colors flex items-center gap-1 text-[10px] font-bold shadow-sm" title="Añadir Día">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Añadir Día
                </button>
            </div>
            
            <div class="flex flex-row md:flex-col overflow-x-auto md:overflow-x-visible gap-2 pb-2 md:pb-0" id="days-tabs">
                @foreach($plan->days as $index => $day)
                    <button onclick="showDayMenu({{ $day->day_number }})" 
                            id="tab-day-{{ $day->day_number }}" 
                            class="day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border {{ $index === 0 ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30' : 'bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200' }}">
                        <div class="font-bold">Día {{ $day->day_number }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Meals Details Area -->
        <div class="md:col-span-3" id="meals-details-container">
            @foreach($plan->days as $index => $day)
                <div id="menu-day-{{ $day->day_number }}" class="day-menu-content space-y-6 {{ $index === 0 ? '' : 'hidden' }}">
                    
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Distribución del Día {{ $day->day_number }}</h2>
                            <span class="text-xs text-slate-455">Total: 5 Comidas Planificadas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick='openProgramModal({{ json_encode($day) }})' class="px-3 py-1.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 text-xs font-bold rounded-xl shadow-md transition-colors flex items-center gap-1.5">
                                <i data-lucide="utensils" class="w-3.5 h-3.5"></i> Programar Menú
                            </button>
                            <button type="button" onclick="openDeleteDayModal('{{ route('nutricion.delete_meal_plan_day', [$plan->id, $day->id]) }}', {{ $day->day_number }})" class="p-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-rose-400 rounded-xl transition-colors" title="Eliminar este día">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Meals List -->
                    @php
                        $meals = [
                            ['key' => 'breakfast', 'label' => 'Desayuno', 'recipe' => $day->breakfast, 'icon' => 'coffee', 'color' => 'text-amber-400 bg-amber-500/10'],
                            ['key' => 'snack1', 'label' => 'Media Mañana (Snack 1)', 'recipe' => $day->snack1, 'icon' => 'cookie', 'color' => 'text-lime-400 bg-lime-500/10'],
                            ['key' => 'lunch', 'label' => 'Almuerzo / Comida Principal', 'recipe' => $day->lunch, 'icon' => 'utensils', 'color' => 'text-emerald-400 bg-emerald-500/10'],
                            ['key' => 'snack2', 'label' => 'Media Tarde (Snack 2)', 'recipe' => $day->snack2, 'icon' => 'apple', 'color' => 'text-purple-400 bg-purple-500/10'],
                            ['key' => 'dinner', 'label' => 'Cena', 'recipe' => $day->dinner, 'icon' => 'soup', 'color' => 'text-blue-400 bg-blue-500/10']
                        ];
                    @endphp

                    <div class="space-y-4" id="meals-list-day-{{ $day->day_number }}">
                        @foreach($meals as $meal)
                            @if($meal['recipe'])
                                <!-- Meal Block -->
                                <div id="meal_block_{{ $day->id }}_{{ $meal['key'] }}" class="bg-slate-900/30 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700/60 transition-all">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-850 pb-3 mb-4">
                                        <div class="flex items-center gap-3">
                                            @if($meal['recipe']->image_url)
                                                <img src="{{ asset($meal['recipe']->image_url) }}" class="w-12 h-12 rounded-xl object-cover border border-slate-800 shrink-0">
                                            @else
                                                <div class="p-2.5 rounded-xl {{ $meal['color'] }} shrink-0">
                                                    <i data-lucide="{{ $meal['icon'] }}" class="w-5 h-5"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-500">{{ $meal['label'] }}</span>
                                                <h4 class="font-bold text-slate-100 text-base">{{ $meal['recipe']->name }}</h4>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-3 text-xs bg-slate-950 p-2 rounded-lg border border-slate-850">
                                                <span class="font-bold text-amber-400">{{ number_format($meal['recipe']->calories_total, 0) }} kcal</span>
                                                <span class="text-slate-600">|</span>
                                                <span class="text-slate-400">P: <strong class="text-red-400 font-semibold">{{ $meal['recipe']->protein_g }}g</strong></span>
                                                <span class="text-slate-400">C: <strong class="text-lime-400 font-semibold">{{ $meal['recipe']->carbs_g }}g</strong></span>
                                                <span class="text-slate-400">F: <strong class="text-amber-500 font-semibold">{{ $meal['recipe']->fat_g }}g</strong></span>
                                            </div>
                                            <!-- Remove single meal button -->
                                            <button type="button" onclick="submitRemoveMeal('{{ $day->id }}', '{{ $meal['key'] }}', '{{ addslashes($meal['label']) }}', {{ $day->day_number }})" class="p-2 bg-slate-950 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 border border-slate-850 hover:border-rose-500/30 rounded-xl transition-all shadow-sm" title="Quitar {{ $meal['label'] }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($meal['recipe']->description)
                                        <p class="text-xs text-slate-400 mb-3 italic">"{{ $meal['recipe']->description }}"</p>
                                    @endif

                                    <div class="space-y-2">
                                        <span class="block text-[10px] uppercase font-extrabold tracking-wider text-slate-500">Preparación</span>
                                        <p class="text-xs text-slate-300 leading-relaxed">{{ $meal['recipe']->instructions ?? 'No se detallan instrucciones de preparación.' }}</p>
                                    </div>
                                </div>
                            @else
                                <div id="meal_block_{{ $day->id }}_{{ $meal['key'] }}" class="bg-slate-950/20 border border-dashed border-slate-850 rounded-xl p-5 text-center text-xs text-slate-600 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $meal['icon'] }}" class="w-4 h-4 text-slate-700"></i>
                                        <span>Sin comida planificada en: <strong class="text-slate-450">{{ $meal['label'] }}</strong></span>
                                    </div>
                                    <button onclick='openProgramModal({{ json_encode($day) }})' class="text-[10px] text-lime-450 hover:text-lime-300 font-bold uppercase transition-colors">
                                        + Asignar
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>

<!-- ================= MODAL: EDITAR INFORMACIÓN DEL PLAN ================= -->
<div id="edit-meal-plan-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Editar Información del Plan</h3>
                <span class="text-xs text-slate-400">Modifica los parámetros nutricionales y generales</span>
            </div>
            <button type="button" onclick="toggleModal('edit-meal-plan-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-plan-form" action="{{ route('nutricion.update_info', $plan->id) }}" method="POST" onsubmit="submitEditPlan(event)" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre del Plan *</label>
                <input type="text" name="name" id="plan_edit_name" required value="{{ $plan->name }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción</label>
                <textarea name="description" id="plan_edit_description" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">{{ $plan->description }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Objetivo Nutricional *</label>
                    <select name="goal_type" id="plan_edit_goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="lose_weight" {{ $plan->goal_type === 'lose_weight' ? 'selected' : '' }}>Déficit / Pérdida de Grasa</option>
                        <option value="gain_muscle" {{ $plan->goal_type === 'gain_muscle' ? 'selected' : '' }}>Hipertrofia / Ganar Músculo</option>
                        <option value="gain_weight" {{ $plan->goal_type === 'gain_weight' ? 'selected' : '' }}>Aumento de Peso</option>
                        <option value="maintain" {{ $plan->goal_type === 'maintain' ? 'selected' : '' }}>Mantenimiento</option>
                        <option value="improve_endurance" {{ $plan->goal_type === 'improve_endurance' ? 'selected' : '' }}>Resistencia</option>
                        <option value="general" {{ $plan->goal_type === 'general' ? 'selected' : '' }}>General</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Categoría IMC *</label>
                    <select name="bmi_category" id="plan_edit_bmi_category" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="all" {{ $plan->bmi_category === 'all' ? 'selected' : '' }}>Para todos los IMC</option>
                        <option value="underweight" {{ $plan->bmi_category === 'underweight' ? 'selected' : '' }}>Bajo Peso</option>
                        <option value="normal" {{ $plan->bmi_category === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="overweight" {{ $plan->bmi_category === 'overweight' ? 'selected' : '' }}>Sobrepeso</option>
                        <option value="obese" {{ $plan->bmi_category === 'obese' ? 'selected' : '' }}>Obesidad</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Calorías Diarias (kcal) *</label>
                    <input type="number" name="daily_calories" id="plan_edit_daily_calories" required step="1" min="500" max="10000" value="{{ $plan->daily_calories }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Duración (Semanas) *</label>
                    <input type="number" name="duration_weeks" id="plan_edit_duration_weeks" required min="1" value="{{ $plan->duration_weeks }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="plan_edit_is_active" value="1" {{ $plan->is_active ? 'checked' : '' }} class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                    <label for="plan_edit_is_active" class="text-xs text-slate-300 font-medium cursor-pointer">Plan Activo</label>
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-meal-plan-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-plan-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: PROGRAMAR MENÚ DEL DÍA ================= -->
<div id="program-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100" id="program-modal-title">Programar Menú</h3>
            <button type="button" onclick="toggleModal('program-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form id="program-menu-form" action="{{ route('nutricion.save_comidas_day', $plan->id) }}" method="POST" onsubmit="submitSaveComidasDay(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <input type="hidden" name="day_number" id="modal-day-number">

            <!-- Breakfast select -->
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Desayuno</label>
                <select name="breakfast_recipe_id" id="select-breakfast" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">-- Sin asignar --</option>
                    @foreach($recipes as $recipe)
                        <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ number_format($recipe->calories_total, 0) }} kcal)</option>
                    @endforeach
                </select>
            </div>

            <!-- Snack 1 select -->
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Media Mañana (Snack 1)</label>
                <select name="snack1_recipe_id" id="select-snack1" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">-- Sin asignar --</option>
                    @foreach($recipes as $recipe)
                        <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ number_format($recipe->calories_total, 0) }} kcal)</option>
                    @endforeach
                </select>
            </div>

            <!-- Lunch select -->
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Almuerzo / Comida Principal</label>
                <select name="lunch_recipe_id" id="select-lunch" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">-- Sin asignar --</option>
                    @foreach($recipes as $recipe)
                        <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ number_format($recipe->calories_total, 0) }} kcal)</option>
                    @endforeach
                </select>
            </div>

            <!-- Snack 2 select -->
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Media Tarde (Snack 2)</label>
                <select name="snack2_recipe_id" id="select-snack2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">-- Sin asignar --</option>
                    @foreach($recipes as $recipe)
                        <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ number_format($recipe->calories_total, 0) }} kcal)</option>
                    @endforeach
                </select>
            </div>

            <!-- Dinner select -->
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Cena</label>
                <select name="dinner_recipe_id" id="select-dinner" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="">-- Sin asignar --</option>
                    @foreach($recipes as $recipe)
                        <option value="{{ $recipe->id }}">{{ $recipe->name }} ({{ number_format($recipe->calories_total, 0) }} kcal)</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('program-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="program-modal-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Menú
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ELIMINAR DÍA DEL PLAN ================= -->
<div id="delete-day-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Eliminar Día del Plan</h3>
                    <span class="text-xs text-rose-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-day-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-400 leading-relaxed">
            ¿Estás seguro de que deseas eliminar el <strong id="delete-day-name-text" class="text-slate-100">Día X</strong> del plan? Se quitarán todas las recetas asignadas a este día.
        </p>

        <form id="delete-day-form" action="" method="POST" onsubmit="submitDeleteDay(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('delete-day-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-day-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-rose-600/20 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Sí, Eliminar Día</span>
            </button>
        </form>
    </div>
</div>

<script>
    let currentPlanData = @json($plan);
    let allRecipesList = @json($recipes);

    function translateGoal(g) {
        const map = {
            'lose_weight': 'Déficit / Pérdida de Grasa',
            'gain_muscle': 'Hipertrofia / Ganar Músculo',
            'gain_weight': 'Aumento de Peso',
            'maintain': 'Mantenimiento',
            'improve_endurance': 'Resistencia',
            'general': 'General'
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

    // Temporary Toast Notifications
    function showToast(message, type = 'success') {
        let container = document.getElementById('meal-plan-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'meal-plan-toast-container';
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

    function showDayMenu(dayNumber) {
        const contents = document.querySelectorAll('.day-menu-content');
        contents.forEach(content => content.classList.add('hidden'));

        const tabs = document.querySelectorAll('.day-tab-btn');
        tabs.forEach(tab => {
            tab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200";
        });

        const activeContent = document.getElementById('menu-day-' + dayNumber);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        const activeTab = document.getElementById('tab-day-' + dayNumber);
        if (activeTab) {
            activeTab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30";
        }
    }

    function openEditPlanModal() {
        const p = currentPlanData;
        document.getElementById('plan_edit_name').value = p.name;
        document.getElementById('plan_edit_description').value = p.description || '';
        document.getElementById('plan_edit_goal_type').value = p.goal_type;
        document.getElementById('plan_edit_bmi_category').value = p.bmi_category;
        document.getElementById('plan_edit_daily_calories').value = p.daily_calories;
        document.getElementById('plan_edit_duration_weeks').value = p.duration_weeks;
        document.getElementById('plan_edit_is_active').checked = !!p.is_active;

        toggleModal('edit-meal-plan-modal');
    }

    function openProgramModal(day) {
        document.getElementById('program-modal-title').innerText = 'Programar Menú: Día ' + day.day_number;
        document.getElementById('modal-day-number').value = day.day_number;
        
        document.getElementById('select-breakfast').value = day.breakfast_recipe_id || '';
        document.getElementById('select-snack1').value = day.snack1_recipe_id || '';
        document.getElementById('select-lunch').value = day.lunch_recipe_id || '';
        document.getElementById('select-snack2').value = day.snack2_recipe_id || '';
        document.getElementById('select-dinner').value = day.dinner_recipe_id || '';

        toggleModal('program-modal');
    }

    function openDeleteDayModal(actionUrl, dayNumber) {
        document.getElementById('delete-day-form').action = actionUrl;
        document.getElementById('delete-day-name-text').innerText = 'Día ' + dayNumber;
        toggleModal('delete-day-modal');
    }

    // AJAX Submission: Edit Plan Information
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
                currentPlanData = data.plan;

                // Update Header DOM elements
                document.getElementById('plan-header-name').textContent = data.plan.name;
                document.getElementById('plan-header-desc').textContent = data.plan.description || 'Menú diario y desglose nutricional.';
                document.getElementById('plan-header-calories').textContent = Number(data.plan.daily_calories).toLocaleString();
                document.getElementById('plan-header-weeks').textContent = data.plan.duration_weeks;
                document.getElementById('plan-header-goal').textContent = translateGoal(data.plan.goal_type);
                document.getElementById('plan-header-bmi').textContent = translateBmi(data.plan.bmi_category);

                const statusSpan = document.getElementById('plan-header-status');
                if (statusSpan) {
                    statusSpan.innerHTML = data.plan.is_active 
                        ? `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>`
                        : `<span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`;
                }

                toggleModal('edit-meal-plan-modal');
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar información del plan.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar actualizar el plan.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Add Day
    async function submitAddDay() {
        const btn = document.getElementById('add-day-btn');
        setBtnLoading(btn, true, '+Día...');

        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            const response = await fetch(`{{ route('nutricion.add_meal_plan_day', $plan->id) }}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                currentPlanData = data.plan;
                const newDay = data.new_day;

                // Update Header days count
                const daysCountEl = document.getElementById('plan-header-days-count');
                if (daysCountEl) daysCountEl.textContent = data.plan.days.length;

                // Add Day Tab
                const daysTabsContainer = document.getElementById('days-tabs');
                const newTabBtn = document.createElement('button');
                newTabBtn.onclick = () => showDayMenu(newDay.day_number);
                newTabBtn.id = `tab-day-${newDay.day_number}`;
                newTabBtn.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200";
                newTabBtn.innerHTML = `<div class="font-bold">Día ${newDay.day_number}</div>`;
                daysTabsContainer.appendChild(newTabBtn);

                // Add Day Content Container
                const detailsContainer = document.getElementById('meals-details-container');
                const newContentDiv = document.createElement('div');
                newContentDiv.id = `menu-day-${newDay.day_number}`;
                newContentDiv.className = `day-menu-content space-y-6 hidden`;

                const dayJsonStr = JSON.stringify(newDay).replace(/'/g, "&#39;");

                newContentDiv.innerHTML = `
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-100">Distribución del Día ${newDay.day_number}</h2>
                            <span class="text-xs text-slate-455">Total: 5 Comidas Planificadas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick='openProgramModal(${dayJsonStr})' class="px-3 py-1.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 text-xs font-bold rounded-xl shadow-md transition-colors flex items-center gap-1.5">
                                <i data-lucide="utensils" class="w-3.5 h-3.5"></i> Programar Menú
                            </button>
                            <button type="button" onclick="openDeleteDayModal('/nutricion/${currentPlanData.id}/comidas/${newDay.id}', ${newDay.day_number})" class="p-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-rose-400 rounded-xl transition-colors" title="Eliminar este día">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4" id="meals-list-day-${newDay.day_number}">
                        ${renderMealsListHtml(newDay)}
                    </div>
                `;

                detailsContainer.appendChild(newContentDiv);
                if (window.lucide) window.lucide.createIcons();

                showDayMenu(newDay.day_number);
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al añadir nuevo día.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar añadir el día.', 'error');
        } finally {
            setBtnLoading(btn, false);
        }
    }

    // AJAX Submission: Save / Program Meals for a Day
    async function submitSaveComidasDay(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('program-modal-submit-btn');

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
                currentPlanData = data.plan;
                const day = data.day;

                // Re-render meals list HTML for this day
                const mealsContainer = document.getElementById(`meals-list-day-${day.day_number}`);
                if (mealsContainer) {
                    mealsContainer.innerHTML = renderMealsListHtml(day);
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('program-modal');
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al programar menú del día.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar guardar el menú.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Delete Day
    async function submitDeleteDay(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-day-submit-btn');

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
                currentPlanData = data.plan;

                // Re-render whole DOM days tabs and details
                rebuildDaysDOM(data.plan.days);

                toggleModal('delete-day-modal');
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al eliminar el día.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar eliminar el día.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Remove Single Meal from a Day
    async function submitRemoveMeal(dayId, mealType, mealLabel, dayNumber) {
        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('meal_type', mealType);

            const response = await fetch(`/nutricion/${currentPlanData.id}/comidas/${dayId}/remove-meal`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                currentPlanData = data.plan;
                const day = data.day;

                // Re-render meals list for this day
                const mealsContainer = document.getElementById(`meals-list-day-${dayNumber}`);
                if (mealsContainer) {
                    mealsContainer.innerHTML = renderMealsListHtml(day);
                }

                if (window.lucide) window.lucide.createIcons();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al quitar la comida.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al quitar la comida.', 'error');
        }
    }

    // Helper: Rebuild all days DOM after deletion
    function rebuildDaysDOM(days) {
        const daysTabsContainer = document.getElementById('days-tabs');
        const detailsContainer = document.getElementById('meals-details-container');
        
        const daysCountEl = document.getElementById('plan-header-days-count');
        if (daysCountEl) daysCountEl.textContent = days.length;

        daysTabsContainer.innerHTML = '';
        detailsContainer.innerHTML = '';

        days.forEach((day, index) => {
            // Tab button
            const tabBtn = document.createElement('button');
            tabBtn.onclick = () => showDayMenu(day.day_number);
            tabBtn.id = `tab-day-${day.day_number}`;
            tabBtn.className = `day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border ${index === 0 ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30' : 'bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200'}`;
            tabBtn.innerHTML = `<div class="font-bold">Día ${day.day_number}</div>`;
            daysTabsContainer.appendChild(tabBtn);

            // Content section
            const dayJsonStr = JSON.stringify(day).replace(/'/g, "&#39;");
            const contentDiv = document.createElement('div');
            contentDiv.id = `menu-day-${day.day_number}`;
            contentDiv.className = `day-menu-content space-y-6 ${index === 0 ? '' : 'hidden'}`;

            contentDiv.innerHTML = `
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-100">Distribución del Día ${day.day_number}</h2>
                        <span class="text-xs text-slate-455">Total: 5 Comidas Planificadas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='openProgramModal(${dayJsonStr})' class="px-3 py-1.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 text-xs font-bold rounded-xl shadow-md transition-colors flex items-center gap-1.5">
                            <i data-lucide="utensils" class="w-3.5 h-3.5"></i> Programar Menú
                        </button>
                        <button type="button" onclick="openDeleteDayModal('/nutricion/${currentPlanData.id}/comidas/${day.id}', ${day.day_number})" class="p-2 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-rose-400 rounded-xl transition-colors" title="Eliminar este día">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-4" id="meals-list-day-${day.day_number}">
                    ${renderMealsListHtml(day)}
                </div>
            `;

            detailsContainer.appendChild(contentDiv);
        });

        if (days.length > 0) {
            showDayMenu(days[0].day_number);
        }

        if (window.lucide) window.lucide.createIcons();
    }

    // Helper: Render 5 meal blocks HTML for a Day
    function renderMealsListHtml(day) {
        const meals = [
            { key: 'breakfast', label: 'Desayuno', recipe: day.breakfast, icon: 'coffee', color: 'text-amber-400 bg-amber-500/10' },
            { key: 'snack1', label: 'Media Mañana (Snack 1)', recipe: day.snack1, icon: 'cookie', color: 'text-lime-400 bg-lime-500/10' },
            { key: 'lunch', label: 'Almuerzo / Comida Principal', recipe: day.lunch, icon: 'utensils', color: 'text-emerald-400 bg-emerald-500/10' },
            { key: 'snack2', label: 'Media Tarde (Snack 2)', recipe: day.snack2, icon: 'apple', color: 'text-purple-400 bg-purple-500/10' },
            { key: 'dinner', label: 'Cena', recipe: day.dinner, icon: 'soup', color: 'text-blue-400 bg-blue-500/10' }
        ];

        const dayJsonStr = JSON.stringify(day).replace(/'/g, "&#39;");

        return meals.map(m => {
            if (m.recipe) {
                const safeRecipeName = escapeHtml(m.recipe.name);
                const safeRecipeDesc = escapeHtml(m.recipe.description || '');
                const safeInstructions = escapeHtml(m.recipe.instructions || 'No se detallan instrucciones de preparación.');
                const imgHtml = m.recipe.image_url 
                    ? `<img src="/${m.recipe.image_url}" class="w-12 h-12 rounded-xl object-cover border border-slate-800 shrink-0">`
                    : `<div class="p-2.5 rounded-xl ${m.color} shrink-0"><i data-lucide="${m.icon}" class="w-5 h-5"></i></div>`;

                return `
                    <div id="meal_block_${day.id}_${m.key}" class="bg-slate-900/30 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700/60 transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-850 pb-3 mb-4">
                            <div class="flex items-center gap-3">
                                ${imgHtml}
                                <div>
                                    <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-500">${m.label}</span>
                                    <h4 class="font-bold text-slate-100 text-base">${safeRecipeName}</h4>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-3 text-xs bg-slate-950 p-2 rounded-lg border border-slate-850">
                                    <span class="font-bold text-amber-400">${Math.round(m.recipe.calories_total)} kcal</span>
                                    <span class="text-slate-600">|</span>
                                    <span class="text-slate-400">P: <strong class="text-red-400 font-semibold">${m.recipe.protein_g}g</strong></span>
                                    <span class="text-slate-400">C: <strong class="text-lime-400 font-semibold">${m.recipe.carbs_g}g</strong></span>
                                    <span class="text-slate-400">F: <strong class="text-amber-500 font-semibold">${m.recipe.fat_g}g</strong></span>
                                </div>
                                <button type="button" onclick="submitRemoveMeal('${day.id}', '${m.key}', '${escapeHtml(m.label)}', ${day.day_number})" class="p-2 bg-slate-950 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 border border-slate-850 hover:border-rose-500/30 rounded-xl transition-all shadow-sm" title="Quitar ${m.label}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        
                        ${safeRecipeDesc ? `<p class="text-xs text-slate-400 mb-3 italic">"${safeRecipeDesc}"</p>` : ''}

                        <div class="space-y-2">
                            <span class="block text-[10px] uppercase font-extrabold tracking-wider text-slate-500">Preparación</span>
                            <p class="text-xs text-slate-300 leading-relaxed">${safeInstructions}</p>
                        </div>
                    </div>
                `;
            } else {
                return `
                    <div id="meal_block_${day.id}_${m.key}" class="bg-slate-950/20 border border-dashed border-slate-850 rounded-xl p-5 text-center text-xs text-slate-600 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="${m.icon}" class="w-4 h-4 text-slate-700"></i>
                            <span>Sin comida planificada en: <strong class="text-slate-455">${m.label}</strong></span>
                        </div>
                        <button onclick='openProgramModal(${dayJsonStr})' class="text-[10px] text-lime-450 hover:text-lime-300 font-bold uppercase transition-colors">
                            + Asignar
                        </button>
                    </div>
                `;
            }
        }).join('');
    }

    // Auto-trigger session flash messages as toasts on load
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif
    });
</script>
@endsection
