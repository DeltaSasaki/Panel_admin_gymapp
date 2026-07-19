@extends('layouts.admin')

@section('title', 'Registrar Nuevo Cliente')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Quick navigation -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="{{ route('clientes.index') }}" class="hover:text-lime-400 transition-colors">Mis Clientes</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        <span class="text-slate-200">Registrar Cliente</span>
    </div>

    <!-- Main Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 md:p-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Registrar Nuevo Cliente</h1>
            <p class="text-slate-400 text-xs mt-1">Crea el usuario del atleta y guarda sus medidas antropométricas iniciales.</p>
        </div>

        @if ($errors->any())
            <div class="mt-4 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-xs space-y-1">
                <span class="font-bold">Por favor corrige los siguientes errores:</span>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clientes.store') }}" method="POST" class="mt-8 space-y-6">
            @csrf

            <!-- Section 1: Account Information -->
            <div class="space-y-4">
                <h3 class="text-xs uppercase font-extrabold tracking-wider text-slate-500 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> 1. Información de la Cuenta
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Correo Electrónico *</label>
                        <input type="email" name="email" required value="{{ old('email') }}" placeholder="ejemplo@correo.com" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Contraseña de Acceso *</label>
                        <input type="password" name="password" required placeholder="Mínimo 6 caracteres" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>
            </div>

            <!-- Section 2: Personal Profile -->
            <div class="space-y-4 pt-4 border-t border-slate-850/60">
                <h3 class="text-xs uppercase font-extrabold tracking-wider text-slate-500 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> 2. Perfil Personal
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombres *</label>
                        <input type="text" name="first_name" required value="{{ old('first_name') }}" placeholder="María Inés" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Apellidos *</label>
                        <input type="text" name="last_name" required value="{{ old('last_name') }}" placeholder="Silva" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">DNI *</label>
                        <input type="text" name="dni" required value="{{ old('dni') }}" placeholder="12345678" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+34 600 000 000" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Fecha de Nacimiento</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Género *</label>
                        <select name="gender" required class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 focus:outline-none focus:border-lime-500/50">
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Masculino</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Femenino</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Foto de Perfil (URL)</label>
                        <input type="url" name="profile_photo" value="{{ old('profile_photo') }}" placeholder="https://unsplash.com/..." class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                    </div>
                </div>
            </div>



            <!-- Submit Section -->
            <div class="pt-6 border-t border-slate-850/60 flex items-center justify-end gap-3">
                <a href="{{ route('clientes.index') }}" class="px-5 py-2.5 bg-slate-950 hover:bg-slate-850 border border-slate-850 text-slate-400 hover:text-slate-200 text-xs font-bold rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">
                    Guardar y Registrar Atleta
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
