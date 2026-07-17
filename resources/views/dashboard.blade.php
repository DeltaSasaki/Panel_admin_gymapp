@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Welcome Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent p-6 rounded-3xl border border-slate-800/40">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex flex-wrap items-center gap-2.5">
                ¡Hola, {{ auth()->user()->profile->first_name ?? 'Coach' }}!
                @if(auth()->user()->role === 'superadmin')
                    <span class="px-2 py-0.5 text-xs font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30 rounded-lg uppercase tracking-wider">SuperAdmin</span>
                @elseif(auth()->user()->role === 'admin')
                    <span class="px-2 py-0.5 text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-lg uppercase tracking-wider">Administrador</span>
                @else
                    <span class="px-2 py-0.5 text-xs font-bold bg-lime-500/20 text-lime-400 border border-lime-500/30 rounded-lg uppercase tracking-wider">Entrenador</span>
                @endif
            </h1>
            <p class="text-slate-400 text-sm mt-1">Aquí tienes el resumen del rendimiento de tus atletas y tus tareas de hoy.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 text-xs font-semibold text-lime-400 bg-lime-500/10 rounded-full border border-lime-500/20 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-ping"></span>
                Gym Abierto
            </span>
            <div class="text-xs text-slate-500 font-medium">{{ date('d M, Y') }}</div>
        </div>
    </div>

    <!-- SaaS Global Metrics (Only visible to Superadmins) -->
    @if(auth()->user()->role === 'superadmin')
        <div class="space-y-4">
            <h2 class="text-xs uppercase font-extrabold tracking-widest text-purple-400">Consola Global SaaS (Soporte Técnico)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Gyms -->
                <div class="bg-purple-950/20 border border-purple-500/25 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Gimnasios Clientes</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="dumbbell" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-white relative z-10">{{ $totalGyms }} Sucursales</span>
                    <p class="text-[10px] text-purple-300/80 mt-1.5 relative z-10 font-bold uppercase tracking-wider">{{ $activeGymsCount }} Habilitadas &bull; {{ $inactiveGymsCount }} Suspendidas</p>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="dumbbell" class="w-16 h-16"></i>
                    </div>
                </div>
                
                <!-- Total System Users -->
                <div class="bg-purple-950/20 border border-purple-500/25 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Usuarios Globales</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="users-2" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-white relative z-10">{{ $totalSystemUsers }} Cuentas</span>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="users-2" class="w-16 h-16"></i>
                    </div>
                </div>

                <!-- Global Sales -->
                <div class="bg-purple-950/20 border border-purple-500/25 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Recaudación Total</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="banknote" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-white relative z-10">${{ number_format($globalSalesTotal, 2) }}</span>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="banknote" class="w-16 h-16"></i>
                    </div>
                </div>

                <!-- Database Health -->
                <div class="bg-purple-950/20 border border-purple-500/25 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Estado Servidor</span>
                        <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl animate-pulse">
                            <i data-lucide="server" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-emerald-400 relative z-10">100% ONLINE</span>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="server" class="w-16 h-16"></i>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Stats Grid (Active Gym Context) -->
    <div>
        <h2 class="text-xs uppercase font-extrabold tracking-widest text-slate-500 mb-4">Métricas del Gimnasio Activo</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Total Clients Card -->
            <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-6 rounded-2xl hover:border-slate-700/60 transition-all hover:-translate-y-1 duration-300">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Clientes</span>
                    <div class="p-2 bg-lime-500/10 text-lime-400 rounded-xl">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-white">{{ $totalClients }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Atletas registrados en este gimnasio</p>
            </div>

            <!-- Active Clients Today Card -->
            <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-6 rounded-2xl hover:border-slate-700/60 transition-all hover:-translate-y-1 duration-300">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entrenando Hoy</span>
                    <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                        <i data-lucide="flame" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-white">{{ $activeClientsToday }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Sesiones iniciadas hoy</p>
            </div>

            <!-- Card 3: Dynamic based on role -->
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <!-- Monthly Cashflow (Admin / Superadmin only) -->
                <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-6 rounded-2xl hover:border-slate-700/60 transition-all hover:-translate-y-1 duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Caja Mensual</span>
                        <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                            <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">${{ number_format($monthlyIncome, 2) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Pagos de Membresías + POS (mes actual)</p>
                </div>
            @else
                <!-- Total Routines (Trainers only) -->
                <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-6 rounded-2xl hover:border-slate-700/60 transition-all hover:-translate-y-1 duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Planes de Rutina</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">{{ $totalRoutines }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Plantillas cargadas en el sistema</p>
                </div>
            @endif

            <!-- Card 4: Dynamic based on role -->
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <!-- Administrative Alerts (Admin / Superadmin only) -->
                <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-6 rounded-2xl hover:border-slate-700/60 transition-all hover:-translate-y-1 duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alertas Administrativas</span>
                        <div class="p-2 bg-rose-500/10 text-rose-400 rounded-xl">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs text-slate-300">
                            <span>Bajo Stock:</span>
                            <span class="font-bold text-rose-400">{{ $lowStockCount }} productos</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-300">
                            <span>Pagos Pendientes:</span>
                            <span class="font-bold text-amber-400">{{ $pendingPaymentsCount }} socios</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Atención administrativa requerida</p>
                </div>
            @else
                <!-- Total Meal Plans (Trainers only) -->
                <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-6 rounded-2xl hover:border-slate-700/60 transition-all hover:-translate-y-1 duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Planes de Dieta</span>
                        <div class="p-2 bg-amber-500/10 text-amber-400 rounded-xl">
                            <i data-lucide="apple" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-white">{{ $totalMealPlans }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Modelos alimentarios guardados</p>
                </div>
            @endif

        </div>
    </div>

    <!-- Graphic and Activity Rows -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Attendance Chart Card (SVG Powered for Clean Render) -->
        <div class="lg:col-span-2 bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-lg text-white">Asistencia Semanal</h3>
                    <p class="text-xs text-slate-400">Entrenamientos registrados por día (esta semana)</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-lime-400 rounded-full"></span>
                    <span class="text-xs text-slate-300 font-medium">Esta Semana</span>
                </div>
            </div>

            <!-- Custom Premium SVG Chart -->
            <div class="relative h-64 w-full flex items-end">
                <svg class="w-full h-full" viewBox="0 0 600 220" preserveAspectRatio="none">
                    <!-- Grid Lines -->
                    <line x1="0" y1="20" x2="600" y2="20" stroke="#1e293b" stroke-dasharray="4" />
                    <line x1="0" y1="80" x2="600" y2="80" stroke="#1e293b" stroke-dasharray="4" />
                    <line x1="0" y1="140" x2="600" y2="140" stroke="#1e293b" stroke-dasharray="4" />
                    <line x1="0" y1="200" x2="600" y2="200" stroke="#334155" />

                    <!-- Gradient Definition -->
                    <defs>
                        <linearGradient id="chart-grad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#a3e635" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#a3e635" stop-opacity="0.0"/>
                        </linearGradient>
                    </defs>

                    <!-- Shaded Area Under Line -->
                    <polygon points="{{ $chartPolygonPoints }}" fill="url(#chart-grad)" />

                    <!-- Chart Line -->
                    <polyline points="{{ $chartLinePoints }}" fill="none" stroke="#a3e635" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                    <!-- Data dots -->
                    @foreach($attendanceData as $index => $count)
                        @php
                            $xCoords = [30, 120, 210, 300, 390, 480, 570];
                            $x = $xCoords[$index];
                            $maxVal = max($attendanceData) ?: 1;
                            $y = 180 - (($count / $maxVal) * 145);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#a3e635" stroke="#070a13" stroke-width="2" />
                    @endforeach
                </svg>
            </div>
            
            <!-- X Axis Labels -->
            <div class="flex justify-between items-center mt-4 px-4 text-xs font-semibold text-slate-500">
                <span>Lun ({{ $attendanceData[0] }})</span>
                <span>Mar ({{ $attendanceData[1] }})</span>
                <span>Mié ({{ $attendanceData[2] }})</span>
                <span>Jue ({{ $attendanceData[3] }})</span>
                <span>Vie ({{ $attendanceData[4] }})</span>
                <span>Sáb ({{ $attendanceData[5] }})</span>
                <span>Dom ({{ $attendanceData[6] }})</span>
            </div>
        </div>

        <!-- Right Hand Column: System Diagnostics for Superadmin OR Coach Tasks for others -->
        @if(auth()->user()->role === 'superadmin')
            <!-- System Diagnostics & Uptime Logs (Superadmin Only) -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg text-white mb-1">Alertas de Soporte Técnico</h3>
                    <p class="text-xs text-slate-400">Diagnóstico del servidor y telemetría SaaS</p>
                </div>

                <div class="space-y-4 my-6">
                    @foreach($systemAlerts as $alert)
                        <div class="flex items-start gap-3 p-3 bg-slate-950/40 rounded-xl border border-slate-850/60">
                            <div class="mt-0.5">
                                @if($alert['type'] === 'warning')
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400"></i>
                                @elseif($alert['type'] === 'success')
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                                @else
                                    <i data-lucide="info" class="w-4 h-4 text-blue-400"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="block text-xs font-bold text-slate-200">{{ $alert['message'] }}</span>
                                <p class="text-[10px] text-slate-500">{{ $alert['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="w-full py-2.5 bg-purple-950/30 hover:bg-purple-900/20 text-purple-300 text-xs font-bold rounded-xl border border-purple-500/20 transition-colors">
                    Consola de Base de Datos
                </button>
            </div>
        @else
            <!-- Standard Coach Tasks (Admins and Trainers) -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg text-white mb-1">Tareas del Coach</h3>
                    <p class="text-xs text-slate-400">Objetivos y chequeos pendientes para hoy</p>
                </div>

                <div class="space-y-4 my-6">
                    <!-- Task 1 -->
                    <div class="flex items-start gap-3 p-3 bg-slate-950/40 rounded-xl border border-slate-850/60">
                        <div class="mt-0.5">
                            <input type="checkbox" checked class="w-4 h-4 rounded text-lime-500 bg-slate-900 border-slate-700 focus:ring-0 focus:ring-offset-0">
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 line-through">Revisar macros de Sofía</span>
                            <p class="text-[10px] text-slate-500">Completado por la mañana</p>
                        </div>
                    </div>

                    <!-- Task 2 -->
                    <div class="flex items-start gap-3 p-3 bg-slate-950/40 rounded-xl border border-slate-850/60">
                        <div class="mt-0.5">
                            <input type="checkbox" class="w-4 h-4 rounded text-lime-500 bg-slate-900 border-slate-700 focus:ring-0 focus:ring-offset-0">
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-200">Ajustar rutina de fuerza de Javier</span>
                            <p class="text-[10px] text-purple-400 font-semibold flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                                Rutinas
                            </p>
                        </div>
                    </div>

                    <!-- Task 3 -->
                    <div class="flex items-start gap-3 p-3 bg-slate-950/40 rounded-xl border border-slate-850/60">
                        <div class="mt-0.5">
                            <input type="checkbox" class="w-4 h-4 rounded text-lime-500 bg-slate-900 border-slate-700 focus:ring-0 focus:ring-offset-0">
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-200">Llamada de seguimiento - Mateo M.</span>
                            <p class="text-[10px] text-amber-400 font-semibold flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                Clientes
                            </p>
                        </div>
                    </div>
                </div>

                <button class="w-full py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 text-xs font-bold rounded-xl border border-slate-700/50 hover:border-slate-600 transition-colors">
                    Ver todas las tareas (5)
                </button>
            </div>
        @endif

    </div>

    <!-- Active Clients List -->
    <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="font-bold text-lg text-white">Clientes Recientemente Registrados</h3>
                <p class="text-xs text-slate-400">Últimos ingresos del sistema</p>
            </div>
            <a href="{{ url('/clientes') }}" class="text-xs font-semibold text-lime-400 hover:text-lime-300 flex items-center gap-1 transition-colors">
                Ver todos los clientes
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3 px-4">Cliente</th>
                        <th class="py-3 px-4">Objetivo principal</th>
                        <th class="py-3 px-4">Peso / Altura</th>
                        <th class="py-3 px-4">Rutina Asignada</th>
                        <th class="py-3 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-sm">
                    @php
                        $goalsMap = [
                            'lose_weight' => 'Déficit / Pérdida de Peso',
                            'gain_muscle' => 'Hipertrofia / Ganancia Muscular',
                            'gain_weight' => 'Aumento de Peso',
                            'maintain' => 'Mantenimiento',
                            'improve_endurance' => 'Resistencia',
                            'improve_flexibility' => 'Flexibilidad',
                            'general' => 'General'
                        ];
                    @endphp

                    @forelse($recentClients as $client)
                        <tr class="hover:bg-slate-800/20 transition-colors">
                            <td class="py-4 px-4 flex items-center gap-3">
                                <img src="{{ $client->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-800">
                                <div>
                                    <span class="block font-bold text-slate-200">
                                        {{ $client->profile->first_name ?? 'Cliente' }} {{ $client->profile->last_name ?? '' }}
                                    </span>
                                    <span class="block text-xs text-slate-500">Último acceso: Reciente</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-slate-300">
                                {{ $client->activeRoutine ? ($goalsMap[$client->activeRoutine->routine->goal_type] ?? 'Entrenamiento') : 'Acondicionamiento General' }}
                            </td>
                            <td class="py-4 px-4">
                                @if($client->latestMeasurement)
                                    <span class="font-semibold text-slate-300">{{ $client->latestMeasurement->weight_kg }} kg</span>
                                    <span class="block text-[10px] text-slate-500">{{ $client->latestMeasurement->height_cm }} cm</span>
                                @else
                                    <span class="text-xs text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($client->activeRoutine)
                                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20">
                                        {{ $client->activeRoutine->routine->name }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-slate-800 text-slate-400 rounded-full border border-slate-700">
                                        Sin Rutina
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('clientes.show', $client->id) }}" class="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded-lg transition-colors inline-block">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">No hay clientes recientemente registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
