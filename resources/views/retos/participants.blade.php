@extends('layouts.admin')

@section('title', 'Participantes del Reto')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('retos.index') }}" class="inline-flex items-center gap-1.5 text-xs text-lime-400 font-extrabold hover:underline mb-2 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver a Retos & Clasificación
            </a>
            <h1 class="text-3xl font-black text-slate-100 tracking-tight flex items-center gap-3">
                {{ $challenge->title }}
                <span class="px-2.5 py-0.5 text-xs font-extrabold rounded-lg uppercase tracking-wider border {{ $challenge->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                    {{ $challenge->is_active ? 'Reto Activo' : 'Reto Inactivo' }}
                </span>
            </h1>
            <p class="text-slate-400 text-xs mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 font-medium">
                <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5 text-lime-400"></i> Vigencia: <strong class="text-slate-200">{{ \Carbon\Carbon::parse($challenge->start_date)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($challenge->end_date)->format('d/m/Y') }}</strong></span>
                <span>•</span>
                <span class="flex items-center gap-1 text-amber-400"><i data-lucide="zap" class="w-3.5 h-3.5"></i> Recompensa: <strong>+{{ number_format($challenge->xp_reward) }} XP</strong></span>
                <span>•</span>
                <span class="flex items-center gap-1 text-emerald-400"><i data-lucide="coins" class="w-3.5 h-3.5"></i> Monedas: <strong>+{{ number_format($challenge->token_reward, 2) }}</strong></span>
            </p>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Manual Enrollment Card with Real-time DNI Search & Summary Stats -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Inscribe Manual Card -->
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

                @if(!$challenge->is_active)
                    <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-2xl flex items-start gap-3">
                        <i data-lucide="slash" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold leading-relaxed">
                            Este reto se encuentra inhabilitado. No se pueden inscribir nuevos atletas.
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
                    <form id="enroll-participant-form" action="{{ route('retos.enroll_participant') }}" method="POST" onsubmit="submitEnrollParticipant(event)" class="space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="challenge_id" value="{{ $challenge->id }}">
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
                                <option value="" disabled selected>-- Ver lista completa de disponibles --</option>
                                @foreach($availableClients as $client)
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

                        <button type="submit" id="enroll-participant-submit-btn" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                            Inscribir Atleta al Reto
                        </button>
                    </form>
                @endif
            </div>

            <!-- Stats Summary Card -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="border-b border-slate-800 pb-3 flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-wider">Resumen de Participación</h3>
                </div>
                
                <div class="space-y-3 text-xs font-semibold text-slate-300">
                    <div class="flex justify-between items-center p-2.5 bg-slate-950/60 rounded-xl border border-slate-850">
                        <span class="text-slate-400">Total Inscritos:</span>
                        <span class="font-black text-slate-100 text-sm" id="stat_total_enrolled">{{ $participants->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-slate-950/60 rounded-xl border border-slate-850">
                        <span class="text-slate-400">En Curso (Activos):</span>
                        <span class="font-black text-purple-400 text-sm" id="stat_active">{{ $participants->where('status', 'active')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-slate-950/60 rounded-xl border border-slate-850">
                        <span class="text-slate-400">Retos Completados:</span>
                        <span class="font-black text-emerald-400 text-sm" id="stat_completed">{{ $participants->where('status', 'completed')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-slate-950/60 rounded-xl border border-slate-850">
                        <span class="text-slate-400">Retos Fallidos:</span>
                        <span class="font-black text-rose-400 text-sm" id="stat_failed">{{ $participants->where('status', 'failed')->count() }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Panel: Participants List Table Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                
                <!-- Clean Filters & Search Bar (Flex wrap without horizontal scrollbar) -->
                <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
                    
                    <!-- Status Filter Tabs: Flex Wrap Pills -->
                    <div class="flex flex-wrap items-center gap-1.5 bg-slate-950 p-1.5 rounded-2xl border border-slate-850">
                        <button type="button" onclick="setParticipantStatusFilter('all')" id="participant-status-filter-all" class="participant-status-tab-btn px-3 py-1.5 rounded-xl text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                            Todos (<span id="tab-count-all">{{ $participants->count() }}</span>)
                        </button>
                        <button type="button" onclick="setParticipantStatusFilter('active')" id="participant-status-filter-active" class="participant-status-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            En Curso (<span id="tab-count-active">{{ $participants->where('status', 'active')->count() }}</span>)
                        </button>
                        <button type="button" onclick="setParticipantStatusFilter('completed')" id="participant-status-filter-completed" class="participant-status-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            Completados (<span id="tab-count-completed">{{ $participants->where('status', 'completed')->count() }}</span>)
                        </button>
                        <button type="button" onclick="setParticipantStatusFilter('failed')" id="participant-status-filter-failed" class="participant-status-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                            Fallidos (<span id="tab-count-failed">{{ $participants->where('status', 'failed')->count() }}</span>)
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full xl:w-60">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" id="search-participant-input" oninput="onParticipantFilterChange()" placeholder="Buscar atleta por nombre..." class="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-850 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-extrabold tracking-wider">
                                <th class="py-3 px-4">Atleta</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                                <th class="py-3 px-4 text-right">Progreso & Actualización</th>
                            </tr>
                        </thead>
                        <tbody id="participants-table-body" class="divide-y divide-slate-800/40 text-xs font-semibold">
                            @forelse($participants as $p)
                                @php
                                    $clientName = trim(($p->user->profile->first_name ?? 'Atleta') . ' ' . ($p->user->profile->last_name ?? ''));
                                    $clientEmail = $p->user->email ?? '';
                                    $photoUrl = $p->user->profile->profile_photo ? asset($p->user->profile->profile_photo) : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
                                @endphp
                                <tr id="participant_row_{{ $p->id }}" 
                                    data-participant-row
                                    data-name="{{ strtolower($clientName) }}"
                                    data-email="{{ strtolower($clientEmail) }}"
                                    data-status="{{ $p->status }}"
                                    class="hover:bg-slate-850/40 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                            <div class="overflow-hidden min-w-0">
                                                <span class="block font-bold text-slate-100 truncate">{{ $clientName }}</span>
                                                <span class="block text-[10px] text-slate-400 truncate">{{ $clientEmail }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span id="participant_badge_{{ $p->id }}" class="px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider {{ $p->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($p->status === 'failed' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20') }}">
                                            {{ $p->status === 'completed' ? 'Completado' : ($p->status === 'failed' ? 'Fallido' : 'En Curso') }}
                                        </span>
                                        @if($p->completed_at)
                                            <span class="block text-[8px] text-slate-500 mt-1 font-semibold">{{ \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right" id="participant_action_cell_{{ $p->id }}">
                                        @if($p->status === 'active')
                                            <form action="{{ route('retos.update_participant', $p->id) }}" method="POST" onsubmit="submitUpdateParticipant(event, {{ $p->id }})" class="flex items-center justify-end gap-2 text-xs font-bold">
                                                @csrf
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] text-slate-400 uppercase">Valor:</span>
                                                    <input type="number" name="progress_value" value="{{ $p->progress_value }}" min="0" required class="w-16 bg-slate-950 border border-slate-850 rounded-xl px-2 py-1 text-center font-extrabold text-slate-100 focus:outline-none focus:border-lime-500/50">
                                                </div>
                                                <select name="status" class="bg-slate-950 border border-slate-850 rounded-xl px-2.5 py-1 text-slate-300 text-xs focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                                    <option value="active" selected>En Curso</option>
                                                    <option value="completed">Completado</option>
                                                    <option value="failed">Fallido</option>
                                                </select>
                                                <button type="submit" class="px-3 py-1 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1">
                                                    Guardar
                                                </button>
                                            </form>
                                        @else
                                            <div class="flex items-center justify-end gap-2 text-xs font-semibold text-slate-400 italic">
                                                <span>Progreso Final: <strong class="text-slate-200 font-bold">{{ $p->progress_value }}</strong></span>
                                                <span class="text-slate-600">•</span>
                                                <span class="text-slate-500">Finalizado</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="no_participants_empty">
                                    <td colspan="3" class="py-16 text-center text-slate-500">
                                        <i data-lucide="users-2" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                        <p class="font-bold text-slate-400">No hay participantes registrados para este reto</p>
                                        <p class="text-xs text-slate-500 mt-1">Inscribe a tu primer atleta usando el panel de la izquierda.</p>
                                    </td>
                                </tr>
                            @endforelse

                            <tr id="no_participants_search_row" class="hidden">
                                <td colspan="3" class="py-12 text-center text-slate-500">
                                    <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-700 mb-2"></i>
                                    <p class="font-bold text-slate-400 text-sm">No se encontraron participantes que coincidan con la búsqueda.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer Controls -->
                <div id="participant_pagination_container" class="bg-slate-950/60 border border-slate-850 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                    <span id="participant_pagination_info">Mostrando participantes...</span>
                    <div class="flex items-center gap-2">
                        <button type="button" id="participant_prev_page_btn" onclick="changeParticipantPage(-1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                            Anterior
                        </button>
                        <span id="participant_page_number_display" class="font-bold text-slate-200 px-2">Página 1</span>
                        <button type="button" id="participant_next_page_btn" onclick="changeParticipantPage(1)" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition-colors">
                            Siguiente
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
    // Real-time DNI / Name Search Logic
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
                submitEnrollParticipant();
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
        let container = document.getElementById('participant-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'participant-toast-container';
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

    // AJAX Submission: Enroll Participant
    async function submitEnrollParticipant(e) {
        if (e) e.preventDefault();
        const form = document.getElementById('enroll-participant-form');
        const submitBtn = document.getElementById('enroll-participant-submit-btn');

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
                const p = data.participant;
                const tbody = document.getElementById('participants-table-body');
                const emptyMsg = document.getElementById('no_participants_empty');
                if (emptyMsg) emptyMsg.classList.add('hidden');

                const clientName = p.user && p.user.profile 
                    ? `${p.user.profile.first_name || ''} ${p.user.profile.last_name || ''}`.trim()
                    : 'Atleta';
                const clientEmail = p.user ? p.user.email : '';
                const photoUrl = (p.user && p.user.profile && p.user.profile.profile_photo)
                    ? `/${p.user.profile.profile_photo}`
                    : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';

                const tr = document.createElement('tr');
                tr.id = `participant_row_${p.id}`;
                tr.setAttribute('data-participant-row', '');
                tr.setAttribute('data-name', clientName.toLowerCase());
                tr.setAttribute('data-email', clientEmail.toLowerCase());
                tr.setAttribute('data-status', p.status);
                tr.className = 'hover:bg-slate-850/40 transition-colors';

                tr.innerHTML = `
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-3">
                            <img src="${photoUrl}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                            <div class="overflow-hidden min-w-0">
                                <span class="block font-bold text-slate-100 truncate">${escapeHtml(clientName)}</span>
                                <span class="block text-[10px] text-slate-400 truncate">${escapeHtml(clientEmail)}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <span id="participant_badge_${p.id}" class="px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider bg-purple-500/10 text-purple-400 border-purple-500/20">
                            En Curso
                        </span>
                    </td>
                    <td class="py-4 px-4 text-right" id="participant_action_cell_${p.id}">
                        <form action="/retos/participantes/${p.id}/actualizar" method="POST" onsubmit="submitUpdateParticipant(event, ${p.id})" class="flex items-center justify-end gap-2 text-xs font-bold">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] text-slate-400 uppercase">Valor:</span>
                                <input type="number" name="progress_value" value="${p.progress_value}" min="0" required class="w-16 bg-slate-950 border border-slate-850 rounded-xl px-2 py-1 text-center font-extrabold text-slate-100 focus:outline-none focus:border-lime-500/50">
                            </div>
                            <select name="status" class="bg-slate-950 border border-slate-850 rounded-xl px-2.5 py-1 text-slate-300 text-xs focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                <option value="active" selected>En Curso</option>
                                <option value="completed">Completado</option>
                                <option value="failed">Fallido</option>
                            </select>
                            <button type="submit" class="px-3 py-1 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1">
                                Guardar
                            </button>
                        </form>
                    </td>
                `;

                const searchRow = document.getElementById('no_participants_search_row');
                if (searchRow) {
                    tbody.insertBefore(tr, searchRow);
                } else {
                    tbody.appendChild(tr);
                }

                // Remove newly enrolled user from fallback select list
                const selectEl = document.getElementById('user_id_select');
                if (selectEl) {
                    const optToRemove = selectEl.querySelector(`option[value="${p.user_id}"]`);
                    if (optToRemove) optToRemove.remove();
                }

                if (window.lucide) window.lucide.createIcons();

                clearSelectedClient();
                updateCountsUI();
                renderParticipantsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al inscribir al atleta.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al intentar inscribir al atleta.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // AJAX Submission: Update Participant Status & Progress
    async function submitUpdateParticipant(e, pId) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');

        setBtnLoading(submitBtn, true, 'Guardando...');

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
                const p = data.participant;
                const row = document.getElementById(`participant_row_${pId}`);
                if (row) {
                    row.setAttribute('data-status', p.status);

                    const badge = document.getElementById(`participant_badge_${pId}`);
                    if (badge) {
                        let badgeClass = 'bg-purple-500/10 text-purple-400 border-purple-500/20';
                        let badgeText = 'En Curso';

                        if (p.status === 'completed') {
                            badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                            badgeText = 'Completado';
                        } else if (p.status === 'failed') {
                            badgeClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
                            badgeText = 'Fallido';
                        }

                        badge.className = `px-2.5 py-1 text-[9px] font-extrabold uppercase rounded-lg border tracking-wider ${badgeClass}`;
                        badge.textContent = badgeText;
                    }

                    const actionCell = document.getElementById(`participant_action_cell_${pId}`);
                    if (actionCell && p.status !== 'active') {
                        actionCell.innerHTML = `
                            <div class="flex items-center justify-end gap-2 text-xs font-semibold text-slate-400 italic">
                                <span>Progreso Final: <strong class="text-slate-200 font-bold">${p.progress_value}</strong></span>
                                <span class="text-slate-600">•</span>
                                <span class="text-slate-500">Finalizado</span>
                            </div>
                        `;
                    }
                }

                if (window.lucide) window.lucide.createIcons();

                updateCountsUI();
                renderParticipantsPage();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Error al actualizar el participante.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ocurrió un error al actualizar el participante.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    // Update Counts UI Summary Cards & Tab Badges
    function updateCountsUI() {
        const allRows = document.querySelectorAll('[data-participant-row]');
        let cAll = allRows.length;
        let cActive = 0;
        let cCompleted = 0;
        let cFailed = 0;

        allRows.forEach(r => {
            const st = r.getAttribute('data-status');
            if (st === 'active') cActive++;
            else if (st === 'completed') cCompleted++;
            else if (st === 'failed') cFailed++;
        });

        const statTotal = document.getElementById('stat_total_enrolled');
        const statActive = document.getElementById('stat_active');
        const statCompleted = document.getElementById('stat_completed');
        const statFailed = document.getElementById('stat_failed');

        if (statTotal) statTotal.textContent = cAll;
        if (statActive) statActive.textContent = cActive;
        if (statCompleted) statCompleted.textContent = cCompleted;
        if (statFailed) statFailed.textContent = cFailed;

        const tabAll = document.getElementById('tab-count-all');
        const tabActive = document.getElementById('tab-count-active');
        const tabCompleted = document.getElementById('tab-count-completed');
        const tabFailed = document.getElementById('tab-count-failed');

        if (tabAll) tabAll.textContent = cAll;
        if (tabActive) tabActive.textContent = cActive;
        if (tabCompleted) tabCompleted.textContent = cCompleted;
        if (tabFailed) tabFailed.textContent = cFailed;
    }

    // Filtering & Pagination System (8 rows per page)
    let currentParticipantPage = 1;
    let currentParticipantStatusFilter = 'all';
    const participantItemsPerPage = 8;

    function setParticipantStatusFilter(status) {
        currentParticipantStatusFilter = status;

        const tabs = document.querySelectorAll('.participant-status-tab-btn');
        tabs.forEach(tab => {
            tab.className = "participant-status-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
        });

        const activeTab = document.getElementById('participant-status-filter-' + status);
        if (activeTab) {
            activeTab.className = "participant-status-tab-btn px-3 py-1.5 rounded-xl text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all";
        }

        currentParticipantPage = 1;
        renderParticipantsPage();
    }

    function onParticipantFilterChange() {
        currentParticipantPage = 1;
        renderParticipantsPage();
    }

    function renderParticipantsPage() {
        const searchVal = (document.getElementById('search-participant-input')?.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('[data-participant-row]'));

        const filtered = rows.filter(r => {
            const name = r.getAttribute('data-name') || '';
            const email = r.getAttribute('data-email') || '';
            const status = r.getAttribute('data-status') || '';

            const matchesStatus = (currentParticipantStatusFilter === 'all') || (status === currentParticipantStatusFilter);
            const matchesSearch = !searchVal || name.includes(searchVal) || email.includes(searchVal);

            return matchesStatus && matchesSearch;
        });

        const totalFiltered = filtered.length;
        const totalPages = Math.ceil(totalFiltered / participantItemsPerPage) || 1;

        if (currentParticipantPage > totalPages) currentParticipantPage = totalPages;
        if (currentParticipantPage < 1) currentParticipantPage = 1;

        const startIndex = (currentParticipantPage - 1) * participantItemsPerPage;
        const endIndex = startIndex + participantItemsPerPage;

        rows.forEach(r => r.classList.add('hidden'));

        filtered.slice(startIndex, endIndex).forEach(r => r.classList.remove('hidden'));

        const noSearchRow = document.getElementById('no_participants_search_row');
        if (noSearchRow) {
            if (totalFiltered === 0 && rows.length > 0) {
                noSearchRow.classList.remove('hidden');
            } else {
                noSearchRow.classList.add('hidden');
            }
        }

        const infoSpan = document.getElementById('participant_pagination_info');
        const pageSpan = document.getElementById('participant_page_number_display');
        const prevBtn = document.getElementById('participant_prev_page_btn');
        const nextBtn = document.getElementById('participant_next_page_btn');

        if (infoSpan) {
            if (totalFiltered === 0) {
                infoSpan.textContent = "No hay participantes para mostrar.";
            } else {
                const fromNum = startIndex + 1;
                const toNum = Math.min(endIndex, totalFiltered);
                infoSpan.textContent = `Mostrando ${fromNum}-${toNum} de ${totalFiltered} participantes`;
            }
        }

        if (pageSpan) pageSpan.textContent = `Página ${currentParticipantPage} de ${totalPages}`;
        if (prevBtn) prevBtn.disabled = (currentParticipantPage <= 1);
        if (nextBtn) nextBtn.disabled = (currentParticipantPage >= totalPages);
    }

    function changeParticipantPage(delta) {
        currentParticipantPage += delta;
        renderParticipantsPage();
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

        updateCountsUI();
        renderParticipantsPage();
    });
</script>
@endsection
