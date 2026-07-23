@extends('layouts.admin')

@section('title', 'Inventario de Equipamiento')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Equipamiento del Gimnasio</h1>
            <p class="text-xs text-slate-400 mt-1">Control de máquinas, racks y pesas asignadas a las salas de entrenamiento.</p>
        </div>
        <div>
            <button onclick="openCreateEquipmentModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="dumbbell" class="w-4 h-4"></i> Registrar Máquina / Equipo
            </button>
        </div>
    </div>

    <!-- Equipment Status Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-400 text-[10px] font-bold uppercase mb-1">Total de Equipos</span>
            <h3 class="text-xl font-black text-slate-100"><span id="stat-total-equipment">{{ $equipment->count() }}</span> Equipos</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Equipos Activos</span>
            <h3 class="text-xl font-black text-lime-400"><span id="stat-active-equipment">{{ $equipment->where('is_active', 1)->count() }}</span> Activos</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-emerald-400 text-[10px] font-bold uppercase mb-1">Requieren Sala Física</span>
            <h3 class="text-xl font-black text-emerald-400"><span id="stat-gym-equipment">{{ $equipment->where('requires_gym', 1)->count() }}</span> Instalados en Sala</h3>
        </div>
    </div>

    <!-- Equipment Table Container -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <!-- Header Filters Bar -->
        <div class="p-6 border-b border-slate-850 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 flex-wrap">
                <h3 class="font-bold text-lg text-slate-100">Inventario Físico de Sala</h3>
                
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setStatusFilter('all')" id="status-filter-btn-all" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-status-all">{{ $equipment->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('1')" id="status-filter-btn-1" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-status-active">{{ $equipment->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('0')" id="status-filter-btn-0" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivos (<span id="count-status-inactive">{{ $equipment->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>

                <!-- Requirement Dropdown Filter -->
                <div class="flex items-center gap-2">
                    <select id="filter-requires-gym" onchange="onEquipmentFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">Cualquier Requerimiento</option>
                        <option value="1">Requiere Local Físico (Sala)</option>
                        <option value="0">Sin Requerimiento (Peso Corporal / Libre)</option>
                    </select>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-input" oninput="onEquipmentFilterChange()" placeholder="Buscar equipo o descripción..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6 text-left">Nombre de Equipo</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-left">Descripción</th>
                        <th class="p-4 text-center">Requiere Local Físico</th>
                        <th class="p-4 text-center pr-6">Acciones</th>
                    </tr>
                </thead>
                <tbody id="equipment_table_body" class="divide-y divide-slate-850/50">
                    @forelse($equipment as $item)
                        <tr id="equipment_row_{{ $item->id }}"
                            data-equipment-row
                            data-name="{{ strtolower($item->name) }}"
                            data-desc="{{ strtolower($item->description ?? '') }}"
                            data-requires-gym="{{ $item->requires_gym ? 1 : 0 }}"
                            data-active="{{ $item->is_active ? 1 : 0 }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors {{ $item->is_active ? '' : 'opacity-60 bg-slate-950/30' }}">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img id="eq_img_{{ $item->id }}" src="{{ $item->image_url ? asset($item->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <span id="eq_name_{{ $item->id }}" class="block font-bold text-slate-100">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-center" id="eq_status_{{ $item->id }}">
                                @if($item->is_active)
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-400 max-w-xs truncate" id="eq_desc_{{ $item->id }}">
                                {{ $item->description ?? 'Sin descripción.' }}
                            </td>
                            <td class="p-4 text-center font-bold uppercase text-[10px]" id="eq_requires_gym_{{ $item->id }}">
                                <span class="{{ $item->requires_gym ? 'text-lime-400' : 'text-slate-500' }}">
                                    {{ $item->requires_gym ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openEditEquipmentModal({{ json_encode($item) }})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Equipo">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="openDeleteEquipmentModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->is_active ? 1 : 0 }})" 
                                            id="eq_toggle_btn_{{ $item->id }}"
                                            class="p-1.5 {{ $item->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                            title="{{ $item->is_active ? 'Inhabilitar Equipo' : 'Reactivar Equipo' }}">
                                        <i data-lucide="{{ $item->is_active ? 'power' : 'check-circle' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no_equipment_empty_row">
                            <td colspan="5" class="p-8 text-center text-slate-550">
                                No se ha registrado ninguna máquina de entrenamiento aún.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_equipment_search_row" class="hidden">
                        <td colspan="5" class="p-10 text-center text-slate-500">
                            <i data-lucide="dumbbell" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron equipos que coincidan con la búsqueda o filtro.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div id="equipment_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="equipment_pagination_info">Mostrando equipos...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="prev_page_btn" onclick="changeEquipmentPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="next_page_btn" onclick="changeEquipmentPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR MÁQUINA ================= -->
<div id="equipment-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Equipo Deportivo</h3>
            <button type="button" onclick="toggleModal('equipment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-equipment-form" action="{{ route('catalogos.store_equipment') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateEquipment(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Equipo *</label>
                <input type="text" name="name" required placeholder="Ej: Prensa de Pierna 45° Matrix" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" placeholder="Ej: Prensa inclinada de discos para musculación de cuádriceps" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto del Equipo (Opcional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-400 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="requires_gym" id="requires_gym" value="1" checked class="rounded border-slate-850 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                <label for="requires_gym" class="text-slate-350 cursor-pointer">¿Requiere estar físicamente en la sucursal?</label>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('equipment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="create-equipment-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Equipo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR MÁQUINA ================= -->
<div id="edit-equipment-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Equipo Deportivo</h3>
            <button type="button" onclick="toggleModal('edit-equipment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-equipment-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditEquipment(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Equipo *</label>
                <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="description" id="edit-description" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Actualizar Foto del Equipo (Opcional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-450 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>
            <div class="flex items-center gap-2 pt-1 hidden" id="current-image-container">
                <input type="checkbox" name="remove_image" id="edit-remove-image" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                <label for="edit-remove-image" class="text-xs text-rose-400 font-medium cursor-pointer">Eliminar foto actual</label>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="requires_gym" id="edit-requires-gym" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                <label for="edit-requires-gym" class="text-slate-350 cursor-pointer">¿Requiere estar físicamente en la sucursal?</label>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-equipment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-equipment-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE EQUIPO ================= -->
<div id="delete-equipment-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-equipment-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-equipment-status-title">Cambiar Estado del Equipo</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-equipment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-equipment-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de este equipo deportivo?
        </p>

        <form id="delete-equipment-form" action="" method="POST" onsubmit="submitDeleteEquipment(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('delete-equipment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-equipment-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-equipment-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Temporary Toast Notifications
    function showToast(message, type = 'success') {
        let container = document.getElementById('equipment-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'equipment-toast-container';
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

    function openCreateEquipmentModal() {
        document.getElementById('create-equipment-form').reset();
        toggleModal('equipment-modal');
    }

    function openEditEquipmentModal(item) {
        document.getElementById('edit-equipment-form').action = `/equipamiento/${item.id}`;
        document.getElementById('edit-name').value = item.name;
        document.getElementById('edit-description').value = item.description || '';
        document.getElementById('edit-requires-gym').checked = item.requires_gym == 1;

        const currentImgContainer = document.getElementById('current-image-container');
        const removeImgCheck = document.getElementById('edit-remove-image');
        if (item.image_url) {
            currentImgContainer.classList.remove('hidden');
        } else {
            currentImgContainer.classList.add('hidden');
        }
        removeImgCheck.checked = false;

        toggleModal('edit-equipment-modal');
    }

    function openDeleteEquipmentModal(eqId, eqName, isActive) {
        document.getElementById('delete-equipment-form').action = `/equipamiento/${eqId}`;
        const titleEl = document.getElementById('modal-equipment-status-title');
        const descEl = document.getElementById('modal-equipment-status-desc');
        const btnTextEl = document.getElementById('modal-equipment-status-btn-text');
        const submitBtn = document.getElementById('delete-equipment-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Equipo';
            descEl.innerHTML = `¿Estás seguro de que deseas marcar como <strong>inactivo</strong> el equipo (<strong class="text-slate-100">${escapeHtml(eqName)}</strong>)? Ya no figurará disponible para asignación.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Equipo';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> el equipo (<strong class="text-slate-100">${escapeHtml(eqName)}</strong>) en el inventario?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('delete-equipment-modal');
    }

    // AJAX Submission: Create Equipment
    async function submitCreateEquipment(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-equipment-submit-btn');

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
                const eq = data.equipment;
                const tbody = document.getElementById('equipment_table_body');
                
                const emptyRow = document.getElementById('no_equipment_empty_row');
                if (emptyRow) emptyRow.classList.add('hidden');

                const eqJsonStr = JSON.stringify(eq).replace(/'/g, "&#39;");
                const safeName = escapeHtml(eq.name);
                const safeDesc = escapeHtml(eq.description || 'Sin descripción.');
                const imgUrl = eq.image_url ? `/${eq.image_url}` : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop';

                const tr = document.createElement('tr');
                tr.id = `equipment_row_${eq.id}`;
                tr.setAttribute('data-equipment-row', '');
                tr.setAttribute('data-name', (eq.name || '').toLowerCase());
                tr.setAttribute('data-desc', (eq.description || '').toLowerCase());
                tr.setAttribute('data-requires-gym', eq.requires_gym ? 1 : 0);
                tr.setAttribute('data-active', '1');
                tr.className = 'hover:bg-slate-900/20 text-slate-200 transition-colors';

                tr.innerHTML = `
                    <td class="p-4 pl-6 flex items-center gap-3">
                        <img id="eq_img_${eq.id}" src="${imgUrl}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                        <div>
                            <span id="eq_name_${eq.id}" class="block font-bold text-slate-100">${safeName}</span>
                        </div>
                    </td>
                    <td class="p-4 text-center" id="eq_status_${eq.id}">
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                    </td>
                    <td class="p-4 text-slate-400 max-w-xs truncate" id="eq_desc_${eq.id}">${safeDesc}</td>
                    <td class="p-4 text-center font-bold uppercase text-[10px]" id="eq_requires_gym_${eq.id}">
                        <span class="${eq.requires_gym ? 'text-lime-400' : 'text-slate-500'}">
                            ${eq.requires_gym ? 'Sí' : 'No'}
                        </span>
                    </td>
                    <td class="p-4 text-center pr-6">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openEditEquipmentModal(${eqJsonStr})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Equipo">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </button>
                            <button onclick="openDeleteEquipmentModal(${eq.id}, '${safeName.replace(/'/g, "\\'")}', 1)" id="eq_toggle_btn_${eq.id}" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Equipo">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                `;

                tbody.prepend(tr);
                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('equipment-modal');
                updateCounters();
                renderEquipmentPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al registrar equipo.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar registrar el equipo.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Equipment
    async function submitEditEquipment(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-equipment-submit-btn');

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
                const eq = data.equipment;
                const row = document.getElementById(`equipment_row_${eq.id}`);

                if (row) {
                    row.setAttribute('data-name', (eq.name || '').toLowerCase());
                    row.setAttribute('data-desc', (eq.description || '').toLowerCase());
                    row.setAttribute('data-requires-gym', eq.requires_gym ? 1 : 0);

                    const nameEl = document.getElementById(`eq_name_${eq.id}`);
                    const descEl = document.getElementById(`eq_desc_${eq.id}`);
                    const reqGymEl = document.getElementById(`eq_requires_gym_${eq.id}`);
                    const imgEl = document.getElementById(`eq_img_${eq.id}`);

                    if (nameEl) nameEl.textContent = eq.name;
                    if (descEl) descEl.textContent = eq.description || 'Sin descripción.';
                    if (reqGymEl) {
                        reqGymEl.innerHTML = `<span class="${eq.requires_gym ? 'text-lime-400' : 'text-slate-500'}">${eq.requires_gym ? 'Sí' : 'No'}</span>`;
                    }
                    if (imgEl && eq.image_url) {
                        imgEl.src = `/${eq.image_url}`;
                    }
                }

                toggleModal('edit-equipment-modal');
                updateCounters();
                renderEquipmentPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar equipo.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el equipo.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status (Disable/Enable)
    async function submitDeleteEquipment(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-equipment-submit-btn');

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
                const eqId = data.equipment_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const row = document.getElementById(`equipment_row_${eqId}`);

                if (row) {
                    row.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        row.classList.remove('opacity-60', 'bg-slate-950/30');
                    } else {
                        row.classList.add('opacity-60', 'bg-slate-950/30');
                    }

                    // Update Status Badge
                    const statusCell = document.getElementById(`eq_status_${eqId}`);
                    if (statusCell) {
                        statusCell.innerHTML = newActiveStatus 
                            ? `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>`
                            : `<span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`;
                    }

                    // Update Toggle Button
                    const toggleBtn = document.getElementById(`eq_toggle_btn_${eqId}`);
                    const nameText = document.getElementById(`eq_name_${eqId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openDeleteEquipmentModal(eqId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Equipo' : 'Reactivar Equipo';
                        toggleBtn.className = `p-1.5 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-3.5 h-3.5"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('delete-equipment-modal');
                updateCounters();
                renderEquipmentPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado del equipo.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado del equipo.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Pagination & Filter Logic (10 per page)
    let currentEquipmentPage = 1;
    let currentEquipmentStatusFilter = 'all';
    const itemsPerPage = 10;

    function setStatusFilter(status) {
        currentEquipmentStatusFilter = status;

        const tabs = document.querySelectorAll('.status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('status-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentEquipmentPage = 1;
        renderEquipmentPage();
    }

    function onEquipmentFilterChange() {
        currentEquipmentPage = 1;
        renderEquipmentPage();
    }

    function updateCounters() {
        const rows = document.querySelectorAll('[data-equipment-row]');
        let countActive = 0;
        let countInactive = 0;
        let countGym = 0;

        rows.forEach(r => {
            const isActive = r.getAttribute('data-active') === '1';
            const reqGym = r.getAttribute('data-requires-gym') === '1';
            if (isActive) countActive++;
            else countInactive++;
            if (reqGym) countGym++;
        });

        const cAll = document.getElementById('count-status-all');
        const cActive = document.getElementById('count-status-active');
        const cInactive = document.getElementById('count-status-inactive');
        
        const statTotal = document.getElementById('stat-total-equipment');
        const statActive = document.getElementById('stat-active-equipment');
        const statGym = document.getElementById('stat-gym-equipment');

        if (cAll) cAll.textContent = rows.length;
        if (cActive) cActive.textContent = countActive;
        if (cInactive) cInactive.textContent = countInactive;
        if (statTotal) statTotal.textContent = rows.length;
        if (statActive) statActive.textContent = countActive;
        if (statGym) statGym.textContent = countGym;
    }

    function renderEquipmentPage() {
        const searchVal = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
        const reqGymVal = document.getElementById('filter-requires-gym')?.value || '';

        const rows = Array.from(document.querySelectorAll('[data-equipment-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            const desc = r.getAttribute('data-desc') || '';
            const reqGym = r.getAttribute('data-requires-gym') || '';
            const isActive = r.getAttribute('data-active') || '1';

            const matchesStatus = (currentEquipmentStatusFilter === 'all') || (isActive === currentEquipmentStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || desc.includes(searchVal);
            const matchesReqGym = !reqGymVal || reqGym === reqGymVal;

            return matchesStatus && matchesSearch && matchesReqGym;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;

        if (currentEquipmentPage > totalPages) currentEquipmentPage = totalPages;
        if (currentEquipmentPage < 1) currentEquipmentPage = 1;

        const startIndex = (currentEquipmentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_equipment_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        // Pagination controls update
        const infoSpan = document.getElementById('equipment_pagination_info');
        const pageSpan = document.getElementById('page_number_display');
        const prevBtn = document.getElementById('prev_page_btn');
        const nextBtn = document.getElementById('next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay equipos para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} equipos`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentEquipmentPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentEquipmentPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentEquipmentPage >= totalPages);
    }

    function changeEquipmentPage(delta) {
        currentEquipmentPage += delta;
        renderEquipmentPage();
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

        updateCounters();
        renderEquipmentPage();
    });
</script>
@endsection
