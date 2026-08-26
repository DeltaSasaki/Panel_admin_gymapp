@extends('layouts.admin')

@section('title', 'Carnet Digital - ' . ($cliente->profile->first_name ?? 'Socio'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('clientes.index') }}" class="hover:text-lime-400 transition-colors">Socios</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('clientes.show', $cliente->id) }}" class="hover:text-lime-400 transition-colors">{{ $cliente->profile->first_name ?? 'Perfil' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-slate-200">Carnet Digital</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clientes.show', $cliente->id) }}" class="px-3.5 py-1.5 bg-slate-900 border border-slate-800 hover:bg-slate-800 text-xs font-bold rounded-xl text-slate-300 transition-colors flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver
            </a>
            <button type="button" onclick="printDigitalCarnet()" class="px-4 py-1.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 text-xs font-extrabold rounded-xl transition-all shadow-lg flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i> Imprimir / PDF
            </button>
        </div>
    </div>

    <!-- Printable Carnet Container -->
    <div id="carnet-printable-area" class="flex justify-center py-4">
        
        <!-- Digital Card (Standard Credit/ID Card Ratio) -->
        <div class="w-full max-w-[420px] bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 border-2 border-lime-500/40 rounded-3xl p-6 shadow-2xl relative overflow-hidden text-slate-100 space-y-5">
            
            <!-- Glow background accents -->
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-lime-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-12 -left-12 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Card Header: Gym SaaS Brand & Status -->
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-lime-500/10 border border-lime-500/30 rounded-xl text-lime-400">
                        <i data-lucide="dumbbell" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-100 line-clamp-1">
                            {{ $cliente->gym->name ?? 'BIGWORLD FITNESS' }}
                        </h2>
                        <span class="text-[9px] font-bold text-lime-400 uppercase tracking-widest block">CARNET VIRTUAL DE ATLETA</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full border {{ $cliente->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30' }}">
                    {{ $cliente->is_active ? 'ACTIVO' : 'INACTIVO' }}
                </span>
            </div>

            <!-- Card Body: Photo & Member Details -->
            <div class="grid grid-cols-12 gap-4 items-center">
                <!-- Member Photo -->
                <div class="col-span-4 flex flex-col items-center">
                    <img src="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100&auto=format&fit=crop' }}" 
                         alt="Foto Socio" 
                         class="w-24 h-24 rounded-2xl object-cover border-2 border-lime-400/50 shadow-lg ring-2 ring-slate-900">
                    <span class="mt-2 text-[9px] font-mono text-slate-400 font-extrabold tracking-wider">ID: #{{ str_pad($cliente->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>

                <!-- Personal Data -->
                <div class="col-span-8 space-y-2">
                    <div>
                        <span class="block text-[9px] font-extrabold uppercase text-slate-500 tracking-wider">Nombre del Socio</span>
                        <h3 class="text-base font-black text-slate-100 leading-tight">
                            {{ $cliente->profile->first_name ?? 'Socio' }} {{ $cliente->profile->last_name ?? '' }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <span class="block text-[9px] font-extrabold uppercase text-slate-500">DNI / Cédula</span>
                            <span class="text-xs font-mono font-bold text-slate-200">{{ $cliente->profile->dni ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-extrabold uppercase text-slate-500">Membresía</span>
                            <span class="text-xs font-bold text-lime-400">{{ $cliente->activeMembership->plan->name ?? 'Estándar' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Footer: QR Code & Verification Info -->
            <div class="pt-4 border-t border-slate-800/80">
                <div class="flex items-center justify-between gap-4 p-3 bg-slate-950 border border-slate-850 rounded-2xl">
                    <div class="space-y-1">
                        <span class="text-[9px] font-extrabold text-lime-400 uppercase tracking-widest block">Acceso Rápido</span>
                        <p class="text-[11px] text-slate-300 font-bold">Escanee este código QR en el torniquete o recepción para registrar su ingreso.</p>
                        <span class="text-[9px] text-slate-500 font-semibold block">Emitido: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
                    </div>
                    <div class="w-[90px] h-[90px] flex items-center justify-center shrink-0 bg-slate-950 p-1 rounded-xl border border-slate-800 shadow-inner">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function printDigitalCarnet() {
        const content = document.getElementById('carnet-printable-area').innerHTML;
        const win = window.open('', '_blank');
        win.document.write(`
            <html>
                <head>
                    <title>Carnet Digital - {{ $cliente->profile->first_name }} {{ $cliente->profile->last_name }}</title>
                    <script src="https://cdn.tailwindcss.com"><\/script>
                    <style>
                        @page { margin: 10mm; }
                        body { background-color: #0f172a; display: flex; justify-content: center; align-items: center; min-h: 100vh; font-family: sans-serif; }
                    </style>
                </head>
                <body>
                    ${content}
                    <script>
                        window.onload = function() {
                            window.print();
                            window.close();
                        }
                    <\/script>
                </body>
            </html>
        `);
        win.document.close();
    }
</script>
@endsection
