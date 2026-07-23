@extends('layouts.admin')

@section('title', 'Control de Asistencia')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Control de Asistencia</h1>
            <p class="text-slate-400 text-xs mt-1">Registra la entrada y salida manual de atletas, y supervisa el flujo de accesos.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Check-in Panel -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="check-square" class="text-lime-400 w-4 h-4"></i>
                    Check-in Manual
                </h3>
                
                @php
                    $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                @endphp

                @if($activeGymId === 'all')
                    <div class="mt-4 p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs rounded-xl flex items-start gap-2.5">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold">
                            Estás en la vista de todas las sucursales. Selecciona una sucursal específica en el menú superior para poder registrar asistencias.
                        </p>
                    </div>
                @else
                    <form id="checkin_form" action="{{ route('asistencia.check_in') }}" method="POST" onsubmit="submitCheckIn(event)" class="mt-4 space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="user_id" id="selected_user_id" value="{{ old('user_id') }}">

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
                            Registrar Entrada
                        </button>
                    </form>
                @endif
            </div>

            <!-- Quick Information Summary -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg space-y-4">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="activity" class="text-lime-400 w-4 h-4"></i>
                    Aforo del Día
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl text-center">
                        <span id="today_entries_count_val" class="block text-2xl font-black text-lime-400">
                            {{ $todayEntriesCount }}
                        </span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Entradas Hoy</span>
                    </div>
                    <div class="bg-slate-950/40 p-4 border border-slate-850 rounded-xl text-center">
                        <span id="currently_in_gym_count_val" class="block text-2xl font-black text-lime-400">
                            {{ $currentlyInGymCount }}
                        </span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">En el Gimnasio</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Logs feed -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 mb-4 gap-3">
                    <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="history" class="text-lime-400 w-4 h-4"></i>
                        Historial de Accesos
                    </h3>
                    <div class="flex items-center gap-2 m-0">
                        <label for="date-filter" class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Fecha:</label>
                        <input type="date" id="date-filter" name="date" value="{{ $selectedDate }}" onchange="reloadAttendanceData(this.value)" onclick="this.showPicker()" class="text-xs bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-lime-400 font-bold focus:outline-none focus:border-lime-500 transition-all cursor-pointer">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                                <th class="py-3 px-4 text-center">Atleta</th>
                                <th class="py-3 px-4 text-center">Entrada</th>
                                <th class="py-3 px-4 text-center">Salida</th>
                                <th class="py-3 px-4 text-center">Medio</th>
                                <th class="py-3 px-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="logs_table_body" class="divide-y divide-slate-800/40 text-sm transition-opacity duration-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-800/10 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $log->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover border border-slate-800 shrink-0">
                                            <div class="overflow-hidden">
                                                <span class="block font-bold text-slate-200 truncate">{{ $log->user->profile->first_name ?? 'Atleta' }} {{ $log->user->profile->last_name ?? '' }}</span>
                                                <span class="block text-[10px] text-slate-500 truncate">{{ $log->user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="block font-semibold text-slate-300 text-xs">{{ \Carbon\Carbon::parse($log->check_in)->format('H:i') }}</span>
                                        <span class="block text-[9px] text-slate-500">{{ \Carbon\Carbon::parse($log->check_in)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($log->check_out)
                                            <span class="block font-semibold text-slate-400 text-xs">{{ \Carbon\Carbon::parse($log->check_out)->format('H:i') }}</span>
                                            <span class="block text-[9px] text-slate-550">{{ \Carbon\Carbon::parse($log->check_out)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[9px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded">
                                                En Sala
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $methodMap = [
                                                'admin' => 'Admin',
                                                'biometric' => 'Biométrico',
                                                'rfid' => 'RFID',
                                                'app_manual' => 'App Móvil'
                                            ];
                                            $methodBadge = [
                                                'admin' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'biometric' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                'rfid' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'app_manual' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 text-[9px] font-bold border rounded-md {{ $methodBadge[$log->entry_method] ?? 'bg-slate-950 text-slate-500 border-slate-850' }}">
                                            {{ $methodMap[$log->entry_method] ?? $log->entry_method }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if(!$log->check_out)
                                            <button type="button" onclick="submitCheckOut(event, '{{ route('asistencia.check_out', $log->id) }}')" class="px-2.5 py-1.5 bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-750 hover:text-slate-100 text-xs font-bold rounded-lg transition-all text-center leading-tight">
                                                Marcar <br> Salida
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-500 italic font-semibold">Completado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-500">
                                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                                        <p>No se encontraron registros de asistencia hoy.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    let searchDebounceTimeout = null;

    // Show temporary toast alerts (matches productos.blade.php style)
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

    // Capture ENTER keypress on search input to auto-pick and submit without page reload!
    async function onDniSearchKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();

            const selectedId = document.getElementById('selected_user_id')?.value;
            if (selectedId) {
                // Already selected a client, proceed to submit check-in via AJAX!
                submitCheckIn();
                return;
            }

            const searchInput = document.getElementById('dni_search_input');
            const query = (searchInput?.value || '').trim();

            if (!query) {
                showToast('Escribe un DNI o nombre para realizar la búsqueda.', 'warning');
                return;
            }

            // Quick lookup to pick best match
            try {
                const res = await fetch(`{{ route('api.clientes.search_dni') }}?q=${encodeURIComponent(query)}`);
                const clients = await res.json();

                if (clients && clients.length > 0) {
                    const topMatch = clients[0];
                    pickClient(topMatch.id, topMatch.name, topMatch.dni, topMatch.email, topMatch.photo);
                    // Automatically submit checkin
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

    // Hide search dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const searchInput = document.getElementById('dni_search_input');
        const resultsDropdown = document.getElementById('search_results_dropdown');
        if (searchInput && resultsDropdown && !searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
            resultsDropdown.classList.add('hidden');
        }
    });

    // Helper for button loading status
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
                showToast(data.message, 'success');
                clearSelectedClient();
                const currentDateFilter = document.getElementById('date-filter')?.value || '';
                reloadAttendanceData(currentDateFilter);
            } else {
                showToast(data.message || 'Error al procesar check-in.', 'error');
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
                showToast(data.message, 'success');
                const currentDateFilter = document.getElementById('date-filter')?.value || '';
                reloadAttendanceData(currentDateFilter);
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

    // Dynamic Attendance Data and Capacity Counter Reload (AJAX)
    function reloadAttendanceData(dateVal) {
        const tbody = document.getElementById('logs_table_body');
        const selectedDate = dateVal || document.getElementById('date-filter')?.value || '';

        if (tbody) tbody.style.opacity = '0.4';

        fetch(`{{ route('api.asistencia.logs') }}?date=${encodeURIComponent(selectedDate)}`)
            .then(res => res.json())
            .then(data => {
                const logs = data.logs || [];

                // Update today summary counters
                const entriesVal = document.getElementById('today_entries_count_val');
                const inGymVal = document.getElementById('currently_in_gym_count_val');
                if (entriesVal && data.today_entries_count !== undefined) entriesVal.textContent = data.today_entries_count;
                if (inGymVal && data.currently_in_gym_count !== undefined) inGymVal.textContent = data.currently_in_gym_count;

                // Update Sidebar capacity metrics if present in DOM
                const sidebarCount = document.querySelector('.aforo-count-val');
                const sidebarBadge = document.querySelector('.aforo-pct-badge-val');
                const sidebarBar = document.getElementById('aforo-bar');
                if (sidebarCount && data.aforo_current_users !== undefined && data.aforo_max_users !== undefined) {
                    sidebarCount.textContent = `${data.aforo_current_users}/${data.aforo_max_users}`;
                    const pct = data.aforo_max_users > 0 ? Math.round((data.aforo_current_users / data.aforo_max_users) * 100) : 0;
                    if (sidebarBadge) sidebarBadge.textContent = `${pct}%`;
                    if (sidebarBar) sidebarBar.style.width = `${Math.min(100, Math.max(2, pct))}%`;
                }

                if (logs.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                                <p>No se encontraron registros de asistencia para la fecha elegida.</p>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = logs.map(log => {
                        const checkOutCell = log.check_out 
                            ? `<span class="block font-semibold text-slate-400 text-xs">${log.check_out.time}</span>
                               <span class="block text-[9px] text-slate-550">${log.check_out.date}</span>`
                            : `<span class="px-2 py-0.5 text-[9px] font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded">
                                  En Sala
                               </span>`;

                        const actionCell = log.check_out 
                            ? `<span class="text-xs text-slate-500 italic font-semibold">Completado</span>`
                            : `<button type="button" onclick="submitCheckOut(event, '${log.check_out_url}')" class="px-2.5 py-1.5 bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-750 hover:text-slate-100 text-xs font-bold rounded-lg transition-all text-center leading-tight">
                                   Marcar <br> Salida
                               </button>`;

                        return `
                            <tr class="hover:bg-slate-800/10 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="${log.user_photo}" class="w-8 h-8 rounded-full object-cover border border-slate-800 shrink-0">
                                        <div class="overflow-hidden">
                                            <span class="block font-bold text-slate-200 truncate">${escapeHtml(log.user_name)}</span>
                                            <span class="block text-[10px] text-slate-500 truncate">${escapeHtml(log.user_email)}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="block font-semibold text-slate-300 text-xs">${log.check_in_time}</span>
                                    <span class="block text-[9px] text-slate-500">${log.check_in_date}</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    ${checkOutCell}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2 py-0.5 text-[9px] font-bold border rounded-md ${log.entry_method_badge}">
                                        ${log.entry_method_label}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    ${actionCell}
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                tbody.style.opacity = '1';
                if (window.lucide) window.lucide.createIcons();
            })
            .catch(err => {
                console.error('Error al actualizar asistencias:', err);
                if (tbody) tbody.style.opacity = '1';
            });
    }

    // Auto-trigger session flash messages on page load as toasts
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif
    });
</script>
@endsection
