@extends('layouts.admin')

@section('title', 'Inventario de Equipamiento')

@section('content')
<div class="space-y-8">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Equipamiento del Gimnasio</h1>
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

    <!-- Equipment Status Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-550 text-[10px] font-bold uppercase mb-1">Total de Equipos Registrados</span>
            <h3 class="text-xl font-black text-white">{{ $totalMachines }} Equipos</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Sede Física Requerida</span>
            <h3 class="text-xl font-black text-white text-lime-400">100% Instalado en Sala</h3>
        </div>
    </div>

    <!-- Equipment Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-white">Inventario Físico de Sala</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Nombre de Equipo</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4 text-center pr-6">Requiere Local Físico</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($equipment as $item)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 font-bold text-slate-100">{{ $item->name }}</td>
                            <td class="p-4 text-slate-400">{{ $item->description ?? 'Sin descripción.' }}</td>
                            <td class="p-4 text-center pr-6 font-bold text-lime-400 uppercase text-[10px]">
                                {{ $item->requires_gym ? 'Sí' : 'No' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-slate-550">
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
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Registrar Equipo Deportivo</h3>
            <button onclick="toggleModal('equipment-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_equipment') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre de Equipo</label>
                <input type="text" name="name" required placeholder="Ej: Prensa de Pierna 45° Matrix" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Descripción</label>
                <textarea name="description" placeholder="Ej: Prensa inclinada de discos para musculación de cuádriceps" rows="3" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="pt-4 flex gap-3">
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

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }
</script>
@endsection
