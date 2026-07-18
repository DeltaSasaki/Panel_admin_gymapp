@extends('layouts.admin')

@section('title', 'Perfil de ' . ($cliente->profile->first_name ?? 'Atleta'))

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb and Quick Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('clientes.index') }}" class="hover:text-lime-400 transition-colors">Mis Clientes</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-200">Perfil del Atleta</span>
        </div>
        <a href="{{ route('clientes.index') }}" class="px-3.5 py-1.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-xs font-bold rounded-xl text-slate-300 transition-colors flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al listado
        </a>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Profile Card -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 space-y-6">
            <div class="text-center pb-6 border-b border-slate-800/60">
                <div class="relative inline-block">
                    <img src="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop' }}" 
                         alt="Foto de perfil" 
                         class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-lime-500/20">
                    <span class="absolute bottom-0 right-2 w-4 h-4 {{ $cliente->is_active ? 'bg-emerald-500' : 'bg-slate-500' }} border-2 border-slate-900 rounded-full"></span>
                </div>
                <h2 class="text-xl font-bold text-white mt-4">{{ $cliente->profile->first_name }} {{ $cliente->profile->last_name }}</h2>
                <span class="px-3 py-1 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-xs font-semibold rounded-full mt-2 inline-block">
                    {{ $cliente->role === 'member' ? 'Atleta' : 'Admin' }}
                </span>
            </div>

            <!-- Contact and Details -->
            <div class="space-y-4 text-sm">
                <h3 class="text-xs uppercase font-extrabold tracking-wider text-slate-500">Datos Personales</h3>
                
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-950 text-slate-400 rounded-lg">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500">Correo Electrónico</span>
                        <span class="text-slate-200 font-medium">
                            @if(auth()->user()->role === 'trainer')
                                {{ preg_replace('/(?<=..).(?=[^@]*?@)/', '*', $cliente->email) }}
                            @else
                                {{ $cliente->email }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-950 text-slate-400 rounded-lg">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500">Teléfono</span>
                        <span class="text-slate-200 font-medium">
                            @if(auth()->user()->role === 'trainer' && $cliente->profile->phone)
                                {{ substr($cliente->profile->phone, 0, 4) . ' •••• ' . substr($cliente->profile->phone, -3) }}
                            @else
                                {{ $cliente->profile->phone ?? 'Sin teléfono' }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-950 text-slate-400 rounded-lg">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <div>
                        @if(auth()->user()->role === 'trainer')
                            <span class="block text-[10px] text-slate-500">Edad Estimada</span>
                            <span class="text-slate-200 font-medium">
                                {{ $cliente->profile->birth_date ? \Carbon\Carbon::parse($cliente->profile->birth_date)->age . ' años' : 'No registrada' }}
                            </span>
                        @else
                            <span class="block text-[10px] text-slate-500">Fecha de Nacimiento</span>
                            <span class="text-slate-200 font-medium">
                                {{ $cliente->profile->birth_date ? \Carbon\Carbon::parse($cliente->profile->birth_date)->format('d M, Y') : 'No registrada' }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-950 text-slate-400 rounded-lg">
                        <i data-lucide="users-2" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500">Género</span>
                        <span class="text-slate-200 font-medium uppercase text-xs">
                            @if($cliente->profile->gender === 'male') Masculino 
                            @elseif($cliente->profile->gender === 'female') Femenino 
                            @else Otro @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profile Actions -->
            <div class="pt-6 border-t border-slate-800/60 flex flex-col gap-2">
                <button onclick="toggleModal('routine-modal')" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="dumbbell" class="w-4 h-4"></i> Asignar Rutina
                </button>
                <button onclick="toggleModal('meal-modal')" class="w-full py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 hover:border-slate-700 text-slate-200 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="apple" class="w-4 h-4"></i> Asignar Nutrición
                </button>
            </div>
        </div>

        <!-- Center Column (Weight Evolution and Measurements) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Weight Chart Card -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-white">Evolución de Peso</h3>
                        <p class="text-xs text-slate-400">Histórico de mediciones (kg)</p>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-950 text-xs font-bold text-lime-400 border border-slate-850 rounded-lg">
                        Último registro: {{ $cliente->latestMeasurement ? $cliente->latestMeasurement->weight_kg . ' kg' : 'N/A' }}
                    </span>
                </div>

                @if(!empty($weightPoints))
                    <!-- Dynamic Weight Evolution SVG Chart -->
                    <div class="relative h-60 w-full flex items-end">
                        <svg class="w-full h-full" viewBox="0 0 600 200" preserveAspectRatio="none">
                            <line x1="0" y1="20" x2="600" y2="20" stroke="#1e293b" stroke-dasharray="4" />
                            <line x1="0" y1="90" x2="600" y2="90" stroke="#1e293b" stroke-dasharray="4" />
                            <line x1="0" y1="160" x2="600" y2="160" stroke="#1e293b" stroke-dasharray="4" />
                            <line x1="0" y1="200" x2="600" y2="200" stroke="#334155" />

                            <polygon points="{{ $weightPolygonPoints }}" fill="url(#chart-grad)" />
                            <polyline points="{{ $weightPoints }}" fill="none" stroke="#a3e635" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                            <!-- Dots -->
                            @foreach($cliente->bodyMeasurements as $index => $m)
                                @php
                                    $minWeight = $cliente->bodyMeasurements->min('weight_kg') - 2;
                                    $maxWeight = $cliente->bodyMeasurements->max('weight_kg') + 2;
                                    $weightRange = $maxWeight - $minWeight ?: 1;
                                    $xStep = $cliente->bodyMeasurements->count() > 1 ? (540 / ($cliente->bodyMeasurements->count() - 1)) : 540;
                                    $x = 30 + ($index * $xStep);
                                    $y = 180 - ((($m->weight_kg - $minWeight) / $weightRange) * 140);
                                @endphp
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#a3e635" class="stroke-slate-950" stroke-width="2" />
                            @endforeach
                        </svg>
                    </div>

                    <!-- Chart Dates -->
                    <div class="flex justify-between items-center mt-4 px-4 text-xs font-semibold text-slate-500">
                        @foreach($weightDates as $date)
                            <span>{{ $date }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="h-60 flex flex-col items-center justify-center text-slate-500 text-sm">
                        <i data-lucide="scale" class="w-12 h-12 text-slate-700 mb-2"></i>
                        Aún no hay mediciones registradas para este cliente.
                    </div>
                @endif
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- IMC Card -->
                <div class="bg-slate-900/50 border border-slate-800 p-5 rounded-2xl">
                    <span class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Índice de Masa Corporal (IMC)</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-white">{{ $cliente->latestMeasurement->bmi ?? 'N/A' }}</span>
                        @if($cliente->latestMeasurement)
                            @php
                                $badgeColor = 'bg-emerald-500/10 text-emerald-400';
                                if ($cliente->latestMeasurement->bmi_category !== 'normal') {
                                    $badgeColor = 'bg-amber-500/10 text-amber-400';
                                }
                            @endphp
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase {{ $badgeColor }} rounded-full">
                                {{ __($cliente->latestMeasurement->bmi_category) }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Altura: {{ $cliente->latestMeasurement->height_cm ?? '-' }} cm</p>
                </div>

                <!-- Muscle Mass -->
                <div class="bg-slate-900/50 border border-slate-800 p-5 rounded-2xl">
                    <span class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Masa Muscular Estimada</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-white">{{ $cliente->latestMeasurement->muscle_mass_kg ?? 'N/A' }} kg</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Última medición</p>
                </div>

                <!-- Body Fat -->
                <div class="bg-slate-900/50 border border-slate-800 p-5 rounded-2xl">
                    <span class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Porcentaje de Grasa</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-white">{{ $cliente->latestMeasurement->body_fat_pct ?? 'N/A' }}%</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Grasa subcutánea</p>
                </div>
            </div>

            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <!-- Membership & Payments Section (Visible to Admins/Superadmins only) -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
                <h3 class="font-bold text-lg text-white mb-4 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-5 h-5 text-lime-400"></i> Membresía y Estado de Pagos
                </h3>
                @if($cliente->activeMembership)
                    @php
                        $paymentStatus = $cliente->activeMembership->payment_status;
                        $statusBadge = '';
                        $paymentBadge = '';

                        if ($cliente->activeMembership->status === 'active') {
                            $statusBadge = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                        } elseif ($cliente->activeMembership->status === 'expired') {
                            $statusBadge = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
                        } else {
                            $statusBadge = 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                        }

                        if ($paymentStatus === 'paid') {
                            $paymentBadge = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                            $statusText = 'Pagado';
                        } elseif ($paymentStatus === 'pending') {
                            $paymentBadge = 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                            $statusText = 'Pendiente';
                        } else {
                            $paymentBadge = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
                            $statusText = 'Vencido / Deuda';
                        }
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-slate-950/40 p-5 rounded-2xl border border-slate-850">
                        <div>
                            <span class="block text-[10px] text-slate-500 font-bold uppercase mb-1">Plan Contratado</span>
                            <span class="font-extrabold text-sm text-slate-100">{{ $cliente->activeMembership->plan->name }}</span>
                            <span class="block text-xs text-slate-400 mt-0.5">{{ number_format($cliente->activeMembership->plan->price, 2) }} {{ $cliente->activeMembership->plan->currency }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-500 font-bold uppercase mb-1">Vigencia</span>
                            <span class="font-bold text-xs text-slate-300">
                                {{ \Carbon\Carbon::parse($cliente->activeMembership->start_date)->format('d/m/Y') }} al 
                                {{ \Carbon\Carbon::parse($cliente->activeMembership->end_date)->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-500 font-bold uppercase mb-1">Estado de Membresía</span>
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full inline-block {{ $statusBadge }}">
                                {{ __($cliente->activeMembership->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-500 font-bold uppercase mb-1">Estado del Pago</span>
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full inline-block {{ $paymentBadge }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                    @if($cliente->activeMembership->notes)
                        <p class="text-xs text-slate-400 bg-slate-950/20 border border-slate-850/40 rounded-xl p-3 mt-4">
                            <strong>Notas administrativas:</strong> {{ $cliente->activeMembership->notes }}
                        </p>
                    @endif
                @else
                    <div class="py-6 text-center text-slate-500 text-sm">
                        <i data-lucide="alert-circle" class="w-8 h-8 text-slate-700 mb-2 mx-auto"></i>
                        No tiene ninguna membresía activa registrada en este gimnasio.
                    </div>
                @endif
            </div>
            @endif

            <!-- Active Plans Status (Routines & Diets) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Training Plan Card -->
                <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-white mb-3 flex items-center gap-2">
                            <i data-lucide="dumbbell" class="w-5 h-5 text-lime-400"></i> Programa de Entrenamiento
                        </h4>
                        @if($cliente->activeRoutine)
                            <h3 class="font-bold text-lg text-slate-100">{{ $cliente->activeRoutine->routine->name }}</h3>
                            <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $cliente->activeRoutine->routine->description }}</p>
                            
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs bg-slate-950/40 p-3 rounded-xl border border-slate-850">
                                <div>
                                    <span class="block text-slate-500 font-medium">Asignado por</span>
                                    <span class="font-bold text-slate-300">
                                        {{ $cliente->activeRoutine->assigner ? 'Coach ' . $cliente->activeRoutine->assigner->first_name . ' ' . substr($cliente->activeRoutine->assigner->last_name, 0, 1) . '.' : 'Administrador' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-slate-500 font-medium">Inicio</span>
                                    <span class="font-bold text-slate-300">{{ \Carbon\Carbon::parse($cliente->activeRoutine->start_date)->format('d/m/Y') }}</span>
                                </div>
                             </div>
                        @else
                            <div class="py-8 text-center text-slate-500 text-sm">
                                <p class="mb-3">Sin rutina de entrenamiento activa.</p>
                                <button onclick="toggleModal('routine-modal')" class="px-3 py-1.5 bg-lime-500 hover:bg-lime-400 text-slate-950 text-xs font-bold rounded-lg transition-colors inline-block">
                                    Asignar Rutina
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Meal Plan Card -->
                <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-white mb-3 flex items-center gap-2">
                            <i data-lucide="apple" class="w-5 h-5 text-amber-400"></i> Plan Nutricional
                        </h4>
                        @if($cliente->activeMealPlan)
                            <h3 class="font-bold text-lg text-slate-100">{{ $cliente->activeMealPlan->mealPlan->name }}</h3>
                            <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $cliente->activeMealPlan->mealPlan->description }}</p>
                            
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs bg-slate-950/40 p-3 rounded-xl border border-slate-850 mb-4">
                                <div>
                                    <span class="block text-slate-500 font-medium">Calorías Diarias</span>
                                    <span class="font-bold text-amber-400 font-semibold">{{ number_format($cliente->activeMealPlan->mealPlan->daily_calories, 0) }} kcal</span>
                                </div>
                                <div>
                                    <span class="block text-slate-500 font-medium">Duración</span>
                                    <span class="font-bold text-slate-300">{{ $cliente->activeMealPlan->mealPlan->duration_weeks }} Semanas</span>
                                </div>
                            </div>

                            <a href="{{ route('nutricion.comidas', $cliente->activeMealPlan->mealPlan->id) }}" class="w-full text-center py-2 bg-slate-950 hover:bg-slate-850 text-xs font-bold text-slate-300 rounded-xl border border-slate-850 hover:border-slate-700 transition-colors block">
                                Ver comidas de esta dieta
                            </a>
                        @else
                            <div class="py-8 text-center text-slate-500 text-sm">
                                <p class="mb-3">Sin plan nutricional activo.</p>
                                <button onclick="toggleModal('meal-modal')" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition-colors inline-block">
                                    Asignar Nutrición
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ================= MODAL: ASIGNAR RUTINA ================= -->
<div id="routine-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 animate-scale-up space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Asignar Rutina de Entrenamiento</h3>
            <button onclick="toggleModal('routine-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clientes.assign_routine', $cliente->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Rutina</label>
                <select name="routine_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" disabled selected>Selecciona una plantilla...</option>
                    @foreach($routines as $routine)
                        <option value="{{ $routine->id }}">{{ $routine->name }} ({{ $routine->duration_weeks }} sem / {{ $routine->days_per_week }}x por sem)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('routine-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar Plan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ASIGNAR NUTRICION ================= -->
<div id="meal-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 animate-scale-up space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Asignar Plan de Nutrición</h3>
            <button onclick="toggleModal('meal-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clientes.assign_meal_plan', $cliente->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Dieta</label>
                <select name="meal_plan_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" disabled selected>Selecciona una dieta...</option>
                    @foreach($mealPlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->daily_calories, 0) }} kcal)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('meal-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar Dieta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }
</script>

<style>
    /* Gradient under dynamic weight line */
    svg {
        overflow: visible;
    }
    #chart-grad stop {
        transition: stop-color 0.3s;
    }
</style>
@endsection
