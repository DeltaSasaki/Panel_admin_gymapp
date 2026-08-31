<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Big World Fitness - Acceso Administrativo</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Subtle ambient light - CSS only, zero lag */
        .ambient-radial {
            background: radial-gradient(circle at 50% 15%, rgba(132, 204, 22, 0.12) 0%, rgba(16, 185, 129, 0.04) 35%, transparent 70%);
        }

        .bg-subtle-grid {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased bg-slate-950 flex flex-col items-center justify-center p-4 sm:p-6 relative overflow-x-hidden selection:bg-lime-500/30 selection:text-lime-400">

    <!-- Ambient Grid & Glow (Ultralight, high performance) -->
    <div class="fixed inset-0 bg-subtle-grid pointer-events-none opacity-70"></div>
    <div class="fixed inset-0 ambient-radial pointer-events-none"></div>

    <!-- Centered Card Container -->
    <div class="w-full max-w-md relative z-10 space-y-6">
        
        <!-- Logo & Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center p-3.5 bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 rounded-3xl shadow-2xl shadow-lime-500/5 relative group">
                <div class="absolute inset-0 bg-lime-500/20 blur-xl rounded-full opacity-50 group-hover:opacity-80 transition-opacity pointer-events-none"></div>
                <div class="p-2.5 bg-gradient-to-br from-lime-500 to-emerald-500 rounded-2xl text-slate-950 flex items-center justify-center relative z-10">
                    <i data-lucide="dumbbell" class="w-7 h-7 stroke-[2.5]"></i>
                </div>
            </div>
            
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Big World <span class="bg-gradient-to-r from-lime-400 to-emerald-400 bg-clip-text text-transparent">Fitness</span>
                </h1>
                <p class="text-xs text-slate-400 font-semibold tracking-wide uppercase mt-1">
                    Panel de Control y Administración
                </p>
            </div>
        </div>

        <!-- Main Authentication Card -->
        <div class="bg-slate-900/75 border border-slate-800/90 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-slate-950 backdrop-blur-xl relative overflow-hidden">
            
            <!-- Top Gradient Line -->
            <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-lime-500/80 to-transparent"></div>

            <div class="mb-6 space-y-1">
                <h2 class="text-lg font-bold text-white tracking-tight">Acceso al Panel</h2>
                <p class="text-xs text-slate-400 font-medium">Ingresa tus credenciales autorizadas de personal.</p>
            </div>

            <!-- Validation Errors Alert -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/25 text-rose-400 rounded-2xl text-xs flex items-start gap-2.5 shadow-lg">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                    <div class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <span class="block font-semibold">{{ $error }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" id="login-form" class="space-y-4" onsubmit="handleLoginSubmit(event)">
                @csrf
                
                <!-- Email Input -->
                <div>
                    <label for="login_email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Correo Electrónico
                    </label>
                    <div class="relative group">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-lime-400 transition-colors pointer-events-none">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" 
                               id="login_email" 
                               name="email" 
                               required 
                               value="{{ old('email') }}" 
                               placeholder="usuario@gimnasio.com" 
                               autocomplete="email"
                               class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500 focus:ring-1 focus:ring-lime-500/30 transition-all font-semibold">
                    </div>
                </div>

                <!-- Password Input with Show/Hide Toggle -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="login_password" class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Contraseña
                        </label>
                        <span class="text-[10px] text-slate-500 font-medium">Clave de acceso</span>
                    </div>
                    <div class="relative group">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-lime-400 transition-colors pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" 
                               id="login_password" 
                               name="password" 
                               required 
                               placeholder="••••••••••••" 
                               autocomplete="current-password"
                               class="w-full pl-10 pr-11 py-2.5 text-sm bg-slate-950 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none focus:border-lime-500 focus:ring-1 focus:ring-lime-500/30 transition-all font-semibold">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                id="toggle-password-btn" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 focus:outline-none transition-colors p-1"
                                title="Mostrar u ocultar contraseña">
                            <i data-lucide="eye" id="toggle-password-icon" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-lime-500 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                        <span class="text-slate-400 font-medium">Mantener sesión activa</span>
                    </label>
                    <span class="text-[10px] text-slate-500 font-semibold flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3 h-3 text-emerald-400"></i> Acceso Seguro
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            id="login-submit-btn"
                            class="w-full py-3 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-extrabold text-sm rounded-xl shadow-lg shadow-lime-500/15 hover:shadow-lime-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2 group">
                        <span id="login-btn-text">Ingresar al Panel</span>
                        <i data-lucide="arrow-right" id="login-btn-icon" class="w-4 h-4 stroke-[2.5] group-hover:translate-x-0.5 transition-transform"></i>
                    </button>
                </div>
            </form>

        </div>

        <!-- Minimalist System Footer -->
        <div class="text-center space-y-1.5">
            <p class="text-[11px] text-slate-500 font-medium flex items-center justify-center gap-1.5">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-600"></i>
                <span>Acceso restringido únicamente para personal autorizado</span>
            </p>
            <p class="text-xs text-slate-400 font-medium">
                <span class="text-slate-300 font-bold">Big World Fitness</span> • Creado por 
                <a href="https://www.corpoasia.net/" target="_blank" rel="noopener noreferrer" class="font-bold text-lime-400 hover:text-lime-300 transition-colors underline decoration-lime-500/40 hover:decoration-lime-300 underline-offset-2">Corpoasia</a>
                &
                <a href="https://prisma-code.vercel.app/" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-400 hover:text-emerald-300 transition-colors underline decoration-emerald-500/40 hover:decoration-emerald-300 underline-offset-2">Prisma Code</a>
            </p>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        // Toggle Password Visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('login_password');
            const toggleIcon = document.getElementById('toggle-password-icon');

            if (!passwordInput || !toggleIcon) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                toggleIcon.setAttribute('data-lucide', 'eye');
            }

            if (window.lucide) {
                lucide.createIcons();
            }
        }

        // Handle Form Submit Loading State
        function handleLoginSubmit(e) {
            const btn = document.getElementById('login-submit-btn');
            const text = document.getElementById('login-btn-text');
            const icon = document.getElementById('login-btn-icon');

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-80', 'cursor-not-allowed');
                if (text) text.textContent = 'Verificando...';
                if (icon) {
                    icon.setAttribute('data-lucide', 'loader-2');
                    icon.classList.add('animate-spin');
                    if (window.lucide) lucide.createIcons();
                }
            }
        }
    </script>
</body>
</html>
