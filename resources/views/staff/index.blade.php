@extends('layouts.admin')

@section('title', 'Gestión de Staff y Entrenadores')

@section('content')
<div class="space-y-8">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-100 tracking-tight">Staff de Entrenadores</h1>
            <p class="text-xs text-slate-400 mt-1">Registra personal de entrenamientos, salarios de nómina y credenciales de acceso.</p>
        </div>
        <div>
            <button onclick="toggleModal('trainer-modal')" class="px-4 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Reclutar Entrenador
            </button>
        </div>
    </div>

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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($trainers as $trainer)
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 flex flex-col justify-between hover:border-slate-700 transition-colors relative overflow-hidden">
                <div class="flex items-start gap-4">
                    <img src="{{ $trainer->photo_url ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" 
                         alt="Avatar" 
                         class="w-16 h-16 rounded-2xl object-cover ring-2 ring-lime-500/20 shrink-0">
                    <div class="space-y-1 overflow-hidden">
                        <div class="flex items-center gap-2">
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

                <!-- Info Grid -->
                <div class="grid grid-cols-3 gap-3 bg-slate-950/40 p-3.5 rounded-xl border border-slate-850/50 my-5 text-[10px]">
                    <div>
                        <span class="block text-slate-500 font-bold uppercase mb-0.5">Sueldo Mensual</span>
                        <span class="font-mono font-bold text-slate-200 text-xs">${{ number_format($trainer->salary, 2) }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 font-bold uppercase mb-0.5">Experiencia</span>
                        <span class="font-bold text-slate-200 text-xs">{{ $trainer->experience_years }} Años</span>
                    </div>
                    <div>
                        <span class="block text-slate-500 font-bold uppercase mb-0.5">Contrato</span>
                        <span class="font-bold text-slate-200 text-xs">{{ \Carbon\Carbon::parse($trainer->hire_date)->format('d/m/y') }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-850/50 pt-4">
                    <span class="text-[10px] text-slate-500 truncate">Contacto: <strong>{{ $trainer->email }}</strong></span>
                    <form action="{{ route('staff.toggle_status', $trainer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-[10px] font-bold rounded-lg border transition-colors {{ $trainer->is_active ? 'bg-rose-500/10 text-rose-400 border-rose-500/20 hover:bg-rose-550/15' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-550/15' }}">
                            {{ $trainer->is_active ? 'Suspender Acceso' : 'Reactivar Acceso' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-550">
                <i data-lucide="users-2" class="w-12 h-12 text-slate-700 mx-auto mb-2"></i>
                No hay entrenadores registrados en la base de datos para este gimnasio.
            </div>
        @endforelse
    </div>
</div>

<!-- ================= MODAL: RECLUTAR ENTRENADOR ================= -->
<div id="trainer-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md mx-4 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="font-bold text-lg text-slate-100">Reclutar Nuevo Entrenador</h3>
            <button onclick="toggleModal('trainer-modal')" class="p-1 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('staff.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombre</label>
                    <input type="text" name="first_name" required placeholder="Ej: Carlos" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Apellido</label>
                    <input type="text" name="last_name" required placeholder="Ej: Ruiz" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" placeholder="+34 600 000 000" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Sueldo Mensual ($)</label>
                    <input type="number" step="0.01" name="salary" required placeholder="2000" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Especialidad</label>
                    <input type="text" name="specialty" placeholder="Ej: HIIT, Musculación" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Años de Exp.</label>
                    <input type="number" name="experience_years" min="0" placeholder="5" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Certificaciones</label>
                <input type="text" name="certification" placeholder="Ej: NSCA-CPT, Kettlebell L2" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
            </div>
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="toggleModal('trainer-modal')" class="flex-1 py-2.5 bg-slate-950 hover:bg-slate-800 text-xs font-bold rounded-xl border border-slate-850 text-slate-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition-all">
                    Registrar Trainer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }
</script>
@endsection
