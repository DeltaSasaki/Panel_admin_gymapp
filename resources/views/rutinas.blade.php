@extends('layouts.admin')

@section('title', 'Planes de Rutinas')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Planes de Rutinas</h1>
            <p class="text-slate-400 text-xs mt-1">Crea, edita y asigna programas de entrenamiento de fuerza y acondicionamiento.</p>
        </div>
        <a href="{{ route('rutinas.crear') }}" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4 stroke-[3px]"></i>
            Crear Plan de Rutina
        </a>
    </div>

    <!-- Stats & Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-slate-900/40 p-4 rounded-xl border border-slate-800 flex items-center gap-4">
            <div class="p-3 bg-lime-500/10 text-lime-400 rounded-xl">
                <i data-lucide="dumbbell" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block text-slate-400 text-xs font-semibold">Total Plantillas</span>
                <span class="block text-xl font-bold text-slate-100">{{ $rutinas->count() }} Rutinas</span>
            </div>
        </div>
        <div class="bg-slate-900/40 p-4 rounded-xl border border-slate-800 flex items-center gap-4">
            <div class="p-3 bg-purple-500/10 text-purple-400 rounded-xl">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block text-slate-400 text-xs font-semibold">Clientes Entrenando</span>
                <span class="block text-xl font-bold text-slate-100">{{ $activeAssignmentsCount }} Atletas</span>
            </div>
        </div>
        <div class="bg-slate-900/40 p-4 rounded-xl border border-slate-800 flex items-center gap-4">
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="block text-slate-400 text-xs font-semibold">Más Popular</span>
                <span class="block text-xl font-bold text-slate-100 truncate max-w-[200px]" title="{{ $popularRoutineName }}">{{ $popularRoutineName }}</span>
            </div>
        </div>
    </div>

    <!-- Routine Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        @forelse($rutinas as $rutina)
            <!-- Routine Card -->
            <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 hover:border-slate-700/80 transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        @if($rutina->difficulty === 'advanced')
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-purple-500/10 text-purple-400 rounded-full border border-purple-500/20">Avanzado</span>
                        @elseif($rutina->difficulty === 'intermediate')
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-lime-500/10 text-lime-400 rounded-full border border-lime-500/20">Intermedio</span>
                        @else
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20">Principiante</span>
                        @endif

                        <span class="text-xs text-slate-500 font-semibold flex items-center gap-1">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i> <span id="active_count_{{ $rutina->id }}">{{ $rutina->active_assignments_count }}</span> activos
                        </span>
                    </div>
                    <h3 class="font-bold text-lg text-slate-100">{{ $rutina->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $rutina->description ?? 'Sin descripción disponible.' }}</p>
                    
                    <div class="mt-4 grid grid-cols-2 gap-3 bg-slate-950/40 p-3 rounded-xl border border-slate-850/60 text-xs">
                        <div>
                            <span class="block text-slate-500 font-medium">Frecuencia</span>
                            <span class="font-bold text-slate-300">{{ $rutina->days_per_week }}x por semana</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Duración</span>
                            <span class="font-bold text-slate-300">{{ $rutina->duration_weeks }} Semanas</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-850/50 flex gap-2">
                    <a href="{{ route('rutinas.ejercicios', $rutina->id) }}" class="flex-1 py-2 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-lg border border-slate-850 hover:border-slate-700 text-slate-300 transition-colors text-center block">
                        Editar Ejercicios
                    </a>
                    <button onclick="openAssignRoutineModal('{{ route('rutinas.assign', $rutina->id) }}', '{{ addslashes($rutina->name) }}', {{ $rutina->id }})" class="px-3 py-2 bg-lime-500 hover:bg-lime-400 text-slate-950 font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                        <i data-lucide="link" class="w-3.5 h-3.5"></i> Asignar
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                <i data-lucide="dumbbell" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                <p>No se encontraron rutinas registradas.</p>
            </div>
        @endforelse

    </div>
</div>

<!-- ================= MODAL: ASIGNAR RUTINA ================= -->
<div id="assign-routine-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-bold text-lg text-slate-100">Asignar Rutina</h3>
                <span id="modal-routine-name" class="text-xs text-lime-400 font-semibold"></span>
            </div>
            <button type="button" onclick="toggleModal('assign-routine-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="assign-routine-form" method="POST" onsubmit="submitAssignRoutine(event)" class="space-y-4">
            @csrf
            <input type="hidden" id="assign-routine-id-input" value="">
            <input type="hidden" name="user_id" id="assign_routine_selected_user_id" value="" required>

            <!-- Real-time DNI / Name Search -->
            <div class="relative">
                <label for="routine_dni_search_input" class="block text-xs font-bold uppercase text-slate-400 mb-1.5 flex justify-between items-center">
                    <span>Buscar por DNI o Nombre</span>
                </label>
                <div class="relative">
                    <input type="text" id="routine_dni_search_input" placeholder="Escribe el DNI o nombre del atleta..." autocomplete="off"
                           class="w-full bg-slate-950 border border-slate-850 rounded-xl pl-9 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-3"></i>
                </div>

                <!-- Live Results Dropdown -->
                <div id="routine_search_results_dropdown" class="absolute left-0 right-0 top-full mt-1 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl z-50 max-h-52 overflow-y-auto hidden">
                    <!-- Dynamic AJAX content populated here -->
                </div>
            </div>

            <!-- Selected Client Preview Card -->
            <div id="routine_selected_client_card" class="hidden p-3.5 bg-slate-950/80 border border-lime-500/30 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <img id="routine_card_client_photo" src="" class="w-9 h-9 rounded-full object-cover border border-slate-800 shrink-0">
                        <div class="min-w-0">
                            <h4 id="routine_card_client_name" class="font-bold text-slate-100 text-xs truncate"></h4>
                            <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                <span id="routine_card_client_dni" class="px-1.5 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[9px] font-mono font-bold rounded"></span>
                                <span id="routine_card_client_email" class="text-[10px] text-slate-500 truncate"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="clearRoutineSelectedClient()" class="p-1 text-slate-400 hover:text-rose-400 hover:bg-slate-850 rounded-lg transition-colors shrink-0" title="Cambiar Atleta">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Fallback Select Dropdown -->
            <div class="pt-1 border-t border-slate-850">
                <label for="routine_user_id_select" class="block text-slate-500 text-[10px] uppercase tracking-wider mb-1">O selecciona de la lista con DNI:</label>
                <select id="routine_user_id_select" onchange="selectRoutineClientFromDropdown(this)" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="" disabled selected>-- Ver lista completa con DNI --</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" 
                                data-name="{{ $cliente->profile->first_name ?? 'Cliente' }} {{ $cliente->profile->last_name ?? '' }}" 
                                data-dni="{{ $cliente->profile->dni ?? 'Sin DNI' }}" 
                                data-email="{{ $cliente->email }}" 
                                data-photo="{{ $cliente->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}">
                            {{ $cliente->profile->first_name ?? 'Cliente' }} {{ $cliente->profile->last_name ?? '' }} - DNI: {{ $cliente->profile->dni ?? 'Sin DNI' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Inicio</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="pt-4 flex gap-3 border-t border-slate-800">
                <button type="button" onclick="toggleModal('assign-routine-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="assign-routine-submit-btn" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Asignar Rutina
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let routineSearchDebounceTimeout = null;

    function initRoutineDniSearch() {
        const searchInput = document.getElementById('routine_dni_search_input');
        const resultsDropdown = document.getElementById('routine_search_results_dropdown');

        if (!searchInput || !resultsDropdown) return;
        if (searchInput.dataset.initialized === 'true') return;
        searchInput.dataset.initialized = 'true';

        searchInput.addEventListener('input', function () {
            clearTimeout(routineSearchDebounceTimeout);
            const query = this.value.trim();

            if (query.length < 1) {
                resultsDropdown.classList.add('hidden');
                return;
            }

            routineSearchDebounceTimeout = setTimeout(() => {
                fetch(`{{ route('api.clientes.search_dni') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(clients => {
                        if (clients.length === 0) {
                            resultsDropdown.innerHTML = `<div class="p-3 text-center text-slate-500 text-xs">No se encontraron atletas con ese DNI o nombre.</div>`;
                        } else {
                            resultsDropdown.innerHTML = clients.map(client => `
                                <div onclick="pickRoutineClient(${client.id}, '${escapeRoutineHtml(client.name)}', '${escapeRoutineHtml(client.dni)}', '${escapeRoutineHtml(client.email)}', '${escapeRoutineHtml(client.photo)}')" 
                                     class="p-2.5 hover:bg-slate-800 flex items-center justify-between gap-3 cursor-pointer transition-colors border-b border-slate-850/40 last:border-0">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="${client.photo}" class="w-7 h-7 rounded-full object-cover border border-slate-800 shrink-0">
                                        <div class="min-w-0">
                                            <span class="block font-bold text-slate-200 text-xs truncate">${client.name}</span>
                                            <span class="block text-[10px] text-slate-500 truncate">${client.email}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 text-[10px] font-mono font-bold rounded shrink-0">
                                        DNI: ${client.dni}
                                    </span>
                                </div>
                            `).join('');
                        }
                        resultsDropdown.classList.remove('hidden');
                        if (window.lucide) window.lucide.createIcons();
                    })
                    .catch(err => {
                        console.error('Error al buscar cliente:', err);
                    });
            }, 200);
        });

        document.addEventListener('click', function (e) {
            if (searchInput && resultsDropdown && !searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                resultsDropdown.classList.add('hidden');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRoutineDniSearch);
    } else {
        initRoutineDniSearch();
    }

    function escapeRoutineHtml(str) {
        return (str || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
    }

    function pickRoutineClient(id, name, dni, email, photo) {
        document.getElementById('assign_routine_selected_user_id').value = id;
        
        document.getElementById('routine_card_client_photo').src = photo;
        document.getElementById('routine_card_client_name').textContent = name;
        document.getElementById('routine_card_client_dni').textContent = 'DNI: ' + dni;
        document.getElementById('routine_card_client_email').textContent = email;
        
        document.getElementById('routine_selected_client_card').classList.remove('hidden');
        document.getElementById('routine_search_results_dropdown').classList.add('hidden');
        document.getElementById('routine_dni_search_input').value = name + ' (DNI: ' + dni + ')';
        
        if (window.lucide) window.lucide.createIcons();
    }

    function selectRoutineClientFromDropdown(selectEl) {
        const option = selectEl.options[selectEl.selectedIndex];
        if (!option || !option.value) return;

        const id = option.value;
        const name = option.getAttribute('data-name');
        const dni = option.getAttribute('data-dni');
        const email = option.getAttribute('data-email');
        const photo = option.getAttribute('data-photo');

        pickRoutineClient(id, name, dni, email, photo);
    }

    function clearRoutineSelectedClient() {
        document.getElementById('assign_routine_selected_user_id').value = '';
        document.getElementById('routine_selected_client_card').classList.add('hidden');
        document.getElementById('routine_dni_search_input').value = '';
        const selectEl = document.getElementById('routine_user_id_select');
        if (selectEl) selectEl.selectedIndex = 0;
    }

    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Move modal to body if inside a transformed parent container so position:fixed centers in viewport
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

    function openAssignRoutineModal(actionUrl, routineName, routineId) {
        initRoutineDniSearch();
        document.getElementById('assign-routine-form').action = actionUrl;
        document.getElementById('modal-routine-name').innerText = routineName;
        document.getElementById('assign-routine-id-input').value = routineId || '';
        clearRoutineSelectedClient();
        toggleModal('assign-routine-modal');
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

    async function submitAssignRoutine(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('assign-routine-submit-btn');

        if (!document.getElementById('assign_routine_selected_user_id').value) {
            showRoutineToast('Por favor selecciona un atleta por DNI o lista.', 'error');
            return;
        }

        setBtnLoading(submitBtn, true, 'Asignando rutina...');

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
                form.reset();
                clearRoutineSelectedClient();
                toggleModal('assign-routine-modal');
                showRoutineToast(data.message, 'success');

                const routineId = document.getElementById('assign-routine-id-input').value;
                if (routineId) {
                    const badge = document.getElementById(`active_count_${routineId}`);
                    if (badge) {
                        let currentCount = parseInt(badge.textContent) || 0;
                        badge.textContent = currentCount + 1;
                    }
                }
            } else {
                const errMsg = data.message || 'Error al asignar la rutina.';
                showRoutineToast(errMsg, 'error');
            }
        } catch (err) {
            console.error(err);
            showRoutineToast('Ocurrió un error al intentar asignar la rutina.', 'error');
        } finally {
            setBtnLoading(submitBtn, false);
        }
    }

    function showRoutineToast(message, type = 'success') {
        let container = document.getElementById('routine-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'routine-toast-container';
            container.className = 'fixed top-24 right-6 z-50 flex flex-col gap-2.5 pointer-events-none max-w-xs sm:max-w-sm w-full';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        const isDanger = type === 'danger' || type === 'error';

        let iconName = isDanger ? 'alert-circle' : 'check-circle';
        let borderColor = isDanger ? 'border-rose-500/30' : 'border-emerald-500/30';
        let iconColor = isDanger ? 'text-rose-400' : 'text-emerald-400';
        let glowColor = isDanger ? 'shadow-rose-500/10' : 'shadow-emerald-500/10';

        toast.className = `pointer-events-auto flex items-center gap-3 p-3.5 pr-4 bg-slate-900 border ${borderColor} text-slate-100 text-xs font-semibold rounded-2xl shadow-xl ${glowColor} transition-all duration-300 transform translate-x-10 opacity-0`;

        toast.innerHTML = `
            <div class="p-1.5 rounded-xl bg-slate-950/60 shrink-0 ${iconColor}">
                <i data-lucide="${iconName}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 leading-tight">${message}</div>
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
        }, 3500);
    }
</script>
@endsection
