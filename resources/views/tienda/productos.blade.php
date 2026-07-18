@extends('layouts.admin')

@section('title', 'Gestión de Productos e Inventario')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Inventario de Productos</h1>
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
                        <li>{{ $lowProduct->name }} (Stock: <strong class="text-white">{{ $lowProduct->stock_quantity }}</strong> / Mínimo: {{ $lowProduct->min_stock }})</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Products Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-white">Listado de Artículos</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Producto</th>
                        <th class="p-4">Categoría</th>
                        <th class="p-4 text-right">Costo unitario</th>
                        <th class="p-4 text-right">Precio venta</th>
                        <th class="p-4 text-right">Margen ganancia</th>
                        <th class="p-4 text-center">Stock actual</th>
                        <th class="p-4 text-right pr-6 w-44">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($products as $p)
                        @php
                            $profit = $p->price - $p->cost_price;
                            $marginPct = $p->price > 0 ? ($profit / $p->price) * 100 : 0;
                            $isLow = $p->stock_quantity <= $p->min_stock;
                        @endphp
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img src="{{ $p->image_url ? asset($p->image_url) : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=150&auto=format&fit=crop' }}" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shrink-0">
                                <div>
                                    <span class="block font-bold text-slate-100">{{ $p->name }}</span>
                                    <span class="block text-[10px] text-slate-550 truncate max-w-xs">{{ $p->description ?? 'Sin descripción.' }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 bg-slate-950/60 text-slate-400 border border-slate-850/50 rounded-md font-semibold text-[10px]">
                                    {{ $p->category->name }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-mono text-slate-300">${{ number_format($p->cost_price, 2) }}</td>
                            <td class="p-4 text-right font-mono text-lime-400 font-bold">${{ number_format($p->price, 2) }}</td>
                            <td class="p-4 text-right font-mono text-emerald-400 font-semibold">
                                +${{ number_format($profit, 2) }} <span class="text-[10px] text-slate-550">({{ number_format($marginPct, 0) }}%)</span>
                            </td>
                            <td class="p-4 text-center font-mono">
                                <span class="px-2 py-0.5 rounded-md font-bold {{ $isLow ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-slate-950/50 text-slate-200' }}">
                                    {{ $p->stock_quantity }}
                                </span>
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2.5">
                                    <button onclick="openRestockModal({{ $p->id }}, '{{ $p->name }}')" class="px-2 py-1 bg-slate-950 hover:bg-slate-850 border border-slate-800 rounded-lg text-[9px] font-bold text-slate-300 hover:text-white transition-colors uppercase">
                                        + Stock
                                    </button>
                                    <button 
                                        onclick="openEditModal({{ json_encode($p) }})" 
                                        class="p-1 text-lime-450 hover:text-lime-350 transition-colors"
                                        title="Editar">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('tienda.delete_product', $p->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
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
                            <td colspan="7" class="p-8 text-center text-slate-550">
                                No hay productos registrados en el inventario.
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
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Crear Categoría de Producto</h3>
            <button onclick="toggleModal('category-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('tienda.store_category') }}" method="POST" class="space-y-4 text-xs font-semibold">
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
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTRAR PRODUCTO ================= -->
<div id="product-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Registrar Producto de Venta</h3>
            <button onclick="toggleModal('product-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('tienda.store_product') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
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
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR PRODUCTO ================= -->
<div id="edit-product-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6 animate-scale-up">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Editar Producto</h3>
            <button onclick="toggleModal('edit-product-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
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
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: AÑADIR STOCK (REABASTECER) ================= -->
<div id="restock-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-white">Reabastecer Producto</h3>
            <button onclick="toggleModal('restock-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="restock-form" method="POST" class="space-y-4 text-xs font-semibold">
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
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Stock
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
</script>
@endsection
