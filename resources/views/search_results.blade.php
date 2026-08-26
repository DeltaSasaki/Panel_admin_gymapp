@extends('layouts.admin')

@section('title', 'Resultados de Búsqueda')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800/60 pb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="search" class="w-7 h-7 text-lime-400"></i>
                Resultados para: <span class="text-lime-400">"{{ $queryStr }}"</span>
            </h1>
            <p class="text-slate-400 text-xs mt-1">
                Buscando en la sucursal: <span class="font-bold text-slate-200">{{ $activeGymName }}</span>
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-slate-200 border border-slate-700/50 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Volver al Dashboard
        </a>
    </div>

    @php
        $totalResults = ($clientes->count() ?? 0) + ($personal->count() ?? 0) + ($productos->count() ?? 0) + ($rutinas->count() ?? 0) + ($dietas->count() ?? 0);
        
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
        <div class="py-16 text-center bg-slate-900/20 border border-slate-800/80 rounded-3xl p-8 max-w-lg mx-auto">
            <div class="w-16 h-16 bg-slate-900/80 border border-slate-850 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-500 shadow-xl">
                <i data-lucide="search-x" class="w-8 h-8 text-slate-400"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-200">No se encontraron resultados</h3>
            <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                No pudimos encontrar ningún cliente, personal, producto, rutina o dieta que coincida con "<span class="text-slate-300 font-semibold">{{ $queryStr }}</span>" en <strong class="text-slate-200">{{ $activeGymName }}</strong>.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('clientes.index') }}" class="px-3 py-2 bg-slate-900 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800">Ver Clientes</a>
                <a href="{{ route('tienda.products') }}" class="px-3 py-2 bg-slate-900 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800">Ver Tienda</a>
            </div>
        </div>
    @else
        <!-- Results Grid -->
        <div class="space-y-8">
            
            <!-- SECTION 1: Clientes y Personal (2 Columns Grid) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- COLUMN: Clientes (Atletas) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-lime-400"></i>
                            Atletas / Clientes ({{ $clientes->count() }})
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @forelse($clientes as $cliente)
                            <a href="{{ route('clientes.show', $cliente->id) }}" class="block bg-slate-900/50 border border-slate-800 hover:border-lime-500/30 rounded-2xl p-4 transition-all hover:bg-slate-900/80 group shadow-sm">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-800">
                                    <div class="overflow-hidden flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="font-bold text-slate-200 group-hover:text-lime-400 transition-colors truncate">
                                                {{ $cliente->profile->first_name ?? 'Atleta' }} {{ $cliente->profile->last_name ?? '' }}
                                            </h3>
                                            @if($cliente->activeMembership)
                                                <span class="px-2 py-0.5 text-[9px] font-extrabold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-md shrink-0">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-[9px] font-bold bg-slate-800 text-slate-400 rounded-md shrink-0">
                                                    Sin Membresía
                                                </span>
                                            @endif
                                        </div>
                                        <span class="block text-[11px] text-slate-400 truncate">{{ $cliente->email }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>CI: <strong class="text-slate-300 font-mono">{{ $cliente->profile->dni ?? 'N/D' }}</strong> | Tel: {{ $cliente->profile->phone ?? 'Sin teléfono' }}</span>
                                    <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-400 font-bold uppercase tracking-wider">
                                        {{ $cliente->gym->name ?? 'Global' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                                Ningún atleta coincide con el criterio
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- COLUMN: Personal & Staff -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-4 h-4 text-blue-400"></i>
                            Equipo & Personal ({{ $personal->count() }})
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @forelse($personal as $staffMember)
                            @php
                                $roleDisplay = match($staffMember->role) {
                                    'trainer' => 'Coach / Entrenador',
                                    'cajero' => 'Personal de Caja',
                                    'admin' => 'Administrador',
                                    'superadmin' => 'SuperAdmin Global',
                                    default => ucfirst($staffMember->role)
                                };
                                $roleBadgeColor = match($staffMember->role) {
                                    'trainer' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'cajero' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'admin' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    'superadmin' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    default => 'bg-slate-800 text-slate-300'
                                };
                                $targetUrl = $staffMember->role === 'cajero' ? route('cajeros.index') : route('staff.index');
                            @endphp
                            <a href="{{ $targetUrl }}" class="block bg-slate-900/50 border border-slate-800 hover:border-blue-500/30 rounded-2xl p-4 transition-all hover:bg-slate-900/80 group shadow-sm">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $staffMember->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-800">
                                    <div class="overflow-hidden flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h3 class="font-bold text-slate-200 group-hover:text-blue-400 transition-colors truncate">
                                                {{ $staffMember->profile->first_name ?? 'Usuario' }} {{ $staffMember->profile->last_name ?? '' }}
                                            </h3>
                                            <span class="px-2 py-0.5 text-[9px] font-extrabold border rounded-md shrink-0 {{ $roleBadgeColor }}">
                                                {{ $roleDisplay }}
                                            </span>
                                        </div>
                                        <span class="block text-[11px] text-slate-400 truncate">{{ $staffMember->email }}</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>Tel: {{ $staffMember->profile->phone ?? 'Sin teléfono' }}</span>
                                    <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-400 font-bold uppercase tracking-wider">
                                        {{ $staffMember->gym->name ?? 'Global' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                                Ningún miembro del equipo coincide
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Productos de Tienda, Rutinas y Dietas (3 Columns Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- COLUMN: Productos -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i data-lucide="package" class="w-4 h-4 text-amber-400"></i>
                            Productos de Tienda ({{ $productos->count() }})
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @forelse($productos as $prod)
                            <a href="{{ route('tienda.products') }}" class="block bg-slate-900/50 border border-slate-800 hover:border-amber-500/30 rounded-2xl p-4 transition-all hover:bg-slate-900/80 group">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="overflow-hidden">
                                        <h3 class="font-bold text-slate-200 group-hover:text-amber-400 transition-colors truncate">
                                            {{ $prod->name }}
                                        </h3>
                                        <span class="text-[10px] text-slate-400 block font-mono mt-0.5">SKU: {{ $prod->sku ?? 'S/C' }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-black text-lime-400 bg-lime-500/10 border border-lime-500/20 rounded-lg">
                                        ${{ number_format($prod->price, 2) }}
                                    </span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>Stock: <strong class="{{ $prod->stock_quantity <= $prod->min_stock ? 'text-rose-400' : 'text-slate-200' }}">{{ $prod->stock_quantity }} unid.</strong></span>
                                    <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-400 font-bold uppercase tracking-wider">
                                        {{ $prod->category->name ?? 'General' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                                Ningún producto coincide
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- COLUMN: Rutinas -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i data-lucide="dumbbell" class="w-4 h-4 text-purple-400"></i>
                            Rutinas ({{ $rutinas->count() }})
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @forelse($rutinas as $rutina)
                            <a href="{{ route('rutinas.ejercicios', $rutina->id) }}" class="block bg-slate-900/50 border border-slate-800 hover:border-purple-500/30 rounded-2xl p-4 transition-all hover:bg-slate-900/80 group">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="overflow-hidden">
                                        <h3 class="font-bold text-slate-200 group-hover:text-purple-400 transition-colors truncate">
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
                                    <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-400 font-bold uppercase tracking-wider">
                                        {{ $rutina->gym->name ?? 'Global' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                                Ninguna rutina coincide
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- COLUMN: Dietas (Planes de Nutrición) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h2 class="font-black text-xs uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i data-lucide="apple" class="w-4 h-4 text-emerald-400"></i>
                            Dietas & Nutrición ({{ $dietas->count() }})
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @forelse($dietas as $dieta)
                            <a href="{{ route('nutricion.comidas', $dieta->id) }}" class="block bg-slate-900/50 border border-slate-800 hover:border-emerald-500/30 rounded-2xl p-4 transition-all hover:bg-slate-900/80 group">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="overflow-hidden">
                                        <h3 class="font-bold text-slate-200 group-hover:text-emerald-400 transition-colors truncate">
                                            {{ $dieta->name }}
                                        </h3>
                                        <p class="text-[10px] text-slate-400 line-clamp-2 mt-1">{{ $dieta->description ?? 'Sin descripción' }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md">
                                        {{ $goalsMap[$dieta->goal_type] ?? $dieta->goal_type }}
                                    </span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-slate-950 border border-slate-850 text-slate-400 rounded-md">
                                        {{ $dieta->daily_calories }} kcal
                                    </span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-800/50 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>Duración: {{ $dieta->duration_weeks }} Semanas</span>
                                    <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 rounded text-slate-400 font-bold uppercase tracking-wider">
                                        {{ $dieta->gym->name ?? 'Global' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center italic bg-slate-950/20 rounded-xl border border-slate-900/60">
                                Ningún plan de nutrición coincide
                            </p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    @endif

</div>
@endsection
