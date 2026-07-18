@extends('layouts.admin')

@section('title', 'Crear Plantilla Nutricional')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Quick navigation -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('nutricion.index') }}" class="hover:text-lime-400 transition-colors">Planes de Nutrición</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        <span class="text-slate-200">Crear Plan de Alimentación</span>
    </div>

    <!-- Main Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 md:p-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Crear Nueva Plantilla de Nutrición</h1>
            <p class="text-slate-400 text-xs mt-1">Define las calorías, el objetivo y la estructura energética del plan alimenticio.</p>
        </div>

        @if ($errors->any())
            <div class="mt-4 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-xs space-y-1">
                <span class="font-bold">Por favor corrige los siguientes errores:</span>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('nutricion.store') }}" method="POST" class="mt-8 space-y-6">
            @csrf

            <!-- Form Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre del Plan Nutricional *</label>
                    <input type="text" name="name" required value="{{ old('name') }}" placeholder="Ej. Volumen Limpio 2500 kcal" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción o Recomendaciones Generales</label>
                    <textarea name="description" rows="3" placeholder="Ingresa detalles sobre suplementación, comidas permitidas o división de porciones..." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">{{ old('description') }}</textarea>
                </div>

                <!-- Goal Type -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Enfoque Metabólico *</label>
                    <select name="goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                        <option value="gain_muscle" {{ old('goal_type') === 'gain_muscle' ? 'selected' : '' }}>Hipertrofia / Volumen</option>
                        <option value="lose_weight" {{ old('goal_type') === 'lose_weight' ? 'selected' : '' }}>Déficit / Definición</option>
                        <option value="gain_weight" {{ old('goal_type') === 'gain_weight' ? 'selected' : '' }}>Aumento de Peso</option>
                        <option value="maintain" {{ old('goal_type') === 'maintain' ? 'selected' : '' }}>Recomposición / Balanceado</option>
                        <option value="improve_endurance" {{ old('goal_type') === 'improve_endurance' ? 'selected' : '' }}>Rendimiento / Resistencia</option>
                        <option value="general" {{ old('goal_type') === 'general' ? 'selected' : '' }}>General / Salud</option>
                    </select>
                </div>

                <!-- Daily Calories -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Calorías Diarias Objetivo *</label>
                    <input type="number" name="daily_calories" required min="500" max="10000" value="{{ old('daily_calories', 2000) }}" placeholder="Ej. 2500" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                </div>

                <!-- Duration (Weeks) -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Duración (Semanas) *</label>
                    <input type="number" name="duration_weeks" required min="1" value="{{ old('duration_weeks', 12) }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <!-- Submit Section -->
            <div class="pt-6 border-t border-slate-850/60 flex items-center justify-end gap-3">
                <a href="{{ route('nutricion.index') }}" class="px-5 py-2.5 bg-slate-950 hover:bg-slate-850 border border-slate-850 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">
                    Crear Dieta
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
