@extends('layouts.admin')

@section('title', 'Control de Asistencia')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Control de Asistencia</h1>
            <p class="text-slate-400 text-xs mt-1">Registra la entrada y salida manual de atletas, y supervisa el flujo de accesos.</p>
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
        
        <!-- Check-in Panel -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="check-square" class="text-lime-400 w-4 h-4"></i>
                    Check-in Manual
                </h3>
                
                @php
                    $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                @endphp

                @if($activeGymId === 'all')
                    <div class="mt-4 p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs rounded-xl flex items-start gap-2.5">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold">
                            Estás en la vista de todas las sucursales. Selecciona una sucursal específica en el menú superior para poder registrar asistencias.
                        </p>
                    </div>
                @else
                    <form action="{{ route('asistencia.check_in') }}" method="POST" class="mt-4 space-y-4 text-xs font-semibold">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Atleta</label>
                            <select name="user_id" id="user_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                <option value="" disabled selected>Busca o selecciona un atleta...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('user_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->profile->first_name ?? 'Atleta' }} {{ $client->profile->last_name ?? '' }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                            Registrar Entrada
                        </button>
                    </form>
                @endif
            </div>

            <!-- Quick Information Summary -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg space-y-4">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="activity" class="text-lime-400 w-4 h-4"></i>
                    Aforo del Día
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl text-center">
                        <span class="block text-2xl font-black text-lime-400">
                            {{ $todayEntriesCount }}
                        </span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Entradas Hoy</span>
                    </div>
                    <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl text-center">
                        <span class="block text-2xl font-black text-lime-400">
                            {{ $currentlyInGymCount }}
                        </span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">En el Gimnasio</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Logs feed -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 mb-4 gap-3">
                    <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="history" class="text-lime-400 w-4 h-4"></i>
                        Historial de Accesos
                    </h3>
                    <form action="" method="GET" class="flex items-center gap-2 m-0">
                        <label for="date-filter" class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Fecha:</label>
                        <input type="date" id="date-filter" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" onclick="this.showPicker()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-lime-400 font-bold focus:outline-none focus:border-lime-500 transition-all cursor-pointer">
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                                <th class="py-3 px-4">Atleta</th>
                                <th class="py-3 px-4">Entrada</th>
                                <th class="py-3 px-4">Salida</th>
                                <th class="py-3 px-4 text-center">Médio</th>
                                <th class="py-3 px-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-sm">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-800/10 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $log->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover border border-slate-800 shrink-0">
                                            <div class="overflow-hidden">
                                                <span class="block font-bold text-slate-200 truncate">{{ $log->user->profile->first_name ?? 'Atleta' }} {{ $log->user->profile->last_name ?? '' }}</span>
                                                <span class="block text-[10px] text-slate-500 truncate">{{ $log->user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="block font-semibold text-slate-300 text-xs">{{ \Carbon\Carbon::parse($log->check_in)->format('H:i') }}</span>
                                        <span class="block text-[9px] text-slate-500">{{ \Carbon\Carbon::parse($log->check_in)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($log->check_out)
                                            <span class="block font-semibold text-slate-400 text-xs">{{ \Carbon\Carbon::parse($log->check_out)->format('H:i') }}</span>
                                            <span class="block text-[9px] text-slate-550">{{ \Carbon\Carbon::parse($log->check_out)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[9px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded">
                                                En Sala
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $methodMap = [
                                                'admin' => 'Admin',
                                                'biometric' => 'Biométrico',
                                                'rfid' => 'RFID',
                                                'app_manual' => 'App Móvil'
                                            ];
                                            $methodBadge = [
                                                'admin' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'biometric' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                'rfid' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'app_manual' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 text-[9px] font-bold border rounded-md {{ $methodBadge[$log->entry_method] ?? 'bg-slate-950 text-slate-500 border-slate-850' }}">
                                            {{ $methodMap[$log->entry_method] ?? $log->entry_method }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if(!$log->check_out)
                                            <form action="{{ route('asistencia.check_out', $log->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-750 hover:text-white text-xs font-bold rounded-lg transition-all">
                                                    Marcar Salida
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-500 italic font-semibold">Completado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-500">
                                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                                        <p>No se encontraron registros de asistencia hoy.</p>
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
