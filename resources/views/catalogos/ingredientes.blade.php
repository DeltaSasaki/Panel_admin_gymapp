@extends('layouts.admin')

@section('title', 'Catálogo de Ingredientes y Nutrientes')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Catálogo de Ingredientes</h1>
            <p class="text-xs text-slate-400 mt-1">Base de datos de alimentos, macros y densidad calórica para la creación de planes nutricionales.</p>
        </div>
        <div>
            <button onclick="openCreateModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Añadir Alimento / Ingrediente
            </button>
        </div>
    </div>

    <!-- Ingredients Table Container -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <!-- Header Filters Bar -->
        <div class="p-6 border-b border-slate-850 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 flex-wrap">
                <h3 class="font-bold text-lg text-slate-100">Alimentos Registrados</h3>
                
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setStatusFilter('all')" id="status-filter-btn-all" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-status-all">{{ $ingredients->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('1')" id="status-filter-btn-1" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-status-active">{{ $ingredients->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('0')" id="status-filter-btn-0" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivos (<span id="count-status-inactive">{{ $ingredients->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>

                <!-- Unit Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setUnitFilter('all')" id="unit-filter-btn-all" class="unit-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todas Unidades
                    </button>
                    <button type="button" onclick="setUnitFilter('g')" id="unit-filter-btn-g" class="unit-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        100g
                    </button>
                    <button type="button" onclick="setUnitFilter('ml')" id="unit-filter-btn-ml" class="unit-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        100ml
                    </button>
                    <button type="button" onclick="setUnitFilter('unit')" id="unit-filter-btn-unit" class="unit-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Unidad
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="ingredient_search_input" oninput="onIngredientSearchInput()" placeholder="Buscar ingrediente..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6 text-left">Alimento</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-center">Unidad Medida</th>
                        <th class="p-4 text-right">Proteína (g)</th>
                        <th class="p-4 text-right">Carbohidratos (g)</th>
                        <th class="p-4 text-right">Grasa (g)</th>
                        <th class="p-4 text-right">Calorías (kcal)</th>
                        <th class="p-4 text-center pr-6">Acciones</th>
                    </tr>
                </thead>
                <tbody id="ingredients_table_body" class="divide-y divide-slate-850/50">
                    @forelse($ingredients as $i)
                        <tr id="ingredient_row_{{ $i->id }}"
                            data-ingredient-row
                            data-name="{{ strtolower($i->name) }}"
                            data-unit="{{ $i->unit }}"
                            data-active="{{ $i->is_active ? 1 : 0 }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors {{ $i->is_active ? '' : 'opacity-60 bg-slate-950/30' }}">
                            <td class="p-4 pl-6 font-bold text-slate-100" id="ing_name_{{ $i->id }}">
                                {{ $i->name }}
                            </td>
                            <td class="p-4 text-center" id="ing_status_{{ $i->id }}">
                                @if($i->is_active)
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4 text-center text-slate-400 font-semibold" id="ing_unit_{{ $i->id }}">
                                @if($i->unit === 'g') 
                                    <span class="px-2 py-0.5 bg-slate-950 text-slate-300 border border-slate-850 rounded-md font-mono text-[10px]">100g</span>
                                @elseif($i->unit === 'ml') 
                                    <span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 border border-sky-500/20 rounded-md font-mono text-[10px]">100ml</span>
                                @else 
                                    <span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-mono text-[10px]">Por Unidad</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-mono text-lime-400 font-semibold" id="ing_protein_{{ $i->id }}">{{ number_format($i->protein_g, 1) }}g</td>
                            <td class="p-4 text-right font-mono text-amber-400 font-semibold" id="ing_carbs_{{ $i->id }}">{{ number_format($i->carbs_g, 1) }}g</td>
                            <td class="p-4 text-right font-mono text-rose-400 font-semibold" id="ing_fat_{{ $i->id }}">{{ number_format($i->fat_g, 1) }}g</td>
                            <td class="p-4 text-right font-mono font-black text-slate-100" id="ing_calories_{{ $i->id }}">{{ number_format($i->calories_per_100g, 0) }} kcal</td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openEditModal({{ json_encode($i) }})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Ingrediente">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="openDeleteModal({{ $i->id }}, '{{ addslashes($i->name) }}', {{ $i->is_active ? 1 : 0 }}, {{ $i->recipes_count ?? 0 }})" 
                                            id="ing_toggle_btn_{{ $i->id }}"
                                            class="p-1.5 {{ $i->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                            title="{{ $i->is_active ? 'Inhabilitar Alimento' : 'Reactivar Alimento' }}">
                                        <i data-lucide="{{ $i->is_active ? 'power' : 'check-circle' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no_ingredients_row">
                            <td colspan="8" class="p-8 text-center text-slate-550">
                                No hay ingredientes cargados en el catálogo alimenticio.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_ingredients_search_row" class="hidden">
                        <td colspan="8" class="p-10 text-center text-slate-500">
                            <i data-lucide="apple" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron ingredientes que coincidan con la búsqueda o filtro.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div id="ingredient_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="ingredient_pagination_info">Mostrando ingredientes...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="prev_page_btn" onclick="changeIngredientPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="next_page_btn" onclick="changeIngredientPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR ALIMENTO ================= -->
<div id="ingredient-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Añadir Alimento al Catálogo</h3>
            <button type="button" onclick="toggleModal('ingredient-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-ingredient-form" action="{{ route('catalogos.store_ingredient') }}" method="POST" onsubmit="submitCreateIngredient(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Alimento *</label>
                <input type="text" name="name" required placeholder="Ej: Pechuga de Pollo Deshuesada" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Unidad de Referencia *</label>
                <select name="unit" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="g" selected>Gramos (por cada 100g)</option>
                    <option value="ml">Mililitros (por cada 100ml)</option>
                    <option value="unit">Por Unidad (1 huevo, 1 plátano)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Proteínas (g) *</label>
                    <input type="number" step="0.1" name="protein_g" required placeholder="23.0" min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Carbohidratos (g) *</label>
                    <input type="number" step="0.1" name="carbs_g" required placeholder="0.0" min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Grasas (g) *</label>
                    <input type="number" step="0.1" name="fat_g" required placeholder="2.5" min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Calorías Totales (kcal) *</label>
                    <input type="number" name="calories_per_100g" required placeholder="120" min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('ingredient-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="create-ingredient-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Añadir Alimento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR ALIMENTO ================= -->
<div id="edit-ingredient-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Alimento del Catálogo</h3>
            <button type="button" onclick="toggleModal('edit-ingredient-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-ingredient-form" action="" method="POST" onsubmit="submitEditIngredient(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Alimento *</label>
                <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Unidad de Referencia *</label>
                <select name="unit" id="edit-unit" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="g">Gramos (por cada 100g)</option>
                    <option value="ml">Mililitros (por cada 100ml)</option>
                    <option value="unit">Por Unidad (1 huevo, 1 plátano)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Proteínas (g) *</label>
                    <input type="number" step="0.1" name="protein_g" id="edit-protein_g" required min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Carbohidratos (g) *</label>
                    <input type="number" step="0.1" name="carbs_g" id="edit-carbs_g" required min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Grasas (g) *</label>
                    <input type="number" step="0.1" name="fat_g" id="edit-fat_g" required min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Calorías Totales (kcal) *</label>
                    <input type="number" name="calories_per_100g" id="edit-calories_per_100g" required min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-ingredient-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-ingredient-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE ALIMENTO ================= -->
<div id="delete-ingredient-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-status-title">Cambiar Estado del Alimento</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-ingredient-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-status-description">
            ¿Estás seguro de que deseas cambiar el estado de este alimento?
        </p>

        <form id="delete-ingredient-form" action="" method="POST" onsubmit="submitDeleteIngredient(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('delete-ingredient-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-ingredient-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Temporary Toast Notifications
    function showToast(message, type = 'success') {
        let container = document.getElementById('ingredient-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'ingredient-toast-container';
            container.className = 'fixed top-24 right-6 z-50 flex flex-col gap-2.5 pointer-events-none max-w-xs sm:max-w-sm w-full';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const isDanger = type === 'danger' || type === 'error';

        let iconName = 'check-circle';
        let borderColor = 'border-emerald-500/30';
        let iconColor = 'text-emerald-400';
        let glowColor = 'shadow-emerald-500/10';

        if (isDanger) {
            iconName = 'alert-circle';
            borderColor = 'border-rose-500/30';
            iconColor = 'text-rose-400';
            glowColor = 'shadow-rose-500/10';
        } else if (type === 'warning') {
            iconName = 'alert-triangle';
            borderColor = 'border-amber-500/30';
            iconColor = 'text-amber-400';
            glowColor = 'shadow-amber-500/10';
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

        setTimeout(() => {
            toast.classList.remove('translate-x-10', 'opacity-0');
        }, 10);

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

    // Centered Static Modal Handler
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const isOpening = modal.classList.contains('hidden');
        modal.classList.toggle('hidden');

        if (isOpening) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    }

    function openCreateModal() {
        document.getElementById('create-ingredient-form').reset();
        toggleModal('ingredient-modal');
    }

    function openEditModal(ing) {
        document.getElementById('edit-ingredient-form').action = `/ingredientes/${ing.id}`;
        document.getElementById('edit-name').value = ing.name;
        document.getElementById('edit-unit').value = ing.unit;
        document.getElementById('edit-protein_g').value = ing.protein_g;
        document.getElementById('edit-carbs_g').value = ing.carbs_g;
        document.getElementById('edit-fat_g').value = ing.fat_g;
        document.getElementById('edit-calories_per_100g').value = ing.calories_per_100g;

        toggleModal('edit-ingredient-modal');
    }

    function openDeleteModal(ingId, ingName, isActive, recipesCount = 0) {
        document.getElementById('delete-ingredient-form').action = `/ingredientes/${ingId}`;
        const titleEl = document.getElementById('modal-status-title');
        const descEl = document.getElementById('modal-status-description');
        const btnTextEl = document.getElementById('modal-status-btn-text');
        const submitBtn = document.getElementById('delete-ingredient-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Alimento';
            let warningAlert = '';
            if (recipesCount > 0) {
                warningAlert = `
                    <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-amber-300 text-xs font-semibold space-y-1 mt-2">
                        <div class="flex items-center gap-1.5 font-extrabold text-amber-400">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i> ⚠️ ¡ADVERTENCIA DE DEPENDENCIA!
                        </div>
                        <p class="leading-relaxed">Este ingrediente está incluido en <strong class="text-amber-200 underline">${recipesCount} receta(s) activa(s)</strong>. Al inhabilitarlo, permanecerá en recetas existentes pero las recetas mostrarán la alerta de ingrediente inactivo.</p>
                    </div>
                `;
            }
            descEl.innerHTML = `
                <div class="space-y-2">
                    <p>¿Estás seguro de que deseas marcar como <strong>inactivo</strong> el alimento (<strong class="text-slate-100">${escapeHtml(ingName)}</strong>)?</p>
                    ${warningAlert}
                </div>
            `;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Alimento';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> el alimento (<strong class="text-slate-100">${escapeHtml(ingName)}</strong>) para que esté disponible en el catálogo?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        if (window.lucide) window.lucide.createIcons();
        toggleModal('delete-ingredient-modal');
    }

    // AJAX Submission: Create Ingredient
    async function submitCreateIngredient(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-ingredient-submit-btn');

        setBtnLoading(submitBtn, true, 'Añadiendo...');

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
                const ing = data.ingredient;
                const tbody = document.getElementById('ingredients_table_body');
                
                const noIngRow = document.getElementById('no_ingredients_row');
                if (noIngRow) noIngRow.classList.add('hidden');

                const unitBadgeHtml = ing.unit === 'g' 
                    ? `<span class="px-2 py-0.5 bg-slate-950 text-slate-300 border border-slate-850 rounded-md font-mono text-[10px]">100g</span>`
                    : (ing.unit === 'ml' 
                        ? `<span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 border border-sky-500/20 rounded-md font-mono text-[10px]">100ml</span>`
                        : `<span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-mono text-[10px]">Por Unidad</span>`);

                const ingJsonStr = JSON.stringify(ing).replace(/'/g, "&#39;");
                const safeName = escapeHtml(ing.name);

                const tr = document.createElement('tr');
                tr.id = `ingredient_row_${ing.id}`;
                tr.setAttribute('data-ingredient-row', '');
                tr.setAttribute('data-name', (ing.name || '').toLowerCase());
                tr.setAttribute('data-unit', ing.unit);
                tr.setAttribute('data-active', '1');
                tr.className = 'hover:bg-slate-900/20 text-slate-200 transition-colors';
                tr.innerHTML = `
                    <td class="p-4 pl-6 font-bold text-slate-100" id="ing_name_${ing.id}">${safeName}</td>
                    <td class="p-4 text-center" id="ing_status_${ing.id}">
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                    </td>
                    <td class="p-4 text-center text-slate-400 font-semibold" id="ing_unit_${ing.id}">${unitBadgeHtml}</td>
                    <td class="p-4 text-right font-mono text-lime-400 font-semibold" id="ing_protein_${ing.id}">${parseFloat(ing.protein_g).toFixed(1)}g</td>
                    <td class="p-4 text-right font-mono text-amber-400 font-semibold" id="ing_carbs_${ing.id}">${parseFloat(ing.carbs_g).toFixed(1)}g</td>
                    <td class="p-4 text-right font-mono text-rose-400 font-semibold" id="ing_fat_${ing.id}">${parseFloat(ing.fat_g).toFixed(1)}g</td>
                    <td class="p-4 text-right font-mono font-black text-slate-100" id="ing_calories_${ing.id}">${Math.round(ing.calories_per_100g)} kcal</td>
                    <td class="p-4 text-center pr-6">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openEditModal(${ingJsonStr})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Ingrediente">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </button>
                            <button onclick="openDeleteModal(${ing.id}, '${safeName.replace(/'/g, "\\'")}', 1)" id="ing_toggle_btn_${ing.id}" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Alimento">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                `;

                tbody.prepend(tr);
                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('ingredient-modal');
                updateTabCounters();
                renderIngredientPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al añadir ingrediente.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar añadir el ingrediente.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Ingredient
    async function submitEditIngredient(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-ingredient-submit-btn');

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
                const ing = data.ingredient;
                const row = document.getElementById(`ingredient_row_${ing.id}`);

                if (row) {
                    row.setAttribute('data-name', (ing.name || '').toLowerCase());
                    row.setAttribute('data-unit', ing.unit);

                    const nameCell = document.getElementById(`ing_name_${ing.id}`);
                    const unitCell = document.getElementById(`ing_unit_${ing.id}`);
                    const protCell = document.getElementById(`ing_protein_${ing.id}`);
                    const carbsCell = document.getElementById(`ing_carbs_${ing.id}`);
                    const fatCell = document.getElementById(`ing_fat_${ing.id}`);
                    const calCell = document.getElementById(`ing_calories_${ing.id}`);

                    if (nameCell) nameCell.textContent = ing.name;
                    if (protCell) protCell.textContent = parseFloat(ing.protein_g).toFixed(1) + 'g';
                    if (carbsCell) carbsCell.textContent = parseFloat(ing.carbs_g).toFixed(1) + 'g';
                    if (fatCell) fatCell.textContent = parseFloat(ing.fat_g).toFixed(1) + 'g';
                    if (calCell) calCell.textContent = Math.round(ing.calories_per_100g) + ' kcal';

                    if (unitCell) {
                        unitCell.innerHTML = ing.unit === 'g' 
                            ? `<span class="px-2 py-0.5 bg-slate-950 text-slate-300 border border-slate-850 rounded-md font-mono text-[10px]">100g</span>`
                            : (ing.unit === 'ml' 
                                ? `<span class="px-2 py-0.5 bg-sky-500/10 text-sky-400 border border-sky-500/20 rounded-md font-mono text-[10px]">100ml</span>`
                                : `<span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-mono text-[10px]">Por Unidad</span>`);
                    }
                }

                toggleModal('edit-ingredient-modal');
                updateTabCounters();
                renderIngredientPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar ingrediente.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar actualizar el ingrediente.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status (Disable/Enable)
    async function submitDeleteIngredient(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-ingredient-submit-btn');

        setBtnLoading(submitBtn, true, 'Procesando...');

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
                const ingId = data.ingredient_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const row = document.getElementById(`ingredient_row_${ingId}`);

                if (row) {
                    row.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        row.classList.remove('opacity-60', 'bg-slate-950/30');
                    } else {
                        row.classList.add('opacity-60', 'bg-slate-950/30');
                    }

                    // Update Status Badge
                    const statusCell = document.getElementById(`ing_status_${ingId}`);
                    if (statusCell) {
                        statusCell.innerHTML = newActiveStatus 
                            ? `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>`
                            : `<span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`;
                    }

                    // Update Toggle Button
                    const toggleBtn = document.getElementById(`ing_toggle_btn_${ingId}`);
                    if (toggleBtn) {
                        const nameText = document.getElementById(`ing_name_${ingId}`)?.textContent || '';
                        toggleBtn.onclick = () => openDeleteModal(ingId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Alimento' : 'Reactivar Alimento';
                        toggleBtn.className = `p-1.5 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-3.5 h-3.5"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('delete-ingredient-modal');
                updateTabCounters();
                renderIngredientPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado del ingrediente.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Pagination & Filter Logic
    var currentIngredientPage = 1;
    var currentStatusFilter = 'all';
    var currentUnitFilter = 'all';
    var itemsPerPage = 10;

    function setStatusFilter(status) {
        currentStatusFilter = status;

        const tabs = document.querySelectorAll('.status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('status-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentIngredientPage = 1;
        renderIngredientPage();
    }

    function setUnitFilter(unit) {
        currentUnitFilter = unit;

        const tabs = document.querySelectorAll('.unit-tab-btn');
        tabs.forEach(tab => {
            tab.className = "unit-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('unit-filter-btn-' + unit);
        if (activeTab) {
            activeTab.className = "unit-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentIngredientPage = 1;
        renderIngredientPage();
    }

    function onIngredientSearchInput() {
        currentIngredientPage = 1;
        renderIngredientPage();
    }

    function updateTabCounters() {
        const rows = document.querySelectorAll('[data-ingredient-row]');
        let countActive = 0;
        let countInactive = 0;

        rows.forEach(r => {
            const isActive = r.getAttribute('data-active') === '1';
            if (isActive) countActive++;
            else countInactive++;
        });

        const cAll = document.getElementById('count-status-all');
        const cActive = document.getElementById('count-status-active');
        const cInactive = document.getElementById('count-status-inactive');

        if (cAll) cAll.textContent = rows.length;
        if (cActive) cActive.textContent = countActive;
        if (cInactive) cInactive.textContent = countInactive;
    }

    function renderIngredientPage() {
        const rawQuery = (document.getElementById('ingredient_search_input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-ingredient-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            const unit = r.getAttribute('data-unit') || '';
            const isActive = r.getAttribute('data-active') || '1';

            const matchesStatus = (currentStatusFilter === 'all') || (isActive === currentStatusFilter);
            const matchesUnit = (currentUnitFilter === 'all') || (unit === currentUnitFilter);
            const matchesQuery = !rawQuery || name.includes(rawQuery);

            return matchesStatus && matchesUnit && matchesQuery;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;

        if (currentIngredientPage > totalPages) currentIngredientPage = totalPages;
        if (currentIngredientPage < 1) currentIngredientPage = 1;

        const startIndex = (currentIngredientPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_ingredients_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        // Pagination controls update
        const infoSpan = document.getElementById('ingredient_pagination_info');
        const pageSpan = document.getElementById('page_number_display');
        const prevBtn = document.getElementById('prev_page_btn');
        const nextBtn = document.getElementById('next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay ingredientes para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} ingredientes`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentIngredientPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentIngredientPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentIngredientPage >= totalPages);
    }

    function changeIngredientPage(delta) {
        currentIngredientPage += delta;
        renderIngredientPage();
    }

    // Auto-trigger session flash messages on page load
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif

        updateTabCounters();
        renderIngredientPage();
    });
</script>
@endsection
