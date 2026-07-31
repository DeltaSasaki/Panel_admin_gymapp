@extends('layouts.admin')

@section('title', 'Centro de Notificaciones & Comunicados')

@section('content')
<div class="space-y-6">

    <!-- Top Header Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-850 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <span>Comunicación</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-lime-400 font-semibold">Centro de Notificaciones</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight flex items-center gap-2.5">
                <i data-lucide="bell" class="w-7 h-7 text-lime-400"></i> Centro de Notificaciones & Comunicados
            </h1>
            <p class="text-xs text-slate-400 mt-1">Envía avisos urgentes a tus socios, recordatorios automáticos de gym y alertas de membresía.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="runAutoTriggers()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-850 text-slate-200 border border-slate-800 font-bold text-xs rounded-xl transition-colors flex items-center gap-2 cursor-pointer shadow-sm">
                <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> Ejecutar Alertas Automáticas
            </button>

            <button type="button" onclick="toggleModal('send-manual-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="send" class="w-4 h-4"></i> Redactar Notificación URGENTE
            </button>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Total Notificaciones</span>
                <h3 class="text-2xl font-black text-slate-100 mt-1">{{ $totalSent }}</h3>
                <span class="text-[11px] text-slate-500">Histórico de avisos emitidos</span>
            </div>
            <div class="p-3 bg-lime-500/10 border border-lime-500/20 text-lime-400 rounded-2xl">
                <i data-lucide="bell" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Pendientes de Leer</span>
                <h3 class="text-2xl font-black text-rose-400 mt-1">{{ $unreadCount }}</h3>
                <span class="text-[11px] text-slate-500">Notificaciones no leídas por socios</span>
            </div>
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl">
                <i data-lucide="mail-warning" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Notificaciones Leídas</span>
                <h3 class="text-2xl font-black text-emerald-400 mt-1">{{ $readCount }}</h3>
                <span class="text-[11px] text-slate-500">Confirmadas por clientes</span>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl">
                <i data-lucide="check-check" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Broadcasts Manuales</span>
                <h3 class="text-2xl font-black text-sky-400 mt-1">{{ $manualBroadcasts }}</h3>
                <span class="text-[11px] text-slate-500">Avisos masivos enviados por admin</span>
            </div>
            <div class="p-3 bg-sky-500/10 border border-sky-500/20 text-sky-400 rounded-2xl">
                <i data-lucide="megaphone" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="flex border-b border-slate-800 gap-2">
        <button type="button" onclick="switchNotifTab('historial')" id="tab-btn-historial" class="px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold transition-all border-lime-500 text-lime-400 focus:outline-none">
            <i data-lucide="inbox" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Bitácora & Historial ({{ $notifications->count() }})
        </button>
        <button type="button" onclick="switchNotifTab('broadcast')" id="tab-btn-broadcast" class="px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none">
            <i data-lucide="megaphone" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Envío Masivo / Broadcast
        </button>
        <button type="button" onclick="switchNotifTab('automaticas')" id="tab-btn-automaticas" class="px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none">
            <i data-lucide="sliders" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Disparadores Automáticos
        </button>
    </div>

    <!-- TAB 1: Historial de Notificaciones -->
    <div id="tab-content-historial" class="space-y-4">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
            <!-- Card Header: Title, Search & Filters -->
            <div class="p-5 border-b border-slate-850 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-lime-500/10 border border-lime-500/20 text-lime-400 rounded-2xl">
                        <i data-lucide="list" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-100">Registro de Notificaciones Recientes</h3>
                        <p class="text-xs text-slate-400">Histórico de notificaciones emitidas y su estado de lectura.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                        <button type="button" onclick="setNotifStatusFilter('all')" id="notif-filter-btn-all" class="notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all cursor-pointer">
                            Todas ({{ $notifications->count() }})
                        </button>
                        <button type="button" onclick="setNotifStatusFilter('unread')" id="notif-filter-btn-unread" class="notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer">
                            Pendientes ({{ $unreadCount }})
                        </button>
                        <button type="button" onclick="setNotifStatusFilter('read')" id="notif-filter-btn-read" class="notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer">
                            Leídas ({{ $readCount }})
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-56">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" id="notif_search_input" oninput="onNotifSearchInput()" placeholder="Buscar por título, socio..." class="w-full pl-9 pr-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 font-medium">
                    </div>

                    <!-- Redesigned Mark All as Read Button -->
                    <button type="button" onclick="toggleModal('mark-all-read-modal')" class="px-3.5 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-xl text-xs font-extrabold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-95">
                        <i data-lucide="check-check" class="w-4 h-4"></i> Marcar Todas como Leídas
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-950/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-850">
                            <th class="p-4 pl-6">Fecha / Hora</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Socio Destinatario</th>
                            <th class="p-4">Título y Mensaje</th>
                            <th class="p-4 pr-6 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="notifications_table_body" class="divide-y divide-slate-850">
                        @forelse($notifications as $notif)
                            @php
                                $member = $notif->user;
                                $memberName = $member ? trim(($member->profile->first_name ?? '') . ' ' . ($member->profile->last_name ?? '')) : 'Socio ID #' . $notif->user_id;
                                if (empty($memberName)) $memberName = $member->email ?? 'Socio';
                                $searchText = strtolower($notif->title . ' ' . $notif->body . ' ' . $memberName);
                            @endphp
                            <tr data-notif-row 
                                data-status="{{ $notif->is_read ? 'read' : 'unread' }}" 
                                data-search="{{ $searchText }}" 
                                class="hover:bg-slate-900/40 transition-colors {{ $loop->index >= 10 ? 'hidden' : '' }}">
                                <td class="p-4 pl-6 font-bold text-slate-200">
                                    {{ \Carbon\Carbon::parse($notif->createdAt)->format('d/m/Y H:i A') }}
                                </td>
                                <td class="p-4">
                                    @if($notif->type === 'membership_expiry')
                                        <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[10px] font-extrabold uppercase rounded-full border border-rose-500/20">💳 Vencimiento</span>
                                    @elseif($notif->type === 'payment_reminder')
                                        <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 text-[10px] font-extrabold uppercase rounded-full border border-amber-500/20">🏋️ Recordatorio Gym</span>
                                    @elseif($notif->type === 'new_routine')
                                        <span class="px-2.5 py-0.5 bg-lime-500/10 text-lime-400 text-[10px] font-extrabold uppercase rounded-full border border-lime-500/20">📋 Nueva Rutina</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-sky-500/10 text-sky-400 text-[10px] font-extrabold uppercase rounded-full border border-sky-500/20">📢 Broadcast Manual</span>
                                    @endif
                                </td>
                                <td class="p-4 font-bold text-slate-100">
                                    {{ $memberName }}
                                </td>
                                <td class="p-4 max-w-md whitespace-normal">
                                    <span class="block font-bold text-slate-100 text-xs">{{ $notif->title }}</span>
                                    <span class="text-slate-400 text-[11px] line-clamp-2 mt-0.5">{{ $notif->body }}</span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    @if($notif->is_read)
                                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold uppercase rounded-full border border-emerald-500/20">Leída ✓</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-slate-950 text-slate-400 text-[10px] font-extrabold uppercase rounded-full border border-slate-800">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500 font-semibold italic">
                                    No hay notificaciones registradas en la bitácora.
                                </td>
                            </tr>
                        @endforelse

                        <tr id="no_notifications_search_row" class="hidden">
                            <td colspan="5" class="p-12 text-center text-slate-500 font-semibold">
                                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400 text-sm">No se encontraron notificaciones que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer Controls -->
            <div id="notif_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 bg-slate-950/40">
                <span id="notif_pagination_info">Mostrando notificaciones...</span>
                <div class="flex items-center gap-2">
                    <button type="button" id="notif_prev_btn" onclick="changeNotifPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed font-bold transition-colors">
                        Anterior
                    </button>
                    <span id="notif_page_number_display" class="font-bold text-lime-400 px-3 py-1.5 bg-slate-950 rounded-xl border border-slate-850">Página 1</span>
                    <button type="button" id="notif_next_btn" onclick="changeNotifPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed font-bold transition-colors">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Formulario de Envíos Masivos / Broadcast -->
    <div id="tab-content-broadcast" class="hidden space-y-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 sm:p-7 max-w-2xl mx-auto shadow-xl space-y-6">
            <div class="border-b border-slate-850 pb-4">
                <h3 class="font-extrabold text-lg text-slate-100 flex items-center gap-2">
                    <i data-lucide="megaphone" class="w-5 h-5 text-lime-400"></i> Enviar Aviso o Notificación URGENTE
                </h3>
                <p class="text-xs text-slate-400 mt-1">Redacta avisos de último momento para ser entregados instantáneamente a la app de tus socios.</p>
            </div>

            <!-- Plantillas de Mensaje Rápido -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Plantillas de Mensaje Rápido</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="applyTemplate('clase_dj')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5">
                        🎉 Evento Especial Hoy
                    </button>
                    <button type="button" onclick="applyTemplate('mantenimiento')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5">
                        ⚡ Aviso Mantenimiento
                    </button>
                    <button type="button" onclick="applyTemplate('motivacion')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5">
                        🔥 Motivación Gym
                    </button>
                </div>
            </div>

            <form action="{{ route('notificaciones.send_manual') }}" method="POST" onsubmit="submitManualNotifForm(event)" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Grupo Destinatario *</label>
                    <select name="target_type" id="broadcast_target_type" onchange="toggleSpecificUserSelect(this.value)" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs font-bold text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="all">📢 Todos los Socios del Gimnasio</option>
                        <option value="inactive">🚨 Socios Inactivos (Sin check-in hace > 5 días)</option>
                        <option value="specific">👤 Socio Específico (Mensaje individual)</option>
                    </select>
                </div>

                <div id="specific_user_wrapper" class="hidden">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Seleccionar Socio *</label>
                    <select name="user_id" id="broadcast_user_id" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs font-bold text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        @foreach($members as $m)
                            @php
                                $name = trim(($m->profile->first_name ?? '') . ' ' . ($m->profile->last_name ?? ''));
                                if (empty($name)) $name = $m->email;
                            @endphp
                            <option value="{{ $m->id }}">{{ $name }} (ID #{{ $m->id }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Título de la Notificación *</label>
                    <input type="text" name="title" id="broadcast_title" required placeholder="Ej: 🎉 ¡Clase Especial con DJ Hoy a las 6:00 PM!" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Cuerpo del Mensaje *</label>
                    <textarea name="body" id="broadcast_body" rows="4" required placeholder="Escribe aquí el mensaje detallado para la app del cliente..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-850 flex items-center justify-end">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i> Confirmar y Enviar Notificación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: Disparadores Automáticos del Sistema -->
    <div id="tab-content-automaticas" class="hidden space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            <!-- Rule 1: Recordatorio de Horario de Entrenamiento -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-lime-500/10 text-lime-400 rounded-2xl border border-lime-500/20">
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-100 text-sm">Recordatorios de Horario Gym</h4>
                            <span class="text-[11px] text-slate-400">Automatización por hora preferida</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/20">ACTIVO</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Envía notificaciones automáticas al socio a la hora del día en que suele asistir al gym (*ej: "🏋️ ¡Hora de entrenar! Tu horario recomendado es a las 6:00 PM"*).
                </p>
            </div>

            <!-- Rule 2: Vencimiento de Membresía -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-rose-500/10 text-rose-400 rounded-2xl border border-rose-500/20">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-100 text-sm">Alerta de Vencimiento Membresía</h4>
                            <span class="text-[11px] text-slate-400">Disparador 3 días antes de expirar</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/20">ACTIVO</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Avisa al socio cuando faltan 3 días para vencer su suscripción, invitándolo a renovar en recepción o en línea para evitar suspensiones.
                </p>
            </div>

            <!-- Rule 3: Reactivación por Inactividad -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-amber-500/10 text-amber-400 rounded-2xl border border-amber-500/20">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-100 text-sm">Reactivación de Socios Inactivos</h4>
                            <span class="text-[11px] text-slate-400">Sin asistencia por > 5 días</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/20">ACTIVO</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Envía un mensaje motivacional de reactivación a los socios ausentes que llevan más de 5 días sin marcar asistencia.
                </p>
            </div>

            <!-- Rule 4: Nueva Rutina o Dieta -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-sky-500/10 text-sky-400 rounded-2xl border border-sky-500/20">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-100 text-sm">Asignación de Rutinas y Planes</h4>
                            <span class="text-[11px] text-slate-400">Notificación al instante</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/20">ACTIVO</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Notifica al socio inmediatamente cuando su entrenador le asigna una nueva rutina o actualiza su plan de alimentación.
                </p>
            </div>

        </div>
    </div>

</div>

<!-- Modal: Redacción Rápida / Envío Manual -->
<div id="send-manual-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 sm:p-7 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
            <h3 class="font-extrabold text-base text-slate-100 flex items-center gap-2">
                <i data-lucide="send" class="w-5 h-5 text-lime-400"></i> Notificación Manual Rápida
            </h3>
            <button type="button" onclick="toggleModal('send-manual-modal')" class="text-slate-400 hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('notificaciones.send_manual') }}" method="POST" onsubmit="submitManualNotifForm(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block uppercase text-slate-400 mb-1">Destinatarios</label>
                <select name="target_type" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none">
                    <option value="all">📢 Todos los Socios Activos del Gym</option>
                    <option value="inactive">🚨 Socios Inactivos (> 5 Días)</option>
                </select>
            </div>

            <div>
                <label class="block uppercase text-slate-400 mb-1">Título</label>
                <input type="text" name="title" required placeholder="Ej: ¡Aviso Importante del Gym!" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none">
            </div>

            <div>
                <label class="block uppercase text-slate-400 mb-1">Mensaje</label>
                <textarea name="body" rows="3" required placeholder="Escribe el mensaje..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-850">
                <button type="button" onclick="toggleModal('send-manual-modal')" class="px-4 py-2 bg-slate-950 border border-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg">Enviar Ahora</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirmar Marcar Todas como Leídas -->
<div id="mark-all-read-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl">
                    <i data-lucide="check-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Marcar Notificaciones</h3>
                    <p class="text-xs text-slate-400">Confirmación de acción en lote</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('mark-all-read-modal')" class="p-1 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-850 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
            ¿Estás seguro de que deseas marcar <strong class="text-emerald-400 font-extrabold">todas las notificaciones pendientes como leídas</strong>? Esta acción actualizará el estado del historial.
        </p>

        <form action="{{ route('notificaciones.mark_all_read') }}" method="POST" class="flex items-center gap-3 pt-2">
            @csrf
            <button type="button" onclick="toggleModal('mark-all-read-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-850 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl border border-slate-800 transition-colors cursor-pointer">
                Cancelar
            </button>
            <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="check" class="w-4 h-4"></i> Sí, Marcar Todas
            </button>
        </form>
    </div>
</div>

<script>
    // Centered Static Modal Handler (relocates element to document.body and freezes scroll)
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const isOpening = modal.classList.contains('hidden');
        modal.classList.toggle('hidden');

        if (isOpening) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    }

    function switchNotifTab(tabName) {
        document.getElementById('tab-content-historial').classList.add('hidden');
        document.getElementById('tab-content-broadcast').classList.add('hidden');
        document.getElementById('tab-content-automaticas').classList.add('hidden');

        document.getElementById('tab-btn-historial').className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none";
        document.getElementById('tab-btn-broadcast').className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none";
        document.getElementById('tab-btn-automaticas').className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none";

        document.getElementById(`tab-content-${tabName}`).classList.remove('hidden');
        document.getElementById(`tab-btn-${tabName}`).className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold transition-all border-lime-500 text-lime-400 focus:outline-none";
    }

    function toggleSpecificUserSelect(val) {
        const wrapper = document.getElementById('specific_user_wrapper');
        if (val === 'specific') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function applyTemplate(type) {
        const titleEl = document.getElementById('broadcast_title');
        const bodyEl = document.getElementById('broadcast_body');

        if (type === 'clase_dj') {
            titleEl.value = "🎉 ¡Clase Especial con DJ en Vivo Hoy!";
            bodyEl.value = "¡Hola equipo! Hoy a las 6:00 PM tendremos Masterclass de Spinning con DJ en vivo y sorpresas. ¡No faltes!";
        } else if (type === 'mantenimiento') {
            titleEl.value = "⚡ Aviso de Mantenimiento Mínimo";
            bodyEl.value = "Estimados socios, el área de saunas estará en mantenimiento preventivo este jueves de 2:00 PM a 4:00 PM. El resto de áreas opera con normalidad.";
        } else if (type === 'motivacion') {
            titleEl.value = "🔥 ¡Hoy es un Gran Día para Entrenar!";
            bodyEl.value = "La disciplina se demuestra viniendo cuando menos ganas tienes. ¡Te esperamos en el gym para dar tu 100% hoy!";
        }
    }

    async function submitManualNotifForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                if (typeof toggleModal === 'function') {
                    toggleModal('send-manual-modal');
                }
                if (typeof showNotification === 'function') {
                    showNotification('¡Enviado!', data.message, 'success');
                }
                if (typeof window.loadUrl === 'function') {
                    window.loadUrl(window.location.href, false);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Error al enviar la notificación.');
            }
        } catch (err) {
            console.error(err);
            alert('Error en el servidor al enviar la notificación.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmar y Enviar Notificación';
        }
    }

    async function runAutoTriggers() {
        try {
            const response = await fetch("{{ route('notificaciones.run_triggers') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification('¡Sistema Actualizado!', data.message, 'success');
                }
                if (typeof window.loadUrl === 'function') {
                    window.loadUrl(window.location.href, false);
                } else {
                    window.location.reload();
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Notifications History Filtering & Pagination (Max 10 per page)
    let currentNotifPage = 1;
    let currentNotifStatusFilter = 'all';
    const notifPerPage = 10;

    function setNotifStatusFilter(status) {
        currentNotifStatusFilter = status;
        document.querySelectorAll('.notif-status-tab-btn').forEach(btn => {
            btn.className = "notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer";
        });
        const activeBtn = document.getElementById('notif-filter-btn-' + status);
        if (activeBtn) {
            activeBtn.className = "notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all cursor-pointer";
        }
        currentNotifPage = 1;
        renderNotifPage();
    }

    function renderNotifPage() {
        const query = (document.getElementById('notif_search_input')?.value || '').toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('[data-notif-row]'));

        const matchingRows = allRows.filter(row => {
            const status = row.getAttribute('data-status') || '';
            const search = row.getAttribute('data-search') || '';

            let matchesStatus = true;
            if (currentNotifStatusFilter === 'read') matchesStatus = (status === 'read');
            else if (currentNotifStatusFilter === 'unread') matchesStatus = (status === 'unread');

            let matchesSearch = true;
            if (query) matchesSearch = search.includes(query);

            return matchesStatus && matchesSearch;
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.ceil(totalMatching / notifPerPage) || 1;

        if (currentNotifPage > totalPages) currentNotifPage = totalPages;
        if (currentNotifPage < 1) currentNotifPage = 1;

        allRows.forEach(row => row.classList.add('hidden'));

        const startIndex = (currentNotifPage - 1) * notifPerPage;
        const endIndex = startIndex + notifPerPage;
        const pageRows = matchingRows.slice(startIndex, endIndex);

        pageRows.forEach(row => row.classList.remove('hidden'));

        const emptySearchRow = document.getElementById('no_notifications_search_row');
        if (emptySearchRow) {
            if (totalMatching === 0 && allRows.length > 0) emptySearchRow.classList.remove('hidden');
            else emptySearchRow.classList.add('hidden');
        }

        const infoSpan = document.getElementById('notif_pagination_info');
        if (infoSpan) {
            const from = totalMatching === 0 ? 0 : startIndex + 1;
            const to = Math.min(endIndex, totalMatching);
            infoSpan.textContent = `Mostrando ${from} a ${to} de ${totalMatching} notificaciones`;
        }

        const pageDisplay = document.getElementById('notif_page_number_display');
        if (pageDisplay) pageDisplay.textContent = `Página ${currentNotifPage} de ${totalPages}`;

        const prevBtn = document.getElementById('notif_prev_btn');
        if (prevBtn) prevBtn.disabled = (currentNotifPage <= 1);

        const nextBtn = document.getElementById('notif_next_btn');
        if (nextBtn) nextBtn.disabled = (currentNotifPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function onNotifSearchInput() {
        currentNotifPage = 1;
        renderNotifPage();
    }

    function changeNotifPage(delta) {
        currentNotifPage += delta;
        renderNotifPage();
    }

    function initNotifPagination() {
        renderNotifPage();
    }

    initNotifPagination();

    if (document.readyState !== 'loading') {
        initNotifPagination();
    } else {
        document.addEventListener('DOMContentLoaded', initNotifPagination);
    }

    window.addEventListener('load', initNotifPagination);
    window.addEventListener('pageshow', initNotifPagination);
    window.addEventListener('page:loaded', initNotifPagination);
    document.addEventListener('livewire:navigated', initNotifPagination);
    document.addEventListener('turbo:load', initNotifPagination);
</script>
@endsection
