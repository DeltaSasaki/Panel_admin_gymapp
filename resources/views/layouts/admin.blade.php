<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GymOS') - Panel de Administración</title>

    <!-- Google Fonts: Plus Jakarta Sans for a premium, clean look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite Assets) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons CDN for easy, clean modern icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom scrollbar for premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #090d16;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-[#070a13] text-slate-200 overflow-x-hidden antialiased">

    <!-- Wrapper -->
    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- Mobile Header Bar (Visible on mobile only) -->
        <header class="md:hidden flex items-center justify-between px-6 py-4 bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-lime-500/10 rounded-xl border border-lime-500/30 text-lime-400">
                    <i data-lucide="dumbbell" class="w-6 h-6"></i>
                </div>
                <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-lime-400 to-emerald-400 bg-clip-text text-transparent">GYMFLOW</span>
            </div>
            <button id="mobile-menu-btn" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-lime-500">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </header>

        <!-- Sidebar (Fixed on Desktop, Off-canvas Drawer on Mobile) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-slate-900/90 backdrop-blur-md border-r border-slate-800/80 p-6 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:h-screen">
            
            <div>
                <!-- Brand Logo & Header -->
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-800/60">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-lime-500/10 rounded-xl border border-lime-500/20 text-lime-400 shadow-lg shadow-lime-500/5">
                            <i data-lucide="dumbbell" class="w-6 h-6 animate-pulse"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-lime-400 to-emerald-400 bg-clip-text text-transparent">GYMFLOW</span>
                            <span class="block text-[10px] uppercase font-bold text-slate-500 tracking-wider">Centro de Control</span>
                        </div>
                    </div>
                    <!-- Close button for Mobile Menu -->
                    <button id="close-menu-btn" class="md:hidden p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Coach Badge / Quick Info -->
                <div class="bg-gradient-to-br from-slate-950 to-slate-900/50 border border-slate-800/50 rounded-2xl p-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop" 
                                 alt="Avatar de Coach" 
                                 class="w-11 h-11 rounded-full object-cover border-2 border-lime-500/30">
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-semibold text-sm text-slate-100 truncate">Coach Carlos Ruiz</h4>
                            <p class="text-xs text-lime-400 font-medium">Head Trainer / Dueño</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Options -->
                <nav class="space-y-1.5">
                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest px-3 mb-2">Principal</p>
                    
                    <!-- Dashboard Link -->
                    <a href="{{ url('/dashboard') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-medium transition-all group {{ Request::is('dashboard') || Request::is('/') ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-l-4 border-lime-500 shadow-sm' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Mis Clientes Link -->
                    <a href="{{ url('/clientes') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-medium transition-all group {{ Request::is('clientes*') ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-l-4 border-lime-500 shadow-sm' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                        <i data-lucide="users" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                        <span>Mis Clientes</span>
                    </a>

                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest px-3 mt-6 mb-2">Entrenamiento</p>

                    <!-- Planes de Rutinas Link -->
                    <a href="{{ url('/rutinas') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-medium transition-all group {{ Request::is('rutinas*') ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-l-4 border-lime-500 shadow-sm' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                        <i data-lucide="dumbbell" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                        <span>Planes de Rutinas</span>
                    </a>

                    <!-- Planes de Nutrición Link -->
                    <a href="{{ url('/nutricion') }}" 
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-medium transition-all group {{ Request::is('nutricion*') ? 'bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 border-l-4 border-lime-500 shadow-sm' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-100' }}">
                        <i data-lucide="apple" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                        <span>Planes de Nutrición</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="pt-6 border-t border-slate-800/60 space-y-4">
                <!-- Gym Status Summary -->
                <div class="bg-slate-950/40 rounded-xl p-3 text-xs border border-slate-800/50">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-slate-500 font-medium">Aforo del Gym</span>
                        <span class="text-lime-400 font-bold">42%</span>
                    </div>
                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-lime-500 to-emerald-400 h-full rounded-full" style="width: 42%"></div>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="flex flex-col gap-1">
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-100 hover:bg-slate-800/40 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Configuración
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile menu background overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs hidden md:hidden"></div>

        <!-- Main Workspace (Displaced left-side on desktop to make room for fixed sidebar) -->
        <div class="flex-1 flex flex-col md:pl-72 min-h-screen">
            
            <!-- Top Navbar / Header -->
            <header class="sticky top-0 z-20 bg-[#070a13]/80 backdrop-blur-md border-b border-slate-800/40 px-6 py-4 flex items-center justify-between">
                
                <!-- Quick Search (Desktop) -->
                <div class="hidden sm:block relative w-80">
                    <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" 
                           placeholder="Buscar cliente, rutina, dieta..." 
                           class="w-full pl-10 pr-4 py-2 text-sm bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 focus:ring-1 focus:ring-lime-500/50 transition-all">
                </div>
                <div class="sm:hidden text-sm font-semibold text-slate-400">
                    Panel Entrenador
                </div>

                <!-- Right items: Actions, Notifications, Profile -->
                <div class="flex items-center gap-4">
                    <!-- Quick action button -->
                    <a href="{{ route('clientes.crear') }}" class="hidden sm:flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all">
                        <i data-lucide="plus" class="w-4 h-4 stroke-[3px]"></i>
                        Registrar Cliente
                    </a>

                    <!-- Notifications Dropdown Trigger -->
                    <button class="relative p-2.5 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white rounded-xl border border-slate-850 hover:border-slate-700 transition-colors focus:outline-none">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-slate-900"></span>
                    </button>

                    <!-- Profile quick dropdown (Desktop) -->
                    <div class="flex items-center gap-3 pl-3 border-l border-slate-850">
                        <div class="text-right hidden xl:block">
                            <span class="block text-xs font-semibold text-slate-200">Coach Carlos R.</span>
                            <span class="block text-[10px] text-lime-400">Online</span>
                        </div>
                        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop" 
                             alt="Avatar" 
                             class="w-9 h-9 rounded-xl object-cover ring-2 ring-lime-500/20">
                    </div>
                </div>
            </header>

            <!-- Main Dynamic Content -->
            <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto">
                <!-- Dynamically injected screen content -->
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="mt-auto py-6 px-8 border-t border-slate-900/60 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} GymFlow OS. Creado para entrenadores de élite.</p>
            </footer>

        </div>
    </div>

    <!-- Toggle Sidebar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initializing Lucide icons
            lucide.createIcons();

            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            function toggleMenu() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            }

            if (mobileMenuBtn && sidebar && overlay) {
                mobileMenuBtn.addEventListener('click', toggleMenu);
            }

            if (closeMenuBtn) {
                closeMenuBtn.addEventListener('click', toggleMenu);
            }

            if (overlay) {
                overlay.addEventListener('click', toggleMenu);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
