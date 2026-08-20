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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left Profile Card (Sticky) -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 space-y-6 lg:sticky lg:top-24">
            <div class="text-center pb-6 border-b border-slate-800/60">
                <div class="relative inline-block">
                    <img src="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop' }}" 
                         alt="Foto de perfil" 
                         class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-lime-500/20">
                    <span class="absolute bottom-0 right-2 w-4 h-4 {{ $cliente->is_active ? 'bg-emerald-500' : 'bg-slate-500' }} border-2 border-slate-900 rounded-full"></span>
                </div>
                <h2 class="text-xl font-bold text-slate-100 mt-4">{{ $cliente->profile->first_name }} {{ $cliente->profile->last_name }}</h2>
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
                        <i data-lucide="id-card" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500">DNI</span>
                        <span class="text-slate-200 font-medium">
                            @if(auth()->user()->role === 'trainer' && $cliente->profile->dni)
                                {{ substr($cliente->profile->dni, 0, 2) . '•••' . substr($cliente->profile->dni, -2) }}
                            @else
                                {{ $cliente->profile->dni ?? 'No registrado' }}
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

                <div class="flex items-center gap-3">
                    <div class="p-2 bg-slate-950 text-slate-400 rounded-lg">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500">Entrenador Personal</span>
                        <span class="text-slate-200 font-medium text-xs">
                            @if($cliente->activeTrainerAssignment && $cliente->activeTrainerAssignment->trainer)
                                {{ $cliente->activeTrainerAssignment->trainer->first_name }} {{ $cliente->activeTrainerAssignment->trainer->last_name }}
                                <span class="block text-[9px] text-slate-400">({{ $cliente->activeTrainerAssignment->trainer->specialty }})</span>
                            @else
                                <span class="text-slate-500 italic text-[11px]">Sin asignar</span>
                            @endif
                        </span>
                    </div>
                </div>
                <!-- Active Membership Status & Daily Rate Card -->
                <div class="pt-4 border-t border-slate-800/60">
                    <span class="text-xs uppercase font-extrabold tracking-wider text-slate-500 block mb-2.5">Estado de Membresía</span>
                    @if($cliente->activeMembership)
                        @php
                            $mPlan = $cliente->activeMembership->plan;
                            $mPrice = $mPlan->price ?? 0;
                            $mDays = max(1, $mPlan->duration_days ?? 30);
                            $mDaily = $mDays > 0 ? ($mPrice / $mDays) : 0;
                        @endphp
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-100 text-xs">{{ $mPlan->name ?? 'Membresía Activa' }}</span>
                                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] font-bold uppercase rounded-full">Activa</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Costo por Día:</span>
                                <span class="font-extrabold text-amber-400">${{ number_format($mDaily, 2) }} / día</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Vence:</span>
                                <span class="font-bold text-slate-200">{{ date('d/m/Y', strtotime($cliente->activeMembership->end_date)) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 text-slate-500 text-xs text-center space-y-2">
                            <span class="italic block">Sin membresía activa en este momento</span>
                            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                                <button type="button" onclick="toggleModal('client-assign-membership-modal')" class="w-full py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i> Asignar Plan
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Profile Actions -->
            <div class="pt-6 border-t border-slate-800/60 flex flex-col gap-2">
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                    @if($cliente->activeMembership)
                        @if(($cliente->activeMembership->payment_status ?? '') === 'pending')
                            <button type="button" onclick="toggleModal('client-payment-modal')" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer animate-pulse">
                                <i data-lucide="receipt" class="w-4 h-4"></i> Registrar Cobro de Membresía
                            </button>
                        @endif
                        <button type="button" onclick="toggleModal('client-abono-modal')" class="w-full py-2.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 font-bold text-xs rounded-xl border border-amber-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                            <i data-lucide="coins" class="w-4 h-4"></i> Registrar Abono (Adelantado)
                        </button>
                    @endif
                    <button type="button" onclick="toggleModal('client-assign-membership-modal')" class="w-full py-2.5 bg-lime-500/10 hover:bg-lime-500 text-lime-400 hover:text-slate-950 font-bold text-xs rounded-xl border border-lime-500/25 transition-all flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                        <i data-lucide="credit-card" class="w-4 h-4"></i> {{ $cliente->activeMembership ? 'Renovar / Cambiar Plan' : 'Asignar Plan a Socio' }}
                    </button>
                @endif
                
                <!-- Firma Digital Preview -->
                <div class="pt-4 border-t border-slate-800/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase font-extrabold tracking-wider text-slate-500">Firma Digital del Socio</span>
                        <button type="button" onclick="openUpdateSignatureModal()" class="text-[11px] font-bold text-lime-400 hover:underline">
                            {{ !empty($cliente->profile->signature_url) ? 'Actualizar' : 'Firmar' }}
                        </button>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl flex items-center justify-center min-h-[70px]">
                        @if(!empty($cliente->profile->signature_url))
                            <img src="{{ $cliente->profile->signature_url }}" alt="Firma Digital" class="max-h-12 object-contain filter invert opacity-90">
                        @else
                            <span class="text-xs text-slate-500 italic">Pendiente por registrar firma digital</span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2 pt-4 border-t border-slate-800/60">
                    <a href="{{ route('clientes.carnet', $cliente->id) }}" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="qr-code" class="w-4 h-4"></i> Ver Carnet Digital QR
                    </a>
                    <button onclick="toggleModal('routine-assignment-modal')" class="w-full py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 hover:border-slate-700 text-slate-200 transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="dumbbell" class="w-4 h-4"></i> Asignar Rutina
                    </button>
                    <button onclick="toggleModal('mealplan-assignment-modal')" class="w-full py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 hover:border-slate-700 text-slate-200 transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="apple" class="w-4 h-4"></i> Asignar Nutrición
                    </button>
                    @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                        <button onclick="toggleModal('trainer-assignment-modal')" class="w-full py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 hover:border-slate-700 text-slate-200 transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="user-check" class="w-4 h-4"></i> Asignar Entrenador
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Center Column (Weight Evolution and Measurements) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Weight Chart Card -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-lime-400"></i> Evolución de Peso y Progreso
                        </h3>
                        <p class="text-xs text-slate-400">Histórico de evaluaciones corporales (kg)</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($cliente->bodyMeasurements && $cliente->bodyMeasurements->count() > 1)
                            @php
                                $firstWeight = $cliente->bodyMeasurements->first()->weight_kg;
                                $lastWeight = $cliente->latestMeasurement->weight_kg;
                                $weightDiff = round($lastWeight - $firstWeight, 1);
                            @endphp
                            @if($weightDiff < 0)
                                <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-black rounded-xl flex items-center gap-1" title="Reducción de Peso">
                                    <i data-lucide="arrow-down-right" class="w-3.5 h-3.5"></i> {{ abs($weightDiff) }} kg
                                </span>
                            @elseif($weightDiff > 0)
                                <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-black rounded-xl flex items-center gap-1" title="Aumento de Peso / Masa">
                                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i> +{{ $weightDiff }} kg
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-800 text-slate-400 text-xs font-bold rounded-xl">
                                    Sin variación
                                </span>
                            @endif
                        @endif

                        <span class="px-3 py-1.5 bg-slate-950 text-xs font-black text-lime-400 border border-slate-850 rounded-xl flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="scale" class="w-3.5 h-3.5 text-slate-400"></i>
                            Último: {{ $cliente->latestMeasurement ? $cliente->latestMeasurement->weight_kg . ' kg' : 'N/A' }}
                        </span>
                    </div>
                </div>

                @if(!empty($weightPoints))
                    @php
                        $mCount = $cliente->bodyMeasurements->count();
                    @endphp

                    <!-- Dynamic Weight Evolution SVG Chart -->
                    <div class="relative h-60 w-full flex items-end pt-6">
                        <svg class="w-full h-full" viewBox="0 0 600 200" preserveAspectRatio="none">
                            <line x1="0" y1="20" x2="600" y2="20" stroke="#1e293b" stroke-dasharray="4" />
                            <line x1="0" y1="90" x2="600" y2="90" stroke="#1e293b" stroke-dasharray="4" />
                            <line x1="0" y1="160" x2="600" y2="160" stroke="#1e293b" stroke-dasharray="4" />
                            <line x1="0" y1="200" x2="600" y2="200" stroke="#334155" />

                            @if($mCount === 1)
                                @php
                                    $mSingle = $cliente->bodyMeasurements->first();
                                @endphp
                                <!-- Single measurement baseline & badge -->
                                <polygon points="30,200 30,100 570,100 570,200" fill="url(#chart-grad)" />
                                <line x1="30" y1="100" x2="570" y2="100" stroke="#a3e635" stroke-width="2" stroke-dasharray="6" />
                                <circle cx="300" cy="100" r="7" fill="#a3e635" stroke="#020617" stroke-width="3" />
                                
                                <!-- Floating label for single registration -->
                                <rect x="220" y="45" width="160" height="34" rx="10" fill="#090d16" stroke="#a3e635" stroke-width="1.5" class="shadow-lg" />
                                <text x="300" y="67" fill="#a3e635" font-size="13" font-weight="900" text-anchor="middle">⚖️ {{ $mSingle->weight_kg }} kg</text>
                                <text x="300" y="130" fill="#94a3b8" font-size="11" font-weight="700" text-anchor="middle">Primer Registro de Peso ({{ \Carbon\Carbon::parse($mSingle->measured_at)->format('d/m/Y') }})</text>
                            @else
                                <polygon points="{{ $weightPolygonPoints }}" fill="url(#chart-grad)" />
                                <polyline points="{{ $weightPoints }}" fill="none" stroke="#a3e635" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                                <!-- Multi-point Dots with Weight Labels -->
                                @foreach($cliente->bodyMeasurements as $index => $m)
                                    @php
                                        $minWeight = $cliente->bodyMeasurements->min('weight_kg') - 2;
                                        $maxWeight = $cliente->bodyMeasurements->max('weight_kg') + 2;
                                        $weightRange = $maxWeight - $minWeight ?: 1;
                                        $xStep = (540 / ($mCount - 1));
                                        $x = 30 + ($index * $xStep);
                                        $y = 180 - ((($m->weight_kg - $minWeight) / $weightRange) * 140);
                                    @endphp
                                    <g>
                                        <!-- Dot -->
                                        <circle cx="{{ $x }}" cy="{{ $y }}" r="6" fill="#a3e635" stroke="#020617" stroke-width="2.5" />
                                        <!-- Weight Value Label above dot -->
                                        <text x="{{ $x }}" y="{{ $y - 12 }}" fill="#a3e635" font-size="11" font-weight="800" text-anchor="middle">{{ $m->weight_kg }} kg</text>
                                    </g>
                                @endforeach
                            @endif
                        </svg>
                    </div>

                    <!-- Chart Dates -->
                    <div class="flex justify-between items-center mt-2 px-4 text-xs font-semibold text-slate-500">
                        @if($mCount === 1)
                            <span class="mx-auto text-lime-400 font-bold">1 Registro Evaluado</span>
                        @else
                            @foreach($weightDates as $date)
                                <span>{{ $date }}</span>
                            @endforeach
                        @endif
                    </div>

                    <!-- Measurements History Table Strip -->
                    @if($mCount > 0)
                        <div class="pt-4 border-t border-slate-850">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Historial de Mediciones Registradas ({{ $mCount }})</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                @foreach($cliente->bodyMeasurements->sortByDesc('measured_at') as $bm)
                                    <div class="bg-slate-950 p-3 rounded-2xl border border-slate-850 flex items-center justify-between text-xs">
                                        <div>
                                            <span class="block font-bold text-slate-200">{{ \Carbon\Carbon::parse($bm->measured_at)->format('d/m/Y') }}</span>
                                            <span class="text-[10px] text-slate-500">IMC: {{ $bm->bmi ?? 'N/A' }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-black text-lime-400 text-sm block">{{ $bm->weight_kg }} kg</span>
                                            <span class="text-[10px] text-slate-400">Grasa: {{ $bm->body_fat_pct ? $bm->body_fat_pct . '%' : 'N/A' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
                        <span class="text-2xl font-extrabold text-slate-100">{{ $cliente->latestMeasurement->bmi ?? 'N/A' }}</span>
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
                        <span class="text-2xl font-extrabold text-slate-100">{{ $cliente->latestMeasurement->muscle_mass_kg ?? 'N/A' }} kg</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Última medición</p>
                </div>

                <!-- Body Fat -->
                <div class="bg-slate-900/50 border border-slate-800 p-5 rounded-2xl">
                    <span class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Porcentaje de Grasa</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-extrabold text-slate-100">{{ $cliente->latestMeasurement->body_fat_pct ?? 'N/A' }}%</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Grasa subcutánea</p>
                </div>
            </div>

            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <!-- Membership & Payments Section (Visible to Admins/Superadmins only) -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 space-y-6">
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-850/80">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-lime-500/10 rounded-2xl border border-lime-500/20 text-lime-400">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-100">Membresía y Auditoría Financiera</h3>
                            <p class="text-xs text-slate-400">Estado del contrato activo y registro de movimientos</p>
                        </div>
                    </div>

                    <!-- Saldo a Favor Badge -->
                    <div class="flex items-center gap-2 bg-slate-950 px-4 py-2 rounded-2xl border border-slate-850 shadow-sm shrink-0">
                        <i data-lucide="coins" class="w-4.5 h-4.5 text-amber-400"></i>
                        <span class="text-xs text-slate-400 font-semibold">Saldo a Favor:</span>
                        <span class="text-sm font-black text-amber-400">${{ number_format($cliente->credit_balance ?? 0, 2) }}</span>
                    </div>
                </div>

                @if($cliente->activeMembership)
                    @php
                        $paymentStatus = $cliente->activeMembership->payment_status;
                        $statusBadge = '';
                        $paymentBadge = '';

                        if ($cliente->activeMembership->status === 'active') {
                            $statusBadge = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                            $statusLabel = 'Activa';
                        } elseif ($cliente->activeMembership->status === 'expired') {
                            $statusBadge = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
                            $statusLabel = 'Vencida';
                        } else {
                            $statusBadge = 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                            $statusLabel = ucfirst($cliente->activeMembership->status);
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

                        $mPlan = $cliente->activeMembership->plan;
                        $mPrice = $mPlan->price ?? 0;
                        $mDays = max(1, $mPlan->duration_days ?? 30);
                        $mDailyRate = $mDays > 0 ? ($mPrice / $mDays) : 0;

                        $startDate = \Carbon\Carbon::parse($cliente->activeMembership->start_date);
                        $endDate = \Carbon\Carbon::parse($cliente->activeMembership->end_date);
                        $daysLeft = (int) max(0, \Carbon\Carbon::now()->diffInDays($endDate, false));
                    @endphp

                    <!-- Responsive 3-Column Membership Card Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Sub-Card 1: Plan & Pricing -->
                        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-850/80 flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Plan Contratado</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-900 text-slate-300 rounded-md border border-slate-800">
                                    {{ $mDays }} días vigencia
                                </span>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-slate-100">{{ $cliente->activeMembership->plan->name }}</h4>
                                <p class="text-xs font-semibold text-slate-400 mt-0.5">${{ number_format($mPrice, 2) }} {{ $mPlan->currency }}</p>
                            </div>
                            <div class="pt-2 border-t border-slate-850/50 flex items-center justify-between text-xs">
                                <span class="text-slate-400">Tarifa Diaria:</span>
                                <span class="font-bold text-amber-400">${{ number_format($mDailyRate, 2) }} / día</span>
                            </div>
                        </div>

                        <!-- Sub-Card 2: Vigencia & Dates -->
                        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-850/80 flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Período de Vigencia</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-900 text-slate-300 rounded-md border border-slate-800">
                                    {{ $daysLeft }} días restantes
                                </span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-slate-500 font-semibold w-12">Desde:</span>
                                    <span class="font-bold text-slate-200">{{ $startDate->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-slate-500 font-semibold w-12">Hasta:</span>
                                    <span class="font-bold text-slate-200">{{ $endDate->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-850/50 flex items-center justify-between text-[11px] text-slate-400">
                                <span>Estado Temporal:</span>
                                <span class="font-semibold text-slate-300">{{ $endDate->isPast() ? 'Vencido' : 'En Curso' }}</span>
                            </div>
                        </div>

                        <!-- Sub-Card 3: Badges & Action -->
                        <div class="bg-slate-950/60 p-4 rounded-2xl border border-slate-850/80 flex flex-col justify-between space-y-3">
                            <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Estado & Auditoría</span>
                            
                            <div class="flex items-center justify-around gap-2 bg-slate-900/60 p-2.5 rounded-xl border border-slate-850">
                                <div class="text-center">
                                    <span class="block text-[9px] text-slate-500 font-bold uppercase mb-1">Membresía</span>
                                    <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase rounded-full inline-block {{ $statusBadge }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <div class="w-px h-7 bg-slate-800"></div>
                                <div class="text-center">
                                    <span class="block text-[9px] text-slate-500 font-bold uppercase mb-1">Pago</span>
                                    <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase rounded-full inline-block {{ $paymentBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                @if($paymentStatus === 'pending')
                                    <button type="button" onclick="toggleModal('client-payment-modal')" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer animate-pulse">
                                        <i data-lucide="receipt" class="w-4 h-4"></i>
                                        <span>Registrar Cobro (${{ number_format($mPrice, 2) }})</span>
                                    </button>
                                @endif
                                <button type="button" onclick="toggleModal('client-abono-modal')" class="w-full py-2 bg-amber-500/15 hover:bg-amber-500 border border-amber-500/30 text-amber-400 hover:text-slate-950 font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i data-lucide="coins" class="w-4 h-4"></i>
                                    <span>Registrar Abono (+Días)</span>
                                </button>
                                <button type="button" onclick="toggleModal('client-assign-membership-modal')" class="w-full py-1.5 bg-slate-900 hover:bg-slate-850 border border-slate-800 text-slate-300 hover:text-lime-400 font-bold text-[11px] rounded-xl transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-lime-400"></i>
                                    <span>Renovar / Cambiar Plan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center text-slate-400 text-sm bg-slate-950/40 rounded-2xl border border-slate-850 space-y-3">
                        <i data-lucide="alert-circle" class="w-8 h-8 text-amber-400 mb-2 mx-auto"></i>
                        <p class="text-xs text-slate-400">Este socio no tiene ninguna membresía activa registrada en este gimnasio.</p>
                        @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                            <button type="button" onclick="toggleModal('client-assign-membership-modal')" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transition-all inline-flex items-center gap-2 cursor-pointer">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                                <span>Asignar Plan de Membresía</span>
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Historial de Abonos y Pagos (Bitácora Auditora del Socio) -->
                <div class="pt-4 border-t border-slate-850 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h4 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4 text-amber-400"></i> Bitácora de Transacciones y Abonos
                        </h4>
                        <span class="text-xs text-slate-400 font-medium">Auditoría completa de movimientos de caja</span>
                    </div>

                    @if($cliente->membershipPayments && $cliente->membershipPayments->count() > 0)
                        <div class="max-h-72 overflow-y-auto rounded-2xl border border-slate-850 bg-slate-950/40 shadow-inner">
                            <table class="w-full text-left border-collapse text-xs text-slate-300 min-w-[650px]">
                                <thead class="sticky top-0 bg-slate-950 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-850 z-10 shadow-sm">
                                    <tr>
                                        <th class="py-3 px-4 w-36">Fecha / Hora</th>
                                        <th class="py-3 px-4 w-36">Monto Abonado</th>
                                        <th class="py-3 px-4 w-28">Método</th>
                                        <th class="py-3 px-4 w-40">Referencia</th>
                                        <th class="py-3 px-4">Detalle y Auditoría</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-850/60">
                                    @foreach($cliente->membershipPayments->sortByDesc('id') as $pmt)
                                        <tr class="hover:bg-slate-900/50 transition-colors">
                                            <td class="py-3.5 px-4 font-semibold text-slate-200 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($pmt->payment_date)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-3.5 px-4 font-black text-emerald-400 whitespace-nowrap">
                                                +${{ number_format($pmt->amount, 2) }} <span class="text-[10px] text-slate-400">{{ $pmt->currency }}</span>
                                            </td>
                                            <td class="py-3.5 px-4 uppercase text-[10px] font-bold text-slate-400 whitespace-nowrap">
                                                @if($pmt->payment_method === 'cash')
                                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-slate-300 border border-slate-800">Efectivo</span>
                                                @elseif($pmt->payment_method === 'transfer')
                                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-slate-300 border border-slate-800">Transferencia</span>
                                                @elseif($pmt->payment_method === 'card')
                                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-slate-300 border border-slate-800">Tarjeta</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-slate-300 border border-slate-800">{{ $pmt->payment_method }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-300 whitespace-nowrap">
                                                {{ $pmt->reference_code ?: 'N/A' }}
                                            </td>
                                            <td class="py-3.5 px-4 text-[11px] text-slate-400">
                                                {{ $pmt->notes ?: 'Cobro de membresía procesado.' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 bg-slate-950/40 rounded-xl border border-slate-850 text-slate-500 text-xs italic text-center">
                            No se han registrado abonos ni cobros para este cliente todavía.
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Active Plans Status (Routines & Diets) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Training Plan Card -->
                <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-slate-100 mb-3 flex items-center gap-2">
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
                        <h4 class="font-bold text-slate-100 mb-3 flex items-center gap-2">
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

@push('modals')
<!-- ================= MODAL: ASIGNAR RUTINA ================= -->
<div id="routine-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto shadow-2xl animate-scale-up space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Asignar Rutina de Entrenamiento</h3>
            <button onclick="toggleModal('routine-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clientes.assign_routine', $cliente->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Rutina</label>
                <select name="routine_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                <button type="button" onclick="toggleModal('routine-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all cursor-pointer">
                    Asignar Plan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ASIGNAR NUTRICION ================= -->
<div id="meal-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto shadow-2xl animate-scale-up space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Asignar Plan de Nutrición</h3>
            <button onclick="toggleModal('meal-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clientes.assign_meal_plan', $cliente->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Dieta</label>
                <select name="meal_plan_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                <button type="button" onclick="toggleModal('meal-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all cursor-pointer">
                    Asignar Dieta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ASIGNAR ENTRENADOR ================= -->
<div id="trainer-assignment-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto shadow-2xl animate-scale-up space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Asignar Entrenador Personal</h3>
            <button onclick="toggleModal('trainer-assignment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('clientes.assign_trainer', $cliente->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Entrenador</label>
                <select name="trainer_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un entrenador...</option>
                    @foreach($trainers as $trainer)
                        @php
                            $activeMarker = ($cliente->activeTrainerAssignment && $cliente->activeTrainerAssignment->trainer_id == $trainer->id) ? ' (Actual)' : '';
                        @endphp
                        <option value="{{ $trainer->id }}" {{ $cliente->activeTrainerAssignment && $cliente->activeTrainerAssignment->trainer_id == $trainer->id ? 'selected' : '' }}>
                            {{ $trainer->first_name }} {{ $trainer->last_name }} - {{ $trainer->specialty }}{{ $activeMarker }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Notas / Instrucciones especiales (Opcional)</label>
                <textarea name="notes" rows="3" placeholder="Ej: Enfoque en pérdida de peso y rehabilitación de rodilla..." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 resize-none">{{ $cliente->activeTrainerAssignment->notes ?? '' }}</textarea>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('trainer-assignment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all cursor-pointer">
                    Asignar Entrenador
                </button>
            </div>
        </form>
    </div>
</div>

@if($cliente->activeMembership)
<!-- ================= MODAL: REGISTRAR ABONO (PERFIL SOCIO) ================= -->
<div id="client-abono-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto shadow-2xl animate-scale-up space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <i data-lucide="coins" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-100">Registrar Abono para {{ $cliente->profile->first_name }}</h3>
                    <p class="text-[11px] text-slate-400">Plan: {{ $cliente->activeMembership->plan->name ?? 'Membresía' }}</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('client-abono-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('finanzas.record_abono') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="user_membership_id" value="{{ $cliente->activeMembership->id }}">

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Monto del Abono *</label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-slate-400 font-bold">$</span>
                    <input type="number" step="0.01" min="0.01" name="amount" id="client_abono_amount_input" oninput="calculateClientAbonoPreview()" required placeholder="Ej: 5.00" class="w-full pl-8 pr-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none focus:border-amber-500/50">
                </div>
            </div>

            @php
                $clientPlan = $cliente->activeMembership->plan;
                $clientPlanPrice = $clientPlan->price ?? 0;
                $clientPlanDays = max(1, $clientPlan->duration_days ?? 30);
                $clientDailyRate = $clientPlanDays > 0 ? ($clientPlanPrice / $clientPlanDays) : 0;
                $clientEndDate = $cliente->activeMembership->end_date;
            @endphp

            <!-- Calculadora en Vivo (Live Preview Box) -->
            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850 space-y-2.5 text-xs">
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Precio del Plan:</span>
                    <span class="font-bold text-slate-200">${{ number_format($clientPlanPrice, 2) }} ({{ $clientPlanDays }} días)</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Costo Diario (1 Día):</span>
                    <span class="font-bold text-amber-400">${{ number_format($clientDailyRate, 2) }} / día</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Saldo a Favor Previo:</span>
                    <span class="font-bold text-amber-400">${{ number_format($cliente->credit_balance ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-300 border-b border-slate-850/60 pb-2 font-bold">
                    <span>Total Fondos Disponibles:</span>
                    <span id="client_abono_total_funds" class="font-black text-slate-100">${{ number_format($cliente->credit_balance ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Días Otorgados:</span>
                    <span id="client_abono_extra_days" class="font-black text-lime-400 text-sm">+0 Días</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Nuevo Saldo a Favor Restante:</span>
                    <span id="client_abono_new_credit" class="font-bold text-amber-400">${{ number_format($cliente->credit_balance ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-300 font-bold pt-1">
                    <span>Nueva Fecha de Vencimiento:</span>
                    <span id="client_abono_new_end_date" class="text-slate-100 font-black text-sm">{{ date('d/m/Y', strtotime($clientEndDate)) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Método de Pago *</label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-amber-500/50 cursor-pointer">
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="card">Tarjeta de Débito/Crédito</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">N° Referencia (Opcional)</label>
                    <input type="text" name="reference_number" placeholder="Ej: REF-9874" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-amber-500/50">
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('client-abono-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-emerald-500 hover:from-amber-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                    <span>Confirmar Abono</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- ================= MODAL: ASIGNAR PLAN / MEMBRESÍA A ESTE SOCIO ================= -->
<div id="client-assign-membership-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto shadow-2xl animate-scale-up space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-lime-500/10 text-lime-400 border border-lime-500/20">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-100">{{ $cliente->activeMembership ? 'Renovar / Cambiar Plan' : 'Asignar Membresía a Socio' }}</h3>
                    <p class="text-[11px] text-slate-400">Socio: <span class="text-lime-400 font-semibold">{{ $cliente->profile->first_name ?? 'Socio' }} {{ $cliente->profile->last_name ?? '' }}</span></p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('client-assign-membership-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('finanzas.renew_membership') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="user_id" value="{{ $cliente->id }}">

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Plan de Membresía *</label>
                <select name="plan_id" id="client_assign_plan_select" onchange="calculateClientAssignPlanPreview()" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona un plan de membresía...</option>
                    @if(isset($membershipPlans))
                        @foreach($membershipPlans as $plan)
                            <option value="{{ $plan->id }}" data-price="{{ $plan->price }}" data-days="{{ $plan->duration_days }}" data-currency="{{ $plan->currency ?? 'USD' }}">
                                {{ $plan->name }} (${{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }} - {{ $plan->duration_days }} días)
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio *</label>
                <input type="date" name="start_date" id="client_assign_start_date" onchange="calculateClientAssignPlanPreview()" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <!-- Calculadora en Vivo (Live Preview Box) -->
            <div id="client_assign_preview_box" class="bg-slate-950 p-4 rounded-2xl border border-slate-850 space-y-2.5 text-xs">
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Precio del Plan:</span>
                    <span id="client_assign_preview_price" class="font-black text-lime-400 text-sm">$0.00</span>
                </div>
                <div class="flex items-center justify-between text-slate-400 border-b border-slate-850/60 pb-2">
                    <span class="font-semibold">Duración de Cobertura:</span>
                    <span id="client_assign_preview_duration" class="font-bold text-slate-200">0 Días</span>
                </div>
                <div class="flex items-center justify-between text-slate-300 font-bold pt-1">
                    <span>Fecha de Vencimiento Estimada:</span>
                    <span id="client_assign_preview_end_date" class="text-slate-100 font-black text-sm">--/--/----</span>
                </div>
            </div>

            <!-- Opciones de Cobro / Pago Inmediato -->
            <div class="bg-slate-950/80 p-3.5 rounded-2xl border border-slate-850 space-y-3">
                @if(($cliente->credit_balance ?? 0) > 0)
                    <div class="bg-amber-500/10 border border-amber-500/20 p-2.5 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="coins" class="w-4 h-4 text-amber-400"></i>
                            <span class="text-xs font-bold text-slate-200">Saldo a Favor disponible:</span>
                        </div>
                        <span class="text-xs font-black text-amber-400">${{ number_format($cliente->credit_balance, 2) }}</span>
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="use_credit" value="1" checked class="w-4 h-4 rounded text-amber-500 bg-slate-900 border-slate-700 focus:ring-amber-500/50">
                        <span class="text-xs font-bold text-amber-300">Aplicar Saldo a Favor (${{ number_format($cliente->credit_balance, 2) }}) al plan</span>
                    </label>
                @endif

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="paid_now" value="1" id="client_assign_paid_now_checkbox" onchange="toggleAssignPaymentFields()" checked class="w-4 h-4 rounded text-lime-500 bg-slate-900 border-slate-700 focus:ring-lime-500/50">
                    <span class="text-xs font-bold text-slate-200">Registrar cobro y marcar como PAGADO de inmediato</span>
                </label>
                
                <div id="assign_payment_details_container" class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-850/80">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Método de Pago *</label>
                        <select name="payment_method" id="client_assign_payment_method" class="w-full px-3 py-2 text-xs bg-slate-900 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="card">Tarjeta de Débito/Crédito</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">N° Referencia (Opcional)</label>
                        <input type="text" name="reference_number" placeholder="Ej: REF-9874" class="w-full px-3 py-2 text-xs bg-slate-900 border border-slate-800 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('client-assign-membership-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Asignar y Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

@if($cliente->activeMembership)
<!-- ================= MODAL: REGISTRAR COBRO / PAGO DE MEMBRESÍA ================= -->
<div id="client-payment-modal" class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto shadow-2xl animate-scale-up space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-lime-500/10 text-lime-400 border border-lime-500/20">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-100">Registrar Cobro de Membresía</h3>
                    <p class="text-[11px] text-slate-400">Socio: <span class="text-lime-400 font-semibold">{{ $cliente->profile->first_name ?? 'Socio' }} {{ $cliente->profile->last_name ?? '' }}</span></p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('client-payment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('finanzas.record_payment') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="user_membership_id" value="{{ $cliente->activeMembership->id }}">

            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span>Plan Contratado:</span>
                    <span class="font-bold text-slate-200">{{ $cliente->activeMembership->plan->name ?? 'Membresía' }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400">
                    <span>Vigencia:</span>
                    <span class="font-bold text-slate-200">{{ date('d/m/Y', strtotime($cliente->activeMembership->start_date)) }} - {{ date('d/m/Y', strtotime($cliente->activeMembership->end_date)) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-300 font-bold border-t border-slate-850/80 pt-2">
                    <span>Total a Cobrar:</span>
                    <span class="font-black text-lime-400 text-base">${{ number_format($cliente->activeMembership->plan->price ?? 0, 2) }} {{ $cliente->activeMembership->plan->currency ?? 'USD' }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Monto Recibido *</label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-slate-400 font-bold">$</span>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ $cliente->activeMembership->plan->price ?? 0 }}" required class="w-full pl-8 pr-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Método de Pago *</label>
                    <select name="payment_method" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="card">Tarjeta de Débito/Crédito</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">N° Referencia (Opcional)</label>
                    <input type="text" name="reference_number" placeholder="Ej: REF-9874" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('client-payment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Confirmar Pago</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endpush

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function toggleAssignPaymentFields() {
        const checkbox = document.getElementById('client_assign_paid_now_checkbox');
        const container = document.getElementById('assign_payment_details_container');
        if (checkbox && container) {
            if (checkbox.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    }

    function calculateClientAssignPlanPreview() {
        const select = document.getElementById('client_assign_plan_select');
        const startDateInput = document.getElementById('client_assign_start_date');
        const priceEl = document.getElementById('client_assign_preview_price');
        const durationEl = document.getElementById('client_assign_preview_duration');
        const endDateEl = document.getElementById('client_assign_preview_end_date');

        if (!select || !select.selectedOptions || select.selectedOptions.length === 0) return;
        const opt = select.selectedOptions[0];
        if (!opt || !opt.value) return;

        const price = parseFloat(opt.dataset.price) || 0;
        const days = parseInt(opt.dataset.days) || 0;
        const currency = opt.dataset.currency || 'USD';

        if (priceEl) priceEl.textContent = `$${price.toFixed(2)} ${currency}`;
        if (durationEl) durationEl.textContent = `${days} Días`;

        const startVal = startDateInput && startDateInput.value ? startDateInput.value : null;
        if (startVal && days > 0) {
            const parts = startVal.split('-');
            if (parts.length === 3) {
                let d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                d.setDate(d.getDate() + days);
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                if (endDateEl) endDateEl.textContent = `${day}/${month}/${year}`;
            }
        }
    }

    function calculateClientAbonoPreview() {
        const amountInput = document.getElementById('client_abono_amount_input');
        const totalFundsEl = document.getElementById('client_abono_total_funds');
        const extraDaysEl = document.getElementById('client_abono_extra_days');
        const newCreditEl = document.getElementById('client_abono_new_credit');
        const newEndDateEl = document.getElementById('client_abono_new_end_date');

        @if($cliente->activeMembership)
            const dailyRate = {{ $clientDailyRate }};
            const endDateStr = "{{ $clientEndDate }}";
            const prevCredit = {{ (float) ($cliente->credit_balance ?? 0) }};
            const amount = parseFloat(amountInput ? amountInput.value : '0') || 0;
            const totalFunds = amount + prevCredit;

            const extraDays = dailyRate > 0 ? Math.floor(totalFunds / dailyRate) : 0;
            const costUsed = extraDays * dailyRate;
            const newCredit = Math.max(0, totalFunds - costUsed);

            if (totalFundsEl) totalFundsEl.textContent = '$' + totalFunds.toFixed(2);
            if (extraDaysEl) extraDaysEl.textContent = '+' + extraDays + ' Días';
            if (newCreditEl) newCreditEl.textContent = '$' + newCredit.toFixed(2);

            if (endDateStr) {
                let baseDate = new Date(endDateStr);
                let now = new Date();
                if (isNaN(baseDate.getTime()) || baseDate < now) {
                    baseDate = now;
                }
                baseDate.setDate(baseDate.getDate() + extraDays);
                const day = String(baseDate.getDate()).padStart(2, '0');
                const month = String(baseDate.getMonth() + 1).padStart(2, '0');
                const year = baseDate.getFullYear();
                if (newEndDateEl) newEndDateEl.textContent = `${day}/${month}/${year}`;
            }
        @endif
    }
</script>

<!-- Modal Actualizar Firma Digital -->
<div id="update-signature-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="font-bold text-base text-slate-100 flex items-center gap-2">
                <i data-lucide="pen-tool" class="w-5 h-5 text-lime-400"></i> Firma Digital del Socio
            </h3>
            <button type="button" onclick="closeUpdateSignatureModal()" class="text-slate-400 hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-400">Dibuja la firma del socio en el recuadro para registrarla en su carnet digital y expediente:</p>

        <div class="bg-slate-950 border border-slate-850 rounded-2xl p-2 relative">
            <canvas id="modal-signature-canvas" width="500" height="180" class="w-full h-44 rounded-xl bg-slate-950 touch-none cursor-crosshair border border-slate-800"></canvas>
            <div id="modal-sig-placeholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-600 text-xs font-semibold">
                Firme aquí táctilmente o con el mouse
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="clearModalSignature()" class="px-3 py-1.5 bg-slate-950 border border-slate-800 text-xs font-bold text-slate-400 hover:text-rose-400 rounded-xl transition-colors flex items-center gap-1">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Borrar Canvas
            </button>

            <div class="flex items-center gap-2">
                <button type="button" onclick="closeUpdateSignatureModal()" class="px-4 py-2 bg-slate-950 border border-slate-800 text-xs font-bold text-slate-400 rounded-xl">
                    Cancelar
                </button>
                <button type="button" onclick="saveModalSignature()" class="px-5 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 text-xs font-extrabold rounded-xl shadow-lg">
                    Guardar Firma
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    let modalSigPad = null;

    function openUpdateSignatureModal() {
        const modal = document.getElementById('update-signature-modal');
        if (modal) modal.classList.remove('hidden');

        setTimeout(() => {
            const canvas = document.getElementById('modal-signature-canvas');
            if (canvas && typeof SignaturePad !== 'undefined') {
                if (!modalSigPad) {
                    modalSigPad = new SignaturePad(canvas, {
                        penColor: '#a3e635',
                        backgroundColor: 'rgba(15, 23, 42, 0)',
                        minWidth: 1.5,
                        maxWidth: 3.5
                    });
                    modalSigPad.addEventListener("beginStroke", () => {
                        const placeholder = document.getElementById('modal-sig-placeholder');
                        if (placeholder) placeholder.classList.add('hidden');
                    });
                } else {
                    modalSigPad.clear();
                    const placeholder = document.getElementById('modal-sig-placeholder');
                    if (placeholder) placeholder.classList.remove('hidden');
                }
            }
        }, 100);
    }

    function closeUpdateSignatureModal() {
        const modal = document.getElementById('update-signature-modal');
        if (modal) modal.classList.add('hidden');
    }

    function clearModalSignature() {
        if (modalSigPad) {
            modalSigPad.clear();
            const placeholder = document.getElementById('modal-sig-placeholder');
            if (placeholder) placeholder.classList.remove('hidden');
        }
    }

    function saveModalSignature() {
        if (!modalSigPad || modalSigPad.isEmpty()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Canvas Vacío',
                    text: 'Debes firmar dentro del recuadro antes de guardar.',
                    icon: 'warning',
                    background: '#0f172a',
                    color: '#f8fafc',
                    confirmButtonColor: '#0ea5e9'
                });
            }
            return;
        }

        const dataUrl = modalSigPad.toDataURL('image/png');
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('signature_base64', dataUrl);

        fetch("{{ route('clientes.update_signature', $cliente->id) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeUpdateSignatureModal();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Firma Guardada!',
                        text: data.message,
                        icon: 'success',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#84cc16'
                    }).then(() => window.location.reload());
                } else {
                    window.location.reload();
                }
            } else {
                showPosToast(data.message || 'Error al guardar firma.', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showPosToast('Error de conexión.', 'danger');
        });
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
