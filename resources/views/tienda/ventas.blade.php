@extends('layouts.admin')

@section('title', 'Historial de Ventas')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Historial de Ventas</h1>
        <p class="text-xs text-slate-400 mt-1">Monitorea los comprobantes de caja y transacciones de productos de la tienda.</p>
    </div>

    <!-- Sales Receipts Logs -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-slate-100">Transacciones Registradas</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
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
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
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
                                {{ $sale->seller ? 'Coach ' . ($sale->seller->profile->first_name ?? $sale->seller->email) : 'Sistema' }}
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
                            <td class="p-4 text-right font-mono font-black text-lime-400 text-sm whitespace-nowrap">
                                ${{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="p-4 text-center pr-6 whitespace-nowrap">
                                @if(!empty($sale->notes))
                                    @php
                                        $clientName = $sale->client ? (($sale->client->profile->first_name ?? '') . ' ' . ($sale->client->profile->last_name ?? '')) : 'Cliente General';
                                    @endphp
                                    <button type="button" onclick="openSaleNoteModal('#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}', {{ json_encode($sale->notes) }}, {{ json_encode($clientName) }}, '{{ \Carbon\Carbon::parse($sale->createdAt)->format('d/m/Y H:i') }}')" class="p-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-xl transition-all cursor-pointer inline-flex items-center gap-1 text-[10px] font-bold" title="Ver Nota Adicional">
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
                </tbody>
            </table>
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
</script>
@endsection
