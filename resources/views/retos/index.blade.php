@extends('layouts.admin')

@section('title', 'Retos & Gamificación')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="trophy" class="w-8 h-8 text-lime-400"></i>
                Retos & Gamificación
            </h1>
            <p class="text-slate-400 text-xs mt-1 font-medium">Supervisa los desafíos del gimnasio, asigna recompensas de Experiencia (XP) / Monedas y gestiona el catálogo de logros.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="openAwardAchievementModal()" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-200 hover:text-slate-100 rounded-2xl text-xs font-extrabold transition-all flex items-center gap-2 shadow-lg hover:border-amber-500/30">
                <i data-lucide="medal" class="w-4 h-4 text-amber-400"></i>
                Otorgar Medalla
            </button>
            <button type="button" onclick="openCreateAchievementModal()" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-200 hover:text-slate-100 rounded-2xl text-xs font-extrabold transition-all flex items-center gap-2 shadow-lg hover:border-lime-500/30">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i>
                Nueva Medalla
            </button>
            <button type="button" onclick="openCreateChallengeModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 rounded-2xl text-xs font-black shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
                <i data-lucide="trophy" class="w-4 h-4 stroke-[3px]"></i>
                Crear Reto
            </button>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Desafíos del Gimnasio</span>
                <h3 class="text-2xl font-black text-slate-100"><span id="stat-total-challenges">{{ $challenges->count() }}</span> <span class="text-xs font-normal text-slate-400">Retos</span></h3>
            </div>
            <div class="p-3 bg-lime-500/10 border border-lime-500/20 rounded-2xl text-lime-400">
                <i data-lucide="trophy" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Medallas y Logros</span>
                <h3 class="text-2xl font-black text-amber-400"><span id="stat-total-achievements">{{ $achievements->count() }}</span> <span class="text-xs font-normal text-slate-400">Medallas</span></h3>
            </div>
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400">
                <i data-lucide="award" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Atletas Destacados</span>
                <h3 class="text-2xl font-black text-emerald-400"><span>{{ $leaderboard->count() }}</span> <span class="text-xs font-normal text-slate-400">Top Lideres</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="flame" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="flex border-b border-slate-800/80 gap-2 overflow-x-auto">
        <button 
            type="button"
            id="tab-btn-retos"
            onclick="switchRetosTab('retos')" 
            class="retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-black focus:outline-none transition-all border-lime-500 text-lime-400 whitespace-nowrap">
            <i data-lucide="trophy" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
            Retos del Gimnasio (<span id="count-tab-challenges">{{ $challenges->count() }}</span>)
        </button>
        <button 
            type="button"
            id="tab-btn-leaderboard"
            onclick="switchRetosTab('leaderboard')" 
            class="retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-bold focus:outline-none transition-all border-transparent text-slate-400 hover:text-slate-200 whitespace-nowrap">
            <i data-lucide="flame" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
            Tabla de Clasificación (Ranking)
        </button>
        <button 
            type="button"
            id="tab-btn-medallas"
            onclick="switchRetosTab('medallas')" 
            class="retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-bold focus:outline-none transition-all border-transparent text-slate-400 hover:text-slate-200 whitespace-nowrap">
            <i data-lucide="award" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
            Catálogo de Medallas (<span id="count-tab-achievements">{{ $achievements->count() }}</span>)
        </button>
    </div>

    <!-- ================= PESTAÑA 1: RETOS DE GIMNASIO ================= -->
    <div id="tab-content-retos" class="space-y-6">
        <!-- Header Filters Bar for Challenges -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-300 mr-2 flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Filtro de Desafíos:
                </h3>

                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                    <button type="button" onclick="setChallengeStatusFilter('all')" id="challenge-status-filter-btn-all" class="challenge-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-challenge-status-all">{{ $challenges->count() }}</span>)
                    </button>
                    <button type="button" onclick="setChallengeStatusFilter('1')" id="challenge-status-filter-btn-1" class="challenge-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-challenge-status-active">{{ $challenges->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setChallengeStatusFilter('0')" id="challenge-status-filter-btn-0" class="challenge-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivos (<span id="count-challenge-status-inactive">{{ $challenges->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>
            </div>

            <!-- Search Bar for Challenges -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-challenge-input" oninput="onChallengeFilterChange()" placeholder="Buscar reto por nombre..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <!-- Premium Challenges Grid -->
        <div id="challenges-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($challenges as $challenge)
                <div id="challenge_card_{{ $challenge->id }}"
                     data-challenge-card
                     data-title="{{ strtolower($challenge->title) }}"
                     data-desc="{{ strtolower($challenge->description ?? '') }}"
                     data-active="{{ $challenge->is_active ? 1 : 0 }}"
                     class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm {{ $challenge->is_active ? '' : 'opacity-60 bg-slate-950/40 border-slate-850' }}">
                    
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-2xl bg-gradient-to-br from-lime-500/20 to-emerald-500/10 border border-lime-500/30 text-lime-400 shrink-0 shadow-inner">
                                    <i data-lucide="trophy" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-base text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="challenge_title_{{ $challenge->id }}">{{ $challenge->title }}</h3>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Desafío Oficial</span>
                                </div>
                            </div>
                            <span id="challenge_status_badge_{{ $challenge->id }}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 {{ $challenge->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                {{ $challenge->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 font-medium" id="challenge_desc_{{ $challenge->id }}">{{ $challenge->description ?? 'Sin descripción disponible.' }}</p>
                    </div>

                    <!-- Rewards Pill Container -->
                    <div class="grid grid-cols-2 gap-2.5 p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                        <div class="flex items-center gap-2 p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 font-extrabold text-xs">
                            <i data-lucide="zap" class="w-4 h-4 shrink-0"></i>
                            <span id="challenge_xp_{{ $challenge->id }}">+{{ number_format($challenge->xp_reward) }} XP</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 font-extrabold text-xs">
                            <i data-lucide="coins" class="w-4 h-4 shrink-0"></i>
                            <span id="challenge_token_{{ $challenge->id }}">+{{ number_format($challenge->token_reward, 2) }} Monedas</span>
                        </div>
                    </div>

                    <!-- Dates & Actions Footer -->
                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold text-slate-400">
                        <span class="inline-flex items-center gap-1.5 text-slate-300 font-bold" id="challenge_dates_{{ $challenge->id }}">
                            <i data-lucide="calendar" class="w-4 h-4 text-lime-400"></i>
                            {{ \Carbon\Carbon::parse($challenge->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($challenge->end_date)->format('d/m/Y') }}
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <a href="{{ route('retos.participants', $challenge->id) }}" class="p-2 bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-slate-100 border border-slate-800 rounded-xl transition-all shadow-sm" title="Ver Participantes">
                                <i data-lucide="users" class="w-4 h-4 text-lime-400"></i>
                            </a>
                            <button type="button" onclick='openEditChallengeModal({{ json_encode($challenge) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Reto">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteChallengeModal({{ $challenge->id }}, '{{ addslashes($challenge->title) }}', {{ $challenge->is_active ? 1 : 0 }})" 
                                    id="challenge_toggle_btn_{{ $challenge->id }}"
                                    class="p-2 {{ $challenge->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                    title="{{ $challenge->is_active ? 'Inhabilitar Reto' : 'Reactivar Reto' }}">
                                <i data-lucide="{{ $challenge->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div id="no_challenges_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl">
                    <i data-lucide="trophy" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <p class="font-bold text-slate-400">No hay retos registrados</p>
                    <p class="text-xs text-slate-500 mt-1">Crea tu primer reto haciendo clic en "Crear Reto".</p>
                </div>
            @endforelse

            <div id="no_challenges_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl hidden">
                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                <p class="font-bold text-slate-400 text-sm">No se encontraron retos que coincidan con la búsqueda.</p>
            </div>
        </div>

        <!-- Challenge Pagination Controls Footer -->
        <div id="challenge_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="challenge_pagination_info">Mostrando retos...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="challenge_prev_page_btn" onclick="changeChallengePage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="challenge_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="challenge_next_page_btn" onclick="changeChallengePage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- ================= PESTAÑA 2: TABLA DE CLASIFICACIÓN (RANKING) ================= -->
    <div id="tab-content-leaderboard" class="space-y-6 hidden">
        <!-- Header Filters Bar for Leaderboard -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                    <i data-lucide="flame" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-wider">Tabla de Clasificación de Atletas Top</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Ranking actualizado en tiempo real según XP acumulada.</p>
                </div>
            </div>

            <!-- Search Bar for Leaderboard -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-leaderboard-input" oninput="onLeaderboardFilterChange()" placeholder="Buscar atleta por nombre..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <!-- Leaderboard Table Card -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-extrabold tracking-wider">
                            <th class="py-3.5 px-4 text-center">Posición</th>
                            <th class="py-3.5 px-4">Atleta</th>
                            <th class="py-3.5 px-4 text-center">Puntos de Experiencia</th>
                            <th class="py-3.5 px-4 text-center">Monedas Canjeables</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboard-table-body" class="divide-y divide-slate-800/40 text-xs font-semibold">
                        @forelse($leaderboard as $index => $stat)
                            @php
                                $athleteName = trim(($stat->user->profile->first_name ?? 'Atleta') . ' ' . ($stat->user->profile->last_name ?? ''));
                                $photoUrl = $stat->user->profile->profile_photo ? asset($stat->user->profile->profile_photo) : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
                                $rank = $index + 1;
                            @endphp
                            <tr data-leaderboard-row data-name="{{ strtolower($athleteName) }}" class="hover:bg-slate-850/40 transition-colors">
                                <td class="py-4 px-4 text-center font-black">
                                    @if($rank === 1)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-400 text-xs font-black shadow-md">1º</span>
                                    @elseif($rank === 2)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300/20 border border-slate-300/40 text-slate-300 text-xs font-black shadow-md">2º</span>
                                    @elseif($rank === 3)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700/20 border border-amber-700/40 text-amber-600 text-xs font-black shadow-md">3º</span>
                                    @else
                                        <span class="text-slate-400 text-xs font-bold">#{{ $rank }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $photoUrl }}" class="w-9 h-9 rounded-full object-cover border border-slate-700 shrink-0">
                                        <div class="overflow-hidden min-w-0">
                                            <span class="block font-bold text-slate-100 text-xs truncate">{{ $athleteName }}</span>
                                            <span class="block text-[10px] text-slate-400 truncate">{{ $stat->user->email ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-3 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-black rounded-xl">
                                        ⚡ {{ number_format($stat->total_xp) }} XP
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-black rounded-xl">
                                        🪙 {{ number_format($stat->token_balance, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr id="no_leaderboard_empty">
                                <td colspan="4" class="py-16 text-center text-slate-500">
                                    <i data-lucide="flame" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                    <p class="font-bold text-slate-400">No hay registros en la clasificación aún</p>
                                    <p class="text-xs text-slate-500 mt-1">Los atletas aparecerán aquí a medida que completen retos y ganen XP.</p>
                                </td>
                            </tr>
                        @endforelse

                        <tr id="no_leaderboard_search_row" class="hidden">
                            <td colspan="4" class="py-12 text-center text-slate-500">
                                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400 text-sm">No se encontraron atletas que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Leaderboard Pagination Footer -->
            <div id="leaderboard_pagination_container" class="bg-slate-950/60 border border-slate-850 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                <span id="leaderboard_pagination_info">Mostrando atletas...</span>
                <div class="flex items-center gap-2">
                    <button type="button" id="leaderboard_prev_page_btn" onclick="changeLeaderboardPage(-1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                        Anterior
                    </button>
                    <span id="leaderboard_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                    <button type="button" id="leaderboard_next_page_btn" onclick="changeLeaderboardPage(1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PESTAÑA 3: CATÁLOGO DE MEDALLAS ================= -->
    <div id="tab-content-medallas" class="space-y-6 hidden">
        <!-- Header Filters Bar for Achievements -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-300 mr-2 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-lime-400"></i> Catálogo de Medallas:
                </h3>

                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded-2xl border border-slate-855">
                    <button type="button" onclick="setAchievementStatusFilter('all')" id="achievement-status-filter-btn-all" class="achievement-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800">
                        Todas (<span id="count-achievement-status-all">{{ $achievements->count() }}</span>)
                    </button>
                    <button type="button" onclick="setAchievementStatusFilter('1')" id="achievement-status-filter-btn-1" class="achievement-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activas (<span id="count-achievement-status-active">{{ $achievements->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setAchievementStatusFilter('0')" id="achievement-status-filter-btn-0" class="achievement-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivas (<span id="count-achievement-status-inactive">{{ $achievements->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>
            </div>

            <!-- Search Bar for Achievements -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-achievement-input" oninput="onAchievementFilterChange()" placeholder="Buscar medalla por nombre..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>

        <!-- Premium Achievements Grid -->
        <div id="achievements-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $condMap = [
                    'workouts_completed' => 'Sesiones Completadas',
                    'consecutive_days' => 'Días Consecutivos',
                    'challenges_won' => 'Retos Superados',
                    'special_event' => 'Evento Especial'
                ];
            @endphp
            @forelse($achievements as $ach)
                @php
                    $condLabel = $condMap[$ach->condition_type] ?? $ach->condition_type;
                @endphp
                <div id="achievement_card_{{ $ach->id }}"
                     data-achievement-card
                     data-name="{{ strtolower($ach->name) }}"
                     data-desc="{{ strtolower($ach->description ?? '') }}"
                     data-active="{{ $ach->is_active ? 1 : 0 }}"
                     class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-amber-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm {{ $ach->is_active ? '' : 'opacity-60 bg-slate-950/40 border-slate-850' }}">
                    
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-2xl bg-gradient-to-br from-amber-500/20 to-yellow-500/10 border border-amber-500/30 text-amber-400 shrink-0 shadow-inner">
                                    <i data-lucide="{{ $ach->icon_url ?? 'award' }}" class="w-6 h-6"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-base text-slate-100 group-hover:text-amber-400 transition-colors truncate" id="achievement_name_{{ $ach->id }}">{{ $ach->name }}</h3>
                                    <span class="block text-[10px] text-amber-400/90 font-extrabold uppercase tracking-wider mt-0.5" id="achievement_cond_{{ $ach->id }}">{{ $condLabel }} • Meta: {{ $ach->target_value }}</span>
                                </div>
                            </div>
                            <span id="achievement_status_badge_{{ $ach->id }}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 {{ $ach->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                {{ $ach->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 font-medium" id="achievement_desc_{{ $ach->id }}">{{ $ach->description ?? 'Sin descripción disponible.' }}</p>
                    </div>

                    <!-- Rewards Pill Container -->
                    <div class="grid grid-cols-2 gap-2.5 p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                        <div class="flex items-center gap-2 p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 font-extrabold text-xs">
                            <i data-lucide="zap" class="w-4 h-4 shrink-0"></i>
                            <span id="achievement_xp_{{ $ach->id }}">+{{ number_format($ach->xp_reward) }} XP</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 font-extrabold text-xs">
                            <i data-lucide="coins" class="w-4 h-4 shrink-0"></i>
                            <span id="achievement_token_{{ $ach->id }}">+{{ number_format($ach->token_reward, 2) }} Monedas</span>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold text-slate-400">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                            {{ $ach->gym_id ? 'Medalla de Sucursal' : 'Medalla Global' }}
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditAchievementModal({{ json_encode($ach) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Medalla">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteAchievementModal({{ $ach->id }}, '{{ addslashes($ach->name) }}', {{ $ach->is_active ? 1 : 0 }})" 
                                    id="achievement_toggle_btn_{{ $ach->id }}"
                                    class="p-2 {{ $ach->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                    title="{{ $ach->is_active ? 'Inhabilitar Medalla' : 'Reactivar Medalla' }}">
                                <i data-lucide="{{ $ach->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div id="no_achievements_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl">
                    <i data-lucide="award" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <p class="font-bold text-slate-400">No hay medallas configuradas</p>
                    <p class="text-xs text-slate-500 mt-1">Crea tu primer logro haciendo clic en "Nueva Medalla".</p>
                </div>
            @endforelse

            <div id="no_achievements_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl hidden">
                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                <p class="font-bold text-slate-400 text-sm">No se encontraron medallas que coincidan con la búsqueda.</p>
            </div>
        </div>

        <!-- Achievement Pagination Controls Footer -->
        <div id="achievement_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="achievement_pagination_info">Mostrando medallas...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="achievement_prev_page_btn" onclick="changeAchievementPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="achievement_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="achievement_next_page_btn" onclick="changeAchievementPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ================= MODAL: CREAR RETO ================= -->
<div id="modal-create-challenge" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="trophy" class="w-4 h-4 text-lime-400"></i> Crear Nuevo Reto del Gimnasio
            </h3>
            <button type="button" onclick="toggleModal('modal-create-challenge')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-challenge-form" action="{{ route('retos.store_challenge') }}" method="POST" onsubmit="submitCreateChallenge(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="create_challenge_title" class="block text-slate-400 uppercase tracking-wider mb-1.5">Título del Reto *</label>
                <input type="text" name="title" id="create_challenge_title" required placeholder="Ej: Maratón de 100K Calorías, Desafío de Fuerza..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="create_challenge_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="create_challenge_description" rows="3" placeholder="Detalla los requisitos para completar este reto..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_challenge_start_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Inicio *</label>
                    <input type="date" name="start_date" id="create_challenge_start_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
                <div>
                    <label for="create_challenge_end_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Fin *</label>
                    <input type="date" name="end_date" id="create_challenge_end_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_challenge_xp_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (XP) *</label>
                    <input type="number" name="xp_reward" id="create_challenge_xp_reward" required min="0" placeholder="Ej: 500" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_challenge_token_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (Monedas) *</label>
                    <input type="number" step="0.01" name="token_reward" id="create_challenge_token_reward" required min="0" placeholder="Ej: 25.00" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-challenge')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-challenge-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Reto</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR RETO ================= -->
<div id="modal-edit-challenge" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Reto del Gimnasio
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-challenge')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-challenge-form" action="" method="POST" onsubmit="submitEditChallenge(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_challenge_title" class="block text-slate-400 uppercase tracking-wider mb-1.5">Título del Reto *</label>
                <input type="text" name="title" id="edit_challenge_title" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="edit_challenge_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="edit_challenge_description" rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_challenge_start_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Inicio *</label>
                    <input type="date" name="start_date" id="edit_challenge_start_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
                <div>
                    <label for="edit_challenge_end_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Fin *</label>
                    <input type="date" name="end_date" id="edit_challenge_end_date" required onclick="this.showPicker()" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_challenge_xp_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (XP) *</label>
                    <input type="number" name="xp_reward" id="edit_challenge_xp_reward" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_challenge_token_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (Monedas) *</label>
                    <input type="number" step="0.01" name="token_reward" id="edit_challenge_token_reward" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-challenge')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-challenge-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE RETO ================= -->
<div id="modal-delete-challenge" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-challenge-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-challenge-status-title">Cambiar Estado del Reto</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-delete-challenge')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-challenge-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de este reto?
        </p>

        <form id="delete-challenge-form" action="" method="POST" onsubmit="submitDeleteChallenge(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-challenge')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-challenge-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-challenge-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: CREAR MEDALLA / LOGRO ================= -->
<div id="modal-create-achievement" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-amber-400"></i> Crear Nueva Medalla de Logro
            </h3>
            <button type="button" onclick="toggleModal('modal-create-achievement')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-achievement-form" action="{{ route('retos.store_achievement') }}" method="POST" onsubmit="submitCreateAchievement(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="create_ach_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Medalla *</label>
                <input type="text" name="name" id="create_ach_name" required placeholder="Ej: Leyenda del Gimnasio, Rey del Cardio..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="create_ach_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="create_ach_description" rows="2" placeholder="Explica cómo se desbloquea esta medalla..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_ach_condition_type" class="block text-slate-400 uppercase tracking-wider mb-1.5">Tipo de Condición *</label>
                    <select name="condition_type" id="create_ach_condition_type" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="workouts_completed">Sesiones Completadas</option>
                        <option value="consecutive_days">Días Consecutivos</option>
                        <option value="challenges_won">Retos Superados</option>
                        <option value="special_event">Evento Especial</option>
                    </select>
                </div>
                <div>
                    <label for="create_ach_target_value" class="block text-slate-400 uppercase tracking-wider mb-1.5">Valor Meta *</label>
                    <input type="number" name="target_value" id="create_ach_target_value" required min="1" placeholder="Ej: 10" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_ach_xp_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (XP) *</label>
                    <input type="number" name="xp_reward" id="create_ach_xp_reward" required min="0" placeholder="Ej: 200" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_ach_token_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (Monedas) *</label>
                    <input type="number" step="0.01" name="token_reward" id="create_ach_token_reward" required min="0" placeholder="Ej: 10.00" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label for="create_ach_icon" class="block text-slate-400 uppercase tracking-wider mb-1.5">Icono de la Medalla</label>
                <select name="icon_url" id="create_ach_icon" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="award">Medalla (Insignia)</option>
                    <option value="trophy">Trofeo de Victoria</option>
                    <option value="crown">Corona Imperial</option>
                    <option value="zap">Rayo de Energía</option>
                    <option value="flame">Fuego Intenso</option>
                    <option value="star">Estrella Brillante</option>
                    <option value="shield">Escudo de Protección</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-achievement')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-achievement-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Medalla</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR MEDALLA ================= -->
<div id="modal-edit-achievement" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Medalla de Logro
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-achievement')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-achievement-form" action="" method="POST" onsubmit="submitEditAchievement(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_ach_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Medalla *</label>
                <input type="text" name="name" id="edit_ach_name" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="edit_ach_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="edit_ach_description" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_ach_condition_type" class="block text-slate-400 uppercase tracking-wider mb-1.5">Tipo de Condición *</label>
                    <select name="condition_type" id="edit_ach_condition_type" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="workouts_completed">Sesiones Completadas</option>
                        <option value="consecutive_days">Días Consecutivos</option>
                        <option value="challenges_won">Retos Superados</option>
                        <option value="special_event">Evento Especial</option>
                    </select>
                </div>
                <div>
                    <label for="edit_ach_target_value" class="block text-slate-400 uppercase tracking-wider mb-1.5">Valor Meta *</label>
                    <input type="number" name="target_value" id="edit_ach_target_value" required min="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_ach_xp_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (XP) *</label>
                    <input type="number" name="xp_reward" id="edit_ach_xp_reward" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_ach_token_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Recompensa (Monedas) *</label>
                    <input type="number" step="0.01" name="token_reward" id="edit_ach_token_reward" required min="0" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label for="edit_ach_icon" class="block text-slate-400 uppercase tracking-wider mb-1.5">Icono de la Medalla</label>
                <select name="icon_url" id="edit_ach_icon" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="award">Medalla (Insignia)</option>
                    <option value="trophy">Trofeo de Victoria</option>
                    <option value="crown">Corona Imperial</option>
                    <option value="zap">Rayo de Energía</option>
                    <option value="flame">Fuego Intenso</option>
                    <option value="star">Estrella Brillante</option>
                    <option value="shield">Escudo de Protección</option>
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-achievement')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-achievement-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE MEDALLA ================= -->
<div id="modal-delete-achievement" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-ach-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-ach-status-title">Cambiar Estado de la Medalla</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-delete-achievement')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-ach-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de esta medalla?
        </p>

        <form id="delete-achievement-form" action="" method="POST" onsubmit="submitDeleteAchievement(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-achievement')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-achievement-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-ach-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: OTORGAR MEDALLA A ATLETA ================= -->
<div id="modal-award-achievement" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="medal" class="w-4 h-4 text-amber-400"></i> Otorgar Medalla a Atleta
            </h3>
            <button type="button" onclick="toggleModal('modal-award-achievement')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="award-achievement-form" action="{{ route('retos.award_achievement') }}" method="POST" onsubmit="submitAwardAchievement(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="award_user_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Atleta *</label>
                <select name="user_id" id="award_user_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Busca o selecciona un atleta...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">
                            {{ $client->profile->first_name ?? 'Atleta' }} {{ $client->profile->last_name ?? '' }} ({{ $client->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="award_achievement_definition_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Medalla *</label>
                <select name="achievement_definition_id" id="award_achievement_definition_id" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un logro a otorgar...</option>
                    @foreach($achievements as $ach)
                        @if($ach->is_active)
                            <option value="{{ $ach->id }}">{{ $ach->name }} (+{{ number_format($ach->xp_reward) }} XP, +{{ number_format($ach->token_reward, 2) }} Monedas)</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-award-achievement')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="award-achievement-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-95 transition-all">Otorgar Medalla</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Condition Type Spanish Translation Map
    const conditionMap = {
        'workouts_completed': 'Sesiones Completadas',
        'consecutive_days': 'Días Consecutivos',
        'challenges_won': 'Retos Superados',
        'special_event': 'Evento Especial'
    };

    function getConditionLabel(cond) {
        return conditionMap[cond] || cond;
    }

    // Floating Toast Notifications System
    function showToast(message, type = 'success') {
        let container = document.getElementById('retos-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'retos-toast-container';
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

    // Tabs Switcher
    function switchRetosTab(tabName) {
        const btnRetos = document.getElementById('tab-btn-retos');
        const btnLeaderboard = document.getElementById('tab-btn-leaderboard');
        const btnMedallas = document.getElementById('tab-btn-medallas');

        const contentRetos = document.getElementById('tab-content-retos');
        const contentLeaderboard = document.getElementById('tab-content-leaderboard');
        const contentMedallas = document.getElementById('tab-content-medallas');

        const tabBtns = [btnRetos, btnLeaderboard, btnMedallas];
        const tabContents = [contentRetos, contentLeaderboard, contentMedallas];

        tabBtns.forEach(btn => {
            if (btn) btn.className = "retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-bold focus:outline-none transition-all border-transparent text-slate-400 hover:text-slate-200 whitespace-nowrap";
        });
        tabContents.forEach(cnt => {
            if (cnt) cnt.classList.add('hidden');
        });

        if (tabName === 'retos') {
            btnRetos.className = "retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-black focus:outline-none transition-all border-lime-500 text-lime-400 whitespace-nowrap";
            contentRetos.classList.remove('hidden');
            renderChallengesPage();
        } else if (tabName === 'leaderboard') {
            btnLeaderboard.className = "retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-black focus:outline-none transition-all border-lime-500 text-lime-400 whitespace-nowrap";
            contentLeaderboard.classList.remove('hidden');
            renderLeaderboardPage();
        } else if (tabName === 'medallas') {
            btnMedallas.className = "retos-tab-btn px-6 py-3.5 border-b-2 text-xs uppercase tracking-wider font-black focus:outline-none transition-all border-lime-500 text-lime-400 whitespace-nowrap";
            contentMedallas.classList.remove('hidden');
            renderAchievementsPage();
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

    function openCreateChallengeModal() {
        document.getElementById('create-challenge-form').reset();
        toggleModal('modal-create-challenge');
    }

    function openEditChallengeModal(item) {
        document.getElementById('edit-challenge-form').action = `/retos/${item.id}`;
        document.getElementById('edit_challenge_title').value = item.title;
        document.getElementById('edit_challenge_description').value = item.description || '';
        document.getElementById('edit_challenge_start_date').value = item.start_date;
        document.getElementById('edit_challenge_end_date').value = item.end_date;
        document.getElementById('edit_challenge_xp_reward').value = item.xp_reward;
        document.getElementById('edit_challenge_token_reward').value = item.token_reward;

        toggleModal('modal-edit-challenge');
    }

    function openDeleteChallengeModal(id, title, isActive) {
        document.getElementById('delete-challenge-form').action = `/retos/${id}`;
        const titleEl = document.getElementById('modal-challenge-status-title');
        const descEl = document.getElementById('modal-challenge-status-desc');
        const btnTextEl = document.getElementById('modal-challenge-status-btn-text');
        const submitBtn = document.getElementById('delete-challenge-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Reto';
            descEl.innerHTML = `¿Estás seguro de que deseas marcar como <strong>inactivo</strong> el reto (<strong class="text-slate-100">${escapeHtml(title)}</strong>)? No estará visible para nuevos participantes.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Reto';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> el reto (<strong class="text-slate-100">${escapeHtml(title)}</strong>)?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-delete-challenge');
    }

    function openCreateAchievementModal() {
        document.getElementById('create-achievement-form').reset();
        toggleModal('modal-create-achievement');
    }

    function openEditAchievementModal(item) {
        document.getElementById('edit-achievement-form').action = `/retos/medallas/${item.id}`;
        document.getElementById('edit_ach_name').value = item.name;
        document.getElementById('edit_ach_description').value = item.description || '';
        document.getElementById('edit_ach_condition_type').value = item.condition_type;
        document.getElementById('edit_ach_target_value').value = item.target_value;
        document.getElementById('edit_ach_xp_reward').value = item.xp_reward;
        document.getElementById('edit_ach_token_reward').value = item.token_reward;
        if (item.icon_url) document.getElementById('edit_ach_icon').value = item.icon_url;

        toggleModal('modal-edit-achievement');
    }

    function openDeleteAchievementModal(id, name, isActive) {
        document.getElementById('delete-achievement-form').action = `/retos/medallas/${id}`;
        const titleEl = document.getElementById('modal-ach-status-title');
        const descEl = document.getElementById('modal-ach-status-desc');
        const btnTextEl = document.getElementById('modal-ach-status-btn-text');
        const submitBtn = document.getElementById('delete-achievement-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Medalla';
            descEl.innerHTML = `¿Estás seguro de que deseas marcar como <strong>inactiva</strong> la medalla (<strong class="text-slate-100">${escapeHtml(name)}</strong>)?`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Medalla';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> la medalla (<strong class="text-slate-100">${escapeHtml(name)}</strong>)?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-delete-achievement');
    }

    function openAwardAchievementModal() {
        document.getElementById('award-achievement-form').reset();
        toggleModal('modal-award-achievement');
    }

    // Dynamic Leaderboard Re-renderer (AJAX)
    function renderLeaderboardTable(leaderboardData) {
        if (!leaderboardData || !Array.isArray(leaderboardData)) return;

        const tbody = document.getElementById('leaderboard-table-body');
        if (!tbody) return;

        if (leaderboardData.length === 0) {
            tbody.innerHTML = `
                <tr id="no_leaderboard_empty">
                    <td colspan="4" class="py-16 text-center text-slate-500">
                        <i data-lucide="flame" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                        <p class="font-bold text-slate-400">No hay registros en la clasificación aún</p>
                        <p class="text-xs text-slate-500 mt-1">Los atletas aparecerán aquí a medida que completen retos y ganen XP.</p>
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = leaderboardData.map((stat, idx) => {
                const rank = idx + 1;
                let rankBadge = `<span class="text-slate-400 text-xs font-bold">#${rank}</span>`;

                if (rank === 1) {
                    rankBadge = `<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-400 text-xs font-black shadow-md">1º</span>`;
                } else if (rank === 2) {
                    rankBadge = `<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300/20 border border-slate-300/40 text-slate-300 text-xs font-black shadow-md">2º</span>`;
                } else if (rank === 3) {
                    rankBadge = `<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700/20 border border-amber-700/40 text-amber-600 text-xs font-black shadow-md">3º</span>`;
                }

                const safeName = escapeHtml(stat.name);
                const safeEmail = escapeHtml(stat.email);
                const xpFormatted = new Intl.NumberFormat().format(stat.total_xp);
                const tokenFormatted = parseFloat(stat.token_balance).toFixed(2);

                return `
                    <tr data-leaderboard-row data-name="${safeName.toLowerCase()}" class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-4 px-4 text-center font-black">
                            ${rankBadge}
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <img src="${stat.photo}" class="w-9 h-9 rounded-full object-cover border border-slate-700 shrink-0">
                                <div class="overflow-hidden min-w-0">
                                    <span class="block font-bold text-slate-100 text-xs truncate">${safeName}</span>
                                    <span class="block text-[10px] text-slate-400 truncate">${safeEmail}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-black rounded-xl">
                                ⚡ ${xpFormatted} XP
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-black rounded-xl">
                                🪙 ${tokenFormatted}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        const noSearchRow = document.createElement('tr');
        noSearchRow.id = 'no_leaderboard_search_row';
        noSearchRow.className = 'hidden';
        noSearchRow.innerHTML = `
            <td colspan="4" class="py-12 text-center text-slate-500">
                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                <p class="font-bold text-slate-400 text-sm">No se encontraron atletas que coincidan con la búsqueda.</p>
            </td>
        `;
        tbody.appendChild(noSearchRow);

        if (window.lucide) window.lucide.createIcons();
        renderLeaderboardPage();
    }

    // AJAX Submission: Create Challenge
    async function submitCreateChallenge(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-challenge-submit-btn');

        setBtnLoading(submitBtn, true, 'Creando...');

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
                const c = data.challenge;
                const container = document.getElementById('challenges-grid-container');
                const emptyMsg = document.getElementById('no_challenges_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const cJsonStr = JSON.stringify(c).replace(/'/g, "&#39;");
                const safeTitle = escapeHtml(c.title);
                const safeDesc = escapeHtml(c.description || 'Sin descripción disponible.');

                const card = document.createElement('div');
                card.id = `challenge_card_${c.id}`;
                card.setAttribute('data-challenge-card', '');
                card.setAttribute('data-title', (c.title || '').toLowerCase());
                card.setAttribute('data-desc', (c.description || '').toLowerCase());
                card.setAttribute('data-active', '1');
                card.className = 'bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm';

                card.innerHTML = `
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-2xl bg-gradient-to-br from-lime-500/20 to-emerald-500/10 border border-lime-500/30 text-lime-400 shrink-0 shadow-inner">
                                    <i data-lucide="trophy" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-base text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="challenge_title_${c.id}">${safeTitle}</h3>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Desafío Oficial</span>
                                </div>
                            </div>
                            <span id="challenge_status_badge_${c.id}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                Activo
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 font-medium" id="challenge_desc_${c.id}">${safeDesc}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                        <div class="flex items-center gap-2 p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 font-extrabold text-xs">
                            <i data-lucide="zap" class="w-4 h-4 shrink-0"></i>
                            <span id="challenge_xp_${c.id}">+${c.xp_reward} XP</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 font-extrabold text-xs">
                            <i data-lucide="coins" class="w-4 h-4 shrink-0"></i>
                            <span id="challenge_token_${c.id}">+${parseFloat(c.token_reward).toFixed(2)} Monedas</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold text-slate-400">
                        <span class="inline-flex items-center gap-1.5 text-slate-300 font-bold" id="challenge_dates_${c.id}">
                            <i data-lucide="calendar" class="w-4 h-4 text-lime-400"></i>
                            ${c.start_date} - ${c.end_date}
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <a href="/retos/${c.id}/participantes" class="p-2 bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-slate-100 border border-slate-800 rounded-xl transition-all shadow-sm" title="Ver Participantes">
                                <i data-lucide="users" class="w-4 h-4 text-lime-400"></i>
                            </a>
                            <button type="button" onclick='openEditChallengeModal(${cJsonStr})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Reto">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteChallengeModal(${c.id}, '${safeTitle.replace(/'/g, "\\'")}', 1)" id="challenge_toggle_btn_${c.id}" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Reto">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;

                container.prepend(card);

                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-challenge');
                updateCounters();
                renderChallengesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al crear el reto.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar crear el reto.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Challenge
    async function submitEditChallenge(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-challenge-submit-btn');

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
                const c = data.challenge;
                const card = document.getElementById(`challenge_card_${c.id}`);

                if (card) {
                    card.setAttribute('data-title', (c.title || '').toLowerCase());
                    card.setAttribute('data-desc', (c.description || '').toLowerCase());

                    const titleEl = document.getElementById(`challenge_title_${c.id}`);
                    const descEl = document.getElementById(`challenge_desc_${c.id}`);
                    const xpEl = document.getElementById(`challenge_xp_${c.id}`);
                    const tokenEl = document.getElementById(`challenge_token_${c.id}`);
                    const datesEl = document.getElementById(`challenge_dates_${c.id}`);

                    if (titleEl) titleEl.textContent = c.title;
                    if (descEl) descEl.textContent = c.description || 'Sin descripción disponible.';
                    if (xpEl) xpEl.textContent = `+${c.xp_reward} XP`;
                    if (tokenEl) tokenEl.textContent = `+${parseFloat(c.token_reward).toFixed(2)} Monedas`;
                    if (datesEl) datesEl.innerHTML = `<i data-lucide="calendar" class="w-4 h-4 text-lime-400"></i> ${c.start_date} - ${c.end_date}`;
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-edit-challenge');
                renderChallengesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar el reto.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el reto.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Challenge Status
    async function submitDeleteChallenge(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-challenge-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

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
                const cId = data.challenge_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const card = document.getElementById(`challenge_card_${cId}`);

                if (card) {
                    card.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        card.classList.remove('opacity-60', 'bg-slate-950/40', 'border-slate-850');
                    } else {
                        card.classList.add('opacity-60', 'bg-slate-950/40', 'border-slate-850');
                    }

                    const badge = document.getElementById(`challenge_status_badge_${cId}`);
                    if (badge) {
                        badge.className = `px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 ${newActiveStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'}`;
                        badge.textContent = newActiveStatus ? 'Activo' : 'Inactivo';
                    }

                    const toggleBtn = document.getElementById(`challenge_toggle_btn_${cId}`);
                    const titleText = document.getElementById(`challenge_title_${cId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openDeleteChallengeModal(cId, titleText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Reto' : 'Reactivar Reto';
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-delete-challenge');
                updateCounters();
                renderChallengesPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado del reto.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado del reto.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Create Achievement
    async function submitCreateAchievement(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-achievement-submit-btn');

        setBtnLoading(submitBtn, true, 'Creando...');

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
                const ach = data.achievement;
                const container = document.getElementById('achievements-grid-container');
                const emptyMsg = document.getElementById('no_achievements_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const achJsonStr = JSON.stringify(ach).replace(/'/g, "&#39;");
                const safeName = escapeHtml(ach.name);
                const safeDesc = escapeHtml(ach.description || 'Sin descripción.');
                const iconName = ach.icon_url || 'award';
                const condLabel = getConditionLabel(ach.condition_type);

                const card = document.createElement('div');
                card.id = `achievement_card_${ach.id}`;
                card.setAttribute('data-achievement-card', '');
                card.setAttribute('data-name', (ach.name || '').toLowerCase());
                card.setAttribute('data-desc', (ach.description || '').toLowerCase());
                card.setAttribute('data-active', '1');
                card.className = 'bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-amber-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm';

                card.innerHTML = `
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-2xl bg-gradient-to-br from-amber-500/20 to-yellow-500/10 border border-amber-500/30 text-amber-400 shrink-0 shadow-inner">
                                    <i data-lucide="${iconName}" class="w-6 h-6"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-base text-slate-100 group-hover:text-amber-400 transition-colors truncate" id="achievement_name_${ach.id}">${safeName}</h3>
                                    <span class="block text-[10px] text-amber-400/90 font-extrabold uppercase tracking-wider mt-0.5" id="achievement_cond_${ach.id}">${condLabel} • Meta: ${ach.target_value}</span>
                                </div>
                            </div>
                            <span id="achievement_status_badge_${ach.id}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                Activa
                            </span>
                        </div>
                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-3 font-medium" id="achievement_desc_${ach.id}">${safeDesc}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 p-3 bg-slate-950/70 border border-slate-850 rounded-2xl">
                        <div class="flex items-center gap-2 p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 font-extrabold text-xs">
                            <i data-lucide="zap" class="w-4 h-4 shrink-0"></i>
                            <span id="achievement_xp_${ach.id}">+${ach.xp_reward} XP</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 font-extrabold text-xs">
                            <i data-lucide="coins" class="w-4 h-4 shrink-0"></i>
                            <span id="achievement_token_${ach.id}">+${parseFloat(ach.token_reward).toFixed(2)} Monedas</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold text-slate-400">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                            ${ach.gym_id ? 'Medalla de Sucursal' : 'Medalla Global'}
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick='openEditAchievementModal(${achJsonStr})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Medalla">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="openDeleteAchievementModal(${ach.id}, '${safeName.replace(/'/g, "\\'")}', 1)" id="achievement_toggle_btn_${ach.id}" class="p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Medalla">
                                <i data-lucide="power" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                `;

                container.prepend(card);

                // Add to Award select dropdown if element exists
                const awardSel = document.getElementById('award_achievement_definition_id');
                if (awardSel) {
                    const opt = document.createElement('option');
                    opt.value = ach.id;
                    opt.textContent = `${ach.name} (+${ach.xp_reward} XP, +${ach.token_reward} Monedas)`;
                    awardSel.appendChild(opt);
                }

                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('modal-create-achievement');
                updateCounters();
                renderAchievementsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al crear la medalla.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar crear la medalla.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Achievement
    async function submitEditAchievement(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-achievement-submit-btn');

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
                const ach = data.achievement;
                const card = document.getElementById(`achievement_card_${ach.id}`);

                if (card) {
                    card.setAttribute('data-name', (ach.name || '').toLowerCase());
                    card.setAttribute('data-desc', (ach.description || '').toLowerCase());

                    const nameEl = document.getElementById(`achievement_name_${ach.id}`);
                    const descEl = document.getElementById(`achievement_desc_${ach.id}`);
                    const condEl = document.getElementById(`achievement_cond_${ach.id}`);
                    const xpEl = document.getElementById(`achievement_xp_${ach.id}`);
                    const tokenEl = document.getElementById(`achievement_token_${ach.id}`);

                    if (nameEl) nameEl.textContent = ach.name;
                    if (descEl) descEl.textContent = ach.description || 'Sin descripción disponible.';
                    if (condEl) condEl.textContent = `${getConditionLabel(ach.condition_type)} • Meta: ${ach.target_value}`;
                    if (xpEl) xpEl.textContent = `+${ach.xp_reward} XP`;
                    if (tokenEl) tokenEl.textContent = `+${parseFloat(ach.token_reward).toFixed(2)} Monedas`;
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-edit-achievement');
                renderAchievementsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar la medalla.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar la medalla.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Achievement Status
    async function submitDeleteAchievement(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-achievement-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

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
                const achId = data.achievement_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const card = document.getElementById(`achievement_card_${achId}`);

                if (card) {
                    card.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        card.classList.remove('opacity-60', 'bg-slate-950/40', 'border-slate-850');
                    } else {
                        card.classList.add('opacity-60', 'bg-slate-950/40', 'border-slate-850');
                    }

                    const badge = document.getElementById(`achievement_status_badge_${achId}`);
                    if (badge) {
                        badge.className = `px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 ${newActiveStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'}`;
                        badge.textContent = newActiveStatus ? 'Activa' : 'Inactiva';
                    }

                    const toggleBtn = document.getElementById(`achievement_toggle_btn_${achId}`);
                    const nameText = document.getElementById(`achievement_name_${achId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openDeleteAchievementModal(achId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Medalla' : 'Reactivar Medalla';
                        toggleBtn.className = `p-2 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('modal-delete-achievement');
                updateCounters();
                renderAchievementsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado de la medalla.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado de la medalla.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Award Achievement Manually (With Leaderboard Dynamic Reload)
    async function submitAwardAchievement(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('award-achievement-submit-btn');

        setBtnLoading(submitBtn, true, 'Otorgando...');

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
                toggleModal('modal-award-achievement');
                form.reset();

                // Dynamically re-render Leaderboard if data is returned!
                if (data.leaderboard) {
                    renderLeaderboardTable(data.leaderboard);
                }

                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al otorgar la medalla.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al otorgar la medalla.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Update Counters UI
    function updateCounters() {
        const challengeCards = document.querySelectorAll('[data-challenge-card]');
        const achievementCards = document.querySelectorAll('[data-achievement-card]');

        let countActiveChallenges = 0;
        let countInactiveChallenges = 0;
        challengeCards.forEach(c => {
            if (c.getAttribute('data-active') === '1') countActiveChallenges++;
            else countInactiveChallenges++;
        });

        let countActiveAchievements = 0;
        let countInactiveAchievements = 0;
        achievementCards.forEach(a => {
            if (a.getAttribute('data-active') === '1') countActiveAchievements++;
            else countInactiveAchievements++;
        });

        const statChallenges = document.getElementById('stat-total-challenges');
        const statAchievements = document.getElementById('stat-total-achievements');

        const tabCountChallenges = document.getElementById('count-tab-challenges');
        const tabCountAchievements = document.getElementById('count-tab-achievements');

        if (statChallenges) statChallenges.textContent = challengeCards.length;
        if (statAchievements) statAchievements.textContent = achievementCards.length;
        if (tabCountChallenges) tabCountChallenges.textContent = challengeCards.length;
        if (tabCountAchievements) tabCountAchievements.textContent = achievementCards.length;

        const cAllCh = document.getElementById('count-challenge-status-all');
        const cActCh = document.getElementById('count-challenge-status-active');
        const cInactCh = document.getElementById('count-challenge-status-inactive');
        if (cAllCh) cAllCh.textContent = challengeCards.length;
        if (cActCh) cActCh.textContent = countActiveChallenges;
        if (cInactCh) cInactCh.textContent = countInactiveChallenges;

        const cAllAch = document.getElementById('count-achievement-status-all');
        const cActAch = document.getElementById('count-achievement-status-active');
        const cInactAch = document.getElementById('count-achievement-status-inactive');
        if (cAllAch) cAllAch.textContent = achievementCards.length;
        if (cActAch) cActAch.textContent = countActiveAchievements;
        if (cInactAch) cInactAch.textContent = countInactiveAchievements;
    }

    // Challenge Tab Filtering & Pagination (6 cards per page)
    let currentChallengePage = 1;
    let currentChallengeStatusFilter = 'all';
    const challengeItemsPerPage = 6;

    function setChallengeStatusFilter(status) {
        currentChallengeStatusFilter = status;

        const tabs = document.querySelectorAll('.challenge-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "challenge-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('challenge-status-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "challenge-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentChallengePage = 1;
        renderChallengesPage();
    }

    function onChallengeFilterChange() {
        currentChallengePage = 1;
        renderChallengesPage();
    }

    function renderChallengesPage() {
        const searchVal = (document.getElementById('search-challenge-input')?.value || '').toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('[data-challenge-card]'));

        const filtered = cards.filter(c => {
            const title = c.getAttribute('data-title') || '';
            const desc = c.getAttribute('data-desc') || '';
            const isActive = c.getAttribute('data-active') || '1';

            const matchesStatus = (currentChallengeStatusFilter === 'all') || (isActive === currentChallengeStatusFilter);
            const matchesSearch = !searchVal || title.includes(searchVal) || desc.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / challengeItemsPerPage) || 1;

        if (currentChallengePage > totalPages) currentChallengePage = totalPages;
        if (currentChallengePage < 1) currentChallengePage = 1;

        const startIndex = (currentChallengePage - 1) * challengeItemsPerPage;
        const endIndex = startIndex + challengeItemsPerPage;

        cards.forEach(c => c.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(c => c.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_challenges_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && cards.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('challenge_pagination_info');
        const pageSpan = document.getElementById('challenge_page_number_display');
        const prevBtn = document.getElementById('challenge_prev_page_btn');
        const nextBtn = document.getElementById('challenge_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay retos para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} retos`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentChallengePage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentChallengePage <= 1);
        if (nextBtn) nextBtn.disabled = (currentChallengePage >= totalPages);
    }

    function changeChallengePage(delta) {
        currentChallengePage += delta;
        renderChallengesPage();
    }

    // Leaderboard Tab Filtering & Pagination (10 rows per page)
    let currentLeaderboardPage = 1;
    const leaderboardItemsPerPage = 10;

    function onLeaderboardFilterChange() {
        currentLeaderboardPage = 1;
        renderLeaderboardPage();
    }

    function renderLeaderboardPage() {
        const searchVal = (document.getElementById('search-leaderboard-input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-leaderboard-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            return !searchVal || name.includes(searchVal);
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / leaderboardItemsPerPage) || 1;

        if (currentLeaderboardPage > totalPages) currentLeaderboardPage = totalPages;
        if (currentLeaderboardPage < 1) currentLeaderboardPage = 1;

        const startIndex = (currentLeaderboardPage - 1) * leaderboardItemsPerPage;
        const endIndex = startIndex + leaderboardItemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_leaderboard_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('leaderboard_pagination_info');
        const pageSpan = document.getElementById('leaderboard_page_number_display');
        const prevBtn = document.getElementById('leaderboard_prev_page_btn');
        const nextBtn = document.getElementById('leaderboard_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay atletas para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} atletas`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentLeaderboardPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentLeaderboardPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentLeaderboardPage >= totalPages);
    }

    function changeLeaderboardPage(delta) {
        currentLeaderboardPage += delta;
        renderLeaderboardPage();
    }

    // Achievements Tab Filtering & Pagination (6 cards per page)
    let currentAchievementPage = 1;
    let currentAchievementStatusFilter = 'all';
    const achievementItemsPerPage = 6;

    function setAchievementStatusFilter(status) {
        currentAchievementStatusFilter = status;

        const tabs = document.querySelectorAll('.achievement-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "achievement-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('achievement-status-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "achievement-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentAchievementPage = 1;
        renderAchievementsPage();
    }

    function onAchievementFilterChange() {
        currentAchievementPage = 1;
        renderAchievementsPage();
    }

    function renderAchievementsPage() {
        const searchVal = (document.getElementById('search-achievement-input')?.value || '').toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('[data-achievement-card]'));

        const filtered = cards.filter(a => {
            const name = a.getAttribute('data-name') || '';
            const desc = a.getAttribute('data-desc') || '';
            const isActive = a.getAttribute('data-active') || '1';

            const matchesStatus = (currentAchievementStatusFilter === 'all') || (isActive === currentAchievementStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || desc.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / achievementItemsPerPage) || 1;

        if (currentAchievementPage > totalPages) currentAchievementPage = totalPages;
        if (currentAchievementPage < 1) currentAchievementPage = 1;

        const startIndex = (currentAchievementPage - 1) * achievementItemsPerPage;
        const endIndex = startIndex + achievementItemsPerPage;

        cards.forEach(a => a.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(a => a.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_achievements_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && cards.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('achievement_pagination_info');
        const pageSpan = document.getElementById('achievement_page_number_display');
        const prevBtn = document.getElementById('achievement_prev_page_btn');
        const nextBtn = document.getElementById('achievement_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay medallas para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} medallas`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentAchievementPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentAchievementPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentAchievementPage >= totalPages);
    }

    function changeAchievementPage(delta) {
        currentAchievementPage += delta;
        renderAchievementsPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif

        updateCounters();
        renderChallengesPage();
        renderLeaderboardPage();
        renderAchievementsPage();
    });
</script>
@endsection
