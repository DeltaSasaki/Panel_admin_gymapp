@extends('layouts.admin')

@section('title', 'Planes de Nutrición')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Planes de Nutrición</h1>
            <p class="text-slate-400 text-xs mt-1">Crea plantillas de macronutrientes, planes de comidas y guías de suplementación.</p>
        </div>
        <a href="{{ route('nutricion.crear') }}" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4 stroke-[3px]"></i>
            Crear Plan Nutricional
        </a>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Average Calories -->
        <div class="bg-slate-900/40 p-5 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <span class="block text-slate-500 text-xs font-semibold uppercase tracking-wider">Promedio de Calorías</span>
                <span class="block text-xl font-bold text-white mt-1">
                    {{ number_format($dietas->avg('daily_calories'), 0) }} kcal
                </span>
            </div>
            <div class="p-2.5 bg-amber-500/10 text-amber-400 rounded-xl">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Meal Plans -->
        <div class="bg-slate-900/40 p-5 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <span class="block text-slate-500 text-xs font-semibold uppercase tracking-wider">Plantillas Activas</span>
                <span class="block text-xl font-bold text-white mt-1">{{ $dietas->count() }} Dietas</span>
            </div>
            <div class="p-2.5 bg-lime-500/10 text-lime-400 rounded-xl">
                <i data-lucide="folder-git2" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Adherence Rate -->
        <div class="bg-slate-900/40 p-5 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <span class="block text-slate-500 text-xs font-semibold uppercase tracking-wider">Adherencia Estimada</span>
                <span class="block text-xl font-bold text-emerald-400 mt-1">82%</span>
            </div>
            <div class="p-2.5 bg-emerald-500/10 text-emerald-400 rounded-xl">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Hydration Target -->
        <div class="bg-slate-900/40 p-5 rounded-2xl border border-slate-800 flex items-center justify-between">
            <div>
                <span class="block text-slate-500 text-xs font-semibold uppercase tracking-wider">Objetivo Hidratación</span>
                <span class="block text-xl font-bold text-blue-400 mt-1">3.2 L / día</span>
            </div>
            <div class="p-2.5 bg-blue-500/10 text-blue-400 rounded-xl">
                <i data-lucide="droplet" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Nutrition Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $goalsMap = [
                'lose_weight' => 'Déficit / Definición',
                'gain_muscle' => 'Volumen / Hipertrofia',
                'gain_weight' => 'Aumento de Peso',
                'maintain' => 'Recomposición Corporal',
                'improve_endurance' => 'Resistencia Deportiva',
                'general' => 'General / Balanceado'
            ];
        @endphp

        @forelse($dietas as $dieta)
            @php
                // Dynamic macro splitter based on goal_type
                $calories = $dieta->daily_calories;
                if ($dieta->goal_type === 'gain_muscle') {
                    $pPct = 25; $cPct = 50; $fPct = 25;
                } elseif ($dieta->goal_type === 'lose_weight') {
                    $pPct = 40; $cPct = 35; $fPct = 25;
                } else {
                    $pPct = 30; $cPct = 40; $fPct = 30;
                }

                $proteinGrams = round(($calories * ($pPct / 100)) / 4);
                $carbGrams = round(($calories * ($cPct / 100)) / 4);
                $fatGrams = round(($calories * ($fPct / 100)) / 9);
            @endphp

            <!-- Plan Card -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 hover:border-slate-700/80 transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 rounded-full border border-amber-500/20">
                            {{ $goalsMap[$dieta->goal_type] ?? 'Nutrición' }}
                        </span>
                        <span class="text-xs text-slate-500 font-semibold">{{ $dieta->active_assignments_count }} atletas</span>
                    </div>
                    <h3 class="font-bold text-lg text-white">{{ $dieta->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $dieta->description ?? 'Sin descripción disponible.' }}</p>

                    <!-- Macro Distribution Visual -->
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs font-semibold text-slate-400">
                            <span>Macros: P / C / F</span>
                            <span class="text-slate-200">
                                {{ $proteinGrams }}g / {{ $carbGrams }}g / {{ $fatGrams }}g
                            </span>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden flex">
                            <!-- Protein -->
                            <div class="bg-red-400 h-full" style="width: {{ $pPct }}%" title="Proteína: {{ $pPct }}%"></div>
                            <!-- Carbs -->
                            <div class="bg-lime-400 h-full" style="width: {{ $cPct }}%" title="Carbohidratos: {{ $cPct }}%"></div>
                            <!-- Fats -->
                            <div class="bg-amber-400 h-full" style="width: {{ $fPct }}%" title="Grasas: {{ $fPct }}%"></div>
                        </div>
                        <div class="flex gap-4 text-[10px] font-bold text-slate-500">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400"></span> Pro ({{ $pPct }}%)</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-lime-400"></span> Carbs ({{ $cPct }}%)</span>
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Fats ({{ $fPct }}%)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-850/50 flex gap-2">
                    <a href="{{ route('nutricion.comidas', $dieta->id) }}" class="flex-1 py-2 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-lg border border-slate-850 hover:border-slate-700 text-slate-300 transition-colors text-center block">
                        Ver Comidas
                    </a>
                    <button onclick="openAssignMealModal('{{ route('nutricion.assign', $dieta->id) }}', '{{ $dieta->name }}')" class="px-3 py-2 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                        <i data-lucide="link" class="w-3.5 h-3.5"></i> Asignar
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                <i data-lucide="apple" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                <p>No se encontraron planes de nutrición registrados.</p>
            </div>
        @endforelse

    </div>
</div>

<!-- ================= MODAL: ASIGNAR DIETA ================= -->
<div id="assign-meal-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 animate-scale-up space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-white">Asignar Dieta</h3>
                <span id="modal-meal-name" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button onclick="toggleModal('assign-meal-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="assign-meal-form" method="POST" class="space-y-4">
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
                <button type="button" onclick="toggleModal('assign-meal-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar Dieta
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

    function openAssignMealModal(actionUrl, mealPlanName) {
        document.getElementById('assign-meal-form').action = actionUrl;
        document.getElementById('modal-meal-name').innerText = mealPlanName;
        toggleModal('assign-meal-modal');
    }
</script>
@endsection
