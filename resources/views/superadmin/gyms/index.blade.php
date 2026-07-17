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
    <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                        <th class="py-3 px-4">Logo / Nombre</th>
                        <th class="py-3 px-4">Contacto</th>
                        <th class="py-3 px-4">Dirección</th>
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
                            <td class="py-4 px-4">
                                <span class="block text-xs text-slate-300 font-medium">{{ $gym->email ?? 'Sin correo' }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $gym->phone ?? 'Sin teléfono' }}</span>
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-400 max-w-xs truncate">
                                {{ $gym->address ?? 'No registrada' }}
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
                                    <form action="{{ route('superadmin.gyms.toggle', $gym->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 {{ $gym->is_active ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 hover:bg-amber-500' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500' }} hover:text-slate-950 text-xs font-bold rounded-lg border transition-all">
                                            {{ $gym->is_active ? 'Suspender' : 'Habilitar' }}
                                        </button>
                                    </form>

                                    <!-- Delete Form -->
                                    <form action="{{ route('superadmin.gyms.destroy', $gym->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta sucursal permanentemente? Esta acción es irreversible.');">
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
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <i data-lucide="shield-alert" class="w-12 h-12 mx-auto text-slate-600 mb-3"></i>
                                <p>No se encontraron sucursales registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Gym Modal/Form Panel -->
    <div id="new-gym-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-extrabold text-white text-lg flex items-center gap-2">
                    <i data-lucide="dumbbell" class="text-lime-400 w-5 h-5"></i>
                    Registrar Nueva Sucursal
                </h3>
                <button onclick="toggleNewGymModal()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.gyms.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                @csrf

                <div>
                    <label for="name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Gimnasio</label>
                    <input type="text" name="name" id="name" required placeholder="Ej: GymFlow Studio Sur" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto</label>
                    <input type="email" name="email" id="email" placeholder="contacto@ejemplo.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                    <input type="text" name="phone" id="phone" placeholder="+58 412..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física</label>
                    <textarea name="address" id="address" rows="3" placeholder="Dirección detallada de la sucursal..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
                </div>

                <div>
                    <label for="logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Logo del Gimnasio (Opcional)</label>
                    <input type="file" name="logo" id="logo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-400 file:mr-4 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
                </div>

                <div class="pt-4 flex gap-3">
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
    <div id="edit-gym-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-extrabold text-white text-lg flex items-center gap-2">
                    <i data-lucide="edit-3" class="text-lime-400 w-5 h-5"></i>
                    Editar Sucursal
                </h3>
                <button onclick="toggleEditGymModal()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="edit-gym-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit_name" class="block text-slate-400 uppercase tracking-wider mb-1.5">Nombre del Gimnasio</label>
                    <input type="text" name="name" id="edit_name" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="edit_email" class="block text-slate-400 uppercase tracking-wider mb-1.5">Correo de Contacto</label>
                    <input type="email" name="email" id="edit_email" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="edit_phone" class="block text-slate-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50">
                </div>

                <div>
                    <label for="edit_address" class="block text-slate-400 uppercase tracking-wider mb-1.5">Dirección Física</label>
                    <textarea name="address" id="edit_address" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50"></textarea>
                </div>

                <div>
                    <label for="edit_logo" class="block text-slate-400 uppercase tracking-wider mb-1.5">Logo del Gimnasio (Opcional)</label>
                    <div class="flex items-center gap-4">
                        <div id="edit_logo_preview_container" class="w-12 h-12 rounded-xl border border-slate-800 overflow-hidden bg-slate-950 flex items-center justify-center shrink-0">
                            <!-- Populated dynamically via JS -->
                        </div>
                        <input type="file" name="logo" id="edit_logo" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-400 file:mr-4 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-lime-500/10 file:text-lime-400 hover:file:bg-lime-500/20 cursor-pointer">
                    </div>
                    <div class="flex items-center gap-2 mt-2.5 hidden" id="remove_logo_container">
                        <input type="checkbox" name="remove_logo" id="remove_logo" value="1" class="rounded bg-slate-950 border-slate-800 text-lime-500 focus:ring-lime-500 cursor-pointer">
                        <label for="remove_logo" class="text-slate-400 font-bold select-none cursor-pointer">Eliminar logo actual</label>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
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

</div>

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
        document.getElementById('edit_email').value = gym.email || '';
        document.getElementById('edit_phone').value = gym.phone || '';
        document.getElementById('edit_address').value = gym.address || '';
        
        // Reset checkbox state
        const removeLogoCheckbox = document.getElementById('remove_logo');
        const removeLogoContainer = document.getElementById('remove_logo_container');
        removeLogoCheckbox.checked = false;
        
        const previewContainer = document.getElementById('edit_logo_preview_container');
        if (gym.logo_url) {
            previewContainer.innerHTML = `<img src="/${gym.logo_url}" class="w-full h-full object-cover">`;
            removeLogoContainer.classList.remove('hidden');
        } else {
            previewContainer.innerHTML = `<i data-lucide="dumbbell" class="w-5 h-5 text-slate-600"></i>`;
            lucide.createIcons();
            removeLogoContainer.classList.add('hidden');
        }

        const form = document.getElementById('edit-gym-form');
        form.action = `/superadmin/gyms/${gym.id}`;

        toggleEditGymModal();
    }
</script>
@endsection
