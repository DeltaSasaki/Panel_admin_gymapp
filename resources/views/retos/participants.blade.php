@extends('layouts.admin')

@section('title', 'Participantes del Reto')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('retos.index') }}" class="text-xs text-lime-400 font-bold hover:underline flex items-center gap-1.5 mb-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Volver a Retos & Clasificación
            </a>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">{{ $challenge->title }}</h1>
            <p class="text-slate-400 text-xs mt-1">
                Vigencia: {{ \Carbon\Carbon::parse($challenge->start_date)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($challenge->end_date)->format('d/m/Y') }} |
                Recompensas: +{{ $challenge->xp_reward }} XP, +{{ (float)$challenge->token_reward }} Tokens
            </p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Challenge details and Enrollment -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Inscribe Manual Card -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="user-plus" class="text-lime-400 w-4 h-4"></i>
                    Inscribir Socio al Reto
                </h3>
                
                @php
                    $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                @endphp

                @if($activeGymId === 'all')
                    <div class="mt-4 p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs rounded-xl flex items-start gap-2.5">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold">
                            Estás en la vista global. Selecciona una sucursal específica en el menú superior para poder inscribir atletas.
                        </p>
                    </div>
                @else
                    <form action="{{ route('retos.enroll_participant') }}" method="POST" class="mt-4 space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="challenge_id" value="{{ $challenge->id }}">
                        
                        <div>
                            <label for="user_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Socio</label>
                            <select name="user_id" id="user_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                <option value="" disabled selected>Busca o selecciona un atleta...</option>
                                @foreach($availableClients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->profile->first_name ?? 'Socio' }} {{ $client->profile->last_name ?? '' }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                            Inscribir Atleta
                        </button>
                    </form>
                @endif
            </div>

            <!-- Stats Card -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg space-y-4">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="info" class="text-lime-400 w-4 h-4"></i>
                    Resumen del Reto
                </h3>
                <div class="space-y-2.5 text-xs font-semibold text-slate-300">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total Inscritos:</span>
                        <span>{{ $participants->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Retos Completados:</span>
                        <span class="text-emerald-400">{{ $participants->where('status', 'completed')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Retos Fallidos:</span>
                        <span class="text-rose-400">{{ $participants->where('status', 'failed')->count() }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Panel: Participants Table -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 shadow-lg">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-4 mb-4 flex items-center gap-2">
                    <i data-lucide="users-2" class="text-lime-400 w-4 h-4"></i>
                    Lista de Participantes
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                                <th class="py-3 px-4">Atleta</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                                <th class="py-3 px-4 text-right">Progreso & Actualización</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-sm">
                            @forelse($participants as $p)
                                <tr class="hover:bg-slate-800/10 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $p->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=100&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover shrink-0">
                                            <div class="overflow-hidden">
                                                <span class="block font-bold text-slate-200 truncate">{{ $p->user->profile->first_name ?? 'Socio' }} {{ $p->user->profile->last_name ?? '' }}</span>
                                                <span class="block text-[10px] text-slate-500 truncate">{{ $p->user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($p->status === 'completed')
                                            <span class="px-2.5 py-0.5 text-[9px] font-bold border rounded-md bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                                                Completado
                                            </span>
                                            @if($p->completed_at)
                                                <span class="block text-[8px] text-slate-500 mt-1 font-semibold">{{ \Carbon\Carbon::parse($p->completed_at)->format('d/m H:i') }}</span>
                                            @endif
                                        @elseif($p->status === 'failed')
                                            <span class="px-2.5 py-0.5 text-[9px] font-bold border rounded-md bg-rose-500/10 text-rose-400 border-rose-500/20">
                                                Fallido
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-[9px] font-bold border rounded-md bg-purple-500/10 text-purple-400 border-purple-500/20">
                                                En Curso
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if($p->status === 'active')
                                            <form action="{{ route('retos.update_participant', $p->id) }}" method="POST" class="flex items-center justify-end gap-2 m-0 text-xs font-bold">
                                                @csrf
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] text-slate-450 uppercase">Progreso:</span>
                                                    <input type="number" name="progress_value" value="{{ $p->progress_value }}" min="0" required class="w-14 bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-center font-bold text-slate-200 focus:outline-none focus:border-lime-500">
                                                </div>
                                                <select name="status" class="bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1 text-slate-300 focus:outline-none focus:border-lime-500 cursor-pointer">
                                                    <option value="active" selected>Activo</option>
                                                    <option value="completed">Completado</option>
                                                    <option value="failed">Fallido</option>
                                                </select>
                                                <button type="submit" class="px-3 py-1 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold rounded-lg transition-all flex items-center gap-1">
                                                    Guardar
                                                </button>
                                            </form>
                                        @else
                                            <div class="flex items-center justify-end gap-2 text-xs font-semibold text-slate-500 italic pr-2">
                                                <span>Progreso: {{ $p->progress_value }}</span>
                                                <span class="text-slate-650">•</span>
                                                <span>Finalizado</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-16 text-center text-slate-500">
                                        <i data-lucide="users-2" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                        <p class="font-bold">No hay participantes registrados para este reto.</p>
                                        <p class="text-xs text-slate-550 mt-1">Inscribe a tu primer atleta usando el panel de la izquierda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
