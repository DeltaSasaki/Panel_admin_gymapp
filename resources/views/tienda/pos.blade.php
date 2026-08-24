@extends('layouts.admin')

@section('title', 'Punto de Venta (POS)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left Column: Products Grid (2/3 width) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Header Title & Subtitle -->
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Punto de Venta (POS)</h1>
            <p class="text-xs text-slate-400 mt-1">Registra la venta rápida de suplementos y productos con filtrado rápido por categoría.</p>
        </div>

        <!-- Cashier Category & Search Quick Filter Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <!-- Category Filter Dropdown -->
            <div class="flex items-center gap-2 flex-1 sm:max-w-xs">
                <div class="p-2 rounded-xl bg-slate-950 border border-slate-850 text-lime-400 shrink-0">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </div>
                <select id="category-select-filter" onchange="filterByCategorySelect(this.value)" class="w-full px-3.5 py-2.5 text-xs font-bold bg-slate-950 border border-slate-850 rounded-xl text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all">Todas las Categorías ({{ $products->count() }})</option>
                    @foreach($categories as $cat)
                        @php
                            $catCount = $products->where('category_id', $cat->id)->count();
                        @endphp
                        @if($catCount > 0)
                            <option value="{{ strtolower($cat->name) }}">{{ $cat->name }} ({{ $catCount }})</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Search Bar & Barcode Scanner Button -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" onclick="openPosBarcodeScannerModal()" class="px-3 py-2.5 bg-slate-950 border border-lime-500/40 hover:bg-lime-500/10 text-lime-400 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shrink-0">
                    <i data-lucide="barcode" class="w-4 h-4 text-lime-400"></i>
                    <span class="hidden sm:inline">Escanear Código</span>
                </button>
                <div class="relative w-full sm:w-56 shrink-0">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                    <input type="text" id="search-input" onkeyup="filterProducts()" placeholder="Buscar por nombre..." class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-xs flex gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <span class="block">{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Products Catalog Grid (Max 9 per page) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="products-container">
            @forelse($products as $product)
                <div class="product-card bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between hover:border-lime-500/40 transition-colors cursor-pointer select-none active:scale-[0.98]" 
                     style="{{ $loop->index >= 9 ? 'display: none;' : '' }}"
                     data-id="{{ $product->id }}" 
                     data-name="{{ $product->name }}" 
                     data-category="{{ $product->category->name ?? '' }}"
                     data-price="{{ $product->price }}" 
                     data-stock="{{ $product->stock_quantity }}"
                     onclick="addToCart(this)">
                    <div>
                        <!-- Category Badge & Stock Indicator -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[10px] uppercase font-extrabold text-slate-400 px-2 py-0.5 bg-slate-950 rounded-md border border-slate-800 truncate max-w-[65%] shrink" title="{{ $product->category->name ?? 'Producto' }}">
                                {{ $product->category->name ?? 'Producto' }}
                            </span>
                            <span class="text-xs font-bold text-lime-400 shrink-0 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-lime-400"></span>
                                Stock: {{ $product->stock_quantity }}
                            </span>
                        </div>
                        
                        <!-- Title with Even Height -->
                        <h3 class="font-bold text-slate-100 text-sm leading-snug line-clamp-2 min-h-[40px]" title="{{ $product->name }}">
                            {{ $product->name }}
                        </h3>
                        
                        <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">{{ $product->description ?? 'Sin descripción.' }}</p>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-800/80 pt-3 mt-3">
                        <div>
                            <span class="block text-[9px] text-slate-500 uppercase tracking-wider font-semibold">Precio</span>
                            <div class="flex items-baseline gap-1.5">
                                <span class="font-black text-lime-400 text-base">${{ number_format($product->price, 2) }}</span>
                                <span class="text-[11px] font-bold text-slate-400 font-mono">/ {{ \App\Services\ExchangeRateService::formatVES($product->price * ($currentRate ?? 1)) }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-lg text-xs font-extrabold flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Agregar
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-550">
                    <i data-lucide="package-x" class="w-12 h-12 text-slate-700 mx-auto mb-2"></i>
                    No hay productos en inventario con stock disponible actualmente.
                </div>
            @endforelse

            <div id="no_pos_results_msg" class="col-span-full py-12 text-center text-slate-500 hidden">
                <i data-lucide="search-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                <p>No se encontraron productos que coincidan con el filtro o la búsqueda.</p>
            </div>
        </div>

        <!-- POS Interactive Grid Pagination Controls (Max 9 per page) -->
        <div id="pos_pagination_container" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl text-xs font-medium text-slate-400 mt-4">
            <span id="pos_pagination_info">Mostrando productos...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="pos_prev_btn" onclick="changePosGridPage(-1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    Anterior
                </button>
                <span id="pos_page_display" class="px-3.5 py-1.5 bg-slate-950 rounded-xl font-bold text-lime-400 border border-slate-850">Página 1</span>
                <button type="button" id="pos_next_btn" onclick="changePosGridPage(1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    Siguiente
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column: Shopping Cart (1/3 width) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 flex flex-col min-h-[calc(100vh-120px)] sticky top-24">
        <h3 class="font-bold text-lg text-slate-100 mb-3 flex items-center justify-between pb-3 border-b border-slate-800 shrink-0">
            <span class="flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-lime-400"></i> Detalle de Venta
            </span>
        </h3>

        <!-- Cart Items List -->
        <div class="min-h-[150px] max-h-[260px] overflow-y-auto pr-1 space-y-2.5 my-2" id="cart-items-container">
            <!-- Empty Cart State -->
            <div class="h-full min-h-[130px] flex flex-col items-center justify-center text-slate-500 text-xs py-6" id="empty-cart-state">
                <i data-lucide="shopping-bag" class="w-9 h-9 text-slate-700 mb-2"></i>
                Haz clic en un producto para agregarlo al carrito.
            </div>
        </div>

        <!-- Checkout Form Details -->
        <form action="{{ route('tienda.register_sale') }}" method="POST" class="pt-4 border-t border-slate-800 mt-2 space-y-3 shrink-0" onsubmit="prepareSubmit(event)">
            @csrf
            <input type="hidden" name="cart" id="cart-json-input">

            <!-- Customer Association -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Asociar Socio (Opcional)</label>
                <select name="user_id" id="pos-user-id-select" class="w-full px-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="" data-email="">Cliente General (Sin asociar)</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" data-email="{{ $client->email }}">{{ $client->profile->first_name ?? 'Socio' }} {{ $client->profile->last_name ?? '' }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Método de Pago</label>
                <select name="payment_method" required class="w-full px-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="cash" selected>Efectivo</option>
                    <option value="card">Tarjeta de Débito/Crédito</option>
                    <option value="transfer">Transferencia Bancaria</option>
                </select>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Notas adicionales</label>
                <input type="text" name="notes" placeholder="Ej: Venta de shaker con tapa" class="w-full px-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-700 focus:outline-none focus:border-lime-500/50">
            </div>

            <!-- Promo Code -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Código Promocional (Opcional)</label>
                <div class="flex gap-2">
                    <input type="text" name="promo_code" id="pos_promo_code" placeholder="Ej: DESCUENTO10" class="flex-1 px-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 uppercase focus:outline-none focus:border-lime-500/50">
                    <button type="button" onclick="applyPosPromo()" class="px-3 bg-slate-800 hover:bg-slate-750 text-slate-200 hover:text-slate-100 text-xs font-bold rounded-xl border border-slate-750 transition-colors">
                        Aplicar
                    </button>
                </div>
                <span id="pos-promo-feedback" class="block text-[9px] font-bold mt-1.5 hidden"></span>
            </div>

            <!-- Total Price Calculation -->
            <div class="bg-slate-950/60 p-4 rounded-xl border border-slate-850 space-y-2">
                <div class="flex justify-between items-center text-xs text-slate-400">
                    <span>Artículos Totales:</span>
                    <span id="total-qty-badge">0</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-400">
                    <span>Subtotal:</span>
                    <span id="subtotal-amount-badge">$0.00</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-400 hidden" id="discount-row">
                    <span>Descuento (<span id="discount-code-badge"></span>):</span>
                    <span class="text-rose-450" id="discount-amount-badge">-$0.00</span>
                </div>
                <div class="flex justify-between items-baseline border-t border-slate-850/50 pt-2">
                    <span class="text-xs font-bold text-slate-100 uppercase">Monto Total:</span>
                    <div class="text-right">
                        <span class="text-lg font-black text-lime-400" id="total-amount-badge">$0.00</span>
                        <span class="text-xs font-extrabold text-emerald-400 font-mono block" id="total-amount-ves-badge">Bs. 0,00</span>
                    </div>
                </div>
            </div>

            <!-- Submit Checkout -->
            <button type="submit" id="checkout-submit-btn" disabled class="w-full py-3 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                Confirmar y Registrar Venta
            </button>
        </form>
    </div>
</div>

<script>
    const currentExchangeRate = {{ (float)($currentRate ?? 1) }};
    let cart = [];
    let lastCompletedSaleData = null;
    let lastCompletedSaleDetails = null;
    let currentPosPage = 1;
    const posPerPage = 9;
    let matchingPosCards = [];
    let selectedCategoryFilter = 'all';

    function filterByCategorySelect(catValue) {
        selectedCategoryFilter = (catValue || 'all').toLowerCase();
        filterProducts();
    }

    function filterProducts() {
        const query = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card');
        matchingPosCards = [];
        currentPosPage = 1;

        cards.forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const category = (card.getAttribute('data-category') || '').toLowerCase();
            
            const matchesCategory = (selectedCategoryFilter === 'all' || category === selectedCategoryFilter);
            const matchesQuery = (query.length === 0 || name.includes(query) || category.includes(query));

            if (matchesCategory && matchesQuery) {
                matchingPosCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        renderPosPaginatedGrid();
    }

    function renderPosPaginatedGrid() {
        const cards = document.querySelectorAll('.product-card');
        const totalMatching = matchingPosCards.length;
        const totalPages = Math.ceil(totalMatching / posPerPage) || 1;

        if (currentPosPage > totalPages) currentPosPage = totalPages;
        if (currentPosPage < 1) currentPosPage = 1;

        const startIndex = (currentPosPage - 1) * posPerPage;
        const endIndex = startIndex + posPerPage;

        // Hide all cards first
        cards.forEach(c => c.style.display = 'none');

        // Show current page slice
        const pageSlice = matchingPosCards.slice(startIndex, endIndex);
        pageSlice.forEach(c => c.style.display = 'flex');

        // Update UI controls
        const infoSpan = document.getElementById('pos_pagination_info');
        const pageSpan = document.getElementById('pos_page_display');
        const prevBtn = document.getElementById('pos_prev_btn');
        const nextBtn = document.getElementById('pos_next_btn');
        const emptyMsg = document.getElementById('no_pos_results_msg');
        const paginationContainer = document.getElementById('pos_pagination_container');

        if (emptyMsg) {
            if (totalMatching === 0 && cards.length > 0) {
                emptyMsg.classList.remove('hidden');
                if (paginationContainer) paginationContainer.classList.add('hidden');
            } else {
                emptyMsg.classList.add('hidden');
                if (paginationContainer) paginationContainer.classList.remove('hidden');
            }
        }

        if (infoSpan) {
            if (totalMatching === 0) {
                infoSpan.textContent = "No hay productos para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalMatching);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalMatching} productos`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentPosPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentPosPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentPosPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function changePosGridPage(delta) {
        currentPosPage += delta;
        renderPosPaginatedGrid();
    }

    function addToCart(card) {
        const id = parseInt(card.getAttribute('data-id'));
        const name = card.getAttribute('data-name');
        const price = parseFloat(card.getAttribute('data-price'));
        const maxStock = parseInt(card.getAttribute('data-stock'));

        // Check if already in cart
        const existing = cart.find(item => item.product_id === id);
        if (existing) {
            if (existing.quantity < maxStock) {
                existing.quantity++;
            } else {
                showPosToast(`Stock máximo alcanzado (${maxStock} unidades disponibles).`, 'warning');
                return;
            }
        } else {
            cart.push({
                product_id: id,
                name: name,
                price: price,
                quantity: 1,
                maxStock: maxStock
            });
        }

        renderCart();
    }

    function updateQuantity(id, delta) {
        const item = cart.find(item => item.product_id === id);
        if (!item) return;

        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(item => item.product_id !== id);
        } else if (item.quantity > item.maxStock) {
            item.quantity = item.maxStock;
            showPosToast(`Solo hay ${item.maxStock} unidades disponibles en inventario.`, 'warning');
        }

        renderCart();
    }

    let appliedPromo = null;

    async function applyPosPromo() {
        const codeInput = document.getElementById('pos_promo_code');
        const code = codeInput.value.trim().toUpperCase();
        const feedback = document.getElementById('pos-promo-feedback');

        if (cart.length === 0) {
            showPosToast('Agrega productos al carrito antes de aplicar una promoción.', 'warning');
            return;
        }

        if (!code) {
            feedback.className = "block text-[9px] font-bold mt-1.5 text-rose-450";
            feedback.innerText = "Ingresa un código.";
            feedback.classList.remove('hidden');
            return;
        }

        feedback.className = "block text-[9px] font-bold mt-1.5 text-slate-450";
        feedback.innerText = "Validando...";
        feedback.classList.remove('hidden');

        try {
            const response = await fetch(`/api/promos/validate?code=${encodeURIComponent(code)}`);
            const data = await response.json();

            if (data.valid) {
                appliedPromo = data;
                appliedPromo.code = code;
                feedback.className = "block text-[9px] font-bold mt-1.5 text-emerald-400";
                feedback.innerText = "¡Código de descuento aplicado con éxito!";
            } else {
                appliedPromo = null;
                feedback.className = "block text-[9px] font-bold mt-1.5 text-rose-400";
                feedback.innerText = data.message;
            }
            renderCart();
        } catch (e) {
            console.error(e);
            feedback.className = "block text-[9px] font-bold mt-1.5 text-rose-400";
            feedback.innerText = "Error al conectar con el servidor.";
        }
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('empty-cart-state');
        const totalQty = document.getElementById('total-qty-badge');
        const subtotalAmt = document.getElementById('subtotal-amount-badge');
        const discountRow = document.getElementById('discount-row');
        const discountCode = document.getElementById('discount-code-badge');
        const discountAmt = document.getElementById('discount-amount-badge');
        const totalAmt = document.getElementById('total-amount-badge');
        const btn = document.getElementById('checkout-submit-btn');

        if (cart.length === 0) {
            emptyState.style.display = 'flex';
            appliedPromo = null;
            document.getElementById('pos_promo_code').value = '';
            document.getElementById('pos-promo-feedback').classList.add('hidden');
            
            // Clear other items
            const cards = container.querySelectorAll('.cart-item-row');
            cards.forEach(c => c.remove());
            totalQty.innerText = '0';
            subtotalAmt.innerText = '$0.00';
            discountRow.classList.add('hidden');
            totalAmt.innerText = '$0.00';
            const totalVesBadge = document.getElementById('total-amount-ves-badge');
            if (totalVesBadge) totalVesBadge.innerText = 'Bs. 0,00';
            btn.disabled = true;
            return;
        }

        emptyState.style.display = 'none';

        // Clear existing rows
        const cards = container.querySelectorAll('.cart-item-row');
        cards.forEach(c => c.remove());

        let totalQ = 0;
        let subtotal = 0;

        cart.forEach(item => {
            totalQ += item.quantity;
            subtotal += item.price * item.quantity;

            const row = document.createElement('div');
            row.className = 'cart-item-row flex items-center justify-between bg-slate-950/40 p-3 border border-slate-850 rounded-xl text-xs';
            row.innerHTML = `
                <div class="overflow-hidden pr-2">
                    <span class="block font-bold text-slate-100 truncate">${item.name}</span>
                    <span class="block text-[10px] text-lime-400 font-medium">$${item.price.toFixed(2)} c/u</span>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <div class="flex items-center bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                        <button type="button" onclick="updateQuantity(${item.product_id}, -1)" class="px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition-colors">-</button>
                        <span class="px-2.5 text-slate-200 font-bold font-mono">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${item.product_id}, 1)" class="px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition-colors">+</button>
                    </div>
                    <span class="font-extrabold text-slate-200 min-w-[50px] text-right">$${(item.price * item.quantity).toFixed(2)}</span>
                </div>
            `;
            container.appendChild(row);
        });

        // Apply coupon discount
        let discount = 0;
        if (appliedPromo) {
            if (appliedPromo.discount_type === 'percentage') {
                discount = subtotal * (appliedPromo.discount_value / 100);
            } else {
                discount = appliedPromo.discount_value;
            }
            
            discountRow.classList.remove('hidden');
            discountCode.innerText = appliedPromo.code;
            discountAmt.innerText = `-$${discount.toFixed(2)}`;
        } else {
            discountRow.classList.add('hidden');
        }

        const finalTotal = Math.max(0, subtotal - discount);
        const finalTotalVes = finalTotal * currentExchangeRate;

        totalQty.innerText = totalQ;
        subtotalAmt.innerText = `$${subtotal.toFixed(2)}`;
        totalAmt.innerText = `$${finalTotal.toFixed(2)}`;
        
        const totalVesBadge = document.getElementById('total-amount-ves-badge');
        if (totalVesBadge) {
            totalVesBadge.innerText = `Bs. ${finalTotalVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        btn.disabled = false;

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    async function prepareSubmit(e) {
        if (e) e.preventDefault();

        if (cart.length === 0) {
            showPosToast('El carrito de venta está vacío.', 'danger');
            return;
        }

        const paymentMethod = document.getElementById('payment-method-select')?.value || 'cash';
        const userSelect = document.getElementById('pos-user-id-select');
        const userId = userSelect?.value || '';
        const selectedOption = userSelect?.options[userSelect.selectedIndex];
        const selectedEmail = selectedOption?.dataset?.email || '';

        const promoCode = document.getElementById('pos_promo_code')?.value || '';
        const notes = document.getElementById('pos_notes')?.value || '';

        const cartJson = JSON.stringify(cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity
        })));

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('payment_method', paymentMethod);
        if (userId) formData.append('user_id', userId);
        if (selectedEmail) formData.append('recipient_email', selectedEmail);
        if (promoCode) formData.append('promo_code', promoCode);
        if (notes) formData.append('notes', notes);
        formData.append('cart', cartJson);

        const btn = document.getElementById('checkout-submit-btn');
        if (btn) btn.disabled = true;

        try {
            const response = await fetch("{{ route('tienda.register_sale') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                lastCompletedSaleData = data;
                lastCompletedSaleDetails = {
                    sale_id: data.sale_id,
                    total: data.total_formatted,
                    total_ves: data.total_ves_formatted || ('Bs. ' + (parseFloat(data.total_amount) * currentExchangeRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })),
                    exchange_rate: data.exchange_rate || currentExchangeRate,
                    date: data.sale_date,
                    payment: data.payment_method,
                    gym_name: data.gym_name || 'BigWorldFitness',
                    cashier_name: data.cashier_name || 'Cajero Principal',
                    client_name: data.client_name || 'Cliente General',
                    client_dni: data.client_dni || 'Sin DNI',
                    items: [...cart]
                };

                // Reset Cart immediately
                cart = [];
                renderCart();

                // Show completed sale modal with intelligent options
                showSaleCompletedModal(data);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error en Transacción',
                        text: data.message || 'No se pudo completar la venta.',
                        icon: 'error',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#f43f5e'
                    });
                } else {
                    showPosToast(data.message || 'Error al procesar venta.', 'danger');
                }
            }
        } catch (err) {
            console.error(err);
            showPosToast('Error de conexión al procesar venta.', 'danger');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function printPosReceipt(sale) {
        const paymentLabel = {
            'cash': 'Efectivo',
            'card': 'Tarjeta Débito / Crédito',
            'transfer': 'Transferencia / Pago Móvil',
            'other': 'Otro Método'
        }[sale.payment] || (sale.payment || 'Efectivo');

        const rate = sale.exchange_rate || currentExchangeRate || 1;
        const totalVes = sale.total_ves || ('Bs. ' + (parseFloat((sale.total || '0').replace('$', '').replace(',', '')) * rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        const itemsRows = sale.items.map(i => {
            const itemVes = (i.price * i.quantity * rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return `
            <tr style="border-bottom: 1px dashed #e2e8f0;">
                <td style="padding: 6px 0; vertical-align: top;">
                    <strong style="font-size: 11px; color: #0f172a; display: block; line-height: 1.2;">${i.name}</strong>
                    <span style="font-size: 9px; color: #64748b;">${i.quantity} unidad(es) x $${parseFloat(i.price).toFixed(2)}</span>
                </td>
                <td style="padding: 6px 0; text-align: right; font-weight: bold; font-size: 11px; color: #0f172a; vertical-align: top;">
                    $${(i.price * i.quantity).toFixed(2)}
                    <div style="font-size: 9px; color: #16a34a; font-weight: bold;">Bs. ${itemVes}</div>
                </td>
            </tr>
            `;
        }).join('');

        const receiptCss = `
            @page {
                size: portrait;
                margin: 0mm !important;
            }
            @media print {
                @page {
                    margin: 0mm !important;
                }
                html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                #receipt-wrapper {
                    padding: 15px 0 !important;
                }
                #receipt-container {
                    box-shadow: none !important;
                    border: 1.5px solid #0f172a !important;
                }
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-family: 'Segoe UI', Arial, sans-serif;
            }
            #receipt-wrapper {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 15px 0;
                box-sizing: border-box;
            }
            #receipt-container {
                width: 320px;
                max-width: 100%;
                margin: 0 auto;
                padding: 16px;
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                box-sizing: border-box;
            }
        `;

        const receiptHtml = `
            <div id="receipt-wrapper">
                <div id="receipt-container">
                    
                    <!-- HEADER -->
                    <div style="text-align: center; padding-bottom: 10px; border-bottom: 2px solid #0f172a; margin-bottom: 12px;">
                        <h1 style="margin: 0; font-size: 17px; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; color: #0f172a;">${escapeHtml(sale.gym_name || 'BIGWORLD FITNESS')}</h1>
                        <p style="margin: 3px 0 0 0; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569;">Gimnasio & Centro de Entrenamiento</p>
                        <p style="margin: 2px 0 0 0; font-size: 8px; color: #64748b;">Comprobante No Fiscal | Sistema BigWorldFitness</p>
                    </div>

                    <!-- METADATA BOX -->
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; margin-bottom: 12px; font-size: 10px; line-height: 1.5;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-weight: 600;">N° Ticket:</span>
                            <strong style="color: #0f172a; font-family: monospace; font-size: 11px;">#${sale.sale_id}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-weight: 600;">Fecha y Hora:</span>
                            <strong style="color: #0f172a;">${sale.date}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-weight: 600;">Cliente / Socio:</span>
                            <strong style="color: #0f172a;">${escapeHtml(sale.client_name || 'Cliente General')}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-weight: 600;">DNI / Cédula:</span>
                            <strong style="color: #0f172a; font-family: monospace;">${escapeHtml(sale.client_dni || 'Sin DNI')}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-weight: 600;">Atendido por:</span>
                            <strong style="color: #0f172a;">${escapeHtml(sale.cashier_name || 'Cajero')}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-weight: 600;">Forma de Pago:</span>
                            <strong style="color: #0f172a;">${paymentLabel}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 4px; margin-top: 4px;">
                            <span style="color: #64748b; font-weight: 600;">Factor Cambiario:</span>
                            <strong style="color: #16a34a; font-family: monospace;">1 USD = Bs. ${rate.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                        </div>
                    </div>

                    <!-- ITEMS TABLE -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                        <thead>
                            <tr style="border-bottom: 1.5px solid #0f172a; text-transform: uppercase; font-size: 9px; color: #475569;">
                                <th style="text-align: left; padding-bottom: 4px;">Ítem / Cant.</th>
                                <th style="text-align: right; padding-bottom: 4px;">Total ($ / Bs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                    </table>

                    <!-- TOTAL BANNER -->
                    <div style="background: #0f172a; color: #ffffff; border-radius: 8px; padding: 10px 12px; margin-top: 8px; display: flex; justify-content: space-between; align-items: center; -webkit-print-color-adjust: exact;">
                        <div>
                            <span style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; display: block;">TOTAL COBRADO</span>
                            <span style="font-size: 9px; color: #94a3b8; font-weight: normal;">Equivalente en Bolívares</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 16px; font-weight: 900; font-family: monospace; color: #a3e635; display: block;">${sale.total}</span>
                            <span style="font-size: 12px; font-weight: 800; font-family: monospace; color: #ffffff; display: block;">${totalVes}</span>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div style="text-align: center; margin-top: 14px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 8px; color: #64748b;">
                        <p style="margin: 0; font-weight: 700; color: #334155; font-size: 9px;">¡Gracias por entrenar en BigWorldFitness!</p>
                        <p style="margin: 2px 0 0 0; color: #94a3b8;">Conserva este ticket para cualquier consulta o reclamo.</p>
                    </div>
                </div>
            </div>
        `;

        if (typeof printJS !== 'undefined') {
            printJS({
                printable: receiptHtml,
                type: 'raw-html',
                style: receiptCss,
                documentTitle: ''
            });
        } else {
            const win = window.open('', '_blank');
            win.document.write(`<html><head><title></title><style>${receiptCss}</style></head><body>${receiptHtml}</body></html>`);
            win.document.close();
            win.focus();
            win.print();
            win.close();
        }
    }

    // Fast & Intelligent POS Sale Completed Modal
    function showSaleCompletedModal(saleData) {
        if (typeof Swal === 'undefined') {
            showPosToast(saleData.message, 'success');
            return;
        }

        const hasEmail = Boolean(saleData.has_email && saleData.recipient_email);

        let emailStatusHtml = '';
        if (hasEmail) {
            emailStatusHtml = `
                <div id="pos-email-status-badge" class="p-2.5 bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs rounded-xl flex items-center justify-center gap-2 font-medium mt-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-sky-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Enviando comprobante a <strong>${escapeHtml(saleData.recipient_email)}</strong>...</span>
                </div>
            `;
        }

        let extraButtonsHtml = `
            <div class="flex flex-col sm:flex-row gap-2 mt-4 pt-3 border-t border-slate-800">
                <button type="button" onclick="printPosReceipt(lastCompletedSaleDetails)" class="flex-1 py-2.5 px-3 bg-gradient-to-r from-lime-500 to-emerald-500 text-slate-950 font-bold rounded-xl text-xs flex items-center justify-center gap-2 hover:from-lime-400 hover:to-emerald-400 transition-all shadow-lg">
                    <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Ticket POS
                </button>
        `;

        if (!hasEmail) {
            extraButtonsHtml += `
                <button type="button" onclick="promptSendReceiptEmail(lastCompletedSaleDetails.sale_id, '')" class="flex-1 py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-sky-400 border border-sky-500/30 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="mail" class="w-4 h-4"></i> Enviar Ticket por Correo
                </button>
            `;
        } else {
            extraButtonsHtml += `
                <button type="button" onclick="promptSendReceiptEmail(lastCompletedSaleDetails.sale_id, '${escapeHtml(saleData.recipient_email)}')" class="py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all" title="Reenviar o cambiar dirección de correo">
                    <i data-lucide="mail-plus" class="w-4 h-4 text-sky-400"></i> Reenviar Correo
                </button>
            `;
        }

        extraButtonsHtml += `</div>`;

        Swal.fire({
            title: '¡Venta Registrada Exitosamente!',
            html: `
                <div class="space-y-3 text-center text-slate-200 py-1">
                    <p class="text-xs text-slate-400">Comprobante POS #${saleData.sale_id} procesado correctamente.</p>
                    <div class="p-4 bg-slate-950 border border-slate-800 rounded-2xl">
                        <span class="block text-[10px] uppercase text-slate-500 font-extrabold tracking-wider">Total Cobrado</span>
                        <span class="text-3xl font-black text-lime-400 mt-0.5 block">${saleData.total_formatted}</span>
                        <span class="text-xs font-extrabold text-emerald-400 font-mono block mt-1">${saleData.total_ves_formatted || ('Bs. ' + (parseFloat(saleData.total_amount) * currentExchangeRate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))}</span>
                    </div>
                    ${emailStatusHtml}
                    ${extraButtonsHtml}
                </div>
            `,
            icon: 'success',
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonColor: '#475569',
            cancelButtonText: 'Cerrar y Nueva Venta',
            background: '#0f172a',
            color: '#f8fafc',
            didOpen: () => {
                if (window.lucide) window.lucide.createIcons();

                if (hasEmail) {
                    dispatchBackgroundReceiptEmail(saleData.sale_id, saleData.recipient_email);
                }
            }
        });
    }

    // Async Non-Blocking Background Email Dispatcher
    function dispatchBackgroundReceiptEmail(saleId, email) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('sale_id', saleId);
        formData.append('email', email);

        fetch("{{ route('tienda.send_email') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('pos-email-status-badge');
            if (badge) {
                if (data.success) {
                    badge.className = "p-2.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs rounded-xl flex items-center justify-center gap-2 font-medium mt-2";
                    badge.innerHTML = `<i data-lucide="mail-check" class="w-4 h-4 text-emerald-400"></i><span>✔ Ticket enviado con éxito a: <strong>${escapeHtml(email)}</strong></span>`;
                } else {
                    badge.className = "p-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl flex items-center justify-center gap-2 font-medium mt-2";
                    badge.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 text-rose-400"></i><span>No se pudo enviar el correo a <strong>${escapeHtml(email)}</strong></span>`;
                }
                if (window.lucide) window.lucide.createIcons();
            }
        })
        .catch(err => {
            console.error(err);
            const badge = document.getElementById('pos-email-status-badge');
            if (badge) {
                badge.className = "p-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl flex items-center justify-center gap-2 font-medium mt-2";
                badge.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 text-rose-400"></i><span>Error de conexión al enviar correo.</span>`;
                if (window.lucide) window.lucide.createIcons();
            }
        });
    }

    // Interactive Email Prompt for POS Receipts via Gmail SMTP with Bi-directional Navigation
    function promptSendReceiptEmail(saleId, defaultEmail) {
        if (typeof Swal === 'undefined') return;

        const hasEmail = Boolean(defaultEmail && defaultEmail.trim() !== '');

        Swal.fire({
            title: hasEmail ? 'Confirmar / Reenviar Correo' : 'Enviar Ticket por Correo',
            text: hasEmail 
                ? 'Se enviará el comprobante digital a la siguiente dirección de correo:' 
                : 'El cliente no tiene un correo registrado. Ingresa la dirección de correo donde enviar la factura:',
            input: 'email',
            inputValue: defaultEmail || '',
            inputPlaceholder: 'correo@ejemplo.com',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#0ea5e9',
            denyButtonColor: '#334155',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Enviar Ahora',
            denyButtonText: '↩ Volver al Resumen',
            cancelButtonText: 'Cancelar',
            background: '#0f172a',
            color: '#f8fafc',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar un correo electrónico válido.';
                }
            }
        }).then((result) => {
            if (result.isDenied) {
                if (lastCompletedSaleData) showSaleCompletedModal(lastCompletedSaleData);
            } else if (result.isConfirmed && result.value) {
                const targetEmail = result.value;
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('sale_id', saleId);
                formData.append('email', targetEmail);

                Swal.fire({
                    title: 'Enviando Correo...',
                    text: `Enviando comprobante digital a ${targetEmail}`,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    background: '#0f172a',
                    color: '#f8fafc'
                });

                fetch("{{ route('tienda.send_email') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Correo Enviado!',
                            text: data.message,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#84cc16',
                            cancelButtonColor: '#334155',
                            confirmButtonText: 'Imprimir Ticket POS',
                            cancelButtonText: '↩ Volver al Resumen',
                            background: '#0f172a',
                            color: '#f8fafc'
                        }).then((postResult) => {
                            if (postResult.isConfirmed && lastCompletedSaleDetails) {
                                printPosReceipt(lastCompletedSaleDetails);
                                setTimeout(() => {
                                    if (lastCompletedSaleData) showSaleCompletedModal(lastCompletedSaleData);
                                }, 500);
                            } else {
                                if (lastCompletedSaleData) showSaleCompletedModal(lastCompletedSaleData);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error al Enviar',
                            text: data.message || 'No se pudo enviar el correo.',
                            icon: 'error',
                            confirmButtonText: '↩ Volver al Resumen',
                            confirmButtonColor: '#0ea5e9',
                            background: '#0f172a',
                            color: '#f8fafc'
                        }).then(() => {
                            if (lastCompletedSaleData) showSaleCompletedModal(lastCompletedSaleData);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    showPosToast('Error de conexión al enviar el correo.', 'danger');
                    if (lastCompletedSaleData) showSaleCompletedModal(lastCompletedSaleData);
                });
            }
        });
    }

    // POS BARCODE SCANNER HANDLER
    let posBarcodeQrInstance = null;

    function openPosBarcodeScannerModal() {
        const modal = document.getElementById('pos_barcode_modal');
        if (modal) modal.classList.remove('hidden');

        setTimeout(() => {
            if (typeof Html5Qrcode !== 'undefined') {
                posBarcodeQrInstance = new Html5Qrcode("pos_barcode_viewport");
                posBarcodeQrInstance.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 160 } },
                    (scannedCode) => {
                        onPosBarcodeScanned(scannedCode);
                    },
                    (error) => {}
                ).catch(err => {
                    console.error("Camera error:", err);
                    showPosToast("No se pudo acceder a la cámara para escanear código de barras.", "danger");
                });
            } else {
                showPosToast("Librería de escáner cargándose...", "info");
            }
        }, 300);
    }

    function closePosBarcodeScannerModal() {
        const modal = document.getElementById('pos_barcode_modal');
        if (modal) modal.classList.add('hidden');

        if (posBarcodeQrInstance) {
            posBarcodeQrInstance.stop().then(() => {
                posBarcodeQrInstance.clear();
                posBarcodeQrInstance = null;
            }).catch(err => console.error(err));
        }
    }

    function onPosBarcodeScanned(scannedCode) {
        closePosBarcodeScannerModal();

        const codeStr = scannedCode.trim().toLowerCase();
        // Try finding matching product in grid cards by ID, name or SKU
        const cards = document.querySelectorAll('.product-card');
        let matchedCard = null;

        cards.forEach(card => {
            const pid = card.dataset.id;
            const pname = (card.dataset.name || '').toLowerCase();
            if (pid === codeStr || pname.includes(codeStr) || codeStr.includes(pid)) {
                matchedCard = card;
            }
        });

        if (matchedCard) {
            addToCart(matchedCard);
            showPosToast(`¡Producto agregado al carrito: ${matchedCard.dataset.name}!`, 'success');
        } else {
            showPosToast(`No se encontró ningún producto para el código: ${scannedCode}`, 'danger');
        }
    }

    // Custom Toast Notification System for POS
    function showPosToast(message, type = 'warning') {
        let container = document.getElementById('pos-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'pos-toast-container';
            container.className = 'fixed top-24 right-6 z-50 flex flex-col gap-2.5 pointer-events-none max-w-xs sm:max-w-sm w-full';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        const isDanger = type === 'danger' || type === 'error';
        const isSuccess = type === 'success';

        let iconName = 'alert-triangle';
        let borderColor = 'border-amber-500/30';
        let iconColor = 'text-amber-400';
        let glowColor = 'shadow-amber-500/10';

        if (isDanger) {
            iconName = 'alert-circle';
            borderColor = 'border-rose-500/30';
            iconColor = 'text-rose-400';
            glowColor = 'shadow-rose-500/10';
        } else if (isSuccess) {
            iconName = 'check-circle';
            borderColor = 'border-emerald-500/30';
            iconColor = 'text-emerald-400';
            glowColor = 'shadow-emerald-500/10';
        }

        toast.className = `pointer-events-auto flex items-center gap-3 p-3.5 pr-4 bg-slate-900/95 border ${borderColor} text-slate-100 text-xs font-semibold rounded-2xl shadow-2xl ${glowColor} backdrop-blur-md transition-all duration-300 transform translate-x-10 opacity-0`;

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

    // Initialize pagination on load and navigation events
    function initPosPagination() {
        const cards = document.querySelectorAll('.product-card');
        matchingPosCards = Array.from(cards);
        renderPosPaginatedGrid();
    }

    initPosPagination();

    if (document.readyState !== 'loading') {
        initPosPagination();
    } else {
        document.addEventListener('DOMContentLoaded', initPosPagination);
    }

    window.addEventListener('load', initPosPagination);
    window.addEventListener('pageshow', initPosPagination);
</script>

<!-- POS BARCODE SCANNER MODAL -->
<div id="pos_barcode_modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md hidden p-4 animate-fade-in">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl relative space-y-4">
        <button type="button" onclick="closePosBarcodeScannerModal()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800 rounded-xl transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <h3 class="text-lg font-extrabold text-slate-100 flex items-center justify-center gap-2">
                <i data-lucide="barcode" class="w-5 h-5 text-lime-400"></i>
                Escanear Código de Barras
            </h3>
            <p class="text-xs text-slate-400 mt-1">Apunta con la cámara al código de barras del producto.</p>
        </div>
        <div id="pos_barcode_viewport" class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 min-h-[220px] flex items-center justify-center">
            <!-- Html5Qrcode viewport -->
        </div>
        <div class="text-center">
            <button type="button" onclick="closePosBarcodeScannerModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<!-- LIBRARIES CDNs -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
<link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">
@endsection
