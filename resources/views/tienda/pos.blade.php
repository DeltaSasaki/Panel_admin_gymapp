@extends('layouts.admin')

@section('title', 'Punto de Venta (POS)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left Column: Products Grid (2/3 width) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Punto de Venta</h1>
                <p class="text-xs text-slate-400 mt-1">Registra la venta rápida de productos para socios y clientes generales.</p>
            </div>
            <!-- Search bar -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="search-input" onkeyup="filterProducts()" placeholder="Buscar producto..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
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

        <!-- Products Catalog Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="products-container">
            @forelse($products as $product)
                <div class="product-card bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between hover:border-lime-500/30 transition-colors cursor-pointer select-none active:scale-[0.98]" 
                     data-id="{{ $product->id }}" 
                     data-name="{{ $product->name }}" 
                     data-price="{{ $product->price }}" 
                     data-stock="{{ $product->stock_quantity }}"
                     onclick="addToCart(this)">
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <span class="text-[10px] uppercase font-bold text-slate-500 px-2 py-0.5 bg-slate-950/60 rounded-md border border-slate-850/50">
                                {{ $product->category->name }}
                            </span>
                            <span class="text-xs text-lime-400 font-bold">Stock: {{ $product->stock_quantity }}</span>
                        </div>
                        <h3 class="font-bold text-slate-100 text-sm leading-snug truncate-2-lines">{{ $product->name }}</h3>
                        <p class="text-[10px] text-slate-400 mt-1 line-clamp-1">{{ $product->description ?? 'Sin descripción.' }}</p>
                    </div>
                    <div class="flex justify-between items-center border-t border-slate-850/50 pt-3 mt-3">
                        <span class="text-[10px] text-slate-500 uppercase">Precio</span>
                        <span class="font-black text-lime-400 text-sm">${{ number_format($product->price, 2) }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-550">
                    <i data-lucide="package-x" class="w-12 h-12 text-slate-700 mx-auto mb-2"></i>
                    No hay productos en inventario con stock disponible actualmente.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Shopping Cart (1/3 width) -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 flex flex-col h-[calc(100vh-120px)] sticky top-24">
        <h3 class="font-bold text-lg text-white mb-4 flex items-center gap-2 pb-3 border-b border-slate-800">
            <i data-lucide="shopping-cart" class="w-5 h-5 text-lime-400"></i> Detalle de Venta
        </h3>

        <!-- Cart Items List -->
        <div class="flex-1 overflow-y-auto pr-1 space-y-3" id="cart-items-container">
            <!-- Empty Cart State -->
            <div class="h-full flex flex-col items-center justify-center text-slate-500 text-xs py-8" id="empty-cart-state">
                <i data-lucide="shopping-bag" class="w-10 h-10 text-slate-700 mb-2"></i>
                Haz clic en un producto para agregarlo al carrito.
            </div>
        </div>

        <!-- Checkout Form Details -->
        <form action="{{ route('tienda.register_sale') }}" method="POST" class="pt-4 border-t border-slate-800 mt-4 space-y-4" onsubmit="prepareSubmit(event)">
            @csrf
            <input type="hidden" name="cart" id="cart-json-input">

            <!-- Customer Association -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Asociar Socio (Opcional)</label>
                <select name="user_id" class="w-full px-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <option value="">Cliente General (Sin asociar)</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->profile->first_name }} {{ $client->profile->last_name }}</option>
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
                    <button type="button" onclick="applyPosPromo()" class="px-3 bg-slate-800 hover:bg-slate-750 text-slate-200 hover:text-white text-xs font-bold rounded-xl border border-slate-750 transition-colors">
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
                    <span class="text-xs font-bold text-white uppercase">Monto Total:</span>
                    <span class="text-lg font-black text-lime-400" id="total-amount-badge">$0.00</span>
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
    let cart = [];

    function filterProducts() {
        const query = document.getElementById('search-input').value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            if (name.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
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
                alert(`No puedes agregar más de este producto. Stock máximo disponible: ${maxStock}`);
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
            alert(`Stock máximo disponible: ${item.maxStock}`);
        }

        renderCart();
    }

    let appliedPromo = null;

    async function applyPosPromo() {
        const codeInput = document.getElementById('pos_promo_code');
        const code = codeInput.value.trim().toUpperCase();
        const feedback = document.getElementById('pos-promo-feedback');

        if (cart.length === 0) {
            alert('Agrega productos al carrito antes de aplicar una promoción.');
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
                        <button type="button" onclick="updateQuantity(${item.product_id}, -1)" class="px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">-</button>
                        <span class="px-2.5 text-slate-200 font-bold font-mono">${item.quantity}</span>
                        <button type="button" onclick="updateQuantity(${item.product_id}, 1)" class="px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">+</button>
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

        totalQty.innerText = totalQ;
        subtotalAmt.innerText = `$${subtotal.toFixed(2)}`;
        totalAmt.innerText = `$${finalTotal.toFixed(2)}`;
        btn.disabled = false;

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    function prepareSubmit(e) {
        if (cart.length === 0) {
            e.preventDefault();
            return;
        }
        
        // Load JSON into input
        const cartJson = JSON.stringify(cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity
        })));
        
        document.getElementById('cart-json-input').value = cartJson;
    }
</script>
@endsection
