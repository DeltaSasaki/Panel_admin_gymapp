@extends('layouts.admin')

@section('title', 'Gestión de Staff y Entrenadores')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Staff de Entrenadores</h1>
            <p class="text-xs text-slate-400 mt-1">Registra personal de entrenamientos, salarios de nómina y credenciales de acceso.</p>
        </div>
        <div>
            <button onclick="openCreateModal()" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Reclutar Entrenador
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-xs flex gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <div>
                @foreach($errors->all() as $error)
                    <span class="block">{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Trainers Grid List -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($trainers as $trainer)
            @php
                $photo = $trainer->photo_url ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
                $dni = $trainer->user->profile->dni ?? 'N/A';
            @endphp
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 flex flex-col justify-between hover:border-slate-700 hover:bg-slate-900/60 transition-all shadow-xl group">
                <div>
                    <!-- Main Profile Header -->
                    <div class="flex items-start gap-4">
                        <img src="{{ asset($photo) }}" 
                             alt="Avatar" 
                             class="w-16 h-16 rounded-2xl object-cover ring-2 ring-lime-500/10 group-hover:ring-lime-500/30 transition-all shrink-0">
                        <div class="space-y-1 overflow-hidden flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="font-extrabold text-slate-100 text-base truncate">{{ $trainer->first_name }} {{ $trainer->last_name }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $trainer->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                                    {{ $trainer->is_active ? 'Activo' : 'Suspendido' }}
                                </span>
                            </div>
                            <p class="text-xs text-lime-400 font-semibold truncate">{{ $trainer->specialty }}</p>
                            <p class="text-[10px] text-slate-500 flex items-center gap-1">
                                <i data-lucide="award" class="w-3.5 h-3.5 text-slate-500"></i> {{ $trainer->certification }}
                            </p>
                        </div>
                    </div>

                    <!-- Description/Bio preview -->
                    @if($trainer->bio)
                        <p class="text-xs text-slate-400 mt-4 line-clamp-2 italic bg-slate-950/20 p-2.5 rounded-lg border border-slate-850/50">
                            "{{ $trainer->bio }}"
                        </p>
                    @endif

                    <!-- Info Grid -->
                    <div class="grid grid-cols-3 gap-3 bg-slate-950/45 p-3.5 rounded-xl border border-slate-850/40 my-5 text-[10px]">
                        <div>
                            <span class="block text-slate-500 font-bold uppercase mb-0.5">Sueldo</span>
                            <span class="font-mono font-bold text-slate-200 text-xs">${{ number_format($trainer->salary, 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-bold uppercase mb-0.5">Experiencia</span>
                            <span class="font-bold text-slate-200 text-xs">{{ $trainer->experience_years }} Años</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-bold uppercase mb-0.5">DNI / Cédula</span>
                            <span class="font-mono font-bold text-slate-200 text-xs">{{ $dni }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Operations -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-t border-slate-850/50 pt-4 gap-3">
                    <span class="text-[10px] text-slate-500 truncate">Contacto: <strong>{{ $trainer->email }}</strong></span>
                    
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Details Button -->
                        <button 
                            onclick="openDetailsModal({{ json_encode($trainer) }}, '{{ $dni }}')"
                            class="p-2 bg-slate-950 hover:bg-slate-850 hover:text-slate-100 text-slate-400 rounded-xl border border-slate-850 transition-colors cursor-pointer" 
                            title="Ver Ficha Completa">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>

                        <!-- Edit Button -->
                        <button 
                            onclick="openEditModal({{ json_encode($trainer) }}, '{{ $dni }}')"
                            class="p-2 bg-slate-950 hover:bg-slate-850 hover:text-slate-100 text-slate-400 rounded-xl border border-slate-850 transition-colors cursor-pointer" 
                            title="Editar Datos">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                        </button>

                        <!-- Suspend Button -->
                        <form action="{{ route('staff.toggle_status', $trainer->id) }}" method="POST" class="m-0 inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-[10px] font-bold rounded-xl border transition-colors cursor-pointer {{ $trainer->is_active ? 'bg-amber-500/10 text-amber-450 border-amber-500/20 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-450 border-emerald-500/20 hover:bg-emerald-500/20' }}">
                                {{ $trainer->is_active ? 'Suspender' : 'Reactivar' }}
                            </button>
                        </form>

                        <!-- Delete Button -->
                        <form action="{{ route('staff.destroy', $trainer->id) }}" method="POST" class="m-0 inline" onsubmit="return confirm('¿Estás seguro de eliminar permanentemente a este entrenador? Esto borrará su usuario de acceso y todo su historial.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-xl border border-rose-500/20 transition-colors cursor-pointer" title="Eliminar Entrenador">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-550 bg-slate-900/10 border border-slate-850 border-dashed rounded-3xl">
                <i data-lucide="users-2" class="w-12 h-12 text-slate-700 mx-auto mb-2"></i>
                No hay entrenadores registrados en la base de datos para este gimnasio.
            </div>
        @endforelse
    </div>
</div>

<!-- ================= MODAL: RECLUTAR ENTRENADOR ================= -->
<div id="trainer-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Reclutar Nuevo Entrenador</h3>
            <button onclick="toggleModal('trainer-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <!-- Photo Upload with Preview -->
            <div class="flex flex-col items-center gap-3 bg-slate-950/30 p-4 rounded-2xl border border-slate-850/50">
                <span class="text-xs font-bold uppercase text-slate-400">Foto de Perfil</span>
                <div class="relative group">
                    <img id="create-photo-preview" src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop" 
                         alt="Previsualización" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-slate-800">
                    <label class="absolute inset-0 bg-slate-950/70 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-[10px] font-bold text-slate-200">
                        Cambiar
                        <input type="file" name="photo" class="hidden" accept="image/*" onchange="previewImage(this, 'create-photo-preview')">
                    </label>
                </div>
                <span class="text-[10px] text-slate-500">Formatos: JPG, PNG, WEBP (Max 2MB)</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre</label>
                    <input type="text" name="first_name" required placeholder="Ej: Carlos" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Apellido</label>
                    <input type="text" name="last_name" required placeholder="Ej: Ruiz" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">DNI / Cédula</label>
                    <input type="text" name="dni" required placeholder="Ej: 12345678X" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" placeholder="+34 600 000 000" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Correo de Acceso (Usuario)</label>
                <input type="email" name="email" required placeholder="coach3@gymflow.com" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Sueldo Mensual ($)</label>
                    <input type="number" step="0.01" name="salary" required placeholder="2000" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Años de Exp.</label>
                    <input type="number" name="experience_years" min="0" placeholder="5" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Especialidad</label>
                    <input type="text" name="specialty" placeholder="Ej: HIIT, Musculación" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Límite Socios Asignados</label>
                    <input type="number" name="max_clients" min="1" placeholder="20" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Certificaciones</label>
                <input type="text" name="certification" placeholder="Ej: NSCA-CPT, Kettlebell L2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Biografía / Perfil Curricular</label>
                <textarea name="bio" rows="3" placeholder="Describe brevemente la trayectoria, perfil y enfoque del entrenador..." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 resize-none"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('trainer-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all cursor-pointer">
                    Registrar Trainer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDITAR ENTRENADOR ================= -->
<div id="edit-trainer-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Editar Entrenador</h3>
            <button onclick="toggleModal('edit-trainer-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form id="edit-trainer-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            
            <!-- Photo Upload with Preview -->
            <div class="flex flex-col items-center gap-3 bg-slate-950/30 p-4 rounded-2xl border border-slate-850/50">
                <span class="text-xs font-bold uppercase text-slate-400">Foto de Perfil</span>
                <div class="relative group">
                    <img id="edit-photo-preview" src="" 
                         alt="Previsualización" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-slate-800">
                    <label class="absolute inset-0 bg-slate-950/70 rounded-2xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-[10px] font-bold text-slate-200">
                        Cambiar
                        <input type="file" name="photo" class="hidden" accept="image/*" onchange="previewImage(this, 'edit-photo-preview')">
                    </label>
                </div>
                <span class="text-[10px] text-slate-500">Formatos: JPG, PNG, WEBP (Max 2MB)</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre</label>
                    <input type="text" name="first_name" id="edit-first_name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Apellido</label>
                    <input type="text" name="last_name" id="edit-last_name" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">DNI / Cédula</label>
                    <input type="text" name="dni" id="edit-dni" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" id="edit-phone" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Correo de Acceso (Usuario)</label>
                <input type="email" name="email" id="edit-email" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Contraseña (Opcional)</label>
                <input type="password" name="password" placeholder="Dejar en blanco para no modificar" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Sueldo Mensual ($)</label>
                    <input type="number" step="0.01" name="salary" id="edit-salary" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Años de Exp.</label>
                    <input type="number" name="experience_years" id="edit-experience_years" min="0" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Especialidad</label>
                    <input type="text" name="specialty" id="edit-specialty" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Límite Socios</label>
                    <input type="number" name="max_clients" id="edit-max_clients" min="1" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Certificaciones</label>
                <input type="text" name="certification" id="edit-certification" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Biografía / Perfil Curricular</label>
                <textarea name="bio" id="edit-bio" rows="3" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50 resize-none"></textarea>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('edit-trainer-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all cursor-pointer">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: DETALLES DE ENTRENADOR ================= -->
<div id="details-trainer-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden animate-fade-in">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
                <i data-lucide="user-check" class="text-lime-400 w-5 h-5"></i> Ficha del Entrenador
            </h3>
            <button onclick="toggleModal('details-trainer-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Left Side Details -->
            <div class="flex flex-col items-center gap-4 text-center shrink-0 w-full md:w-1/3">
                <img id="detail-photo" src="" alt="Avatar" class="w-28 h-28 rounded-2xl object-cover ring-4 ring-slate-850 shadow-xl">
                <div>
                    <h4 id="detail-name" class="font-extrabold text-slate-100 text-lg leading-tight"></h4>
                    <span id="detail-specialty" class="block text-xs text-lime-400 font-semibold mt-1"></span>
                </div>
                <span id="detail-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase"></span>
            </div>

            <!-- Right Side Details -->
            <div class="flex-1 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-850/50">
                        <span class="block text-slate-500 font-bold uppercase text-[9px] mb-0.5">DNI / Cédula</span>
                        <span id="detail-dni" class="font-mono font-bold text-slate-200"></span>
                    </div>
                    <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-850/50">
                        <span class="block text-slate-500 font-bold uppercase text-[9px] mb-0.5">Teléfono</span>
                        <span id="detail-phone" class="font-bold text-slate-200"></span>
                    </div>
                    <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-850/50">
                        <span class="block text-slate-500 font-bold uppercase text-[9px] mb-0.5">Sueldo Mensual</span>
                        <span id="detail-salary" class="font-mono font-bold text-slate-200"></span>
                    </div>
                    <div class="bg-slate-950/40 p-3 rounded-xl border border-slate-850/50">
                        <span class="block text-slate-500 font-bold uppercase text-[9px] mb-0.5">Años Exp.</span>
                        <span id="detail-experience" class="font-bold text-slate-200"></span>
                    </div>
                </div>

                <div class="bg-slate-950/40 p-3.5 rounded-xl border border-slate-850/50 text-xs">
                    <span class="block text-slate-500 font-bold uppercase text-[9px] mb-1">Certificaciones</span>
                    <p id="detail-certification" class="font-bold text-slate-200 flex items-center gap-1.5"></p>
                </div>

                <div class="bg-slate-950/40 p-3.5 rounded-xl border border-slate-850/50 text-xs">
                    <div class="flex items-center justify-between text-[9px] text-slate-500 font-bold uppercase mb-1">
                        <span>Límite de Alumnos</span>
                        <span id="detail-max-clients-label" class="text-lime-400"></span>
                    </div>
                    <div class="w-full bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-850">
                        <div id="detail-max-clients-bar" class="bg-lime-500 h-full rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biography Section -->
        <div class="bg-slate-950/20 p-4 rounded-2xl border border-slate-850/50 text-xs space-y-2">
            <span class="text-slate-500 font-bold uppercase text-[9px] flex items-center gap-1">
                <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Biografía / Resumen Profesional
            </span>
            <p id="detail-bio" class="text-slate-300 leading-relaxed italic"></p>
        </div>

        <div class="border-t border-slate-850/50 pt-4 flex justify-between items-center text-[10px] text-slate-500">
            <span>Contratado: <strong id="detail-hire-date"></strong></span>
            <span>Correo: <strong id="detail-email"></strong></span>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    function openCreateModal() {
        document.getElementById('create-photo-preview').src = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
        toggleModal('trainer-modal');
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openEditModal(trainer, dni) {
        const form = document.getElementById('edit-trainer-form');
        form.action = `/staff/${trainer.id}`;
        
        document.getElementById('edit-first_name').value = trainer.first_name;
        document.getElementById('edit-last_name').value = trainer.last_name;
        document.getElementById('edit-dni').value = dni;
        document.getElementById('edit-phone').value = trainer.phone || '';
        document.getElementById('edit-email').value = trainer.email;
        document.getElementById('edit-salary').value = trainer.salary;
        document.getElementById('edit-experience_years').value = trainer.experience_years || 0;
        document.getElementById('edit-specialty').value = trainer.specialty || '';
        document.getElementById('edit-max_clients').value = trainer.max_clients || 20;
        document.getElementById('edit-certification').value = trainer.certification || '';
        document.getElementById('edit-bio').value = trainer.bio || '';
        
        const photoPath = trainer.photo_url ? `/${trainer.photo_url}` : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
        document.getElementById('edit-photo-preview').src = photoPath;

        toggleModal('edit-trainer-modal');
    }

    function openDetailsModal(trainer, dni) {
        document.getElementById('detail-name').innerText = `${trainer.first_name} ${trainer.last_name}`;
        document.getElementById('detail-specialty').innerText = trainer.specialty || 'Entrenador General';
        document.getElementById('detail-dni').innerText = dni;
        document.getElementById('detail-phone').innerText = trainer.phone || 'N/A';
        document.getElementById('detail-salary').innerText = `$${parseFloat(trainer.salary).toLocaleString('es-ES', {minimumFractionDigits: 2})}`;
        document.getElementById('detail-experience').innerText = `${trainer.experience_years || 0} Años`;
        document.getElementById('detail-certification').innerText = trainer.certification || 'Sin certificaciones añadidas';
        document.getElementById('detail-bio').innerText = trainer.bio ? `"${trainer.bio}"` : 'El entrenador no ha redactado su biografía profesional aún.';
        document.getElementById('detail-email').innerText = trainer.email;
        
        // Format Hire Date
        const date = new Date(trainer.hire_date);
        document.getElementById('detail-hire-date').innerText = date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
        
        // Photo
        const photoPath = trainer.photo_url ? `/${trainer.photo_url}` : 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop';
        document.getElementById('detail-photo').src = photoPath;

        // Status Badge
        const statusBadge = document.getElementById('detail-status');
        if (trainer.is_active) {
            statusBadge.innerText = 'Activo';
            statusBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-450 border border-emerald-500/20';
        } else {
            statusBadge.innerText = 'Suspendido';
            statusBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-rose-500/10 text-rose-450 border border-rose-500/20';
        }

        // Progress bar for clients
        const maxClients = trainer.max_clients || 20;
        document.getElementById('detail-max-clients-label').innerText = `Límite: ${maxClients} alumnos`;
        
        // Let's set the bar to 75% for aesthetic display
        const barWidth = 75; 
        document.getElementById('detail-max-clients-bar').style.width = `${barWidth}%`;

        toggleModal('details-trainer-modal');
    }
</script>
@endsection
