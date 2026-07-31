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
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">DNI / Cédula *</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="input_dni" name="dni" required value="{{ old('dni') }}" placeholder="12345678" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50">
                            <button type="button" id="btn_cne" onclick="consultarCNE()" class="px-4 py-2.5 bg-lime-500 hover:bg-lime-400 active:scale-95 text-slate-950 font-bold text-xs uppercase rounded-xl transition flex items-center justify-center gap-1.5 shrink-0 shadow-lg shadow-lime-500/10 cursor-pointer">
                                <span id="cne_btn_icon">
                                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <span id="cne_btn_text">CNE</span>
                            </button>
                        </div>
                        <p id="cne_status" class="text-[11px] mt-1.5 hidden font-medium"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nombres *</label>
                        <input type="text" id="input_first_name" name="first_name" required value="{{ old('first_name') }}" placeholder="María Inés" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Apellidos *</label>
                        <input type="text" id="input_last_name" name="last_name" required value="{{ old('last_name') }}" placeholder="Silva" class="w-full px-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500/50 transition-all">
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

<script>
function consultarCNE() {
    const dniInput = document.getElementById('input_dni');
    const firstNameInput = document.getElementById('input_first_name');
    const lastNameInput = document.getElementById('input_last_name');
    const btnCne = document.getElementById('btn_cne');
    const btnIcon = document.getElementById('cne_btn_icon');
    const btnText = document.getElementById('cne_btn_text');
    const statusMsg = document.getElementById('cne_status');

    const dniValue = dniInput.value.trim();
    if (!dniValue) {
        statusMsg.textContent = '⚠️ Ingresa una cédula o DNI antes de consultar.';
        statusMsg.className = 'text-[11px] mt-1.5 font-medium text-amber-400 block';
        dniInput.focus();
        return;
    }

    // Set loading state
    btnCne.disabled = true;
    btnCne.classList.add('opacity-75', 'cursor-not-allowed');
    btnText.textContent = 'Consultando...';
    btnIcon.innerHTML = `<svg class="animate-spin w-4 h-4 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
    statusMsg.className = 'text-[11px] mt-1.5 font-medium text-slate-400 block';
    statusMsg.textContent = 'Buscando datos en CNE...';

    fetch(`{{ route('api.consultar_cne') }}?dni=${encodeURIComponent(dniValue)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                firstNameInput.value = data.first_name || '';
                lastNameInput.value = data.last_name || '';
                
                // Highlight inputs temporarily
                firstNameInput.classList.add('ring-2', 'ring-lime-500');
                lastNameInput.classList.add('ring-2', 'ring-lime-500');
                setTimeout(() => {
                    firstNameInput.classList.remove('ring-2', 'ring-lime-500');
                    lastNameInput.classList.remove('ring-2', 'ring-lime-500');
                }, 2000);

                statusMsg.textContent = '✓ Datos encontrados y autocompletados con éxito.';
                statusMsg.className = 'text-[11px] mt-1.5 font-medium text-lime-400 block';
            } else {
                statusMsg.textContent = `❌ ${data.message || 'No se pudieron obtener los datos.'}`;
                statusMsg.className = 'text-[11px] mt-1.5 font-medium text-rose-400 block';
            }
        })
        .catch(err => {
            console.error('Error CNE:', err);
            statusMsg.textContent = '❌ Ocurrió un error al conectar con la API de CNE.';
            statusMsg.className = 'text-[11px] mt-1.5 font-medium text-rose-400 block';
        })
        .finally(() => {
            btnCne.disabled = false;
            btnCne.classList.remove('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'CNE';
            btnIcon.innerHTML = `<svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>`;
        });
}
</script>
@endsection
