<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - GymFlow OS</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind Asset -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 font-sans min-h-screen text-slate-100 flex flex-col items-center justify-center p-4 antialiased selection:bg-lime-500/30 selection:text-lime-400">

    <!-- Top Glow Effect -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-lime-500/5 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="w-full max-w-md space-y-6 relative z-10">
        
        <!-- Logo Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center p-3.5 bg-gradient-to-r from-lime-500/10 to-emerald-500/10 border border-lime-500/20 rounded-2xl shadow-xl shadow-lime-500/5">
                <i data-lucide="dumbbell" class="w-8 h-8 text-lime-400"></i>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white mt-4">GymFlow <span class="bg-gradient-to-r from-lime-400 to-emerald-400 bg-clip-text text-transparent">OS</span></h1>
            <p class="text-xs text-slate-400">Panel de Administración Multi-Gimnasio</p>
        </div>

        <!-- Main Card -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 md:p-8 backdrop-blur-md shadow-2xl">
            <h2 class="text-lg font-bold text-white mb-6">Ingresar al Sistema</h2>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-xs flex gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <span class="block">{{ $error }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Correo Electrónico</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                        <input type="email" name="email" required value="{{ old('email') }}" placeholder="coach@gymflow.com" class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-650 focus:outline-none focus:border-lime-500/50 transition-colors">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-bold uppercase text-slate-400">Contraseña</label>
                        <a href="#" class="text-[10px] font-bold text-lime-400 hover:text-lime-300">¿La olvidaste?</a>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"></i>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-950 border border-slate-850 rounded-xl text-slate-100 placeholder-slate-700 focus:outline-none focus:border-lime-500/50 transition-colors">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-sm rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>

        <!-- Testing Credentials Card -->
        <div class="bg-slate-900/40 border border-slate-850 rounded-3xl p-5 text-xs space-y-4 shadow-xl">
            <span class="block font-bold text-slate-300 uppercase text-[10px] tracking-wider flex items-center gap-1.5 border-b border-slate-800 pb-2">
                <i data-lucide="key-round" class="w-4 h-4 text-lime-400"></i> Cuentas de Prueba Multi-Gimnasio
            </span>
            <div class="space-y-4">
                <!-- Gym 1 -->
                <div>
                    <span class="block font-extrabold text-lime-400 text-[10px] uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i data-lucide="building" class="w-3.5 h-3.5"></i> GIMNASIO 1: GymFlow HQ
                    </span>
                    <div class="grid grid-cols-1 gap-2 pl-2">
                        <div class="flex justify-between items-center bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-850/40">
                            <span class="text-slate-500 font-medium">Entrenador (Trainer):</span>
                            <span class="text-slate-300 font-mono select-all">coach@gymflow.com</span>
                        </div>
                        <div class="flex justify-between items-center bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-850/40">
                            <span class="text-slate-500 font-medium">Dueño (Admin):</span>
                            <span class="text-slate-300 font-mono select-all">admin@gymflow.com</span>
                        </div>
                        <div class="flex justify-between items-center bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-850/40">
                            <span class="text-slate-500 font-medium">Soporte (Superadmin):</span>
                            <span class="text-slate-300 font-mono select-all">support@gymflow.com</span>
                        </div>
                    </div>
                </div>

                <!-- Gym 2 -->
                <div class="pt-2 border-t border-slate-850/50">
                    <span class="block font-extrabold text-emerald-400 text-[10px] uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i data-lucide="building" class="w-3.5 h-3.5"></i> GIMNASIO 2: PowerHouse Studio
                    </span>
                    <div class="grid grid-cols-1 gap-2 pl-2">
                        <div class="flex justify-between items-center bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-850/40">
                            <span class="text-slate-500 font-medium">Entrenador (Trainer):</span>
                            <span class="text-slate-300 font-mono select-all">coach2@powerhouse.com</span>
                        </div>
                        <div class="flex justify-between items-center bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-850/40">
                            <span class="text-slate-500 font-medium">Dueño (Admin):</span>
                            <span class="text-slate-300 font-mono select-all">admin2@powerhouse.com</span>
                        </div>
                        <div class="flex justify-between items-center bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-850/40">
                            <span class="text-slate-500 font-medium">Soporte (Superadmin):</span>
                            <span class="text-slate-300 font-mono select-all">support2@powerhouse.com</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="text-[10px] text-slate-500 text-center pt-2">
                Clave común para todas las cuentas: <strong class="text-slate-300">password</strong>
            </p>
        </div>

    </div>

    <script>
        // Initialize lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
