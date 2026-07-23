@extends('layouts.admin')

@section('title', 'Auditoría & Bitácora de Seguridad')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight flex items-center gap-3">
                <i data-lucide="shield-check" class="w-8 h-8 text-lime-400"></i>
                Auditoría & Bitácora de Seguridad
            </h1>
            <p class="text-xs text-slate-400 mt-1.5 font-medium">Trazabilidad detallada de cambios en la base de datos, acciones administrativas e intentos de acceso a la plataforma.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.audit.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 font-extrabold text-xs rounded-xl transition-all flex items-center gap-2" title="Actualizar Bitácora">
                <i data-lucide="rotate-cw" class="w-3.5 h-3.5 text-lime-400"></i>
                Refrescar
            </a>
        </div>
    </div>

    <!-- Summary Metrics (Performance Optimized, Crisp Dark Surfaces) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-slate-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Eventos en Sistema</span>
                <h3 class="text-2xl font-black text-slate-100">{{ $logs->total() }} <span class="text-xs font-normal text-slate-400">eventos</span></h3>
            </div>
            <div class="p-3 bg-slate-950 border border-slate-800 rounded-2xl text-slate-400">
                <i data-lucide="list" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Creaciones (INSERT)</span>
                <h3 class="text-2xl font-black text-emerald-400">{{ $logs->where('action_type', 'INSERT')->count() }} <span class="text-xs font-normal text-slate-400">creaciones</span></h3>
            </div>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400">
                <i data-lucide="plus-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-amber-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Modificaciones (UPDATE)</span>
                <h3 class="text-2xl font-black text-amber-400">{{ $logs->where('action_type', 'UPDATE')->count() }} <span class="text-xs font-normal text-slate-400">ediciones</span></h3>
            </div>
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400">
                <i data-lucide="edit-3" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="block text-purple-400 text-[10px] font-extrabold uppercase tracking-wider mb-1">Alertas de Seguridad</span>
                <h3 class="text-2xl font-black text-purple-400">{{ $logs->whereIn('action_type', ['LOGIN_FAILED', 'DELETE'])->count() }} <span class="text-xs font-normal text-slate-400">alertas</span></h3>
            </div>
            <div class="p-3 bg-purple-500/10 border border-purple-500/20 rounded-2xl text-purple-400">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filters Bar Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
        <form method="GET" action="{{ route('superadmin.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs font-semibold">
            <!-- Search input -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Buscar Trazabilidad</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="IP, módulo, usuario o correo..." class="w-full bg-slate-950 border border-slate-850 rounded-xl pl-9 pr-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-3"></i>
                </div>
            </div>

            <!-- Action Type -->
            <div>
                <label for="action_type" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Tipo Acción</label>
                <select name="action_type" id="action_type" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all" {{ request('action_type') == 'all' ? 'selected' : '' }}>Todas las Acciones</option>
                    <option value="INSERT" {{ request('action_type') == 'INSERT' ? 'selected' : '' }}>Creación (INSERT)</option>
                    <option value="UPDATE" {{ request('action_type') == 'UPDATE' ? 'selected' : '' }}>Modificación (UPDATE)</option>
                    <option value="DELETE" {{ request('action_type') == 'DELETE' ? 'selected' : '' }}>Eliminación (DELETE)</option>
                    <option value="LOGIN_FAILED" {{ request('action_type') == 'LOGIN_FAILED' ? 'selected' : '' }}>Acceso Fallido (LOGIN_FAILED)</option>
                    <option value="EXPORT_DATA" {{ request('action_type') == 'EXPORT_DATA' ? 'selected' : '' }}>Exportación (EXPORT_DATA)</option>
                </select>
            </div>

            <!-- Gym Filter -->
            <div>
                <label for="gym_id" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Sucursal</label>
                <select name="gym_id" id="gym_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all" {{ request('gym_id') == 'all' ? 'selected' : '' }}>Todas (+ Global)</option>
                    <option value="global" {{ request('gym_id') == 'global' ? 'selected' : '' }}>⚙️ Acciones Globales</option>
                    @foreach($gyms as $g)
                        <option value="{{ $g->id }}" {{ request('gym_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Admin Filter -->
            <div>
                <label for="admin_id" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Usuario / Admin</label>
                <select name="admin_id" id="admin_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl px-3 py-2.5 text-slate-200 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all" {{ request('admin_id') == 'all' ? 'selected' : '' }}>Todos los Usuarios</option>
                    @foreach($admins as $a)
                        <option value="{{ $a->id }}" {{ request('admin_id') == $a->id ? 'selected' : '' }}>{{ $a->profile ? ($a->profile->first_name . ' ' . $a->profile->last_name) : $a->email }} ({{ ucfirst($a->role) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Filter Button -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black rounded-xl shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="filter" class="w-4 h-4 stroke-[3px]"></i> Filtrar
                </button>
                @if(request()->hasAny(['search', 'action_type', 'gym_id', 'admin_id', 'date_from', 'date_to']))
                    <a href="{{ route('superadmin.audit.index') }}" class="p-2.5 bg-slate-950 hover:bg-rose-500 text-slate-400 hover:text-white rounded-xl border border-slate-850 transition-all cursor-pointer" title="Limpiar Filtros">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table Card (Spacious Vertical Layout & Premium Typography) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="py-4 px-5">Fecha & Hora</th>
                        <th class="py-4 px-5">Acción</th>
                        <th class="py-4 px-5">Usuario / Responsable</th>
                        <th class="py-4 px-5">Sucursal</th>
                        <th class="py-4 px-5">Módulo / Registro</th>
                        <th class="py-4 px-5">Dirección IP</th>
                        <th class="py-4 px-5 text-right">Trazabilidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-xs font-semibold text-slate-300">
                    @forelse($logs as $log)
                        @php
                            $tableMap = [
                                'saas_subscription_plans' => 'Planes SaaS',
                                'gyms' => 'Sucursales',
                                'users' => 'Usuarios',
                                'user_profiles' => 'Perfiles',
                                'trainers' => 'Staff / Entrenadores',
                                'challenges' => 'Retos del Gimnasio',
                                'achievement_definitions' => 'Medallas & Logros',
                                'class_schedules' => 'Horarios de Clase',
                                'group_classes' => 'Clases Grupales',
                                'equipment' => 'Equipamiento',
                                'exercises' => 'Ejercicios',
                                'memberships' => 'Membresías',
                            ];
                            $moduleName = $tableMap[$log->table_name] ?? ucfirst($log->table_name);
                        @endphp
                        <tr class="hover:bg-slate-850/40 transition-colors">
                            <!-- Fecha & Hora (Orden: Fecha, Hora, Hace cuanto) -->
                            <td class="py-5 px-5 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="font-black text-slate-100 text-xs flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-lime-400 shrink-0"></i>
                                        <span>{{ $log->createdAt ? $log->createdAt->format('d/m/Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="font-extrabold text-slate-300 text-xs flex items-center gap-1.5 font-mono">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span>{{ $log->createdAt ? $log->createdAt->format('H:i:s') : 'N/A' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-semibold flex items-center gap-1.5">
                                        <i data-lucide="history" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i>
                                        <span>{{ $log->createdAt ? $log->createdAt->diffForHumans() : '' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Acción Badge -->
                            <td class="py-5 px-5 whitespace-nowrap">
                                @if($log->action_type === 'INSERT')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wide bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> INSERT
                                    </span>
                                @elseif($log->action_type === 'UPDATE')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wide bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> UPDATE
                                    </span>
                                @elseif($log->action_type === 'DELETE')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wide bg-rose-500/10 text-rose-400 border border-rose-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> DELETE
                                    </span>
                                @elseif($log->action_type === 'LOGIN_FAILED')
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wide bg-purple-500/10 text-purple-400 border border-purple-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> LOGIN FAILED
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wide bg-sky-500/10 text-sky-400 border border-sky-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> {{ $log->action_type }}
                                    </span>
                                @endif
                            </td>

                            <!-- Usuario Responsable (Orden: Nombre, Rol, Correo) -->
                            <td class="py-5 px-5 whitespace-nowrap">
                                @if($log->admin)
                                    @php
                                        $adminName = $log->admin->profile ? trim(($log->admin->profile->first_name ?? '') . ' ' . ($log->admin->profile->last_name ?? '')) : $log->admin->email;
                                        $role = strtolower($log->admin->role);
                                        $roleBadgeClass = ($role === 'superadmin') ? 'bg-lime-500/10 text-lime-400 border-lime-500/20' : (($role === 'admin') ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-slate-800 text-slate-300 border-slate-700');
                                        $roleLabel = ($role === 'superadmin') ? 'Superadministrador' : (($role === 'admin') ? 'Administrador' : 'Entrenador Staff');
                                    @endphp
                                    <div class="flex items-start gap-3">
                                        <div class="space-y-1 min-w-0">
                                            <div class="font-extrabold text-slate-100 text-xs truncate">
                                                {{ $adminName }}
                                            </div>
                                            <div>
                                                <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider border {{ $roleBadgeClass }}">
                                                    {{ $roleLabel }}
                                                </span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono truncate">
                                                {{ $log->admin->email }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-1">
                                        <div class="font-extrabold text-slate-400 text-xs flex items-center gap-1.5">
                                            <i data-lucide="bot" class="w-3.5 h-3.5 text-slate-500"></i>
                                            <span>Proceso Automático</span>
                                        </div>
                                        <div>
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider bg-slate-950 text-slate-500 border border-slate-855">
                                                Sistema
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono">system@gymflow.local</div>
                                    </div>
                                @endif
                            </td>

                            <!-- Sucursal -->
                            <td class="py-5 px-5 whitespace-nowrap">
                                @if($log->gym)
                                    <span class="font-bold text-slate-200 text-xs flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-lime-400"></i>
                                        {{ $log->gym->name }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-slate-950 text-slate-400 border border-slate-800">
                                        GLOBAL / SUPERADMIN
                                    </span>
                                @endif
                            </td>

                            <!-- Módulo / Registro -->
                            <td class="py-5 px-5 whitespace-nowrap">
                                <div class="font-black text-slate-100 text-xs leading-snug">{{ $moduleName }}</div>
                                <div class="text-[9px] text-slate-400 font-mono mt-1 flex items-center gap-1">
                                    <span class="text-lime-400 font-semibold">{{ $log->table_name }}</span>
                                    @if($log->record_id)
                                        <span class="text-slate-500">#{{ $log->record_id }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td class="py-5 px-5 whitespace-nowrap">
                                <span class="px-2 py-1 bg-slate-950 border border-slate-850 rounded-lg text-[10px] font-mono text-slate-300">
                                    IP: {{ $log->ip_address ?? '127.0.0.1' }}
                                </span>
                            </td>

                            <!-- Detalle Button -->
                            <td class="py-5 px-5 whitespace-nowrap text-right">
                                <button type="button" onclick='openAuditDetailModal({{ json_encode($log) }})' class="px-3.5 py-1.5 bg-slate-950 hover:bg-slate-800 text-slate-200 hover:text-lime-400 font-bold text-xs rounded-xl border border-slate-800 transition-all inline-flex items-center gap-1.5 cursor-pointer shadow-sm">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-lime-400"></i> Detalle
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-500 font-bold">
                                <i data-lucide="shield-off" class="w-12 h-12 mx-auto mb-3 text-slate-700"></i>
                                <p class="text-slate-400">No se encontraron registros de auditoría que coincidan con los filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ================= MODAL: DETALLE DE AUDITORÍA (HUMANO Y AMIGABLE) ================= -->
<div id="audit-detail-modal" class="fixed inset-0 z-50 bg-slate-950/85 flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-3xl mx-auto my-auto space-y-6 animate-scale-up shadow-2xl max-h-[92vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="font-black text-slate-100 text-lg flex items-center gap-2.5">
                    <i data-lucide="shield-check" class="text-lime-400 w-6 h-6"></i>
                    Detalle del Evento de Auditoría
                </h3>
                <p class="text-slate-400 text-xs mt-0.5">Resumen inteligible y comparativa clara de cambios en la plataforma.</p>
            </div>
            <button type="button" onclick="toggleModal('audit-detail-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Metadata Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 space-y-1">
                <span class="block text-[9px] text-slate-500 uppercase tracking-wider font-extrabold">Fecha & Hora</span>
                <span id="detail_date" class="text-slate-100 font-bold text-xs block"></span>
            </div>
            <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 space-y-1">
                <span class="block text-[9px] text-slate-500 uppercase tracking-wider font-extrabold">Acción Realizada</span>
                <span id="detail_action" class="font-black text-xs block"></span>
            </div>
            <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 space-y-1">
                <span class="block text-[9px] text-slate-500 uppercase tracking-wider font-extrabold">Módulo / Sección</span>
                <span id="detail_table" class="text-lime-400 font-bold text-xs truncate block"></span>
            </div>
            <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 space-y-1">
                <span class="block text-[9px] text-slate-500 uppercase tracking-wider font-extrabold">Dirección IP</span>
                <span id="detail_ip" class="text-slate-300 font-mono text-xs block"></span>
            </div>
        </div>

        <!-- User Agent Box -->
        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-850 space-y-1">
            <span class="block text-[9px] text-slate-500 uppercase tracking-wider font-extrabold">Dispositivo / Navegador Origino</span>
            <span id="detail_user_agent" class="text-slate-400 font-mono text-[10px] break-all block"></span>
        </div>

        <!-- View Mode Switcher Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h4 class="font-extrabold text-xs text-slate-200 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="git-compare" class="w-4 h-4 text-lime-400"></i>
                Comparación de Datos Modificados
            </h4>
            
            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-850">
                <button type="button" onclick="setAuditViewMode('friendly')" id="view-mode-friendly-btn" class="px-3 py-1 rounded-lg text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all">
                    🌱 Modo Amigable
                </button>
                <button type="button" onclick="setAuditViewMode('json')" id="view-mode-json-btn" class="px-3 py-1 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all">
                    💻 Modo Técnico (JSON)
                </button>
            </div>
        </div>

        <!-- View Mode 1: Friendly Human Readable Comparison -->
        <div id="audit-friendly-container" class="space-y-4">
            <div id="audit-friendly-list" class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
                <!-- Dynamic Human Readable Rows inserted by JS -->
            </div>
        </div>

        <!-- View Mode 2: Technical Raw JSON Side-by-Side Comparison -->
        <div id="audit-json-container" class="space-y-3 hidden">
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-400 font-bold">Respuesta Raw JSON de la Base de Datos:</span>
                <button type="button" onclick="copyJsonToClipboard()" class="px-2.5 py-1 bg-slate-950 hover:bg-slate-800 text-slate-300 text-[10px] font-bold rounded-lg border border-slate-800 transition-colors">
                    Copiar JSON al Portapapeles
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold text-xs flex items-center gap-1.5">
                        <i data-lucide="history" class="w-4 h-4 text-amber-400"></i> Estado Anterior (JSON Raw)
                    </span>
                    <pre id="detail_old_data" class="bg-slate-950 border border-slate-850 rounded-2xl p-4 text-[11px] font-mono text-amber-300 overflow-x-auto min-h-[140px] max-h-[260px] whitespace-pre-wrap break-words leading-relaxed"></pre>
                </div>
                <div>
                    <span class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold text-xs flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-4 h-4 text-emerald-400"></i> Estado Nuevo (JSON Raw)
                    </span>
                    <pre id="detail_new_data" class="bg-slate-950 border border-slate-850 rounded-2xl p-4 text-[11px] font-mono text-emerald-300 overflow-x-auto min-h-[140px] max-h-[260px] whitespace-pre-wrap break-words leading-relaxed"></pre>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex justify-end">
            <button type="button" onclick="toggleModal('audit-detail-modal')" class="px-6 py-2.5 bg-slate-950 hover:bg-slate-800 text-slate-200 font-bold text-xs rounded-xl border border-slate-800 transition-colors cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    // Spanish Dictionaries for Tables & Fields
    const TABLE_NAMES_MAP = {
        'saas_subscription_plans': 'Planes de Suscripción SaaS',
        'gyms': 'Sucursales de Gimnasio',
        'users': 'Cuentas de Usuario',
        'user_profiles': 'Perfil de Usuario / Atleta',
        'trainers': 'Entrenadores del Staff',
        'challenges': 'Retos del Gimnasio',
        'achievement_definitions': 'Catálogo de Medallas & Logros',
        'class_schedules': 'Horarios de Clases',
        'group_classes': 'Clases Grupales',
        'equipment': 'Equipamiento & Maquinaria',
        'exercises': 'Ejercicios de Rutina',
        'memberships': 'Membresías',
        'class_bookings': 'Reservas de Clase'
    };

    const FIELD_MAP = {
        'name': 'Nombre / Título',
        'title': 'Título de Reto / Medalla',
        'description': 'Descripción',
        'monthly_price': 'Precio Mensual ($)',
        'currency': 'Moneda',
        'max_users': 'Límite de Atletas Miembros',
        'max_trainers': 'Límite de Personal de Staff',
        'is_active': 'Estado de Disponibilidad / Servicio',
        'first_name': 'Nombre(s)',
        'last_name': 'Apellido(s)',
        'email': 'Correo Electrónico',
        'phone': 'Teléfono / WhatsApp',
        'dni': 'DNI / Documento Identidad',
        'address': 'Dirección Física',
        'slug': 'Identificador Slug',
        'primary_color': 'Color Primario (Hex)',
        'secondary_color': 'Color Secundario (Hex)',
        'subscription_status': 'Estado de Suscripción SaaS',
        'role': 'Rol de Usuario',
        'specialty': 'Especialidad',
        'certification': 'Certificación Fitness',
        'experience_years': 'Años de Experiencia',
        'salary': 'Salario de Nómina',
        'bio': 'Biografía / Reseña',
        'max_clients': 'Cupo Máximo de Atletas',
        'xp_reward': 'Recompensa de Puntos XP',
        'token_reward': 'Recompensa en Monedas Virtuales',
        'start_date': 'Fecha de Inicio',
        'end_date': 'Fecha de Finalización',
        'timezone': 'Zona Horaria'
    };

    // Centered Static Modal Handler
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

    let currentAuditViewMode = 'friendly';
    let currentRawJsonOld = '';
    let currentRawJsonNew = '';

    function setAuditViewMode(mode) {
        currentAuditViewMode = mode;
        const friendlyBtn = document.getElementById('view-mode-friendly-btn');
        const jsonBtn = document.getElementById('view-mode-json-btn');

        const friendlyContainer = document.getElementById('audit-friendly-container');
        const jsonContainer = document.getElementById('audit-json-container');

        if (mode === 'friendly') {
            friendlyBtn.className = "px-3 py-1 rounded-lg text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all";
            jsonBtn.className = "px-3 py-1 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
            friendlyContainer.classList.remove('hidden');
            jsonContainer.classList.add('hidden');
        } else {
            jsonBtn.className = "px-3 py-1 rounded-lg text-xs font-extrabold bg-slate-900 text-lime-400 border border-slate-800 transition-all";
            friendlyBtn.className = "px-3 py-1 rounded-lg text-xs font-bold text-slate-400 hover:text-slate-200 transition-all";
            jsonContainer.classList.remove('hidden');
            friendlyContainer.classList.add('hidden');
        }
    }

    function formatValueFriendly(key, val) {
        if (val === null || val === undefined) return '<span class="text-slate-500 italic font-normal">Sin especificar</span>';
        if (key === 'is_active') {
            return (val == 1 || val === true)
                ? '<span class="text-emerald-400 font-black">Activo / En Servicio</span>'
                : '<span class="text-rose-400 font-black">Inactivo / Suspendido</span>';
        }
        if (typeof val === 'object') return JSON.stringify(val);
        return escapeHtml(String(val));
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

    function copyJsonToClipboard() {
        const textToCopy = `OLD DATA:\n${currentRawJsonOld}\n\nNEW DATA:\n${currentRawJsonNew}`;
        navigator.clipboard.writeText(textToCopy).then(() => {
            alert('JSON copiado al portapapeles exitosamente.');
        });
    }

    function openAuditDetailModal(log) {
        document.getElementById('detail_date').textContent = log.createdAt ? new Date(log.createdAt).toLocaleString() : 'N/A';
        
        const actionSpan = document.getElementById('detail_action');
        actionSpan.textContent = log.action_type;
        if (log.action_type === 'INSERT') actionSpan.className = 'font-black text-emerald-400 text-xs block';
        else if (log.action_type === 'UPDATE') actionSpan.className = 'font-black text-amber-400 text-xs block';
        else if (log.action_type === 'DELETE') actionSpan.className = 'font-black text-rose-400 text-xs block';
        else actionSpan.className = 'font-black text-purple-400 text-xs block';

        const friendlyTableName = TABLE_NAMES_MAP[log.table_name] || log.table_name;
        document.getElementById('detail_table').textContent = friendlyTableName + (log.record_id ? ' #' + log.record_id : '');
        document.getElementById('detail_ip').textContent = log.ip_address || '127.0.0.1';
        document.getElementById('detail_user_agent').textContent = log.user_agent || 'N/A';

        // Parse JSON
        let oldObj = null;
        let newObj = null;

        let oldRaw = log.old_data;
        let newRaw = log.new_data;

        try {
            if (typeof oldRaw === 'string' && (oldRaw.startsWith('{') || oldRaw.startsWith('['))) {
                oldObj = JSON.parse(oldRaw);
                oldRaw = JSON.stringify(oldObj, null, 2);
            } else if (typeof oldRaw === 'object' && oldRaw !== null) {
                oldObj = oldRaw;
                oldRaw = JSON.stringify(oldObj, null, 2);
            }
        } catch(e) {}

        try {
            if (typeof newRaw === 'string' && (newRaw.startsWith('{') || newRaw.startsWith('['))) {
                newObj = JSON.parse(newRaw);
                newRaw = JSON.stringify(newObj, null, 2);
            } else if (typeof newRaw === 'object' && newRaw !== null) {
                newObj = newRaw;
                newRaw = JSON.stringify(newObj, null, 2);
            }
        } catch(e) {}

        currentRawJsonOld = oldRaw || '';
        currentRawJsonNew = newRaw || '';

        document.getElementById('detail_old_data').textContent = oldRaw || '(Sin datos previos / Creación inicial)';
        document.getElementById('detail_new_data').textContent = newRaw || '(Sin datos nuevos / Eliminación)';

        // Build Friendly Human-Readable List
        const listContainer = document.getElementById('audit-friendly-list');
        listContainer.innerHTML = '';

        if (log.action_type === 'UPDATE' && oldObj && newObj) {
            const keys = Array.from(new Set([...Object.keys(oldObj), ...Object.keys(newObj)]));
            let changesFound = false;

            keys.forEach(k => {
                const oldV = oldObj[k];
                const newV = newObj[k];

                if (JSON.stringify(oldV) !== JSON.stringify(newV)) {
                    changesFound = true;
                    const labelName = FIELD_MAP[k] || k;
                    const row = document.createElement('div');
                    row.className = 'p-4 bg-slate-950 border border-amber-500/30 rounded-2xl space-y-2';
                    row.innerHTML = `
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-black text-slate-100 uppercase tracking-wider">${escapeHtml(labelName)}</span>
                            <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[9px] font-extrabold rounded-lg">
                                Valor Modificado
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-semibold pt-2 border-t border-slate-850">
                            <div class="p-2.5 bg-rose-500/5 border border-rose-500/10 rounded-xl">
                                <span class="text-[9px] text-slate-400 uppercase block font-extrabold mb-0.5">Antes (Valor Prevido):</span>
                                <div class="text-rose-300 font-bold">${formatValueFriendly(k, oldV)}</div>
                            </div>
                            <div class="p-2.5 bg-emerald-500/5 border border-emerald-500/10 rounded-xl">
                                <span class="text-[9px] text-slate-400 uppercase block font-extrabold mb-0.5">Ahora (Nuevo Valor):</span>
                                <div class="text-emerald-300 font-bold">${formatValueFriendly(k, newV)}</div>
                            </div>
                        </div>
                    `;
                    listContainer.appendChild(row);
                }
            });

            if (!changesFound) {
                listContainer.innerHTML = `<div class="p-4 bg-slate-950 rounded-2xl text-center text-slate-500 text-xs">No se detectaron diferencias clave entre registros.</div>`;
            }
        } else if (log.action_type === 'INSERT' && newObj) {
            const keys = Object.keys(newObj);
            listContainer.innerHTML = `<div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-black rounded-xl mb-3">✨ Registro Creado con los siguientes datos iniciales:</div>`;
            keys.forEach(k => {
                const labelName = FIELD_MAP[k] || k;
                const row = document.createElement('div');
                row.className = 'p-3 bg-slate-950 border border-slate-850 rounded-2xl flex items-center justify-between text-xs font-semibold';
                row.innerHTML = `
                    <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">${escapeHtml(labelName)}:</span>
                    <span class="text-slate-100 font-extrabold">${formatValueFriendly(k, newObj[k])}</span>
                `;
                listContainer.appendChild(row);
            });
        } else if (log.action_type === 'DELETE' && oldObj) {
            const keys = Object.keys(oldObj);
            listContainer.innerHTML = `<div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-black rounded-xl mb-3">🗑️ Registro Eliminado de la base de datos:</div>`;
            keys.forEach(k => {
                const labelName = FIELD_MAP[k] || k;
                const row = document.createElement('div');
                row.className = 'p-3 bg-slate-950 border border-slate-850 rounded-2xl flex items-center justify-between text-xs font-semibold';
                row.innerHTML = `
                    <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">${escapeHtml(labelName)}:</span>
                    <span class="text-slate-300 font-extrabold">${formatValueFriendly(k, oldObj[k])}</span>
                `;
                listContainer.appendChild(row);
            });
        } else {
            listContainer.innerHTML = `<div class="p-4 bg-slate-950 rounded-2xl text-center text-slate-400 text-xs font-semibold">Consulte el Modo Técnico (JSON) para ver el cuerpo crudo de la notificación o evento.</div>`;
        }

        setAuditViewMode('friendly');
        toggleModal('audit-detail-modal');
    }
</script>
@endsection
