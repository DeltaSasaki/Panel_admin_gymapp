@extends('layouts.admin')

@section('title', 'Auditoría & Bitácora de Seguridad')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight flex items-center gap-2.5">
                <i data-lucide="shield-check" class="w-7 h-7 text-lime-400"></i>
                Auditoría & Bitácora de Seguridad
            </h1>
            <p class="text-slate-400 text-xs mt-1">Supervisa en tiempo real las acciones administrativas, cambios en registros, eliminaciones e intentos de acceso a la plataforma.</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-5 shadow-xl space-y-4">
        <form method="GET" action="{{ route('superadmin.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Search input -->
            <div>
                <label for="search" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Buscar</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="IP, tabla, usuario..." class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-3"></i>
                </div>
            </div>

            <!-- Action Type -->
            <div>
                <label for="action_type" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Tipo Acción</label>
                <select name="action_type" id="action_type" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
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
                <select name="gym_id" id="gym_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all" {{ request('gym_id') == 'all' ? 'selected' : '' }}>Todas las Sucursales (+ Global)</option>
                    <option value="global" {{ request('gym_id') == 'global' ? 'selected' : '' }}>⚙️ Acciones Globales / Sistema</option>
                    @foreach($gyms as $g)
                        <option value="{{ $g->id }}" {{ request('gym_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Admin Filter -->
            <div>
                <label for="admin_id" class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold">Usuario / Admin</label>
                <select name="admin_id" id="admin_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                    <option value="all" {{ request('admin_id') == 'all' ? 'selected' : '' }}>Todos los Usuarios</option>
                    @foreach($admins as $a)
                        <option value="{{ $a->id }}" {{ request('admin_id') == $a->id ? 'selected' : '' }}>{{ $a->profile ? ($a->profile->first_name . ' ' . $a->profile->last_name) : $a->email }} ({{ ucfirst($a->role) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Filter Button -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold rounded-xl border border-slate-700/60 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="filter" class="w-4 h-4 text-lime-400"></i> Filtrar
                </button>
                @if(request()->hasAny(['search', 'action_type', 'gym_id', 'admin_id', 'date_from', 'date_to']))
                    <a href="{{ route('superadmin.audit.index') }}" class="p-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-xl border border-rose-500/20 transition-all cursor-pointer" title="Limpiar Filtros">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="py-4 px-6">Fecha & Hora</th>
                        <th class="py-4 px-6">Acción</th>
                        <th class="py-4 px-6">Usuario / Admin</th>
                        <th class="py-4 px-6">Sucursal</th>
                        <th class="py-4 px-6">Tabla / Registro</th>
                        <th class="py-4 px-6">Dirección IP</th>
                        <th class="py-4 px-6 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-xs font-semibold text-slate-300">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-850/40 transition-colors">
                            <!-- Fecha -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="font-bold text-slate-200">{{ $log->createdAt ? $log->createdAt->format('d/m/Y H:i:s') : 'N/A' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $log->createdAt ? $log->createdAt->diffForHumans() : '' }}</div>
                            </td>

                            <!-- Acción Badge -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($log->action_type === 'INSERT')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="plus-circle" class="w-3 h-3"></i> INSERT
                                    </span>
                                @elseif($log->action_type === 'UPDATE')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-amber-500/10 text-amber-400 border border-amber-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i> UPDATE
                                    </span>
                                @elseif($log->action_type === 'DELETE')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-rose-500/10 text-rose-400 border border-rose-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i> DELETE
                                    </span>
                                @elseif($log->action_type === 'LOGIN_FAILED')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-purple-500/10 text-purple-400 border border-purple-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="shield-alert" class="w-3 h-3"></i> LOGIN FAILED
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-sky-500/10 text-sky-400 border border-sky-500/20 inline-flex items-center gap-1">
                                        <i data-lucide="file-text" class="w-3 h-3"></i> {{ $log->action_type }}
                                    </span>
                                @endif
                            </td>

                            <!-- Usuario -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($log->admin)
                                    <div class="font-bold text-slate-100 flex items-center gap-1.5">
                                        <span>{{ $log->admin->profile ? ($log->admin->profile->first_name . ' ' . $log->admin->profile->last_name) : $log->admin->email }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">({{ ucfirst($log->admin->role) }})</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ $log->admin->email }}</div>
                                @else
                                    <span class="text-slate-500 italic">Desconocido / Anónimo</span>
                                @endif
                            </td>

                            <!-- Sucursal -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($log->gym)
                                    <span class="font-bold text-slate-300 flex items-center gap-1.5">
                                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-lime-400"></i>
                                        {{ $log->gym->name }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-800 text-slate-400 border border-slate-700">
                                        GLOBAL / SUPERADMIN
                                    </span>
                                @endif
                            </td>

                            <!-- Tabla / Registro -->
                            <td class="py-4 px-6 whitespace-nowrap font-mono text-[11px]">
                                <span class="text-lime-400 font-bold">{{ $log->table_name }}</span>
                                @if($log->record_id)
                                    <span class="text-slate-500">#{{ $log->record_id }}</span>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="py-4 px-6 whitespace-nowrap font-mono text-[11px] text-slate-400">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>

                            <!-- Detalle Button -->
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                <button onclick="openAuditDetailModal({{ json_encode($log) }})" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold text-xs rounded-xl border border-slate-700/60 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-lime-400"></i> Detalle
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500 font-bold">
                                <i data-lucide="shield-off" class="w-12 h-12 mx-auto mb-3 text-slate-700"></i>
                                No se encontraron registros de auditoría que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

@push('modals')
    <!-- ================= MODAL: DETALLE DE AUDITORÍA ================= -->
    <div id="audit-detail-modal" class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-2xl w-full shadow-2xl my-auto animate-scale-up space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="font-extrabold text-slate-100 text-lg flex items-center gap-2">
                    <i data-lucide="shield-check" class="text-lime-400 w-5 h-5"></i>
                    Detalle de Evento de Auditoría
                </h3>
                <button onclick="toggleAuditDetailModal()" class="text-slate-400 hover:text-slate-100 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-4 text-xs font-semibold max-h-[60vh] overflow-y-auto pr-1">
                <!-- Metadata Info Box -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-950/60 p-4 rounded-2xl border border-slate-850">
                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase tracking-wider">Fecha / Hora</span>
                        <span id="detail_date" class="text-slate-200 font-bold"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase tracking-wider">Acción</span>
                        <span id="detail_action" class="font-black"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase tracking-wider">Tabla / Registro</span>
                        <span id="detail_table" class="text-lime-400 font-bold font-mono"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase tracking-wider">Dirección IP</span>
                        <span id="detail_ip" class="text-slate-300 font-mono"></span>
                    </div>
                </div>

                <!-- User Agent -->
                <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-850">
                    <span class="block text-[10px] text-slate-500 uppercase tracking-wider mb-1">Dispositivo / User Agent</span>
                    <span id="detail_user_agent" class="text-slate-400 font-mono text-[11px] break-all"></span>
                </div>

                <!-- Before vs After Diff Comparison -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <!-- Old Data -->
                    <div>
                        <span class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold flex items-center gap-1.5">
                            <i data-lucide="history" class="w-4 h-4 text-amber-400"></i> Estado Anterior (old_data)
                        </span>
                        <pre id="detail_old_data" class="bg-slate-950 border border-slate-800 rounded-xl p-4 text-[11px] font-mono text-amber-300 overflow-x-auto min-h-[120px] max-h-[220px] whitespace-pre-wrap break-words"></pre>
                    </div>

                    <!-- New Data -->
                    <div>
                        <span class="block text-slate-400 uppercase tracking-wider mb-1.5 font-bold flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-4 h-4 text-emerald-400"></i> Estado Nuevo (new_data)
                        </span>
                        <pre id="detail_new_data" class="bg-slate-950 border border-slate-800 rounded-xl p-4 text-[11px] font-mono text-emerald-300 overflow-x-auto min-h-[120px] max-h-[220px] whitespace-pre-wrap break-words"></pre>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="button" onclick="toggleAuditDetailModal()" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold text-xs rounded-xl border border-slate-700/50 transition-colors cursor-pointer">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
@endpush

<script>
    function toggleAuditDetailModal() {
        const modal = document.getElementById('audit-detail-modal');
        if (modal) modal.classList.toggle('hidden');
    }

    function openAuditDetailModal(log) {
        document.getElementById('detail_date').textContent = log.createdAt ? new Date(log.createdAt).toLocaleString() : 'N/A';
        document.getElementById('detail_action').textContent = log.action_type;
        document.getElementById('detail_table').textContent = log.table_name + (log.record_id ? ' #' + log.record_id : '');
        document.getElementById('detail_ip').textContent = log.ip_address || '127.0.0.1';
        document.getElementById('detail_user_agent').textContent = log.user_agent || 'N/A';

        // Format JSON or text
        let oldStr = log.old_data;
        let newStr = log.new_data;

        try {
            if (oldStr && typeof oldStr === 'string' && (oldStr.startsWith('{') || oldStr.startsWith('['))) {
                oldStr = JSON.stringify(JSON.parse(oldStr), null, 2);
            }
        } catch(e) {}

        try {
            if (newStr && typeof newStr === 'string' && (newStr.startsWith('{') || newStr.startsWith('['))) {
                newStr = JSON.stringify(JSON.parse(newStr), null, 2);
            }
        } catch(e) {}

        document.getElementById('detail_old_data').textContent = oldStr || '(Sin datos previos / Creación)';
        document.getElementById('detail_new_data').textContent = newStr || '(Sin datos nuevos / Eliminación)';

        toggleAuditDetailModal();
    }
</script>
@endsection
