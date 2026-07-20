@extends('layouts.admin')

@section('title', 'Biblioteca de Ejercicios')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Ejercicios & Biblioteca</h1>
            <p class="text-xs text-slate-400 mt-1">Diccionario global de ejercicios para programación de rutinas y planes de entrenamiento.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="toggleModal('category-modal')" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-300 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-4 h-4"></i> Crear Categoría
            </button>
            <button onclick="toggleModal('exercise-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Registrar Ejercicio
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

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 relative overflow-hidden group">
            <span class="block text-slate-550 text-[10px] font-bold uppercase mb-1">Total de Ejercicios</span>
            <h3 class="text-xl font-black text-slate-100">{{ $exercises->count() }} Ejercicios</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Categorías de Fuerza & Cardio</span>
            <h3 class="text-xl font-black text-slate-100 text-lime-400">{{ $categories->count() }} Categorías</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-emerald-400 text-[10px] font-bold uppercase mb-1">Dificultad Promedio</span>
            <h3 class="text-xl font-black text-slate-100 text-emerald-400">Intermedio / Completo</h3>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="relative w-full md:max-w-xs">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-input" onkeyup="filterExercises()" placeholder="Buscar por nombre o músculo..." class="w-full pl-10 pr-4 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50">
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <select id="filter-category" onchange="filterExercises()" class="px-3 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50">
                <option value="">Todas las Categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
            
            <select id="filter-difficulty" onchange="filterExercises()" class="px-3 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50">
                <option value="">Cualquier Dificultad</option>
                <option value="beginner">Principiante (Beginner)</option>
                <option value="intermediate">Intermedio (Intermediate)</option>
                <option value="advanced">Avanzado (Advanced)</option>
            </select>
            
            <select id="filter-equipment" onchange="filterExercises()" class="px-3 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50">
                <option value="">Cualquier Requerimiento</option>
                <option value="Sí">Requiere Máquina/Equipo</option>
                <option value="No">Sin Equipamiento (Peso Corporal)</option>
            </select>
        </div>
    </div>

    <!-- Exercises Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-slate-100">Biblioteca General de Movimientos</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse" id="exercises-table">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Nombre de Ejercicio</th>
                        <th class="p-4">Categoría</th>
                        <th class="p-4">Grupo Muscular</th>
                        <th class="p-4 text-center">Dificultad</th>
                        <th class="p-4 text-center">Requiere Equipamiento</th>
                        <th class="p-4 text-right pr-6 w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($exercises as $exercise)
                        <tr class="hover:bg-slate-900/20 text-slate-200 exercise-row" data-name="{{ strtolower($exercise->name) }}" data-muscle="{{ strtolower($exercise->muscle_group) }}" data-category="{{ $exercise->category->name ?? '' }}" data-difficulty="{{ $exercise->difficulty }}" data-equipment="{{ $exercise->requires_equipment ? 'Sí' : 'No' }}">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img src="{{ $exercise->image_url ? asset($exercise->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $exercise->name }}</span>
                                    @if($exercise->description)
                                        <span class="block text-[10px] text-slate-450 mt-0.5 line-clamp-1" title="{{ $exercise->description }}">{{ $exercise->description }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-slate-350">{{ $exercise->category->name ?? 'Sin categoría' }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 bg-slate-950 border border-slate-850 text-slate-300 rounded-md text-[10px] font-medium">
                                    {{ $exercise->muscle_group ?? 'Cuerpo Completo' }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($exercise->difficulty === 'beginner')
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full text-[9px] font-bold uppercase">Principiante</span>
                                @elseif($exercise->difficulty === 'intermediate')
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/25 rounded-full text-[9px] font-bold uppercase">Intermedio</span>
                                @else
                                    <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/25 rounded-full text-[9px] font-bold uppercase">Avanzado</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-slate-450 uppercase text-[10px]">
                                {{ $exercise->requires_equipment ? 'Sí' : 'No' }}
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    @if($exercise->video_url)
                                        <a href="{{ $exercise->video_url }}" target="_blank" class="p-1.5 text-lime-450 hover:text-lime-350 transition-colors" title="Ver video demostrativo">
                                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    
                                    @if($exercise->gym_id) {{-- Only allow modifying gym-specific exercises --}}
                                        <button 
                                            onclick="openEditModal({{ json_encode($exercise) }})" 
                                            class="p-1.5 text-lime-450 hover:text-lime-350 transition-colors"
                                            title="Editar">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('catalogos.delete_exercise', $exercise->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Estás seguro de eliminar este ejercicio de la biblioteca?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-450 hover:text-rose-350 transition-colors" title="Eliminar">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[9px] text-slate-550 italic" title="Este es un ejercicio global provisto por la plataforma y no puede eliminarse ni modificarse.">Global</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-550">
                                No se ha registrado ningún ejercicio en el catálogo de esta sucursal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR CATEGORÍA ================= -->
<div id="category-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Categoría de Ejercicios</h3>
            <button onclick="toggleModal('category-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_exercise_category') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Categoría</label>
                <input type="text" name="name" required placeholder="Ej: Fuerza Máxima, HIIT, Flexibilidad" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción (Opcional)</label>
                <textarea name="description" placeholder="Breve nota sobre qué tipo de ejercicios engloba" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('category-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Crear Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR EJERCICIO ================= -->
<div id="exercise-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Ejercicio en Biblioteca</h3>
            <button onclick="toggleModal('exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_exercise') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Ejercicio</label>
                    <input type="text" name="name" required placeholder="Ej: Press de Banca Inclinado" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría</label>
                    <select name="category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50">
                        <option value="">Selecciona Categoría</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Grupo Muscular Principal</label>
                    <input type="text" name="muscle_group" placeholder="Ej: Pectorales, Bíceps, Espalda" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Dificultad</label>
                    <select name="difficulty" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50">
                        <option value="beginner">Principiante (Beginner)</option>
                        <option value="intermediate" selected>Intermedio (Intermediate)</option>
                        <option value="advanced">Avanzado (Advanced)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción Breve</label>
                <textarea name="description" placeholder="Resumen del ejercicio y su finalidad." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Instrucciones de Ejecución</label>
                <textarea name="instructions" placeholder="1. Colocar pies a anchura de hombros. 2. Bajar lentamente controlando..." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto o Ilustración del Ejercicio (Opcional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-450 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">URL del Video (Demostrativo)</label>
                    <input type="text" name="video_url" placeholder="Ej: https://youtube.com/watch?v=..." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="requires_equipment" id="requires_equipment" value="1" class="rounded border-slate-850 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                    <label for="requires_equipment" class="text-slate-350 cursor-pointer">¿Requiere máquina o equipo?</label>
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Ejercicio
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR EJERCICIO ================= -->
<div id="edit-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Ejercicio</h3>
            <button onclick="toggleModal('edit-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Ejercicio</label>
                    <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría</label>
                    <select name="category_id" id="edit-category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Grupo Muscular Principal</label>
                    <input type="text" name="muscle_group" id="edit-muscle_group" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Dificultad</label>
                    <select name="difficulty" id="edit-difficulty" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50">
                        <option value="beginner">Principiante (Beginner)</option>
                        <option value="intermediate">Intermedio (Intermediate)</option>
                        <option value="advanced">Avanzado (Advanced)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción Breve</label>
                <textarea name="description" id="edit-description" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Instrucciones de Ejecución</label>
                <textarea name="instructions" id="edit-instructions" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Actualizar Foto del Ejercicio (Opcional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-450 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>
            <div class="flex items-center gap-2 pt-1 hidden" id="current-image-container">
                <input type="checkbox" name="remove_image" id="edit-remove-image" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                <label for="edit-remove-image" class="text-xs text-rose-400 font-medium cursor-pointer">Eliminar foto actual</label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">URL del Video (Demostrativo)</label>
                    <input type="text" name="video_url" id="edit-video_url" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="requires_equipment" id="edit-requires_equipment" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                    <label for="edit-requires_equipment" class="text-slate-350 cursor-pointer">¿Requiere máquina o equipo?</label>
                </div>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
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
        document.getElementById('edit-form').action = `/ejercicios/${item.id}`;
        document.getElementById('edit-name').value = item.name;
        document.getElementById('edit-category_id').value = item.category_id;
        document.getElementById('edit-muscle_group').value = item.muscle_group || '';
        document.getElementById('edit-difficulty').value = item.difficulty;
        document.getElementById('edit-description').value = item.description || '';
        document.getElementById('edit-instructions').value = item.instructions || '';
        document.getElementById('edit-video_url').value = item.video_url || '';
        document.getElementById('edit-requires_equipment').checked = item.requires_equipment == 1;
        
        // Show/hide remove photo checkbox
        const currentImgContainer = document.getElementById('current-image-container');
        const removeImgCheck = document.getElementById('edit-remove-image');
        if (item.image_url) {
            currentImgContainer.classList.remove('hidden');
        } else {
            currentImgContainer.classList.add('hidden');
        }
        removeImgCheck.checked = false;

        toggleModal('edit-exercise-modal');
    }

    function filterExercises() {
        const searchVal = document.getElementById('search-input').value.toLowerCase();
        const categoryVal = document.getElementById('filter-category').value;
        const difficultyVal = document.getElementById('filter-difficulty').value;
        const equipmentVal = document.getElementById('filter-equipment').value;
        
        const rows = document.querySelectorAll('.exercise-row');
        
        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const muscle = row.getAttribute('data-muscle');
            const category = row.getAttribute('data-category');
            const difficulty = row.getAttribute('data-difficulty');
            const equipment = row.getAttribute('data-equipment');
            
            const matchesSearch = name.includes(searchVal) || muscle.includes(searchVal);
            const matchesCategory = categoryVal === "" || category === categoryVal;
            const matchesDifficulty = difficultyVal === "" || difficulty === difficultyVal;
            const matchesEquipment = equipmentVal === "" || equipment === equipmentVal;
            
            if (matchesSearch && matchesCategory && matchesDifficulty && matchesEquipment) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
</script>
@endsection
