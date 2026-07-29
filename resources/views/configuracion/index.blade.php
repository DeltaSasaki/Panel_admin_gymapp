@extends('layouts.admin')

@section('title', 'Configuración')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-lime-400 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-slate-200">Configuración</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight flex items-center gap-2.5">
                <i data-lucide="settings" class="w-7 h-7 text-lime-400"></i> Configuración del Sistema
            </h1>
            <p class="text-xs text-slate-400 mt-1">Personaliza tu experiencia, preferencias visuales y accesibilidad del panel.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-slate-900 border border-slate-800 rounded-xl text-xs font-semibold text-slate-300 flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-amber-400"></i> {{ $gym->name ?? 'Gimnasio' }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-xs font-bold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Sleek Toast Notification Container -->
    <div id="settings-toast-msg" class="hidden p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-emerald-400 text-xs font-bold flex items-center justify-between shadow-lg animate-fade-in">
        <div class="flex items-center gap-2.5">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
            <span id="settings-toast-text">¡Preferencia de tamaño de letra guardada con éxito!</span>
        </div>
        <button type="button" onclick="document.getElementById('settings-toast-msg').classList.add('hidden')" class="text-emerald-400/60 hover:text-emerald-300 text-xs font-mono font-bold cursor-pointer">✕</button>
    </div>

    <!-- Settings Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">

        <!-- Left Settings Sidebar / Navigation -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-4 space-y-1 lg:sticky lg:top-24">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 py-2">Categorías</span>
            
            <a href="#accesibilidad" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-lime-500/10 text-lime-400 border border-lime-500/20 transition-all">
                <i data-lucide="type" class="w-4 h-4 text-lime-400"></i>
                <span>Accesibilidad y Letra</span>
            </a>

            <a href="#gimnasio" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all">
                <i data-lucide="building-2" class="w-4 h-4 text-slate-500"></i>
                <span>Datos del Gimnasio</span>
            </a>

            <a href="#notificaciones" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all">
                <i data-lucide="bell" class="w-4 h-4 text-slate-500"></i>
                <span>Notificaciones</span>
            </a>

            <a href="#seguridad" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all">
                <i data-lucide="shield" class="w-4 h-4 text-slate-500"></i>
                <span>Seguridad y Sesión</span>
            </a>
        </div>

        <!-- Main Content Settings Options -->
        <div class="lg:col-span-3 space-y-8">

            <!-- Section 1: Font Size & Accessibility -->
            <div id="accesibilidad" class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6">
                <div class="flex items-start justify-between gap-4 border-b border-slate-850 pb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                            <i data-lucide="eye" class="w-5 h-5 text-lime-400"></i> Tamaños de Letra y Accesibilidad
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Ajusta el tamaño del texto de toda la plataforma según tu comodidad visual. Diseñado especialmente para facilitar la lectura o pantallas de alta resolución.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-950 border border-slate-850 text-slate-400 text-xs font-mono font-bold rounded-lg whitespace-nowrap">
                        CSS REM Scaler
                    </span>
                </div>

                <!-- Font Size Selectors Grid -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-3">Selecciona el Tamaño Preferido</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" id="font-size-picker">
                        
                        <!-- Option: Small -->
                        <button type="button" onclick="selectFontSize('small')" id="btn-font-small"
                            class="font-option-btn p-4 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between h-32 border-slate-850 bg-slate-950 hover:border-slate-750">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-extrabold text-slate-200">Pequeño</span>
                                    <span class="text-[10px] font-mono text-slate-500">87.5%</span>
                                </div>
                                <p class="text-[11px] text-slate-400 line-clamp-2">Ideal para pantallas pequeñas o compactar información.</p>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 font-mono">14px Base</span>
                        </button>

                        <!-- Option: Normal (Default) -->
                        <button type="button" onclick="selectFontSize('normal')" id="btn-font-normal"
                            class="font-option-btn p-4 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between h-32 border-slate-850 bg-slate-950 hover:border-slate-750">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-extrabold text-slate-200">Normal</span>
                                    <span class="text-[10px] bg-lime-500/10 text-lime-400 border border-lime-500/20 px-1.5 py-0.5 rounded font-mono font-bold">Por Defecto</span>
                                </div>
                                <p class="text-[11px] text-slate-400 line-clamp-2">Tamaño estándar y equilibrado del panel.</p>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 font-mono">16px Base</span>
                        </button>

                        <!-- Option: Large -->
                        <button type="button" onclick="selectFontSize('large')" id="btn-font-large"
                            class="font-option-btn p-4 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between h-32 border-slate-850 bg-slate-950 hover:border-slate-750">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-extrabold text-slate-200">Grande</span>
                                    <span class="text-[10px] font-mono text-slate-500">112.5%</span>
                                </div>
                                <p class="text-[11px] text-slate-400 line-clamp-2">Texto ampliado para una lectura clara y cómoda.</p>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 font-mono">18px Base</span>
                        </button>

                        <!-- Option: Extra Large -->
                        <button type="button" onclick="selectFontSize('xlarge')" id="btn-font-xlarge"
                            class="font-option-btn p-4 rounded-2xl border text-left transition-all cursor-pointer flex flex-col justify-between h-32 border-slate-850 bg-slate-950 hover:border-slate-750">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-extrabold text-slate-200">Extra Grande</span>
                                    <span class="text-[10px] font-mono text-slate-500">125%</span>
                                </div>
                                <p class="text-[11px] text-slate-400 line-clamp-2">Máxima visibilidad de textos y números.</p>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 font-mono">20px Base</span>
                        </button>

                    </div>
                </div>

                <!-- Previsualizador en Vivo (Live Preview Container) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Previsualizador en Tiempo Real</label>
                        <span class="text-xs text-lime-400 font-semibold flex items-center gap-1">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Cambios visibles instantáneamente
                        </span>
                    </div>

                    <div class="bg-slate-950 p-5 rounded-2xl border border-slate-850 space-y-4 shadow-inner">
                        <div class="flex items-center justify-between border-b border-slate-850/80 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-400 flex items-center justify-center font-bold text-sm">
                                    MS
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-sm text-slate-100">María Inés Silva</h4>
                                    <span class="text-xs text-slate-400">Atleta de Alto Rendimiento • Plan Mensual</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold rounded-full">
                                Activo
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-850/60">
                                <span class="block text-[11px] text-slate-400 font-medium">Asistencia Este Mes</span>
                                <span class="text-lg font-black text-slate-100">18 Días</span>
                            </div>
                            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-850/60">
                                <span class="block text-[11px] text-slate-400 font-medium">Tarifa Diaria</span>
                                <span class="text-lg font-black text-amber-400">$1.67 / día</span>
                            </div>
                            <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-850/60">
                                <span class="block text-[11px] text-slate-400 font-medium">Saldo a Favor</span>
                                <span class="text-lg font-black text-emerald-400">$0.33</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" class="px-3.5 py-2 bg-slate-900 text-xs font-bold text-slate-300 rounded-xl border border-slate-800">
                                Cancelar
                            </button>
                            <button type="button" class="px-3.5 py-2 bg-lime-500 text-slate-950 text-xs font-bold rounded-xl shadow-md">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-850">
                    <button type="button" onclick="selectFontSize('normal')" class="text-xs font-bold text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1.5">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Restablecer a Tamaño Predeterminado
                    </button>

                    <button type="button" onclick="saveFontSizePreference()" class="px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i> Guardar Preferencias
                    </button>
                </div>

            </div>

            <!-- Section 2: Gym General Settings (Placeholder card) -->
            <div id="gimnasio" class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 space-y-4">
                <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-5 h-5 text-amber-400"></i> Información de la Sucursal
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850">
                        <span class="block text-slate-500 font-bold uppercase text-[10px] mb-1">Nombre del Gimnasio</span>
                        <span class="font-extrabold text-slate-100 text-sm">{{ $gym->name ?? 'Gimnasio' }}</span>
                    </div>
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-850">
                        <span class="block text-slate-500 font-bold uppercase text-[10px] mb-1">Plan SaaS Contratado</span>
                        <span class="font-extrabold text-amber-400 text-sm">{{ $gym->plan->name ?? 'Plan Estándar' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<div id="pjax-modals-container">
    <!-- Modal: Cambios Sin Guardar -->
    <div id="unsaved-changes-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm hidden transition-opacity">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-7 w-full max-w-md space-y-5 shadow-2xl relative animate-fade-in">
            
            <div class="flex items-start gap-4">
                <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-amber-400 shrink-0">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-100">Tienes Cambios Sin Guardar</h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        Has seleccionado un nuevo tamaño de letra en la previsualización pero aún no has guardado las preferencias. ¿Deseas aplicar estos cambios antes de salir?
                    </p>
                </div>
            </div>

            <div class="p-3.5 bg-slate-950 rounded-2xl border border-slate-850 flex items-center justify-between text-xs">
                <span class="text-slate-400 font-medium">Tamaño Seleccionado:</span>
                <span id="unsaved-modal-font-name" class="font-extrabold text-lime-400 uppercase">--</span>
            </div>

            <div class="flex flex-col gap-2 pt-1">
                <button type="button" onclick="confirmSaveAndNavigate()" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-lime-500/10 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i> Guardar y Continuar
                </button>
                <button type="button" onclick="confirmDiscardAndNavigate()" class="w-full py-2.5 bg-slate-950 hover:bg-slate-850 text-slate-300 font-bold text-xs rounded-xl border border-slate-850 transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4 text-slate-500"></i> Descartar y Salir
                </button>
                <button type="button" onclick="cancelUnsavedModal()" class="w-full py-2 text-center text-xs text-slate-400 hover:text-slate-200 transition-colors font-medium cursor-pointer">
                    Seguir Editando
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        let committedFont = window.committedFontSize || localStorage.getItem('gym_app_font_size') || 'normal';
        window.currentSelectedFont = committedFont;
        let pendingNavigationUrl = null;

        const FONT_LABELS = {
            small: 'Pequeño (14px)',
            normal: 'Normal (16px)',
            large: 'Grande (18px)',
            xlarge: 'Extra Grande (20px)'
        };

        window.updateFontPickerUI = function(activeKey) {
            window.currentSelectedFont = activeKey;
            const keys = ['small', 'normal', 'large', 'xlarge'];
            
            keys.forEach(k => {
                const btn = document.getElementById(`btn-font-${k}`);
                if (btn) {
                    if (k === activeKey) {
                        btn.classList.remove('border-slate-850', 'bg-slate-950');
                        btn.classList.add('border-lime-500/60', 'bg-lime-500/10', 'ring-2', 'ring-lime-500/20');
                    } else {
                        btn.classList.remove('border-lime-500/60', 'bg-lime-500/10', 'ring-2', 'ring-lime-500/20');
                        btn.classList.add('border-slate-850', 'bg-slate-950');
                    }
                }
            });
        };

        window.selectFontSize = function(key) {
            window.updateFontPickerUI(key);
            if (typeof window.previewAppFontSize === 'function') {
                window.previewAppFontSize(key);
            }
        };

        function hasUnsavedChanges() {
            const committed = window.committedFontSize || localStorage.getItem('gym_app_font_size') || 'normal';
            return window.currentSelectedFont !== committed;
        }

        window.checkUnsavedSettings = function(targetUrl) {
            if (hasUnsavedChanges()) {
                pendingNavigationUrl = targetUrl;
                const fontNameEl = document.getElementById('unsaved-modal-font-name');
                if (fontNameEl) {
                    fontNameEl.textContent = FONT_LABELS[window.currentSelectedFont] || window.currentSelectedFont;
                }
                const modal = document.getElementById('unsaved-changes-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    if (window.lucide) window.lucide.createIcons();
                }
                return true;
            }
            return false;
        };

        window.cancelUnsavedModal = function() {
            pendingNavigationUrl = null;
            const modal = document.getElementById('unsaved-changes-modal');
            if (modal) modal.classList.add('hidden');
        };

        window.confirmDiscardAndNavigate = function() {
            const target = pendingNavigationUrl;
            pendingNavigationUrl = null;
            const modal = document.getElementById('unsaved-changes-modal');
            if (modal) modal.classList.add('hidden');

            if (typeof window.revertAppFontSize === 'function') {
                window.revertAppFontSize();
            }
            window.currentSelectedFont = window.committedFontSize || 'normal';
            window.checkUnsavedSettings = null;

            if (target && typeof window.loadUrl === 'function') {
                window.loadUrl(target, true);
            } else if (target) {
                window.location.href = target;
            }
        };

        window.confirmSaveAndNavigate = function() {
            const target = pendingNavigationUrl;
            window.saveFontSizePreference(function() {
                const modal = document.getElementById('unsaved-changes-modal');
                if (modal) modal.classList.add('hidden');
                window.checkUnsavedSettings = null;

                if (target && typeof window.loadUrl === 'function') {
                    window.loadUrl(target, true);
                } else if (target) {
                    window.location.href = target;
                }
            });
        };

        window.saveFontSizePreference = function(onSuccessCallback = null) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('configuracion.update_font_size') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    font_size: window.currentSelectedFont
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.commitAppFontSize === 'function') {
                        window.commitAppFontSize(window.currentSelectedFont);
                    }
                    
                    const toast = document.getElementById('settings-toast-msg');
                    const toastText = document.getElementById('settings-toast-text');
                    if (toast && toastText) {
                        toastText.textContent = data.message;
                        toast.classList.remove('hidden');
                        setTimeout(() => { toast.classList.add('hidden'); }, 4000);
                    }

                    if (typeof onSuccessCallback === 'function') {
                        onSuccessCallback();
                    }
                }
            })
            .catch(err => {
                console.error(err);
            });
        };

        // Initialize UI with committed font size immediately
        window.updateFontPickerUI(committedFont);
    })();

    window.addEventListener('beforeunload', function() {
        if (typeof window.revertAppFontSize === 'function') {
            window.revertAppFontSize();
        }
    });
</script>
@endsection
