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

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent p-6 rounded-3xl border border-slate-800/40">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight">{{ $plan->name }}</h1>
        <p class="text-slate-400 text-sm mt-1">{{ $plan->description ?? 'Menú diario y desglose nutricional.' }}</p>
        <div class="flex items-center gap-4 mt-4 text-xs font-bold text-slate-400">
            <span class="flex items-center gap-1.5"><i data-lucide="flame" class="w-4 h-4 text-amber-500"></i> {{ number_format($plan->daily_calories, 0) }} kcal</span>
            <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-lime-500"></i> {{ $plan->duration_weeks }} semanas</span>
            <span class="flex items-center gap-1.5"><i data-lucide="activity" class="w-4 h-4 text-purple-500"></i> {{ $plan->days->count() }} Días Planificados</span>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <!-- Days Left Navigation (Tabs) -->
        <div class="md:col-span-1 space-y-2">
            <h3 class="text-xs uppercase font-extrabold tracking-wider text-slate-500 px-2 mb-3">Días del Plan</h3>
            <div class="flex flex-row md:flex-col overflow-x-auto md:overflow-x-visible gap-2 pb-2 md:pb-0" id="days-tabs">
                @foreach($plan->days as $index => $day)
                    <button onclick="showDayMenu({{ $day->day_number }})" 
                            id="tab-day-{{ $day->day_number }}" 
                            class="day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border {{ $index === 0 ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30' : 'bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200' }}">
                        Día {{ $day->day_number }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Meals Details Area -->
        <div class="md:col-span-3">
            @foreach($plan->days as $index => $day)
                <div id="menu-day-{{ $day->day_number }}" class="day-menu-content space-y-6 {{ $index === 0 ? '' : 'hidden' }}">
                    
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h2 class="text-lg font-bold text-slate-100">Distribución del Día {{ $day->day_number }}</h2>
                        <span class="text-xs text-slate-400">Total: 5 Comidas Planificadas</span>
                    </div>

                    <!-- Meals List -->
                    @php
                        $meals = [
                            ['label' => 'Desayuno', 'recipe' => $day->breakfast, 'icon' => 'coffee', 'color' => 'text-amber-400 bg-amber-500/10'],
                            ['label' => 'Media Mañana', 'recipe' => $day->snack1, 'icon' => 'cookie', 'color' => 'text-lime-400 bg-lime-500/10'],
                            ['label' => 'Almuerzo / Comida', 'recipe' => $day->lunch, 'icon' => 'utensils', 'color' => 'text-emerald-400 bg-emerald-500/10'],
                            ['label' => 'Media Tarde', 'recipe' => $day->snack2, 'icon' => 'apple', 'color' => 'text-purple-400 bg-purple-500/10'],
                            ['label' => 'Cena', 'recipe' => $day->dinner, 'icon' => 'soup', 'color' => 'text-blue-400 bg-blue-500/10']
                        ];
                    @endphp

                    @foreach($meals as $meal)
                        @if($meal['recipe'])
                            <!-- Meal Block -->
                            <div class="bg-slate-900/30 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700/60 transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-850 pb-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2.5 rounded-xl {{ $meal['color'] }}">
                                            <i data-lucide="{{ $meal['icon'] }}" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-500">{{ $meal['label'] }}</span>
                                            <h4 class="font-bold text-slate-100 text-base">{{ $meal['recipe']->name }}</h4>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs bg-slate-950 p-2 rounded-lg border border-slate-850">
                                        <span class="font-bold text-amber-400">{{ number_format($meal['recipe']->calories_total, 0) }} kcal</span>
                                        <span class="text-slate-600">|</span>
                                        <span class="text-slate-400">P: <strong class="text-red-400 font-semibold">{{ $meal['recipe']->protein_g }}g</strong></span>
                                        <span class="text-slate-400">C: <strong class="text-lime-400 font-semibold">{{ $meal['recipe']->carbs_g }}g</strong></span>
                                        <span class="text-slate-400">F: <strong class="text-amber-500 font-semibold">{{ $meal['recipe']->fat_g }}g</strong></span>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <span class="block text-[10px] uppercase font-extrabold tracking-wider text-slate-500">Preparación</span>
                                    <p class="text-xs text-slate-300 leading-relaxed">{{ $meal['recipe']->instructions }}</p>
                                </div>
                            </div>
                        @else
                            <div class="bg-slate-950/20 border border-dashed border-slate-850 rounded-xl p-4 text-center text-xs text-slate-600">
                                Sin comida planificada en: {{ $meal['label'] }}
                            </div>
                        @endif
                    @endforeach

                </div>
            @endforeach
        </div>

    </div>
</div>

<script>
    function showDayMenu(dayNumber) {
        // Hide all day contents
        const contents = document.querySelectorAll('.day-menu-content');
        contents.forEach(content => content.classList.add('hidden'));

        // Reset all tabs styles
        const tabs = document.querySelectorAll('.day-tab-btn');
        tabs.forEach(tab => {
            tab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-slate-900/45 text-slate-400 border-slate-800 hover:bg-slate-800/40 hover:text-slate-200";
        });

        // Show selected day content
        const activeContent = document.getElementById('menu-day-' + dayNumber);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        // Highlight selected tab
        const activeTab = document.getElementById('tab-day-' + dayNumber);
        if (activeTab) {
            activeTab.className = "day-tab-btn flex-none px-4 py-3 rounded-xl text-left text-sm font-semibold transition-all border bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-lime-500/30";
        }
    }
</script>
@endsection
