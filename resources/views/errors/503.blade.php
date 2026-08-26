<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Sistema en Mantenimiento | GymFlow OS</title>
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
        .ambient-radial-purple {
            background: radial-gradient(circle at 50% 20%, rgba(168, 85, 247, 0.14) 0%, rgba(147, 51, 234, 0.03) 40%, transparent 75%);
        }
        .bg-subtle-grid {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 antialiased bg-slate-950 flex flex-col items-center justify-center p-4 sm:p-6 relative overflow-x-hidden selection:bg-purple-500/30 selection:text-purple-400">

    <!-- Ambient Grid & Glow -->
    <div class="fixed inset-0 bg-subtle-grid pointer-events-none opacity-70"></div>
    <div class="fixed inset-0 ambient-radial-purple pointer-events-none"></div>

    <!-- Centered Card -->
    <div class="w-full max-w-lg relative z-10 space-y-6 animate-fade-in">
        
        <!-- Logo & Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 to-slate-950 border border-purple-500/30 rounded-3xl shadow-2xl shadow-purple-500/10 relative group">
                <div class="absolute inset-0 bg-purple-500/20 blur-xl rounded-full opacity-60 pointer-events-none"></div>
                <div class="p-3 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl text-white flex items-center justify-center relative z-10">
                    <i data-lucide="wrench" class="w-8 h-8 stroke-[2.5]"></i>
                </div>
            </div>
            
            <div>
                <span class="px-3 py-1 bg-purple-500/10 border border-purple-500/25 text-purple-400 text-xs font-black uppercase tracking-widest rounded-full">
                    Error 503 • Maintenance Mode
                </span>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-3">
                    Modo <span class="bg-gradient-to-r from-purple-400 to-indigo-400 bg-clip-text text-transparent">Mantenimiento</span>
                </h1>
                <p class="text-xs text-slate-400 font-medium max-w-sm mx-auto mt-2 leading-relaxed">
                    Estamos realizando tareas de actualización y optimización de infraestructura. La plataforma estará disponible nuevamente en unos minutos.
                </p>
            </div>
        </div>

        <!-- Glassmorphism Card -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-3xl p-6 sm:p-7 shadow-2xl backdrop-blur-xl space-y-5">
            
            <div class="p-4 bg-slate-950/70 border border-slate-850 rounded-2xl flex items-center gap-3.5">
                <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 shrink-0">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0 text-xs text-slate-300 font-medium leading-relaxed">
                    Agradecemos tu paciencia mientras culminamos estas mejoras para garantizar un rendimiento óptimo.
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <button type="button" onclick="window.location.reload()" class="w-full py-3 px-4 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg shadow-lime-500/15 active:scale-95 transition-all flex items-center justify-center gap-2 text-center">
                    <i data-lucide="refresh-cw" class="w-4 h-4 stroke-[3px]"></i>
                    <span>Verificar Disponibilidad</span>
                </button>
            </div>

        </div>

        <!-- Footer Notice -->
        <p class="text-center text-[11px] text-slate-500">
            GymFlow OS • Mantenimiento Programado
        </p>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
