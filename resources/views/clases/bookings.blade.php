@extends('layouts.admin')

@section('title', 'Reservaciones de Clase')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('clases.index') }}" class="inline-flex items-center gap-1.5 text-xs text-lime-400 font-extrabold hover:underline mb-2 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver a Clases & Sesiones
            </a>
            <h1 class="text-3xl font-black text-slate-100 tracking-tight flex items-center gap-3">
                {{ $schedule->gymClass->name }}
                <span class="px-2.5 py-0.5 text-xs font-extrabold rounded-lg uppercase tracking-wider border {{ $schedule->status === 'scheduled' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($schedule->status === 'completed' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20') }}">
                    {{ $schedule->status === 'scheduled' ? 'Programado' : ($schedule->status === 'completed' ? 'Completada' : 'Cancelada') }}
                </span>
            </h1>
            <p class="text-slate-400 text-xs mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 font-medium">
                <span class="flex items-center gap-1"><i data-lucide="user-check" class="w-3.5 h-3.5 text-lime-400"></i> Instructor: <strong class="text-slate-200">{{ $schedule->trainer->user->profile->first_name ?? 'Coach' }} {{ $schedule->trainer->user->profile->last_name ?? '' }}</strong></span>
                <span>•</span>
                <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5 text-lime-400"></i> Fecha: <strong class="text-slate-200">{{ \Carbon\Carbon::parse($schedule->scheduled_date)->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}</strong></span>
                <span>•</span>
                <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5 text-lime-400"></i> Horario: <strong class="text-slate-200">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</strong></span>
            </p>
        </div>
    </div>

    <!-- Capacity Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex items-center justify-between shadow-lg">
            <div>
                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Capacidad Total</span>
                <h3 class="text-xl font-black text-slate-100">{{ $schedule->gymClass->capacity }} <span class="text-xs text-slate-500 font-normal">cupos</span></h3>
            </div>
            <div class="p-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-400">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex items-center justify-between shadow-lg">
            <div>
                <span class="block text-[10px] text-emerald-400 font-bold uppercase tracking-wider mb-0.5">Cupos Confirmados</span>
                <h3 class="text-xl font-black text-emerald-400"><span id="counter-booked">{{ $bookings->where('status', 'booked')->count() }}</span> <span class="text-xs text-slate-500 font-normal">atletas</span></h3>
            </div>
            <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex items-center justify-between shadow-lg">
            <div>
                <span class="block text-[10px] text-amber-400 font-bold uppercase tracking-wider mb-0.5">Lista de Espera</span>
                <h3 class="text-xl font-black text-amber-400"><span id="counter-waitlisted">{{ $bookings->where('status', 'waitlisted')->count() }}</span> <span class="text-xs text-slate-500 font-normal">atletas</span></h3>
            </div>
            <div class="p-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 flex items-center justify-between shadow-lg">
            <div>
                <span class="block text-[10px] text-blue-400 font-bold uppercase tracking-wider mb-0.5">Asistencias Confirmadas</span>
                <h3 class="text-xl font-black text-blue-400"><span id="counter-attended">{{ $bookings->where('status', 'attended')->count() }}</span> <span class="text-xs text-slate-500 font-normal">asistieron</span></h3>
            </div>
            <div class="p-2.5 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-400">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Manual Enrollment Card with DNI Search -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                <div class="border-b border-slate-800 pb-3.5 flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-lime-500/10 border border-lime-500/20 text-lime-400">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-wider">Inscribir Atleta Manual</h3>
                        <p class="text-[10px] text-slate-400 font-medium">Buscador inteligente por DNI o Nombre.</p>
                    </div>
                </div>

                @php
                    $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                @endphp

                @if($schedule->status === 'cancelled' || !$schedule->gymClass || !$schedule->gymClass->is_active)
                    <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-2xl flex items-start gap-3">
                        <i data-lucide="slash" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold leading-relaxed">
                            Esta sesión se encuentra cancelada o la clase fue inhabilitada. No se permiten nuevas reservaciones ni inscripciones de atletas.
                        </p>
                    </div>
                @elseif($activeGymId === 'all')
                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs rounded-2xl flex items-start gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold leading-relaxed">
                            Estás en la vista global de todas las sucursales. Selecciona una sucursal específica para inscribir atletas.
                        </p>
                    </div>
                @else
                    <form id="book-client-form" action="{{ route('clases.book_client') }}" method="POST" onsubmit="submitBookClient(event)" class="space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="class_schedule_id" value="{{ $schedule->id }}">
                        <input type="hidden" name="user_id" id="selected_user_id" value="" required>

                        <!-- Real-time DNI / Name Search Bar -->
                        <div>
                            <label for="dni_search_input" class="block text-slate-400 uppercase tracking-wider mb-1.5">Buscar por DNI o Nombre *</label>
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="text" 
                                       id="dni_search_input" 
                                       autocomplete="off" 
                                       oninput="onDniSearchInput(this)" 
                                       onfocus="onDniSearchFocus(this)" 
                                       onkeydown="onDniSearchKeydown(event)"
                                       placeholder="Escribe DNI o Nombre del atleta..." 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                                
                                <!-- DNI Search Results Dropdown -->
                                <div id="search_results_dropdown" class="absolute left-0 right-0 top-full mt-1 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl z-50 max-h-60 overflow-y-auto hidden divide-y divide-slate-800/60">
                                </div>
                            </div>
                        </div>

                        <!-- Selected Client Card Preview -->
                        <div id="selected_client_card" class="hidden p-3.5 bg-slate-950/80 border border-lime-500/30 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img id="card_client_photo" src="" class="w-10 h-10 rounded-full object-cover border border-slate-800 shrink-0">
                                    <div class="min-w-0">
                                        <h4 id="card_client_name" class="font-bold text-slate-100 text-xs truncate"></h4>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <span id="card_client_dni" class="px-1.5 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-mono font-bold rounded"></span>
                                            <span id="card_client_email" class="text-[10px] text-slate-500 truncate"></span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="clearSelectedClient()" class="p-1 text-slate-400 hover:text-rose-400 hover:bg-slate-850 rounded-lg transition-colors shrink-0" title="Cambiar Atleta">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Fallback Full List Selector -->
                        <div class="pt-2 border-t border-slate-850/80">
                            <label for="user_id_select" class="block text-slate-500 text-[10px] uppercase tracking-wider mb-1">O selecciona de la lista con DNI:</label>
                            <select id="user_id_select" onchange="selectClientFromDropdown(this)" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2 text-[11px] text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                <option value="" disabled selected>-- Ver lista completa con DNI --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" 
                                            data-name="{{ trim(($client->profile->first_name ?? 'Atleta') . ' ' . ($client->profile->last_name ?? '')) }}" 
                                            data-dni="{{ $client->profile->dni ?? 'Sin DNI' }}" 
                                            data-email="{{ $client->email }}" 
                                            data-photo="{{ $client->profile->profile_photo ? asset($client->profile->profile_photo) : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}">
                                        {{ $client->profile->first_name ?? 'Atleta' }} {{ $client->profile->last_name ?? '' }} - DNI: {{ $client->profile->dni ?? 'Sin DNI' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" id="book-client-submit-btn" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                            Inscribir Atleta
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Right Column: Bookings Table Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                
                <!-- Clean Filters & Search Bar (Flex-wrap without horizontal scrollbar) -->
                <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
                    
                    <!-- Status Filter Tabs: Flex Wrap Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                        <button type="button" onclick="setBookingStatusFilter('all')" id="booking-status-filter-all" class="booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                            Todos (<span id="tab-count-all">{{ $bookings->count() }}</span>)
                        </button>
                        <button type="button" onclick="setBookingStatusFilter('booked')" id="booking-status-filter-booked" class="booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            Confirmados (<span id="tab-count-booked">{{ $bookings->where('status', 'booked')->count() }}</span>)
                        </button>
                        <button type="button" onclick="setBookingStatusFilter('waitlisted')" id="booking-status-filter-waitlisted" class="booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            En Espera (<span id="tab-count-waitlisted">{{ $bookings->where('status', 'waitlisted')->count() }}</span>)
                        </button>
                        <button type="button" onclick="setBookingStatusFilter('attended')" id="booking-status-filter-attended" class="booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            Asistieron (<span id="tab-count-attended">{{ $bookings->where('status', 'attended')->count() }}</span>)
                        </button>
                        <button type="button" onclick="setBookingStatusFilter('cancelled')" id="booking-status-filter-cancelled" class="booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            Cancelados (<span id="tab-count-cancelled">{{ $bookings->where('status', 'cancelled')->count() }}</span>)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full xl:w-60">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" id="search-booking-input" oninput="onBookingFilterChange()" placeholder="Buscar atleta por nombre..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-extrabold tracking-wider">
                                <th class="py-3 px-4">Atleta</th>
                                <th class="py-3 px-4">Inscripción</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                                <th class="py-3 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="bookings-table-body" class="divide-y divide-slate-800/40 text-xs font-semibold">
                            @forelse($bookings as $booking)
                                @php
                                    $clientName = trim(($booking->user->profile->first_name ?? 'Atleta') . ' ' . ($booking->user->profile->last_name ?? ''));
                                    $clientEmail = $booking->user->email ?? '';
                                    $photoUrl = $booking->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
                                @endphp
                                <tr id="booking_row_{{ $booking->id }}" 
                                    data-booking-row
                                    data-name="{{ strtolower($clientName) }}"
                                    data-email="{{ strtolower($clientEmail) }}"
                                    data-status="{{ $booking->status }}"
                                    class="hover:bg-slate-850/40 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ asset($photoUrl) }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                            <div class="overflow-hidden min-w-0">
                                                <span class="block font-bold text-slate-100 truncate" id="booking_client_name_{{ $booking->id }}">{{ $clientName }}</span>
                                                <span class="block text-[10px] text-slate-400 truncate">{{ $clientEmail }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="block text-[11px] text-slate-300 font-bold">{{ \Carbon\Carbon::parse($booking->booked_at)->format('H:i') }}</span>
                                        <span class="block text-[9px] text-slate-500 font-semibold">{{ \Carbon\Carbon::parse($booking->booked_at)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @php
                                            $statusMap = [
                                                'booked' => 'Confirmado',
                                                'waitlisted' => 'En Espera',
                                                'attended' => 'Asistió',
                                                'cancelled' => 'Cancelado',
                                                'no_show' => 'Falta'
                                            ];
                                            $statusBadge = [
                                                'booked' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                'waitlisted' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'attended' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'cancelled' => 'bg-slate-800/40 text-slate-500 border-slate-800',
                                                'no_show' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                            ];
                                        @endphp
                                        <span id="booking_badge_{{ $booking->id }}" class="px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider {{ $statusBadge[$booking->status] ?? 'bg-slate-950 text-slate-400 border-slate-800' }}">
                                            {{ $statusMap[$booking->status] ?? $booking->status }}
                                        </span>
                                    </td>
                                    <!-- Actions Column Stacked Vertically -->
                                    <td class="py-3.5 px-4 text-right" id="booking_actions_{{ $booking->id }}">
                                        @if(in_array($booking->status, ['booked', 'waitlisted']))
                                            <div class="flex flex-col items-end gap-1">
                                                <!-- Mark Attended Button -->
                                                <button type="button" onclick="changeBookingStatus({{ $booking->id }}, 'attended')" class="w-24 px-2 py-1 bg-blue-500/10 hover:bg-blue-500 text-blue-400 hover:text-white border border-blue-500/25 text-[10px] font-extrabold rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm" title="Marcar Asistencia">
                                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                                    Asistió
                                                </button>

                                                <!-- Cancel Booking Button -->
                                                <button type="button" onclick="changeBookingStatus({{ $booking->id }}, 'cancelled')" class="w-24 px-2 py-1 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/25 text-[10px] font-extrabold rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm" title="Cancelar Reservación">
                                                    <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                    Cancelar
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-500 italic font-medium">Finalizado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="no_bookings_empty">
                                    <td colspan="4" class="py-16 text-center text-slate-500">
                                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                        <p class="font-bold text-slate-400">No hay reservaciones registradas para esta sesión</p>
                                        <p class="text-xs text-slate-500 mt-1">Inscribe a tu primer atleta usando el panel de la izquierda.</p>
                                    </td>
                                </tr>
                            @endforelse

                            <tr id="no_bookings_search_row" class="hidden">
                                <td colspan="4" class="py-12 text-center text-slate-500">
                                    <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                                    <p class="font-bold text-slate-400 text-sm">No se encontraron reservaciones que coincidan con la búsqueda.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer Controls -->
                <div id="booking_pagination_container" class="bg-slate-950/60 border border-slate-850 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                    <span id="booking_pagination_info">Mostrando reservaciones...</span>
                    <div class="flex items-center gap-2">
                        <button type="button" id="booking_prev_page_btn" onclick="changeBookingPage(-1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                            Anterior
                        </button>
                        <span id="booking_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                        <button type="button" id="booking_next_page_btn" onclick="changeBookingPage(1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                            Siguiente
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
    // Real-time DNI / Name Search Logic (Copied and enhanced from Asistencia screen)
    let searchDebounceTimeout = null;

    function onDniSearchFocus(inputEl) {
        if ((inputEl.value || '').trim().length >= 1) {
            onDniSearchInput(inputEl);
        }
    }

    function onDniSearchInput(inputEl) {
        clearTimeout(searchDebounceTimeout);
        const query = (inputEl.value || '').trim();
        const resultsDropdown = document.getElementById('search_results_dropdown');
        if (!resultsDropdown) return;

        if (query.length < 1) {
            resultsDropdown.classList.add('hidden');
            return;
        }

        searchDebounceTimeout = setTimeout(() => {
            fetch(`{{ route('api.clientes.search_dni') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(clients => {
                    if (clients.length === 0) {
                        resultsDropdown.innerHTML = `<div class="p-3 text-center text-slate-500 text-xs font-semibold">No se encontraron atletas con ese DNI o nombre.</div>`;
                    } else {
                        resultsDropdown.innerHTML = clients.map(client => {
                            const safeName = (client.name || '').replace(/'/g, "\\'");
                            const safeDni = (client.dni || '').replace(/'/g, "\\'");
                            const safeEmail = (client.email || '').replace(/'/g, "\\'");
                            const safePhoto = (client.photo || '').replace(/'/g, "\\'");
                            return `
                                <div onclick="pickClient(${client.id}, '${safeName}', '${safeDni}', '${safeEmail}', '${safePhoto}')" 
                                     class="p-2.5 hover:bg-slate-800 flex items-center justify-between gap-3 cursor-pointer transition-colors border-b border-slate-850/40 last:border-0">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="${client.photo}" class="w-7 h-7 rounded-full object-cover border border-slate-800 shrink-0">
                                        <div class="min-w-0">
                                            <span class="block font-bold text-slate-200 text-xs truncate">${escapeHtml(client.name)}</span>
                                            <span class="block text-[10px] text-slate-500 truncate">${escapeHtml(client.email)}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[10px] font-mono font-bold rounded shrink-0">
                                        DNI: ${escapeHtml(client.dni)}
                                    </span>
                                </div>
                            `;
                        }).join('');
                    }
                    resultsDropdown.classList.remove('hidden');
                    if (window.lucide) window.lucide.createIcons();
                })
                .catch(err => {
                    console.error('Error al buscar cliente por DNI:', err);
                });
        }, 150);
    }

    async function onDniSearchKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();

            const selectedId = document.getElementById('selected_user_id')?.value;
            if (selectedId) {
                submitBookClient();
                return;
            }

            const searchInput = document.getElementById('dni_search_input');
            const query = (searchInput?.value || '').trim();

            if (!query) {
                showToast('Escribe un DNI o nombre para realizar la búsqueda.', 'warning');
                return;
            }

            try {
                const res = await fetch(`{{ route('api.clientes.search_dni') }}?q=${encodeURIComponent(query)}`);
                const clients = await res.json();

                if (clients && clients.length > 0) {
                    const topMatch = clients[0];
                    pickClient(topMatch.id, topMatch.name, topMatch.dni, topMatch.email, topMatch.photo);
                } else {
                    showToast('No se encontró ningún atleta registrado con ese DNI o nombre.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error al consultar atleta por DNI.', 'error');
            }
        }
    }

    function pickClient(id, name, dni, email, photo) {
        const userIdInput = document.getElementById('selected_user_id');
        if (userIdInput) userIdInput.value = id;
        
        const photoEl = document.getElementById('card_client_photo');
        if (photoEl) photoEl.src = photo;

        const nameEl = document.getElementById('card_client_name');
        if (nameEl) nameEl.textContent = name;

        const dniEl = document.getElementById('card_client_dni');
        if (dniEl) dniEl.textContent = 'DNI: ' + dni;

        const emailEl = document.getElementById('card_client_email');
        if (emailEl) emailEl.textContent = email;
        
        const cardEl = document.getElementById('selected_client_card');
        if (cardEl) cardEl.classList.remove('hidden');

        const dropdownEl = document.getElementById('search_results_dropdown');
        if (dropdownEl) dropdownEl.classList.add('hidden');

        const searchInput = document.getElementById('dni_search_input');
        if (searchInput) searchInput.value = `${name} (DNI: ${dni})`;

        const selectEl = document.getElementById('user_id_select');
        if (selectEl) selectEl.value = id;
        
        if (window.lucide) window.lucide.createIcons();
    }

    function selectClientFromDropdown(selectEl) {
        const option = selectEl.options[selectEl.selectedIndex];
        if (!option || !option.value) return;

        const id = option.value;
        const name = option.getAttribute('data-name');
        const dni = option.getAttribute('data-dni');
        const email = option.getAttribute('data-email');
        const photo = option.getAttribute('data-photo');

        pickClient(id, name, dni, email, photo);
    }

    function clearSelectedClient() {
        const userIdInput = document.getElementById('selected_user_id');
        if (userIdInput) userIdInput.value = '';

        const cardEl = document.getElementById('selected_client_card');
        if (cardEl) cardEl.classList.add('hidden');

        const searchInput = document.getElementById('dni_search_input');
        if (searchInput) searchInput.value = '';

        const selectEl = document.getElementById('user_id_select');
        if (selectEl) selectEl.selectedIndex = 0;
    }

    // Hide search dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const searchInput = document.getElementById('dni_search_input');
        const resultsDropdown = document.getElementById('search_results_dropdown');
        if (searchInput && resultsDropdown && !searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
            resultsDropdown.classList.add('hidden');
        }
    });

    // Floating Toast Notifications System
    function showToast(message, type = 'success') {
        let container = document.getElementById('booking-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'booking-toast-container';
            container.className = 'fixed top-24 right-6 z-50 flex flex-col gap-2.5 pointer-events-none max-w-xs sm:max-w-sm w-full';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const isDanger = type === 'danger' || type === 'error';

        let iconName = 'check-circle';
        let borderColor = 'border-emerald-500/30';
        let iconColor = 'text-emerald-400';
        let glowColor = 'shadow-emerald-500/10';

        if (isDanger) {
            iconName = 'alert-circle';
            borderColor = 'border-rose-500/30';
            iconColor = 'text-rose-400';
            glowColor = 'shadow-rose-500/10';
        } else if (type === 'warning') {
            iconName = 'alert-triangle';
            borderColor = 'border-amber-500/30';
            iconColor = 'text-amber-400';
            glowColor = 'shadow-amber-500/10';
        }

        toast.className = `pointer-events-auto flex items-center gap-3 p-3.5 pr-4 bg-slate-900 border ${borderColor} text-slate-100 text-xs font-semibold rounded-2xl shadow-xl ${glowColor} transition-all duration-300 transform translate-x-10 opacity-0`;

        toast.innerHTML = `
            <div class="p-1.5 rounded-xl bg-slate-950/60 shrink-0 ${iconColor}">
                <i data-lucide="${iconName}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 leading-tight">${escapeHtml(message)}</div>
            <button type="button" onclick="this.parentElement.remove()" class="p-1 text-slate-400 hover:text-slate-100 text-xs ml-1 shrink-0">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        `;

        container.appendChild(toast);
        if (window.lucide) window.lucide.createIcons();

        setTimeout(() => {
            toast.classList.remove('translate-x-10', 'opacity-0');
        }, 10);

        setTimeout(() => {
            toast.classList.add('translate-x-10', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3800);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function setBtnLoading(btn, isLoading, text = 'Procesando...') {
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.classList.add('opacity-80', 'cursor-wait');
            btn.innerHTML = `
                <span class="inline-flex items-center justify-center gap-2 animate-pulse">
                    <svg class="animate-spin h-3.5 w-3.5 text-current shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>${text}</span>
                </span>
            `;
        } else {
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-wait');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }
    }

    // AJAX Submission: Book Client
    async function submitBookClient(e) {
        if (e) e.preventDefault();
        const form = document.getElementById('book-client-form');
        const submitBtn = document.getElementById('book-client-submit-btn');

        if (!document.getElementById('selected_user_id')?.value) {
            showToast('Por favor, selecciona un atleta buscando por DNI o desde la lista.', 'warning');
            return;
        }

        setBtnLoading(submitBtn, true, 'Inscribiendo...');

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const b = data.booking;
                const tbody = document.getElementById('bookings-table-body');
                const emptyMsg = document.getElementById('no_bookings_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const clientName = b.user && b.user.profile 
                    ? `${b.user.profile.first_name || ''} ${b.user.profile.last_name || ''}`.trim()
                    : 'Atleta';
                const clientEmail = b.user ? b.user.email : '';
                const photoUrl = (b.user && b.user.profile && b.user.profile.profile_photo)
                    ? `/${b.user.profile.profile_photo}`
                    : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';

                const now = new Date();
                const timeStr = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
                const dateStr = `${String(now.getDate()).padStart(2,'0')}/${String(now.getMonth()+1).padStart(2,'0')}/${now.getFullYear()}`;

                const statusText = b.status === 'booked' ? 'Confirmado' : 'En Espera';
                const statusBadgeClass = b.status === 'booked' 
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                    : 'bg-amber-500/10 text-amber-400 border-amber-500/20';

                const tr = document.createElement('tr');
                tr.id = `booking_row_${b.id}`;
                tr.setAttribute('data-booking-row', '');
                tr.setAttribute('data-name', clientName.toLowerCase());
                tr.setAttribute('data-email', clientEmail.toLowerCase());
                tr.setAttribute('data-status', b.status);
                tr.className = 'hover:bg-slate-850/40 transition-colors';

                tr.innerHTML = `
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <img src="${photoUrl}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                            <div class="overflow-hidden min-w-0">
                                <span class="block font-bold text-slate-100 truncate" id="booking_client_name_${b.id}">${escapeHtml(clientName)}</span>
                                <span class="block text-[10px] text-slate-400 truncate">${escapeHtml(clientEmail)}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="block text-[11px] text-slate-300 font-bold">${timeStr}</span>
                        <span class="block text-[9px] text-slate-500 font-semibold">${dateStr}</span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <span id="booking_badge_${b.id}" class="px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider ${statusBadgeClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right" id="booking_actions_${b.id}">
                        <div class="flex flex-col items-end gap-1">
                            <button type="button" onclick="changeBookingStatus(${b.id}, 'attended')" class="w-24 px-2 py-1 bg-blue-500/10 hover:bg-blue-500 text-blue-400 hover:text-white border border-blue-500/25 text-[10px] font-extrabold rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm" title="Marcar Asistencia">
                                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                Asistió
                            </button>
                            <button type="button" onclick="changeBookingStatus(${b.id}, 'cancelled')" class="w-24 px-2 py-1 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/25 text-[10px] font-extrabold rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm" title="Cancelar Reservación">
                                <i data-lucide="x-circle" class="w-3 h-3"></i>
                                Cancelar
                            </button>
                        </div>
                    </td>
                `;

                const searchRow = document.getElementById('no_bookings_search_row');
                if (searchRow) {
                    tbody.insertBefore(tr, searchRow);
                } else {
                    tbody.appendChild(tr);
                }

                if (window.lucide) window.lucide.createIcons();

                clearSelectedClient();
                updateCountsUI(data.counts);
                renderBookingsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al registrar la inscripción.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar registrar la inscripción.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Change Booking Status
    async function changeBookingStatus(bookingId, status) {
        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('status', status);

            const response = await fetch(`/clases/reservas/${bookingId}/estado`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const b = data.booking;
                const row = document.getElementById(`booking_row_${bookingId}`);
                if (row) {
                    row.setAttribute('data-status', b.status);

                    const badge = document.getElementById(`booking_badge_${bookingId}`);
                    if (badge) {
                        let badgeClass = 'bg-slate-950 text-slate-400 border-slate-800';
                        let badgeText = b.status;

                        if (b.status === 'booked') {
                            badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                            badgeText = 'Confirmado';
                        } else if (b.status === 'waitlisted') {
                            badgeClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                            badgeText = 'En Espera';
                        } else if (b.status === 'attended') {
                            badgeClass = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                            badgeText = 'Asistió';
                        } else if (b.status === 'cancelled') {
                            badgeClass = 'bg-slate-800/40 text-slate-500 border-slate-800';
                            badgeText = 'Cancelado';
                        } else if (b.status === 'no_show') {
                            badgeClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                            badgeText = 'Falta';
                        }

                        badge.className = `px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider ${badgeClass}`;
                        badge.textContent = badgeText;
                    }

                    const actionsTd = document.getElementById(`booking_actions_${bookingId}`);
                    if (actionsTd) {
                        if (['booked', 'waitlisted'].includes(b.status)) {
                            actionsTd.innerHTML = `
                                <div class="flex flex-col items-end gap-1">
                                    <button type="button" onclick="changeBookingStatus(${bookingId}, 'attended')" class="w-24 px-2 py-1 bg-blue-500/10 hover:bg-blue-500 text-blue-400 hover:text-white border border-blue-500/25 text-[10px] font-extrabold rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm" title="Marcar Asistencia">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                        Asistió
                                    </button>
                                    <button type="button" onclick="changeBookingStatus(${bookingId}, 'cancelled')" class="w-24 px-2 py-1 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/25 text-[10px] font-extrabold rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm" title="Cancelar Reservación">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i>
                                        Cancelar
                                    </button>
                                </div>
                            `;
                        } else {
                            actionsTd.innerHTML = `<span class="text-[11px] text-slate-500 italic font-medium">Finalizado</span>`;
                        }
                    }
                }

                // If a waitlisted client was promoted automatically, update their row badge!
                if (data.promoted_booking_id) {
                    const promRow = document.getElementById(`booking_row_${data.promoted_booking_id}`);
                    if (promRow) {
                        promRow.setAttribute('data-status', 'booked');
                        const promBadge = document.getElementById(`booking_badge_${data.promoted_booking_id}`);
                        if (promBadge) {
                            promBadge.className = 'px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                            promBadge.textContent = 'Confirmado';
                        }
                    }
                }

                if (window.lucide) window.lucide.createIcons();

                updateCountsUI(data.counts);
                renderBookingsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al cambiar estado.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el estado.', 'error');
        }
    }

    // Update Counts UI Summary Cards & Tab Badges
    function updateCountsUI(counts) {
        if (!counts) return;
        const bookedEl = document.getElementById('counter-booked');
        const waitlistedEl = document.getElementById('counter-waitlisted');
        const attendedEl = document.getElementById('counter-attended');

        if (bookedEl) bookedEl.textContent = counts.booked || 0;
        if (waitlistedEl) waitlistedEl.textContent = counts.waitlisted || 0;
        if (attendedEl) attendedEl.textContent = counts.attended || 0;

        const allRows = document.querySelectorAll('[data-booking-row]');
        let cAll = allRows.length;
        let cBooked = 0;
        let cWaitlisted = 0;
        let cAttended = 0;
        let cCancelled = 0;

        allRows.forEach(r => {
            const st = r.getAttribute('data-status');
            if (st === 'booked') cBooked++;
            else if (st === 'waitlisted') cWaitlisted++;
            else if (st === 'attended') cAttended++;
            else if (st === 'cancelled') cCancelled++;
        });

        const tabAll = document.getElementById('tab-count-all');
        const tabBooked = document.getElementById('tab-count-booked');
        const tabWaitlisted = document.getElementById('tab-count-waitlisted');
        const tabAttended = document.getElementById('tab-count-attended');
        const tabCancelled = document.getElementById('tab-count-cancelled');

        if (tabAll) tabAll.textContent = cAll;
        if (tabBooked) tabBooked.textContent = cBooked;
        if (tabWaitlisted) tabWaitlisted.textContent = cWaitlisted;
        if (tabAttended) tabAttended.textContent = cAttended;
        if (tabCancelled) tabCancelled.textContent = cCancelled;
    }

    // Filtering & Pagination System (8 rows per page)
    let currentBookingPage = 1;
    let currentBookingStatusFilter = 'all';
    const bookingItemsPerPage = 8;

    function setBookingStatusFilter(status) {
        currentBookingStatusFilter = status;

        const tabs = document.querySelectorAll('.booking-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
        });

        const activeTab = document.getElementById('booking-status-filter-' + status);
        if (activeTab) {
            activeTab.className = "booking-status-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all";
        }

        currentBookingPage = 1;
        renderBookingsPage();
    }

    function onBookingFilterChange() {
        currentBookingPage = 1;
        renderBookingsPage();
    }

    function renderBookingsPage() {
        const searchVal = (document.getElementById('search-booking-input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-booking-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            const email = r.getAttribute('data-email') || '';
            const status = r.getAttribute('data-status') || '';

            const matchesStatus = (currentBookingStatusFilter === 'all') || (status === currentBookingStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || email.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / bookingItemsPerPage) || 1;

        if (currentBookingPage > totalPages) currentBookingPage = totalPages;
        if (currentBookingPage < 1) currentBookingPage = 1;

        const startIndex = (currentBookingPage - 1) * bookingItemsPerPage;
        const endIndex = startIndex + bookingItemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_bookings_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('booking_pagination_info');
        const pageSpan = document.getElementById('booking_page_number_display');
        const prevBtn = document.getElementById('booking_prev_page_btn');
        const nextBtn = document.getElementById('booking_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay reservaciones para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} reservaciones`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentBookingPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentBookingPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentBookingPage >= totalPages);
    }

    function changeBookingPage(delta) {
        currentBookingPage += delta;
        renderBookingsPage();
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif

        renderBookingsPage();
    });
</script>
@endsection
