@extends('layouts.admin')

@section('title', 'Gestión de Cajeros y Recepción')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="calculator" class="w-8 h-8 text-lime-400"></i>
                Personal de Caja y Recepción
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Administra las cuentas de cajeros, turnos de recepción, permisos de cobro en TPV y salarios de nómina.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="openCreateCashierModal()" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4 stroke-[3px]"></i> Registrar Cajero
            </button>
        </div>
    </div>

    @php
        $totalCashiers = $cashiers->count();
        $activeCashiers = $cashiers->where('is_active', 1);
        $totalActive = $activeCashiers->count();
        $totalInactive = $cashiers->where('is_active', 0)->count();
        $totalPayroll = $activeCashiers->sum('salary');
        $totalProcessed = $cashiers->sum(function($c) {
            return ($c->membershipPayments ? $c->membershipPayments->count() : 0) + ($c->productSales ? $c->productSales->count() : 0);
        });
    @endphp

    <!-- Quick Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Total Cajeros Staff</span>
                <h3 class="text-2xl font-black text-slate-100"><span id="stat_total">{{ $totalCashiers }}</span> <span class="text-xs font-normal text-slate-400">cuentas</span></h3>
            </div>
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-400">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Cajeros Activos</span>
                <h3 class="text-2xl font-black text-emerald-400"><span id="stat_active">{{ $totalActive }}</span> <span class="text-xs font-normal text-slate-400">habilitados</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-cyan-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Operaciones Procesadas</span>
                <h3 class="text-2xl font-black text-cyan-400"><span>{{ $totalProcessed }}</span> <span class="text-xs font-normal text-slate-400">cobros & ventas</span></h3>
            </div>
            <div class="p-3 bg-cyan-500/10 border border-cyan-500/20 rounded-2xl text-cyan-400">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Nómina Mensual Cajeros</span>
                <h3 class="text-2xl font-black text-amber-400">$ <span id="stat_payroll">{{ number_format($totalPayroll, 2) }}</span></h3>
            </div>
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-xl">
        <div class="flex flex-wrap items-center gap-3">
            <h3 class="font-extrabold text-xs uppercase tracking-wider text-slate-300 mr-2 flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Filtro por Estado:
            </h3>

            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-950 p-1.5 rounded-2xl border border-slate-855">
                <button type="button" onclick="setCashierStatusFilter('all')" id="cashier-status-filter-all" class="cashier-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                    Todos (<span id="count-status-all">{{ $totalCashiers }}</span>)
                </button>
                <button type="button" onclick="setCashierStatusFilter('1')" id="cashier-status-filter-1" class="cashier-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Activos (<span id="count-status-active">{{ $totalActive }}</span>)
                </button>
                <button type="button" onclick="setCashierStatusFilter('0')" id="cashier-status-filter-0" class="cashier-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    Inactivos (<span id="count-status-inactive">{{ $totalInactive }}</span>)
                </button>
            </div>
        </div>

        <!-- Live Search Bar -->
        <div class="relative w-full xl:w-80">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-cashier-input" oninput="onCashierFilterChange()" placeholder="Buscar por nombre, DNI, turno, correo..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-855 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 font-medium">
        </div>
    </div>

    <!-- Cashiers Grid Container -->
    <div id="cashiers-grid-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($cashiers as $cashier)
            @php
                $fullName = trim(($cashier->first_name ?? '') . ' ' . ($cashier->last_name ?? ''));
                $dni = $cashier->user && $cashier->user->profile ? ($cashier->user->profile->dni ?? 'Sin DNI') : 'Sin DNI';
                $photoUrl = $cashier->photo_url 
                    ? asset($cashier->photo_url) 
                    : ($cashier->user && $cashier->user->profile && $cashier->user->profile->profile_photo ? asset($cashier->user->profile->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($fullName ?: 'Cajero') . '&background=0f172a&color=a3e635&size=150');
                $gymName = $cashier->gym ? $cashier->gym->name : 'Sede Principal';
                $paymentsCount = $cashier->membershipPayments ? $cashier->membershipPayments->count() : 0;
                $salesCount = $cashier->productSales ? $cashier->productSales->count() : 0;
                $totalOps = $paymentsCount + $salesCount;
                $hireDateFormatted = $cashier->hire_date ? \Carbon\Carbon::parse($cashier->hire_date)->translatedFormat('d M Y') : null;
            @endphp
            <div id="cashier_card_{{ $cashier->id }}"
                 data-cashier-card
                 data-name="{{ strtolower($fullName) }}"
                 data-dni="{{ strtolower($dni) }}"
                 data-email="{{ strtolower($cashier->email) }}"
                 data-shift="{{ strtolower($cashier->shift ?? '') }}"
                 data-active="{{ $cashier->is_active ? 1 : 0 }}"
                 class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all flex flex-col justify-between gap-5 relative overflow-hidden group shadow-xl backdrop-blur-sm {{ $cashier->is_active ? '' : 'opacity-60 bg-slate-950/40 border-slate-855' }}">
                
                <div class="space-y-4">
                    
                    <!-- Card Top: Sucursal Badge (Superadmin only) & Status -->
                    <div class="flex items-center justify-between gap-2 border-b border-slate-800/60 pb-3">
                        @if(auth()->user()->role === 'superadmin')
                            <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-[10px] font-extrabold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="building" class="w-3 h-3 text-lime-400"></i>
                                <span id="cashier_gym_{{ $cashier->id }}">{{ $gymName }}</span>
                            </span>
                        @else
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-lime-400"></i> Personal de Recepción
                            </span>
                        @endif

                        <span id="cashier_status_badge_{{ $cashier->id }}" class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 {{ $cashier->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                            {{ $cashier->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <!-- Profile Avatar & Identity -->
                    <div class="flex items-start gap-3.5">
                        <div class="relative shrink-0">
                            <img src="{{ $photoUrl }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=Cajero&background=0f172a&color=a3e635&size=150'" id="cashier_photo_img_{{ $cashier->id }}" class="w-14 h-14 rounded-2xl object-cover border border-slate-700 shadow-md group-hover:scale-105 transition-transform">
                            <span id="cashier_dot_{{ $cashier->id }}" class="w-3.5 h-3.5 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 {{ $cashier->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-base text-slate-100 group-hover:text-lime-400 transition-colors truncate" id="cashier_name_{{ $cashier->id }}">{{ $fullName }}</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-mono font-bold rounded" id="cashier_dni_{{ $cashier->id }}">DNI: {{ $dni }}</span>
                                <span class="text-[10px] text-slate-400 truncate" id="cashier_email_{{ $cashier->id }}">{{ $cashier->email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Shift & Tenure Info Pill -->
                    <div class="p-3.5 bg-slate-950/70 border border-slate-855 rounded-2xl space-y-2 text-xs font-semibold">
                        <div class="flex items-center justify-between text-slate-300">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Turno Asignado:</span>
                            <span class="font-black text-cyan-400" id="cashier_shift_{{ $cashier->id }}">{{ $cashier->shift ?? 'Mañana (06:00 - 14:00)' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Teléfono:</span>
                            <span class="font-bold text-slate-300 truncate" id="cashier_phone_{{ $cashier->id }}">{{ $cashier->phone ?? 'Sin registrar' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300 border-t border-slate-855/80 pt-2">
                            <span class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Antigüedad:</span>
                            <span class="font-extrabold text-amber-400" id="cashier_tenure_{{ $cashier->id }}">
                                {{ $cashier->tenure }} @if($hireDateFormatted) (Desde {{ $hireDateFormatted }}) @endif
                            </span>
                        </div>
                    </div>

                    <!-- Operations & Salary Summary Grid -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-center">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Salario Nómina</span>
                            <span class="font-black text-emerald-400 text-sm" id="cashier_salary_{{ $cashier->id }}">$ {{ number_format($cashier->salary, 2) }}</span>
                        </div>
                        <div class="p-2.5 bg-purple-500/10 border border-purple-500/20 rounded-xl text-center">
                            <span class="block text-[9px] text-slate-400 uppercase font-extrabold">Operaciones TPV</span>
                            <span class="font-black text-purple-400 text-sm" id="cashier_ops_{{ $cashier->id }}">{{ $totalOps }} reg.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex justify-between items-center border-t border-slate-800/80 pt-4 text-xs font-semibold">
                    <button type="button" 
                            onclick="openCashierDetailsModal({{ $cashier->id }})" 
                            class="px-3 py-1.5 bg-lime-500/10 hover:bg-lime-500 text-lime-400 hover:text-slate-950 border border-lime-500/25 rounded-xl transition-all font-bold flex items-center gap-1.5 shadow-sm" 
                            title="Ver Ficha y Registro de Operaciones">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Ver Ficha</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <!-- Edit Button -->
                        <button type="button" onclick='openEditCashierModal({{ json_encode($cashier->load("user.profile", "gym")) }})' class="p-2 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Datos del Cajero">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>

                        <!-- Toggle Active Status Button -->
                        <button type="button" onclick="openToggleCashierModal({{ $cashier->id }}, '{{ addslashes($fullName) }}', {{ $cashier->is_active ? 1 : 0 }})" 
                                id="cashier_toggle_btn_{{ $cashier->id }}"
                                class="p-2 {{ $cashier->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                title="{{ $cashier->is_active ? 'Inhabilitar Cajero' : 'Reactivar Cajero' }}">
                            <i data-lucide="{{ $cashier->is_active ? 'power' : 'check-circle' }}" class="w-4 h-4"></i>
                        </button>

                        <!-- Permanent Delete Button -->
                        <button type="button" onclick="openDeleteCashierModal({{ $cashier->id }}, '{{ addslashes($fullName) }}')" class="p-2 bg-slate-950 hover:bg-rose-600 text-slate-400 hover:text-white border border-slate-800 hover:border-rose-600 rounded-xl transition-all shadow-sm" title="Eliminar Cajero">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div id="no_cashier_empty" class="col-span-full py-16 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl">
                <i data-lucide="calculator" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                <p class="font-bold text-slate-400">No hay cajeros registrados en el staff</p>
                <p class="text-xs text-slate-500 mt-1">Registra tu primer personal de recepción haciendo clic en "Registrar Cajero".</p>
            </div>
        @endforelse

        <div id="no_cashier_search_row" class="col-span-full py-12 text-center text-slate-500 bg-slate-900/20 border border-slate-800/60 rounded-3xl hidden">
            <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
            <p class="font-bold text-slate-400 text-sm">No se encontraron cajeros que coincidan con la búsqueda.</p>
        </div>
    </div>

    <!-- Pagination Controls Footer -->
    <div id="cashier_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
        <span id="cashier_pagination_info">Mostrando cajeros...</span>
        <div class="flex items-center gap-2">
            <button type="button" id="cashier_prev_page_btn" onclick="changeCashierPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Anterior
            </button>
            <span id="cashier_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
            <button type="button" id="cashier_next_page_btn" onclick="changeCashierPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Siguiente
            </button>
        </div>
    </div>

</div>

<!-- ================= MODAL: FICHA / EXPEDIENTE DEL CAJERO ================= -->
<div id="modal-cashier-details" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-3xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] flex flex-col">
        
        <!-- Modal Top Bar -->
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center bg-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-lime-500/10 border border-lime-500/20 text-lime-400">
                    <i data-lucide="calculator" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-black text-base text-slate-100" id="details_modal_title">Ficha del Cajero</h3>
                    <p class="text-xs text-slate-400 font-medium" id="details_modal_subtitle">Turno, nómina y registro de operaciones</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('modal-cashier-details')" class="p-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800 rounded-xl transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
            
            <!-- Hero Header -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 p-5 bg-slate-950/80 border border-slate-855 rounded-3xl">
                <img src="" id="details_photo_img" class="w-20 h-20 rounded-2xl object-cover border border-slate-700 shadow-xl shrink-0">
                
                <div class="space-y-2 text-center sm:text-left flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-xl font-black text-white" id="details_name">Nombre Cajero</h2>
                            <span class="text-xs text-cyan-400 font-bold" id="details_shift_badge">Turno Mañana</span>
                        </div>
                        <span id="details_status_pill" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border w-fit mx-auto sm:mx-0">
                            Activo
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-slate-400 text-xs pt-1">
                        <span class="flex items-center gap-1.5"><i data-lucide="id-card" class="w-4 h-4 text-slate-500"></i> <strong id="details_dni" class="text-slate-300">DNI</strong></span>
                        <span class="flex items-center gap-1.5"><i data-lucide="mail" class="w-4 h-4 text-slate-500"></i> <span id="details_email" class="text-slate-300">cajero@gym.com</span></span>
                        <span class="flex items-center gap-1.5"><i data-lucide="phone" class="w-4 h-4 text-slate-500"></i> <span id="details_phone" class="text-slate-300">+51 987654321</span></span>
                        @if(auth()->user()->role === 'superadmin')
                            <span class="flex items-center gap-1.5"><i data-lucide="building" class="w-4 h-4 text-slate-500"></i> <span id="details_gym" class="text-slate-300">Sede</span></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detail Tabs Switcher -->
            <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                <button type="button" onclick="switchDetailsTab('profile')" id="tab-btn-profile" class="px-4 py-2 rounded-xl font-black text-xs bg-lime-500/10 text-lime-400 border border-lime-500/30 transition-all flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Turno & Nómina
                </button>
                <button type="button" onclick="switchDetailsTab('payments')" id="tab-btn-payments" class="px-4 py-2 rounded-xl font-bold text-xs bg-slate-950 text-slate-400 border border-slate-855 hover:text-slate-200 transition-all flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4"></i> Cobros Membresías (<span id="details_payments_count_badge">0</span>)
                </button>
                <button type="button" onclick="switchDetailsTab('sales')" id="tab-btn-sales" class="px-4 py-2 rounded-xl font-bold text-xs bg-slate-950 text-slate-400 border border-slate-855 hover:text-slate-200 transition-all flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i> Ventas de Tienda (<span id="details_sales_count_badge">0</span>)
                </button>
            </div>

            <!-- TAB 1: PROFILE & PAYROLL -->
            <div id="details-tab-profile" class="space-y-4">
                
                <!-- Quick Stats row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 bg-slate-950 border border-slate-855 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Salario Mensual</span>
                        <span class="text-base font-black text-emerald-400" id="details_stat_salary">$0.00</span>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-855 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Total Recaudado</span>
                        <span class="text-base font-black text-cyan-400" id="details_stat_collected">$0.00</span>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-855 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Antigüedad</span>
                        <span class="text-base font-black text-amber-400" id="details_stat_tenure">0 meses</span>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-855 rounded-2xl">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Fecha Ingreso</span>
                        <span class="text-base font-black text-slate-200" id="details_stat_hire_date">01/01/2025</span>
                    </div>
                </div>

                <!-- Notes & Shift Info -->
                <div class="p-4 bg-slate-950/70 border border-slate-855 rounded-2xl space-y-3">
                    <div>
                        <span class="block text-[10px] text-slate-400 uppercase font-extrabold tracking-wider mb-1">Horario y Turno Habitual:</span>
                        <p class="text-slate-200 font-bold" id="details_shift_text">Mañana (06:00 - 14:00)</p>
                    </div>
                    <div class="border-t border-slate-855 pt-3">
                        <span class="block text-[10px] text-slate-400 uppercase font-extrabold tracking-wider mb-1">Notas Internas / Observaciones:</span>
                        <p class="text-slate-300 leading-relaxed font-medium" id="details_notes_text">Sin observaciones registradas.</p>
                    </div>
                </div>

            </div>

            <!-- TAB 2: MEMBERSHIP PAYMENTS -->
            <div id="details-tab-payments" class="space-y-3 hidden">
                <div id="details_payments_list_container" class="space-y-2">
                    <!-- Populated via AJAX -->
                </div>
            </div>

            <!-- TAB 3: PRODUCT SALES -->
            <div id="details-tab-sales" class="space-y-3 hidden">
                <div id="details_sales_list_container" class="space-y-2">
                    <!-- Populated via AJAX -->
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-800 bg-slate-900 flex justify-end shrink-0">
            <button type="button" onclick="toggleModal('modal-cashier-details')" class="px-5 py-2 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-slate-100 rounded-xl font-bold transition-all">
                Cerrar Ficha
            </button>
        </div>

    </div>
</div>

<!-- ================= MODAL: REGISTRAR / CREAR CAJERO ================= -->
<div id="modal-create-cashier" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-lime-400"></i> Registrar Nuevo Cajero
            </h3>
            <button type="button" onclick="toggleModal('modal-create-cashier')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-cashier-form" action="{{ route('cajeros.store') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateCashier(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            
            @if(auth()->user()->role === 'superadmin' && count($gyms) > 1)
                <div>
                    <label for="create_gym_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Sucursal de Destino *</label>
                    <select name="gym_id" id="create_gym_id" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        @foreach($gyms as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_first_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombres *</label>
                    <input type="text" name="first_name" id="create_first_name" required placeholder="Ej: María Elena" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_last_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Apellidos *</label>
                    <input type="text" name="last_name" id="create_last_name" required placeholder="Ej: Rojas Castro" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_dni" class="block text-slate-400 uppercase tracking-wider mb-1.5">DNI / Documento *</label>
                    <input type="text" name="dni" id="create_dni" required placeholder="Ej: 71234567" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" id="create_phone" placeholder="Ej: +51 987654321" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo Electrónico (Acceso) *</label>
                    <input type="email" name="email" id="create_email" required placeholder="cajero@gym.com" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="create_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Contraseña de Acceso *</label>
                    <input type="password" name="password" id="create_password" required minlength="6" placeholder="Mínimo 6 caracteres" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="create_shift" class="block text-slate-400 uppercase tracking-wider mb-1.5">Turno Asignado</label>
                    <select name="shift" id="create_shift" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="Mañana (06:00 - 14:00)">Mañana (06:00 - 14:00)</option>
                        <option value="Tarde (14:00 - 22:00)">Tarde (14:00 - 22:00)</option>
                        <option value="Noche (22:00 - 06:00)">Noche (22:00 - 06:00)</option>
                        <option value="Completo (08:00 - 18:00)">Completo (08:00 - 18:00)</option>
                        <option value="Rotativo">Rotativo</option>
                    </select>
                </div>
                <div>
                    <label for="create_hire_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Contratación</label>
                    <input type="date" name="hire_date" id="create_hire_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-3 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
                <div>
                    <label for="create_salary" class="block text-slate-400 uppercase tracking-wider mb-1.5">Salario ($) *</label>
                    <input type="number" step="0.01" name="salary" id="create_salary" required min="0" placeholder="1200.00" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="create_photo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto de Perfil</label>
                <input type="file" name="photo" id="create_photo" accept="image/*" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-3 py-2 text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
            </div>

            <div>
                <label for="create_notes" class="block text-slate-400 uppercase tracking-wider mb-1.5">Notas / Observaciones</label>
                <textarea name="notes" id="create_notes" rows="2" placeholder="Observaciones de caja, responsabilidad de llaves o fondo..." class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-create-cashier')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="create-cashier-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Registrar Cajero</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR CAJERO ================= -->
<div id="modal-edit-cashier" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center sticky top-0 bg-slate-900 z-10">
            <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Editar Datos de Cajero
            </h3>
            <button type="button" onclick="toggleModal('modal-edit-cashier')" class="text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-cashier-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditCashier(event)" class="p-6 space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_first_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombres *</label>
                    <input type="text" name="first_name" id="edit_first_name" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_last_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Apellidos *</label>
                    <input type="text" name="last_name" id="edit_last_name" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_dni" class="block text-slate-400 uppercase tracking-wider mb-1.5">DNI / Documento *</label>
                    <input type="text" name="dni" id="edit_dni" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono / WhatsApp</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo Electrónico (Acceso) *</label>
                    <input type="email" name="email" id="edit_email" required class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label for="edit_password" class="block text-slate-400 uppercase tracking-wider mb-1.5">Cambiar Contraseña (Opcional)</label>
                    <input type="password" name="password" id="edit_password" placeholder="Dejar en blanco para no cambiar" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="edit_shift" class="block text-slate-400 uppercase tracking-wider mb-1.5">Turno Asignado</label>
                    <select name="shift" id="edit_shift" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="Mañana (06:00 - 14:00)">Mañana (06:00 - 14:00)</option>
                        <option value="Tarde (14:00 - 22:00)">Tarde (14:00 - 22:00)</option>
                        <option value="Noche (22:00 - 06:00)">Noche (22:00 - 06:00)</option>
                        <option value="Completo (08:00 - 18:00)">Completo (08:00 - 18:00)</option>
                        <option value="Rotativo">Rotativo</option>
                    </select>
                </div>
                <div>
                    <label for="edit_hire_date" class="block text-slate-400 uppercase tracking-wider mb-1.5">Fecha Contratación</label>
                    <input type="date" name="hire_date" id="edit_hire_date" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-3 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                </div>
                <div>
                    <label for="edit_salary" class="block text-slate-400 uppercase tracking-wider mb-1.5">Salario ($) *</label>
                    <input type="number" step="0.01" name="salary" id="edit_salary" required min="0" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 font-bold focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label for="edit_photo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nueva Foto de Perfil (Opcional)</label>
                <input type="file" name="photo" id="edit_photo" accept="image/*" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-3 py-2 text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
            </div>

            <div>
                <label for="edit_notes" class="block text-slate-400 uppercase tracking-wider mb-1.5">Notas / Observaciones</label>
                <textarea name="notes" id="edit_notes" rows="2" class="w-full bg-slate-950 border border-slate-855 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('modal-edit-cashier')" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-855 text-slate-300 hover:text-slate-100 rounded-xl transition-all">Cancelar</button>
                <button type="submit" id="edit-cashier-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: INHABILITAR / REACTIVAR CAJERO ================= -->
<div id="modal-toggle-cashier" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-sm mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto flex items-center justify-center">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        <div class="space-y-1">
            <h3 class="font-extrabold text-base text-slate-100" id="modal-cashier-status-title">Cambiar Estado de Cajero</h3>
            <p class="text-xs text-slate-400" id="modal-cashier-status-desc">¿Estás seguro de realizar esta acción?</p>
        </div>
        <form id="toggle-cashier-form" action="" method="POST" onsubmit="submitToggleCashier(event)" class="pt-2 flex items-center gap-3">
            @csrf
            <button type="button" onclick="toggleModal('modal-toggle-cashier')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-slate-300 font-bold text-xs rounded-xl transition-all">Cancelar</button>
            <button type="submit" id="toggle-cashier-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <span id="modal-cashier-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<!-- ================= MODAL: ELIMINAR PERMANENTE CAJERO ================= -->
<div id="modal-delete-cashier" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-sm mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center">
            <i data-lucide="trash-2" class="w-6 h-6"></i>
        </div>
        <div class="space-y-1">
            <h3 class="font-extrabold text-base text-slate-100">Eliminar del Staff</h3>
            <p class="text-xs text-slate-400" id="modal-delete-cashier-desc">Esta acción eliminará la cuenta del cajero de forma definitiva.</p>
        </div>
        <form id="delete-cashier-form" action="" method="POST" onsubmit="submitDeleteCashier(event)" class="pt-2 flex items-center gap-3">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('modal-delete-cashier')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-slate-300 font-bold text-xs rounded-xl transition-all">Cancelar</button>
            <button type="submit" id="delete-cashier-submit-btn" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                Eliminar
            </button>
        </form>
    </div>
</div>

<script>
    (function() {
        // Global State & Pagination
        let currentCashierStatusFilter = 'all';
        let currentCashierSearchQuery = '';
        let currentCashierPage = 1;
        const cashiersPerPage = 9;

        function initCashierView() {
            if (window.lucide) window.lucide.createIcons();
            renderCashierPage();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCashierView);
        } else {
            setTimeout(initCashierView, 10);
        }

    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    // Modal Details Tab Switcher
    function switchDetailsTab(tabName) {
        const tabs = ['profile', 'payments', 'sales'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const content = document.getElementById(`details-tab-${t}`);
            if (t === tabName) {
                if (btn) {
                    btn.className = 'px-4 py-2 rounded-xl font-black text-xs bg-lime-500/10 text-lime-400 border border-lime-500/30 transition-all flex items-center gap-2';
                }
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.className = 'px-4 py-2 rounded-xl font-bold text-xs bg-slate-950 text-slate-400 border border-slate-855 hover:text-slate-200 transition-all flex items-center gap-2';
                }
                if (content) content.classList.add('hidden');
            }
        });
        if (window.lucide) window.lucide.createIcons();
    }

    // Open Cashier Details Modal (AJAX)
    async function openCashierDetailsModal(cashierId) {
        toggleModal('modal-cashier-details');
        switchDetailsTab('profile');

        const titleEl = document.getElementById('details_modal_title');
        const nameEl = document.getElementById('details_name');
        const shiftBadge = document.getElementById('details_shift_badge');
        const statusPill = document.getElementById('details_status_pill');
        const dniEl = document.getElementById('details_dni');
        const emailEl = document.getElementById('details_email');
        const phoneEl = document.getElementById('details_phone');
        const gymEl = document.getElementById('details_gym');
        const photoImg = document.getElementById('details_photo_img');

        const salaryEl = document.getElementById('details_stat_salary');
        const collectedEl = document.getElementById('details_stat_collected');
        const tenureEl = document.getElementById('details_stat_tenure');
        const hireDateEl = document.getElementById('details_stat_hire_date');
        const shiftText = document.getElementById('details_shift_text');
        const notesText = document.getElementById('details_notes_text');

        const paymentsCountBadge = document.getElementById('details_payments_count_badge');
        const paymentsContainer = document.getElementById('details_payments_list_container');
        const salesCountBadge = document.getElementById('details_sales_count_badge');
        const salesContainer = document.getElementById('details_sales_list_container');

        if (nameEl) nameEl.textContent = 'Cargando ficha de cajero...';
        if (paymentsContainer) paymentsContainer.innerHTML = '<div class="p-8 text-center text-slate-500"><i data-lucide="loader-2" class="w-6 h-6 mx-auto animate-spin mb-2"></i>Cargando historial de pagos...</div>';
        if (salesContainer) salesContainer.innerHTML = '<div class="p-8 text-center text-slate-500"><i data-lucide="loader-2" class="w-6 h-6 mx-auto animate-spin mb-2"></i>Cargando ventas de mostrador...</div>';
        if (window.lucide) window.lucide.createIcons();

        try {
            const response = await fetch(`/cajeros/${cashierId}/detalles`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const c = data.cashier;
                const p = c.user?.profile;
                const fullName = `${c.first_name || ''} ${c.last_name || ''}`.trim();
                const photoSrc = c.photo_url ? `/${c.photo_url}` : (p?.profile_photo ? `/${p.profile_photo}` : 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=150&auto=format&fit=crop');

                if (titleEl) titleEl.textContent = `Ficha de Caja: ${fullName}`;
                if (nameEl) nameEl.textContent = fullName;
                if (shiftBadge) shiftBadge.textContent = c.shift || 'Mañana (06:00 - 14:00)';
                if (dniEl) dniEl.textContent = p?.dni || 'Sin DNI';
                if (emailEl) emailEl.textContent = c.email || 'Sin correo';
                if (phoneEl) phoneEl.textContent = c.phone || p?.phone || 'Sin teléfono';
                if (gymEl) gymEl.textContent = c.gym?.name || 'Sede Principal';
                if (photoImg) photoImg.src = photoSrc;

                if (statusPill) {
                    if (c.is_active) {
                        statusPill.className = 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                        statusPill.textContent = 'Habilitado en TPV';
                    } else {
                        statusPill.className = 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border bg-rose-500/10 text-rose-400 border-rose-500/20';
                        statusPill.textContent = 'Inactivo / Suspendido';
                    }
                }

                if (salaryEl) salaryEl.textContent = `$${parseFloat(c.salary || 0).toFixed(2)}`;
                if (collectedEl) collectedEl.textContent = data.total_collected || '$0.00';
                if (tenureEl) tenureEl.textContent = c.tenure || '0 meses';
                if (hireDateEl) hireDateEl.textContent = c.hire_date || 'No registrado';
                if (shiftText) shiftText.textContent = c.shift || 'Mañana (06:00 - 14:00)';
                if (notesText) notesText.textContent = c.notes || 'Sin observaciones registradas.';

                // Render Payments
                const payments = data.recent_payments || [];
                if (paymentsCountBadge) paymentsCountBadge.textContent = data.total_payments_count ?? payments.length;

                if (paymentsContainer) {
                    if (payments.length === 0) {
                        paymentsContainer.innerHTML = `
                            <div class="p-8 text-center bg-slate-950/40 border border-slate-855 rounded-2xl">
                                <i data-lucide="credit-card" class="w-8 h-8 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400">No ha procesado cobros de membresías aún</p>
                            </div>
                        `;
                    } else {
                        paymentsContainer.innerHTML = payments.map(pm => `
                            <div class="flex items-center justify-between p-3.5 bg-slate-950/70 border border-slate-855 rounded-2xl hover:border-lime-500/30 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center font-black text-emerald-400 shrink-0">
                                        <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-200 text-sm">${escapeHtml(pm.client_name)}</h4>
                                        <span class="text-[10px] text-slate-400 font-medium">${pm.payment_date} • Ref: ${escapeHtml(pm.reference)}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-emerald-400">${pm.amount}</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">${pm.payment_method}</span>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                // Render Sales
                const sales = data.recent_sales || [];
                if (salesCountBadge) salesCountBadge.textContent = data.total_sales_count ?? sales.length;

                if (salesContainer) {
                    if (sales.length === 0) {
                        salesContainer.innerHTML = `
                            <div class="p-8 text-center bg-slate-950/40 border border-slate-855 rounded-2xl">
                                <i data-lucide="shopping-bag" class="w-8 h-8 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400">No ha registrado ventas de mostrador aún</p>
                            </div>
                        `;
                    } else {
                        salesContainer.innerHTML = sales.map(s => `
                            <div class="flex items-center justify-between p-3.5 bg-slate-950/70 border border-slate-855 rounded-2xl hover:border-lime-500/30 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center font-black text-cyan-400 shrink-0">
                                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-200 text-sm">${escapeHtml(s.client_name)}</h4>
                                        <span class="text-[10px] text-slate-400 font-medium">${s.sale_date}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-cyan-400">${s.total}</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">${s.payment_method}</span>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                if (window.lucide) window.lucide.createIcons();
            } else {
                showToast(data.message || 'Error al cargar ficha.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al consultar la ficha del cajero.', 'error');
        }
    }

    // Filter Handling
    function setCashierStatusFilter(status) {
        currentCashierStatusFilter = status;
        document.querySelectorAll('.cashier-status-tab-btn').forEach(btn => {
            btn.className = 'cashier-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all';
        });

        const activeBtn = document.getElementById(`cashier-status-filter-${status}`);
        if (activeBtn) {
            activeBtn.className = 'cashier-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-lime-400 border border-slate-800 transition-all';
        }

        currentCashierPage = 1;
        renderCashierPage();
    }

    function onCashierFilterChange() {
        const searchInput = document.getElementById('search-cashier-input');
        currentCashierSearchQuery = (searchInput ? searchInput.value : '').toLowerCase().trim();
        currentCashierPage = 1;
        renderCashierPage();
    }

    function renderCashierPage() {
        const cards = Array.from(document.querySelectorAll('[data-cashier-card]'));
        const searchRow = document.getElementById('no_cashier_search_row');

        const filtered = cards.filter(card => {
            const name = card.getAttribute('data-name') || '';
            const dni = card.getAttribute('data-dni') || '';
            const email = card.getAttribute('data-email') || '';
            const shift = card.getAttribute('data-shift') || '';
            const active = card.getAttribute('data-active') || '';

            // Status filter
            if (currentCashierStatusFilter !== 'all' && active !== currentCashierStatusFilter) {
                return false;
            }

            // Search query filter
            if (currentCashierSearchQuery) {
                const matchName = name.includes(currentCashierSearchQuery);
                const matchDni = dni.includes(currentCashierSearchQuery);
                const matchEmail = email.includes(currentCashierSearchQuery);
                const matchShift = shift.includes(currentCashierSearchQuery);
                if (!matchName && !matchDni && !matchEmail && !matchShift) {
                    return false;
                }
            }

            return true;
        });

        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / cashiersPerPage) || 1;
        if (currentCashierPage > totalPages) currentCashierPage = totalPages;
        if (currentCashierPage < 1) currentCashierPage = 1;

        const startIndex = (currentCashierPage - 1) * cashiersPerPage;
        const endIndex = startIndex + cashiersPerPage;

        cards.forEach(card => card.classList.add('hidden'));

        if (totalItems === 0) {
            if (searchRow) searchRow.classList.remove('hidden');
        } else {
            if (searchRow) searchRow.classList.add('hidden');
            filtered.slice(startIndex, endIndex).forEach(card => {
                card.classList.remove('hidden');
            });
        }

        // Update Pagination controls
        const paginationInfo = document.getElementById('cashier_pagination_info');
        const pageNumberDisplay = document.getElementById('cashier_page_number_display');
        const prevBtn = document.getElementById('cashier_prev_page_btn');
        const nextBtn = document.getElementById('cashier_next_page_btn');

        if (paginationInfo) {
            paginationInfo.textContent = totalItems > 0 
                ? `Mostrando ${startIndex + 1} - ${Math.min(endIndex, totalItems)} de ${totalItems} cajeros`
                : 'No hay resultados que mostrar';
        }

        if (pageNumberDisplay) {
            pageNumberDisplay.textContent = `Página ${currentCashierPage} de ${totalPages}`;
        }

        if (prevBtn) prevBtn.disabled = (currentCashierPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentCashierPage >= totalPages);
    }

    function changeCashierPage(direction) {
        currentCashierPage += direction;
        renderCashierPage();
    }

    // Modal Helpers (Cashier)
    function openCreateCashierModal() {
        const form = document.getElementById('create-cashier-form');
        if (form) form.reset();
        const todayStr = new Date().toISOString().split('T')[0];
        const hireInput = document.getElementById('create_hire_date');
        if (hireInput) hireInput.value = todayStr;
        toggleModal('modal-create-cashier');
    }

    function openEditCashierModal(cashier) {
        if (!cashier) return;
        const form = document.getElementById('edit-cashier-form');
        if (form) form.action = `/cajeros/${cashier.id}`;
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal('edit_first_name', cashier.first_name || (cashier.user?.profile?.first_name || ''));
        setVal('edit_last_name', cashier.last_name || (cashier.user?.profile?.last_name || ''));
        setVal('edit_dni', cashier.user?.profile?.dni || '');
        setVal('edit_phone', cashier.phone || (cashier.user?.profile?.phone || ''));
        setVal('edit_email', cashier.email || (cashier.user?.email || ''));
        setVal('edit_password', '');
        setVal('edit_shift', cashier.shift || 'Mañana (06:00 - 14:00)');
        setVal('edit_hire_date', cashier.hire_date ? cashier.hire_date.split('T')[0] : '');
        setVal('edit_salary', cashier.salary ?? 0);
        setVal('edit_notes', cashier.notes || '');

        toggleModal('modal-edit-cashier');
    }

    function openToggleCashierModal(id, fullName, isActive) {
        const form = document.getElementById('toggle-cashier-form');
        if (form) form.action = `/cajeros/${id}/toggle`;
        const titleEl = document.getElementById('modal-cashier-status-title');
        const descEl = document.getElementById('modal-cashier-status-desc');
        const btnTextEl = document.getElementById('modal-cashier-status-btn-text');
        const submitBtn = document.getElementById('toggle-cashier-submit-btn');

        if (isActive) {
            if (titleEl) titleEl.textContent = 'Inhabilitar Cajero';
            if (descEl) descEl.innerHTML = `¿Estás seguro de que deseas inhabilitar al cajero (<strong class="text-slate-100">${escapeHtml(fullName)}</strong>)? Sus accesos a cobro y TPV quedarán suspendidos.`;
            if (btnTextEl) btnTextEl.textContent = 'Sí, Inhabilitar';
            if (submitBtn) submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            if (titleEl) titleEl.textContent = 'Reactivar Cajero';
            if (descEl) descEl.innerHTML = `¿Deseas reactivar al cajero (<strong class="text-slate-100">${escapeHtml(fullName)}</strong>) para restaurar su servicio en caja?`;
            if (btnTextEl) btnTextEl.textContent = 'Sí, Reactivar';
            if (submitBtn) submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('modal-toggle-cashier');
    }

    function openDeleteCashierModal(id, fullName) {
        const form = document.getElementById('delete-cashier-form');
        if (form) form.action = `/cajeros/${id}`;
        const descEl = document.getElementById('modal-delete-cashier-desc');
        if (descEl) descEl.innerHTML = `¿Estás seguro de que deseas eliminar permanentemente al cajero (<strong class="text-slate-100">${escapeHtml(fullName)}</strong>)? Esta acción eliminará su cuenta de acceso.`;
        toggleModal('modal-delete-cashier');
    }

    // AJAX Form Submissions
    async function submitCreateCashier(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-cashier-submit-btn');

        setBtnLoading(submitBtn, true, 'Registrando...');

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
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showToast(data.message || 'Error al registrar cajero.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al registrar el cajero.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitEditCashier(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-cashier-submit-btn');

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
                const c = data.cashier;
                const p = c.user?.profile;
                const fullName = `${c.first_name || ''} ${c.last_name || ''}`.trim();
                const dni = p?.dni || 'Sin DNI';
                const card = document.getElementById(`cashier_card_${c.id}`);

                if (card) {
                    card.setAttribute('data-name', fullName.toLowerCase());
                    card.setAttribute('data-dni', dni.toLowerCase());
                    card.setAttribute('data-email', (c.email || '').toLowerCase());
                    card.setAttribute('data-shift', (c.shift || '').toLowerCase());

                    const nameEl = document.getElementById(`cashier_name_${c.id}`);
                    const dniEl = document.getElementById(`cashier_dni_${c.id}`);
                    const emailEl = document.getElementById(`cashier_email_${c.id}`);
                    const shiftEl = document.getElementById(`cashier_shift_${c.id}`);
                    const phoneEl = document.getElementById(`cashier_phone_${c.id}`);
                    const tenureEl = document.getElementById(`cashier_tenure_${c.id}`);
                    const salaryEl = document.getElementById(`cashier_salary_${c.id}`);
                    const photoImg = document.getElementById(`cashier_photo_img_${c.id}`);

                    if (nameEl) nameEl.textContent = fullName;
                    if (dniEl) dniEl.textContent = `DNI: ${dni}`;
                    if (emailEl) emailEl.textContent = c.email;
                    if (shiftEl) shiftEl.textContent = c.shift || 'Mañana (06:00 - 14:00)';
                    if (phoneEl) phoneEl.textContent = c.phone || 'Sin registrar';
                    if (tenureEl) tenureEl.textContent = c.tenure || '0 meses';
                    if (salaryEl) salaryEl.textContent = `$ ${parseFloat(c.salary || 0).toFixed(2)}`;

                    if (c.photo_url && photoImg) {
                        photoImg.src = `/${c.photo_url}`;
                    } else if (p?.profile_photo && photoImg) {
                        photoImg.src = `/${p.profile_photo}`;
                    }
                }

                toggleModal('modal-edit-cashier');
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar cajero.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el cajero.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitToggleCashier(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('toggle-cashier-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const card = document.getElementById(`cashier_card_${data.cashier_id}`);
                const badge = document.getElementById(`cashier_status_badge_${data.cashier_id}`);
                const dot = document.getElementById(`cashier_dot_${data.cashier_id}`);
                const toggleBtn = document.getElementById(`cashier_toggle_btn_${data.cashier_id}`);

                if (card) {
                    card.setAttribute('data-active', data.is_active ? '1' : '0');
                    if (data.is_active) {
                        card.className = card.className.replace('opacity-60 bg-slate-950/40 border-slate-855', '');
                    } else {
                        card.className += ' opacity-60 bg-slate-950/40 border-slate-855';
                    }
                }

                if (badge) {
                    badge.className = data.is_active
                        ? 'px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                        : 'px-2.5 py-0.5 text-[9px] font-black uppercase rounded-lg border tracking-wider shrink-0 bg-rose-500/10 text-rose-400 border-rose-500/20';
                    badge.textContent = data.is_active ? 'Activo' : 'Inactivo';
                }

                if (dot) {
                    dot.className = `w-3.5 h-3.5 rounded-full absolute -bottom-0.5 -right-0.5 border-2 border-slate-900 ${data.is_active ? 'bg-emerald-500' : 'bg-rose-500'}`;
                }

                if (toggleBtn) {
                    toggleBtn.className = data.is_active
                        ? 'p-2 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm'
                        : 'p-2 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm';
                    toggleBtn.innerHTML = `<i data-lucide="${data.is_active ? 'power' : 'check-circle'}" class="w-4 h-4"></i>`;
                }

                if (window.lucide) window.lucide.createIcons();

                toggleModal('modal-toggle-cashier');
                renderCashierPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar estado del cajero.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitDeleteCashier(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-cashier-submit-btn');

        setBtnLoading(submitBtn, true, 'Eliminando...');

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
                const card = document.getElementById(`cashier_card_${data.cashier_id}`);
                if (card) card.remove();

                toggleModal('modal-delete-cashier');
                renderCashierPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al eliminar cajero.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al eliminar al cajero.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Toast and UI utilities
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        let iconName = 'check-circle-2';
        let borderColor = 'border-emerald-500/30';
        let iconColor = 'text-emerald-400';
        let glowColor = 'shadow-emerald-500/10';

        if (type === 'error') {
            iconName = 'alert-circle';
            borderColor = 'border-rose-500/30';
            iconColor = 'text-rose-400';
            glowColor = 'shadow-rose-500/10';
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

        setTimeout(() => toast.classList.remove('translate-x-10', 'opacity-0'), 10);
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

    // Expose all handlers to window for HTML onclick attributes & PJAX execution
    window.toggleModal = toggleModal;
    window.switchDetailsTab = switchDetailsTab;
    window.openCashierDetailsModal = openCashierDetailsModal;
    window.openCreateCashierModal = openCreateCashierModal;
    window.openEditCashierModal = openEditCashierModal;
    window.openToggleCashierModal = openToggleCashierModal;
    window.openDeleteCashierModal = openDeleteCashierModal;
    window.setCashierStatusFilter = setCashierStatusFilter;
    window.onCashierFilterChange = onCashierFilterChange;
    window.renderCashierPage = renderCashierPage;
    window.changeCashierPage = changeCashierPage;
    window.submitCreateCashier = submitCreateCashier;
    window.submitEditCashier = submitEditCashier;
    window.submitToggleCashier = submitToggleCashier;
    window.submitDeleteCashier = submitDeleteCashier;
    window.showToast = showToast;
    })();
</script>
@endsection
