@extends('layouts.admin')

@section('title', 'Historial de Ventas')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Historial de Ventas</h1>
        <p class="text-xs text-slate-400 mt-1">Monitorea los comprobantes de caja y transacciones de productos de la tienda.</p>
    </div>

    <!-- Sales Receipts Logs Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl space-y-4">
        
        <!-- Header Controls: Title, Period Filter & Text Search Input -->
        <div class="p-6 border-b border-slate-850 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-lime-400"></i>
                    Transacciones Registradas
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Historial de ventas en terminal POS con filtros por fecha y buscador.</p>
            </div>

            <!-- Date Period & Search Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Date Period Filter Dropdown -->
                <select id="sales_period_filter" onchange="onSalesPeriodFilterChange()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-lime-400 font-bold focus:outline-none focus:border-lime-500 cursor-pointer">
                    <option value="all" selected>Todas las Fechas</option>
                    <option value="today">Hoy</option>
                    <option value="this_week">Esta Semana</option>
                    <option value="last_week">Semana Anterior</option>
                    <option value="this_month">Mes Actual</option>
                    <option value="custom">Rango Personalizado...</option>
                </select>

                <div id="sales_custom_date_container" class="hidden flex items-center gap-2">
                    <input type="date" id="sales_start_date" onchange="applySalesFilters()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-slate-200 font-medium">
                    <span class="text-slate-500 text-xs">-</span>
                    <input type="date" id="sales_end_date" onchange="applySalesFilters()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-slate-200 font-medium">
                </div>

                <!-- Text Search Input (Filters by articles, seller, client, price, ID) -->
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                    <input type="text" id="sales_search_input" oninput="applySalesFilters()" placeholder="Buscar por producto, atendió o precio..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
        </div>
        
        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">ID Venta</th>
                        <th class="p-4">Fecha y Hora</th>
                        <th class="p-4">Cliente / Socio</th>
                        <th class="p-4">Atendido Por</th>
                        <th class="p-4">Artículos Vendidos</th>
                        <th class="p-4 text-center">Cupón</th>
                        <th class="p-4 text-center">Método Pago</th>
                        <th class="p-4 text-right">Monto Total</th>
                        <th class="p-4 text-center pr-6">Nota</th>
                    </tr>
                </thead>
                <tbody id="sales_table_body" class="divide-y divide-slate-850/50">
                    @forelse($sales as $sale)
                        @php
                            $formattedDate = \Carbon\Carbon::parse($sale->createdAt)->format('Y-m-d');
                            $clientNameEmail = $sale->client 
                                ? trim(($sale->client->profile->first_name ?? '') . ' ' . ($sale->client->profile->last_name ?? '') . ' ' . ($sale->client->email ?? ''))
                                : 'cliente general';
                            $sellerNameEmail = $sale->seller 
                                ? trim(($sale->seller->profile->first_name ?? '') . ' ' . ($sale->seller->profile->last_name ?? '') . ' ' . ($sale->seller->email ?? ''))
                                : 'sistema';
                            $itemsListStr = $sale->items->pluck('product.name')->filter()->implode(' ');
                        @endphp
                        <tr data-sale-row
                            data-sale-id="{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}"
                            data-date="{{ $formattedDate }}"
                            data-client="{{ strtolower($clientNameEmail) }}"
                            data-seller="{{ strtolower($sellerNameEmail) }}"
                            data-items="{{ strtolower($itemsListStr) }}"
                            data-amount="{{ number_format($sale->total_amount, 2) }}"
                            class="hover:bg-slate-900/20 text-slate-200 transition-colors">
                            <td class="p-4 pl-6 font-mono font-bold text-lime-400">#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="p-4 text-slate-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($sale->createdAt)->format('d/m/Y H:i') }}</td>
                            <td class="p-4">
                                @if($sale->client)
                                    <span class="block font-bold text-slate-100">{{ $sale->client->profile->first_name ?? '' }} {{ $sale->client->profile->last_name ?? '' }}</span>
                                    <span class="block text-[10px] text-slate-500 font-mono">{{ $sale->client->email }}</span>
                                @else
                                    <span class="text-slate-500 font-medium italic">Cliente General</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-300 whitespace-nowrap">
                                @if($sale->seller)
                                    @php
                                        $sellerPrefix = match($sale->seller->role) {
                                            'cajero' => 'Cajero ',
                                            'trainer' => 'Coach ',
                                            'superadmin' => 'SuperAdmin ',
                                            'admin' => 'Admin ',
                                            default => ''
                                        };
                                    @endphp
                                    {{ $sellerPrefix . ($sale->seller->profile->first_name ?? $sale->seller->email) }}
                                @else
                                    Sistema
                                @endif
                            </td>
                            <td class="p-4">
                                <ul class="space-y-0.5 list-inside list-disc text-slate-400 text-[10px]">
                                    @foreach($sale->items as $item)
                                        <li>{{ $item->product->name ?? 'Producto' }} <strong class="text-slate-200">x{{ $item->quantity }}</strong></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($sale->promoCode)
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded font-black tracking-wide text-[9px] uppercase">
                                        {{ $sale->promoCode->code }}
                                    </span>
                                @else
                                    <span class="text-slate-600 font-bold italic">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($sale->payment_method === 'cash')
                                    <span class="px-2 py-0.5 bg-slate-950/60 text-slate-300 border border-slate-850 rounded-md font-semibold text-[10px]">Efectivo</span>
                                @elseif($sale->payment_method === 'card')
                                    <span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-semibold text-[10px]">Tarjeta</span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-md font-semibold text-[10px]">Transf.</span>
                                @endif
                            </td>
                            @php
                                $effectiveRate = ($sale->exchange_rate && (float)$sale->exchange_rate > 1.0001)
                                    ? (float)$sale->exchange_rate
                                    : (float)\App\Services\ExchangeRateService::getCurrentRate($sale->gym_id);

                                $effectiveVes = ($sale->total_amount_ves && (float)$sale->total_amount_ves > 0)
                                    ? (float)$sale->total_amount_ves
                                    : ((float)$sale->total_amount * $effectiveRate);
                            @endphp
                            <td class="p-4 text-right whitespace-nowrap">
                                <span class="block font-mono font-black text-lime-400 text-sm">${{ number_format($sale->total_amount, 2) }}</span>
                                <span class="block font-mono font-bold text-emerald-400 text-[10px]">Bs. {{ number_format($effectiveVes, 2, ',', '.') }}</span>
                            </td>
                            <td class="p-4 text-center pr-6 whitespace-nowrap">
                                @if(!empty($sale->notes))
                                    @php
                                        $clientName = $sale->client ? (($sale->client->profile->first_name ?? '') . ' ' . ($sale->client->profile->last_name ?? '')) : 'Cliente General';
                                    @endphp
                                    <button type="button" onclick='openSaleNoteModal("#{{ str_pad($sale->id, 5, "0", STR_PAD_LEFT) }}", @json($sale->notes), @json($clientName), "{{ \Carbon\Carbon::parse($sale->createdAt)->format("d/m/Y H:i") }}")' class="p-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-xl transition-all cursor-pointer inline-flex items-center gap-1 text-[10px] font-bold" title="Ver Nota Adicional">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Nota</span>
                                    </button>
                                @else
                                    <span class="text-slate-600 font-bold italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500 font-bold">
                                No se ha registrado ninguna venta en caja todavía.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="no_sales_search_row" class="hidden">
                        <td colspan="9" class="p-10 text-center text-slate-500">
                            <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-600 mb-2"></i>
                            No se encontraron comprobantes de venta que coincidan con la búsqueda o filtro de fecha.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer (Max 10 per page) -->
        <div id="sales_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-medium text-slate-400">
            <span id="sales_pagination_info">Mostrando ventas...</span>
            <div class="flex items-center gap-2">
                <button type="button" id="prev_sales_page_btn" onclick="changeSalesPage(-1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    Anterior
                </button>
                <span id="sales_page_number_display" class="px-3.5 py-1.5 bg-slate-950 rounded-xl font-bold text-lime-400 border border-slate-850">Página 1</span>
                <button type="button" id="next_sales_page_btn" onclick="changeSalesPage(1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                    Siguiente
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

    </div>
</div>

@push('modals')
    <!-- ================= MODAL: NOTA ADICIONAL DE VENTA ================= -->
    <div id="sale-note-modal" class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl my-auto animate-scale-up space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="font-extrabold text-slate-100 text-base flex items-center gap-2">
                    <i data-lucide="file-text" class="text-amber-400 w-5 h-5"></i>
                    <span id="modal_sale_title">Nota de Venta</span>
                </h3>
                <button onclick="toggleSaleNoteModal()" class="text-slate-400 hover:text-slate-100 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs font-semibold">
                <div class="flex items-center justify-between text-slate-400 bg-slate-950/60 p-3 rounded-2xl border border-slate-850">
                    <div>
                        <span class="block text-[10px] uppercase text-slate-500 font-bold">Cliente</span>
                        <span id="modal_sale_client" class="text-slate-200 font-bold"></span>
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] uppercase text-slate-500 font-bold">Fecha y Hora</span>
                        <span id="modal_sale_date" class="text-slate-300 font-mono"></span>
                    </div>
                </div>

                <div>
                    <span class="block text-slate-400 uppercase text-[10px] font-extrabold tracking-wider mb-1.5">Nota Adicional Registrada:</span>
                    <div id="modal_sale_note" class="bg-slate-950 border border-slate-800 rounded-2xl p-4 text-slate-200 text-xs leading-relaxed whitespace-pre-wrap min-h-[90px] font-sans"></div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end">
                <button type="button" onclick="toggleSaleNoteModal()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold text-xs rounded-xl border border-slate-700/50 transition-colors cursor-pointer">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
@endpush

<script>
    let currentSalesPage = 1;
    const salesPerPage = 10;
    let matchingSalesRows = [];

    function onSalesPeriodFilterChange() {
        const period = document.getElementById('sales_period_filter').value;
        const customContainer = document.getElementById('sales_custom_date_container');
        if (period === 'custom') {
            customContainer.classList.remove('hidden');
        } else {
            customContainer.classList.add('hidden');
            applySalesFilters();
        }
    }

    function applySalesFilters() {
        const period = document.getElementById('sales_period_filter').value;
        const query = (document.getElementById('sales_search_input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-sale-row]'));

        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];

        let periodStart = null;
        let periodEnd = null;

        if (period === 'today') {
            periodStart = todayStr;
            periodEnd = todayStr;
        } else if (period === 'this_week') {
            const dayOfWeek = now.getDay() || 7;
            const monday = new Date(now);
            monday.setDate(now.getDate() - (dayOfWeek - 1));
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            periodStart = monday.toISOString().split('T')[0];
            periodEnd = sunday.toISOString().split('T')[0];
        } else if (period === 'last_week') {
            const dayOfWeek = now.getDay() || 7;
            const monday = new Date(now);
            monday.setDate(now.getDate() - (dayOfWeek - 1) - 7);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            periodStart = monday.toISOString().split('T')[0];
            periodEnd = sunday.toISOString().split('T')[0];
        } else if (period === 'this_month') {
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
            periodStart = `${year}-${month}-01`;
            periodEnd = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
        } else if (period === 'custom') {
            periodStart = document.getElementById('sales_start_date')?.value || null;
            periodEnd = document.getElementById('sales_end_date')?.value || null;
        }

        matchingSalesRows = rows.filter(row => {
            const saleDate = row.getAttribute('data-date') || '';
            const saleId = row.getAttribute('data-sale-id') || '';
            const client = row.getAttribute('data-client') || '';
            const seller = row.getAttribute('data-seller') || '';
            const items = row.getAttribute('data-items') || '';
            const amount = row.getAttribute('data-amount') || '';

            // Period check
            let matchesPeriod = true;
            if (periodStart && periodEnd) {
                matchesPeriod = (saleDate >= periodStart && saleDate <= periodEnd);
            } else if (periodStart) {
                matchesPeriod = (saleDate >= periodStart);
            }

            // Search query check (articles, seller, client, ID, amount)
            let matchesQuery = true;
            if (query) {
                matchesQuery = items.includes(query) || 
                               seller.includes(query) || 
                               client.includes(query) || 
                               saleId.includes(query) || 
                               amount.includes(query);
            }

            return matchesPeriod && matchesQuery;
        });

        currentSalesPage = 1;
        renderSalesPage();
    }

    function renderSalesPage() {
        const rows = Array.from(document.querySelectorAll('[data-sale-row]'));
        const totalMatching = matchingSalesRows.length;
        const totalPages = Math.ceil(totalMatching / salesPerPage) || 1;

        if (currentSalesPage > totalPages) currentSalesPage = totalPages;
        if (currentSalesPage < 1) currentSalesPage = 1;

        // Hide all rows first
        rows.forEach(r => r.classList.add('hidden'));

        // Show page slice
        const startIndex = (currentSalesPage - 1) * salesPerPage;
        const endIndex = startIndex + salesPerPage;
        const pageSlice = matchingSalesRows.slice(startIndex, endIndex);

        pageSlice.forEach(r => r.classList.remove('hidden'));

        // Empty state row handler
        const emptyRow = document.getElementById('no_sales_search_row');
        if (emptyRow) {
            if (totalMatching === 0 && rows.length > 0) {
                emptyRow.classList.remove('hidden');
            } else {
                emptyRow.classList.add('hidden');
            }
        }

        // Update UI info
        const infoSpan = document.getElementById('sales_pagination_info');
        const pageDisplay = document.getElementById('sales_page_number_display');
        const prevBtn = document.getElementById('prev_sales_page_btn');
        const nextBtn = document.getElementById('next_sales_page_btn');

        if (infoSpan) {
            if (totalMatching === 0) {
                infoSpan.textContent = "No hay ventas registradas para la búsqueda.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalMatching);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalMatching} ventas`;
            }
        }

        if (pageDisplay) pageDisplay.textContent = `Página ${currentSalesPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentSalesPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentSalesPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function changeSalesPage(delta) {
        currentSalesPage += delta;
        renderSalesPage();
    }

    function toggleSaleNoteModal() {
        const modal = document.getElementById('sale-note-modal');
        if (modal) modal.classList.toggle('hidden');
    }

    function openSaleNoteModal(saleId, noteText, clientName, saleDate) {
        document.getElementById('modal_sale_title').textContent = 'Nota Adicional - Venta ' + saleId;
        document.getElementById('modal_sale_client').textContent = clientName || 'Cliente General';
        document.getElementById('modal_sale_date').textContent = saleDate || '';
        document.getElementById('modal_sale_note').textContent = noteText || '(Sin contenido)';

        toggleSaleNoteModal();
    }

    function initVentasPagination() {
        const rows = Array.from(document.querySelectorAll('[data-sale-row]'));
        matchingSalesRows = rows;
        renderSalesPage();
    }

    // Run pagination immediately on script evaluation & SPA load
    initVentasPagination();

    document.addEventListener('DOMContentLoaded', initVentasPagination);
    window.addEventListener('page:loaded', initVentasPagination);
</script>
@endsection
