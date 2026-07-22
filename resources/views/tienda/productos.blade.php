@extends('layouts.admin')

@section('title', 'Gestión de Productos e Inventario')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Inventario de Productos</h1>
            <p class="text-xs text-slate-400 mt-1">Configura el catálogo, costos, precios al público y niveles de stock de alerta.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="toggleModal('category-modal')" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Crear Categoría
            </button>
            <button onclick="toggleModal('product-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4"></i> Registrar Producto
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

    <!-- Stock Alerts Alert -->
    @php
        $lowStockProducts = $products->filter(fn($p) => $p->stock_quantity <= $p->min_stock);
    @endphp

    @if($lowStockProducts->count() > 0)
        <div class="p-4 bg-rose-500/10 border border-rose-500/25 text-rose-400 rounded-2xl text-xs flex gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-rose-400"></i>
            <div>
                <strong class="font-bold block mb-1">¡Alerta de Stock Crítico!</strong>
                <span>Los siguientes productos tienen existencias iguales o inferiores al mínimo configurado:</span>
                <ul class="list-disc list-inside mt-2 font-mono">
                    @foreach($lowStockProducts as $lowProduct)
                        <li>{{ $lowProduct->name }} (Stock: <strong class="text-slate-100">{{ $lowProduct->stock_quantity }}</strong> / Mínimo: {{ $lowProduct->min_stock }})</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Products Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <h3 class="font-bold text-lg text-slate-100">Listado de Artículos</h3>
                
                <!-- Status Filter Tabs (Todos | Activos | Inhabilitados) -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setProductStatusFilter('all')" id="p-filter-btn-all" class="p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-all-products">{{ $products->count() }}</span>)
                    </button>
                    <button type="button" onclick="setProductStatusFilter('active')" id="p-filter-btn-active" class="p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Activos (<span id="count-active-products">{{ $products->where('is_available', 1)->count() }}</span>)
                    </button>
                    <button type="button" onclick="setProductStatusFilter('disabled')" id="p-filter-btn-disabled" class="p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Inhabilitados (<span id="count-disabled-products">{{ $products->where('is_available', 0)->count() }}</span>)
                    </button>
                </div>
            </div>

            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="product_search_input" oninput="onProductSearchInput()" placeholder="Buscar por producto o categoría..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6 text-left">Producto</th>
                        <th class="p-4 text-center">Categoría</th>
                        <th class="p-4 text-center">Costo Unitario</th>
                        <th class="p-4 text-center">Precio Venta</th>
                        <th class="p-4 text-center">Margen Ganancia</th>
                        <th class="p-4 text-center">Stock Actual</th>
                        <th class="p-4 text-center pr-6">Acciones</th>
                    </tr>
                </thead>
                <tbody id="products_table_body" class="divide-y divide-slate-850/50">
                    @forelse($products as $p)
                        @php
                            $profit = $p->price - $p->cost_price;
                            $marginPct = $p->price > 0 ? ($profit / $p->price) * 100 : 0;
                            $isLow = $p->stock_quantity <= $p->min_stock;
                        @endphp
                        <tr id="product_row_{{ $p->id }}"
                            data-product-row 
                            data-is-available="{{ $p->is_available ? '1' : '0' }}"
                            data-name="{{ strtolower($p->name) }}" 
                            data-category="{{ strtolower($p->category->name ?? '') }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $p->image_url ? asset($p->image_url) : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=150&auto=format&fit=crop' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                    <div>
                                        <div class="flex items-center gap-2" id="product_title_badge_{{ $p->id }}">
                                            <span class="block font-bold text-slate-100">{{ $p->name }}</span>
                                            @if(!$p->is_available)
                                                <span class="px-1.5 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-[9px] font-bold uppercase tracking-wider disabled-tag">x</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 bg-slate-950/80 text-slate-300 border border-slate-850 rounded-lg font-semibold text-[10px]">
                                    {{ $p->category->name }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-mono text-slate-300">${{ number_format($p->cost_price, 2) }}</td>
                            <td class="p-4 text-center font-mono text-lime-400 font-bold">${{ number_format($p->price, 2) }}</td>
                            <td class="p-4 text-center font-mono text-emerald-400 font-semibold">
                                +${{ number_format($profit, 2) }} <span class="text-[10px] text-slate-500">({{ number_format($marginPct, 0) }}%)</span>
                            </td>
                            <td class="p-4 text-center font-mono" id="product_stock_cell_{{ $p->id }}">
                                <span class="px-2.5 py-1 rounded-lg font-extrabold text-xs inline-block {{ $isLow ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : 'bg-slate-950/80 text-slate-200 border border-slate-850' }}">
                                    {{ $p->stock_quantity }}
                                </span>
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-2" id="product_actions_{{ $p->id }}">
                                    <!-- Stock Button (Green) -->
                                    <button onclick="openRestockModal({{ $p->id }}, '{{ addslashes($p->name) }}')" class="px-2.5 py-1.5 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl text-[10px] font-extrabold transition-all flex items-center gap-1 shadow-sm" title="Reabastecer Stock">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                        +Stock
                                    </button>

                                    <!-- Edit Button (Yellow/Amber) -->
                                    <button onclick="openEditModal({{ json_encode($p) }})" class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Producto">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Toggle Availability (Inhabilitar / Habilitar) -->
                                    <div id="product_toggle_btn_container_{{ $p->id }}" class="inline-block">
                                        @if($p->is_available)
                                            <button type="button" onclick="confirmToggleProductStatus({{ $p->id }}, '{{ addslashes($p->name) }}', true)" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Producto">
                                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @else
                                            <button type="button" onclick="confirmToggleProductStatus({{ $p->id }}, '{{ addslashes($p->name) }}', false)" class="p-1.5 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm" title="Habilitar Producto">
                                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-550">
                                No hay productos registrados en el inventario.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_products_search_row" class="hidden">
                        <td colspan="7" class="p-10 text-center text-slate-500">
                            <i data-lucide="package-search" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron productos que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div id="product_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="product_pagination_info">Mostrando productos...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="prev_page_btn" onclick="changeProductPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="next_page_btn" onclick="changeProductPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR CATEGORÍA ================= -->
<div id="category-modal" class="fixed inset-0 z-50 bg-slate-950/80 flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Crear Categoría de Producto</h3>
            <button onclick="toggleModal('category-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-category-form" action="{{ route('tienda.store_category') }}" method="POST" onsubmit="submitCreateCategory(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre de la Categoría</label>
                <input type="text" name="name" required placeholder="Ej: Bebidas Hidratantes" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción (Opcional)</label>
                <textarea name="description" placeholder="Suplementos deportivos, shakers, bebidas energéticas..." rows="2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('category-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="create-category-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR PRODUCTO ================= -->
<div id="product-modal" class="fixed inset-0 z-50 bg-slate-950/80 flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Registrar Producto de Venta</h3>
            <button onclick="toggleModal('product-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-product-form" action="{{ route('tienda.store_product') }}" method="POST" enctype="multipart/form-data" onsubmit="submitCreateProduct(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría</label>
                <select name="category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>Selecciona una categoría...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Producto</label>
                <input type="text" name="name" required placeholder="Ej: Proteína Whey 1kg (Fresa)" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción (Opcional)</label>
                <input type="text" name="description" placeholder="Añade una descripción breve del artículo" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Costo Unitario ($)</label>
                    <input type="number" step="0.01" name="cost_price" required placeholder="15.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Precio de Venta ($)</label>
                    <input type="number" step="0.01" name="price" required placeholder="25.00" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Stock Inicial</label>
                    <input type="number" name="stock_quantity" required min="0" placeholder="10" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Stock Alerta (Mín)</label>
                    <input type="number" name="min_stock" required min="0" placeholder="3" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Foto del Producto (Opcional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-455 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('product-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="create-product-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR PRODUCTO ================= -->
<div id="edit-product-modal" class="fixed inset-0 z-50 bg-slate-950/80 flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Producto</h3>
            <button onclick="toggleModal('edit-product-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" onsubmit="submitEditProduct(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Categoría</label>
                <select name="category_id" id="edit-category_id" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Producto</label>
                <input type="text" name="name" id="edit-name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Descripción (Opcional)</label>
                <input type="text" name="description" id="edit-description" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Costo Unitario ($)</label>
                    <input type="number" step="0.01" name="cost_price" id="edit-cost_price" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Precio de Venta ($)</label>
                    <input type="number" step="0.01" name="price" id="edit-price" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Stock Alerta (Mín)</label>
                <input type="number" name="min_stock" id="edit-min_stock" required min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Actualizar Foto del Producto (Opcional)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-855 rounded-xl text-slate-455 focus:outline-none focus:border-lime-500/50 cursor-pointer">
            </div>
            <div class="flex items-center gap-2 pt-1 hidden" id="edit-current-image-container">
                <input type="checkbox" name="remove_image" id="edit-remove-image" value="1" class="rounded border-slate-855 bg-slate-950 text-lime-500 focus:ring-lime-500 cursor-pointer">
                <label for="edit-remove-image" class="text-xs text-rose-400 font-medium cursor-pointer">Eliminar foto actual</label>
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('edit-product-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-855 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="edit-product-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: AÑADIR STOCK (REABASTECER) ================= -->
<div id="restock-modal" class="fixed inset-0 z-50 bg-slate-950/80 flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Reabastecer Producto</h3>
            <button onclick="toggleModal('restock-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="restock-form" method="POST" onsubmit="submitRestockProduct(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1">Producto</label>
                <span class="block text-sm font-semibold text-slate-200 bg-slate-950 px-4 py-2.5 rounded-xl border border-slate-850" id="restock-product-name"></span>
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Cantidad a agregar</label>
                <input type="number" name="quantity" required min="1" placeholder="Ej: 10" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-slate-400 uppercase tracking-wider mb-1.5">Motivo / Notas</label>
                <input type="text" name="reason" placeholder="Ej: Pedido proveedor oficial" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('restock-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="restock-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Stock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: CONFIRMAR CAMBIO DE ESTADO (HABILITAR / INHABILITAR) ================= -->
<div id="toggle-status-modal" class="fixed inset-0 z-50 bg-slate-950/80 flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-5 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2.5" id="toggle-status-modal-title">
                <i id="toggle-status-modal-icon" data-lucide="power" class="w-5 h-5 text-rose-400"></i>
                <span id="toggle-status-title-text">Confirmar Acción</span>
            </h3>
            <button type="button" onclick="toggleModal('toggle-status-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="space-y-3">
            <p class="text-xs text-slate-300 leading-relaxed" id="toggle-status-modal-desc">
                ¿Estás seguro de que deseas cambiar el estado de este producto?
            </p>
            <div class="p-3 bg-slate-950/60 border border-slate-850 rounded-xl flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-lime-400" id="toggle-status-indicator"></div>
                <span class="text-xs font-bold text-slate-200" id="toggle-status-product-name"></span>
            </div>
        </div>
        <form id="toggle-status-form" action="" method="POST" onsubmit="submitToggleProductStatus(event)" class="pt-2">
            @csrf
            @method('DELETE')
            <div class="flex gap-3">
                <button type="button" onclick="toggleModal('toggle-status-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="toggle-status-submit-btn" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs rounded-xl shadow-lg transition-all">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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

    function openRestockModal(productId, name) {
        document.getElementById('restock-product-name').innerText = name;
        
        // Dynamic route injection
        const form = document.getElementById('restock-form');
        form.action = `/tienda/productos/${productId}/stock`;
        
        toggleModal('restock-modal');
    }

    function openEditModal(product) {
        document.getElementById('edit-form').action = `/tienda/productos/${product.id}`;
        document.getElementById('edit-category_id').value = product.category_id;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-description').value = product.description || '';
        document.getElementById('edit-cost_price').value = product.cost_price;
        document.getElementById('edit-price').value = product.price;
        document.getElementById('edit-min_stock').value = product.min_stock;
        
        // Show/hide remove photo checkbox
        const currentImgContainer = document.getElementById('edit-current-image-container');
        const removeImgCheck = document.getElementById('edit-remove-image');
        if (product.image_url) {
            currentImgContainer.classList.remove('hidden');
        } else {
            currentImgContainer.classList.add('hidden');
        }
        removeImgCheck.checked = false;
        
        toggleModal('edit-product-modal');
    }

    function confirmToggleProductStatus(productId, productName, isAvailable) {
        const form = document.getElementById('toggle-status-form');
        const titleText = document.getElementById('toggle-status-title-text');
        const modalIcon = document.getElementById('toggle-status-modal-icon');
        const desc = document.getElementById('toggle-status-modal-desc');
        const prodNameSpan = document.getElementById('toggle-status-product-name');
        const submitBtn = document.getElementById('toggle-status-submit-btn');
        const indicator = document.getElementById('toggle-status-indicator');

        form.action = `/tienda/productos/${productId}`;
        prodNameSpan.textContent = productName;

        if (isAvailable) {
            titleText.textContent = 'Inhabilitar Producto';
            modalIcon.setAttribute('data-lucide', 'power');
            modalIcon.className = 'w-5 h-5 text-rose-400';
            desc.textContent = '¿Estás seguro de que deseas inhabilitar este producto? Dejará de aparecer en la terminal de ventas (POS), pero toda su información e historial de ventas se conservarán intactos.';
            submitBtn.textContent = 'Sí, Inhabilitar';
            submitBtn.className = 'flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs rounded-xl shadow-lg transition-all';
            indicator.className = 'w-2 h-2 rounded-full bg-rose-400';
        } else {
            titleText.textContent = 'Habilitar Producto';
            modalIcon.setAttribute('data-lucide', 'check-circle');
            modalIcon.className = 'w-5 h-5 text-emerald-400';
            desc.textContent = '¿Estás seguro de que deseas habilitar este producto? Volverá a estar disponible para la venta en la terminal POS.';
            submitBtn.textContent = 'Sí, Habilitar';
            submitBtn.className = 'flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all';
            indicator.className = 'w-2 h-2 rounded-full bg-emerald-400';
        }

        if (window.lucide) window.lucide.createIcons();
        toggleModal('toggle-status-modal');
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

    async function submitCreateCategory(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-category-submit-btn');

        setBtnLoading(submitBtn, true, 'Guardando categoría...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Dynamically append new option to category select dropdowns
                const categorySelects = document.querySelectorAll('select[name="category_id"]');
                categorySelects.forEach(select => {
                    const option = document.createElement('option');
                    option.value = data.category.id;
                    option.textContent = data.category.name;
                    option.selected = true;
                    select.appendChild(option);
                });

                // Reset form
                form.reset();

                // Close modal
                toggleModal('category-modal');

                // Show auto-dismissing toast notification
                showProductToast(data.message, 'success');
            } else {
                const errMsg = data.message || 'Error al validar los datos de la categoría.';
                showProductToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showProductToast('Ocurrió un error al intentar crear la categoría.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitCreateProduct(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('create-product-submit-btn');

        setBtnLoading(submitBtn, true, 'Registrando producto...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const p = data.product;
                const profit = p.price - p.cost_price;
                const marginPct = p.price > 0 ? (profit / p.price) * 100 : 0;
                const isLow = p.stock_quantity <= p.min_stock;
                const imgUrl = p.image_url ? `/${p.image_url}` : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=150&auto=format&fit=crop';
                const catName = p.category ? p.category.name : '';

                // Prepend new row to table
                const tbody = document.getElementById('products_table_body');
                if (tbody) {
                    const tr = document.createElement('tr');
                    tr.id = `product_row_${p.id}`;
                    tr.setAttribute('data-product-row', '');
                    tr.setAttribute('data-is-available', p.is_available ? '1' : '0');
                    tr.setAttribute('data-name', (p.name || '').toLowerCase());
                    tr.setAttribute('data-category', (catName || '').toLowerCase());
                    tr.className = 'hover:bg-slate-900/20 text-slate-200 transition-colors';

                    const safeName = (p.name || '').replace(/'/g, "\\'");

                    tr.innerHTML = `
                        <td class="p-4 pl-6">
                            <div class="flex items-center gap-3">
                                <img src="${imgUrl}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <div class="flex items-center gap-2" id="product_title_badge_${p.id}">
                                        <span class="block font-bold text-slate-100">${p.name}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 bg-slate-950/80 text-slate-300 border border-slate-850 rounded-lg font-semibold text-[10px]">
                                ${catName}
                            </span>
                        </td>
                        <td class="p-4 text-center font-mono text-slate-300">$${parseFloat(p.cost_price).toFixed(2)}</td>
                        <td class="p-4 text-center font-mono text-lime-400 font-bold">$${parseFloat(p.price).toFixed(2)}</td>
                        <td class="p-4 text-center font-mono text-emerald-400 font-semibold">
                            +$${profit.toFixed(2)} <span class="text-[10px] text-slate-500">(${marginPct.toFixed(0)}%)</span>
                        </td>
                        <td class="p-4 text-center font-mono">
                            <span class="px-2.5 py-1 rounded-lg font-extrabold text-xs inline-block ${isLow ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : 'bg-slate-950/80 text-slate-200 border border-slate-850'}">
                                ${p.stock_quantity}
                            </span>
                        </td>
                        <td class="p-4 text-center pr-6">
                            <div class="flex items-center justify-center gap-2" id="product_actions_${p.id}">
                                <button onclick="openRestockModal(${p.id}, '${safeName}')" class="px-2.5 py-1.5 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl text-[10px] font-extrabold transition-all flex items-center gap-1 shadow-sm" title="Reabastecer Stock">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                    +Stock
                                </button>
                                <button onclick='openEditModal(${JSON.stringify(p)})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Producto">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <div id="product_toggle_btn_container_${p.id}" class="inline-block">
                                    <button type="button" onclick="confirmToggleProductStatus(${p.id}, '${safeName}', true)" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Producto">
                                        <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                    `;

                    tbody.prepend(tr);
                }

                // Reset form
                form.reset();

                // Close modal
                toggleModal('product-modal');

                // Update counters and page
                updateTabCounters();
                renderProductPage();

                // Toast notification
                showProductToast(data.message, 'success');
            } else {
                const errMsg = data.message || 'Error al registrar el producto.';
                showProductToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showProductToast('Ocurrió un error al intentar registrar el producto.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitRestockProduct(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('restock-submit-btn');

        setBtnLoading(submitBtn, true, 'Reabasteciendo...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const productId = data.product_id;
                const newStock = data.new_stock;
                const minStock = data.min_stock;
                const isLow = newStock <= minStock;

                // Update stock cell in table dynamically
                const stockCell = document.getElementById(`product_stock_cell_${productId}`);
                if (stockCell) {
                    const badgeClass = isLow 
                        ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' 
                        : 'bg-slate-950/80 text-slate-200 border border-slate-850';

                    stockCell.innerHTML = `
                        <span class="px-2.5 py-1 rounded-lg font-extrabold text-xs inline-block ${badgeClass}">
                            ${newStock}
                        </span>
                    `;
                }

                // Reset form
                form.reset();

                // Close modal
                toggleModal('restock-modal');

                // Toast notification
                showProductToast(data.message, 'success');
            } else {
                const errMsg = data.message || 'Error al reabastecer stock.';
                showProductToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showProductToast('Ocurrió un error al intentar reabastecer stock.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitEditProduct(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('edit-product-submit-btn');

        setBtnLoading(submitBtn, true, 'Guardando cambios...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const p = data.product;
                const profit = p.price - p.cost_price;
                const marginPct = p.price > 0 ? (profit / p.price) * 100 : 0;
                const isLow = p.stock_quantity <= p.min_stock;
                const imgUrl = p.image_url ? `/${p.image_url}` : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=150&auto=format&fit=crop';
                const catName = p.category ? p.category.name : '';
                const safeName = (p.name || '').replace(/'/g, "\\'");

                // Update existing row HTML in DOM
                const tr = document.getElementById(`product_row_${p.id}`);
                if (tr) {
                    tr.setAttribute('data-name', (p.name || '').toLowerCase());
                    tr.setAttribute('data-category', (catName || '').toLowerCase());

                    tr.innerHTML = `
                        <td class="p-4 pl-6">
                            <div class="flex items-center gap-3">
                                <img src="${imgUrl}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <div class="flex items-center gap-2" id="product_title_badge_${p.id}">
                                        <span class="block font-bold text-slate-100">${p.name}</span>
                                        ${!p.is_available ? '<span class="px-1.5 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-[9px] font-bold uppercase tracking-wider disabled-tag">Inhabilitado</span>' : ''}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 bg-slate-950/80 text-slate-300 border border-slate-850 rounded-lg font-semibold text-[10px]">
                                ${catName}
                            </span>
                        </td>
                        <td class="p-4 text-center font-mono text-slate-300">$${parseFloat(p.cost_price).toFixed(2)}</td>
                        <td class="p-4 text-center font-mono text-lime-400 font-bold">$${parseFloat(p.price).toFixed(2)}</td>
                        <td class="p-4 text-center font-mono text-emerald-400 font-semibold">
                            +$${profit.toFixed(2)} <span class="text-[10px] text-slate-500">(${marginPct.toFixed(0)}%)</span>
                        </td>
                        <td class="p-4 text-center font-mono" id="product_stock_cell_${p.id}">
                            <span class="px-2.5 py-1 rounded-lg font-extrabold text-xs inline-block ${isLow ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : 'bg-slate-950/80 text-slate-200 border border-slate-850'}">
                                ${p.stock_quantity}
                            </span>
                        </td>
                        <td class="p-4 text-center pr-6">
                            <div class="flex items-center justify-center gap-2" id="product_actions_${p.id}">
                                <button onclick="openRestockModal(${p.id}, '${safeName}')" class="px-2.5 py-1.5 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl text-[10px] font-extrabold transition-all flex items-center gap-1 shadow-sm" title="Reabastecer Stock">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                    +Stock
                                </button>
                                <button onclick='openEditModal(${JSON.stringify(p)})' class="p-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-950 border border-amber-500/25 rounded-xl transition-all shadow-sm" title="Editar Producto">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <div id="product_toggle_btn_container_${p.id}" class="inline-block">
                                    ${p.is_available ? `
                                        <button type="button" onclick="confirmToggleProductStatus(${p.id}, '${safeName}', true)" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Producto">
                                            <i data-lucide="power" class="w-3.5 h-3.5"></i>
                                        </button>
                                    ` : `
                                        <button type="button" onclick="confirmToggleProductStatus(${p.id}, '${safeName}', false)" class="p-1.5 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm" title="Habilitar Producto">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        </button>
                                    `}
                                </div>
                            </div>
                        </td>
                    `;
                }

                // Reset form
                form.reset();

                // Close modal
                toggleModal('edit-product-modal');

                // Update counters & page
                updateTabCounters();
                renderProductPage();

                // Toast notification
                showProductToast(data.message, 'success');
            } else {
                const errMsg = data.message || 'Error al actualizar el producto.';
                showProductToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showProductToast('Ocurrió un error al intentar actualizar el producto.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    async function submitToggleProductStatus(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('toggle-status-submit-btn');
        const isDisabling = submitBtn ? submitBtn.textContent.includes('Inhabilitar') : false;

        setBtnLoading(submitBtn, true, isDisabling ? 'Inhabilitando...' : 'Habilitando...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const productId = data.product_id;
                const isAvailable = data.is_available === 1;

                // Update Row data attribute
                const row = document.getElementById(`product_row_${productId}`);
                if (row) {
                    row.setAttribute('data-is-available', isAvailable ? '1' : '0');
                }

                // Update Badge next to title
                const badgeContainer = document.getElementById(`product_title_badge_${productId}`);
                if (badgeContainer) {
                    const existingBadge = badgeContainer.querySelector('.disabled-tag');
                    if (!isAvailable && !existingBadge) {
                        const badge = document.createElement('span');
                        badge.className = 'px-1.5 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded text-[9px] font-bold uppercase tracking-wider disabled-tag';
                        badge.textContent = 'Inhabilitado';
                        badgeContainer.appendChild(badge);
                    } else if (isAvailable && existingBadge) {
                        existingBadge.remove();
                    }
                }

                // Update Toggle Button Container Only (Preserving Edit & +Stock buttons!)
                const toggleContainer = document.getElementById(`product_toggle_btn_container_${productId}`);
                if (toggleContainer) {
                    const productName = row ? (row.getAttribute('data-name') || 'Producto') : 'Producto';
                    
                    if (isAvailable) {
                        toggleContainer.innerHTML = `
                            <button type="button" onclick="confirmToggleProductStatus(${productId}, '${productName}', true)" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-100 border border-rose-500/25 rounded-xl transition-all shadow-sm" title="Inhabilitar Producto">
                                <i data-lucide="power" class="w-3.5 h-3.5"></i>
                            </button>
                        `;
                    } else {
                        toggleContainer.innerHTML = `
                            <button type="button" onclick="confirmToggleProductStatus(${productId}, '${productName}', false)" class="p-1.5 bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-slate-950 border border-emerald-500/25 rounded-xl transition-all shadow-sm" title="Habilitar Producto">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            </button>
                        `;
                    }
                }

                updateTabCounters();
                renderProductPage();
                showProductToast(data.message, isAvailable ? 'success' : 'warning');
                toggleModal('toggle-status-modal');
            } else {
                showProductToast('Error al procesar la solicitud', 'error');
            }
        } catch (err) {
            console.error(err);
            showProductToast('Ocurrió un error al intentar cambiar el estado.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    function updateTabCounters() {
        const allRows = document.querySelectorAll('[data-product-row]');
        let activeCount = 0;
        let disabledCount = 0;

        allRows.forEach(row => {
            if (row.getAttribute('data-is-available') === '1') {
                activeCount++;
            } else {
                disabledCount++;
            }
        });

        const cAll = document.getElementById('count-all-products');
        const cActive = document.getElementById('count-active-products');
        const cDisabled = document.getElementById('count-disabled-products');

        if (cAll) cAll.textContent = allRows.length;
        if (cActive) cActive.textContent = activeCount;
        if (cDisabled) cDisabled.textContent = disabledCount;
    }

    function showProductToast(message, type = 'success') {
        let container = document.getElementById('product-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'product-toast-container';
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
            <div class="flex-1 leading-tight">${message}</div>
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
        }, 3500);
    }

    // Pagination, Status Filtering & Live Search Logic (Max 10 products per page)
    let currentProductPage = 1;
    let currentProductStatusFilter = 'all';
    const itemsPerPage = 10;

    function setProductStatusFilter(status) {
        currentProductStatusFilter = status;

        const tabs = document.querySelectorAll('.p-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('p-filter-btn-' + status);
        if (activeTab) {
            activeTab.className = "p-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentProductPage = 1;
        renderProductPage();
    }

    function getMatchingProductRows() {
        const query = (document.getElementById('product_search_input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-product-row]'));

        return rows.filter(row => {
            const isAvailable = row.getAttribute('data-is-available') === '1';
            const name = row.getAttribute('data-name') || '';
            const cat = row.getAttribute('data-category') || '';

            let matchesStatus = true;
            if (currentProductStatusFilter === 'active') {
                matchesStatus = isAvailable;
            } else if (currentProductStatusFilter === 'disabled') {
                matchesStatus = !isAvailable;
            }

            let matchesSearch = true;
            if (query) {
                matchesSearch = name.includes(query) || cat.includes(query);
            }

            return matchesStatus && matchesSearch;
        });
    }

    function renderProductPage() {
        const allRows = Array.from(document.querySelectorAll('[data-product-row]'));
        const matchingRows = getMatchingProductRows();
        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / itemsPerPage) || 1;

        if (currentProductPage > totalPages) currentProductPage = totalPages;
        if (currentProductPage < 1) currentProductPage = 1;

        // Hide all product rows
        allRows.forEach(row => row.classList.add('hidden'));

        // Show slice for current page
        const startIndex = (currentProductPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageRows = matchingRows.slice(startIndex, endIndex);

        pageRows.forEach(row => row.classList.remove('hidden'));

        // Show/hide empty search state row
        const emptySearchRow = document.getElementById('no_products_search_row');
        if (emptySearchRow) {
            if (totalMatching === 0 && allRows.length > 0) {
                emptySearchRow.classList.remove('hidden');
            } else {
                emptySearchRow.classList.add('hidden');
            }
        }

        // Update info & buttons
        const infoSpan = document.getElementById('product_pagination_info');
        if (infoSpan) {
            const from = totalMatching === 0 ? 0 : startIndex + 1;
            const to = Math.min(endIndex, totalMatching);
            infoSpan.textContent = `Mostrando ${from} a ${to} de ${totalMatching} productos`;
        }

        const pageDisplay = document.getElementById('page_number_display');
        if (pageDisplay) {
            pageDisplay.textContent = `Página ${currentProductPage} de ${totalPages}`;
        }

        const prevBtn = document.getElementById('prev_page_btn');
        if (prevBtn) prevBtn.disabled = (currentProductPage <= 1);

        const nextBtn = document.getElementById('next_page_btn');
        if (nextBtn) nextBtn.disabled = (currentProductPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function onProductSearchInput() {
        currentProductPage = 1;
        renderProductPage();
    }

    function changeProductPage(delta) {
        currentProductPage += delta;
        renderProductPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderProductPage();
    });
</script>
@endsection
