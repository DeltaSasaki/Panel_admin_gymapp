@extends('layouts.admin')

@section('title', 'Finanzas y Membresías')

@section('content')
<div class="space-y-8 animate-fade-in" x-data="{ activeTab: 'membresias' }">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Finanzas y Membresías</h1>
            <p class="text-xs text-slate-400 mt-1">Administra los planes de suscripción, cobros, cupones y estados de facturación.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="toggleModal('plan-modal')" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-lime-400"></i> Crear Plan de Membresía
            </button>
            <button onclick="toggleModal('membership-modal')" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-lime-400"></i> Asignar Plan a Socio
            </button>
            <button onclick="toggleModal('promo-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="ticket" class="w-4 h-4"></i> Crear Cupón
            </button>
        </div>
    </div>

    <!-- Error/Success Alerts -->
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
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Financial Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Total Recaudado</span>
                <div class="p-2 bg-lime-500/10 text-lime-400 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-slate-100">${{ number_format($totalCollected, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-2">Suma acumulada de cobros registrados</p>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Monto por Cobrar</span>
                <div class="p-2 bg-amber-500/10 text-amber-400 rounded-lg">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-slate-100">${{ number_format($pendingAmount, 2) }}</h3>
            <p class="text-[10px] text-slate-500 mt-2">Membresías activas pendientes de pago</p>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Cupones Activos</span>
                <div class="p-2 bg-purple-500/10 text-purple-400 rounded-lg">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-slate-100">{{ $promos->where('is_active', 1)->count() }} Activos</h3>
            <p class="text-[10px] text-slate-500 mt-2">Promociones habilitadas en la sucursal</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-900">
        <button 
            @click="activeTab = 'membresias'" 
            :class="activeTab === 'membresias' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Membresías de Socios
        </button>
        <button 
            @click="activeTab = 'planes'" 
            :class="activeTab === 'planes' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Planes de Suscripción
        </button>
        <button 
            @click="activeTab = 'cupones'" 
            :class="activeTab === 'cupones' ? 'border-lime-500 text-lime-400 font-bold' : 'border-transparent text-slate-400 hover:text-slate-200'"
            class="px-5 py-3 border-b-2 text-xs uppercase tracking-wider focus:outline-none transition-all">
            Cupones (Promo Codes)
        </button>
    </div>

    <!-- Tab 1: Membresías de Socios -->
    <div x-show="activeTab === 'membresias'" class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-slate-100">Historial y Estado de Suscripciones</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Socio</th>
                        <th class="p-4">Plan contratado</th>
                        <th class="p-4">Vigencia</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-center">Pago</th>
                        <th class="p-4 text-right pr-6">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($memberships as $m)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img src="{{ $m->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=100&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover shrink-0">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $m->user->profile->first_name }} {{ $m->user->profile->last_name }}</span>
                                    <span class="block text-[10px] text-slate-500">{{ $m->user->email }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-slate-300">{{ $m->plan->name }}</span>
                                <span class="block text-[10px] text-slate-500">${{ number_format($m->plan->price, 2) }}</span>
                            </td>
                            <td class="p-4 text-slate-400">
                                <span>{{ \Carbon\Carbon::parse($m->start_date)->format('d/m/Y') }}</span>
                                <span class="text-slate-600 font-bold mx-1">al</span>
                                <span>{{ \Carbon\Carbon::parse($m->end_date)->format('d/m/Y') }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($m->status === 'active')
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full">Activo</span>
                                @elseif($m->status === 'expired')
                                    <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full">Expirado</span>
                                @else
                                    <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[9px] font-bold uppercase rounded-full">{{ __($m->status) }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($m->payment_status === 'paid')
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full">Pagado</span>
                                @elseif($m->payment_status === 'pending')
                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 text-[9px] font-bold uppercase rounded-full">Pendiente</span>
                                @else
                                    <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full">Deuda</span>
                                @endif
                            </td>
                            <td class="p-4 text-right pr-6">
                                @if($m->payment_status !== 'paid')
                                    <button onclick="openPaymentModal({{ $m->id }}, {{ $m->plan->price }})" class="px-3 py-1 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-[10px] rounded-lg transition-colors shadow-sm">
                                        Registrar Pago
                                    </button>
                                @else
                                    <span class="text-slate-500 font-medium text-[10px]">Facturado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                Ningún socio se ha suscrito a una membresía todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Planes de Suscripción -->
    <div x-show="activeTab === 'planes'" class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6">
        <h3 class="font-bold text-lg text-slate-100 mb-6">Planes de Suscripción Disponibles</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($plans as $plan)
                <div class="bg-slate-950/40 border border-slate-850 p-5 rounded-2xl flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-100 text-sm">{{ $plan->name }}</h4>
                            <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 text-[9px] font-bold uppercase rounded-full">
                                {{ $plan->duration_days }} Días
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 line-clamp-2 mb-4">{{ $plan->description ?? 'Sin descripción.' }}</p>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-slate-850 pt-3">
                        <span class="text-[10px] text-slate-500 uppercase">Precio</span>
                        <span class="text-base font-black text-lime-400">${{ number_format($plan->price, 2) }} {{ $plan->currency }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-500 text-sm">
                    No hay planes de membresía creados en este gimnasio.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 3: Cupones de Descuento -->
    <div x-show="activeTab === 'cupones'" class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-slate-100">Códigos Promocionales y Descuentos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Código</th>
                        <th class="p-4">Descuento</th>
                        <th class="p-4">Vigencia</th>
                        <th class="p-4 text-center">Usos</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-right pr-6">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($promos as $promo)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6">
                                <span class="px-3 py-1 bg-slate-950 border border-slate-855 rounded-lg text-xs font-black tracking-wide text-lime-400 uppercase">
                                    {{ $promo->code }}
                                </span>
                            </td>
                            <td class="p-4 font-semibold">
                                @if($promo->discount_type === 'percentage')
                                    {{ (float)$promo->discount_value }}% de descuento
                                @else
                                    ${{ number_format($promo->discount_value, 2) }} USD de descuento
                                @endif
                            </td>
                            <td class="p-4 text-slate-400 font-semibold">
                                @if($promo->valid_from || $promo->valid_until)
                                    <span>{{ $promo->valid_from ? \Carbon\Carbon::parse($promo->valid_from)->format('d/m/Y') : 'Inicio' }}</span>
                                    <span class="text-slate-600 font-bold mx-1">al</span>
                                    <span>{{ $promo->valid_until ? \Carbon\Carbon::parse($promo->valid_until)->format('d/m/Y') : 'Siempre' }}</span>
                                @else
                                    <span class="italic text-slate-500">Sin límite</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-slate-300">
                                {{ $promo->current_uses }} / {{ $promo->max_uses ?? '∞' }}
                            </td>
                            <td class="p-4 text-center">
                                @if($promo->is_active)
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/25">Activo</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/25">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4 text-right pr-6">
                                <form action="{{ route('finanzas.toggle_promo', $promo->id) }}" method="POST" class="inline m-0">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-[10px] font-bold border transition-colors shadow-sm {{ $promo->is_active ? 'bg-slate-950 border-slate-800 text-rose-400 hover:bg-rose-900/15' : 'bg-lime-500 border-lime-600 text-slate-950 hover:bg-lime-400' }}">
                                        {{ $promo->is_active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                No se han creado códigos promocionales todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ================= MODAL: CREAR PLAN ================= -->
<div id="plan-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Nuevo Plan de Membresía</h3>
            <button onclick="toggleModal('plan-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('finanzas.store_plan') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre del Plan</label>
                <input type="text" name="name" required placeholder="Ej: Plan Anual VIP" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción</label>
                <textarea name="description" placeholder="Incluye acceso libre a máquinas, entrenadores..." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Duración (Días)</label>
                    <input type="number" name="duration_days" required min="1" placeholder="30" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Precio</label>
                    <input type="number" step="0.01" name="price" required placeholder="50.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Moneda</label>
                <input type="text" name="currency" required value="USD" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="includes_trainer" id="includes_trainer" value="1" class="rounded border-slate-850 bg-slate-950 text-lime-500 focus:ring-lime-500">
                <label for="includes_trainer" class="text-xs text-slate-300 font-medium">¿Incluye servicio de entrenador personal?</label>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('plan-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Plan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ASIGNAR PLAN ================= -->
<div id="membership-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Asignar Membresía a Socio</h3>
            <button onclick="toggleModal('membership-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('finanzas.renew_membership') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Socio</label>
                <select name="user_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" disabled selected>Selecciona un atleta...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->profile->first_name }} {{ $client->profile->last_name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Plan</label>
                <select name="plan_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" disabled selected>Selecciona un plan de precios...</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} (${{ number_format($plan->price, 2) }} - {{ $plan->duration_days }} días)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('membership-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar y Pre-Facturar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR PAGO ================= -->
<div id="payment-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Cobro Facturado</h3>
            <button onclick="toggleModal('payment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('finanzas.record_payment') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="user_membership_id" id="payment_membership_id">
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Monto Final a Pagar ($)</label>
                <input type="number" step="0.01" name="amount" id="payment_amount" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Código Promocional (Opcional)</label>
                <div class="flex gap-2">
                    <input type="text" name="promo_code" id="payment_promo_code" placeholder="Ej: VERANO10" class="flex-1 px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 uppercase focus:outline-none focus:border-lime-500/50">
                    <button type="button" onclick="applyPromoCode()" class="px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-slate-100 text-xs font-bold rounded-xl border border-slate-750 transition-colors">
                        Aplicar
                    </button>
                </div>
                <span id="promo-feedback" class="block text-[10px] font-bold mt-1.5 hidden"></span>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Método de Pago</label>
                <select name="payment_method" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="cash" selected>Efectivo</option>
                    <option value="card">Tarjeta de Crédito/Débito</option>
                    <option value="transfer">Transferencia Bancaria</option>
                    <option value="other">Otro</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Número de Referencia (Opcional)</label>
                <input type="text" name="reference_number" placeholder="Ej: TXN-998877" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('payment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Cobro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CREAR CUPÓN (PROMO) ================= -->
<div id="promo-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Código Promocional</h3>
            <button onclick="toggleModal('promo-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('finanzas.store_promo') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Código (Ej: VERANO25)</label>
                <input type="text" name="code" required placeholder="Ej: APERTURA" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 uppercase focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tipo de Descuento</label>
                    <select name="discount_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                        <option value="percentage">Porcentaje (%)</option>
                        <option value="fixed">Valor Fijo ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Valor del Descuento</label>
                    <input type="number" step="0.01" name="discount_value" required placeholder="10.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Válido Desde (Opcional)</label>
                    <input type="date" name="valid_from" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Válido Hasta (Opcional)</label>
                    <input type="date" name="valid_until" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Límite de Usos Totales (Opcional)</label>
                <input type="number" name="max_uses" placeholder="Dejar vacío si es ilimitado" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('promo-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Crear Cupón
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    let originalAmount = 0;

    function openPaymentModal(membershipId, amount) {
        document.getElementById('payment_membership_id').value = membershipId;
        document.getElementById('payment_amount').value = amount;
        originalAmount = parseFloat(amount);
        
        // Reset promo values in form
        document.getElementById('payment_promo_code').value = '';
        const feedback = document.getElementById('promo-feedback');
        feedback.classList.add('hidden');
        feedback.innerText = '';
        
        toggleModal('payment-modal');
    }

    async function applyPromoCode() {
        const codeInput = document.getElementById('payment_promo_code');
        const code = codeInput.value.trim().toUpperCase();
        const feedback = document.getElementById('promo-feedback');
        const amountInput = document.getElementById('payment_amount');

        if (!code) {
            feedback.className = "block text-[10px] font-bold mt-1.5 text-rose-400";
            feedback.innerText = "Por favor ingresa un código.";
            feedback.classList.remove('hidden');
            return;
        }

        feedback.className = "block text-[10px] font-bold mt-1.5 text-slate-400";
        feedback.innerText = "Validando código...";
        feedback.classList.remove('hidden');

        try {
            const response = await fetch(`/api/promos/validate?code=${encodeURIComponent(code)}`);
            const data = await response.json();

            if (data.valid) {
                let discountAmount = 0;
                if (data.discount_type === 'percentage') {
                    discountAmount = originalAmount * (data.discount_value / 100);
                } else {
                    discountAmount = data.discount_value;
                }

                const finalAmount = Math.max(0, originalAmount - discountAmount);
                amountInput.value = finalAmount.toFixed(2);

                feedback.className = "block text-[10px] font-bold mt-1.5 text-emerald-400";
                feedback.innerText = `¡Código aplicado! Descuento: ${data.discount_type === 'percentage' ? data.discount_value + '%' : '$' + data.discount_value}. Nuevo total: $${finalAmount.toFixed(2)}`;
            } else {
                amountInput.value = originalAmount.toFixed(2);
                feedback.className = "block text-[10px] font-bold mt-1.5 text-rose-400";
                feedback.innerText = data.message;
            }
        } catch (error) {
            console.error(error);
            feedback.className = "block text-[10px] font-bold mt-1.5 text-rose-400";
            feedback.innerText = "Error de servidor al validar el cupón.";
        }
    }
</script>
@endsection
