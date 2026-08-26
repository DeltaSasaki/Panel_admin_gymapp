@extends('layouts.admin')

@section('title', 'Gestión de Permisos y Roles - Control de Acceso Granular')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-slate-900/60 border border-slate-800 rounded-3xl p-6 shadow-xl backdrop-blur-md">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-2xl">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-100 tracking-tight">Control de Permisos & Seguridad</h1>
                    <p class="text-xs text-slate-400">Configuración granular de accesos a pantallas, acciones CRUD y visibilidad de tarjetas por Rol o por Usuario individual.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
            <div class="bg-slate-950/50 border border-slate-800 rounded-2xl px-4 py-2.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-lime-500/10 border border-lime-500/20 flex items-center justify-center text-lime-400 font-bold text-xs">
                    {{ $allPermissionsCount }}
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Total Permisos</div>
                    <div class="text-xs font-bold text-slate-200">Catalogados</div>
                </div>
            </div>
            <div class="bg-slate-950/50 border border-slate-800 rounded-2xl px-4 py-2.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-bold text-xs">
                    {{ count($usersWithOverrides) }}
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Excepciones</div>
                    <div class="text-xs font-bold text-slate-200">Usuarios Personalizados</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-2 overflow-x-auto">
        <button type="button" onclick="switchPermTab('roles')" id="tab-btn-roles" 
                class="perm-tab-btn flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 bg-lime-500 text-slate-950 shadow-lg shadow-lime-500/20">
            <i data-lucide="shield" class="w-4 h-4"></i>
            <span>Permisos por Rol (Matriz Base)</span>
        </button>
        <button type="button" onclick="switchPermTab('users')" id="tab-btn-users" 
                class="perm-tab-btn flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 text-slate-400 hover:text-slate-100 hover:bg-slate-900/60">
            <i data-lucide="user-cog" class="w-4 h-4"></i>
            <span>Permisos por Usuario (Excepciones)</span>
            @if(count($usersWithOverrides) > 0)
                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-full">{{ count($usersWithOverrides) }}</span>
            @endif
        </button>
        <button type="button" onclick="switchPermTab('summary')" id="tab-btn-summary" 
                class="perm-tab-btn flex items-center gap-2.5 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 text-slate-400 hover:text-slate-100 hover:bg-slate-900/60">
            <i data-lucide="layers" class="w-4 h-4"></i>
            <span>Resumen de Excepciones</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: PERMISOS POR ROL                   -->
    <!-- ========================================== -->
    <div id="perm-tab-roles" class="space-y-6">
        <!-- Role Selector & Search Bar -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto">
                <span class="text-xs font-bold text-slate-400 mr-2 uppercase tracking-wider">Rol a Configurar:</span>
                <button type="button" onclick="selectRoleTab('admin')" id="role-btn-admin"
                        class="role-select-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold transition-all duration-200 bg-lime-500/10 text-lime-400 border border-lime-500/30">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Administrador (Admin)</span>
                </button>
                <button type="button" onclick="selectRoleTab('cajero')" id="role-btn-cajero"
                        class="role-select-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold transition-all duration-200 text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 border border-transparent">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>
                    <span>Cajero / Recepción</span>
                </button>
                <button type="button" onclick="selectRoleTab('trainer')" id="role-btn-trainer"
                        class="role-select-btn flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-extrabold transition-all duration-200 text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 border border-transparent">
                    <i data-lucide="dumbbell" class="w-4 h-4"></i>
                    <span>Entrenador / Coach</span>
                </button>
            </div>

            <div class="relative w-full md:w-72">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" id="role-perm-search" oninput="filterRolePermissions()" 
                       placeholder="Buscar permiso o módulo..." 
                       class="w-full bg-slate-950/60 border border-slate-800 text-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500 transition-all">
            </div>
        </div>

        <!-- Role Form (3 roles containers) -->
        @foreach(['admin', 'cajero', 'trainer'] as $currentRoleKey)
            @php
                $roleLabelText = match($currentRoleKey) {
                    'admin' => 'Administrador de Gimnasio',
                    'cajero' => 'Cajero / Personal de Recepción',
                    'trainer' => 'Entrenador / Coach Deportivo',
                };
                $roleBadgeColor = match($currentRoleKey) {
                    'admin' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    'cajero' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                    'trainer' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                };
                $enabledIds = $rolePermissions[$currentRoleKey] ?? [];
            @endphp

            <form id="form-role-{{ $currentRoleKey }}" action="{{ route('permisos.update_role') }}" method="POST" 
                  class="role-perm-form space-y-6 {{ $currentRoleKey === 'admin' ? '' : 'hidden' }}">
                @csrf
                <input type="hidden" name="role" value="{{ $currentRoleKey }}">

                <!-- Role Info banner -->
                <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-950/40 border border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 text-xs font-black rounded-lg border {{ $roleBadgeColor }} uppercase tracking-wider">
                            {{ $roleLabelText }}
                        </span>
                        <span class="text-xs text-slate-400 hidden sm:inline">Los cambios guardados aplicarán a todos los usuarios con este rol (salvo que tengan excepciones individuales).</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllCheckboxes('form-role-{{ $currentRoleKey }}', true)" 
                                class="px-3 py-1.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-700 rounded-lg transition-colors">
                            Seleccionar Todos
                        </button>
                        <button type="button" onclick="toggleAllCheckboxes('form-role-{{ $currentRoleKey }}', false)" 
                                class="px-3 py-1.5 text-xs font-semibold text-slate-400 hover:text-slate-200 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-lg transition-colors">
                            Desmarcar Todos
                        </button>
                    </div>
                </div>

                <!-- Grid of Modules -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($groupedPermissions as $moduleName => $permList)
                        <div class="module-card bg-slate-900/50 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-800/80 pb-3">
                                <div class="flex items-center gap-2.5">
                                    @php
                                        $modIcon = match($moduleName) {
                                            'Dashboard' => 'layout-dashboard',
                                            'Clientes' => 'users',
                                            'Ventas & Tienda' => 'shopping-cart',
                                            'Finanzas' => 'credit-card',
                                            'Entrenamiento' => 'dumbbell',
                                            'Personal & Roles' => 'shield',
                                            default => 'layers'
                                        };
                                    @endphp
                                    <div class="p-1.5 bg-slate-800 text-slate-300 rounded-lg">
                                        <i data-lucide="{{ $modIcon }}" class="w-4 h-4"></i>
                                    </div>
                                    <h3 class="font-extrabold text-sm text-slate-200">{{ $moduleName }}</h3>
                                </div>
                                <span class="text-[11px] font-bold text-slate-500 px-2 py-0.5 bg-slate-950 rounded-md border border-slate-850">
                                    {{ count($permList) }} permisos
                                </span>
                            </div>

                            <div class="space-y-3">
                                @foreach($permList as $perm)
                                    @php
                                        $isChecked = in_array($perm->id, $enabledIds);
                                        $typeBadge = match($perm->type) {
                                            'menu_access' => ['label' => 'Pantalla / Vista', 'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
                                            'action' => ['label' => 'Acción / CRUD', 'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
                                            'widget' => ['label' => 'Tarjeta / Métrica', 'class' => 'bg-purple-500/10 text-purple-400 border-purple-500/20'],
                                            default => ['label' => 'General', 'class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20'],
                                        };
                                    @endphp
                                    <label class="perm-item-row flex items-start justify-between gap-3 p-3 rounded-2xl bg-slate-950/40 border border-slate-850 hover:border-slate-700 hover:bg-slate-900/60 transition-all cursor-pointer group">
                                        <div class="space-y-1 flex-1 pr-2">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-bold text-slate-200 group-hover:text-lime-300 transition-colors">
                                                    {{ $perm->name }}
                                                </span>
                                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded border {{ $typeBadge['class'] }}">
                                                    {{ $typeBadge['label'] }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 leading-relaxed">{{ $perm->description }}</p>
                                            <code class="text-[10px] text-slate-500 font-mono tracking-tight">{{ $perm->code }}</code>
                                        </div>

                                        <div class="relative inline-flex items-center shrink-0 mt-1">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" 
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   class="sr-only peer perm-checkbox">
                                            <div class="w-10 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-lime-500 peer-checked:after:bg-slate-950 border border-slate-700"></div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Sticky Save Bar -->
                <div class="sticky bottom-4 z-20 bg-slate-900/90 border border-slate-800 backdrop-blur-md rounded-2xl p-4 flex items-center justify-between shadow-2xl">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <i data-lucide="info" class="w-4 h-4 text-lime-400"></i>
                        <span>Estás configurando los permisos predeterminados del rol: <strong class="text-slate-200">{{ $roleLabelText }}</strong></span>
                    </div>
                    <button type="submit" class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-lime-500/20 active:scale-95 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Guardar Permisos del Rol</span>
                    </button>
                </div>
            </form>
        @endforeach
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: PERMISOS POR USUARIO (OVERRIDES)    -->
    <!-- ========================================== -->
    <div id="perm-tab-users" class="space-y-6 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Staff List Picker -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase text-slate-300 tracking-wider">Seleccionar Usuario</h3>
                        <span class="text-[11px] text-slate-500 font-bold">{{ count($staffUsers) }} usuarios</span>
                    </div>

                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="user-list-search" oninput="filterUserList()" 
                               placeholder="Filtrar por nombre o correo..." 
                               class="w-full bg-slate-950 border border-slate-800 text-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs focus:border-lime-500 focus:outline-none">
                    </div>

                    <div id="user-picker-list" class="space-y-2 max-h-[520px] overflow-y-auto pr-1">
                        @foreach($staffUsers as $stUser)
                            @php
                                $uName = trim(($stUser->profile->first_name ?? '') . ' ' . ($stUser->profile->last_name ?? '')) ?: $stUser->email;
                                $uPhoto = $stUser->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200';
                                $overrideCount = $stUser->permissionsOverride->count();
                                $uRoleBadge = match($stUser->role) {
                                    'admin' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'cajero' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'trainer' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                    default => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                };
                            @endphp
                            <div onclick="loadUserPermissions({{ $stUser->id }})" 
                                 id="user-card-item-{{ $stUser->id }}"
                                 class="user-card-item flex items-center justify-between p-3 rounded-2xl bg-slate-950/40 border border-slate-850 hover:border-lime-500/40 hover:bg-slate-900/80 transition-all cursor-pointer group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ $uPhoto }}" alt="{{ $uName }}" class="w-9 h-9 rounded-full object-cover border border-slate-700 shrink-0">
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-slate-200 truncate group-hover:text-lime-300 transition-colors">{{ $uName }}</h4>
                                        <p class="text-[10px] text-slate-500 truncate">{{ $stUser->email }}</p>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded border {{ $uRoleBadge }}">
                                                {{ ucfirst($stUser->role) }}
                                            </span>
                                            @if($overrideCount > 0)
                                                <span class="text-[9px] font-bold text-blue-400 bg-blue-500/10 border border-blue-500/20 px-1.5 py-0.2 rounded">
                                                    {{ $overrideCount }} {{ $overrideCount === 1 ? 'excepción' : 'excepciones' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-600 group-hover:text-lime-400 transition-colors shrink-0"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right: User Permissions Matrix & Tri-State Overrides -->
            <div class="lg:col-span-8 space-y-4">
                <!-- Empty placeholder before selection -->
                <div id="user-details-empty" class="bg-slate-900/30 border border-slate-800/80 rounded-3xl p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-slate-800/60 border border-slate-700 flex items-center justify-center text-slate-400 mx-auto">
                        <i data-lucide="user-check" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-200">Selecciona un usuario de la lista</h3>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Podrás ver los permisos que hereda de su rol y conceder o denegar permisos específicos para este usuario.</p>
                    </div>
                </div>

                <!-- User Overrides Container -->
                <div id="user-details-container" class="space-y-4 hidden">
                    <!-- User Header Banner -->
                    <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-lg">
                        <div class="flex items-center gap-3.5">
                            <img id="selected-user-photo" src="" alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-lime-500/30 shadow-md">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 id="selected-user-name" class="text-sm font-black text-slate-100 tracking-tight"></h3>
                                    <span id="selected-user-role-badge" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full border"></span>
                                </div>
                                <p id="selected-user-email" class="text-xs text-slate-400 mt-0.5"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="resetSelectedUserPermissions()" 
                                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs transition-colors border border-slate-700">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Restablecer a Rol</span>
                            </button>
                            <button type="button" onclick="saveSelectedUserPermissions()" 
                                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-lime-500 hover:bg-lime-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-all shadow-lg shadow-lime-500/20 active:scale-95 cursor-pointer">
                                <i data-lucide="save" class="w-3.5 h-3.5"></i>
                                <span>Guardar Excepciones</span>
                            </button>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950/60 border border-slate-850 text-xs">
                            <span class="w-3 h-3 rounded-full bg-slate-500 shrink-0"></span>
                            <span class="text-slate-400 text-[11px]"><strong class="text-slate-200">Heredado:</strong> Según su rol base</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs">
                            <span class="w-3 h-3 rounded-full bg-blue-400 shrink-0"></span>
                            <span class="text-blue-300 text-[11px]"><strong class="text-blue-200">Concedido Especial:</strong> Permiso extra</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-xs">
                            <span class="w-3 h-3 rounded-full bg-rose-400 shrink-0"></span>
                            <span class="text-rose-300 text-[11px]"><strong class="text-rose-200">Denegado:</strong> Permiso revocado</span>
                        </div>
                    </div>

                    <!-- Filter search for user permissions -->
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="user-perm-search" oninput="filterUserPermCards()" 
                               placeholder="Filtrar permisos de este usuario..." 
                               class="w-full bg-slate-950 border border-slate-800 text-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:border-lime-500 focus:outline-none">
                    </div>

                    <!-- Permissions List container for the user -->
                    <div id="user-permissions-matrix" class="space-y-4">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: RESUMEN DE EXCEPCIONES ACTIVAS     -->
    <!-- ========================================== -->
    <div id="perm-tab-summary" class="space-y-6 hidden">
        <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-100">Usuarios con Excepciones Personalizadas</h3>
                    <p class="text-xs text-slate-400">Listado de colaboradores que tienen permisos diferentes a los predeterminados de su rol.</p>
                </div>
                <span class="text-xs font-bold text-blue-400 bg-blue-500/10 border border-blue-500/20 px-3 py-1 rounded-full">
                    {{ count($usersWithOverrides) }} colaboradores
                </span>
            </div>

            @if(count($usersWithOverrides) === 0)
                <div class="p-8 text-center bg-slate-950/40 rounded-2xl border border-slate-850 space-y-2">
                    <i data-lucide="check-circle-2" class="w-8 h-8 text-lime-400 mx-auto"></i>
                    <h4 class="text-xs font-bold text-slate-200">Todos los usuarios utilizan los permisos predeterminados de su rol</h4>
                    <p class="text-[11px] text-slate-500">No hay excepciones ni permisos personalizados configurados actualmente.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($usersWithOverrides as $excUser)
                        @php
                            $excName = trim(($excUser->profile->first_name ?? '') . ' ' . ($excUser->profile->last_name ?? '')) ?: $excUser->email;
                            $excPhoto = $excUser->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200';
                            $grantedCount = $excUser->permissionsOverride->where('is_granted', 1)->count();
                            $deniedCount = $excUser->permissionsOverride->where('is_granted', 0)->count();
                        @endphp
                        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-850 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:border-slate-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <img src="{{ $excPhoto }}" alt="{{ $excName }}" class="w-10 h-10 rounded-full object-cover border border-slate-700 shrink-0">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-slate-200">{{ $excName }}</h4>
                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded border bg-slate-800 text-slate-300 border-slate-700">
                                            {{ ucfirst($excUser->role) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500">{{ $excUser->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-wrap">
                                @if($grantedCount > 0)
                                    <span class="text-[10px] font-bold text-blue-400 bg-blue-500/10 border border-blue-500/20 px-2.5 py-1 rounded-lg">
                                        +{{ $grantedCount }} Extra Concedidos
                                    </span>
                                @endif
                                @if($deniedCount > 0)
                                    <span class="text-[10px] font-bold text-rose-400 bg-rose-500/10 border border-rose-500/20 px-2.5 py-1 rounded-lg">
                                        -{{ $deniedCount }} Revocados
                                    </span>
                                @endif
                                <button type="button" onclick="switchPermTab('users'); loadUserPermissions({{ $excUser->id }});"
                                        class="px-3 py-1.5 text-xs font-bold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition-colors">
                                    Editar Excepciones
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // State management
    let activeRoleTab = 'admin';
    let currentSelectedUserId = null;
    let currentUserPermissionsData = [];
    let currentUserOverridesPending = {}; // { permId: 'granted' | 'denied' | 'default' }

    function switchPermTab(tabName) {
        document.querySelectorAll('.perm-tab-btn').forEach(btn => {
            btn.classList.remove('bg-lime-500', 'text-slate-950', 'shadow-lg', 'shadow-lime-500/20');
            btn.classList.add('text-slate-400');
        });

        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.add('bg-lime-500', 'text-slate-950', 'shadow-lg', 'shadow-lime-500/20');
            activeBtn.classList.remove('text-slate-400');
        }

        document.getElementById('perm-tab-roles').classList.toggle('hidden', tabName !== 'roles');
        document.getElementById('perm-tab-users').classList.toggle('hidden', tabName !== 'users');
        document.getElementById('perm-tab-summary').classList.toggle('hidden', tabName !== 'summary');
    }

    function selectRoleTab(roleKey) {
        activeRoleTab = roleKey;
        document.querySelectorAll('.role-select-btn').forEach(btn => {
            btn.classList.remove('bg-lime-500/10', 'text-lime-400', 'border-lime-500/30');
            btn.classList.add('text-slate-400', 'border-transparent');
        });

        const activeBtn = document.getElementById('role-btn-' + roleKey);
        if (activeBtn) {
            activeBtn.classList.add('bg-lime-500/10', 'text-lime-400', 'border-lime-500/30');
            activeBtn.classList.remove('text-slate-400', 'border-transparent');
        }

        document.querySelectorAll('.role-perm-form').forEach(f => f.classList.add('hidden'));
        const targetForm = document.getElementById('form-role-' + roleKey);
        if (targetForm) targetForm.classList.remove('hidden');
    }

    function toggleAllCheckboxes(formId, checkState) {
        const form = document.getElementById(formId);
        if (form) {
            form.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checkState);
        }
    }

    function filterRolePermissions() {
        const query = document.getElementById('role-perm-search').value.toLowerCase().trim();
        const activeForm = document.getElementById('form-role-' + activeRoleTab);
        if (!activeForm) return;

        activeForm.querySelectorAll('.module-card').forEach(card => {
            let hasVisibleItem = false;
            card.querySelectorAll('.perm-item-row').forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.classList.remove('hidden');
                    hasVisibleItem = true;
                } else {
                    row.classList.add('hidden');
                }
            });
            card.classList.toggle('hidden', !hasVisibleItem && query !== '');
        });
    }

    function filterUserList() {
        const query = document.getElementById('user-list-search').value.toLowerCase().trim();
        document.querySelectorAll('.user-card-item').forEach(card => {
            const text = card.innerText.toLowerCase();
            card.classList.toggle('hidden', !text.includes(query) && query !== '');
        });
    }

    // Load user permissions via AJAX
    async function loadUserPermissions(userId) {
        currentSelectedUserId = userId;
        currentUserOverridesPending = {};

        // Highlight selected user in left list
        document.querySelectorAll('.user-card-item').forEach(c => c.classList.remove('border-lime-500', 'bg-slate-900'));
        const activeCard = document.getElementById('user-card-item-' + userId);
        if (activeCard) activeCard.classList.add('border-lime-500', 'bg-slate-900');

        document.getElementById('user-details-empty').classList.add('hidden');
        const container = document.getElementById('user-details-container');
        container.classList.remove('hidden');

        try {
            const res = await fetch(`/permisos/users/${userId}`);
            const data = await res.json();

            if (!data.success) throw new Error(data.message || 'Error al cargar');

            const user = data.user;
            currentUserPermissionsData = data.permissions;

            document.getElementById('selected-user-name').innerText = user.name;
            document.getElementById('selected-user-email').innerText = user.email + ' • Sucursal: ' + user.gym_name;
            document.getElementById('selected-user-photo').src = user.photo;
            
            const roleBadge = document.getElementById('selected-user-role-badge');
            roleBadge.innerText = 'Rol: ' + user.role_label;
            roleBadge.className = 'text-[10px] font-black uppercase px-2 py-0.5 rounded-full border ' + (
                user.role === 'admin' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' :
                (user.role === 'cajero' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20')
            );

            renderUserPermissionsMatrix();
        } catch (err) {
            window.showToast('Error al obtener los permisos del usuario: ' + err.message, 'error');
        }
    }

    function renderUserPermissionsMatrix() {
        const matrixContainer = document.getElementById('user-permissions-matrix');
        matrixContainer.innerHTML = '';

        // Group by module
        const grouped = {};
        currentUserPermissionsData.forEach(p => {
            if (!grouped[p.module]) grouped[p.module] = [];
            grouped[p.module].push(p);
        });

        for (const [moduleName, permList] of Object.entries(grouped)) {
            const card = document.createElement('div');
            card.className = 'user-perm-module-card bg-slate-900/50 border border-slate-800 rounded-3xl p-5 space-y-3';
            
            let html = `
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-2.5">
                    <h4 class="font-extrabold text-xs text-slate-200 uppercase tracking-wider">${moduleName}</h4>
                    <span class="text-[10px] text-slate-500 font-bold">${permList.length} items</span>
                </div>
                <div class="space-y-2.5">
            `;

            permList.forEach(p => {
                const currentState = currentUserOverridesPending[p.id] || (
                    p.has_override ? (p.override_value ? 'granted' : 'denied') : 'default'
                );

                const roleDefaultText = p.role_default ? 'Concedido por Rol' : 'No concedido por Rol';

                html += `
                    <div class="user-perm-row p-3 rounded-2xl bg-slate-950/50 border border-slate-850 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" data-id="${p.id}">
                        <div class="space-y-1 min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-slate-200">${p.name}</span>
                                <span id="status-badge-${p.id}" class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full border"></span>
                            </div>
                            <p class="text-[11px] text-slate-400">${p.description || ''}</p>
                            <div class="flex items-center gap-2 text-[10px] text-slate-500">
                                <code>${p.code}</code>
                                <span>• Base: ${roleDefaultText}</span>
                            </div>
                        </div>

                        <!-- 3-Way Tri-State Selector -->
                        <div class="inline-flex rounded-xl bg-slate-900 p-1 border border-slate-800 shrink-0 self-start sm:self-auto">
                            <button type="button" onclick="setUserPermState(${p.id}, 'default')" id="btn-state-default-${p.id}"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors text-slate-400 hover:text-white">
                                Heredar Rol
                            </button>
                            <button type="button" onclick="setUserPermState(${p.id}, 'granted')" id="btn-state-granted-${p.id}"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors text-slate-400 hover:text-blue-300">
                                + Conceder
                            </button>
                            <button type="button" onclick="setUserPermState(${p.id}, 'denied')" id="btn-state-denied-${p.id}"
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors text-slate-400 hover:text-rose-300">
                                - Denegar
                            </button>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;
            card.innerHTML = html;
            matrixContainer.appendChild(card);
        }

        // Apply visual button styles for each permission
        currentUserPermissionsData.forEach(p => {
            const state = currentUserOverridesPending[p.id] || (
                p.has_override ? (p.override_value ? 'granted' : 'denied') : 'default'
            );
            updatePermRowUI(p.id, state, p.role_default);
        });

        lucide.createIcons();
    }

    function setUserPermState(permId, newState) {
        currentUserOverridesPending[permId] = newState;
        const p = currentUserPermissionsData.find(x => x.id === permId);
        updatePermRowUI(permId, newState, p ? p.role_default : false);
    }

    function updatePermRowUI(permId, state, roleDefault) {
        const btnDefault = document.getElementById(`btn-state-default-${permId}`);
        const btnGranted = document.getElementById(`btn-state-granted-${permId}`);
        const btnDenied = document.getElementById(`btn-state-denied-${permId}`);
        const badge = document.getElementById(`status-badge-${permId}`);

        if (!btnDefault || !btnGranted || !btnDenied || !badge) return;

        [btnDefault, btnGranted, btnDenied].forEach(b => {
            b.className = 'px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors text-slate-400 hover:text-white';
        });

        if (state === 'default') {
            btnDefault.className = 'px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-slate-800 text-slate-200 border border-slate-700 shadow-xs';
            if (roleDefault) {
                badge.innerText = 'Heredado (Activo)';
                badge.className = 'text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full border bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
            } else {
                badge.innerText = 'Heredado (Inactivo)';
                badge.className = 'text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full border bg-slate-800 text-slate-400 border-slate-700';
            }
        } else if (state === 'granted') {
            btnGranted.className = 'px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-blue-500 text-slate-950 shadow-md shadow-blue-500/20';
            badge.innerText = 'Concedido Especial';
            badge.className = 'text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full border bg-blue-500/20 text-blue-400 border-blue-500/30';
        } else if (state === 'denied') {
            btnDenied.className = 'px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-rose-500 text-white shadow-md shadow-rose-500/20';
            badge.innerText = 'Denegado Explícito';
            badge.className = 'text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full border bg-rose-500/20 text-rose-400 border-rose-500/30';
        }
    }

    function filterUserPermCards() {
        const query = document.getElementById('user-perm-search').value.toLowerCase().trim();
        document.querySelectorAll('.user-perm-module-card').forEach(card => {
            let hasVisible = false;
            card.querySelectorAll('.user-perm-row').forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    row.classList.add('hidden');
                }
            });
            card.classList.toggle('hidden', !hasVisible && query !== '');
        });
    }

    async function saveUserPermissions() {
        if (!currentSelectedUserId) return;

        const payload = [];
        for (const [permId, state] of Object.entries(currentUserOverridesPending)) {
            payload.push({
                permission_id: parseInt(permId),
                state: state
            });
        }

        if (payload.length === 0) {
            window.showToast('No has modificado ningún permiso para este usuario.', 'warning');
            return;
        }

        try {
            const res = await fetch(`/permisos/users/${currentSelectedUserId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ overrides: payload })
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Error al guardar');

            window.showToast(data.message, 'success');
            // Reload user permissions to update UI
            loadUserPermissions(currentSelectedUserId);
        } catch (err) {
            window.showToast('Error al guardar permisos del usuario: ' + err.message, 'error');
        }
    }

    async function resetSelectedUserPermissions() {
        if (!currentSelectedUserId) return;
        if (!confirm('¿Deseas restablecer todos los permisos de este usuario a los valores predeterminados de su rol?')) {
            return;
        }

        try {
            const res = await fetch(`/permisos/users/${currentSelectedUserId}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Error al restablecer');

            window.showToast(data.message, 'success');
            loadUserPermissions(currentSelectedUserId);
        } catch (err) {
            window.showToast('Error al restablecer: ' + err.message, 'error');
        }
    }
</script>
@endsection
