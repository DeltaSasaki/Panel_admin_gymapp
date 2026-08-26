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
            @if(auth()->user()->hasPermission('notificaciones.send'))
                <button type="button" onclick="runAutoTriggers()" id="btn-run-auto-triggers" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-850 text-slate-200 border border-slate-800 font-bold text-xs rounded-xl transition-all flex items-center gap-2 cursor-pointer shadow-sm active:scale-95">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> Ejecutar Alertas Automáticas
                </button>

                <button type="button" onclick="toggleModal('send-manual-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i> Redactar Notificación URGENTE
                </button>
            @endif
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Total Notificaciones</span>
                <h3 class="text-2xl font-black text-slate-100 mt-1" id="stat-total-sent">{{ number_format($totalSent) }}</h3>
                <span class="text-[11px] text-slate-500">Histórico total emitido</span>
            </div>
            <div class="p-3 bg-lime-500/10 border border-lime-500/20 text-lime-400 rounded-2xl">
                <i data-lucide="bell" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Pendientes de Leer</span>
                <h3 class="text-2xl font-black text-rose-400 mt-1" id="stat-unread-count">{{ number_format($unreadCount) }}</h3>
                <span class="text-[11px] text-slate-500">No abiertas por socios</span>
            </div>
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl">
                <i data-lucide="mail-warning" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Notificaciones Leídas</span>
                <h3 class="text-2xl font-black text-emerald-400 mt-1" id="stat-read-count">{{ number_format($readCount) }}</h3>
                <span class="text-[11px] text-slate-500">Confirmadas por clientes</span>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl">
                <i data-lucide="check-check" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Broadcasts Manuales</span>
                <h3 class="text-2xl font-black text-sky-400 mt-1" id="stat-broadcast-count">{{ number_format($manualBroadcasts) }}</h3>
                <span class="text-[11px] text-slate-500">Avisos masivos de admin</span>
            </div>
            <div class="p-3 bg-sky-500/10 border border-sky-500/20 text-sky-400 rounded-2xl">
                <i data-lucide="megaphone" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="flex border-b border-slate-800 gap-2">
        <button type="button" onclick="switchNotifTab('historial')" id="tab-btn-historial" class="px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold transition-all border-lime-500 text-lime-400 focus:outline-none cursor-pointer">
            <i data-lucide="inbox" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Bitácora & Historial (<span id="tab-count-historial">{{ $notifications->count() }}</span>)
        </button>
        <button type="button" onclick="switchNotifTab('broadcast')" id="tab-btn-broadcast" class="px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none cursor-pointer">
            <i data-lucide="megaphone" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i> Envío Masivo / Broadcast
        </button>
        <button type="button" onclick="switchNotifTab('automaticas')" id="tab-btn-automaticas" class="px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none cursor-pointer">
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
                            Todas
                        </button>
                        <button type="button" onclick="setNotifStatusFilter('unread')" id="notif-filter-btn-unread" class="notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer">
                            Pendientes
                        </button>
                        <button type="button" onclick="setNotifStatusFilter('read')" id="notif-filter-btn-read" class="notif-status-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all cursor-pointer">
                            Leídas
                        </button>
                    </div>

                    <!-- Type Filter Dropdown -->
                    <select id="notif_type_filter" onchange="setNotifTypeFilter(this.value)" class="px-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-300 font-bold focus:outline-none focus:border-lime-500/50 cursor-pointer">
                        <option value="all">📌 Todos los Tipos</option>
                        <option value="general">📢 General / Broadcast</option>
                        <option value="membership_expiry">💳 Vencimiento Membresía</option>
                        <option value="payment_reminder">🏋️ Recordatorio Gym</option>
                        <option value="new_routine">📋 Nueva Rutina</option>
                        <option value="achievement">🏆 Logros & Motivación</option>
                    </select>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-56">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" id="notif_search_input" oninput="onNotifSearchInput()" placeholder="Buscar por título, socio..." class="w-full pl-9 pr-3 py-2 text-xs bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 font-medium">
                    </div>

                    <!-- Mark All as Read Button -->
                    <button type="button" onclick="toggleModal('mark-all-read-modal')" class="px-3.5 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-xl text-xs font-extrabold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-95">
                        <i data-lucide="check-check" class="w-4 h-4"></i> Marcar Todas Leídas
                    </button>

                    <!-- Cleanup Old Button -->
                    <button type="button" onclick="toggleModal('cleanup-old-modal')" class="px-3 py-2 bg-slate-950 hover:bg-slate-900 text-slate-400 hover:text-slate-200 border border-slate-850 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer" title="Limpiar notificaciones antiguas">
                        <i data-lucide="trash" class="w-3.5 h-3.5"></i>
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
                            <th class="p-4 text-center">Estado</th>
                            <th class="p-4 pr-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="notifications_table_body" class="divide-y divide-slate-850">
                        @forelse($notifications as $notif)
                            @php
                                $member = $notif->user;
                                $memberName = $member ? trim(($member->profile->first_name ?? '') . ' ' . ($member->profile->last_name ?? '')) : 'Socio ID #' . $notif->user_id;
                                if (empty($memberName)) $memberName = $member->email ?? 'Socio';
                                $memberDni = $member->profile->dni ?? '';
                                $memberPhoto = $member->profile->photo ?? null;
                                if ($memberPhoto && !str_starts_with($memberPhoto, 'http')) {
                                    $memberPhoto = asset('storage/' . $memberPhoto);
                                }
                                if (!$memberPhoto) {
                                    $memberPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($memberName) . '&background=1e293b&color=a3e635';
                                }
                                $planName = $member->activeMembership->plan->name ?? null;
                                $searchText = strtolower($notif->title . ' ' . $notif->body . ' ' . $memberName . ' ' . $memberDni . ' ' . ($member->email ?? ''));
                            @endphp
                            <tr data-notif-row 
                                data-id="{{ $notif->id }}"
                                data-status="{{ $notif->is_read ? 'read' : 'unread' }}" 
                                data-type="{{ $notif->type }}"
                                data-title="{{ e($notif->title) }}"
                                data-body="{{ e($notif->body) }}"
                                data-user-name="{{ e($memberName) }}"
                                data-user-dni="{{ e($memberDni) }}"
                                data-user-photo="{{ $memberPhoto }}"
                                data-user-plan="{{ e($planName ?? 'Sin Plan Activo') }}"
                                data-date="{{ \Carbon\Carbon::parse($notif->createdAt)->format('d/m/Y H:i A') }}"
                                data-search="{{ $searchText }}" 
                                class="hover:bg-slate-900/40 transition-colors {{ $loop->index >= 10 ? 'hidden' : '' }}">
                                
                                <td class="p-4 pl-6 font-bold text-slate-300">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-500"></i>
                                        <span>{{ \Carbon\Carbon::parse($notif->createdAt)->format('d/m/Y') }}</span>
                                        <span class="text-[10px] text-slate-500 font-mono font-normal">{{ \Carbon\Carbon::parse($notif->createdAt)->format('H:i A') }}</span>
                                    </div>
                                </td>

                                <td class="p-4">
                                    @if($notif->type === 'membership_expiry')
                                        <span class="px-2.5 py-0.5 bg-rose-500/10 text-rose-400 text-[10px] font-extrabold uppercase rounded-full border border-rose-500/20 flex items-center gap-1 w-max">
                                            <i data-lucide="credit-card" class="w-3 h-3"></i> Vencimiento
                                        </span>
                                    @elseif($notif->type === 'payment_reminder')
                                        <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 text-[10px] font-extrabold uppercase rounded-full border border-amber-500/20 flex items-center gap-1 w-max">
                                            <i data-lucide="dumbbell" class="w-3 h-3"></i> Recordatorio Gym
                                        </span>
                                    @elseif($notif->type === 'new_routine')
                                        <span class="px-2.5 py-0.5 bg-lime-500/10 text-lime-400 text-[10px] font-extrabold uppercase rounded-full border border-lime-500/20 flex items-center gap-1 w-max">
                                            <i data-lucide="clipboard-list" class="w-3 h-3"></i> Nueva Rutina
                                        </span>
                                    @elseif($notif->type === 'achievement')
                                        <span class="px-2.5 py-0.5 bg-purple-500/10 text-purple-400 text-[10px] font-extrabold uppercase rounded-full border border-purple-500/20 flex items-center gap-1 w-max">
                                            <i data-lucide="trophy" class="w-3 h-3"></i> Logro / Motivación
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-sky-500/10 text-sky-400 text-[10px] font-extrabold uppercase rounded-full border border-sky-500/20 flex items-center gap-1 w-max">
                                            <i data-lucide="megaphone" class="w-3 h-3"></i> Broadcast
                                        </span>
                                    @endif
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $memberPhoto }}" alt="{{ $memberName }}" class="w-8 h-8 rounded-full object-cover border border-slate-800 shrink-0">
                                        <div>
                                            <span class="block font-bold text-slate-100 text-xs">{{ $memberName }}</span>
                                            <div class="flex items-center gap-2 text-[10px] text-slate-500">
                                                @if($memberDni)
                                                    <span class="font-mono text-lime-400/80">DNI: {{ $memberDni }}</span>
                                                @endif
                                                @if($planName)
                                                    <span class="text-slate-400">• {{ $planName }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4 max-w-sm whitespace-normal cursor-pointer" onclick="openNotifDetailModal({{ $notif->id }})">
                                    <span class="block font-bold text-slate-100 text-xs hover:text-lime-400 transition-colors">{{ $notif->title }}</span>
                                    <span class="text-slate-400 text-[11px] line-clamp-1 mt-0.5">{{ $notif->body }}</span>
                                </td>

                                <td class="p-4 text-center" id="notif-status-cell-{{ $notif->id }}">
                                    @if($notif->is_read)
                                        <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold uppercase rounded-full border border-emerald-500/20 inline-flex items-center gap-1">
                                            <i data-lucide="check-check" class="w-3 h-3"></i> Leída
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 text-[10px] font-extrabold uppercase rounded-full border border-amber-500/20 inline-flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Pendiente
                                        </span>
                                    @endif
                                </td>

                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View Detail -->
                                        <button type="button" onclick="openNotifDetailModal({{ $notif->id }})" class="p-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 hover:text-slate-100 rounded-lg border border-slate-850 transition-colors cursor-pointer" title="Ver Detalle Completo">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <!-- Mark as Read Single -->
                                        <button type="button" id="btn-mark-read-{{ $notif->id }}" onclick="markSingleNotifAsRead({{ $notif->id }})" class="p-1.5 bg-slate-950 hover:bg-emerald-500/20 text-slate-400 hover:text-emerald-400 rounded-lg border border-slate-850 transition-colors cursor-pointer {{ $notif->is_read ? 'hidden' : '' }}" title="Marcar como Leída">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <!-- Delete Single -->
                                        <button type="button" onclick="openDeleteNotifModal({{ $notif->id }}, '{{ addslashes($notif->title) }}')" class="p-1.5 bg-slate-950 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 rounded-lg border border-slate-850 transition-colors cursor-pointer" title="Eliminar Notificación">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500 font-semibold italic">
                                    No hay notificaciones registradas en la bitácora.
                                </td>
                            </tr>
                        @endforelse

                        <tr id="no_notifications_search_row" class="hidden">
                            <td colspan="6" class="p-12 text-center text-slate-500 font-semibold">
                                <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                                <p class="font-bold text-slate-400 text-sm">No se encontraron notificaciones que coincidan con los filtros aplicados.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer Controls -->
            <div id="notif_pagination_container" class="p-4 border-t border-slate-850 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 bg-slate-950/40">
                <span id="notif_pagination_info">Mostrando notificaciones...</span>
                <div class="flex items-center gap-2">
                    <button type="button" id="notif_prev_btn" onclick="changeNotifPage(-1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed font-bold transition-colors cursor-pointer">
                        Anterior
                    </button>
                    <span id="notif_page_number_display" class="font-bold text-lime-400 px-3 py-1.5 bg-slate-950 rounded-xl border border-slate-850">Página 1</span>
                    <button type="button" id="notif_next_btn" onclick="changeNotifPage(1)" class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-900 border border-slate-850 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed font-bold transition-colors cursor-pointer">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Formulario de Envíos Masivos / Broadcast -->
    <div id="tab-content-broadcast" class="hidden space-y-6">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-3xl mx-auto shadow-xl space-y-6">
            <div class="border-b border-slate-850 pb-4">
                <h3 class="font-extrabold text-lg text-slate-100 flex items-center gap-2">
                    <i data-lucide="megaphone" class="w-5 h-5 text-lime-400"></i> Redactor de Comunicados & Envíos Push
                </h3>
                <p class="text-xs text-slate-400 mt-1">Envía avisos de último momento, recordatorios de pago o promociones instantáneas a la aplicación móvil de tus socios.</p>
            </div>

            <!-- Plantillas de Mensaje Rápido -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Plantillas de Mensaje Rápido</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="applyTemplate('clase_dj')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer">
                        🎉 Evento Especial
                    </button>
                    <button type="button" onclick="applyTemplate('mantenimiento')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer">
                        ⚡ Aviso Mantenimiento
                    </button>
                    <button type="button" onclick="applyTemplate('motivacion')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer">
                        🔥 Motivación Gym
                    </button>
                    <button type="button" onclick="applyTemplate('vencimiento')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer">
                        💳 Recordatorio Membresía
                    </button>
                    <button type="button" onclick="applyTemplate('logro')" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-850 text-slate-300 text-xs font-bold rounded-xl border border-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer">
                        🏆 Desafío / Meta Cumplida
                    </button>
                </div>
            </div>

            <form action="{{ route('notificaciones.send_manual') }}" method="POST" onsubmit="submitManualNotifForm(event)" class="space-y-5">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Grupo Destinatario *</label>
                        <select name="target_type" id="broadcast_target_type" onchange="toggleSpecificUserSelect(this.value)" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs font-bold text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="all">📢 Todos los Socios del Gimnasio ({{ $members->count() }})</option>
                            <option value="active_membership">💳 Solo Socios con Membresía Activa</option>
                            <option value="expiring_soon">⚠️ Socios con Membresía por Vencer (7 días)</option>
                            <option value="inactive">🚨 Socios Inactivos (Sin check-in > 5 días)</option>
                            <option value="specific">👤 Socio Específico (Búsqueda Individual)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Categoría / Tipo *</label>
                        <select name="type" id="broadcast_type" onchange="updateLivePreview()" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs font-bold text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="general">📢 General / Comunicado Oficial</option>
                            <option value="membership_expiry">💳 Vencimiento o Pago de Membresía</option>
                            <option value="payment_reminder">🏋️ Recordatorio de Entrenamiento</option>
                            <option value="new_routine">📋 Rutina o Plan Nutricional</option>
                            <option value="achievement">🏆 Logro, Reto o Motivación</option>
                        </select>
                    </div>
                </div>

                <!-- Specific Athlete Picker (Searchable) -->
                <div id="specific_user_wrapper" class="hidden space-y-2 p-4 bg-slate-950/60 border border-slate-850 rounded-2xl animate-fade-in">
                    <label class="block text-xs font-bold uppercase text-lime-400">Seleccionar Socio Destinatario *</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" id="athlete_picker_search" oninput="filterAthletePicker(this.value)" placeholder="Buscar atleta por nombre o DNI..." class="w-full pl-9 pr-3 py-2 text-xs bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 font-medium">
                    </div>
                    
                    <select name="user_id" id="broadcast_user_id" size="5" class="w-full p-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer font-mono">
                        @foreach($members as $m)
                            @php
                                $mName = trim(($m->profile->first_name ?? '') . ' ' . ($m->profile->last_name ?? ''));
                                if (empty($mName)) $mName = $m->email;
                                $mDni = $m->profile->dni ?? 'S/DNI';
                            @endphp
                            <option value="{{ $m->id }}" data-name="{{ $mName }}" data-dni="{{ $mDni }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $mName }} • DNI: {{ $mDni }} (ID #{{ $m->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase text-slate-400">Título de la Notificación *</label>
                        <span id="title_char_count" class="text-[10px] text-slate-500 font-mono">0 / 150</span>
                    </div>
                    <input type="text" name="title" id="broadcast_title" required maxlength="150" oninput="updateLivePreview()" placeholder="Ej: 🎉 ¡Clase Especial con DJ Hoy a las 6:00 PM!" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50 font-bold">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase text-slate-400">Cuerpo del Mensaje *</label>
                        <span id="body_char_count" class="text-[10px] text-slate-500 font-mono">0 / 2000</span>
                    </div>
                    <textarea name="body" id="broadcast_body" rows="4" required maxlength="2000" oninput="updateLivePreview()" placeholder="Escribe aquí el mensaje detallado para la app del cliente..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-lime-500/50 leading-relaxed"></textarea>
                </div>

                <!-- Live Push Preview Mockup -->
                <div class="p-4 bg-slate-950/80 border border-slate-800 rounded-2xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="smartphone" class="w-3.5 h-3.5 text-lime-400"></i> Vista Previa en el Móvil del Socio
                        </span>
                        <span class="text-[10px] text-slate-500">Ahora mismo</span>
                    </div>
                    <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl flex items-start gap-3 shadow-md">
                        <div class="p-2 bg-lime-500/10 text-lime-400 rounded-xl border border-lime-500/20 shrink-0 mt-0.5">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-100 text-xs truncate" id="preview_title">Título de la Notificación</span>
                                <span class="text-[9px] px-1.5 py-0.5 bg-slate-950 text-slate-400 border border-slate-800 rounded font-mono">Gymapp</span>
                            </div>
                            <p class="text-slate-300 text-[11px] mt-1 leading-relaxed whitespace-pre-wrap" id="preview_body">Aquí se mostrará el cuerpo del mensaje redactado para el atleta...</p>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-850 flex items-center justify-end">
                    <button type="submit" id="broadcast_submit_btn" class="px-6 py-3 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i> Confirmar y Enviar Notificación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: Disparadores Automáticos del Sistema -->
    <div id="tab-content-automaticas" class="hidden space-y-5">
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-base text-slate-100 flex items-center gap-2">
                    <i data-lucide="cpu" class="w-5 h-5 text-lime-400"></i> Motor de Automatizaciones & Triggers
                </h3>
                <p class="text-xs text-slate-400 mt-1">El sistema evalúa las asistencias, vencimientos y rutinas para enviar avisos predictivos a tus atletas.</p>
            </div>
            <button type="button" onclick="runAutoTriggers()" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-amber-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer shrink-0">
                <i data-lucide="zap" class="w-4 h-4"></i> Ejecutar Triggers Ahora
            </button>
        </div>

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
                            <span class="text-[11px] text-slate-400">Automatización predictiva</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/20">ACTIVO</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Envía notificaciones automáticas al socio para motivarlo a asistir al gym en sus días activos (*"🏋️ ¡Hora de entrenar! Recuerda que hoy es un gran día para cumplir tu meta"*).
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
                    Avisa al socio cuando faltan 3 días para vencer su plan, invitándolo a renovar en recepción o app móvil para evitar cortes de acceso por torniquete.
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
                    Envía un mensaje motivacional de reactivación a los atletas que llevan más de 5 días sin marcar asistencia en el gimnasio.
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
                            <h4 class="font-extrabold text-slate-100 text-sm">Asignación de Rutinas y Dietas</h4>
                            <span class="text-[11px] text-slate-400">Notificación al instante</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold rounded-full border border-emerald-500/20">ACTIVO</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Notifica al socio inmediatamente cuando su entrenador le asigna una nueva rutina de ejercicios o actualiza su plan de alimentación.
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
                <select name="target_type" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none cursor-pointer">
                    <option value="all">📢 Todos los Socios del Gimnasio</option>
                    <option value="active_membership">💳 Socios con Membresía Activa</option>
                    <option value="inactive">🚨 Socios Inactivos (> 5 Días)</option>
                </select>
            </div>

            <div>
                <label class="block uppercase text-slate-400 mb-1">Tipo de Notificación</label>
                <select name="type" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none cursor-pointer">
                    <option value="general">📢 General / Comunicado</option>
                    <option value="membership_expiry">💳 Aviso de Membresía</option>
                    <option value="payment_reminder">🏋️ Recordatorio Gym</option>
                    <option value="achievement">🏆 Logro / Motivación</option>
                </select>
            </div>

            <div>
                <label class="block uppercase text-slate-400 mb-1">Título</label>
                <input type="text" name="title" required maxlength="150" placeholder="Ej: ¡Aviso Importante del Gym!" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none">
            </div>

            <div>
                <label class="block uppercase text-slate-400 mb-1">Mensaje</label>
                <textarea name="body" rows="3" required maxlength="2000" placeholder="Escribe el mensaje..." class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-850">
                <button type="button" onclick="toggleModal('send-manual-modal')" class="px-4 py-2 bg-slate-950 border border-slate-800 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl cursor-pointer">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg cursor-pointer">Enviar Ahora</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Ver Detalle de Notificación -->
<div id="notif-detail-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 sm:p-7 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
            <div class="flex items-center gap-3">
                <div id="modal_detail_icon_bg" class="p-2.5 bg-lime-500/10 text-lime-400 rounded-2xl border border-lime-500/20">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100" id="modal_detail_title">Detalle de Notificación</h3>
                    <span class="text-xs text-slate-400" id="modal_detail_date">Fecha y hora</span>
                </div>
            </div>
            <button type="button" onclick="toggleModal('notif-detail-modal')" class="text-slate-400 hover:text-slate-200 p-1 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Recipient Info Card -->
        <div class="p-3.5 bg-slate-950 border border-slate-850 rounded-2xl flex items-center gap-3">
            <img id="modal_detail_user_photo" src="" alt="Atleta" class="w-10 h-10 rounded-full object-cover border border-slate-800 shrink-0">
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-100 text-xs truncate" id="modal_detail_user_name">Nombre del Socio</span>
                    <span class="text-[10px] font-bold text-lime-400 font-mono" id="modal_detail_user_dni">DNI: -</span>
                </div>
                <span class="text-[11px] text-slate-400 block truncate" id="modal_detail_user_plan">Plan: -</span>
            </div>
        </div>

        <!-- Body Message -->
        <div class="space-y-1.5">
            <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Contenido del Mensaje</label>
            <div class="p-4 bg-slate-950/80 border border-slate-850 rounded-2xl text-xs text-slate-200 leading-relaxed whitespace-pre-wrap font-medium" id="modal_detail_body">
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-850">
            <div id="modal_detail_status_badge">
                <!-- Status Badge -->
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="modal_detail_btn_read" onclick="" class="px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold rounded-xl transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i> Marcar como Leída
                </button>
                <button type="button" onclick="toggleModal('notif-detail-modal')" class="px-4 py-2 bg-slate-950 border border-slate-800 text-slate-300 hover:text-slate-100 text-xs font-bold rounded-xl cursor-pointer">
                    Cerrar
                </button>
            </div>
        </div>
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
            ¿Estás seguro de que deseas marcar <strong class="text-emerald-400 font-extrabold">todas las notificaciones pendientes como leídas</strong>? Esta acción actualizará el estado de la bitácora para tu gimnasio activo.
        </p>

        <form onsubmit="submitMarkAllAsRead(event)" class="flex items-center gap-3 pt-2">
            @csrf
            <button type="button" onclick="toggleModal('mark-all-read-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-850 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl border border-slate-800 transition-colors cursor-pointer">
                Cancelar
            </button>
            <button type="submit" id="btn-confirm-mark-all" class="flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="check" class="w-4 h-4"></i> Sí, Marcar Todas
            </button>
        </form>
    </div>
</div>

<!-- Modal: Confirmar Eliminar Notificación -->
<div id="delete-notif-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl">
                    <i data-lucide="trash-2" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Eliminar Notificación</h3>
                    <p class="text-xs text-rose-400 font-semibold">Esta acción es irreversible</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('delete-notif-modal')" class="p-1 rounded-lg text-slate-400 hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
            ¿Deseas eliminar permanentemente la notificación: <strong class="text-slate-100" id="delete-notif-title-preview"></strong>?
        </p>

        <form id="delete-notif-form" onsubmit="submitDeleteNotif(event)" class="flex items-center gap-3 pt-2">
            @csrf
            <input type="hidden" id="delete-notif-id-input" value="">
            <button type="button" onclick="toggleModal('delete-notif-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-850 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl border border-slate-800 cursor-pointer">
                Cancelar
            </button>
            <button type="submit" id="btn-confirm-delete-notif" class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
            </button>
        </form>
    </div>
</div>

<!-- Modal: Limpiar Notificaciones Antiguas -->
<div id="cleanup-old-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md mx-auto my-auto overflow-hidden animate-scale-up shadow-2xl p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-850 pb-3">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl">
                    <i data-lucide="archive" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-100">Depurar Historial Antiguo</h3>
                    <p class="text-xs text-slate-400">Optimizar almacenamiento</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('cleanup-old-modal')" class="p-1 rounded-lg text-slate-400 hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="submitCleanupOld(event)" class="space-y-4 text-xs font-semibold">
            @csrf
            <div>
                <label class="block uppercase text-slate-400 mb-1">Eliminar Notificaciones con más de:</label>
                <select name="days" id="cleanup_days_select" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 font-bold focus:outline-none cursor-pointer">
                    <option value="30">30 Días de antigüedad</option>
                    <option value="60">60 Días de antigüedad</option>
                    <option value="90">90 Días de antigüedad</option>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="toggleModal('cleanup-old-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-850 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl border border-slate-800 cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" id="btn-confirm-cleanup" class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="trash" class="w-4 h-4"></i> Limpiar Ahora
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    var currentNotifPage = 1;
    var currentNotifStatusFilter = 'all';
    var currentNotifTypeFilter = 'all';
    var notifPerPage = 10;

    function switchNotifTab(tabName) {
        document.getElementById('tab-content-historial').classList.add('hidden');
        document.getElementById('tab-content-broadcast').classList.add('hidden');
        document.getElementById('tab-content-automaticas').classList.add('hidden');

        document.getElementById('tab-btn-historial').className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none cursor-pointer";
        document.getElementById('tab-btn-broadcast').className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none cursor-pointer";
        document.getElementById('tab-btn-automaticas').className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-semibold transition-all border-transparent text-slate-400 hover:text-slate-200 focus:outline-none cursor-pointer";

        document.getElementById(`tab-content-${tabName}`).classList.remove('hidden');
        document.getElementById(`tab-btn-${tabName}`).className = "px-6 py-3 border-b-2 text-xs uppercase tracking-wider font-extrabold transition-all border-lime-500 text-lime-400 focus:outline-none cursor-pointer";

        if (window.lucide) window.lucide.createIcons();
    }

    function toggleSpecificUserSelect(val) {
        const wrapper = document.getElementById('specific_user_wrapper');
        if (val === 'specific') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function filterAthletePicker(query) {
        const q = (query || '').toLowerCase().trim();
        const select = document.getElementById('broadcast_user_id');
        if (!select) return;

        Array.from(select.options).forEach(opt => {
            const name = (opt.getAttribute('data-name') || '').toLowerCase();
            const dni = (opt.getAttribute('data-dni') || '').toLowerCase();
            const text = opt.textContent.toLowerCase();

            if (!q || name.includes(q) || dni.includes(q) || text.includes(q)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function applyTemplate(type) {
        const titleEl = document.getElementById('broadcast_title');
        const bodyEl = document.getElementById('broadcast_body');
        const typeSelect = document.getElementById('broadcast_type');

        if (type === 'clase_dj') {
            titleEl.value = "🎉 ¡Clase Especial con DJ en Vivo Hoy!";
            bodyEl.value = "¡Hola equipo! Hoy a las 6:00 PM tendremos Masterclass de Spinning con DJ en vivo y sorpresas. ¡No faltes!";
            if (typeSelect) typeSelect.value = "general";
        } else if (type === 'mantenimiento') {
            titleEl.value = "⚡ Aviso de Mantenimiento Mínimo";
            bodyEl.value = "Estimados socios, el área de saunas estará en mantenimiento preventivo este jueves de 2:00 PM a 4:00 PM. El resto de áreas opera con normalidad.";
            if (typeSelect) typeSelect.value = "general";
        } else if (type === 'motivacion') {
            titleEl.value = "🔥 ¡Hoy es un Gran Día para Entrenar!";
            bodyEl.value = "La disciplina se demuestra viniendo cuando menos ganas tienes. ¡Te esperamos en el gym para dar tu 100% hoy!";
            if (typeSelect) typeSelect.value = "achievement";
        } else if (type === 'vencimiento') {
            titleEl.value = "💳 Recordatorio: Tu Membresía está por Vencer";
            bodyEl.value = "Estimado socio, tu plan vence en los próximos días. Renuévalo hoy en recepción o por la app para continuar disfrutando sin cortes.";
            if (typeSelect) typeSelect.value = "membership_expiry";
        } else if (type === 'logro') {
            titleEl.value = "🏆 ¡Felicitaciones por tu Constancia!";
            bodyEl.value = "¡Gran trabajo esta semana! Has superado tu récord de entrenamientos. ¡Sigue así construyendo tu mejor versión!";
            if (typeSelect) typeSelect.value = "achievement";
        }

        updateLivePreview();
    }

    function updateLivePreview() {
        const titleVal = document.getElementById('broadcast_title')?.value || '';
        const bodyVal = document.getElementById('broadcast_body')?.value || '';

        const previewTitle = document.getElementById('preview_title');
        const previewBody = document.getElementById('preview_body');
        const titleCount = document.getElementById('title_char_count');
        const bodyCount = document.getElementById('body_char_count');

        if (previewTitle) previewTitle.textContent = titleVal.trim() || 'Título de la Notificación';
        if (previewBody) previewBody.textContent = bodyVal.trim() || 'Aquí se mostrará el cuerpo del mensaje redactado para el atleta...';

        if (titleCount) titleCount.textContent = `${titleVal.length} / 150`;
        if (bodyCount) bodyCount.textContent = `${bodyVal.length} / 2000`;
    }

    async function submitManualNotifForm(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const origHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="inline-flex items-center justify-center gap-2 animate-pulse">
                <svg class="animate-spin h-3.5 w-3.5 text-current shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Enviando...</span>
            </span>
        `;

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
                showNotifToast(data.message, 'success');
                form.reset();
                updateLivePreview();
                setTimeout(() => {
                    if (window.loadUrl) window.loadUrl(window.location.href, false);
                    else window.location.reload();
                }, 800);
            } else {
                showNotifToast(data.message || 'Error al enviar la notificación.', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotifToast('Error en el servidor al enviar la notificación.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origHtml;
        }
    }

    async function runAutoTriggers() {
        const btn = document.getElementById('btn-run-auto-triggers');
        if (btn) btn.disabled = true;

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
                showNotifToast(data.message, 'success');
                setTimeout(() => {
                    if (window.loadUrl) window.loadUrl(window.location.href, false);
                    else window.location.reload();
                }, 800);
            } else {
                showNotifToast(data.message || 'Error al ejecutar disparadores.', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotifToast('Error al procesar disparadores automáticos.', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    async function markSingleNotifAsRead(notifId) {
        try {
            const response = await fetch(`/notificaciones/${notifId}/leer`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                // Update row in table
                const row = document.querySelector(`[data-notif-row][data-id="${notifId}"]`);
                if (row) {
                    row.setAttribute('data-status', 'read');
                    const statusCell = document.getElementById(`notif-status-cell-${notifId}`);
                    if (statusCell) {
                        statusCell.innerHTML = `
                            <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-extrabold uppercase rounded-full border border-emerald-500/20 inline-flex items-center gap-1">
                                <i data-lucide="check-check" class="w-3 h-3"></i> Leída
                            </span>
                        `;
                    }
                    const btn = document.getElementById(`btn-mark-read-${notifId}`);
                    if (btn) btn.classList.add('hidden');
                }

                // Update unread and read counters in header
                const unreadEl = document.getElementById('stat-unread-count');
                const readEl = document.getElementById('stat-read-count');
                if (unreadEl) {
                    let uVal = parseInt(unreadEl.textContent.replace(/,/g, '')) || 0;
                    if (uVal > 0) unreadEl.textContent = (uVal - 1).toLocaleString();
                }
                if (readEl) {
                    let rVal = parseInt(readEl.textContent.replace(/,/g, '')) || 0;
                    readEl.textContent = (rVal + 1).toLocaleString();
                }

                showNotifToast('Notificación marcada como leída.', 'success');
                if (window.lucide) window.lucide.createIcons();
                renderNotifPage();
            }
        } catch (err) {
            console.error(err);
            showNotifToast('Error al marcar notificación como leída.', 'error');
        }
    }

    async function submitMarkAllAsRead(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-confirm-mark-all');
        if (btn) btn.disabled = true;

        try {
            const response = await fetch("{{ route('notificaciones.mark_all_read') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                toggleModal('mark-all-read-modal');
                showNotifToast(data.message, 'success');
                setTimeout(() => {
                    if (window.loadUrl) window.loadUrl(window.location.href, false);
                    else window.location.reload();
                }, 600);
            }
        } catch (err) {
            console.error(err);
            showNotifToast('Error al marcar todas las notificaciones.', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function openNotifDetailModal(notifId) {
        const row = document.querySelector(`[data-notif-row][data-id="${notifId}"]`);
        if (!row) return;

        const title = row.getAttribute('data-title') || '';
        const body = row.getAttribute('data-body') || '';
        const userName = row.getAttribute('data-user-name') || '';
        const userDni = row.getAttribute('data-user-dni') || '';
        const userPhoto = row.getAttribute('data-user-photo') || '';
        const userPlan = row.getAttribute('data-user-plan') || '';
        const date = row.getAttribute('data-date') || '';
        const status = row.getAttribute('data-status') || 'unread';

        document.getElementById('modal_detail_title').textContent = title;
        document.getElementById('modal_detail_date').textContent = date;
        document.getElementById('modal_detail_user_name').textContent = userName;
        document.getElementById('modal_detail_user_dni').textContent = userDni ? `DNI: ${userDni}` : '';
        document.getElementById('modal_detail_user_plan').textContent = `Plan: ${userPlan}`;
        document.getElementById('modal_detail_body').textContent = body;
        
        const photoEl = document.getElementById('modal_detail_user_photo');
        if (photoEl) photoEl.src = userPhoto;

        const badgeEl = document.getElementById('modal_detail_status_badge');
        const btnRead = document.getElementById('modal_detail_btn_read');

        if (status === 'read') {
            badgeEl.innerHTML = `
                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-extrabold uppercase rounded-full border border-emerald-500/20 inline-flex items-center gap-1.5">
                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Notificación Leída
                </span>
            `;
            if (btnRead) btnRead.classList.add('hidden');
        } else {
            badgeEl.innerHTML = `
                <span class="px-3 py-1 bg-amber-500/10 text-amber-400 text-xs font-extrabold uppercase rounded-full border border-amber-500/20 inline-flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> Pendiente de Lectura
                </span>
            `;
            if (btnRead) {
                btnRead.classList.remove('hidden');
                btnRead.onclick = function() {
                    markSingleNotifAsRead(notifId);
                    toggleModal('notif-detail-modal');
                };
            }
        }

        if (window.lucide) window.lucide.createIcons();
        toggleModal('notif-detail-modal');
    }

    function openDeleteNotifModal(id, title) {
        document.getElementById('delete-notif-id-input').value = id;
        document.getElementById('delete-notif-title-preview').textContent = `"${title}"`;
        toggleModal('delete-notif-modal');
    }

    async function submitDeleteNotif(e) {
        e.preventDefault();
        const id = document.getElementById('delete-notif-id-input').value;
        if (!id) return;

        const btn = document.getElementById('btn-confirm-delete-notif');
        if (btn) btn.disabled = true;

        try {
            const response = await fetch(`/notificaciones/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                toggleModal('delete-notif-modal');
                const row = document.querySelector(`[data-notif-row][data-id="${id}"]`);
                if (row) row.remove();
                showNotifToast(data.message, 'success');
                renderNotifPage();
            } else {
                showNotifToast(data.message || 'Error al eliminar la notificación.', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotifToast('Error en el servidor al eliminar.', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    async function submitCleanupOld(e) {
        e.preventDefault();
        const days = document.getElementById('cleanup_days_select')?.value || 30;
        const btn = document.getElementById('btn-confirm-cleanup');
        if (btn) btn.disabled = true;

        try {
            const response = await fetch("{{ route('notificaciones.cleanup_old') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ days: days })
            });

            const data = await response.json();
            if (data.success) {
                toggleModal('cleanup-old-modal');
                showNotifToast(data.message, 'success');
                setTimeout(() => {
                    if (window.loadUrl) window.loadUrl(window.location.href, false);
                    else window.location.reload();
                }, 800);
            }
        } catch (err) {
            console.error(err);
            showNotifToast('Error al depurar el historial.', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

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

    function setNotifTypeFilter(type) {
        currentNotifTypeFilter = type;
        currentNotifPage = 1;
        renderNotifPage();
    }

    function renderNotifPage() {
        const query = (document.getElementById('notif_search_input')?.value || '').toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('[data-notif-row]'));

        const matchingRows = allRows.filter(row => {
            const status = row.getAttribute('data-status') || '';
            const type = row.getAttribute('data-type') || '';
            const search = row.getAttribute('data-search') || '';

            let matchesStatus = true;
            if (currentNotifStatusFilter === 'read') matchesStatus = (status === 'read');
            else if (currentNotifStatusFilter === 'unread') matchesStatus = (status === 'unread');

            let matchesType = true;
            if (currentNotifTypeFilter !== 'all') matchesType = (type === currentNotifTypeFilter);

            let matchesSearch = true;
            if (query) matchesSearch = search.includes(query);

            return matchesStatus && matchesType && matchesSearch;
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

    function showNotifToast(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type === 'danger' ? 'error' : type);
        }
    }

    function initNotifModule() {
        renderNotifPage();
        updateLivePreview();
    }

    initNotifModule();
    window.addEventListener('page:loaded', initNotifModule);
</script>
@endsection
