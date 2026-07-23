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
            <button onclick="openCategoryModal()" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-slate-300 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-4 h-4"></i> Crear Categoría
            </button>
            <button onclick="openCreateRecipeModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Registrar Receta
            </button>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-slate-400 text-[10px] font-bold uppercase mb-1">Total de Recetas</span>
            <h3 class="text-xl font-black text-slate-100"><span id="stat-total-recipes">{{ $recipes->count() }}</span> Recetas</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-lime-400 text-[10px] font-bold uppercase mb-1">Recetas Activas</span>
            <h3 class="text-xl font-black text-lime-400"><span id="stat-active-recipes">{{ $recipes->where('is_active', 1)->count() }}</span> Activas</h3>
        </div>
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
            <span class="block text-emerald-400 text-[10px] font-bold uppercase mb-1">Categorías Registradas</span>
            <h3 class="text-xl font-black text-emerald-400"><span id="stat-total-categories">{{ $categories->count() }}</span> Categorías</h3>
        </div>
    </div>

    <!-- Filters & Search Container -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 space-y-4">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-wrap">
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setStatusFilter('all')" id="status-btn-all" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todas (<span id="count-status-all">{{ $recipes->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('1')" id="status-btn-1" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activas (<span id="count-status-active">{{ $recipes->where('is_active', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setStatusFilter('0')" id="status-btn-0" class="status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inactivas (<span id="count-status-inactive">{{ $recipes->where('is_active', 0)->count() }}</span>)
                    </button>
                </div>

                <!-- Dropdown Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filter-category" onchange="onRecipeFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">Todas las Categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    
                    <select id="filter-goal" onchange="onRecipeFilterChange()" class="px-3 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="">Cualquier Enfoque</option>
                        <option value="gain_muscle">Hipertrofia / Volumen</option>
                        <option value="lose_weight">Déficit / Definición</option>
                        <option value="maintain">Recomposición / Balanceado</option>
                        <option value="improve_endurance">Rendimiento / Resistencia</option>
                        <option value="general">Salud / Nutrición Básica</option>
                    </select>
                </div>
            </div>

            <!-- Search Input -->
            <div class="relative w-full xl:w-72">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-input" oninput="onRecipeFilterChange()" placeholder="Buscar receta..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

        </div>
    </div>

    <!-- Recipes Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="recipes-container">
        @forelse($recipes as $recipe)
            <div id="recipe_card_{{ $recipe->id }}"
                 data-recipe-card
                 data-name="{{ strtolower($recipe->name) }}"
                 data-category="{{ $recipe->category->name ?? '' }}"
                 data-goal="{{ $recipe->goal_type }}"
                 data-active="{{ $recipe->is_active ? 1 : 0 }}"
                 class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden flex flex-col group recipe-card transition-all {{ $recipe->is_active ? '' : 'opacity-65 grayscale-[30%]' }}">
                
                <!-- Recipe Image / Visual header -->
                <div class="h-44 w-full relative overflow-hidden bg-slate-950">
                    <img id="recipe_img_{{ $recipe->id }}" src="{{ $recipe->image_url ? asset($recipe->image_url) : 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=350&auto=format&fit=crop' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                    
                    <div class="absolute top-4 left-4 flex items-center gap-2">
                        <span id="recipe_cat_badge_{{ $recipe->id }}" class="px-2.5 py-1 bg-slate-900/90 backdrop-blur-xs border border-slate-800 text-[10px] font-bold text-slate-300 rounded-lg uppercase tracking-wider">
                            {{ $recipe->category->name ?? 'Sin Categoría' }}
                        </span>
                        <span id="recipe_status_badge_{{ $recipe->id }}">
                            @if($recipe->is_active)
                                <span class="px-2 py-0.5 bg-emerald-500/90 text-slate-950 text-[9px] font-extrabold uppercase rounded-md shadow-sm">Activa</span>
                            @else
                                <span class="px-2 py-0.5 bg-rose-500/90 text-white text-[9px] font-extrabold uppercase rounded-md shadow-sm">Inactiva</span>
                            @endif
                        </span>
                    </div>
                    
                    <span id="recipe_calories_badge_{{ $recipe->id }}" class="absolute bottom-4 right-4 text-sm font-black text-amber-400 bg-slate-950/80 px-2.5 py-1 border border-slate-800 rounded-lg shadow-md">
                        {{ number_format($recipe->calories_total, 0) }} Kcal
                    </span>
                </div>

                <!-- Recipe Content Info -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <h3 id="recipe_title_{{ $recipe->id }}" class="font-bold text-slate-100 text-base line-clamp-1" title="{{ $recipe->name }}">{{ $recipe->name }}</h3>
                        <p id="recipe_desc_{{ $recipe->id }}" class="text-xs text-slate-400 line-clamp-2 min-h-[32px]">{{ $recipe->description ?? 'Sin descripción cargada.' }}</p>
                    </div>

                    <!-- Preparation and Servings -->
                    <div class="flex items-center gap-4 text-[10px] text-slate-500 font-bold uppercase border-b border-slate-850/60 pb-3">
                        <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i> <span id="recipe_prep_{{ $recipe->id }}">{{ $recipe->preparation_min }}</span> MINS</span>
                        <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i> <span id="recipe_servings_{{ $recipe->id }}">{{ $recipe->servings }}</span> PORCIONES</span>
                    </div>

                    <!-- Macro breakdown badges -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-950/45 p-2 rounded-xl border border-slate-850/60 text-center text-[10px] font-bold uppercase">
                        <div>
                            <span class="block text-slate-500 text-[8px] mb-0.5">Proteínas</span>
                            <span id="recipe_protein_{{ $recipe->id }}" class="text-red-400 font-black">{{ $recipe->protein_g }}g</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 text-[8px] mb-0.5">Carbos</span>
                            <span id="recipe_carbs_{{ $recipe->id }}" class="text-lime-400 font-black">{{ $recipe->carbs_g }}g</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 text-[8px] mb-0.5">Grasas</span>
                            <span id="recipe_fat_{{ $recipe->id }}" class="text-amber-500 font-black">{{ $recipe->fat_g }}g</span>
                        </div>
                    </div>

                    <div id="recipe_instructions_container_{{ $recipe->id }}" class="text-[10px] text-slate-350 bg-slate-900/30 p-3 rounded-xl border border-slate-800/40 {{ $recipe->instructions ? '' : 'hidden' }}">
                        <span class="block uppercase font-extrabold text-[9px] text-slate-500 mb-1">Instrucciones:</span>
                        <p id="recipe_instructions_{{ $recipe->id }}" class="line-clamp-2">{{ $recipe->instructions }}</p>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-850/60">
                        <button type="button" onclick='openEditRecipeModal({{ json_encode($recipe) }})' class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500 border border-amber-500/25 text-amber-400 hover:text-slate-950 transition-all rounded-xl flex items-center gap-1.5 text-xs font-bold shadow-sm">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Editar
                        </button>
                        <button type="button" onclick="openDeleteRecipeModal({{ $recipe->id }}, '{{ addslashes($recipe->name) }}', {{ $recipe->is_active ? 1 : 0 }})" 
                                id="recipe_toggle_btn_{{ $recipe->id }}"
                                class="px-3 py-1.5 {{ $recipe->is_active ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25' }} border rounded-xl transition-all flex items-center gap-1.5 text-xs font-bold shadow-sm"
                                title="{{ $recipe->is_active ? 'Inhabilitar Receta' : 'Reactivar Receta' }}">
                            <i data-lucide="{{ $recipe->is_active ? 'power' : 'check-circle' }}" class="w-3.5 h-3.5"></i>
                            <span id="recipe_toggle_txt_{{ $recipe->id }}">{{ $recipe->is_active ? 'Inhabilitar' : 'Activar' }}</span>
                        </button>
                    </div>

                </div>
            </div>
        @empty
            <div id="no_recipes_empty_state" class="col-span-full bg-slate-950/20 border border-dashed border-slate-850 rounded-3xl p-12 text-center text-slate-550">
                <i data-lucide="utensils-crossed" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                <h4 class="font-bold text-slate-400">Recetario Vacío</h4>
                <p class="text-xs text-slate-500 mt-1">Crea recetas nutritivas para que los entrenadores puedan estructurar los planes de alimentación.</p>
            </div>
        @endforelse

        <div id="no_recipes_search_state" class="hidden col-span-full bg-slate-950/20 border border-dashed border-slate-850 rounded-3xl p-12 text-center text-slate-550">
            <i data-lucide="search" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
            <h4 class="font-bold text-slate-400">Sin Coincidencias</h4>
            <p class="text-xs text-slate-500 mt-1">No se encontraron recetas con los filtros aplicados.</p>
        </div>
    </div>

    <!-- Pagination Footer Controls -->
    <div id="recipes_pagination_container" class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 shadow-lg">
        <span id="recipes_pagination_info">Mostrando recetas...</span>
        <div class="flex items-center gap-2">
            <button type="button" id="prev_page_btn" onclick="changeRecipePage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-850 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Anterior
            </button>
            <span id="page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
            <button type="button" id="next_page_btn" onclick="changeRecipePage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-850 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                Siguiente
            </button>
        </div>
    </div>

</div>

<!-- ================= MODAL: REGISTRAR CATEGORÍA ================= -->
<div id="category-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Categoría de Recetas</h3>
            <button type="button" onclick="toggleModal('category-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-category-form" action="{{ route('catalogos.store_recipe_category') }}" method="POST" onsubmit="submitCreateCategory(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de Categoría *</label>
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
                <button type="submit" id="create-category-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Crear Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR RECETA ================= -->
<div id="recipe-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Receta en Recetario</h3>
            <button type="button" onclick="toggleModal('recipe-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-recipe-form" action="{{ route('catalogos.store_recipe') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateRecipe(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plato *</label>
                    <input type="text" name="name" required placeholder="Ej: Pollo al Limón con Papas" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
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
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Enfoque Metabólico *</label>
                    <select name="goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                <button type="submit" id="create-recipe-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Receta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR RECETA ================= -->
<div id="edit-recipe-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Receta</h3>
            <button type="button" onclick="toggleModal('edit-recipe-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-recipe-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditRecipe(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Plato *</label>
                    <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría *</label>
                    <select name="category_id" id="edit-category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300 cursor-pointer">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Enfoque Metabólico *</label>
                    <select name="goal_type" id="edit-goal_type" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-300 cursor-pointer">
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
                <button type="submit" id="edit-recipe-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CAMBIAR ESTADO DE RECETA ================= -->
<div id="delete-recipe-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-5 animate-scale-up shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div id="modal-recipe-status-icon-bg" class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
                    <i data-lucide="power" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal-recipe-status-title">Cambiar Estado de la Receta</h3>
                    <span class="text-xs text-amber-400 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i> Confirmación requerida
                    </span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-recipe-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <p class="text-xs text-slate-300 leading-relaxed" id="modal-recipe-status-desc">
            ¿Estás seguro de que deseas cambiar el estado de esta receta?
        </p>

        <form id="delete-recipe-form" action="" method="POST" onsubmit="submitDeleteRecipe(event)" class="pt-3 flex gap-3 border-t border-slate-800">
            @csrf
            @method('DELETE')
            <button type="button" onclick="toggleModal('delete-recipe-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                Cancelar
            </button>
            <button type="submit" id="delete-recipe-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                <span id="modal-recipe-status-btn-text">Confirmar</span>
            </button>
        </form>
    </div>
</div>

<script>
    // Temporary Toast Notifications
    function showToast(message, type = 'success') {
        let container = document.getElementById('recipe-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'recipe-toast-container';
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

    function openCreateRecipeModal() {
        document.getElementById('create-recipe-form').reset();
        toggleModal('recipe-modal');
    }

    function openEditRecipeModal(recipe) {
        document.getElementById('edit-recipe-form').action = `/recetas/${recipe.id}`;
        document.getElementById('edit-name').value = recipe.name;
        document.getElementById('edit-category_id').value = recipe.category_id;
        document.getElementById('edit-goal_type').value = recipe.goal_type;
        document.getElementById('edit-servings').value = recipe.servings;
        document.getElementById('edit-calories_total').value = Math.round(recipe.calories_total);
        document.getElementById('edit-protein_g').value = recipe.protein_g;
        document.getElementById('edit-carbs_g').value = recipe.carbs_g;
        document.getElementById('edit-fat_g').value = recipe.fat_g;
        document.getElementById('edit-preparation_min').value = recipe.preparation_min;
        document.getElementById('edit-description').value = recipe.description || '';
        document.getElementById('edit-instructions').value = recipe.instructions || '';
        
        const currentImgContainer = document.getElementById('current-image-container');
        const removeImgCheck = document.getElementById('edit-remove-image');
        if (recipe.image_url) {
            currentImgContainer.classList.remove('hidden');
        } else {
            currentImgContainer.classList.add('hidden');
        }
        removeImgCheck.checked = false;

        toggleModal('edit-recipe-modal');
    }

    function openDeleteRecipeModal(recipeId, recipeName, isActive) {
        document.getElementById('delete-recipe-form').action = `/recetas/${recipeId}`;
        const titleEl = document.getElementById('modal-recipe-status-title');
        const descEl = document.getElementById('modal-recipe-status-desc');
        const btnTextEl = document.getElementById('modal-recipe-status-btn-text');
        const submitBtn = document.getElementById('delete-recipe-submit-btn');

        if (isActive) {
            titleEl.textContent = 'Inhabilitar Receta';
            descEl.innerHTML = `¿Estás seguro de que deseas marcar como <strong>inactiva</strong> la receta (<strong class="text-slate-100">${escapeHtml(recipeName)}</strong>)? Ya no podrá ser seleccionada en los nuevos menús de dieta.`;
            btnTextEl.textContent = 'Sí, Inhabilitar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        } else {
            titleEl.textContent = 'Reactivar Receta';
            descEl.innerHTML = `¿Deseas volver a <strong>activar</strong> la receta (<strong class="text-slate-100">${escapeHtml(recipeName)}</strong>) para que esté disponible en el recetario?`;
            btnTextEl.textContent = 'Sí, Activar';
            submitBtn.className = "flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-lime-500 hover:from-emerald-400 hover:to-lime-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-1.5";
        }

        toggleModal('delete-recipe-modal');
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
                
                // Add to category select dropdowns
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

    // AJAX Submission: Create Recipe
    async function submitCreateRecipe(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-recipe-submit-btn');

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
                const r = data.recipe;
                const container = document.getElementById('recipes-container');
                
                const emptyState = document.getElementById('no_recipes_empty_state');
                if (emptyState) emptyState.classList.add('hidden');

                const rJsonStr = JSON.stringify(r).replace(/'/g, "&#39;");
                const safeName = escapeHtml(r.name);
                const safeDesc = escapeHtml(r.description || 'Sin descripción cargada.');
                const safeInstr = escapeHtml(r.instructions || '');
                const catName = r.category ? escapeHtml(r.category.name) : 'Sin Categoría';
                const imgUrl = r.image_url ? `/${r.image_url}` : 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=350&auto=format&fit=crop';

                const card = document.createElement('div');
                card.id = `recipe_card_${r.id}`;
                card.setAttribute('data-recipe-card', '');
                card.setAttribute('data-name', (r.name || '').toLowerCase());
                card.setAttribute('data-category', r.category ? r.category.name : '');
                card.setAttribute('data-goal', r.goal_type);
                card.setAttribute('data-active', '1');
                card.className = 'bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden flex flex-col group recipe-card transition-all';

                card.innerHTML = `
                    <div class="h-44 w-full relative overflow-hidden bg-slate-950">
                        <img id="recipe_img_${r.id}" src="${imgUrl}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                        
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            <span id="recipe_cat_badge_${r.id}" class="px-2.5 py-1 bg-slate-900/90 backdrop-blur-xs border border-slate-800 text-[10px] font-bold text-slate-300 rounded-lg uppercase tracking-wider">
                                ${catName}
                            </span>
                            <span id="recipe_status_badge_${r.id}">
                                <span class="px-2 py-0.5 bg-emerald-500/90 text-slate-950 text-[9px] font-extrabold uppercase rounded-md shadow-sm">Activa</span>
                            </span>
                        </div>
                        
                        <span id="recipe_calories_badge_${r.id}" class="absolute bottom-4 right-4 text-sm font-black text-amber-400 bg-slate-950/80 px-2.5 py-1 border border-slate-800 rounded-lg shadow-md">
                            ${Math.round(r.calories_total)} Kcal
                        </span>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 id="recipe_title_${r.id}" class="font-bold text-slate-100 text-base line-clamp-1" title="${safeName}">${safeName}</h3>
                            <p id="recipe_desc_${r.id}" class="text-xs text-slate-400 line-clamp-2 min-h-[32px]">${safeDesc}</p>
                        </div>

                        <div class="flex items-center gap-4 text-[10px] text-slate-500 font-bold uppercase border-b border-slate-850/60 pb-3">
                            <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i> <span id="recipe_prep_${r.id}">${r.preparation_min}</span> MINS</span>
                            <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i> <span id="recipe_servings_${r.id}">${r.servings}</span> PORCIONES</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 bg-slate-950/45 p-2 rounded-xl border border-slate-850/60 text-center text-[10px] font-bold uppercase">
                            <div>
                                <span class="block text-slate-500 text-[8px] mb-0.5">Proteínas</span>
                                <span id="recipe_protein_${r.id}" class="text-red-400 font-black">${r.protein_g}g</span>
                            </div>
                            <div>
                                <span class="block text-slate-500 text-[8px] mb-0.5">Carbos</span>
                                <span id="recipe_carbs_${r.id}" class="text-lime-400 font-black">${r.carbs_g}g</span>
                            </div>
                            <div>
                                <span class="block text-slate-500 text-[8px] mb-0.5">Grasas</span>
                                <span id="recipe_fat_${r.id}" class="text-amber-500 font-black">${r.fat_g}g</span>
                            </div>
                        </div>

                        <div id="recipe_instructions_container_${r.id}" class="text-[10px] text-slate-350 bg-slate-900/30 p-3 rounded-xl border border-slate-800/40 ${safeInstr ? '' : 'hidden'}">
                            <span class="block uppercase font-extrabold text-[9px] text-slate-500 mb-1">Instrucciones:</span>
                            <p id="recipe_instructions_${r.id}" class="line-clamp-2">${safeInstr}</p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-850/60">
                            <button type="button" onclick='openEditRecipeModal(${rJsonStr})' class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500 border border-amber-500/25 text-amber-400 hover:text-slate-950 transition-all rounded-xl flex items-center gap-1.5 text-xs font-bold shadow-sm">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Editar
                            </button>
                            <button type="button" onclick="openDeleteRecipeModal(${r.id}, '${safeName.replace(/'/g, "\\'")}', 1)" id="recipe_toggle_btn_${r.id}" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all flex items-center gap-1.5 text-xs font-bold shadow-sm" title="Inhabilitar Receta">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                <span id="recipe_toggle_txt_${r.id}">Inhabilitar</span>
                            </button>
                        </div>
                    </div>
                `;

                container.prepend(card);
                if (window.lucide) window.lucide.createIcons();

                form.reset();
                toggleModal('recipe-modal');
                updateCounters();
                renderRecipePage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al guardar la receta.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar guardar la receta.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Edit Recipe
    async function submitEditRecipe(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-recipe-submit-btn');

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
                const r = data.recipe;
                const card = document.getElementById(`recipe_card_${r.id}`);

                if (card) {
                    card.setAttribute('data-name', (r.name || '').toLowerCase());
                    card.setAttribute('data-category', r.category ? r.category.name : '');
                    card.setAttribute('data-goal', r.goal_type);

                    const titleEl = document.getElementById(`recipe_title_${r.id}`);
                    const descEl = document.getElementById(`recipe_desc_${r.id}`);
                    const catBadge = document.getElementById(`recipe_cat_badge_${r.id}`);
                    const calBadge = document.getElementById(`recipe_calories_badge_${r.id}`);
                    const prepEl = document.getElementById(`recipe_prep_${r.id}`);
                    const servEl = document.getElementById(`recipe_servings_${r.id}`);
                    const protEl = document.getElementById(`recipe_protein_${r.id}`);
                    const carbsEl = document.getElementById(`recipe_carbs_${r.id}`);
                    const fatEl = document.getElementById(`recipe_fat_${r.id}`);
                    const instContainer = document.getElementById(`recipe_instructions_container_${r.id}`);
                    const instEl = document.getElementById(`recipe_instructions_${r.id}`);
                    const imgEl = document.getElementById(`recipe_img_${r.id}`);

                    if (titleEl) titleEl.textContent = r.name;
                    if (descEl) descEl.textContent = r.description || 'Sin descripción cargada.';
                    if (catBadge) catBadge.textContent = r.category ? r.category.name : 'Sin Categoría';
                    if (calBadge) calBadge.textContent = `${Math.round(r.calories_total)} Kcal`;
                    if (prepEl) prepEl.textContent = r.preparation_min;
                    if (servEl) servEl.textContent = r.servings;
                    if (protEl) protEl.textContent = `${r.protein_g}g`;
                    if (carbsEl) carbsEl.textContent = `${r.carbs_g}g`;
                    if (fatEl) fatEl.textContent = `${r.fat_g}g`;

                    if (instContainer && instEl) {
                        if (r.instructions) {
                            instEl.textContent = r.instructions;
                            instContainer.classList.remove('hidden');
                        } else {
                            instContainer.classList.add('hidden');
                        }
                    }

                    if (imgEl && r.image_url) {
                        imgEl.src = `/${r.image_url}`;
                    }
                }

                toggleModal('edit-recipe-modal');
                updateCounters();
                renderRecipePage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar receta.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar la receta.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Toggle Active Status (Disable/Enable)
    async function submitDeleteRecipe(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('delete-recipe-submit-btn');

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
                const rId = data.recipe_id;
                const newActiveStatus = data.is_active ? 1 : 0;
                const card = document.getElementById(`recipe_card_${rId}`);

                if (card) {
                    card.setAttribute('data-active', newActiveStatus);
                    if (newActiveStatus) {
                        card.classList.remove('opacity-65', 'grayscale-[30%]');
                    } else {
                        card.classList.add('opacity-65', 'grayscale-[30%]');
                    }

                    // Update Status Badge
                    const statusBadge = document.getElementById(`recipe_status_badge_${rId}`);
                    if (statusBadge) {
                        statusBadge.innerHTML = newActiveStatus 
                            ? `<span class="px-2 py-0.5 bg-emerald-500/90 text-slate-950 text-[9px] font-extrabold uppercase rounded-md shadow-sm">Activa</span>`
                            : `<span class="px-2 py-0.5 bg-rose-500/90 text-white text-[9px] font-extrabold uppercase rounded-md shadow-sm">Inactiva</span>`;
                    }

                    // Update Toggle Button
                    const toggleBtn = document.getElementById(`recipe_toggle_btn_${rId}`);
                    const toggleTxt = document.getElementById(`recipe_toggle_txt_${rId}`);
                    const titleText = document.getElementById(`recipe_title_${rId}`)?.textContent || '';

                    if (toggleBtn) {
                        toggleBtn.onclick = () => openDeleteRecipeModal(rId, titleText, newActiveStatus);
                        toggleBtn.title = newActiveStatus ? 'Inhabilitar Receta' : 'Reactivar Receta';
                        toggleBtn.className = `px-3 py-1.5 ${newActiveStatus ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border-rose-500/25' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border-emerald-500/25'} border rounded-xl transition-all flex items-center gap-1.5 text-xs font-bold shadow-sm`;
                        toggleBtn.innerHTML = `
                            <i data-lucide="${newActiveStatus ? 'power' : 'check-circle'}" class="w-3.5 h-3.5"></i>
                            <span id="recipe_toggle_txt_${rId}">${newActiveStatus ? 'Inhabilitar' : 'Activar'}</span>
                        `;
                    }
                }

                if (window.lucide) window.lucide.createIcons();
                toggleModal('delete-recipe-modal');
                updateCounters();
                renderRecipePage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado de la receta.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al cambiar el estado de la receta.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Pagination & Filter Logic (6 cards per page)
    let currentRecipePage = 1;
    let currentRecipeStatusFilter = 'all';
    const itemsPerPage = 6;

    function setStatusFilter(status) {
        currentRecipeStatusFilter = status;

        const tabs = document.querySelectorAll('.status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('status-btn-' + status);
        if (activeTab) {
            activeTab.className = "status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentRecipePage = 1;
        renderRecipePage();
    }

    function onRecipeFilterChange() {
        currentRecipePage = 1;
        renderRecipePage();
    }

    function updateCounters() {
        const cards = document.querySelectorAll('[data-recipe-card]');
        let countActive = 0;
        let countInactive = 0;

        cards.forEach(c => {
            const isActive = c.getAttribute('data-active') === '1';
            if (isActive) countActive++;
            else countInactive++;
        });

        const cAll = document.getElementById('count-status-all');
        const cActive = document.getElementById('count-status-active');
        const cInactive = document.getElementById('count-status-inactive');
        
        const statTotal = document.getElementById('stat-total-recipes');
        const statActive = document.getElementById('stat-active-recipes');

        if (cAll) cAll.textContent = cards.length;
        if (cActive) cActive.textContent = countActive;
        if (cInactive) cInactive.textContent = countInactive;
        if (statTotal) statTotal.textContent = cards.length;
        if (statActive) statActive.textContent = countActive;
    }

    function renderRecipePage() {
        const searchVal = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
        const categoryVal = document.getElementById('filter-category')?.value || '';
        const goalVal = document.getElementById('filter-goal')?.value || '';

        const cards = Array.from(document.querySelectorAll('[data-recipe-card]'));

        const filtered = cards.filter(c => {
            const name = c.getAttribute('data-name') || '';
            const category = c.getAttribute('data-category') || '';
            const goal = c.getAttribute('data-goal') || '';
            const isActive = c.getAttribute('data-active') || '1';

            const matchesStatus = (currentRecipeStatusFilter === 'all') || (isActive === currentRecipeStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal);
            const matchesCategory = !categoryVal || category === categoryVal;
            const matchesGoal = !goalVal || goal === goalVal;

            return matchesStatus && matchesSearch && matchesCategory && matchesGoal;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / itemsPerPage) || 1;

        if (currentRecipePage > totalPages) currentRecipePage = totalPages;
        if (currentRecipePage < 1) currentRecipePage = 1;

        const startIndex = (currentRecipePage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        cards.forEach(c => c.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(c => c.classList.remove('hidden'));

        const noSearchState = document.getElementById('no_recipes_search_state');
        if (noSearchState) {
            if (totalFiltered === 0 && cards.length > 0) {
                noSearchState.classList.remove('hidden');
            } else {
                noSearchState.classList.add('hidden');
            }
        }

        // Pagination controls update
        const infoSpan = document.getElementById('recipes_pagination_info');
        const pageSpan = document.getElementById('page_number_display');
        const prevBtn = document.getElementById('prev_page_btn');
        const nextBtn = document.getElementById('next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay recetas para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} recetas`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentRecipePage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentRecipePage <= 1);
        if (nextBtn) nextBtn.disabled = (currentRecipePage >= totalPages);
    }

    function changeRecipePage(delta) {
        currentRecipePage += delta;
        renderRecipePage();
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
        renderRecipePage();
    });
</script>
@endsection
