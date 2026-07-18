@extends('layouts.admin')

@section('title', 'Retos e Incentivos')

@section('content')
<div class="space-y-8 animate-fade-in" x-data="{ activeTab: 'retos' }">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Retos, Incentivos & Gamificación</h1>
            <p class="text-slate-400 text-xs mt-1">Crea retos temporales, asigna insignias coleccionables y administra la tabla de clasificación de XP.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            <button onclick="openModal('modal-create-challenge')" class="px-4 py-2 bg-slate-900 border border-slate-800 text-slate-200 hover:text-slate-100 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-lime-400"></i>
                Nuevo Reto
            </button>
            <button onclick="openModal('modal-create-achievement')" class="px-4 py-2 bg-slate-900 border border-slate-800 text-slate-200 hover:text-slate-100 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-purple-400"></i>
                Nueva Medalla
            </button>
            <button onclick="openModal('modal-award-badge')" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 rounded-xl text-xs font-bold shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
                <i data-lucide="medal" class="w-4 h-4 stroke-[3px]"></i>
                Otorgar Medalla
            </button>
        </div>
    </div>

    <!-- Alerts -->
    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-4 rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-900">
        <button 
            @click="activeTab = 'retos'" 
            :class="activeTab === 'retos' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Retos del Gimnasio
        </button>
        <button 
            @click="activeTab = 'leaderboard'" 
            :class="activeTab === 'leaderboard' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Leaderboard (Tabla de Clasificación)
        </button>
        <button 
            @click="activeTab = 'medallas'" 
            :class="activeTab === 'medallas' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Catálogo de Logros
        </button>
    </div>

    <!-- Tab 1: Retos Activos -->
    <div x-show="activeTab === 'retos'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($challenges as $challenge)
            <div class="bg-slate-900/30 border border-slate-850 rounded-2xl p-5 hover:border-slate-800 hover:bg-slate-900/50 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group">
                <div class="space-y-3">
                    <div class="flex justify-between items-start">
                        <h3 class="font-extrabold text-sm text-slate-100 group-hover:text-lime-400 transition-colors">{{ $challenge->title }}</h3>
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-slate-950 text-slate-400 border border-slate-800 rounded">
                            {{ \Carbon\Carbon::parse($challenge->end_date)->diffInDays(now()) }} días rest.
                        </span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed line-clamp-3">{{ $challenge->description ?? 'Sin descripción disponible.' }}</p>
                    
                    <!-- Rewards badges -->
                    <div class="flex items-center gap-2.5 pt-2">
                        <span class="flex items-center gap-1 bg-lime-500/10 text-lime-400 border border-lime-500/20 px-2 py-0.5 text-[9px] font-bold rounded">
                            <i data-lucide="zap" class="w-3 h-3"></i>
                            +{{ $challenge->xp_reward }} XP
                        </span>
                        <span class="flex items-center gap-1 bg-purple-500/10 text-purple-400 border border-purple-500/20 px-2 py-0.5 text-[9px] font-bold rounded">
                            <i data-lucide="coins" class="w-3 h-3"></i>
                            +{{ (float)$challenge->token_reward }} Tokens
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-850/60 pt-4">
                    <div class="flex justify-between items-center text-[10px] text-slate-500 font-semibold">
                        <span>Vigencia: {{ \Carbon\Carbon::parse($challenge->start_date)->format('d/m') }} al {{ \Carbon\Carbon::parse($challenge->end_date)->format('d/m/Y') }}</span>
                    </div>
                    <a href="{{ route('retos.participants', $challenge->id) }}" class="w-full py-2 bg-slate-950 hover:bg-slate-900 border border-slate-850 hover:border-slate-800 text-slate-200 hover:text-slate-100 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        Ver Participantes & Progreso
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500 bg-slate-900/10 border border-slate-900/60 rounded-2xl">
                <i data-lucide="trophy" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                <p class="font-bold text-slate-400">No hay retos vigentes en este momento</p>
                <p class="text-xs text-slate-550 mt-1">Crea tu primer reto grupal haciendo clic en "Nuevo Reto".</p>
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Leaderboard (Tabla de Clasificación) -->
    <div x-show="activeTab === 'leaderboard'" class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-lg">
        <div class="p-6 border-b border-slate-850 flex justify-between items-center">
            <h3 class="font-bold text-lg text-slate-100">Tabla de Clasificación de XP (Top Atletas)</h3>
            <span class="text-xs text-slate-400 font-semibold">Ranking basado en experiencia acumulada</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6 text-center w-16">Puesto</th>
                        <th class="p-4">Atleta</th>
                        <th class="p-4 text-center">Nivel</th>
                        <th class="p-4">Barra de Progreso a Nivel</th>
                        <th class="p-4 text-right">Moneda Virtual</th>
                        <th class="p-4 text-right pr-6 w-32">Puntos XP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($leaderboard as $index => $stat)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 text-center">
                                @if($index === 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-black">1</span>
                                @elseif($index === 1)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-400/10 text-slate-300 border border-slate-400/20 font-black">2</span>
                                @elseif($index === 2)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700/10 text-amber-600 border border-amber-700/20 font-black">3</span>
                                @else
                                    <span class="font-bold text-slate-500">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="p-4 flex items-center gap-3">
                                <img src="{{ $stat->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=100&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover shrink-0">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $stat->user->profile->first_name ?? 'Atleta' }} {{ $stat->user->profile->last_name ?? '' }}</span>
                                    <span class="block text-[10px] text-slate-500">{{ $stat->user->email }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 bg-lime-500/15 text-lime-400 text-[10px] font-black rounded-lg border border-lime-500/20">
                                    LVL {{ $stat->current_level }}
                                </span>
                            </td>
                            <td class="p-4">
                                @php
                                    $xpToNextLevel = $stat->total_xp % 1000;
                                    $progressPercent = ($xpToNextLevel / 1000) * 100;
                                @endphp
                                <div class="w-full max-w-xs space-y-1">
                                    <div class="h-1.5 bg-slate-950 rounded-full overflow-hidden border border-slate-850/60">
                                        <div class="h-full bg-gradient-to-r from-lime-500 to-emerald-500 rounded-full" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <span class="block text-[9px] text-slate-500 font-semibold">{{ $xpToNextLevel }} / 1000 XP para el siguiente nivel</span>
                                </div>
                            </td>
                            <td class="p-4 text-right font-bold text-purple-400">
                                <span class="flex items-center justify-end gap-1.5">
                                    <i data-lucide="coins" class="w-3.5 h-3.5 text-purple-500"></i>
                                    {{ number_format($stat->token_balance, 2) }}
                                </span>
                            </td>
                            <td class="p-4 text-right pr-6 font-mono font-black text-lime-400">
                                {{ number_format($stat->total_xp) }} XP
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                La tabla de clasificación está vacía por el momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Catálogo de Logros -->
    <div x-show="activeTab === 'medallas'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($achievements as $ach)
            <div class="bg-slate-900/30 border border-slate-850 rounded-2xl p-5 hover:border-slate-800 hover:bg-slate-900/50 transition-all flex items-start gap-4">
                <div class="p-3 bg-purple-500/10 border border-purple-500/20 text-purple-400 rounded-2xl">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
                <div class="space-y-1.5 flex-1">
                    <div class="flex justify-between items-start">
                        <h3 class="font-extrabold text-sm text-slate-100">{{ $ach->name }}</h3>
                        @if($ach->gym_id === null)
                            <span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded uppercase">Global</span>
                        @endif
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed">{{ $ach->description ?? 'Sin descripción.' }}</p>
                    <p class="text-[10px] text-slate-500 font-semibold">Condición: {{ $ach->condition_type }} (Mín: {{ $ach->target_value }})</p>
                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-[9px] font-bold text-lime-400 bg-lime-500/5 px-2 py-0.5 border border-lime-500/10 rounded">+{{ $ach->xp_reward }} XP</span>
                        <span class="text-[9px] font-bold text-purple-400 bg-purple-500/5 px-2 py-0.5 border border-purple-500/10 rounded">+${{ number_format($ach->token_reward, 2) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-500 bg-slate-900/10 border border-slate-900/60 rounded-2xl">
                <i data-lucide="award" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                <p class="font-bold text-slate-400">No hay medallas configuradas todavía</p>
                <p class="text-xs text-slate-550 mt-1">Crea tu primera definición de logro haciendo clic en "Nueva Medalla".</p>
            </div>
        @endforelse
    </div>

</div>

<!-- Modal: Crear Reto -->
<div id="modal-create-challenge" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden animate-scale-up">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-black text-sm text-slate-100 uppercase tracking-widest">Crear Nuevo Reto</h3>
            <button onclick="closeModal('modal-create-challenge')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('retos.store_challenge') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="title" class="block text-slate-400 uppercase tracking-wider mb-1.5">Título del Reto</label>
                <input type="text" name="title" id="title" required placeholder="Ej: Reto de Sentadillas 30 Días" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="description" rows="3" placeholder="Detalla los objetivos a completar..." class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha de Inicio</label>
                    <input type="date" name="start_date" id="start_date" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="end_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha de Fin</label>
                    <input type="date" name="end_date" id="end_date" required class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="xp_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Puntos XP (Premio)</label>
                    <input type="number" name="xp_reward" id="xp_reward" required min="0" placeholder="Ej: 500" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="token_reward" class="block text-slate-400 uppercase tracking-wider mb-1.5">Tokens (Moneda de Premio)</label>
                    <input type="number" step="0.01" name="token_reward" id="token_reward" required min="0" placeholder="Ej: 10.00" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-create-challenge')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Reto</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Crear Medalla -->
<div id="modal-create-achievement" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden animate-scale-up">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-black text-sm text-slate-100 uppercase tracking-widest">Crear Nueva Medalla</h3>
            <button onclick="closeModal('modal-create-achievement')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('retos.store_achievement') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Medalla</label>
                <input type="text" name="name" id="name" required placeholder="Ej: Fanático del Cardio, Gladiador" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label for="description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="description" rows="2" placeholder="Ej: Completa 15 clases de spinning..." class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="condition_type" class="block text-slate-400 uppercase tracking-wider mb-1.5">Tipo de Condición</label>
                    <input type="text" name="condition_type" id="condition_type" required placeholder="Ej: workouts_completed" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="target_value" class="block text-slate-400 uppercase tracking-wider mb-1.5">Valor Objetivo</label>
                    <input type="number" name="target_value" id="target_value" required min="1" placeholder="Ej: 15" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="xp_reward_medal" class="block text-slate-400 uppercase tracking-wider mb-1.5">XP de Premio</label>
                    <input type="number" name="xp_reward" id="xp_reward_medal" required min="0" placeholder="Ej: 300" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="token_reward_medal" class="block text-slate-400 uppercase tracking-wider mb-1.5">Tokens de Premio</label>
                    <input type="number" step="0.01" name="token_reward" id="token_reward_medal" required min="0" placeholder="Ej: 5.00" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-create-achievement')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Crear Medalla</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Otorgar Medalla -->
<div id="modal-award-badge" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md overflow-hidden animate-scale-up">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-black text-sm text-slate-100 uppercase tracking-widest">Otorgar Medalla a Socio</h3>
            <button onclick="closeModal('modal-award-badge')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('retos.award_achievement') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label for="user_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Socio</label>
                <select name="user_id" id="user_id" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un atleta...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->profile->first_name ?? 'Socio' }} {{ $client->profile->last_name ?? '' }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="achievement_definition_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Medalla / Logro</label>
                <select name="achievement_definition_id" id="achievement_definition_id" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona una medalla...</option>
                    @foreach($achievements as $ach)
                        <option value="{{ $ach->id }}">{{ $ach->name }} (+{{ $ach->xp_reward }} XP, +{{ (float)$ach->token_reward }} Tokens)</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeModal('modal-award-badge')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Acreditar Recompensa</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
</script>
@endsection
