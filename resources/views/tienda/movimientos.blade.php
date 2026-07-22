@extends('layouts.admin')

@section('title', 'Auditoría de Movimientos de Stock')

@section('content')
@php
    $inCount = $movements->where('movement_type', 'in')->count();
    $outCount = $movements->where('movement_type', 'out')->count();
    $adjCount = $movements->whereNotIn('movement_type', ['in', 'out'])->count() + $auditLogs->count();
    $totalCount = $records->count();
@endphp

<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Auditoría de Movimientos</h1>
            <p class="text-xs text-slate-400 mt-1">Historial cronológico completo de entradas, salidas, ediciones y cambios en almacén.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tienda.products') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-lime-400"></i> Ver Inventario
            </a>
        </div>
    </div>

    <!-- Stock Movements Table Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <h3 class="font-bold text-lg text-slate-100">Registro de Auditoría de Almacén</h3>
                
                <!-- Movement Type Filter Tabs (Todos | Entradas | Salidas | Ajustes / Auditoría) -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                    <button type="button" onclick="setMovementTypeFilter('all')" id="m-filter-btn-all" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800">
                        Todos (<span id="count-all-movements">{{ $totalCount }}</span>)
                    </button>
                    <button type="button" onclick="setMovementTypeFilter('in')" id="m-filter-btn-in" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Entradas (<span id="count-in-movements">{{ $inCount }}</span>)
                    </button>
                    <button type="button" onclick="setMovementTypeFilter('out')" id="m-filter-btn-out" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Salidas (<span id="count-out-movements">{{ $outCount }}</span>)
                    </button>
                    <button type="button" onclick="setMovementTypeFilter('adjustment')" id="m-filter-btn-adjustment" class="m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200">
                        Ajustes & Logs (<span id="count-adj-movements">{{ $adjCount }}</span>)
                    </button>
                </div>
            </div>

            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                <input type="text" id="movement_search_input" oninput="onMovementSearchInput()" placeholder="Buscar por producto, motivo o usuario..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6 text-left">Producto / Elemento</th>
                        <th class="p-4 text-center">Tipo</th>
                        <th class="p-4 text-center">Cantidad / Valor</th>
                        <th class="p-4 text-left">Motivo / Detalle Auditoría</th>
                        <th class="p-4 text-center">Operado Por</th>
                        <th class="p-4 text-center pr-6">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody id="movements_table_body" class="divide-y divide-slate-850/50">
                    <!-- Chronologically Unified List (Sorted Newest to Oldest) -->
                    @forelse($records as $rec)
                        @if($rec->kind === 'movement')
                            @php
                                $mov = $rec->data;
                                $performerName = $mov->performer 
                                    ? (($mov->performer->profile->first_name ?? '') . ' ' . ($mov->performer->profile->last_name ?? '')) 
                                    : 'Sistema';
                                if (trim($performerName) === '') {
                                    $performerName = $mov->performer->email ?? 'Sistema';
                                }
                            @endphp
                            <tr data-movement-row
                                data-type="{{ strtolower($mov->movement_type ?? 'in') }}"
                                data-product="{{ strtolower($mov->product->name ?? '') }}"
                                data-reason="{{ strtolower($mov->reason ?? 'carga de stock') }}"
                                data-performer="{{ strtolower($performerName) }}"
                                class="hover:bg-slate-900/20 text-slate-200 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $mov->product && $mov->product->image_url ? asset($mov->product->image_url) : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=150&auto=format&fit=crop' }}" class="w-9 h-9 rounded-xl object-cover border border-slate-800 shrink-0">
                                        <span class="font-bold text-slate-100">{{ $mov->product->name ?? 'Producto eliminado' }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if($mov->movement_type === 'in')
                                        <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-lg font-black text-[9px] uppercase tracking-wide">
                                            Entrada
                                        </span>
                                    @elseif($mov->movement_type === 'out')
                                        <span class="px-2.5 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-lg font-black text-[9px] uppercase tracking-wide">
                                            {{ str_contains(strtolower($mov->reason ?? ''), 'venta') ? 'Venta POS' : 'Salida' }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-lg font-black text-[9px] uppercase tracking-wide">
                                            Ajuste
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center font-mono font-bold text-slate-100">
                                    <span class="px-2.5 py-1 bg-slate-950/80 border rounded-lg font-mono font-bold {{ $mov->movement_type === 'out' ? 'text-rose-400 border-rose-500/20 bg-rose-500/5' : 'text-emerald-400 border-emerald-500/20 bg-emerald-500/5' }}">
                                        {{ $mov->movement_type === 'out' ? '-' : '+' }}{{ $mov->quantity }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-300 font-medium">
                                    {{ $mov->reason ?? 'Carga de stock' }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 bg-slate-950/60 border border-slate-850 rounded-lg text-slate-300 font-semibold text-[11px]">
                                        {{ $performerName }}
                                    </span>
                                </td>
                                <td class="p-4 text-center pr-6 font-mono text-slate-400 text-xs">
                                    {{ \Carbon\Carbon::parse($mov->createdAt)->format('d/m/Y H:i:s') }}
                                </td>
                            </tr>
                        @else
                            @php
                                $log = $rec->data;
                                $old = $log->old_data ? (is_array($log->old_data) ? $log->old_data : json_decode($log->old_data, true)) : [];
                                $new = $log->new_data ? (is_array($log->new_data) ? $log->new_data : json_decode($log->new_data, true)) : [];

                                // 1. Product / Item Name Resolution
                                $itemName = $new['name'] ?? $old['name'] ?? null;
                                if (!$itemName && isset($new['product_id'])) {
                                    $pModel = \App\Models\InventoryProduct::find($new['product_id']);
                                    if ($pModel) $itemName = $pModel->name;
                                }
                                if (!$itemName && isset($old['product_id'])) {
                                    $pModel = \App\Models\InventoryProduct::find($old['product_id']);
                                    if ($pModel) $itemName = $pModel->name;
                                }

                                if (!$itemName) {
                                    if ($log->table_name === 'inventory_products') $itemName = 'Producto #' . $log->record_id;
                                    elseif ($log->table_name === 'product_categories') $itemName = 'Categoría #' . $log->record_id;
                                    elseif ($log->table_name === 'inventory_movements') $itemName = 'Movimiento #' . $log->record_id;
                                    else $itemName = 'Registro #' . $log->record_id;
                                }

                                // 2. Performer Name Resolution
                                $performerName = $log->admin 
                                    ? trim(($log->admin->profile->first_name ?? '') . ' ' . ($log->admin->profile->last_name ?? '')) 
                                    : 'Sistema';
                                if (empty($performerName) && $log->admin) {
                                    $performerName = $log->admin->email ?? 'Sistema';
                                }

                                // 3. Type Label & Badge Styling (Green for Ajuste!)
                                $typeLabel = 'Ajuste';
                                $badgeClass = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                                $qtyDisplay = '-';
                                $detail = '';

                                if ($log->table_name === 'inventory_products') {
                                    if ($log->action_type === 'INSERT') {
                                        $typeLabel = 'Producto Creado';
                                        $badgeClass = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                                        $qtyDisplay = isset($new['price']) ? '$' . number_format($new['price'], 2) : '-';
                                        $detail = 'Registrado en catálogo con precio $' . number_format($new['price'] ?? 0, 2);
                                    } elseif ($log->action_type === 'UPDATE') {
                                        if (isset($old['is_available']) && isset($new['is_available']) && $old['is_available'] != $new['is_available']) {
                                            if ($new['is_available'] == 0) {
                                                $typeLabel = 'Inhabilitado';
                                                $badgeClass = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
                                                $qtyDisplay = '-';
                                                $detail = 'Producto inhabilitado para ventas en POS';
                                            } else {
                                                $typeLabel = 'Habilitado';
                                                $badgeClass = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                                                $qtyDisplay = '-';
                                                $detail = 'Producto reactivado para ventas en POS';
                                            }
                                        } else {
                                            $changes = [];
                                            $priceDisplay = null;
                                            if (isset($old['name']) && isset($new['name']) && $old['name'] != $new['name']) {
                                                $changes[] = "Nombre: '" . $old['name'] . "' ➔ '" . $new['name'] . "'";
                                            }
                                            if (isset($old['price']) && isset($new['price']) && (float)$old['price'] != (float)$new['price']) {
                                                $changes[] = "Precio: $" . number_format($old['price'], 2) . " ➔ $" . number_format($new['price'], 2);
                                                $priceDisplay = '$' . number_format($new['price'], 2);
                                            }
                                            if (isset($old['cost_price']) && isset($new['cost_price']) && (float)$old['cost_price'] != (float)$new['cost_price']) {
                                                $changes[] = "Costo: $" . number_format($old['cost_price'], 2) . " ➔ $" . number_format($new['cost_price'], 2);
                                            }
                                            if (isset($old['min_stock']) && isset($new['min_stock']) && $old['min_stock'] != $new['min_stock']) {
                                                $changes[] = "Stock mín: " . $old['min_stock'] . " ➔ " . $new['min_stock'];
                                            }

                                            $typeLabel = 'Edición';
                                            $badgeClass = 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
                                            $qtyDisplay = $priceDisplay ?? '-';
                                            $detail = !empty($changes) ? implode(' | ', $changes) : 'Modificación de información del producto';
                                        }
                                    }
                                } elseif ($log->table_name === 'inventory_movements') {
                                    $typeLabel = 'Ajuste';
                                    $badgeClass = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                                    $quantity = $new['quantity'] ?? $old['quantity'] ?? null;
                                    $qtyDisplay = $quantity ? '+' . $quantity : '-';
                                    $reason = $new['reason'] ?? $old['reason'] ?? 'Reabastecimiento de inventario';
                                    $detail = "Ajuste de stock: " . $reason;
                                } elseif ($log->table_name === 'product_categories') {
                                    $typeLabel = 'Categoría';
                                    $badgeClass = 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
                                    $qtyDisplay = '-';
                                    $detail = ($log->action_type === 'INSERT' ? 'Nueva categoría registrada: ' : 'Modificación de categoría: ') . "'" . $itemName . "'";
                                } elseif ($log->table_name === 'product_sales') {
                                    $typeLabel = 'Venta POS';
                                    $badgeClass = 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20';
                                    $qtyDisplay = '$' . number_format($new['total_amount'] ?? 0, 2);
                                    $detail = 'Venta procesada en la terminal POS';
                                } else {
                                    $detail = 'Modificación realizada en ' . $log->table_name;
                                }
                            @endphp
                            <tr data-movement-row
                                data-type="adjustment"
                                data-product="{{ strtolower($itemName) }}"
                                data-reason="{{ strtolower($detail) }}"
                                data-performer="{{ strtolower($performerName) }}"
                                class="hover:bg-slate-900/20 text-slate-200 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center shrink-0 text-slate-400">
                                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-100 block">{{ $itemName }}</span>
                                            <span class="text-[10px] text-slate-500 font-mono uppercase">Auditoría #{{ $log->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 {{ $badgeClass }} rounded-lg font-black text-[9px] uppercase tracking-wide">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-mono font-bold text-slate-100">
                                    <span class="px-2.5 py-1 bg-slate-950/80 border border-slate-850 rounded-lg font-mono font-bold">
                                        {{ $qtyDisplay }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-300 font-medium">
                                    {{ $detail }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 bg-slate-950/60 border border-slate-850 rounded-lg text-slate-300 font-semibold text-[11px]">
                                        {{ $performerName }}
                                    </span>
                                </td>
                                <td class="p-4 text-center pr-6 font-mono text-slate-400 text-xs">
                                    {{ \Carbon\Carbon::parse($log->createdAt)->format('d/m/Y H:i:s') }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 italic font-semibold">
                                No se han registrado movimientos ni eventos de auditoría todavía.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_movements_search_row" class="hidden">
                        <td colspan="6" class="p-10 text-center text-slate-500">
                            <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron movimientos que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div id="movement_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span id="movement_pagination_info">Mostrando movimientos...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="prev_mov_page_btn" onclick="changeMovementPage(-1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Anterior
                </button>
                <span id="mov_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                <button type="button" id="next_mov_page_btn" onclick="changeMovementPage(1)" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentMovementPage = 1;
    let currentMovementTypeFilter = 'all';
    const movementsPerPage = 10;

    function setMovementTypeFilter(type) {
        currentMovementTypeFilter = type;

        const tabs = document.querySelectorAll('.m-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200";
        });

        const activeTab = document.getElementById('m-filter-btn-' + type);
        if (activeTab) {
            activeTab.className = "m-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-lime-400 border border-slate-800";
        }

        currentMovementPage = 1;
        renderMovementPage();
    }

    function getMatchingMovementRows() {
        const query = (document.getElementById('movement_search_input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-movement-row]'));

        return rows.filter(row => {
            const movType = row.getAttribute('data-type') || '';
            const product = row.getAttribute('data-product') || '';
            const reason = row.getAttribute('data-reason') || '';
            const performer = row.getAttribute('data-performer') || '';

            let matchesType = true;
            if (currentMovementTypeFilter === 'in') {
                matchesType = movType === 'in';
            } else if (currentMovementTypeFilter === 'out') {
                matchesType = movType === 'out';
            } else if (currentMovementTypeFilter === 'adjustment') {
                matchesType = (movType !== 'in' && movType !== 'out');
            }

            let matchesSearch = true;
            if (query) {
                matchesSearch = product.includes(query) || reason.includes(query) || performer.includes(query);
            }

            return matchesType && matchesSearch;
        });
    }

    function renderMovementPage() {
        const allRows = Array.from(document.querySelectorAll('[data-movement-row]'));
        const matchingRows = getMatchingMovementRows();
        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / movementsPerPage) || 1;

        if (currentMovementPage > totalPages) currentMovementPage = totalPages;
        if (currentMovementPage < 1) currentMovementPage = 1;

        // Hide all rows
        allRows.forEach(row => row.classList.add('hidden'));

        // Show page rows
        const startIndex = (currentMovementPage - 1) * movementsPerPage;
        const endIndex = startIndex + movementsPerPage;
        const pageRows = matchingRows.slice(startIndex, endIndex);

        pageRows.forEach(row => row.classList.remove('hidden'));

        // Show/hide empty state
        const emptySearchRow = document.getElementById('no_movements_search_row');
        if (emptySearchRow) {
            if (totalMatching === 0 && allRows.length > 0) {
                emptySearchRow.classList.remove('hidden');
            } else {
                emptySearchRow.classList.add('hidden');
            }
        }

        // Update pagination text & buttons
        const infoSpan = document.getElementById('movement_pagination_info');
        if (infoSpan) {
            const from = totalMatching === 0 ? 0 : startIndex + 1;
            const to = Math.min(endIndex, totalMatching);
            infoSpan.textContent = `Mostrando ${from} a ${to} de ${totalMatching} registros`;
        }

        const pageDisplay = document.getElementById('mov_page_number_display');
        if (pageDisplay) {
            pageDisplay.textContent = `Página ${currentMovementPage} de ${totalPages}`;
        }

        const prevBtn = document.getElementById('prev_mov_page_btn');
        if (prevBtn) prevBtn.disabled = (currentMovementPage <= 1);

        const nextBtn = document.getElementById('next_mov_page_btn');
        if (nextBtn) nextBtn.disabled = (currentMovementPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function onMovementSearchInput() {
        currentMovementPage = 1;
        renderMovementPage();
    }

    function changeMovementPage(delta) {
        currentMovementPage += delta;
        renderMovementPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderMovementPage();
    });
</script>
@endsection
