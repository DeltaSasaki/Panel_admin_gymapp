@extends('layouts.admin')

@section('title', 'Planes de Suscripción SaaS')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight flex items-center gap-2.5">
                <i data-lucide="credit-card" class="w-7 h-7 text-lime-400"></i>
                Planes de Suscripción SaaS
            </h1>
            <p class="text-slate-400 text-xs mt-1">Administra los planes de suscripción, tarifas mensuales y límites de cupo para tus sucursales.</p>
        </div>
        <button onclick="toggleNewPlanModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
            Nuevo Plan SaaS
        </button>
    </div>

    <!-- Error/Success Alerts -->
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-4 rounded-2xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-4 rounded-2xl space-y-1">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                <span>Atención:</span>
            </div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $totalCount = $plans->count();
        $activeCount = $plans->where('is_active', 1)->count();
        $inactiveCount = $plans->where('is_active', 0)->count();
    @endphp

    <!-- Filter Bar -->
    <div class="flex items-center gap-2 bg-slate-900/60 p-1.5 rounded-2xl border border-slate-800/80 w-fit">
        <button onclick="filterPlans('all')" id="btn-filter-all" class="filter-plan-btn px-4 py-2 rounded-xl text-xs font-extrabold transition-all bg-lime-500 text-slate-950 shadow-md cursor-pointer flex items-center gap-2">
            <span>Todos</span>
            <span class="bg-slate-950/20 px-2 py-0.5 rounded-md text-[10px]">{{ $totalCount }}</span>
        </button>
        <button onclick="filterPlans('active')" id="btn-filter-active" class="filter-plan-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all hover:bg-slate-800/50 cursor-pointer flex items-center gap-2">
            <span>Activos</span>
            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded-md text-[10px]">{{ $activeCount }}</span>
        </button>
        <button onclick="filterPlans('inactive')" id="btn-filter-inactive" class="filter-plan-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all hover:bg-slate-800/50 cursor-pointer flex items-center gap-2">
            <span>Inactivos / Desactivados</span>
            <span class="bg-slate-800 text-slate-400 border border-slate-700 px-2 py-0.5 rounded-md text-[10px]">{{ $inactiveCount }}</span>
        </button>
    </div>

    <!-- Cards Grid & Table View -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div data-status="{{ $plan->is_active ? 'active' : 'inactive' }}" class="plan-card bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 relative overflow-hidden flex flex-col justify-between group hover:border-slate-700/80 transition-all duration-300 shadow-xl">
                <!-- Background ambient glow -->
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-lime-500/5 rounded-full blur-2xl group-hover:bg-lime-500/10 transition-all"></div>

                <div>
                    <!-- Header Badges -->
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $plan->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                            {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                        
                        <span class="text-[10px] font-bold text-slate-400 flex items-center gap-1 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-850">
                            <i data-lucide="building-2" class="w-3 h-3 text-lime-400"></i>
                            {{ $plan->gyms_count }} {{ $plan->gyms_count == 1 ? 'sucursal' : 'sucursales' }}
                        </span>
                    </div>

                    <!-- Title & Price -->
                    <h3 class="text-xl font-black text-slate-100 tracking-tight">{{ $plan->name }}</h3>
                    <p class="text-slate-400 text-xs mt-1 min-h-[36px] line-clamp-2">{{ $plan->description ?? 'Sin descripción detallada.' }}</p>

                    <div class="my-6 pt-4 border-t border-slate-800/60">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-lime-400 tracking-tight">${{ number_format($plan->monthly_price, 2) }}</span>
                            <span class="text-xs font-bold text-slate-500 uppercase">{{ $plan->currency }} / mes</span>
                        </div>
                    </div>

                    <!-- Limits & Quotas -->
                    <div class="space-y-2.5 text-xs text-slate-300 bg-slate-950/60 p-3.5 rounded-2xl border border-slate-850 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px] flex items-center gap-1.5">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i> Límite de Socios:
                            </span>
                            <span class="font-extrabold text-slate-200">
                                {{ $plan->max_users ? number_format($plan->max_users) . ' miembros' : 'Ilimitado' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[11px] flex items-center gap-1.5">
                                <i data-lucide="users-2" class="w-3.5 h-3.5 text-slate-500"></i> Límite Entrenadores:
                            </span>
                            <span class="font-extrabold text-slate-200">
                                {{ $plan->max_trainers ? number_format($plan->max_trainers) . ' coaches' : 'Ilimitado' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="pt-4 border-t border-slate-800/60 flex items-center gap-2">
                    <!-- Edit Button -->
                    <button onclick="openEditPlanModal({{ json_encode($plan) }})" class="flex-1 py-2 bg-slate-800/80 hover:bg-slate-750 text-slate-200 font-bold text-xs rounded-xl border border-slate-700/60 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-lime-400"></i> Editar
                    </button>

                    <!-- Toggle Status Button -->
                    <form action="{{ route('superadmin.plans.toggle', $plan->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="p-2 bg-slate-800/80 hover:bg-slate-750 text-slate-300 rounded-xl border border-slate-700/60 transition-all cursor-pointer" title="{{ $plan->is_active ? 'Desactivar Plan' : 'Activar Plan' }}">
                            <i data-lucide="{{ $plan->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4 {{ $plan->is_active ? 'text-amber-400' : 'text-emerald-400' }}"></i>
                        </button>
                    </form>

                    <!-- Safe Delete Button -->
                    <form action="{{ route('superadmin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('¿Confirmas que deseas eliminar el plan \'{{ $plan->name }}\'?')" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-xl border border-rose-500/20 transition-all cursor-pointer {{ $plan->gyms_count > 0 ? 'opacity-60' : '' }}" title="{{ $plan->gyms_count > 0 ? 'En uso por ' . $plan->gyms_count . ' sucursal(es)' : 'Eliminar Plan' }}">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-slate-900/40 border border-slate-800/60 rounded-3xl p-12 text-center text-slate-500">
                <i data-lucide="credit-card" class="w-12 h-12 mx-auto mb-3 text-slate-700"></i>
                <p class="font-bold text-sm">No hay planes SaaS registrados aún.</p>
            </div>
        @endforelse
    </div>
</div>

@push('modals')
    <!-- ================= MODAL: NUEVO PLAN SAAS ================= -->
    <div id="new-plan-modal" class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl my-auto animate-scale-up space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="font-extrabold text-slate-100 text-lg flex items-center gap-2">
                    <i data-lucide="plus-circle" class="text-lime-400 w-5 h-5"></i>
                    Crear Nuevo Plan SaaS
                </h3>
                <button onclick="toggleNewPlanModal()" class="text-slate-400 hover:text-slate-100 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.plans.store') }}" method="POST" class="space-y-4 text-xs font-semibold">
                @csrf
                <div>
                    <label for="name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plan</label>
                    <input type="text" name="name" id="name" required placeholder="Ej: Plan Pro Enterprise" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                    <textarea name="description" id="description" rows="2" placeholder="Ej: Incluye módulo completo de nutrición y rutinas personalizadas..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="monthly_price" class="block text-slate-400 uppercase tracking-wider mb-1.5">Precio Mensual ($)</label>
                        <input type="number" step="0.01" min="0" name="monthly_price" id="monthly_price" required placeholder="59.99" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="currency" class="block text-slate-400 uppercase tracking-wider mb-1.5">Moneda</label>
                        <select name="currency" id="currency" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="USD" selected>USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="MXN">MXN ($)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="max_users" class="block text-slate-400 uppercase tracking-wider mb-1.5">Máx. Socios (Dejar vacío = ∞)</label>
                        <input type="number" min="1" name="max_users" id="max_users" placeholder="Ej: 200" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="max_trainers" class="block text-slate-400 uppercase tracking-wider mb-1.5">Máx. Staff (Dejar vacío = ∞)</label>
                        <input type="number" min="1" name="max_trainers" id="max_trainers" placeholder="Ej: 10" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="pt-4 flex gap-3 border-t border-slate-800">
                    <button type="button" onclick="toggleNewPlanModal()" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold rounded-xl border border-slate-700/50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold rounded-xl shadow-lg transition-all cursor-pointer">
                        Crear Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: EDITAR PLAN SAAS ================= -->
    <div id="edit-plan-modal" class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl my-auto animate-scale-up space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="font-extrabold text-slate-100 text-lg flex items-center gap-2">
                    <i data-lucide="edit-3" class="text-lime-400 w-5 h-5"></i>
                    Editar Plan SaaS
                </h3>
                <button onclick="toggleEditPlanModal()" class="text-slate-400 hover:text-slate-100 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="edit-plan-form" action="" method="POST" class="space-y-4 text-xs font-semibold">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit_plan_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plan</label>
                    <input type="text" name="name" id="edit_plan_name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="edit_plan_description" class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                    <textarea name="description" id="edit_plan_description" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_plan_price" class="block text-slate-400 uppercase tracking-wider mb-1.5">Precio Mensual ($)</label>
                        <input type="number" step="0.01" min="0" name="monthly_price" id="edit_plan_price" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_plan_currency" class="block text-slate-400 uppercase tracking-wider mb-1.5">Moneda</label>
                        <select name="currency" id="edit_plan_currency" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="MXN">MXN ($)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_plan_max_users" class="block text-slate-400 uppercase tracking-wider mb-1.5">Máx. Socios (Vacío = ∞)</label>
                        <input type="number" min="1" name="max_users" id="edit_plan_max_users" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_plan_max_trainers" class="block text-slate-400 uppercase tracking-wider mb-1.5">Máx. Staff (Vacío = ∞)</label>
                        <input type="number" min="1" name="max_trainers" id="edit_plan_max_trainers" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="pt-4 flex gap-3 border-t border-slate-800">
                    <button type="button" onclick="toggleEditPlanModal()" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold rounded-xl border border-slate-700/50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold rounded-xl shadow-lg transition-all cursor-pointer">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endpush

<script>
    function filterPlans(status) {
        const cards = document.querySelectorAll('.plan-card');
        const buttons = document.querySelectorAll('.filter-plan-btn');

        buttons.forEach(btn => {
            btn.className = 'filter-plan-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all hover:bg-slate-800/50 cursor-pointer flex items-center gap-2';
        });

        const activeBtn = document.getElementById(`btn-filter-${status}`);
        if (activeBtn) {
            activeBtn.className = 'filter-plan-btn px-4 py-2 rounded-xl text-xs font-extrabold transition-all bg-lime-500 text-slate-950 shadow-md cursor-pointer flex items-center gap-2';
        }

        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (status === 'all' || cardStatus === status) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function toggleNewPlanModal() {
        const modal = document.getElementById('new-plan-modal');
        if (modal) modal.classList.toggle('hidden');
    }

    function toggleEditPlanModal() {
        const modal = document.getElementById('edit-plan-modal');
        if (modal) modal.classList.toggle('hidden');
    }

    function openEditPlanModal(plan) {
        document.getElementById('edit_plan_name').value = plan.name || '';
        document.getElementById('edit_plan_description').value = plan.description || '';
        document.getElementById('edit_plan_price').value = plan.monthly_price || 0;
        document.getElementById('edit_plan_currency').value = plan.currency || 'USD';
        document.getElementById('edit_plan_max_users').value = plan.max_users || '';
        document.getElementById('edit_plan_max_trainers').value = plan.max_trainers || '';

        const form = document.getElementById('edit-plan-form');
        form.action = `/superadmin/planes/${plan.id}`;

        toggleEditPlanModal();
    }
</script>
@endsection
