@extends('layouts.admin')

@section('title', 'Recetario & Biblioteca de Platos')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Recetario & Platos</h1>
            <p class="text-xs text-slate-400 mt-1">Diccionario global de recetas y preparaciones para la estructuración de planes nutricionales.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="toggleModal('category-modal')" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-300 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-4 h-4"></i> Crear Categoría
            </button>
            <button onclick="toggleModal('recipe-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Registrar Receta
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

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-550 text-[10px] font-bold uppercase mb-1">Recetas Registradas</span>
            <h3 class="text-xl font-black text-slate-100">{{ $recipes->count() }} Recetas</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Categorías Creadas</span>
            <h3 class="text-xl font-black text-slate-100 text-lime-400">{{ $categories->count() }} Categorías</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-emerald-400 text-[10px] font-bold uppercase mb-1">Calidad Nutricional</span>
            <h3 class="text-xl font-black text-slate-100 text-emerald-400">100% Personalizado</h3>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="relative w-full md:max-w-xs">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" id="search-input" onkeyup="filterRecipes()" placeholder="Buscar por nombre o descripción..." class="w-full pl-10 pr-4 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50">
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <select id="filter-category" onchange="filterRecipes()" class="px-3 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50">
                <option value="">Todas las Categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
            
            <select id="filter-goal" onchange="filterRecipes()" class="px-3 py-2 bg-slate-950/60 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50">
                <option value="">Cualquier Enfoque</option>
                <option value="gain_muscle">Hipertrofia / Volumen</option>
                <option value="lose_weight">Déficit / Definición</option>
                <option value="maintain">Recomposición / Balanceado</option>
                <option value="improve_endurance">Rendimiento / Resistencia</option>
            </select>
        </div>
    </div>

    <!-- Recipes Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="recipes-container">
        @forelse($recipes as $recipe)
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden flex flex-col group recipe-card" data-name="{{ strtolower($recipe->name) }}" data-category="{{ $recipe->category->name ?? '' }}" data-goal="{{ $recipe->goal_type }}">
                
                <!-- Recipe Image / Visual header -->
                <div class="h-44 w-full relative overflow-hidden bg-slate-950">
                    <img src="{{ $recipe->image_url ? asset($recipe->image_url) : 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=350&auto=format&fit=crop' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                    
                    <span class="absolute top-4 left-4 px-2.5 py-1 bg-slate-900/90 backdrop-blur-xs border border-slate-800 text-[10px] font-bold text-slate-300 rounded-lg uppercase tracking-wider">
                        {{ $recipe->category->name ?? 'Sin Categoría' }}
                    </span>
                    
                    <span class="absolute bottom-4 right-4 text-sm font-black text-amber-400 bg-slate-950/80 px-2 py-1 border border-slate-800 rounded-lg">
                        {{ number_format($recipe->calories_total, 0) }} Kcal
                    </span>
                </div>

                <!-- Recipe Content Info -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-bold text-slate-100 text-base line-clamp-1" title="{{ $recipe->name }}">{{ $recipe->name }}</h3>
                        </div>
                        <p class="text-xs text-slate-400 line-clamp-2 h-8">{{ $recipe->description ?? 'Sin descripción cargada.' }}</p>
                    </div>

                    <!-- Preparation and Servings -->
                    <div class="flex items-center gap-4 text-[10px] text-slate-500 font-bold uppercase border-b border-slate-850/60 pb-3">
                        <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i> {{ $recipe->preparation_min }} MINS</span>
                        <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i> {{ $recipe->servings }} PORCIONES</span>
                    </div>

                    <!-- Macro breakdown badges -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-950/45 p-2 rounded-xl border border-slate-850/60 text-center text-[10px] font-bold uppercase">
                        <div>
                            <span class="block text-slate-500 text-[8px] mb-0.5">Proteínas</span>
                            <span class="text-red-400 font-black">{{ $recipe->protein_g }}g</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 text-[8px] mb-0.5">Carbos</span>
                            <span class="text-lime-400 font-black">{{ $recipe->carbs_g }}g</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 text-[8px] mb-0.5">Grasas</span>
                            <span class="text-amber-500 font-black">{{ $recipe->fat_g }}g</span>
                        </div>
                    </div>

                    @if($recipe->instructions)
                        <div class="text-[10px] text-slate-350 bg-slate-900/30 p-3 rounded-xl border border-slate-800/40 mt-2">
                            <span class="block uppercase font-extrabold text-[9px] text-slate-500 mb-1">Instrucciones:</span>
                            <p class="line-clamp-2">{{ $recipe->instructions }}</p>
                        </div>
                    @endif

                    <!-- Action buttons -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-850/60">
                        <button onclick="openEditModal({{ json_encode($recipe) }})" class="p-1.5 bg-slate-900 hover:bg-slate-850 border border-slate-800 text-lime-400 hover:text-lime-300 transition-colors rounded-lg flex items-center gap-1 text-[10px] font-bold">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Editar
                        </button>
                        <form action="{{ route('catalogos.delete_recipe', $recipe->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Estás seguro de eliminar esta receta?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 bg-slate-900 hover:bg-slate-850 border border-slate-800 text-rose-450 hover:text-rose-350 transition-colors rounded-lg flex items-center gap-1 text-[10px] font-bold">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Borrar
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-span-full bg-slate-950/20 border border-dashed border-slate-850 rounded-3xl p-12 text-center text-slate-550">
                <i data-lucide="utensils-crossed" class="w-8 h-8 mx-auto text-slate-600 mb-2"></i>
                <h4 class="font-bold text-slate-400">Recetario Vacío</h4>
                <p class="text-xs text-slate-500 mt-1">Crea recetas nutritivas para que los entrenadores puedan estructurar los planes de alimentación.</p>
            </div>
        @endforelse
    </div>

</div>

<!-- ================= MODAL: REGISTRAR CATEGORÍA ================= -->
<div id="category-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Categoría de Recetas</h3>
            <button onclick="toggleModal('category-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_recipe_category') }}" method="POST" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Categoría</label>
                <input type="text" name="name" required placeholder="Ej: Nutrición Vegana, Volumen Máximo, Snacks" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción (Opcional)</label>
                <textarea name="description" placeholder="Ej: Recetas bajas en calorías y grasas saturadas." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
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

<!-- ================= MODAL: REGISTRAR RECETA ================= -->
<div id="recipe-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Receta en Recetario</h3>
            <button onclick="toggleModal('recipe-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('catalogos.store_recipe') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plato *</label>
                    <input type="text" name="name" required placeholder="Ej: Pollo al Limón con Papas" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría *</label>
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
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Enfoque Metabólico *</label>
                    <select name="goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50">
                        <option value="gain_muscle">Hipertrofia / Volumen</option>
                        <option value="lose_weight" selected>Déficit / Definición</option>
                        <option value="maintain">Recomposición / Balanceado</option>
                        <option value="improve_endurance">Rendimiento / Resistencia</option>
                        <option value="general">Salud / Nutrición Básica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Porciones por Receta *</label>
                    <input type="number" name="servings" required min="1" value="1" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Calorías (Kcal) *</label>
                    <input type="number" name="calories_total" required min="0" value="350" class="w-full px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Proteínas (g) *</label>
                    <input type="number" step="0.1" name="protein_g" required min="0" value="25" class="w-full px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Carbos (g) *</label>
                    <input type="number" step="0.1" name="carbs_g" required min="0" value="30" class="w-full px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Grasas (g) *</label>
                    <input type="number" step="0.1" name="fat_g" required min="0" value="8" class="w-full px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Tiempo de Preparación (Minutos) *</label>
                    <input type="number" name="preparation_min" required min="1" value="20" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto del Plato (Opcional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-450 focus:outline-none cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción Breve</label>
                <textarea name="description" placeholder="Pequeño resumen del platillo..." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Instrucciones de Cocción</label>
                <textarea name="instructions" placeholder="1. Calentar sartén. 2. Añadir verduras..." rows="3" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('recipe-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Receta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR RECETA ================= -->
<div id="edit-recipe-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Receta</h3>
            <button onclick="toggleModal('edit-recipe-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plato *</label>
                    <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría *</label>
                    <select name="category_id" id="edit-category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Enfoque Metabólico *</label>
                    <select name="goal_type" id="edit-goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300">
                        <option value="gain_muscle">Hipertrofia / Volumen</option>
                        <option value="lose_weight">Déficit / Definición</option>
                        <option value="maintain">Recomposición / Balanceado</option>
                        <option value="improve_endurance">Rendimiento / Resistencia</option>
                        <option value="general">Salud / Nutrición Básica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Porciones por Receta *</label>
                    <input type="number" name="servings" id="edit-servings" required min="1" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Calorías *</label>
                    <input type="number" name="calories_total" id="edit-calories_total" required min="0" class="w-full px-3 py-2 bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Proteínas (g) *</label>
                    <input type="number" step="0.1" name="protein_g" id="edit-protein_g" required min="0" class="w-full px-3 py-2 bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Carbos (g) *</label>
                    <input type="number" step="0.1" name="carbs_g" id="edit-carbs_g" required min="0" class="w-full px-3 py-2 bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1">Grasas (g) *</label>
                    <input type="number" step="0.1" name="fat_g" id="edit-fat_g" required min="0" class="w-full px-3 py-2 bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Tiempo de Preparación (Minutos) *</label>
                    <input type="number" name="preparation_min" id="edit-preparation_min" required min="1" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto del Plato (Opcional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-4 py-2 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-450 focus:outline-none cursor-pointer">
                </div>
            </div>
            
            <div class="flex items-center gap-2 pt-1 hidden" id="current-image-container">
                <input type="checkbox" name="remove_image" id="edit-remove-image" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                <label for="edit-remove-image" class="text-xs text-rose-400 font-medium cursor-pointer">Eliminar foto actual</label>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción Breve</label>
                <textarea name="description" id="edit-description" rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Instrucciones de Cocción</label>
                <textarea name="instructions" id="edit-instructions" rows="3" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-recipe-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
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
        document.getElementById('edit-form').action = `/recetas/${item.id}`;
        document.getElementById('edit-name').value = item.name;
        document.getElementById('edit-category_id').value = item.category_id;
        document.getElementById('edit-goal_type').value = item.goal_type;
        document.getElementById('edit-servings').value = item.servings;
        document.getElementById('edit-calories_total').value = Math.round(item.calories_total);
        document.getElementById('edit-protein_g').value = item.protein_g;
        document.getElementById('edit-carbs_g').value = item.carbs_g;
        document.getElementById('edit-fat_g').value = item.fat_g;
        document.getElementById('edit-preparation_min').value = item.preparation_min;
        document.getElementById('edit-description').value = item.description || '';
        document.getElementById('edit-instructions').value = item.instructions || '';
        
        // Show/hide remove photo checkbox
        const currentImgContainer = document.getElementById('current-image-container');
        const removeImgCheck = document.getElementById('edit-remove-image');
        if (item.image_url) {
            currentImgContainer.classList.remove('hidden');
        } else {
            currentImgContainer.classList.add('hidden');
        }
        removeImgCheck.checked = false;

        toggleModal('edit-recipe-modal');
    }

    function filterRecipes() {
        const searchVal = document.getElementById('search-input').value.toLowerCase();
        const categoryVal = document.getElementById('filter-category').value;
        const goalVal = document.getElementById('filter-goal').value;
        
        const cards = document.querySelectorAll('.recipe-card');
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const category = card.getAttribute('data-category');
            const goal = card.getAttribute('data-goal');
            
            const matchesSearch = name.includes(searchVal);
            const matchesCategory = categoryVal === "" || category === categoryVal;
            const matchesGoal = goalVal === "" || goal === goalVal;
            
            if (matchesSearch && matchesCategory && matchesGoal) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    }
</script>
@endsection
