@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Welcome Header Section (GPU Optimized: Solid Gradients, No Blur) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight flex flex-wrap items-center gap-2.5">
                ¡Hola, {{ auth()->user()->profile->first_name ?? 'Coach' }}!
                @if(auth()->user()->role === 'superadmin')
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-lg uppercase tracking-wider">SuperAdmin</span>
                @elseif(auth()->user()->role === 'admin')
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-lg uppercase tracking-wider">Administrador</span>
                @else
                    <span class="px-2.5 py-0.5 text-xs font-bold bg-lime-500/20 text-lime-300 border border-lime-500/30 rounded-lg uppercase tracking-wider">Entrenador</span>
                @endif
            </h1>
            <p class="text-slate-400 text-sm mt-1">Resumen de rendimiento operativo, retención de atletas e inteligencia de asistencias.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 text-xs font-semibold text-lime-400 bg-lime-500/10 rounded-full border border-lime-500/20 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-lime-400"></span>
                Gym Abierto
            </span>
            <div class="text-xs text-slate-400 font-medium">{{ date('d M, Y') }}</div>
        </div>
    </div>

    <!-- SaaS Global Metrics (Only visible to Superadmins) -->
    @if(auth()->user()->role === 'superadmin')
        <div class="space-y-4">
            <h2 class="text-xs uppercase font-extrabold tracking-widest text-purple-400">Consola Global SaaS (Soporte Técnico)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Gyms -->
                <div class="bg-slate-900 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Gimnasios Clientes</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="dumbbell" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-slate-100 relative z-10">{{ $totalGyms }} Sucursales</span>
                    <p class="text-[10px] text-purple-300/80 mt-1.5 relative z-10 font-bold uppercase tracking-wider">{{ $activeGymsCount }} Habilitadas &bull; {{ $inactiveGymsCount }} Suspendidas</p>
                </div>
                
                <!-- Total System Users -->
                <div class="bg-slate-900 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Usuarios Globales</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="users-2" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-slate-100 relative z-10">{{ $totalSystemUsers }} Cuentas</span>
                </div>

                <!-- Global Sales -->
                <div class="bg-slate-900 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Recaudación Total</span>
                        <div class="p-2 bg-purple-500/10 text-purple-400 rounded-xl">
                            <i data-lucide="banknote" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="mt-1">
                        <span class="text-2xl font-black text-slate-100 relative z-10 block">${{ number_format($globalSalesTotal, 2) }}</span>
                        <span class="text-[11px] font-bold text-emerald-400 font-mono relative z-10 block mt-0.5">≈ {{ \App\Services\ExchangeRateService::formatVES($globalSalesTotal * \App\Services\ExchangeRateService::getCurrentRate()) }}</span>
                    </div>
                </div>

                <!-- Database Health -->
                <div class="bg-slate-900 border border-purple-500/20 p-5 rounded-2xl relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <span class="text-[10px] font-bold text-purple-300 uppercase tracking-widest">Estado Servidor</span>
                        <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20">
                            <i data-lucide="server" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-black text-emerald-400 relative z-10">100% ONLINE</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Stats Grid (Active Gym Context) -->
    <div>
        <h2 class="text-xs uppercase font-extrabold tracking-widest text-slate-500 mb-4">Métricas del Gimnasio Activo</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Total Clients Card -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl hover:border-lime-500/40 transition-colors duration-200">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Clientes</span>
                    <div class="p-2 bg-lime-500/10 text-lime-400 rounded-xl">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-100">{{ $totalClients }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Atletas registrados en el gimnasio</p>
            </div>

            <!-- Active Clients Today Card -->
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl hover:border-lime-500/40 transition-colors duration-200">
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
                <!-- Monthly Cashflow -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl hover:border-lime-500/40 transition-colors duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Caja Mensual</span>
                        <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl">
                            <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div>
                        <span class="text-3xl font-extrabold text-slate-100 block leading-none">${{ number_format($monthlyIncome, 2) }}</span>
                        <span class="text-xs font-bold text-emerald-400 font-mono block mt-1.5">≈ {{ \App\Services\ExchangeRateService::formatVES($monthlyIncomeVes ?? ($monthlyIncome * \App\Services\ExchangeRateService::getCurrentRate(session('active_gym_id', null)))) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Pagos de Membresías + POS (mes actual)</p>
                </div>
            @else
                <!-- Total Routines -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl hover:border-lime-500/40 transition-colors duration-200">
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
                <!-- Administrative Alerts -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl hover:border-lime-500/40 transition-colors duration-200">
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
                <!-- Total Meal Plans -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl hover:border-lime-500/40 transition-colors duration-200">
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

    <!-- 1 & 2. Redesigned Professional Executive Console & Retention Risk Action Panel -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                    <span class="px-2.5 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-full flex items-center gap-1">
                        <i data-lucide="activity" class="w-3.5 h-3.5"></i> Inteligencia Operativa
                    </span>
                    <span>&bull; Rendimiento &amp; Retención del Gimnasio</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-100 flex items-center gap-2.5">
                    <i data-lucide="shield-check" class="w-6 h-6 text-lime-400"></i> Consola Ejecutiva de Asistencia &amp; Retención
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Indicadores clave de actividad diaria, volumen semanal y prevención de deserción de socios.</p>
            </div>
        </div>

        <!-- Top Section: Executive Stats Grid (4 High-Design Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Active Base & Retention -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800/90 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider">Base Activa</span>
                    <span class="p-1.5 rounded-lg bg-lime-500/10 text-lime-400">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </span>
                </div>
                <div>
                    <span class="text-2xl font-black text-slate-100">{{ $activeMembersCount }}</span>
                    <span class="text-xs text-slate-400 font-medium"> socios activos</span>
                </div>
                <div class="pt-2 border-t border-slate-850 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Retención 14d:</span>
                    <span class="font-bold text-lime-400 bg-lime-500/10 px-2 py-0.5 rounded-md border border-lime-500/20">{{ $retentionRatePct }}%</span>
                </div>
            </div>

            <!-- Card 2: Today Check-ins & Attendance % -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800/90 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider">Accesos Hoy</span>
                    <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </span>
                </div>
                <div>
                    <span class="text-2xl font-black text-emerald-400">{{ $todayCheckinsCount }}</span>
                    <span class="text-xs text-slate-400 font-medium"> check-ins</span>
                </div>
                <div class="pt-2 border-t border-slate-850 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Participación:</span>
                    <span class="font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">{{ $todayParticipationPct }}% del total</span>
                </div>
            </div>

            <!-- Card 3: Weekly Attendance & Trend -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-850/90 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider">Esta Semana</span>
                    <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                    </span>
                </div>
                <div>
                    <span class="text-2xl font-black text-slate-100">{{ $thisWeekCheckinsCount }}</span>
                    <span class="text-xs text-slate-400 font-medium"> asistencias</span>
                </div>
                <div class="pt-2 border-t border-slate-850 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Tendencia:</span>
                    @if($weeklyGrowthPct >= 0)
                        <span class="font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">+{{ $weeklyGrowthPct }}% vs ant.</span>
                    @else
                        <span class="font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-md border border-rose-500/20">{{ $weeklyGrowthPct }}% vs ant.</span>
                    @endif
                </div>
            </div>

            <!-- Card 4: Daily Average -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-850/90 flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider">Promedio Diario</span>
                    <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                    </span>
                </div>
                <div>
                    <span class="text-2xl font-black text-purple-400">{{ $thisWeekCheckinsCount > 0 ? round($thisWeekCheckinsCount / 7, 1) : 0 }}</span>
                    <span class="text-xs text-slate-400 font-medium"> atletas / día</span>
                </div>
                <div class="pt-2 border-t border-slate-850 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Semana previa:</span>
                    <span class="font-bold text-slate-300 font-mono">{{ $lastWeekCheckinsCount }} total</span>
                </div>
            </div>

        </div>

        <!-- Bottom Section: Churn & Risk Action Cards Panel -->
        <div class="pt-2">
            <h3 class="text-xs uppercase font-extrabold tracking-widest text-slate-400 mb-3 flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400"></i>
                Consola de Prevención de Deserción &amp; Vencimientos
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Action Card 1: Inactive Members -->
                <div class="bg-slate-950 p-4 rounded-2xl border border-rose-500/20 flex flex-col justify-between space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="block text-xs font-extrabold text-rose-400 uppercase tracking-wider">Inactivos (+7 Días)</span>
                            <span class="text-2xl font-black text-slate-100 mt-1 block">{{ $atRiskMembersCount }} Socios</span>
                        </div>
                        <span class="p-2 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <i data-lucide="user-x" class="w-5 h-5"></i>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">Atletas inscritos sin registro de check-in en la última semana.</p>
                    <a href="{{ url('/notificaciones') }}" class="w-full py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/20 font-bold rounded-xl text-xs text-center transition-colors flex items-center justify-center gap-1.5">
                        <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                        Enviar Notificación Push
                    </a>
                </div>

                <!-- Action Card 2: Expiring Memberships -->
                <div class="bg-slate-950 p-4 rounded-2xl border border-amber-500/20 flex flex-col justify-between space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="block text-xs font-extrabold text-amber-400 uppercase tracking-wider">Vencimientos Próximos</span>
                            <span class="text-2xl font-black text-slate-100 mt-1 block">{{ $expiringMembershipsCount }} Planes</span>
                        </div>
                        <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed">Membresías que vencen en los próximos 7 días.</p>
                    <a href="{{ url('/finanzas') }}" class="w-full py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/20 font-bold rounded-xl text-xs text-center transition-colors flex items-center justify-center gap-1.5">
                        <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
                        Gestionar Renovaciones
                    </a>
                </div>

                <!-- Action Card 3: Active Community Summary -->
                <div class="bg-slate-950 p-4 rounded-2xl border border-emerald-500/20 flex flex-col justify-between space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="block text-xs font-extrabold text-emerald-400 uppercase tracking-wider">Comunidad Conectada</span>
                            <span class="text-2xl font-black text-slate-100 mt-1 block">{{ $retentionRatePct }}% Retención</span>
                        </div>
                        <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <i data-lucide="award" class="w-5 h-5"></i>
                        </span>
                    </div>
                    <div class="space-y-1">
                        <div class="w-full bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-800">
                            <div class="bg-emerald-400 h-2 rounded-full" style="width: {{ min(100, max(0, $retentionRatePct)) }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Porcentaje de la comunidad entrenando activamente.</p>
                    </div>
                    <a href="{{ url('/clientes') }}" class="w-full py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/20 font-bold rounded-xl text-xs text-center transition-colors flex items-center justify-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        Directorio de Clientes
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. Weekly Attendance Control Chart with Integrated Chart Format & Date Selectors -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6">
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-850 pb-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                    <span class="px-2.5 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-full flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Control Semanal
                    </span>
                    <span>&bull; Selector de Rango &amp; Formato de Gráfica</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-100 flex items-center gap-2.5">
                    <i data-lucide="bar-chart-3" class="w-6 h-6 text-lime-400"></i> Gráfica Semanal de Asistencias Diarias
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Selecciona el periodo a auditar y cambia el formato de presentación visual de asistencias.</p>
            </div>

            <!-- Integrated Controls: Format Toggle (Área / Barras) + Date Range Selector -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Format Selector Buttons -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800">
                    <button type="button" onclick="changeAttendanceChartType('line')" id="chart-btn-line" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-lime-500/10 text-lime-400 border border-lime-500/20 flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="line-chart" class="w-3.5 h-3.5"></i>
                        Área
                    </button>
                    <button type="button" onclick="changeAttendanceChartType('bar')" id="chart-btn-bar" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-slate-200 hover:bg-slate-900 flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                        Barras
                    </button>
                </div>

                <!-- Date Period Select -->
                <select id="attendance_period_select" onchange="onAttendancePeriodChange()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-lime-400 font-bold focus:outline-none focus:border-lime-500 cursor-pointer">
                    <option value="this_week" selected>Esta Semana (Lunes a Domingo)</option>
                    <option value="last_week">Semana Anterior</option>
                    <option value="this_month">Mes Actual</option>
                    <option value="custom">Rango Personalizado...</option>
                </select>

                <div id="attendance_custom_dates_container" class="hidden flex items-center gap-2">
                    <input type="date" id="attendance_start_date" onchange="fetchAttendanceChartData()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-slate-200 font-medium">
                    <span class="text-slate-500 text-xs">-</span>
                    <input type="date" id="attendance_end_date" onchange="fetchAttendanceChartData()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-slate-200 font-medium">
                </div>
            </div>
        </div>

        <!-- Weekly Chart.js Container -->
        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-850 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400">
                <span class="font-bold text-slate-300 flex items-center gap-2">
                    <i data-lucide="line-chart" class="w-4 h-4 text-lime-400"></i> Distribución por Día
                </span>
                <div class="flex items-center gap-4 text-[11px]">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-lime-400"></span> Asistencias Registradas</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-slate-600"></span> Base Activa ({{ $activeMembersCount }} Socios)</span>
                </div>
            </div>

            <div class="relative h-64 w-full">
                <canvas id="weeklyAttendanceChartCanvas"></canvas>
            </div>

            <!-- Dynamic Chart Summary Footer -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-850 text-xs font-semibold text-slate-400">
                <div>Total en Periodo: <strong id="att_summary_total" class="text-slate-100 font-extrabold">{{ array_sum($attendanceData) }} asistencias</strong></div>
                <div>Promedio Diario: <strong id="att_summary_avg" class="text-lime-400 font-extrabold">{{ round(array_sum($attendanceData) / 7, 1) }} accesos/día</strong></div>
                <div>Día Pico: <strong id="att_summary_peak" class="text-purple-400 font-extrabold">Calculando...</strong></div>
            </div>
        </div>

    </div>

    <!-- 4. Gym Traffic & Peak Hours Saturation Analytics with Date Selector (Default: Mes Actual) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6">
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-850 pb-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-full flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> Mapa de Tráfico por Hora
                    </span>
                    <span>&bull; Análisis de Afluencia &amp; Saturación</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-100 flex items-center gap-2.5">
                    <i data-lucide="gauge" class="w-6 h-6 text-rose-400"></i> Horarios Pico y Nivel de Saturación del Gimnasio
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Identifica los horarios con mayor aglomeración y las horas más tranquilas filtradas por fecha o periodo.</p>
            </div>

            <!-- Date Selector Controls for Traffic (Default: Mes Actual) -->
            <div class="flex flex-wrap items-center gap-3">
                <select id="traffic_period_select" onchange="onTrafficPeriodChange()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-rose-400 font-bold focus:outline-none focus:border-rose-500 cursor-pointer">
                    <option value="this_month" selected>Mes Actual</option>
                    <option value="this_week">Esta Semana</option>
                    <option value="today">Hoy</option>
                    <option value="custom">Fecha Específica...</option>
                </select>

                <div id="traffic_custom_date_container" class="hidden">
                    <input type="date" id="traffic_custom_date" onchange="fetchTrafficChartData()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-slate-200 font-medium">
                </div>
            </div>
        </div>

        <!-- 3 Executive Key Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Hora Pico Card -->
            <div class="p-4 bg-slate-950 rounded-2xl border border-rose-500/20 relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold uppercase text-rose-400 flex items-center gap-1.5">
                        <i data-lucide="flame" class="w-4 h-4"></i> Hora de Mayor Afluencia
                    </span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <h3 id="traffic_peak_hour_text" class="text-lg font-black text-slate-100">{{ $peakHourText }}</h3>
                <p class="text-[11px] text-slate-500 mt-1">Hora de máxima saturación en el periodo seleccionado</p>
            </div>

            <!-- Hora Valle Card -->
            <div class="p-4 bg-slate-950 rounded-2xl border border-emerald-500/20 relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold uppercase text-emerald-400 flex items-center gap-1.5">
                        <i data-lucide="leaf" class="w-4 h-4"></i> Hora Más Tranquila
                    </span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <h3 id="traffic_quiet_hour_text" class="text-lg font-black text-slate-100">{{ $quietHourText }}</h3>
                <p class="text-[11px] text-slate-500 mt-1">Horario recomendado para entrenar sin esperas</p>
            </div>

            <!-- Día Más Concurrido Card -->
            <div class="p-4 bg-slate-950 rounded-2xl border border-amber-500/20 relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold uppercase text-amber-400 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-4 h-4"></i> Día de Mayor Tráfico
                    </span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <h3 id="traffic_busiest_day_text" class="text-lg font-black text-slate-100">{{ $busiestDayName }}</h3>
                <p class="text-[11px] text-slate-500 mt-1">Día de la semana con mayor concentración</p>
            </div>
        </div>

        <!-- Chart.js Hourly Bar Chart Container -->
        <div class="bg-slate-950 p-5 rounded-2xl border border-slate-850 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-400">
                <span class="font-bold text-slate-300 flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-rose-400"></i> Distribución de Aforo por Hora (06:00 a 22:00 hrs)
                </span>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-rose-500"></span> Pico / Saturado</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-amber-500"></span> Medio</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-emerald-500"></span> Tranquilo</span>
                </div>
            </div>

            <div class="relative h-60 w-full">
                <canvas id="hourlyTrafficChartCanvas"></canvas>
            </div>
        </div>

    </div>

    <!-- 5 Módulos de Inteligencia Operativa & Analítica Avanzada -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-850 pb-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                    <span class="px-2.5 py-0.5 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded-full flex items-center gap-1">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i> Inteligencia Analítica
                    </span>
                    <span>&bull; Rendimiento de Servicios, Ventas &amp; Gamificación</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-100 flex items-center gap-2.5">
                    <i data-lucide="grid" class="w-6 h-6 text-cyan-400"></i> Panel de Operaciones, Ventas &amp; Actividad del Gym
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Monitoreo en tiempo real de clases grupales, productos más vendidos, membresías y ranking de atletas.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Módulo 1: Top Clases & Ocupación de Horarios -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-850">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-lime-500/10 text-lime-400">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </span>
                            <h3 class="font-extrabold text-sm text-slate-100">Top Clases &amp; Ocupación</h3>
                        </div>
                        <a href="{{ url('/clases') }}" class="text-[11px] font-bold text-lime-400 hover:underline">Ver todas &rarr;</a>
                    </div>

                    <div class="space-y-3 mt-4">
                        @forelse($topClasses as $schedule)
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-850 flex items-center justify-between text-xs">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $schedule->gymClass->name ?? 'Clase Grupal' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m') }} &bull; {{ substr($schedule->start_time, 0, 5) }} hrs</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md bg-lime-500/10 text-lime-400 font-extrabold border border-lime-500/20 text-[11px]">
                                    {{ $schedule->bookings_count }} / {{ $schedule->gymClass->capacity ?? 20 }} Cupos
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center">No hay clases programadas hoy.</p>
                        @endforelse
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 pt-2 border-t border-slate-850">Capacidad promedio de salas grupales</p>
            </div>

            <!-- Módulo 2: Top Productos Vendidos POS & Inventario -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-850">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            </span>
                            <h3 class="font-extrabold text-sm text-slate-100">Top Productos Vendidos (POS)</h3>
                        </div>
                        <a href="{{ url('/tienda/productos') }}" class="text-[11px] font-bold text-emerald-400 hover:underline">Inventario &rarr;</a>
                    </div>

                    <div class="space-y-3 mt-4">
                        @forelse($topProducts as $prod)
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-850 flex items-center justify-between text-xs">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $prod->name }}</span>
                                    <span class="text-[10px] text-slate-400">${{ number_format($prod->price, 2) }} &bull; {{ $prod->category->name ?? 'Suplemento' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="block font-black text-slate-200 text-xs">{{ $prod->sale_items_count }} ventas</span>
                                    <span class="text-[10px] {{ $prod->stock_quantity <= $prod->min_stock ? 'text-rose-400 font-bold' : 'text-slate-500' }}">
                                        Stock: {{ $prod->stock_quantity }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center">No hay ventas registradas este mes.</p>
                        @endforelse
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 pt-2 border-t border-slate-850">Productos con mayor demanda en la tienda</p>
            </div>

            <!-- Módulo 3: Distribución de Membresías -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-850">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                            </span>
                            <h3 class="font-extrabold text-sm text-slate-100">Distribución de Membresías</h3>
                        </div>
                        <a href="{{ url('/finanzas') }}" class="text-[11px] font-bold text-cyan-400 hover:underline">Planes &rarr;</a>
                    </div>

                    <div class="space-y-3.5 mt-4">
                        @forelse($membershipDistribution as $plan)
                            @php
                                $pct = $activeMembersCount > 0 ? round(($plan->memberships_count / $activeMembersCount) * 100, 1) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-200">{{ $plan->name }}</span>
                                    <span class="font-bold text-cyan-400">{{ $plan->memberships_count }} socios ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-850">
                                    <div class="bg-cyan-400 h-2 rounded-full" style="width: {{ min(100, max(0, $pct)) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center">No hay planes activos configurados.</p>
                        @endforelse
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 pt-2 border-t border-slate-850">Participación por tipo de plan registrado</p>
            </div>

            <!-- Módulo 4: Líderes de Gamificación (Top Atletas) -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between space-y-4 lg:col-span-2">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-850">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400">
                                <i data-lucide="trophy" class="w-4 h-4"></i>
                            </span>
                            <h3 class="font-extrabold text-sm text-slate-100">Top Atletas del Mes (Gamificación &amp; XP)</h3>
                        </div>
                        <a href="{{ url('/retos') }}" class="text-[11px] font-bold text-amber-400 hover:underline">Ver Leaderboard &rarr;</a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                        @forelse($topAthletes as $index => $stat)
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-850 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-black text-xs {{ $index === 0 ? 'bg-amber-400 text-slate-950' : ($index === 1 ? 'bg-slate-300 text-slate-950' : ($index === 2 ? 'bg-amber-700 text-slate-100' : 'bg-slate-800 text-slate-400')) }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <img src="{{ $stat->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-700 shrink-0">
                                    <div>
                                        <span class="block font-bold text-slate-100">{{ $stat->user->profile->first_name ?? 'Atleta' }} {{ $stat->user->profile->last_name ?? '' }}</span>
                                        <span class="text-[10px] text-amber-400 font-bold">Nivel {{ $stat->current_level }}</span>
                                    </div>
                                </div>
                                <span class="font-black text-slate-200 font-mono text-xs">{{ number_format($stat->total_xp) }} XP</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center col-span-2">No hay registros de XP este mes.</p>
                        @endforelse
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 pt-2 border-t border-slate-850">Atletas con mayor puntaje de consistencia en el gimnasio</p>
            </div>

            <!-- Módulo 5: Sesiones de Entrenamiento Iniciadas Hoy -->
            <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-850">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400">
                                <i data-lucide="dumbbell" class="w-4 h-4"></i>
                            </span>
                            <h3 class="font-extrabold text-sm text-slate-100">Sesiones Iniciadas Hoy</h3>
                        </div>
                        <a href="{{ url('/rutinas') }}" class="text-[11px] font-bold text-purple-400 hover:underline">Rutinas &rarr;</a>
                    </div>

                    <div class="space-y-3 mt-4">
                        @forelse($todaySessions as $session)
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-850 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $session->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-slate-700 shrink-0">
                                    <div>
                                        <span class="block font-bold text-slate-100">{{ $session->user->profile->first_name ?? 'Atleta' }}</span>
                                        <span class="text-[10px] text-purple-300 font-semibold">{{ $session->routine->name ?? 'Entrenamiento' }}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 font-bold text-[10px]">
                                    Completado
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 py-4 text-center">No hay entrenamientos iniciados hoy.</p>
                        @endforelse
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 pt-2 border-t border-slate-850">Actividad de rutinas activas en sala hoy</p>
            </div>

        </div>
    </div>

    <!-- 5. Active Clients List with Paginated Table (Max 10 per page) -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Clientes Recientemente Registrados</h3>
                <p class="text-xs text-slate-400">Listado interactivo de atletas ingresados en el sistema</p>
            </div>
            <a href="{{ url('/clientes') }}" class="text-xs font-semibold text-lime-400 hover:text-lime-300 flex items-center gap-1 transition-colors">
                Ver directorio completo
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
                        <tr data-client-row class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-4 flex items-center gap-3">
                                <img src="{{ $client->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-800 shrink-0">
                                <div>
                                    <span class="block font-bold text-slate-200">
                                        {{ $client->profile->first_name ?? 'Cliente' }} {{ $client->profile->last_name ?? '' }}
                                    </span>
                                    <span class="block text-xs text-slate-500">ID #{{ $client->id }}</span>
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

        <!-- Clients Pagination Controls (10 items per page) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-800 text-xs font-medium text-slate-400">
            <span id="clients_pagination_info">Mostrando clientes...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="clients_prev_btn" onclick="changeClientsPage(-1)" class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors">
                    Anterior
                </button>
                <span id="clients_page_display" class="px-3 py-1.5 bg-slate-950 rounded-lg font-bold text-lime-400 border border-slate-850">Página 1</span>
                <button type="button" id="clients_next_btn" onclick="changeClientsPage(1)" class="px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global chart instances
    window.weeklyAttendanceChartInstance = null;
    window.hourlyTrafficChartInstance = null;

    // GPU-Optimized Chart Defaults (Fast 300ms animation, zero blur repaint)
    Chart.defaults.animation.duration = 300;
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

    // ----------------------------------------------------
    // 3. WEEKLY ATTENDANCE CHART (INTERACTIVE AJAX & CHART.JS)
    // ----------------------------------------------------
    function onAttendancePeriodChange() {
        const period = document.getElementById('attendance_period_select').value;
        const customContainer = document.getElementById('attendance_custom_dates_container');
        if (period === 'custom') {
            customContainer.classList.remove('hidden');
        } else {
            customContainer.classList.add('hidden');
            fetchAttendanceChartData();
        }
    }

    function fetchAttendanceChartData() {
        const period = document.getElementById('attendance_period_select').value;
        const startDate = document.getElementById('attendance_start_date').value;
        const endDate = document.getElementById('attendance_end_date').value;

        let url = `{{ route('dashboard.api.attendance') }}?period=${period}`;
        if (period === 'custom') {
            if (!startDate || !endDate) return;
            url += `&start_date=${startDate}&end_date=${endDate}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderWeeklyAttendanceChart(data.labels, data.counts, data.active_members);
                    document.getElementById('att_summary_total').textContent = `${data.total_checkins} asistencias`;
                    document.getElementById('att_summary_avg').textContent = `${data.avg_daily} accesos/día`;
                    document.getElementById('att_summary_peak').textContent = data.peak_day;
                }
            })
            .catch(err => console.error("Error cargando asistencias:", err));
    }

    function renderWeeklyAttendanceChart(labels, counts, activeMembers) {
        const canvas = document.getElementById('weeklyAttendanceChartCanvas');
        if (!canvas) return;

        if (window.weeklyAttendanceChartInstance) {
            window.weeklyAttendanceChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(163, 230, 53, 0.25)');
        gradient.addColorStop(1, 'rgba(163, 230, 53, 0.00)');

        window.weeklyAttendanceChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Asistencias Reales',
                        data: counts,
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: '#a3e635',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointBackgroundColor: '#a3e635',
                        pointBorderColor: '#090d16',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Base Activa',
                        data: Array(labels.length).fill(activeMembers),
                        borderColor: '#475569',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#a3e635',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    const val = context.parsed.y;
                                    const pct = activeMembers > 0 ? ((val / activeMembers) * 100).toFixed(1) : 0;
                                    return val + ' asistencias (' + pct + '% de participación)';
                                }
                                return 'Socios activos: ' + context.parsed.y;
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
                        ticks: { color: '#94a3b8', font: { size: 11, weight: '600' }, stepSize: 1, precision: 0 }
                    }
                }
            }
        });
    }

    function changeAttendanceChartType(type) {
        if (!window.weeklyAttendanceChartInstance) return;

        const btnLine = document.getElementById('chart-btn-line');
        const btnBar = document.getElementById('chart-btn-bar');

        if (type === 'bar') {
            window.weeklyAttendanceChartInstance.config.type = 'bar';
            window.weeklyAttendanceChartInstance.data.datasets[0].backgroundColor = 'rgba(163, 230, 53, 0.75)';
            if (btnBar && btnLine) {
                btnBar.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-lime-500/10 text-lime-400 border border-lime-500/20 shadow-sm flex items-center gap-1.5 cursor-pointer";
                btnLine.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-slate-200 hover:bg-slate-900 flex items-center gap-1.5 cursor-pointer";
            }
        } else {
            window.weeklyAttendanceChartInstance.config.type = 'line';
            const ctx = document.getElementById('weeklyAttendanceChartCanvas').getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(163, 230, 53, 0.25)');
            gradient.addColorStop(1, 'rgba(163, 230, 53, 0.00)');
            window.weeklyAttendanceChartInstance.data.datasets[0].backgroundColor = gradient;
            if (btnBar && btnLine) {
                btnLine.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-lime-500/10 text-lime-400 border border-lime-500/20 shadow-sm flex items-center gap-1.5 cursor-pointer";
                btnBar.className = "px-3 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-slate-200 hover:bg-slate-900 flex items-center gap-1.5 cursor-pointer";
            }
        }
        window.weeklyAttendanceChartInstance.update();
    }

    // ----------------------------------------------------
    // 4. GYM TRAFFIC & PEAK HOURS CHART (DEFAULT: MES ACTUAL)
    // ----------------------------------------------------
    function onTrafficPeriodChange() {
        const period = document.getElementById('traffic_period_select').value;
        const customContainer = document.getElementById('traffic_custom_date_container');
        if (period === 'custom') {
            customContainer.classList.remove('hidden');
        } else {
            customContainer.classList.add('hidden');
            fetchTrafficChartData();
        }
    }

    function fetchTrafficChartData() {
        const period = document.getElementById('traffic_period_select').value;
        const customDate = document.getElementById('traffic_custom_date').value;

        let url = `{{ route('dashboard.api.traffic') }}?period=${period}`;
        if (period === 'custom') {
            if (!customDate) return;
            url += `&date=${customDate}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderHourlyTrafficChart(data.labels, data.counts, data.colors);
                    document.getElementById('traffic_peak_hour_text').textContent = data.peak_hour;
                    document.getElementById('traffic_quiet_hour_text').textContent = data.quiet_hour;
                    document.getElementById('traffic_busiest_day_text').textContent = data.busiest_day;
                }
            })
            .catch(err => console.error("Error cargando tráfico:", err));
    }

    function renderHourlyTrafficChart(labels, counts, colors) {
        const canvas = document.getElementById('hourlyTrafficChartCanvas');
        if (!canvas) return;

        if (window.hourlyTrafficChartInstance) {
            window.hourlyTrafficChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        window.hourlyTrafficChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Accesos por Hora',
                    data: counts,
                    backgroundColor: colors,
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#f43f5e',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y;
                                const color = context.dataset.backgroundColor[context.dataIndex];
                                let level = 'Afluencia Baja (Tranquilo)';
                                if (color === '#f43f5e') level = '🔴 HORARIO PICO / SATURADO';
                                else if (color === '#f59e0b') level = '🟡 Tráfico Medio';
                                return [val + ' personas registradas', level];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#1e293b', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 10, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1e293b', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 10, weight: '600' }, stepSize: 1, precision: 0 }
                    }
                }
            }
        });
    }

    // ----------------------------------------------------
    // 5. CLIENTS LIST PAGINATION LOGIC (MAX 10 PER PAGE)
    // ----------------------------------------------------
    var currentClientsPage = 1;
    var clientsPerPage = 10;

    function renderClientsTablePage() {
        const rows = Array.from(document.querySelectorAll('[data-client-row]'));
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / clientsPerPage) || 1;

        if (currentClientsPage > totalPages) currentClientsPage = totalPages;
        if (currentClientsPage < 1) currentClientsPage = 1;

        const startIndex = (currentClientsPage - 1) * clientsPerPage;
        const endIndex = startIndex + clientsPerPage;

        rows.forEach(r => r.classList.add('hidden'));
        rows.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const infoSpan = document.getElementById('clients_pagination_info');
        const pageSpan = document.getElementById('clients_page_display');
        const prevBtn = document.getElementById('clients_prev_btn');
        const nextBtn = document.getElementById('clients_next_btn');

        if (infoSpan) {
            if (totalRows === 0) {
                infoSpan.textContent = "No hay clientes registrados.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalRows);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalRows} clientes`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentClientsPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentClientsPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentClientsPage >= totalPages);
    }

    function changeClientsPage(delta) {
        currentClientsPage += delta;
        renderClientsTablePage();
    }

    function initDashboardPage() {
        currentClientsPage = 1;
        const initialLabels = @json($dailyRadarLabels);
        const initialCounts = @json($dailyRadarCounts);
        const initialActiveMembers = {{ $activeMembersCount }};
        renderWeeklyAttendanceChart(initialLabels, initialCounts, initialActiveMembers);

        const initialTrafficLabels = @json($trafficHourLabels);
        const initialTrafficCounts = @json($trafficHourCounts);
        const initialTrafficColors = @json($trafficSaturationColors);
        renderHourlyTrafficChart(initialTrafficLabels, initialTrafficCounts, initialTrafficColors);

        renderClientsTablePage();
    }

    // Initialize module both on full reload and on PJAX navigation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardPage);
    } else {
        initDashboardPage();
    }
</script>
@endsection
