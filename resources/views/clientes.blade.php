@extends('layouts.admin')

@section('title', 'Mis Clientes')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Mis Clientes</h1>
            <p class="text-slate-400 text-xs mt-1">Gestiona los atletas, su progreso y planes activos.</p>
        </div>
        <a href="{{ route('clientes.crear') }}" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4 stroke-[3px]"></i>
            Registrar Cliente
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/40 p-4 rounded-2xl border border-slate-800/80">
        <!-- Tabs -->
        <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
            <button onclick="filterClients('all')" id="filter-btn-all" class="filter-tab-btn px-4 py-2 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                Todos ({{ $clientes->count() }})
            </button>
            <button onclick="filterClients('active')" id="filter-btn-active" class="filter-tab-btn px-4 py-2 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                Activos ({{ $clientes->where('is_active', 1)->count() }})
            </button>
            <button onclick="filterClients('no-routine')" id="filter-btn-no-routine" class="filter-tab-btn px-4 py-2 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                Sin Rutina ({{ $clientes->filter(fn($c) => !$c->activeRoutine)->count() }})
            </button>
            <button onclick="filterClients('inactive')" id="filter-btn-inactive" class="filter-tab-btn px-4 py-2 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                Inactivos ({{ $clientes->where('is_active', 0)->count() }})
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-64">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
            <input type="text" placeholder="Buscar cliente..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 focus:ring-1 focus:ring-lime-500/50">
        </div>
    </div>

    <!-- Grid of Clients -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $goalsMap = [
                'lose_weight' => 'Déficit / Pérdida de Peso',
                'gain_muscle' => 'Hipertrofia / Ganancia Muscular',
                'gain_weight' => 'Aumento de Peso',
                'maintain' => 'Mantenimiento / Recomposición',
                'improve_endurance' => 'Resistencia',
                'improve_flexibility' => 'Flexibilidad',
                'general' => 'General'
            ];
        @endphp

        @forelse($clientes as $cliente)
            <!-- Client Card -->
            <div data-client-card data-is-active="{{ $cliente->is_active }}" data-has-routine="{{ $cliente->activeRoutine ? 'true' : 'false' }}" class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700 transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-800">
                            <div>
                                <h3 class="font-bold text-slate-100 flex flex-wrap items-center gap-1.5">
                                    {{ $cliente->profile->first_name ?? 'Atleta' }} {{ $cliente->profile->last_name ?? '' }}
                                    @if($cliente->role === 'superadmin')
                                        <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-purple-500/20 text-purple-400 border border-purple-500/30 rounded-md uppercase tracking-wider">SuperAdmin</span>
                                    @elseif($cliente->role === 'admin')
                                        <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-md uppercase tracking-wider">Admin</span>
                                    @elseif($cliente->role === 'trainer')
                                        <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-lime-500/20 text-lime-400 border border-lime-500/30 rounded-md uppercase tracking-wider">Trainer</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-slate-500/10 text-slate-400 border border-slate-500/20 rounded-md uppercase tracking-wider">Socio</span>
                                    @endif

                                    @if($cliente->is_active)
                                        <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md uppercase tracking-wider">Activo</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-md uppercase tracking-wider">Inactivo</span>
                                    @endif
                                </h3>
                                <span class="text-[10px] text-slate-500">Registro: {{ \Carbon\Carbon::parse($cliente->createdAt)->format('M Y') }}</span>
                            </div>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full {{ $cliente->is_active ? 'bg-emerald-500 ring-4 ring-emerald-500/10' : 'bg-slate-500' }}"></span>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Objetivo:</span>
                            <span class="text-slate-200 font-semibold">
                                @if($cliente->activeRoutine)
                                    {{ $goalsMap[$cliente->activeRoutine->routine->goal_type] ?? 'General' }}
                                @else
                                    Acondicionamiento Físico
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Plan de Rutina:</span>
                            @if($cliente->activeRoutine)
                                <span class="text-lime-400 font-semibold flex items-center gap-1">
                                    <i data-lucide="dumbbell" class="w-3.5 h-3.5"></i> {{ $cliente->activeRoutine->routine->name }}
                                </span>
                            @else
                                <span class="text-slate-500 font-medium italic">Sin Rutina Asignada</span>
                            @endif
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Plan de Dieta:</span>
                            @if($cliente->activeMealPlan)
                                <span class="text-amber-400 font-semibold flex items-center gap-1">
                                    <i data-lucide="apple" class="w-3.5 h-3.5"></i> {{ $cliente->activeMealPlan->mealPlan->name }}
                                </span>
                            @else
                                <span class="text-slate-500 font-medium italic">Sin Plan Asignado</span>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if($cliente->activeRoutine)
                        @php
                            $start = \Carbon\Carbon::parse($cliente->activeRoutine->start_date);
                            $weeksPassed = max(0, $start->diffInWeeks(\Carbon\Carbon::now()));
                            $duration = $cliente->activeRoutine->routine->duration_weeks ?: 1;
                            $progressPct = min(100, round(($weeksPassed / $duration) * 100));
                        @endphp
                        <div class="mt-4 pt-4 border-t border-slate-850/60">
                            <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                                <span>Progreso del ciclo</span>
                                <span>{{ $weeksPassed }} de {{ $duration }} semanas ({{ $progressPct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-lime-500 to-emerald-400 h-full rounded-full" style="width: {{ $progressPct }}%"></div>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 pt-4 border-t border-slate-850/60">
                            <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                                <span>Progreso del ciclo</span>
                                <span>Sin rutina asignada (0%)</span>
                            </div>
                            <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden">
                                <div class="bg-slate-800 h-full" style="width: 0%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-5 flex gap-2">
                    <a href="{{ route('clientes.show', $cliente->id) }}" class="flex-1 py-2 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-lg border border-slate-850 hover:border-slate-700 text-slate-300 transition-colors text-center block">
                        Ver Perfil
                    </a>
                    @if(!$cliente->activeRoutine)
                        <a href="{{ route('clientes.show', $cliente->id) }}" class="px-3 py-2 bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 hover:from-lime-500 hover:to-emerald-500 hover:text-slate-950 text-xs font-bold rounded-lg border border-lime-500/20 transition-all text-center block">
                            Asignar Rutina
                        </a>
                    @else
                        <button class="px-3 py-2 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-lg border border-slate-850 hover:border-slate-700 text-slate-300 transition-colors">
                            <i data-lucide="message-square" class="w-4 h-4"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                <i data-lucide="users" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                <p>No se encontraron clientes registrados.</p>
            </div>
        @endforelse

    </div>
</div>

<script>
    function filterClients(filterType) {
        const cards = document.querySelectorAll('[data-client-card]');
        cards.forEach(card => {
            const isActive = card.getAttribute('data-is-active') === '1';
            const hasRoutine = card.getAttribute('data-has-routine') === 'true';
            
            if (filterType === 'all') {
                card.classList.remove('hidden');
            } else if (filterType === 'active') {
                if (isActive) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            } else if (filterType === 'no-routine') {
                if (!hasRoutine && isActive) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            } else if (filterType === 'inactive') {
                if (!isActive) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            }
        });

        // Update tab styles
        const tabs = document.querySelectorAll('.filter-tab-btn');
        tabs.forEach(tab => {
            tab.className = "filter-tab-btn px-4 py-2 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('filter-btn-' + filterType);
        if (activeTab) {
            activeTab.className = "filter-tab-btn px-4 py-2 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }
    }
</script>
@endsection
