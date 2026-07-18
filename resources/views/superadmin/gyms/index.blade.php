@extends('layouts.admin')

@section('title', 'Gestión de Sucursales')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Gestión de Sucursales (Gimnasios)</h1>
            <p class="text-slate-400 text-xs mt-1">Supervisa sucursales, activa/suspende el servicio y gestiona el acceso a la plataforma.</p>
        </div>
        <button onclick="toggleNewGymModal()" class="px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
            Nueva Sucursal
        </button>
    </div>

    <!-- Error/Success Alerts -->
    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-4 rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <!-- Gyms Table Card -->
    <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3 px-4">Logo / Nombre</th>
                        <th class="py-3 px-4">Slug / Plan SaaS</th>
                        <th class="py-3 px-4 text-center">Estado Pago</th>
                        <th class="py-3 px-4">Contacto</th>
                        <th class="py-3 px-4 text-center">Socios</th>
                        <th class="py-3 px-4 text-center">Entrenadores</th>
                        <th class="py-3 px-4 text-center">Estado App</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40 text-sm">
                    @forelse($gyms as $gym)
                        <tr class="hover:bg-slate-800/10 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    @if($gym->logo_url && file_exists(public_path($gym->logo_url)))
                                        <img src="{{ asset($gym->logo_url) }}" alt="Logo" class="w-10 h-10 rounded-xl object-cover border border-slate-800 shadow-md shrink-0">
                                    @else
                                        <div class="p-2.5 {{ $gym->is_active ? 'bg-lime-500/10 text-lime-400' : 'bg-slate-850 text-slate-500' }} rounded-xl border border-slate-800 shrink-0">
                                            <i data-lucide="dumbbell" class="w-5 h-5"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="block font-bold text-slate-200">{{ $gym->name }}</span>
                                        <span class="block text-[10px] text-slate-500">ID: {{ $gym->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs">
                                <span class="block font-mono text-lime-400">/{{ $gym->slug ?? 'sin-slug' }}</span>
                                @if($gym->plan)
                                    <span class="px-1.5 py-0.5 bg-purple-500/15 text-purple-400 border border-purple-500/20 rounded font-black text-[9px] uppercase mt-1 inline-block">
                                        {{ $gym->plan->name }}
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 bg-slate-850 text-slate-500 border border-slate-800 rounded font-bold text-[9px] uppercase mt-1 inline-block">
                                        Sin Plan (Ilimitado)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($gym->subscription_status === 'active')
                                    <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md uppercase tracking-wide">
                                        Activo
                                    </span>
                                @elseif($gym->subscription_status === 'trialing')
                                    <span class="px-2 py-0.5 text-[9px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20 rounded-md uppercase tracking-wide">
                                        Prueba
                                    </span>
                                @elseif($gym->subscription_status === 'past_due')
                                    <span class="px-2 py-0.5 text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-md uppercase tracking-wide">
                                        Pendiente
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-md uppercase tracking-wide">
                                        Cancelado
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="block text-xs text-slate-300 font-medium">{{ $gym->email ?? 'Sin correo' }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $gym->phone ?? 'Sin teléfono' }}</span>
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-slate-200">
                                {{ $gym->members_count }}
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-slate-200">
                                {{ $gym->staff_count }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($gym->is_active)
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md">
                                        Habilitado
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-md">
                                        Suspendido
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button onclick="openEditGymModal({{ json_encode($gym) }})" class="p-1.5 bg-slate-800/80 text-slate-300 border border-slate-700/50 hover:bg-slate-700 hover:text-white rounded-lg transition-all" title="Editar Sucursal">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Toggle Status Form -->
                                    <form action="{{ route('superadmin.gyms.toggle', $gym->id) }}" method="POST" class="inline m-0">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 {{ $gym->is_active ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 hover:bg-amber-550' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-550' }} hover:text-slate-950 text-xs font-bold rounded-lg border transition-all">
                                            {{ $gym->is_active ? 'Suspender' : 'Habilitar' }}
                                        </button>
                                    </form>

                                    <!-- Delete Form -->
                                    <form action="{{ route('superadmin.gyms.destroy', $gym->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta sucursal permanentemente? Esta acción es irreversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500 hover:text-slate-950 rounded-lg transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-550">
                                <i data-lucide="shield-alert" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                                <p class="font-bold">No se encontraron sucursales registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
</div>

@push('modals')
    <!-- New Gym Modal/Form Panel -->
    <div id="new-gym-modal" class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl my-auto animate-scale-up">
            <div class="flex items-center justify-between mb-6 border-b border-slate-800 pb-4">
                <h3 class="font-extrabold text-white text-lg flex items-center gap-2">
                    <i data-lucide="dumbbell" class="text-lime-400 w-5 h-5"></i>
                    Registrar Nueva Sucursal
                </h3>
                <button onclick="toggleNewGymModal()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.gyms.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold max-h-[50vh] sm:max-h-[55vh] md:max-h-[60vh] overflow-y-auto pr-1">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Gimnasio</label>
                        <input type="text" name="name" id="name" required placeholder="Ej: GymFlow Studio Sur" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="slug" class="block text-slate-400 uppercase tracking-wider mb-1.5">Identificador URL (Slug)</label>
                        <input type="text" name="slug" id="slug" placeholder="Ej: gymflow-sur (Opcional)" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="current_plan_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Plan SaaS</label>
                        <select name="current_plan_id" id="current_plan_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="">Sin Plan (Ilimitado provisional)</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} (${{ $plan->monthly_price }}/mes)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="subscription_status" class="block text-slate-400 uppercase tracking-wider mb-1.5">Estado Pago SaaS</label>
                        <select name="subscription_status" id="subscription_status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="trialing" selected>Prueba (Trialing)</option>
                            <option value="active">Activo (Al día)</option>
                            <option value="past_due">Pendiente de Pago (Past Due)</option>
                            <option value="canceled">Cancelado (Canceled)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto</label>
                        <input type="email" name="email" id="email" placeholder="contacto@ejemplo.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                        <input type="text" name="phone" id="phone" placeholder="+58 412..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física</label>
                    <textarea name="address" id="address" rows="2" placeholder="Dirección detallada de la sucursal..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="primary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Primario (Branding)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" id="primary_color" value="#000000" class="w-10 h-10 bg-transparent border-0 cursor-pointer rounded-lg">
                            <span class="text-xs text-slate-500 font-mono">Hexadecimal</span>
                        </div>
                    </div>
                    <div>
                        <label for="secondary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Secundario (Branding)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary_color" id="secondary_color" value="#FFFFFF" class="w-10 h-10 bg-transparent border-0 cursor-pointer rounded-lg">
                            <span class="text-xs text-slate-500 font-mono">Hexadecimal</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="timezone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Zona Horaria</label>
                        <select name="timezone" id="timezone" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="Europe/Madrid" selected>Madrid (Europe/Madrid)</option>
                            <option value="America/Caracas">Caracas (America/Caracas)</option>
                            <option value="America/Bogota">Bogotá (America/Bogota)</option>
                            <option value="America/Mexico_City">Ciudad de México (America/Mexico_City)</option>
                            <option value="America/Santiago">Santiago (America/Santiago)</option>
                            <option value="America/Argentina/Buenos_Aires">Buenos Aires (America/Argentina/Buenos_Aires)</option>
                        </select>
                    </div>
                    <div>
                        <label for="logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Logo del Gimnasio</label>
                        <input type="file" name="logo" id="logo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-400 file:mr-4 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
                    </div>
                </div>

                <div class="pt-4 flex gap-3 border-t border-slate-800">
                    <button type="button" onclick="toggleNewGymModal()" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold rounded-xl border border-slate-700/50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 transition-all">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Gym Modal -->
    <div id="edit-gym-modal" class="fixed inset-0 z-50 flex items-start sm:items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl my-auto animate-scale-up">
            <div class="flex items-center justify-between mb-6 border-b border-slate-800 pb-4">
                <h3 class="font-extrabold text-white text-lg flex items-center gap-2">
                    <i data-lucide="edit-3" class="text-lime-400 w-5 h-5"></i>
                    Editar Sucursal
                </h3>
                <button onclick="toggleEditGymModal()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="edit-gym-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold max-h-[50vh] sm:max-h-[55vh] md:max-h-[60vh] overflow-y-auto pr-1">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Gimnasio</label>
                        <input type="text" name="name" id="edit_name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_slug" class="block text-slate-400 uppercase tracking-wider mb-1.5">Identificador URL (Slug)</label>
                        <input type="text" name="slug" id="edit_slug" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_current_plan_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Plan SaaS</label>
                        <select name="current_plan_id" id="edit_current_plan_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="">Sin Plan (Ilimitado provisional)</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} (${{ $plan->monthly_price }}/mes)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit_subscription_status" class="block text-slate-400 uppercase tracking-wider mb-1.5">Estado Pago SaaS</label>
                        <select name="subscription_status" id="edit_subscription_status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="trialing">Prueba (Trialing)</option>
                            <option value="active">Activo (Al día)</option>
                            <option value="past_due">Pendiente de Pago (Past Due)</option>
                            <option value="canceled">Cancelado (Canceled)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto</label>
                        <input type="email" name="email" id="edit_email" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label for="edit_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                        <input type="text" name="phone" id="edit_phone" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>

                <div>
                    <label for="edit_address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física</label>
                    <textarea name="address" id="edit_address" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_primary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Primario (Branding)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" id="edit_primary_color" class="w-10 h-10 bg-transparent border-0 cursor-pointer rounded-lg">
                            <span class="text-xs text-slate-550 font-mono">Hexadecimal</span>
                        </div>
                    </div>
                    <div>
                        <label for="edit_secondary_color" class="block text-slate-400 uppercase tracking-wider mb-1.5">Color Secundario (Branding)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary_color" id="edit_secondary_color" class="w-10 h-10 bg-transparent border-0 cursor-pointer rounded-lg">
                            <span class="text-xs text-slate-550 font-mono">Hexadecimal</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_timezone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Zona Horaria</label>
                        <select name="timezone" id="edit_timezone" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                            <option value="Europe/Madrid">Madrid (Europe/Madrid)</option>
                            <option value="America/Caracas">Caracas (America/Caracas)</option>
                            <option value="America/Bogota">Bogotá (America/Bogota)</option>
                            <option value="America/Mexico_City">Ciudad de México (America/Mexico_City)</option>
                            <option value="America/Santiago">Santiago (America/Santiago)</option>
                            <option value="America/Argentina/Buenos_Aires">Buenos Aires (America/Argentina/Buenos_Aires)</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Logo del Gimnasio (Opcional)</label>
                        <div class="flex items-center gap-4">
                            <div id="edit_logo_preview_container" class="w-10 h-10 rounded-xl border border-slate-800 overflow-hidden bg-slate-950 flex items-center justify-center shrink-0">
                                <!-- Populated dynamically via JS -->
                            </div>
                            <input type="file" name="logo" id="edit_logo" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-400 file:mr-4 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
                        </div>
                        <div class="flex items-center gap-2 mt-2 hidden" id="remove_logo_container">
                            <input type="checkbox" name="remove_logo" id="remove_logo" value="1" class="rounded bg-slate-950 border-slate-800 text-lime-500 focus:ring-lime-500 cursor-pointer">
                            <label for="remove_logo" class="text-xs text-rose-400 font-bold select-none cursor-pointer">Eliminar logo actual</label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-3 border-t border-slate-800">
                    <button type="button" onclick="toggleEditGymModal()" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-750 text-slate-200 font-bold rounded-xl border border-slate-700/50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 transition-all">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endpush

<script>
    function toggleNewGymModal() {
        const modal = document.getElementById('new-gym-modal');
        modal.classList.toggle('hidden');
    }

    function toggleEditGymModal() {
        const modal = document.getElementById('edit-gym-modal');
        modal.classList.toggle('hidden');
    }

    function openEditGymModal(gym) {
        document.getElementById('edit_name').value = gym.name || '';
        document.getElementById('edit_slug').value = gym.slug || '';
        document.getElementById('edit_current_plan_id').value = gym.current_plan_id || '';
        document.getElementById('edit_subscription_status').value = gym.subscription_status || 'trialing';
        document.getElementById('edit_phone').value = gym.phone || '';
        document.getElementById('edit_email').value = gym.email || '';
        document.getElementById('edit_address').value = gym.address || '';
        document.getElementById('edit_primary_color').value = gym.primary_color || '#000000';
        document.getElementById('edit_secondary_color').value = gym.secondary_color || '#FFFFFF';
        document.getElementById('edit_timezone').value = gym.timezone || 'Europe/Madrid';
        
        // Reset checkbox state
        const removeLogoCheckbox = document.getElementById('remove_logo');
        const removeLogoContainer = document.getElementById('remove_logo_container');
        removeLogoCheckbox.checked = false;
        
        const previewContainer = document.getElementById('edit_logo_preview_container');
        if (gym.logo_url) {
            previewContainer.innerHTML = `<img src="/${gym.logo_url}" class="w-full h-full object-cover">`;
            removeLogoContainer.classList.remove('hidden');
        } else {
            previewContainer.innerHTML = `<i data-lucide="dumbbell" class="w-5 h-5 text-slate-650"></i>`;
            removeLogoContainer.classList.add('hidden');
        }
        
        // Re-init lucide icons inside container
        lucide.createIcons();

        const form = document.getElementById('edit-gym-form');
        form.action = `/superadmin/gyms/${gym.id}`;

        toggleEditGymModal();
    }
</script>
@endsection
