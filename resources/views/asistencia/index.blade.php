@extends('layouts.admin')

@section('title', 'Control de Asistencia')

@section('content')
<div class="space-y-6 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-2.5">
                <i data-lucide="check-square" class="w-7 h-7 text-lime-400"></i>
                Control de Asistencia &amp; Aforo
            </h1>
            <p class="text-slate-400 text-sm mt-1">Supervisión de ingresos, aforo en sala e historial de accesos en tiempo real.</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $activeGymId = session('superadmin_gym_id', auth()->user()->role === 'superadmin' ? 'all' : auth()->user()->gym_id);
            @endphp
            @if($activeGymId === 'all')
                <span class="px-3 py-1.5 text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-xl flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                    Vista Global (Todas las Sucursales)
                </span>
            @else
                <span class="px-3 py-1.5 text-xs font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-xl flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-lime-400"></span>
                    Sucursal Activa
                </span>
            @endif
        </div>
    </div>

    <!-- 2. Dynamic Synchronized Executive Aforo & Summary Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Metric Card 1: Entries in Period (Synchronized with Period Filter) -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span id="period_label_title" class="block text-xs font-extrabold text-slate-400 uppercase tracking-wider">Entradas Hoy</span>
                <span id="today_entries_count_val" class="text-3xl font-black text-lime-400 mt-1 block">{{ $todayEntriesCount }}</span>
                <p class="text-[11px] text-slate-500 mt-0.5">Check-ins registrados en el periodo</p>
            </div>
            <div class="p-3 bg-lime-500/10 text-lime-400 rounded-xl border border-lime-500/20">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Metric Card 2: Currently in Gym (Active in Room) -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="block text-xs font-extrabold text-slate-400 uppercase tracking-wider">Actualmente en Sala</span>
                <span id="currently_in_gym_count_val" class="text-3xl font-black text-emerald-400 mt-1 block">{{ $currentlyInGymCount }}</span>
                <p class="text-[11px] text-slate-500 mt-0.5">Atletas entrenando sin marcar salida</p>
            </div>
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Metric Card 3: Completed Sessions -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <span class="block text-xs font-extrabold text-slate-400 uppercase tracking-wider">Entrenamientos Completados</span>
                <span id="completed_sessions_count_val" class="text-3xl font-black text-purple-400 mt-1 block">{{ $completedSessionsCount }}</span>
                <p class="text-[11px] text-slate-500 mt-0.5">Accesos con salida confirmada</p>
            </div>
            <div class="p-3 bg-purple-500/10 text-purple-400 rounded-xl border border-purple-500/20">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column (1/3 Width): Check-in Manual Panel -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="font-extrabold text-base text-slate-100 uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="qr-code" class="text-lime-400 w-5 h-5"></i>
                    Check-in Presencial / QR
                </h3>

                @if($activeGymId === 'all')
                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs rounded-2xl flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-amber-400 shrink-0 mt-0.5"></i>
                        <p class="leading-relaxed font-medium">
                            Estás navegando en modo <strong>Vista Global (Superadmin)</strong>. Para registrar asistencias presenciales, selecciona una sucursal específica en el menú superior.
                        </p>
                    </div>
                @else
                    <!-- LIVE QR SCANNER LAUNCH BUTTON -->
                    <button type="button" onclick="openQrScannerModal()" class="w-full py-3 bg-slate-950 border border-lime-500/40 hover:bg-lime-500/10 text-lime-400 font-bold rounded-2xl shadow-lg hover:shadow-lime-500/10 active:scale-95 transition-all flex items-center justify-center gap-2 group">
                        <i data-lucide="camera" class="w-5 h-5 text-lime-400 group-hover:scale-110 transition-transform"></i>
                        <span>Escanear Carnet QR en Vivo</span>
                    </button>

                    <div class="flex items-center gap-3 my-2">
                        <div class="h-px bg-slate-800 flex-1"></div>
                        <span class="text-[10px] text-slate-500 uppercase tracking-widest font-extrabold">O BÚSQUEDA MANUAL</span>
                        <div class="h-px bg-slate-800 flex-1"></div>
                    </div>

                    <form id="checkin_form" action="{{ route('asistencia.check_in') }}" method="POST" onsubmit="submitCheckIn(event)" class="space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="user_id" id="selected_user_id" value="{{ old('user_id') }}">
                        <input type="hidden" name="entry_method" id="selected_entry_method" value="admin">

                        <!-- Real-time DNI / Name Search -->
                        <div class="relative">
                            <label for="dni_search_input" class="block text-slate-400 uppercase tracking-wider mb-1.5 flex justify-between items-center">
                                <span>Buscar por DNI o Nombre</span>
                            </label>
                            
                            <div class="relative">
                                <input type="text" 
                                       id="dni_search_input" 
                                       oninput="onDniSearchInput(this)" 
                                       onkeydown="onDniSearchKeydown(event)" 
                                       placeholder="Escribe el DNI o nombre del atleta..." 
                                       autocomplete="off"
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 transition-colors">
                                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-3"></i>
                            </div>

                            <!-- Live Results Dropdown -->
                            <div id="search_results_dropdown" class="absolute left-0 right-0 top-full mt-1 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl z-50 max-h-60 overflow-y-auto hidden">
                                <!-- Dynamic AJAX content populated here -->
                            </div>
                        </div>

                        <!-- Selected Client Preview Card -->
                        <div id="selected_client_card" class="hidden p-3.5 bg-slate-950 border border-lime-500/30 rounded-xl">
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

                        <!-- Fallback / Full List Selector -->
                        <div class="pt-2 border-t border-slate-850">
                            <label for="user_id_select" class="block text-slate-500 text-[10px] uppercase tracking-wider mb-1">O selecciona de la lista con DNI:</label>
                            <select id="user_id_select" onchange="selectClientFromDropdown(this)" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2 text-[11px] text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                <option value="" disabled selected>-- Ver lista completa con DNI --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" 
                                            data-name="{{ trim(($client->profile->first_name ?? 'Atleta') . ' ' . ($client->profile->last_name ?? '')) }}" 
                                            data-dni="{{ $client->profile->dni ?? 'Sin DNI' }}" 
                                            data-email="{{ $client->email }}" 
                                            data-photo="{{ $client->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}">
                                        {{ $client->profile->first_name ?? 'Atleta' }} {{ $client->profile->last_name ?? '' }} - DNI: {{ $client->profile->dni ?? 'Sin DNI' }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" id="submit_checkin_btn" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                            Registrar Entrada Presencial
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Right Column (2/3 Width): Re-structured Attendance Logs Feed -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5">
                
                <!-- Period Filter & Custom Date Selector Controls -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 gap-3">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-100 flex items-center gap-2">
                            <i data-lucide="history" class="text-lime-400 w-5 h-5"></i>
                            Historial de Accesos
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Bitácora de entradas, salidas y medios de acceso.</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <select id="attendance_period_filter" onchange="onAttendancePeriodFilterChange()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-lime-400 font-bold focus:outline-none focus:border-lime-500 cursor-pointer">
                            <option value="today" selected>Hoy</option>
                            <option value="this_week">Esta Semana</option>
                            <option value="last_week">Semana Anterior</option>
                            <option value="this_month">Mes Actual</option>
                            <option value="custom">Fecha Específica...</option>
                        </select>

                        <div id="attendance_custom_date_container" class="hidden">
                            <input type="date" id="date-filter" name="date" value="{{ $selectedDate }}" onchange="reloadAttendanceData()" onclick="this.showPicker()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-1.5 text-slate-200 font-medium">
                        </div>
                    </div>
                </div>

                <!-- Spacious Table Layout -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap min-w-[640px]">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                                <th class="py-3 px-3">Atleta</th>
                                @if($activeGymId === 'all')
                                    <th class="py-3 px-3 text-center">Sucursal</th>
                                @endif
                                <th class="py-3 px-3 text-center">Entrada</th>
                                <th class="py-3 px-3 text-center">Salida</th>
                                <th class="py-3 px-3 text-center">Medio</th>
                                <th class="py-3 px-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="logs_table_body" class="divide-y divide-slate-800/50 text-sm transition-opacity duration-200">
                            @forelse($logs as $log)
                                <tr data-log-row class="hover:bg-slate-850/40 transition-colors">
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $log->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-9 h-9 rounded-full object-cover border border-slate-800 shrink-0">
                                            <div class="max-w-[170px] sm:max-w-[220px]">
                                                <span class="block font-bold text-slate-100 text-xs truncate">
                                                    {{ $log->user->profile->first_name ?? 'Atleta' }} {{ $log->user->profile->last_name ?? '' }}
                                                </span>
                                                <span class="block text-[10px] text-slate-500 truncate">{{ $log->user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    @if($activeGymId === 'all')
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20 rounded-md">
                                                {{ $log->gym->name ?? 'Global' }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="block font-bold text-slate-200 text-xs">{{ \Carbon\Carbon::parse($log->check_in)->format('H:i') }}</span>
                                        <span class="block text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($log->check_in)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        @if($log->check_out)
                                            <span class="block font-bold text-slate-300 text-xs">{{ \Carbon\Carbon::parse($log->check_out)->format('H:i') }}</span>
                                            <span class="block text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($log->check_out)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-md">
                                                En Sala
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        @php
                                            $methodMap = [
                                                'admin' => 'Admin',
                                                'biometric' => 'Biométrico',
                                                'rfid' => 'RFID',
                                                'app_manual' => 'App Móvil',
                                                'qr' => 'Escáner QR'
                                            ];
                                            $methodBadge = [
                                                'admin' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'biometric' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                'rfid' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'app_manual' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                'qr' => 'bg-lime-500/10 text-lime-400 border-lime-500/20'
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 text-[10px] font-bold border rounded-md {{ $methodBadge[$log->entry_method] ?? 'bg-slate-950 text-slate-500 border-slate-850' }}">
                                            {{ $methodMap[$log->entry_method] ?? $log->entry_method }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-right">
                                        @if(!$log->check_out)
                                            <button type="button" onclick="submitCheckOut(event, '{{ route('asistencia.check_out', $log->id) }}')" class="px-3 py-1.5 bg-slate-950 text-lime-400 border border-lime-500/30 hover:bg-lime-500 hover:text-slate-950 text-xs font-bold rounded-lg transition-all">
                                                Marcar Salida
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-500 italic font-semibold">Completado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $activeGymId === 'all' ? 6 : 5 }}" class="py-12 text-center text-slate-500">
                                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                                        <p>No se encontraron registros de asistencia hoy.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Logs Table Interactive Pagination Controls (10 per page) -->
                <div id="logs_pagination_container" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-850 text-xs font-medium text-slate-400">
                    <span id="logs_pagination_info">Mostrando accesos...</span>
                    <div class="flex items-center gap-2">
                        <button type="button" id="logs_prev_btn" onclick="changeLogsPage(-1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            Anterior
                        </button>
                        <span id="logs_page_display" class="px-3.5 py-1.5 bg-slate-950 rounded-xl font-bold text-lime-400 border border-slate-850">Página 1</span>
                        <button type="button" id="logs_next_btn" onclick="changeLogsPage(1)" class="px-3.5 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-300 hover:text-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors font-bold flex items-center gap-1">
                            Siguiente
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
    var searchDebounceTimeout = null;
    var currentLogsPage = 1;
    var logsPerPage = 10;
    var cachedLogsData = [];
    var isSuperadminAllMode = {{ $activeGymId === 'all' ? 'true' : 'false' }};

    // Show temporary toast alerts
    function showToast(message, type = 'success') {
        let container = document.getElementById('attendance-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'attendance-toast-container';
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

    // Input handlers for DNI search
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
                        resultsDropdown.innerHTML = `<div class="p-3 text-center text-slate-500 text-xs">No se encontraron atletas con ese DNI o nombre.</div>`;
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
                    console.error('Error al buscar cliente:', err);
                });
        }, 150);
    }

    async function onDniSearchKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();

            const selectedId = document.getElementById('selected_user_id')?.value;
            if (selectedId) {
                submitCheckIn();
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
                    submitCheckIn();
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

    document.addEventListener('click', function (e) {
        const searchInput = document.getElementById('dni_search_input');
        const resultsDropdown = document.getElementById('search_results_dropdown');
        if (searchInput && resultsDropdown && !searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
            resultsDropdown.classList.add('hidden');
        }
    });

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

    // AJAX Check-In Submission
    async function submitCheckIn(e) {
        if (e) e.preventDefault();

        const form = document.getElementById('checkin_form');
        const submitBtn = document.getElementById('submit_checkin_btn');
        const userId = document.getElementById('selected_user_id')?.value;

        if (!userId) {
            showToast('Por favor, selecciona un atleta buscando por DNI o desde la lista.', 'error');
            return;
        }

        setBtnLoading(submitBtn, true, 'Registrando...');

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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Acceso Concedido!',
                        html: `
                            <div class="flex flex-col items-center space-y-3 py-2">
                                <img src="${data.user_photo || ''}" class="w-24 h-24 rounded-full object-cover border-4 border-lime-400 shadow-2xl">
                                <h3 class="text-xl font-extrabold text-slate-100">${escapeHtml(data.user_name || '')}</h3>
                                <span class="px-3 py-1 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-xs font-mono font-bold rounded-lg">DNI: ${escapeHtml(data.user_dni || '')}</span>
                                <span class="text-xs text-lime-400 font-semibold mt-1">✔ ${data.message}</span>
                            </div>
                        `,
                        icon: 'success',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#84cc16',
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    showToast(data.message, 'success');
                }
                clearSelectedClient();
                reloadAttendanceData();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Acceso Denegado',
                        html: `<p class="text-slate-300 text-sm mt-2">${escapeHtml(data.message || 'Membresía inactiva o no pagada.')}</p>`,
                        icon: 'error',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#f43f5e',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    showToast(data.message || 'Error al procesar check-in.', 'error');
                }
            }
        } catch (err) {
            console.error(err);
            showToast('Error de conexión al registrar entrada.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Check-Out Submission
    async function submitCheckOut(e, actionUrl) {
        if (e) e.preventDefault();

        const btn = e.target.closest('button') || e.target;
        setBtnLoading(btn, true, 'Procesando...');

        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');

            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Salida Registrada',
                        text: data.message,
                        icon: 'info',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#a855f7',
                        confirmButtonText: 'OK'
                    });
                } else {
                    showToast(data.message, 'success');
                }
                reloadAttendanceData();
            } else {
                showToast(data.message || 'Error al marcar salida.', 'error');
                setBtnLoading(btn, false);
            }
        } catch (err) {
            console.error(err);
            showToast('Error de conexión al marcar salida.', 'error');
            setBtnLoading(btn, false);
        }
    }

    // LIVE QR SCANNER MODAL HANDLERS (html5-qrcode optimized for low quality webcams & dual camera support)
    let html5QrInstance = null;
    let currentCameraConfig = { facingMode: "environment" };

    async function openQrScannerModal() {
        const modal = document.getElementById('qr_scanner_modal');
        if (modal) modal.classList.remove('hidden');

        setTimeout(async () => {
            if (typeof Html5Qrcode === 'undefined') {
                showToast("Librería de cámara cargándose, intenta en un segundo.", "info");
                return;
            }

            try {
                if (!html5QrInstance) {
                    html5QrInstance = new Html5Qrcode("qr_reader_viewport", {
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        }
                    });
                }

                // Discover available hardware cameras and populate selector
                const cameras = await Html5Qrcode.getCameras().catch(() => []);
                const cameraSelect = document.getElementById('qr_camera_select');

                if (cameraSelect) {
                    let optionsHtml = `
                        <option value="facing_environment">Cámara Trasera (Principal)</option>
                        <option value="facing_user">Cámara Frontal (Selfie)</option>
                    `;

                    if (cameras && cameras.length > 0) {
                        optionsHtml += cameras.map((cam, idx) => {
                            const label = cam.label ? cam.label : `Cámara ${idx + 1}`;
                            return `<option value="${cam.id}">📹 ${label}</option>`;
                        }).join('');
                    }

                    cameraSelect.innerHTML = optionsHtml;
                    cameraSelect.classList.remove('hidden');
                }

                // Default to environment (rear camera)
                currentCameraConfig = { facingMode: "environment" };
                startScannerWithCamera(currentCameraConfig);

            } catch (err) {
                console.warn("Camera enumeration warning:", err);
                startScannerWithCamera({ facingMode: "environment" });
            }
        }, 200);
    }

    function switchQrCamera(selectedVal) {
        if (!selectedVal) return;

        let cameraConfig;
        if (selectedVal === 'facing_environment') {
            cameraConfig = { facingMode: "environment" };
        } else if (selectedVal === 'facing_user') {
            cameraConfig = { facingMode: "user" };
        } else {
            cameraConfig = { deviceId: { exact: selectedVal } };
        }

        currentCameraConfig = cameraConfig;
        startScannerWithCamera(cameraConfig);
    }

    function startScannerWithCamera(cameraConfig) {
        if (!html5QrInstance) return;

        const config = {
            fps: 25, // Higher FPS to quickly catch sharp frames on webcams/mobile
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                const size = Math.max(180, Math.floor(minEdge * 0.85));
                return { width: size, height: size };
            },
            aspectRatio: 1.0
        };

        if (html5QrInstance.isScanning) {
            html5QrInstance.stop().then(() => {
                runStartScanner(cameraConfig, config);
            }).catch(() => runStartScanner(cameraConfig, config));
        } else {
            runStartScanner(cameraConfig, config);
        }
    }

    function runStartScanner(cameraConfig, config) {
        html5QrInstance.start(
            cameraConfig,
            config,
            (decodedText) => {
                onQrCodeScanned(decodedText);
            },
            () => {
                // Silent on unparsed frames
            }
        ).catch(err => {
            console.warn("Camera start failed with target config, trying fallback:", err);
            const fallbackConfig = (typeof cameraConfig === 'object' && cameraConfig.deviceId) 
                ? cameraConfig.deviceId.exact 
                : { facingMode: "user" };

            html5QrInstance.start(
                fallbackConfig,
                { fps: 15, qrbox: { width: 220, height: 220 } },
                (decodedText) => onQrCodeScanned(decodedText),
                () => {}
            ).catch(e => {
                console.error("Final camera fallback error:", e);
                showToast("No se pudo iniciar la cámara seleccionada. Permite los permisos de cámara en tu navegador o selecciona otra opción.", "error");
            });
        });
    }

    async function scanQrFromFile(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];

        // 1. Try Native Browser BarcodeDetector first (Super fast & handles blurry/rotated images!)
        if ('BarcodeDetector' in window) {
            try {
                const barcodeDetector = new BarcodeDetector({ formats: ['qr_code'] });
                const imageBitmap = await createImageBitmap(file);
                const barcodes = await barcodeDetector.detect(imageBitmap);
                if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                    onQrCodeScanned(barcodes[0].rawValue);
                    return;
                }
            } catch (nativeErr) {
                console.warn("Native BarcodeDetector fallback to Html5Qrcode:", nativeErr);
            }
        }

        // 2. Try Html5Qrcode scanFile
        if (!html5QrInstance) {
            html5QrInstance = new Html5Qrcode("qr_reader_viewport");
        }

        try {
            if (html5QrInstance.isScanning) {
                await html5QrInstance.stop();
            }
            const decodedText = await html5QrInstance.scanFile(file, true);
            onQrCodeScanned(decodedText);
        } catch (err) {
            console.warn("Standard scanFile failed, trying Canvas Pre-processing fallback:", err);
            // 3. Fallback: Pre-process Image on Canvas (downscale & contrast)
            tryScanQrFromCanvas(file);
        }
    }

    function tryScanQrFromCanvas(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = async function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                let width = img.width;
                let height = img.height;
                const maxDim = 800;
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }
                canvas.width = width;
                canvas.height = height;

                ctx.drawImage(img, 0, 0, width, height);

                const tryScanBlob = (blob) => {
                    return new Promise((resolve, reject) => {
                        if (!blob) return reject("No blob");
                        const procFile = new File([blob], "proc_qr.png", { type: "image/png" });
                        html5QrInstance.scanFile(procFile, false)
                            .then(res => resolve(res))
                            .catch(err => reject(err));
                    });
                };

                // Pass A: Downscaled Image
                try {
                    const blobA = await new Promise(r => canvas.toBlob(r, 'image/png'));
                    const resultA = await tryScanBlob(blobA);
                    onQrCodeScanned(resultA);
                    return;
                } catch (eA) {
                    console.warn("Canvas Pass A failed, attempting Pass B (Color Inversion for Dark Theme QRs)...");
                }

                // Pass B: Color Inversion (Turns Light Green on Dark Navy into Dark Magenta on Light Cyan)
                const imgData = ctx.getImageData(0, 0, width, height);
                const data = imgData.data;
                for (let i = 0; i < data.length; i += 4) {
                    data[i] = 255 - data[i];       // R
                    data[i + 1] = 255 - data[i + 1]; // G
                    data[i + 2] = 255 - data[i + 2]; // B
                }
                ctx.putImageData(imgData, 0, 0);

                try {
                    const blobB = await new Promise(r => canvas.toBlob(r, 'image/png'));
                    const resultB = await tryScanBlob(blobB);
                    onQrCodeScanned(resultB);
                    return;
                } catch (eB) {
                    console.warn("Canvas Pass B failed, attempting Pass C (High Contrast Binarization)...");
                }

                // Pass C: Grayscale Binarization Thresholding
                for (let i = 0; i < data.length; i += 4) {
                    const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    const bw = avg > 128 ? 255 : 0;
                    data[i] = bw;
                    data[i + 1] = bw;
                    data[i + 2] = bw;
                }
                ctx.putImageData(imgData, 0, 0);

                try {
                    const blobC = await new Promise(r => canvas.toBlob(r, 'image/png'));
                    const resultC = await tryScanBlob(blobC);
                    onQrCodeScanned(resultC);
                    return;
                } catch (eC) {
                    console.error("All canvas scanner passes failed:", eC);
                    showToast("No se pudo detectar un código QR en la imagen. Intenta con una captura o foto más clara.", "error");
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function closeQrScannerModal() {
        const modal = document.getElementById('qr_scanner_modal');
        if (modal) modal.classList.add('hidden');

        if (html5QrInstance && html5QrInstance.isScanning) {
            html5QrInstance.stop().then(() => {
                html5QrInstance.clear();
            }).catch(err => console.error(err));
        }
    }

    function onQrCodeScanned(decodedText) {
        closeQrScannerModal();

        let cleanVal = decodedText.trim();
        if (cleanVal.includes('MEMBER:')) {
            cleanVal = cleanVal.substring(cleanVal.indexOf('MEMBER:'));
        } else if (cleanVal.includes('DNI:')) {
            cleanVal = cleanVal.split('DNI:')[1].trim();
        }

        performQrCheckIn(cleanVal);
    }

    async function performQrCheckIn(value) {
        const actionUrl = "{{ route('asistencia.check_in') }}";
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('entry_method', 'qr');

        let targetVal = value.trim();
        if (/^MEMBER:(\d+)$/i.test(targetVal)) {
            const matches = targetVal.match(/^MEMBER:(\d+)$/i);
            formData.append('user_id', matches[1]);
        } else if (/^\d+$/.test(targetVal) && targetVal.length <= 6) {
            formData.append('user_id', targetVal);
        } else {
            formData.append('dni', targetVal);
        }

        try {
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const isCheckOut = data.action === 'check_out';
                const titleText = isCheckOut ? '¡Salida Registrada!' : '¡Acceso Concedido!';
                const iconType = isCheckOut ? 'info' : 'success';
                const borderColor = isCheckOut ? 'border-purple-400' : 'border-lime-400';
                const btnColor = isCheckOut ? '#a855f7' : '#84cc16';
                const badgeBg = isCheckOut ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : 'bg-lime-500/10 text-lime-400 border-lime-500/20';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: titleText,
                        html: `
                            <div class="flex flex-col items-center space-y-3 py-2">
                                <img src="${data.user_photo || ''}" class="w-24 h-24 rounded-full object-cover border-4 ${borderColor} shadow-2xl">
                                <h3 class="text-xl font-extrabold text-slate-100">${escapeHtml(data.user_name || '')}</h3>
                                <span class="px-3 py-1 ${badgeBg} text-xs font-mono font-bold rounded-lg">DNI: ${escapeHtml(data.user_dni || '')}</span>
                                <span class="text-xs ${isCheckOut ? 'text-purple-400' : 'text-lime-400'} font-semibold mt-1">✔ ${escapeHtml(data.message)}</span>
                            </div>
                        `,
                        icon: iconType,
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: btnColor,
                        confirmButtonText: 'Continuar'
                    });
                } else {
                    showToast(data.message, 'success');
                }
                reloadAttendanceData();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Acceso Denegado',
                        html: `<p class="text-slate-300 text-sm mt-2">${escapeHtml(data.message || 'Membresía inactiva o rechazada.')}</p>`,
                        icon: 'error',
                        background: '#0f172a',
                        color: '#f8fafc',
                        confirmButtonColor: '#f43f5e',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    showToast(data.message || 'Error al procesar QR.', 'error');
                }
            }
        } catch (err) {
            console.error(err);
            showToast('Error de conexión al procesar QR.', 'error');
        }
    }

    // Period selector handler
    function onAttendancePeriodFilterChange() {
        const period = document.getElementById('attendance_period_filter').value;
        const customContainer = document.getElementById('attendance_custom_date_container');
        if (period === 'custom') {
            customContainer.classList.remove('hidden');
        } else {
            customContainer.classList.add('hidden');
            reloadAttendanceData();
        }
    }

    // Dynamic Attendance Data and Pagination (AJAX) - Synchronized with Top Banner!
    function reloadAttendanceData() {
        const tbody = document.getElementById('logs_table_body');
        const period = document.getElementById('attendance_period_filter')?.value || 'today';
        const dateVal = document.getElementById('date-filter')?.value || '';

        if (tbody) tbody.style.opacity = '0.4';

        let url = `{{ route('api.asistencia.logs') }}?period=${encodeURIComponent(period)}`;
        if (period === 'custom' || dateVal) {
            url += `&date=${encodeURIComponent(dateVal)}`;
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                return res.json();
            })
            .then(data => {
                cachedLogsData = data.logs || [];

                // Synchronize Top Summary Banner cards with selected period data!
                const entriesVal = document.getElementById('today_entries_count_val');
                const inGymVal = document.getElementById('currently_in_gym_count_val');
                const completedVal = document.getElementById('completed_sessions_count_val');
                const labelTitle = document.getElementById('period_label_title');

                if (labelTitle && data.period_label) {
                    labelTitle.textContent = `Entradas ${data.period_label}`;
                }
                if (entriesVal && data.period_entries_count !== undefined) {
                    entriesVal.textContent = data.period_entries_count;
                }
                if (inGymVal && data.currently_in_gym_count !== undefined) {
                    inGymVal.textContent = data.currently_in_gym_count;
                }
                if (completedVal && data.completed_sessions_count !== undefined) {
                    completedVal.textContent = data.completed_sessions_count;
                }

                currentLogsPage = 1;
                renderLogsTablePage();
                if (tbody) tbody.style.opacity = '1';
            })
            .catch(err => {
                console.error('Error al actualizar asistencias:', err);
                if (tbody) tbody.style.opacity = '1';
            });
    }

    // Render 10 logs per page slice
    function renderLogsTablePage() {
        const tbody = document.getElementById('logs_table_body');
        if (!tbody) return;

        const totalLogs = cachedLogsData.length;
        const totalPages = Math.ceil(totalLogs / logsPerPage) || 1;

        if (currentLogsPage > totalPages) currentLogsPage = totalPages;
        if (currentLogsPage < 1) currentLogsPage = 1;

        const startIndex = (currentLogsPage - 1) * logsPerPage;
        const endIndex = startIndex + logsPerPage;
        const pageSlice = cachedLogsData.slice(startIndex, endIndex);

        const colSpan = isSuperadminAllMode ? 6 : 5;

        if (totalLogs === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${colSpan}" class="py-12 text-center text-slate-500">
                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                        <p>No se encontraron registros de asistencia para el periodo seleccionado.</p>
                    </td>
                </tr>
            `;
        } else {
            tbody.innerHTML = pageSlice.map(log => {
                const gymCell = isSuperadminAllMode 
                    ? `<td class="py-3.5 px-3 text-center">
                           <span class="px-2 py-0.5 text-[10px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/20 rounded-md">
                               ${escapeHtml(log.gym_name)}
                           </span>
                       </td>` 
                    : '';

                const checkOutCell = log.check_out 
                    ? `<span class="block font-bold text-slate-300 text-xs">${log.check_out.time}</span>
                       <span class="block text-[10px] text-slate-500">${log.check_out.date}</span>`
                    : `<span class="px-2 py-0.5 text-[10px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-md">
                          En Sala
                       </span>`;

                const actionCell = log.check_out 
                    ? `<span class="text-xs text-slate-500 italic font-semibold">Completado</span>`
                    : `<button type="button" onclick="submitCheckOut(event, '${log.check_out_url}')" class="px-3 py-1.5 bg-slate-950 text-lime-400 border border-lime-500/30 hover:bg-lime-500 hover:text-slate-950 text-xs font-bold rounded-lg transition-all">
                           Marcar Salida
                       </button>`;

                return `
                    <tr class="hover:bg-slate-850/40 transition-colors">
                        <td class="py-3.5 px-3">
                            <div class="flex items-center gap-3">
                                <img src="${log.user_photo}" class="w-9 h-9 rounded-full object-cover border border-slate-800 shrink-0">
                                <div class="max-w-[170px] sm:max-w-[220px]">
                                    <span class="block font-bold text-slate-100 text-xs truncate">${escapeHtml(log.user_name)}</span>
                                    <span class="block text-[10px] text-slate-500 truncate">${escapeHtml(log.user_email)}</span>
                                </div>
                            </div>
                        </td>
                        ${gymCell}
                        <td class="py-3.5 px-3 text-center">
                            <span class="block font-bold text-slate-200 text-xs">${log.check_in_time}</span>
                            <span class="block text-[10px] text-slate-500">${log.check_in_date}</span>
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            ${checkOutCell}
                        </td>
                        <td class="py-3.5 px-3 text-center">
                            <span class="px-2 py-0.5 text-[10px] font-bold border rounded-md ${log.entry_method_badge}">
                                ${log.entry_method_label}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            ${actionCell}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Update pagination UI controls
        const infoSpan = document.getElementById('logs_pagination_info');
        const pageSpan = document.getElementById('logs_page_display');
        const prevBtn = document.getElementById('logs_prev_btn');
        const nextBtn = document.getElementById('logs_next_btn');

        if (infoSpan) {
            if (totalLogs === 0) {
                infoSpan.textContent = "No hay asistencias registradas.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalLogs);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalLogs} accesos`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentLogsPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentLogsPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentLogsPage >= totalPages);

        if (window.lucide) window.lucide.createIcons();
    }

    function changeLogsPage(delta) {
        currentLogsPage += delta;
        renderLogsTablePage();
    }

    // Auto-trigger session flash messages on page load as toasts & initialize logs
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(isset($errors) && $errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif

        if (typeof TomSelect !== 'undefined') {
            const selectEl = document.getElementById('user_id_select');
            if (selectEl) {
                new TomSelect(selectEl, {
                    create: false,
                    placeholder: 'Buscar atleta con DNI...'
                });
            }
        }

        reloadAttendanceData();
    });
</script>

<!-- LIVE QR SCANNER MODAL (OPTIMIZED FOR LOW QUALITY WEBCAMS) -->
<div id="qr_scanner_modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md hidden p-4 animate-fade-in">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl relative space-y-4">
        <button type="button" onclick="closeQrScannerModal()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-100 hover:bg-slate-800 rounded-xl transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <h3 class="text-lg font-extrabold text-slate-100 flex items-center justify-center gap-2">
                <i data-lucide="camera" class="w-5 h-5 text-lime-400"></i>
                Escanear Código QR
            </h3>
            <p class="text-xs text-slate-400 mt-1">Apunta el carnet digital a la cámara o sube la imagen del QR.</p>
        </div>

        <!-- Viewport Container -->
        <div id="qr_reader_viewport" class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 min-h-[260px] flex items-center justify-center relative shadow-inner">
            <!-- Html5Qrcode renders video viewport here -->
        </div>

        <!-- Controls: Camera Selector & Image Upload Fallback for blurry/low quality webcams -->
        <div class="flex items-center justify-between gap-2 pt-1">
            <select id="qr_camera_select" onchange="switchQrCamera(this.value)" class="bg-slate-950 border border-slate-800 text-slate-200 rounded-xl px-2.5 py-1.5 font-bold text-xs focus:outline-none focus:border-lime-500/50 hidden max-w-[200px] truncate cursor-pointer">
                <option value="">Cámara por defecto</option>
            </select>

            <label class="cursor-pointer px-3 py-1.5 bg-slate-950 hover:bg-slate-850 border border-slate-800 text-slate-300 hover:text-lime-400 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0">
                <i data-lucide="upload" class="w-3.5 h-3.5 text-lime-400"></i>
                <span>Subir QR / Imagen</span>
                <input type="file" id="qr_file_input" accept="image/*" onchange="scanQrFromFile(this)" class="hidden">
            </label>
        </div>

        <div class="text-center pt-2 border-t border-slate-850">
            <button type="button" onclick="closeQrScannerModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-colors">
                Cancelar Escaneo
            </button>
        </div>
    </div>
</div>
<!-- CDN FALLBACKS FOR LIBRARIES -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endsection
