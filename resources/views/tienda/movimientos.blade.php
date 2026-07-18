@extends('layouts.admin')

@section('title', 'Auditoría de Movimientos de Stock')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Auditoría de Movimientos</h1>
            <p class="text-xs text-slate-400 mt-1">Historial cronológico completo de entradas, salidas y ajustes de stock en almacén.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tienda.products') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-800 text-slate-200 transition-colors flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-lime-400"></i> Ver Inventario
            </a>
        </div>
    </div>

    <!-- Stock Movements Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850">
            <h3 class="font-bold text-lg text-white">Registro de Auditoría de Almacén</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-950/40 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                        <th class="p-4 pl-6">Producto</th>
                        <th class="p-4 text-center">Tipo</th>
                        <th class="p-4 text-center">Cantidad</th>
                        <th class="p-4">Motivo / Detalle</th>
                        <th class="p-4 text-center">Operado Por</th>
                        <th class="p-4 text-right pr-6 w-44">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-850/50">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-slate-900/20 text-slate-200">
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <img src="{{ $mov->product->image_url ? asset($mov->product->image_url) : 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=150&auto=format&fit=crop' }}" class="w-8 h-8 rounded-lg object-cover border border-slate-800 shrink-0">
                                <span class="font-bold text-slate-100">{{ $mov->product->name }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($mov->movement_type === 'in')
                                    <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded font-black text-[9px] uppercase tracking-wide">
                                        Entrada
                                    </span>
                                @elseif($mov->movement_type === 'out')
                                    <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded font-black text-[9px] uppercase tracking-wide">
                                        Salida
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded font-black text-[9px] uppercase tracking-wide">
                                        Ajuste
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-mono font-bold text-slate-100">
                                {{ $mov->quantity }}
                            </td>
                            <td class="p-4 text-slate-400 font-semibold">
                                {{ $mov->reason ?? 'Carga de stock' }}
                            </td>
                            <td class="p-4 text-center text-slate-300">
                                {{ $mov->performer ? $mov->performer->profile->first_name . ' ' . $mov->performer->profile->last_name : 'Sistema' }}
                            </td>
                            <td class="p-4 text-right pr-6 font-mono text-slate-500">
                                {{ \Carbon\Carbon::parse($mov->createdAt)->format('d/m/Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-550 italic font-semibold">
                                No se han registrado movimientos de inventario todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
