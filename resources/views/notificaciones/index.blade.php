@extends('layouts.admin')

@section('title', 'Historial de Notificaciones')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Historial de Notificaciones</h1>
            <p class="text-xs text-slate-400 mt-1">Consulta y gestiona todas tus alertas y avisos del sistema basados en tu rol.</p>
        </div>
        @if($notifications->where('is_read', 0)->count() > 0)
            <form action="{{ route('notificaciones.read_all') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-lime-450 hover:text-lime-300 font-bold text-xs rounded-xl transition-all flex items-center gap-2">
                    <i data-lucide="check-check" class="w-4 h-4"></i> Marcar todas como leídas
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Notifications List -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-850 flex items-center justify-between">
            <h3 class="font-bold text-lg text-slate-100">Bandeja de Entrada</h3>
            <span class="text-xs text-slate-500">Página {{ $notifications->currentPage() }} de {{ $notifications->lastPage() }}</span>
        </div>
        
        <div class="divide-y divide-slate-850/60">
            @forelse($notifications as $n)
                @php
                    $isUnread = !$n->is_read;
                    $bgClass = $isUnread ? 'bg-slate-900/50 border-l-4 border-lime-500' : 'hover:bg-slate-900/10 opacity-75';
                    
                    $iconColor = 'text-lime-400 bg-lime-500/10';
                    $icon = 'bell';
                    $label = 'General';
                    
                    if ($n->type === 'membership_expiry') {
                        $iconColor = 'text-rose-400 bg-rose-500/10';
                        $icon = 'alert-triangle';
                        $label = 'Vencimiento de Membresía';
                    } elseif ($n->type === 'payment_reminder') {
                        $iconColor = 'text-amber-400 bg-amber-500/10';
                        $icon = 'dollar-sign';
                        $label = 'Recordatorio de Pago';
                    } elseif ($n->type === 'new_routine') {
                        $iconColor = 'text-purple-400 bg-purple-500/10';
                        $icon = 'dumbbell';
                        $label = 'Nueva Rutina';
                    } elseif ($n->type === 'achievement') {
                        $iconColor = 'text-yellow-400 bg-yellow-500/10';
                        $icon = 'trophy';
                        $label = 'Logro / Meta';
                    }
                @endphp
                
                <div class="p-5 transition-all {{ $bgClass }} flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex gap-4 items-start flex-1">
                        <div class="p-3 rounded-xl {{ $iconColor }} shrink-0 mt-0.5">
                            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-slate-100">{{ $n->title }}</span>
                                @if($isUnread)
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/25 rounded-full text-[9px] font-bold uppercase">Nuevo</span>
                                @endif
                                <span class="px-2 py-0.5 bg-slate-950 text-slate-500 border border-slate-850 rounded-md text-[9px] font-semibold uppercase">{{ $label }}</span>
                            </div>
                            <p class="text-xs text-slate-350 leading-relaxed">{{ $n->body }}</p>
                            <span class="block text-[10px] text-slate-500 font-bold uppercase pt-1">{{ \Carbon\Carbon::parse($n->createdAt)->diffForHumans() }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center sm:self-center shrink-0">
                        <a href="{{ route('notificaciones.read_and_redirect', $n->id) }}" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-850 text-xs font-bold rounded-xl border border-slate-850 text-slate-300 transition-colors flex items-center gap-1.5">
                            Ver Detalles <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-550">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                    <h4 class="font-bold text-slate-400">Sin notificaciones</h4>
                    <p class="text-xs text-slate-500 mt-1">Estás al día. No se han registrado notificaciones para tu cuenta.</p>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination block -->
        @if($notifications->hasPages())
            <div class="p-6 border-t border-slate-850 flex items-center justify-between">
                <div>
                    {{ $notifications->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
