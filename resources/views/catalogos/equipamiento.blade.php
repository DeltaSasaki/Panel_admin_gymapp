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
            <button onclick="toggleModal('equipment-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="dumbbell" class="w-4 h-4"></i> Registrar Máquina / Equipo
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-4 rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Equipment Status Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-550 text-[10px] font-bold uppercase mb-1">Total de Equipos Registrados</span>
            <h3 class="text-xl font-black text-slate-100">{{ $totalMachines }} Equipos</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Sede Física Requerida</span>
            <h3 class="text-xl font-black text-slate-100 text-lime-400">100% Instalado en Sala</h3>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-slate-100">Inventario Físico de Sala</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Nombre de Equipo</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4 text-center">Requiere Local Físico</th>
                        <th class="p-4 text-right pr-6 w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($equipment as $item)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img src="{{ $item->image_url ? asset($item->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-slate-400 max-w-xs truncate">{{ $item->description ?? 'Sin descripción.' }}</td>
                            <td class="p-4 text-center font-bold text-lime-400 uppercase text-[10px]">
                                {{ $item->requires_gym ? 'Sí' : 'No' }}
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        onclick="openEditModal({{ json_encode($item) }})" 
                                        class="p-1.5 text-lime-450 hover:text-lime-350 transition-colors"
                                        title="Editar">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('catalogos.delete_equipment', $item->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Estás seguro de eliminar este equipo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-450 hover:text-rose-350 transition-colors" title="Eliminar">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-550">
                                No se ha registrado ninguna máquina de entrenamiento aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR MÁQUINA ================= -->
<div id="equipment-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Equipo Deportivo</h3>
            <button onclick="toggleModal('equipment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_equipment') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Equipo</label>
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
                <input type="checkbox" name="requires_gym" id="requires_gym" value="1" checked class="rounded border-slate-850 bg-slate-950 text-lime-500 focus:ring-lime-500">
                <label for="requires_gym" class="text-slate-350">¿Requiere estar físicamente en la sucursal?</label>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('equipment-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Equipo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR MÁQUINA ================= -->
<div id="edit-equipment-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Equipo Deportivo</h3>
            <button onclick="toggleModal('edit-equipment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Equipo</label>
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
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
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

    function openEditModal(item) {
        document.getElementById('edit-form').action = `/equipamiento/${item.id}`;
        document.getElementById('edit-name').value = item.name;
        document.getElementById('edit-description').value = item.description || '';
        
        // Show/hide remove photo checkbox
        const currentImgContainer = document.getElementById('current-image-container');
        const removeImgCheck = document.getElementById('edit-remove-image');
        if (item.image_url) {
            currentImgContainer.classList.remove('hidden');
        } else {
            currentImgContainer.classList.add('hidden');
        }
        removeImgCheck.checked = false;
        
        document.getElementById('edit-requires-gym').checked = item.requires_gym == 1;
        toggleModal('edit-equipment-modal');
    }
</script>
@endsection
