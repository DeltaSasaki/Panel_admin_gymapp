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
            <button onclick="openCategoryModal()" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-300 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-4 h-4"></i> Crear Categoría
            </button>
            <button onclick="openCreateExerciseModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Registrar Ejercicio
            </button>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-400 text-[10px] font-bold uppercase mb-1">Total de Ejercicios</span>
            <h3 class="text-xl font-black text-slate-100"><span id="stat-total-exercises">{{ $exercises->count() }}</span> Ejercicios</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Ejercicios Activos</span>
            <h3 class="text-xl font-black text-lime-400"><span id="stat-active-exercises">{{ $exercises->where('is_active', 1)->count() }}</span> Activos</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-emerald-400 text-[10px] font-bold uppercase mb-1">Categorías de Movimiento</span>
            <h3 class="text-xl font-black text-emerald-400"><span id="stat-total-categories">{{ $categories->count() }}</span> Categorías</h3>
        </div>
    </div>

    <!-- Exercises Table Container -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <!-- Filters Bar Header -->
        <div class="p-6 border-b border-slate-850 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 flex-wrap">
                <h3 class="font-bold text-lg text-slate-100">Biblioteca General de Movimientos</h3>
                
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setStatusFilter('all')" id="status-filter-btn-all" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-status-all">{{ $exercises->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('1')" id="status-filter-btn-1" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-status-active">{{ $exercises->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('0')" id="status-filter-btn-0" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivos (<span id="count-status-inactive">{{ $exercises->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>

                <!-- Dropdown Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filter-category" onchange="onExerciseFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">Todas las Categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    
                    <select id="filter-difficulty" onchange="onExerciseFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">Cualquier Dificultad</option>
                        <option value="beginner">Principiante (Beginner)</option>
                        <option value="intermediate">Intermedio (Intermediate)</option>
                        <option value="advanced">Avanzado (Advanced)</option>
                    </select>
                    
                    <select id="filter-equipment" onchange="onExerciseFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">Cualquier Requerimiento</option>
                        <option value="Sí">Requiere Máquina/Equipo</option>
                        <option value="No">Sin Equipamiento (Peso Corporal)</option>
                    </select>
                </div>
            </div>

            <!-- Search Input -->
            <div class="relative w-full xl:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-input" oninput="onExerciseFilterChange()" placeholder="Buscar por nombre o músculo..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap" id="exercises-table">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6 text-left">Nombre de Ejercicio</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-left">Categoría</th>
                        <th class="p-4 text-left">Grupo Muscular</th>
                        <th class="p-4 text-center">Dificultad</th>
                        <th class="p-4 text-center">Requiere Equipamiento</th>
                        <th class="p-4 text-center pr-6">Acciones</th>
                    </tr>
                </thead>
                <tbody id="exercises_table_body" class="divide-y divide-slate-850/50">
                    @forelse($exercises as $exercise)
                        <tr id="exercise_row_{{ $exercise->id }}"
                            data-exercise-row
                            data-name="{{ strtolower($exercise->name) }}"
                            data-muscle="{{ strtolower($exercise->muscle_group) }}"
                            data-category="{{ $exercise->category->name ?? '' }}"
                            data-difficulty="{{ $exercise->difficulty }}"
                            data-equipment="{{ $exercise->requires_equipment ? 'Sí' : 'No' }}"
                            data-active="{{ $exercise->is_active ? 1 : 0 }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors {{ $exercise->is_active ? '' : 'opacity-60 bg-slate-950/30' }}">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img id="ex_img_{{ $exercise->id }}" src="{{ $exercise->image_url ? asset($exercise->image_url) : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <span id="ex_name_{{ $exercise->id }}" class="block font-bold text-slate-100">{{ $exercise->name }}</span>
                                    <span id="ex_desc_{{ $exercise->id }}" class="block text-[10px] text-slate-450 mt-0.5 line-clamp-1 {{ $exercise->description ? '' : 'hidden' }}" title="{{ $exercise->description }}">{{ $exercise->description }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-center" id="ex_status_{{ $exercise->id }}">
                                @if($exercise->is_active)
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-350 font-medium" id="ex_cat_{{ $exercise->id }}">{{ $exercise->category->name ?? 'Sin categoría' }}</td>
                            <td class="p-4">
                                <span id="ex_muscle_{{ $exercise->id }}" class="px-2.5 py-1 bg-slate-950 border border-slate-850 text-slate-300 rounded-lg text-[10px] font-semibold">
                                    {{ $exercise->muscle_group ?? 'Cuerpo Completo' }}
                                </span>
                            </td>
                            <td class="p-4 text-center" id="ex_diff_{{ $exercise->id }}">
                                @if($exercise->difficulty === 'beginner')
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full text-[9px] font-bold uppercase">Principiante</span>
                                @elseif($exercise->difficulty === 'intermediate')
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/25 rounded-full text-[9px] font-bold uppercase">Intermedio</span>
                                @else
                                    <span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/25 rounded-full text-[9px] font-bold uppercase">Avanzado</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-slate-400 uppercase text-[10px]" id="ex_equip_{{ $exercise->id }}">
                                {{ $exercise->requires_equipment ? 'Sí' : 'No' }}
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a id="ex_video_link_{{ $exercise->id }}" href="{{ $exercise->video_url ?? '#' }}" target="_blank" class="p-1.5 text-lime-400 hover:text-lime-300 transition-colors {{ $exercise->video_url ? '' : 'hidden' }}" title="Ver video demostrativo">
                                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <button type="button" onclick='openEditExerciseModal({{ json_encode($exercise) }})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Ejercicio">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    
                                    <button type="button" onclick="openDeleteExerciseModal({{ $exercise->id }}, '{{ addslashes($exercise->name) }}', {{ $exercise->is_active ? 1 : 0 }})" 
                                            id="ex_toggle_btn_{{ $exercise->id }}"
                                            class="p-1.5 {{ $exercise->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all shadow-sm" 
                                            title="{{ $exercise->is_active ? 'Inhabilitar Ejercicio' : 'Reactivar Ejercicio' }}">
                                        <i data-lucide="{{ $exercise->is_active ? 'power' : 'check-circle' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no_exercises_empty_row">
                            <td colspan="7" class="p-8 text-center text-slate-550">
                                No se ha registrado ningún ejercicio en el catálogo.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_exercises_search_row" class="hidden">
                        <td colspan="7" class="p-10 text-center text-slate-500">
                            <i data-lucide="dumbbell" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron ejercicios que coincidan con la búsqueda o filtro.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div id="exercise_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="exercise_pagination_info">Mostrando ejercicios...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="prev_page_btn" onclick="changeExercisePage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="next_page_btn" onclick="changeExercisePage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR CATEGORÍA ================= -->
<div id="category-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Categoría de Ejercicios</h3>
            <button type="button" onclick="toggleModal('category-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-category-form" action="{{ route('catalogos.store_exercise_category') }}" method="POST" onsubmit="submitCreateCategory(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Categoría *</label>
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
                <button type="submit" id="create-category-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Crear Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR EJERCICIO ================= -->
<div id="exercise-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Ejercicio en Biblioteca</h3>
            <button type="button" onclick="toggleModal('exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-exercise-form" action="{{ route('catalogos.store_exercise') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateExercise(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Ejercicio *</label>
                    <input type="text" name="name" required placeholder="Ej: Press de Banca Inclinado" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría *</label>
                    <select name="category_id" id="create-category-select" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Dificultad *</label>
                    <select name="difficulty" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                <button type="submit" id="create-exercise-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Ejercicio
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR EJERCICIO ================= -->
<div id="edit-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Ejercicio</h3>
            <button type="button" onclick="toggleModal('edit-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-exercise-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditExercise(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Ejercicio *</label>
                    <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría *</label>
                    <select name="category_id" id="edit-category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Dificultad *</label>
                    <select name="difficulty" id="edit-difficulty" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                <button type="submit" id="edit-exercise-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE EJERCICIO ================= -->
<div id="delete-exercise-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-exercise-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-exercise-status-title">Cambiar Estado del Ejercicio</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-exercise-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-exercise-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de este ejercicio?
        </p>

        <form id="delete-exercise-form" action="" method="POST" onsubmit="submitDeleteExercise(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('delete-exercise-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-exercise-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-exercise-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Temporary Toast Notifications
    function showToast(message, type = 'success') {
        let container = document.getElementById('exercise-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'exercise-toast-container';
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

    function openCategoryModal() {
        document.getElementById('create-category-form').reset();
        toggleModal('category-modal');
    }

    function openCreateExerciseModal() {
        document.getElementById('create-exercise-form').reset();
        toggleModal('exercise-modal');
    }

    function openEditExerciseModal(item) {
        document.getElementById('edit-exercise-form').action = `/ejercicios/${item.id}`;
        document.getElementById('edit-name').value = item.name;
        document.getElementById('edit-category_id').value = item.category_id;
        document.getElementById('edit-muscle_group').value = item.muscle_group || '';
        document.getElementById('edit-difficulty').value = item.difficulty;
        document.getElementById('edit-description').value = item.description || '';
        document.getElementById('edit-instructions').value = item.instructions || '';
        document.getElementById('edit-video_url').value = item.video_url || '';
        document.getElementById('edit-requires_equipment').checked = item.requires_equipment == 1;
        
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

    function openDeleteExerciseModal(exId, exName, isActive) {
        document.getElementById('delete-exercise-form').action = `/ejercicios/${exId}`;
        const titleEl = document.getElementById('modal-exercise-status-title');
        const descEl = document.getElementById('modal-exercise-status-desc');
        const btnTextEl = document.getElementById('modal-exercise-status-btn-text');
        const submitBtn = document.getElementById('delete-exercise-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Ejercicio';
            descEl.innerHTML = `¿Estás seguro de que deseas marcar como <strong>inactivo</strong> el ejercicio (<strong class="text-slate-100">${escapeHtml(exName)}</strong>)? Ya no aparecerá al armar nuevas rutinas.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Ejercicio';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> el ejercicio (<strong class="text-slate-100">${escapeHtml(exName)}</strong>) para que esté disponible en la biblioteca?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('delete-exercise-modal');
    }

    // AJAX Submission: Create Category
    async function submitCreateCategory(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-category-submit-btn');

        setBtnLoading(submitBtn, true, 'Creando...');

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
                const cat = data.category;
                
                const filterSelect = document.getElementById('filter-category');
                const createSelect = document.getElementById('create-category-select');
                const editSelect = document.getElementById('edit-category_id');

                if (filterSelect) {
                    const opt = document.createElement('option');
                    opt.value = cat.name;
                    opt.textContent = cat.name;
                    filterSelect.appendChild(opt);
                }

                [createSelect, editSelect].forEach(sel => {
                    if (sel) {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.name;
                        sel.appendChild(opt);
                    }
                });

                form.reset();
                toggleModal('category-modal');
                updateCounters();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al crear la categoría.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar crear la categoría.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Create Exercise
    async function submitCreateExercise(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-exercise-submit-btn');

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
                const ex = data.exercise;
                const tbody = document.getElementById('exercises_table_body');
                
                const emptyRow = document.getElementById('no_exercises_empty_row');
                if (emptyRow) emptyRow.classList.add('hidden');

                const exJsonStr = JSON.stringify(ex).replace(/'/g, "&#39;");
                const safeName = escapeHtml(ex.name);
                const catName = ex.category ? escapeHtml(ex.category.name) : 'Sin categoría';
                const muscleText = escapeHtml(ex.muscle_group || 'Cuerpo Completo');
                const imgUrl = ex.image_url ? `/${ex.image_url}` : 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=150&auto=format&fit=crop';
                
                let diffBadgeHtml = `<span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/25 rounded-full text-[9px] font-bold uppercase">Intermedio</span>`;
                if (ex.difficulty === 'beginner') {
                    diffBadgeHtml = `<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full text-[9px] font-bold uppercase">Principiante</span>`;
                } else if (ex.difficulty === 'advanced') {
                    diffBadgeHtml = `<span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/25 rounded-full text-[9px] font-bold uppercase">Avanzado</span>`;
                }

                const tr = document.createElement('tr');
                tr.id = `exercise_row_${ex.id}`;
                tr.setAttribute('data-exercise-row', '');
                tr.setAttribute('data-name', (ex.name || '').toLowerCase());
                tr.setAttribute('data-muscle', (ex.muscle_group || '').toLowerCase());
                tr.setAttribute('data-category', ex.category ? ex.category.name : '');
                tr.setAttribute('data-difficulty', ex.difficulty);
                tr.setAttribute('data-equipment', ex.requires_equipment ? 'Sí' : 'No');
                tr.setAttribute('data-active', '1');
                tr.className = 'hover:bg-slate-900/20 text-slate-200 transition-colors';

                tr.innerHTML = `
                    <td class="p-4 pl-6 flex items-center gap-3">
                        <img id="ex_img_${ex.id}" src="${imgUrl}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                        <div>
                            <span id="ex_name_${ex.id}" class="block font-bold text-slate-100">${safeName}</span>
                            <span id="ex_desc_${ex.id}" class="block text-[10px] text-slate-450 mt-0.5 line-clamp-1 ${ex.description ? '' : 'hidden'}" title="${escapeHtml(ex.description || '')}">${escapeHtml(ex.description || '')}</span>
                        </div>
                    </td>
                    <td class="p-4 text-center" id="ex_status_${ex.id}">
                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>
                    </td>
                    <td class="p-4 text-slate-350 font-medium" id="ex_cat_${ex.id}">${catName}</td>
                    <td class="p-4">
                        <span id="ex_muscle_${ex.id}" class="px-2.5 py-1 bg-slate-950 border border-slate-850 text-slate-300 rounded-lg text-[10px] font-semibold">
                            ${muscleText}
                        </span>
                    </td>
                    <td class="p-4 text-center" id="ex_diff_${ex.id}">${diffBadgeHtml}</td>
                    <td class="p-4 text-center font-bold text-slate-400 uppercase text-[10px]" id="ex_equip_${ex.id}">${ex.requires_equipment ? 'Sí' : 'No'}</td>
                    <td class="p-4 text-center pr-6">
                        <div class="flex items-center justify-center gap-2">
                            <a id="ex_video_link_${ex.id}" href="${ex.video_url || '#'}" target="_blank" class="p-1.5 text-lime-400 hover:text-lime-300 transition-colors ${ex.video_url ? '' : 'hidden'}" title="Ver video demostrativo">
                                <i data-lucide="play-circle" class="w-4 h-4"></i>
                            </a>
                            <button type="button" onclick='openEditExerciseModal(${exJsonStr})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Ejercicio">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="button" onclick="openDeleteExerciseModal(${ex.id}, '${safeName.replace(/'/g, "\\'")}', 1)" id="ex_toggle_btn_${ex.id}" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Ejercicio">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                `;

                tbody.prepend(tr);
                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('exercise-modal');
                updateCounters();
                renderExercisePage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al guardar el ejercicio.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar guardar el ejercicio.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Exercise
    async function submitEditExercise(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-exercise-submit-btn');

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
                const ex = data.exercise;
                const row = document.getElementById(`exercise_row_${ex.id}`);

                if (row) {
                    row.setAttribute('data-name', (ex.name || '').toLowerCase());
                    row.setAttribute('data-muscle', (ex.muscle_group || '').toLowerCase());
                    row.setAttribute('data-category', ex.category ? ex.category.name : '');
                    row.setAttribute('data-difficulty', ex.difficulty);
                    row.setAttribute('data-equipment', ex.requires_equipment ? 'Sí' : 'No');

                    const nameEl = document.getElementById(`ex_name_${ex.id}`);
                    const descEl = document.getElementById(`ex_desc_${ex.id}`);
                    const catEl = document.getElementById(`ex_cat_${ex.id}`);
                    const muscleEl = document.getElementById(`ex_muscle_${ex.id}`);
                    const diffEl = document.getElementById(`ex_diff_${ex.id}`);
                    const equipEl = document.getElementById(`ex_equip_${ex.id}`);
                    const videoLink = document.getElementById(`ex_video_link_${ex.id}`);
                    const imgEl = document.getElementById(`ex_img_${ex.id}`);

                    if (nameEl) nameEl.textContent = ex.name;
                    if (descEl) {
                        if (ex.description) {
                            descEl.textContent = ex.description;
                            descEl.classList.remove('hidden');
                        } else {
                            descEl.classList.add('hidden');
                        }
                    }
                    if (catEl) catEl.textContent = ex.category ? ex.category.name : 'Sin categoría';
                    if (muscleEl) muscleEl.textContent = ex.muscle_group || 'Cuerpo Completo';
                    if (equipEl) equipEl.textContent = ex.requires_equipment ? 'Sí' : 'No';

                    if (diffEl) {
                        if (ex.difficulty === 'beginner') {
                            diffEl.innerHTML = `<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full text-[9px] font-bold uppercase">Principiante</span>`;
                        } else if (ex.difficulty === 'intermediate') {
                            diffEl.innerHTML = `<span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/25 rounded-full text-[9px] font-bold uppercase">Intermedio</span>`;
                        } else {
                            diffEl.innerHTML = `<span class="px-2 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/25 rounded-full text-[9px] font-bold uppercase">Avanzado</span>`;
                        }
                    }

                    if (videoLink) {
                        if (ex.video_url) {
                            videoLink.href = ex.video_url;
                            videoLink.classList.remove('hidden');
                        } else {
                            videoLink.classList.add('hidden');
                        }
                    }

                    if (imgEl && ex.image_url) {
                        imgEl.src = `/${ex.image_url}`;
                    }
                }

                toggleModal('edit-exercise-modal');
                updateCounters();
                renderExercisePage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar el ejercicio.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el ejercicio.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status (Disable/Enable)
    async function submitDeleteExercise(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-exercise-submit-btn');

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
                const exId = data.exercise_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const row = document.getElementById(`exercise_row_${exId}`);

                if (row) {
                    row.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        row.classList.remove('opacity-60', 'bg-slate-950/30');
                    } else {
                        row.classList.add('opacity-60', 'bg-slate-950/30');
                    }

                    // Update Status Badge
                    const statusCell = document.getElementById(`ex_status_${exId}`);
                    if (statusCell) {
                        statusCell.innerHTML = newActiveStatus 
                            ? `<span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-bold uppercase rounded-full border border-emerald-500/20">Activo</span>`
                            : `<span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[9px] font-bold uppercase rounded-full border border-rose-500/20">Inactivo</span>`;
                    }

                    // Update Toggle Button
                    const toggleBtn = document.getElementById(`ex_toggle_btn_${exId}`);
                    const nameText = document.getElementById(`ex_name_${exId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openDeleteExerciseModal(exId, nameText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Ejercicio' : 'Reactivar Ejercicio';
                        toggleBtn.className = `p-1.5 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all shadow-sm`;
                        toggleBtn.innerHTML = `<i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-3.5 h-3.5"></i>`;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('delete-exercise-modal');
                updateCounters();
                renderExercisePage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado del ejercicio.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado del ejercicio.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Pagination & Filter Logic (10 per page)
    let currentExercisePage = 1;
    let currentExerciseStatusFilter = 'all';
    const itemsPerPage = 10;

    function setStatusFilter(status) {
        currentExerciseStatusFilter = status;

        const tabs = document.querySelectorAll('.status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('status-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentExercisePage = 1;
        renderExercisePage();
    }

    function onExerciseFilterChange() {
        currentExercisePage = 1;
        renderExercisePage();
    }

    function updateCounters() {
        const rows = document.querySelectorAll('[data-exercise-row]');
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
        
        const statTotal = document.getElementById('stat-total-exercises');
        const statActive = document.getElementById('stat-active-exercises');

        if (cAll) cAll.textContent = rows.length;
        if (cActive) cActive.textContent = countActive;
        if (cInactive) cInactive.textContent = countInactive;
        if (statTotal) statTotal.textContent = rows.length;
        if (statActive) statActive.textContent = countActive;
    }

    function renderExercisePage() {
        const searchVal = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
        const categoryVal = document.getElementById('filter-category')?.value || '';
        const difficultyVal = document.getElementById('filter-difficulty')?.value || '';
        const equipmentVal = document.getElementById('filter-equipment')?.value || '';

        const rows = Array.from(document.querySelectorAll('[data-exercise-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            const muscle = r.getAttribute('data-muscle') || '';
            const category = r.getAttribute('data-category') || '';
            const difficulty = r.getAttribute('data-difficulty') || '';
            const equipment = r.getAttribute('data-equipment') || '';
            const isActive = r.getAttribute('data-active') || '1';

            const matchesStatus = (currentExerciseStatusFilter === 'all') || (isActive === currentExerciseStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || muscle.includes(searchVal);
            const matchesCategory = !categoryVal || category === categoryVal;
            const matchesDifficulty = !difficultyVal || difficulty === difficultyVal;
            const matchesEquipment = !equipmentVal || equipment === equipmentVal;

            return matchesStatus && matchesSearch && matchesCategory && matchesDifficulty && matchesEquipment;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;

        if (currentExercisePage > totalPages) currentExercisePage = totalPages;
        if (currentExercisePage < 1) currentExercisePage = 1;

        const startIndex = (currentExercisePage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_exercises_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        // Pagination controls update
        const infoSpan = document.getElementById('exercise_pagination_info');
        const pageSpan = document.getElementById('page_number_display');
        const prevBtn = document.getElementById('prev_page_btn');
        const nextBtn = document.getElementById('next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay ejercicios para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} ejercicios`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentExercisePage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentExercisePage <= 1);
        if (nextBtn) nextBtn.disabled = (currentExercisePage >= totalPages);
    }

    function changeExercisePage(delta) {
        currentExercisePage += delta;
        renderExercisePage();
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
        renderExercisePage();
    });
</script>
@endsection
