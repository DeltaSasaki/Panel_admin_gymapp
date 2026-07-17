@extends('layouts.admin')

@section('title', 'Resultados de Búsqueda')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800/60 pb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <i data-lucide="search" class="w-7 h-7 text-lime-400"></i>
                Resultados para: <span class="text-lime-400">"{{ $queryStr }}"</span>
            </h1>
            <p class="text-slate-400 text-xs mt-1">
                Buscando en la sucursal: <span class="font-bold text-slate-200">{{ $activeGymName }}</span>
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-slate-200 border border-slate-700/50 font-bold text-xs rounded-xl transition-all">
            Volver al Dashboard
        </a>
    </div>

    @php
        $totalResults = $clientes->count() + $rutinas->count() + $dietas->count();
        
        $goalsMap = [
            'lose_weight' => 'Pérdida de Peso',
            'gain_muscle' => 'Ganancia Muscular',
            'gain_weight' => 'Aumento de Peso',
            'maintain' => 'Mantenimiento',
            'improve_endurance' => 'Resistencia',
            'improve_flexibility' => 'Flexibilidad'
        ];

        $difficultyMap = [
            'beginner' => 'Principiante',
            'intermediate' => 'Intermedio',
            'advanced' => 'Avanzado'
        ];
    @endphp

    @if($totalResults === 0)
        <!-- No Results Empty State -->
        <div class="py-16 text-center bg-slate-900/20 border border-slate-800/80 rounded-2xl">
            <div class="w-16 h-16 bg-slate-900/80 border border-slate-850 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500 shadow-lg">
                <i data-lucide="search-code" class="w-8 h-8"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-200">No se encontraron resultados</h3>
            <p class="text-slate-400 text-xs mt-2 max-w-sm mx-auto">
                No pudimos encontrar ningún cliente, rutina o dieta que coincida con tu criterio de búsqueda en <strong>{{ $activeGymName }}</strong>.
            </p>
        </div>
    @else
        <!-- Results Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- COLUMN 1: Clientes (Atletas) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-lime-400"></i>
                        Clientes ({{ $clientes->count() }})
                    </h2>
                </div>

                <div class="space-y-3">
                    @forelse($clientes as $cliente)
                        <a href="{{ route('clientes.show', $cliente->id) }}" class="block bg-slate-900/40 border border-slate-800/80 hover:border-slate-700/80 rounded-2xl p-4 transition-all hover:bg-slate-900/60 group">
                            <div class="flex items-center gap-3">
                                <img src="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-800">
                                <div class="overflow-hidden">
                                    <h3 class="font-bold text-slate-200 group-hover:text-lime-400 transition-colors truncate">
                                        {{ $cliente->profile->first_name ?? 'Atleta' }} {{ $cliente->profile->last_name ?? '' }}
                                    </h3>
                                    <span class="block text-[10px] text-slate-500 truncate">{{ $cliente->email }}</span>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                <span>{{ $cliente->profile->phone ?? 'Sin teléfono' }}</span>
                                <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-500 font-bold uppercase tracking-wider">
                                    {{ $cliente->gym->name }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                            Ningún cliente coincide
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- COLUMN 2: Rutinas -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="dumbbell" class="w-4 h-4 text-lime-400"></i>
                        Rutinas ({{ $rutinas->count() }})
                    </h2>
                </div>

                <div class="space-y-3">
                    @forelse($rutinas as $rutina)
                        <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4 transition-all hover:bg-slate-900/60">
                            <div class="flex items-start justify-between gap-2">
                                <div class="overflow-hidden">
                                    <h3 class="font-bold text-slate-200">
                                        {{ $rutina->name }}
                                    </h3>
                                    <p class="text-[10px] text-slate-400 line-clamp-2 mt-1">{{ $rutina->description ?? 'Sin descripción' }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-md">
                                    {{ $goalsMap[$rutina->goal_type] ?? $rutina->goal_type }}
                                </span>
                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-slate-950 border border-slate-850 text-slate-400 rounded-md">
                                    {{ $difficultyMap[$rutina->difficulty] ?? $rutina->difficulty }}
                                </span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                <span>{{ $rutina->duration_weeks }} Semanas | {{ $rutina->days_per_week }} días/sem</span>
                                <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-500 font-bold uppercase tracking-wider">
                                    {{ $rutina->gym->name }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                            Ninguna rutina coincide
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- COLUMN 3: Dietas (Planes de Nutrición) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="apple" class="w-4 h-4 text-lime-400"></i>
                        Dietas ({{ $dietas->count() }})
                    </h2>
                </div>

                <div class="space-y-3">
                    @forelse($dietas as $dieta)
                        <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4 transition-all hover:bg-slate-900/60">
                            <div class="flex items-start justify-between gap-2">
                                <div class="overflow-hidden">
                                    <h3 class="font-bold text-slate-200">
                                        {{ $dieta->name }}
                                    </h3>
                                    <p class="text-[10px] text-slate-400 line-clamp-2 mt-1">{{ $dieta->description ?? 'Sin descripción' }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-md">
                                    {{ $goalsMap[$dieta->goal_type] ?? $dieta->goal_type }}
                                </span>
                                <span class="px-1.5 py-0.5 text-[9px] font-bold bg-slate-950 border border-slate-850 text-slate-400 rounded-md">
                                    {{ $dieta->daily_calories }} kcal
                                </span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                <span>Duración: {{ $dieta->duration_weeks }} Semanas</span>
                                <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-500 font-bold uppercase tracking-wider">
                                    {{ $dieta->gym->name }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                            Ningún plan de nutrición coincide
                        </p>
                    @endforelse
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
