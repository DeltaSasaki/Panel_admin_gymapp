@extends('layouts.admin')

@section('title', 'Catálogo de Ingredientes y Nutrientes')

@section('content')
<div class="space-y-8">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Catálogo de Ingredientes</h1>
            <p class="text-xs text-slate-400 mt-1">Base de datos de alimentos, macros y densidad calórica para el armado de dietas.</p>
        </div>
        <div>
            <button onclick="toggleModal('ingredient-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Añadir Alimento / Ingrediente
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Ingredients Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-white">Alimentos Registrados</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Alimento</th>
                        <th class="p-4 text-center">Unidad Medida</th>
                        <th class="p-4 text-right">Proteína (g)</th>
                        <th class="p-4 text-right">Carbohidratos (g)</th>
                        <th class="p-4 text-right">Grasa (g)</th>
                        <th class="p-4 text-right pr-6">Calorías (kcal)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($ingredients as $i)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 font-bold text-slate-100">{{ $i->name }}</td>
                            <td class="p-4 text-center text-slate-400 font-semibold">
                                @if($i->unit === 'g') Por cada 100g
                                @elseif($i->unit === 'ml') Por cada 100ml
                                @else Por Unidad
                                @endif
                            </td>
                            <td class="p-4 text-right font-mono text-lime-400 font-semibold">{{ number_format($i->protein_g, 1) }}g</td>
                            <td class="p-4 text-right font-mono text-amber-400 font-semibold">{{ number_format($i->carbs_g, 1) }}g</td>
                            <td class="p-4 text-right font-mono text-pink-400 font-semibold">{{ number_format($i->fat_g, 1) }}g</td>
                            <td class="p-4 text-right pr-6 font-mono font-black text-white">{{ number_format($i->calories_per_100g, 0) }} kcal</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-550">
                                No hay ingredientes cargados en el catálogo alimenticio.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR ALIMENTO ================= -->
<div id="ingredient-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Añadir Alimento al Catálogo</h3>
            <button onclick="toggleModal('ingredient-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_ingredient') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre del Alimento</label>
                <input type="text" name="name" required placeholder="Ej: Pechuga de Pollo Deshuesada" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Unidad de Referencia</label>
                <select name="unit" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="g" selected>Gramos (por cada 100g)</option>
                    <option value="ml">Mililitros (por cada 100ml)</option>
                    <option value="unit">Por Unidad (1 huevo, 1 plátano)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Proteínas (g)</label>
                    <input type="number" step="0.1" name="protein_g" required placeholder="23.0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Carbohidratos (g)</label>
                    <input type="number" step="0.1" name="carbs_g" required placeholder="0.0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Grasas (g)</label>
                    <input type="number" step="0.1" name="fat_g" required placeholder="2.5" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Calorías Totales (kcal)</label>
                    <input type="number" name="calories_per_100g" required placeholder="120" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('ingredient-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Añadir Alimento
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
