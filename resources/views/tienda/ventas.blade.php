@extends('layouts.admin')

@section('title', 'Historial de Ventas')

@section('content')
<div class="space-y-8">
    
    <!-- Top Header -->
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Historial de Ventas</h1>
        <p class="text-xs text-slate-400 mt-1">Monitorea los comprobantes de caja y transacciones de productos de la tienda.</p>
    </div>

    <!-- Sales Receipts Logs -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-white">Transacciones Registradas</h3>
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
                        <th class="p-4 text-right pr-6">Monto Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 font-mono font-bold text-lime-400">#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="p-4 text-slate-400">{{ \Carbon\Carbon::parse($sale->createdAt)->format('d/m/Y H:i') }}</td>
                            <td class="p-4">
                                @if($sale->client)
                                    <span class="block font-bold text-slate-100">{{ $sale->client->profile->first_name }} {{ $sale->client->profile->last_name }}</span>
                                    <span class="block text-[10px] text-slate-550">{{ $sale->client->email }}</span>
                                @else
                                    <span class="text-slate-500 font-medium italic">Cliente General</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-300">
                                {{ $sale->seller ? 'Coach ' . $sale->seller->profile->first_name : 'Sistema' }}
                            </td>
                            <td class="p-4">
                                <ul class="space-y-0.5 list-inside list-disc text-slate-400 text-[10px]">
                                    @foreach($sale->items as $item)
                                        <li>{{ $item->product->name }} <strong class="text-slate-200">x{{ $item->quantity }}</strong></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="p-4 text-center">
                                @if($sale->promoCode)
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded font-black tracking-wide text-[9px] uppercase">
                                        {{ $sale->promoCode->code }}
                                    </span>
                                @else
                                    <span class="text-slate-600 font-bold italic">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($sale->payment_method === 'cash')
                                    <span class="px-2 py-0.5 bg-slate-950/60 text-slate-300 border border-slate-850 rounded-md font-semibold text-[10px]">Efectivo</span>
                                @elseif($sale->payment_method === 'card')
                                    <span class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md font-semibold text-[10px]">Tarjeta</span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-md font-semibold text-[10px]">Transf.</span>
                                @endif
                            </td>
                            <td class="p-4 text-right pr-6 font-mono font-black text-lime-400 text-sm">
                                ${{ number_format($sale->total_amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-550">
                                No se ha registrado ninguna venta en caja todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
