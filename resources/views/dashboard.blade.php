@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Welcome Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-slate-900 via-slate-900/60 to-transparent p-6 rounded-3xl border border-slate-800/40">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight flex flex-wrap items-center gap-2.5">
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
                <span class="w-2 h-2 rounded-full bg-lime-400 shadow-sm shadow-lime-400/50"></span>
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
                <div class="bg-slate-900/40 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Gimnasios Clientes</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="dumbbell" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-slate-100 relative z-10">{{ $totalGyms }} Sucursales</span>
                    <p class="text-[10px] text-purple-300/80 mt-1.5 relative z-10 font-bold uppercase tracking-wider">{{ $activeGymsCount }} Habilitadas &bull; {{ $inactiveGymsCount }} Suspendidas</p>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="dumbbell" class="w-16 h-16"></i>
                    </div>
                </div>
                
                <!-- Total System Users -->
                <div class="bg-slate-900/40 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Usuarios Globales</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="users-2" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-slate-100 relative z-10">{{ $totalSystemUsers }} Cuentas</span>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="users-2" class="w-16 h-16"></i>
                    </div>
                </div>

                <!-- Global Sales -->
                <div class="bg-slate-900/40 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Recaudación Total</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="banknote" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-slate-100 relative z-10">${{ number_format($globalSalesTotal, 2) }}</span>
                    <div class="absolute -right-3 -bottom-3 text-purple-500/5 transition-transform group-hover:scale-110">
                        <i data-lucide="banknote" class="w-16 h-16"></i>
                    </div>
                </div>

                <!-- Database Health -->
                <div class="bg-slate-900/40 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Estado Servidor</span>
                        <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20">
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
            <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-2xl hover:border-lime-500/40 hover:bg-slate-900/80 transition-colors duration-200 shadow-sm hover:shadow-lime-500/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Clientes</span>
                    <div class="p-2 bg-lime-500/10 text-lime-400 rounded-xl">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-100">{{ $totalClients }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Atletas registrados en este gimnasio</p>
            </div>

            <!-- Active Clients Today Card -->
            <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-2xl hover:border-lime-500/40 hover:bg-slate-900/80 transition-colors duration-200 shadow-sm hover:shadow-lime-500/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entrenando Hoy</span>
                    <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                        <i data-lucide="flame" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-100">{{ $activeClientsToday }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Sesiones iniciadas hoy</p>
            </div>

            <!-- Card 3: Dynamic based on role -->
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <!-- Monthly Cashflow (Admin / Superadmin only) -->
                <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-2xl hover:border-lime-500/40 hover:bg-slate-900/80 transition-colors duration-200 shadow-sm hover:shadow-lime-500/[0.03]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Caja Mensual</span>
                        <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                            <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-slate-100">${{ number_format($monthlyIncome, 2) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Pagos de Membresías + POS (mes actual)</p>
                </div>
            @else
                <!-- Total Routines (Trainers only) -->
                <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-2xl hover:border-lime-500/40 hover:bg-slate-900/80 transition-colors duration-200 shadow-sm hover:shadow-lime-500/[0.03]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Planes de Rutina</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="activity" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-slate-100">{{ $totalRoutines }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Plantillas cargadas en el sistema</p>
                </div>
            @endif

            <!-- Card 4: Dynamic based on role -->
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <!-- Administrative Alerts (Admin / Superadmin only) -->
                <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-2xl hover:border-lime-500/40 hover:bg-slate-900/80 transition-colors duration-200 shadow-sm hover:shadow-lime-500/[0.03]">
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
                <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-2xl hover:border-lime-500/40 hover:bg-slate-900/80 transition-colors duration-200 shadow-sm hover:shadow-lime-500/[0.03]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Planes de Dieta</span>
                        <div class="p-2 bg-amber-500/10 text-amber-400 rounded-xl">
                            <i data-lucide="apple" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-slate-100">{{ $totalMealPlans }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Modelos alimentarios guardados</p>
                </div>
            @endif

        </div>
    </div>

    <!-- Graphic and Activity Rows -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Attendance Chart Card (Chart.js Powered) -->
        <div class="lg:col-span-2 bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-lime-400"></i>
                        Asistencia Semanal
                    </h3>
                    <p class="text-xs text-slate-400">Entrenamientos registrados por día (esta semana)</p>
                </div>
                <!-- Interactive Chart Type Selector -->
                <div class="flex items-center gap-1.5 bg-slate-950/60 p-1 rounded-xl border border-slate-800/80">
                    <button type="button" onclick="changeAttendanceChartType('line')" id="chart-btn-line" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-lime-500/10 text-lime-400 border border-lime-500/20 shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="line-chart" class="w-3.5 h-3.5"></i>
                        Área
                    </button>
                    <button type="button" onclick="changeAttendanceChartType('bar')" id="chart-btn-bar" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                        Barras
                    </button>
                </div>
            </div>

            <!-- Chart.js Container -->
            <div class="relative h-64 w-full">
                <canvas id="attendanceChartCanvas"></canvas>
            </div>

            <!-- Summary Footer -->
            <div class="flex justify-between items-center mt-4 pt-3 border-t border-slate-800/50 text-xs font-semibold text-slate-400">
                <span>Total Semana: <strong class="text-slate-100 font-extrabold">{{ array_sum($attendanceData) }} asistencias</strong></span>
                <span>Día Pico: <strong class="text-lime-400 font-extrabold">{{ max($attendanceData) }} asistencias</strong></span>
            </div>
        </div>

        <!-- Right Hand Column: System Diagnostics for Superadmin OR Coach Tasks for others -->
        @if(auth()->user()->role === 'superadmin')
            <!-- System Diagnostics & Uptime Logs (Superadmin Only) -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-lg text-slate-100 mb-1">Alertas de Soporte Técnico</h3>
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
                    <h3 class="font-bold text-lg text-slate-100 mb-1">Tareas del Coach</h3>
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
                <h3 class="font-bold text-lg text-slate-100">Clientes Recientemente Registrados</h3>
                <p class="text-xs text-slate-400">Últimos ingresos del sistema</p>
            </div>
            <a href="{{ url('/clientes') }}" class="text-xs font-semibold text-lime-400 hover:text-lime-300 flex items-center gap-1 transition-colors">
                Ver todos los clientes
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto pb-2">
            <table class="w-full text-left border-collapse min-w-[650px] whitespace-nowrap">
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
                                <img src="{{ $client->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-800 shrink-0">
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
                                <a href="{{ route('clientes.show', $client->id) }}" class="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-slate-100 rounded-lg transition-colors inline-block">
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

<script>
    (function renderAttendanceChart() {
        const canvas = document.getElementById('attendanceChartCanvas');
        if (!canvas) return;

        if (window.attendanceChartInstance) {
            window.attendanceChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        const attendanceData = @json($attendanceData);
        const daysLabels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(163, 230, 53, 0.35)');
        gradient.addColorStop(1, 'rgba(163, 230, 53, 0.00)');

        window.attendanceChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: daysLabels,
                datasets: [{
                    label: 'Asistencias',
                    data: attendanceData,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#a3e635',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: '#a3e635',
                    pointBorderColor: '#090d16',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: '#a3e635',
                    pointHoverBorderWidth: 3,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#a3e635',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false,
                        titleFont: { size: 12, weight: 'bold', family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 13, weight: '800', family: "'Plus Jakarta Sans', sans-serif" },
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' ' + (context.parsed.y === 1 ? 'Sesión de entrenamiento' : 'Sesiones de entrenamiento');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#1e293b', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1e293b', drawBorder: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, weight: '600' },
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });

        window.changeAttendanceChartType = function(type) {
            if (!window.attendanceChartInstance) return;

            const btnLine = document.getElementById('chart-btn-line');
            const btnBar = document.getElementById('chart-btn-bar');

            if (type === 'bar') {
                window.attendanceChartInstance.config.type = 'bar';
                window.attendanceChartInstance.data.datasets[0].backgroundColor = 'rgba(163, 230, 53, 0.75)';
                window.attendanceChartInstance.data.datasets[0].hoverBackgroundColor = '#a3e635';

                if (btnBar && btnLine) {
                    btnBar.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-lime-500/10 text-lime-400 border border-lime-500/20 shadow-sm flex items-center gap-1.5 cursor-pointer";
                    btnLine.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 flex items-center gap-1.5 cursor-pointer";
                }
            } else {
                window.attendanceChartInstance.config.type = 'line';
                window.attendanceChartInstance.data.datasets[0].backgroundColor = gradient;

                if (btnBar && btnLine) {
                    btnLine.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-lime-500/10 text-lime-400 border border-lime-500/20 shadow-sm flex items-center gap-1.5 cursor-pointer";
                    btnBar.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 flex items-center gap-1.5 cursor-pointer";
                }
            }
            window.attendanceChartInstance.update();
        };
    })();
</script>
@endsection
