@php
    $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
    $activeGymLogo = null;
    if ($activeGymId === 'all') {
        $activeGymName = 'Todas las Sucursales';
    } else {
        if ($activeGymId == auth()->user()->gym_id) {
            $activeGymName = auth()->user()->gym->name;
            $activeGymLogo = auth()->user()->gym->logo_url;
        } else {
            $gymRecord = \App\Models\Gym::where('id', $activeGymId)->first(['name', 'logo_url']);
            $activeGymName = $gymRecord->name ?? 'Vista General';
            $activeGymLogo = $gymRecord->logo_url ?? null;
        }
    }

    // Calculate real Aforo / Gym Capacity based on SaaS Plan max_users
    if ($activeGymId === 'all') {
        $aforoCurrentUsers = \App\Models\User::where('role', 'member')->count();
        $allGymsList = \App\Models\Gym::with('plan')->get();
        $aforoMaxUsers = 0;
        foreach ($allGymsList as $g) {
            $aforoMaxUsers += ($g->plan?->max_users ?? 50);
        }
    } else {
        $aforoCurrentUsers = \App\Models\User::where('gym_id', $activeGymId)->where('role', 'member')->count();
        $selectedGymForAforo = \App\Models\Gym::with('plan')->find($activeGymId);
        $aforoMaxUsers = $selectedGymForAforo ? ($selectedGymForAforo->plan?->max_users ?? 50) : 50;
    }

    $aforoPercentage = $aforoMaxUsers > 0 ? round(($aforoCurrentUsers / $aforoMaxUsers) * 100, 1) : 0;
    $aforoPctFormatted = (floor($aforoPercentage) == $aforoPercentage) ? (int)$aforoPercentage : $aforoPercentage;
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GymOS') - Panel de Administración</title>

    <!-- Theme initialization to prevent flash -->
    <script>
        if (localStorage.getItem('theme') === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.add('light');
        } else {
            document.documentElement.classList.remove('light');
        }
    </script>

    <!-- Google Fonts: Plus Jakarta Sans for a premium, clean look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite Assets) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons CDN for easy, clean modern icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js CDN for interactive high-performance charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Theme overrides for light mode */
        :root {
            --bg-body: #070a13;
        }
        
        html.light {
            --bg-body: #f8fafc;
            
            /* Override Tailwind CSS v4 variables */
            --color-slate-950: #f1f5f9;
            --color-slate-900: #ffffff;
            --color-slate-850: #e2e8f0;
            --color-slate-800: #cbd5e1;
            --color-slate-700: #94a3b8;
            --color-slate-600: #94a3b8;
            --color-slate-500: #64748b;
            
            --color-slate-400: #475569;
            --color-slate-300: #334155;
            --color-slate-200: #1e293b;
            --color-slate-100: #0f172a;

        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body) !important;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Scoped transitions for theme toggle & interactive elements */
        body, button, a, input, select, textarea, .sidebar-link, .sidebar-group-box {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        /* Custom Scrollbars for Premium Aesthetic */
        ::-webkit-scrollbar {
            width: 6px !important;
            height: 6px !important;
        }
        ::-webkit-scrollbar-track {
            background: rgba(7, 10, 19, 0.7) !important;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b !important;
            border-radius: 9999px !important;
            border: 1px solid rgba(51, 65, 85, 0.4) !important;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(163, 230, 53, 0.5) !important;
            border-color: rgba(163, 230, 53, 0.3) !important;
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: #1e293b rgba(7, 10, 19, 0.7);
        }

        /* Optimized Micro-Animations for Low-Spec Hardware */
        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInSlide 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .card-hover-effect {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover-effect:hover {
            transform: translateY(-2px);
        }

        /* Fluid Accordion Animation for Navigation Groups */
        .sidebar-accordion-wrapper {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease-out;
            overflow: hidden;
        }
        .sidebar-accordion-wrapper.open {
            grid-template-rows: 1fr;
            opacity: 1;
        }
        .sidebar-accordion-inner {
            min-height: 0;
        }

        /* Smooth Chevron Rotation */
        .sidebar-chevron {
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        /* Sidebar Link Hover & Active States */
        .sidebar-link {
            transition: background-color 0.2s cubic-bezier(0.16, 1, 0.3, 1), 
                        color 0.2s cubic-bezier(0.16, 1, 0.3, 1), 
                        transform 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar-link:hover:not(.active-nav-link) {
            transform: translateX(4px);
        }
        .sidebar-group-box {
            transition: background-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Fluid Dropdown Animation for Notifications & Profiles */
        .dropdown-animate {
            opacity: 0;
            transform: translateY(-8px) scale(0.96);
            pointer-events: none;
            transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .dropdown-animate.open {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-950 text-slate-200 overflow-x-hidden antialiased">

    <!-- Wrapper -->
    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- Mobile Header Bar (Visible on mobile only) -->
        <header class="md:hidden flex items-center justify-between px-6 py-4 bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
            <div class="flex items-center gap-3">
                @if($activeGymLogo && file_exists(public_path($activeGymLogo)))
                    <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 border border-slate-800 shadow-md">
                        <img src="{{ asset($activeGymLogo) }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="p-2 bg-lime-500/10 rounded-xl border border-lime-500/30 text-lime-400">
                        <i data-lucide="dumbbell" class="w-6 h-6"></i>
                    </div>
                @endif
                <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-lime-400 to-emerald-400 bg-clip-text text-transparent">GYMFLOW</span>
            </div>
            <button id="mobile-menu-btn" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-slate-100 focus:outline-none focus:ring-2 focus:ring-lime-500">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </header>
        <!-- Sidebar (Fixed on Desktop, Off-canvas Drawer on Mobile) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-slate-900/90 backdrop-blur-md border-r border-slate-800/80 p-6 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out h-screen overflow-hidden">
            
            <!-- Brand Logo & Header (Pinned at Top) -->
            <div class="flex items-center justify-between pb-5 border-b border-slate-800/50 shrink-0">
                <div class="flex items-center gap-3">
                    @if($activeGymLogo && file_exists(public_path($activeGymLogo)))
                        <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 border border-slate-800 shadow-md">
                            <img src="{{ asset($activeGymLogo) }}" alt="Logo" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="p-2 bg-gradient-to-br from-lime-500/20 to-emerald-500/10 rounded-xl border border-lime-500/30 text-lime-400 shadow-lg shadow-lime-500/10">
                            <i data-lucide="dumbbell" class="w-6 h-6 animate-pulse"></i>
                        </div>
                    @endif
                    <div>
                        <span class="font-black text-xl tracking-tight bg-gradient-to-r from-lime-400 via-lime-500 to-emerald-400 bg-clip-text text-transparent">GYMFLOW</span>
                        <span class="block text-[9px] uppercase font-bold text-slate-400 tracking-wider truncate max-w-[170px] mt-0.5" title="{{ $activeGymName }}">
                            {{ $activeGymName }}
                        </span>
                    </div>
                </div>
                <!-- Close button for Mobile Menu -->
                <button id="close-menu-btn" class="md:hidden p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Scrollable Content Area (Flex-1 scroll) -->
            <div class="flex-1 overflow-y-auto my-6 pr-1 space-y-5 scrollbar-thin">
                <!-- Coach Badge / Quick Info -->
                <div class="bg-slate-900/30 border border-slate-800/70 rounded-2xl p-3.5 relative overflow-hidden group/coach">
                    <div class="absolute inset-0 bg-gradient-to-r from-lime-500/[0.02] to-emerald-500/[0.02] opacity-0 group-hover/coach:opacity-100 transition-opacity duration-300"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="relative shrink-0">
                            <img src="{{ auth()->user()->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" 
                                 alt="Avatar de Coach" 
                                 class="w-10 h-10 rounded-full object-cover border-2 border-lime-500/35 shadow-md shadow-lime-500/10">
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-slate-900 rounded-full animate-pulse"></span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-xs text-slate-100 truncate tracking-wide">Coach {{ auth()->user()->profile->first_name }}</h4>
                            <p class="text-[10px] text-lime-400 font-semibold truncate uppercase tracking-widest mt-0.5">{{ auth()->user()->gym->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Options -->
                <nav class="space-y-4">
                    @php
                        $isPrincipalActive = Request::is('dashboard') || Request::is('/') || Request::is('clientes*') || Request::is('asistencia*');
                        $isCajaActive = Request::is('tienda*') || Request::is('finanzas*');
                        $isEntrenamientoActive = Request::is('rutinas*') || Request::is('nutricion*') || Request::is('ingredientes*') || Request::is('recetas*') || Request::is('ejercicios*') || Request::is('equipamiento*') || Request::is('clases*') || Request::is('retos*');
                        $isSaaSActive = Request::is('staff*');
                        $isSuperadminActive = Request::is('superadmin*');
                    @endphp

                    <!-- Group 1: Resumen General (Recuadro Box) -->
                    <div class="sidebar-group-box rounded-2xl border p-2.5 transition-all duration-300 {{ $isPrincipalActive ? 'bg-slate-900/60 border-slate-800/90 shadow-md shadow-lime-500/[0.01]' : 'bg-slate-950/20 border-slate-900/60 hover:border-slate-800/60 hover:bg-slate-900/30' }}">
                        <button onclick="toggleSidebarGroup('group-principal')" class="w-full flex items-center justify-between text-[11px] uppercase font-bold text-slate-300 hover:text-slate-100 px-1 py-0.5 transition-colors focus:outline-none cursor-pointer group/header">
                            <span class="flex items-center gap-2.5">
                                <div class="p-1.5 {{ $isPrincipalActive ? 'bg-lime-500/10 text-lime-400 border border-lime-500/20' : 'bg-slate-900 text-slate-500 border border-slate-850 group-hover/header:text-slate-300' }} rounded-lg transition-all duration-200">
                                    <i data-lucide="layout" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="tracking-wider">General</span>
                            </span>
                            <div class="p-1 rounded-lg hover:bg-slate-800/50">
                                <i data-lucide="chevron-down" id="chevron-group-principal" class="sidebar-chevron w-3.5 h-3.5 text-slate-500 {{ $isPrincipalActive ? '' : '-rotate-90' }}"></i>
                            </div>
                        </button>
                        <div id="group-principal" class="sidebar-accordion-wrapper {{ $isPrincipalActive ? 'open' : '' }}">
                            <div class="sidebar-accordion-inner pl-3 border-l border-slate-800/60 space-y-1 mt-2.5">
                                <a href="{{ url('/dashboard') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('dashboard') || Request::is('/') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ url('/clientes') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('clientes*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="users" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Mis Clientes</span>
                                </a>
                                <a href="{{ url('/asistencia') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('asistencia*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="calendar-check" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Control Asistencia</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Group 2: Ventas y Finanzas (Recuadro Box) -->
                    <div class="sidebar-group-box rounded-2xl border p-2.5 transition-all duration-300 {{ $isCajaActive ? 'bg-slate-900/60 border-slate-800/90 shadow-md shadow-lime-500/[0.01]' : 'bg-slate-950/20 border-slate-900/60 hover:border-slate-800/60 hover:bg-slate-900/30' }}">
                        <button onclick="toggleSidebarGroup('group-caja')" class="w-full flex items-center justify-between text-[11px] uppercase font-bold text-slate-300 hover:text-slate-100 px-1 py-0.5 transition-colors focus:outline-none cursor-pointer group/header">
                            <span class="flex items-center gap-2.5">
                                <div class="p-1.5 {{ $isCajaActive ? 'bg-lime-500/10 text-lime-400 border border-lime-500/20' : 'bg-slate-900 text-slate-500 border border-slate-850 group-hover/header:text-slate-300' }} rounded-lg transition-all duration-200">
                                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="tracking-wider">Ventas & Caja</span>
                            </span>
                            <div class="p-1 rounded-lg hover:bg-slate-800/50">
                                <i data-lucide="chevron-down" id="chevron-group-caja" class="sidebar-chevron w-3.5 h-3.5 text-slate-500 {{ $isCajaActive ? '' : '-rotate-90' }}"></i>
                            </div>
                        </button>
                        <div id="group-caja" class="sidebar-accordion-wrapper {{ $isCajaActive ? 'open' : '' }}">
                            <div class="sidebar-accordion-inner pl-3 border-l border-slate-800/60 space-y-1 mt-2.5">
                                <a href="{{ url('/tienda/pos') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/pos') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="shopping-cart" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Venta Nueva (POS)</span>
                                </a>
                                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                                    <a href="{{ url('/tienda/productos') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/productos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="package" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Inventario Tienda</span>
                                    </a>
                                    <a href="{{ url('/tienda/movimientos') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/movimientos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="activity" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Auditoría Stock</span>
                                    </a>
                                    <a href="{{ url('/tienda/ventas') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/ventas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="receipt" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Historial Ventas</span>
                                    </a>
                                    <a href="{{ url('/finanzas') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('finanzas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Finanzas & Pagos</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Group 3: Entrenamiento & Nutrición (Recuadro Box) -->
                    <div class="sidebar-group-box rounded-2xl border p-2.5 transition-all duration-300 {{ $isEntrenamientoActive ? 'bg-slate-900/60 border-slate-800/90 shadow-md shadow-lime-500/[0.01]' : 'bg-slate-950/20 border-slate-900/60 hover:border-slate-800/60 hover:bg-slate-900/30' }}">
                        <button onclick="toggleSidebarGroup('group-entrenamiento')" class="w-full flex items-center justify-between text-[11px] uppercase font-bold text-slate-300 hover:text-slate-100 px-1 py-0.5 transition-colors focus:outline-none cursor-pointer group/header">
                            <span class="flex items-center gap-2.5">
                                <div class="p-1.5 {{ $isEntrenamientoActive ? 'bg-lime-500/10 text-lime-400 border border-lime-500/20' : 'bg-slate-900 text-slate-500 border border-slate-850 group-hover/header:text-slate-300' }} rounded-lg transition-all duration-200">
                                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="tracking-wider">Programas & Catálogos</span>
                            </span>
                            <div class="p-1 rounded-lg hover:bg-slate-800/50">
                                <i data-lucide="chevron-down" id="chevron-group-entrenamiento" class="sidebar-chevron w-3.5 h-3.5 text-slate-500 {{ $isEntrenamientoActive ? '' : '-rotate-90' }}"></i>
                            </div>
                        </button>
                        <div id="group-entrenamiento" class="sidebar-accordion-wrapper {{ $isEntrenamientoActive ? 'open' : '' }}">
                            <div class="sidebar-accordion-inner pl-3 border-l border-slate-800/60 space-y-1 mt-2.5">
                                <a href="{{ url('/rutinas') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('rutinas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="dumbbell" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Planes de Rutinas</span>
                                </a>
                                <a href="{{ url('/nutricion') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('nutricion*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="apple" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Planes de Nutrición</span>
                                </a>
                                <a href="{{ url('/ingredientes') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('ingredientes*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="banana" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Ingredientes & Macros</span>
                                </a>
                                <a href="{{ url('/recetas') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('recetas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="utensils" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Recetario & Platos</span>
                                </a>
                                <a href="{{ url('/ejercicios') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('ejercicios*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="book-open" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Ejercicios & Biblioteca</span>
                                </a>
                                <a href="{{ url('/equipamiento') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('equipamiento*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="wrench" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Equipamiento Gym</span>
                                </a>
                                <a href="{{ url('/clases') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('clases*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="users-2" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Clases Grupales</span>
                                </a>
                                <a href="{{ url('/retos') }}" 
                                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('retos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                    <i data-lucide="trophy" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                    <span>Retos & Incentivos</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Group 4: Configuración & Administración (Recuadro Box) -->
                    @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                        <div class="sidebar-group-box rounded-2xl border p-2.5 transition-all duration-300 {{ $isSaaSActive ? 'bg-slate-900/60 border-slate-800/90 shadow-md shadow-lime-500/[0.01]' : 'bg-slate-950/20 border-slate-900/60 hover:border-slate-800/60 hover:bg-slate-900/30' }}">
                            <button onclick="toggleSidebarGroup('group-saas')" class="w-full flex items-center justify-between text-[11px] uppercase font-bold text-slate-300 hover:text-slate-100 px-1 py-0.5 transition-colors focus:outline-none cursor-pointer group/header">
                                <span class="flex items-center gap-2.5">
                                    <div class="p-1.5 {{ $isSaaSActive ? 'bg-lime-500/10 text-lime-400 border border-lime-500/20' : 'bg-slate-900 text-slate-500 border border-slate-850 group-hover/header:text-slate-300' }} rounded-lg transition-all duration-200">
                                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="tracking-wider">Administración</span>
                                </span>

                                <div class="p-1 rounded-lg hover:bg-slate-800/50">
                                    <i data-lucide="chevron-down" id="chevron-group-saas" class="sidebar-chevron w-3.5 h-3.5 text-slate-500 {{ $isSaaSActive ? '' : '-rotate-90' }}"></i>
                                </div>
                            </button>
                            <div id="group-saas" class="sidebar-accordion-wrapper {{ $isSaaSActive ? 'open' : '' }}">
                                <div class="sidebar-accordion-inner pl-3 border-l border-slate-800/60 space-y-1 mt-2.5">
                                    <a href="{{ url('/staff') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('staff*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="users-2" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Entrenadores (Staff)</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Group 5: Control SaaS Global (Recuadro Box) -->
                    @if(auth()->user()->role === 'superadmin')
                        <div class="sidebar-group-box rounded-2xl border p-2.5 transition-all duration-300 {{ $isSuperadminActive ? 'bg-slate-900/60 border-slate-800/90 shadow-md shadow-lime-500/[0.01]' : 'bg-slate-950/20 border-slate-900/60 hover:border-slate-800/60 hover:bg-slate-900/30' }}">
                            <button onclick="toggleSidebarGroup('group-superadmin')" class="w-full flex items-center justify-between text-[11px] uppercase font-bold text-slate-300 hover:text-slate-100 px-1 py-0.5 transition-colors focus:outline-none cursor-pointer group/header">
                                <span class="flex items-center gap-2.5">
                                    <div class="p-1.5 {{ $isSuperadminActive ? 'bg-lime-500/10 text-lime-400 border border-lime-500/20' : 'bg-slate-900 text-slate-500 border border-slate-850 group-hover/header:text-slate-300' }} rounded-lg transition-all duration-200">
                                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="tracking-wider">Superadmin</span>
                                </span>

                                <div class="p-1 rounded-lg hover:bg-slate-800/50">
                                    <i data-lucide="chevron-down" id="chevron-group-superadmin" class="sidebar-chevron w-3.5 h-3.5 text-slate-500 {{ $isSuperadminActive ? '' : '-rotate-90' }}"></i>
                                </div>
                            </button>
                            <div id="group-superadmin" class="sidebar-accordion-wrapper {{ $isSuperadminActive ? 'open' : '' }}">
                                <div class="sidebar-accordion-inner pl-3 border-l border-slate-800/60 space-y-1 mt-2.5">
                                    <a href="{{ url('/superadmin/gyms') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('superadmin/gyms*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="globe" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Gestionar Sucursales</span>
                                    </a>
                                    <a href="{{ url('/superadmin/planes') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('superadmin/planes*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Planes de Suscripción</span>
                                    </a>
                                    <a href="{{ url('/superadmin/auditoria') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('superadmin/auditoria*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Auditoría & Bitácora</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <!-- Sidebar Footer (Pinned at Bottom) -->
            <div class="pt-6 border-t border-slate-800/60 space-y-4 shrink-0">
                <!-- Gym Status Summary (Dynamic Capacity - Responsive & AJAX Enabled) -->
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                    @php
                        $barGradient = 'from-lime-500 to-emerald-400';
                        $textColor = 'text-lime-400';
                        $badgeBg = 'bg-lime-500/10';
                        $badgeBorder = 'border-lime-500/20';
                        if ($aforoPercentage >= 90) {
                            $barGradient = 'from-rose-500 to-red-500';
                            $textColor = 'text-rose-400';
                            $badgeBg = 'bg-rose-500/10';
                            $badgeBorder = 'border-rose-500/20';
                        } elseif ($aforoPercentage >= 75) {
                            $barGradient = 'from-amber-500 to-yellow-400';
                            $textColor = 'text-amber-400';
                            $badgeBg = 'bg-amber-500/10';
                            $badgeBorder = 'border-amber-500/20';
                        }
                    @endphp
                    <div class="bg-slate-950/50 rounded-xl p-3 text-xs border border-slate-800/60 shadow-sm transition-all duration-300">
                        <div class="flex items-center justify-between gap-2 mb-2 flex-wrap sm:flex-nowrap">
                            <span class="text-slate-400 font-medium text-[11px] flex items-center gap-1.5">
                                <i data-lucide="gauge" class="w-3.5 h-3.5 text-slate-500"></i> Aforo del Gym
                            </span>
                            <span class="flex items-center gap-1.5 ml-auto">
                                <span class="aforo-count-val text-slate-300 font-extrabold text-[11px] whitespace-nowrap tracking-tight">{{ $aforoCurrentUsers }}/{{ $aforoMaxUsers }}</span>
                                <span class="aforo-pct-badge-val {{ $badgeBg }} {{ $textColor }} px-1.5 py-0.5 rounded-md text-[10px] font-black tracking-wide border {{ $badgeBorder }} whitespace-nowrap">
                                    {{ $aforoPctFormatted }}%
                                </span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-900 h-1.5 rounded-full overflow-hidden border border-slate-850">
                            <div id="aforo-bar" class="aforo-bar-fill bg-gradient-to-r {{ $barGradient }} h-full rounded-full transition-all duration-700 ease-out" style="width: {{ min(100, max(2, $aforoPercentage)) }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- Action Links -->
                <div class="flex flex-col gap-1">
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-100 hover:bg-slate-800/40 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        <span>Configuración</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile menu background overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs hidden md:hidden"></div>

        <!-- Main Workspace (Displaced left-side on desktop to make room for fixed sidebar) -->
        <div class="flex-1 flex flex-col md:pl-72 min-h-screen">
            
            <!-- Top Navbar / Header -->
            <header class="sticky top-0 z-20 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/40 px-6 py-4 flex items-center justify-between">
                
                <!-- Quick Search & Gym Switcher for Superadmin -->
                <div class="hidden sm:flex items-center gap-4">
                    <form action="{{ route('global.search') }}" method="GET" class="relative w-80 m-0" id="global-search-form">
                        <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" 
                               name="q" 
                               id="global-search-input"
                               autocomplete="off"
                               value="{{ request('q') }}"
                               placeholder="Buscar cliente, rutina, dieta..." 
                               class="w-full pl-10 pr-4 py-2 text-sm bg-slate-900 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 focus:ring-1 focus:ring-lime-500/50 transition-all">
                        
                        <!-- Live Autocomplete Dropdown -->
                        <div id="live-search-results" class="absolute top-full left-0 right-0 mt-2 bg-slate-900/95 backdrop-blur-md border border-slate-800 rounded-2xl shadow-2xl z-50 overflow-hidden hidden max-h-80 overflow-y-auto">
                            <!-- JS will inject results here -->
                        </div>
                    </form>

                    @if(auth()->user()->role === 'superadmin')
                        @php
                            $allGyms = \App\Models\Gym::orderBy('name')->get();
                            $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                        @endphp
                        <div class="flex items-center gap-2">
                            <label for="gym_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sucursal:</label>
                            <select name="gym_id" id="gym_id" onchange="switchGymContext(this.value)" class="text-xs bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-lime-400 font-bold focus:outline-none focus:border-lime-500 transition-all cursor-pointer">
                                <option value="all" {{ $activeGymId === 'all' ? 'selected' : '' }}>Todas las Sucursales</option>
                                @foreach($allGyms as $g)
                                    <option value="{{ $g->id }}" {{ $activeGymId == $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="sm:hidden text-sm font-semibold text-slate-400 flex items-center gap-2">
                    @if(auth()->user()->role === 'superadmin')
                        @php
                            $allGyms = \App\Models\Gym::orderBy('name')->get();
                            $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <select name="gym_id" id="gym_id_mobile" onchange="switchGymContext(this.value)" class="text-[10px] bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-lime-400 font-bold focus:outline-none cursor-pointer">
                                <option value="all" {{ $activeGymId === 'all' ? 'selected' : '' }}>Todas</option>
                                @foreach($allGyms as $g)
                                    <option value="{{ $g->id }}" {{ $activeGymId == $g->id ? 'selected' : '' }}>
                                        {{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        Panel Entrenador
                    @endif
                </div>

                <!-- Right items: Actions, Notifications, Profile -->
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle Button -->
                    <button id="theme-toggle" class="p-2.5 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-slate-100 rounded-xl border border-slate-850 hover:border-slate-700 transition-colors focus:outline-none cursor-pointer" title="Cambiar tema">
                        <i data-lucide="moon" class="w-4 h-4 dark-icon block"></i>
                        <i data-lucide="sun" class="w-4 h-4 light-icon hidden"></i>
                    </button>

                    <!-- Notifications Dropdown Trigger -->
                    <div class="relative inline-block text-left animate-fade-in" id="notifications-menu-container">
                        <button onclick="toggleNotificationsDropdown()" class="relative p-2.5 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-slate-100 rounded-xl border border-slate-850 hover:border-slate-700 transition-colors focus:outline-none cursor-pointer" title="Notificaciones">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span id="unread-dot" class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-slate-900 hidden"></span>
                        </button>
                        
                        <!-- Dropdown Panel (Fluid CSS Animated) -->
                        <div id="notifications-dropdown" class="dropdown-animate absolute right-0 mt-3 w-80 bg-slate-900 border border-slate-800 rounded-2xl shadow-xl z-50 py-2 overflow-hidden">
                            <div class="px-4 py-2 border-b border-slate-850 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-200">Notificaciones</span>
                                <form action="{{ route('notificaciones.read_all') }}" method="POST" class="m-0 inline">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-lime-450 hover:text-lime-300 font-bold uppercase transition-colors">Leer todas</button>
                                </form>
                            </div>
                            
                            <div id="notifications-list" class="max-h-64 overflow-y-auto divide-y divide-slate-850/50">
                                <div class="p-4 text-center text-xs text-slate-500">Cargando...</div>
                            </div>
                            
                            <div class="p-2 border-t border-slate-850 text-center">
                                <a href="{{ route('notificaciones.index') }}" class="block text-[10px] text-slate-400 hover:text-slate-200 font-bold uppercase py-1">Ver todo el historial</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile quick dropdown (Desktop) -->
                    <div class="flex items-center gap-3 pl-3 border-l border-slate-850">
                        <div class="text-right hidden xl:block">
                            <span class="block text-xs font-semibold text-slate-200">Coach {{ auth()->user()->profile->first_name }}</span>
                            <span class="block text-[10px] text-lime-400">Online</span>
                        </div>
                        <img src="{{ auth()->user()->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" 
                             alt="Avatar" 
                             class="w-9 h-9 rounded-xl object-cover ring-2 ring-lime-500/20">
                    </div>
                </div>
            </header>

            <!-- Main Dynamic Content -->
            <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto animate-fade-in">
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
        function toggleSidebarGroup(groupId) {
            const content = document.getElementById(groupId);
            const chevron = document.getElementById('chevron-' + groupId);
            if (content) {
                const isOpen = content.classList.contains('open');
                if (isOpen) {
                    content.classList.remove('open');
                    if (chevron) chevron.classList.add('-rotate-90');
                } else {
                    content.classList.add('open');
                    if (chevron) chevron.classList.remove('-rotate-90');
                }
            }
        }

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

            // Live Autocomplete Search Logic
            const searchInput = document.getElementById('global-search-input');
            const resultsDropdown = document.getElementById('live-search-results');
            let debounceTimer;

            if (searchInput && resultsDropdown) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    const query = searchInput.value.trim();

                    if (query.length < 2) {
                        resultsDropdown.classList.add('hidden');
                        resultsDropdown.innerHTML = '';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`/api/search/live?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length === 0) {
                                    resultsDropdown.innerHTML = `
                                        <div class="px-4 py-3 text-slate-500 text-xs italic text-center">
                                            No se encontraron coincidencias
                                        </div>
                                    `;
                                    resultsDropdown.classList.remove('hidden');
                                    return;
                                }

                                let html = '<div class="py-1.5 divide-y divide-slate-800/40">';
                                data.forEach(user => {
                                    const roleBadge = user.role === 'trainer' 
                                        ? '<span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded uppercase tracking-wider">Entrenador</span>' 
                                        : '<span class="px-1.5 py-0.5 text-[8px] font-extrabold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded uppercase tracking-wider">Atleta</span>';
                                    
                                    html += `
                                        <a href="${user.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-800/60 transition-colors group">
                                            <img src="${user.photo}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-800">
                                            <div class="overflow-hidden flex-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-bold text-xs text-slate-200 group-hover:text-lime-400 transition-colors truncate">${user.name}</span>
                                                    ${roleBadge}
                                                </div>
                                                <div class="flex items-center justify-between text-[9px] text-slate-500 mt-0.5">
                                                    <span class="truncate max-w-[120px]">${user.email}</span>
                                                    <span class="font-semibold uppercase tracking-wider text-slate-600">${user.gym_name}</span>
                                                </div>
                                            </div>
                                        </a>
                                    `;
                                });
                                html += '</div>';

                                resultsDropdown.innerHTML = html;
                                resultsDropdown.classList.remove('hidden');
                            })
                            .catch(err => {
                                console.error('Error fetching live search:', err);
                            });
                    }, 200);
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                        resultsDropdown.classList.add('hidden');
                    }
                });

                // Re-open on focus if query length is valid
                searchInput.addEventListener('focus', () => {
                    if (searchInput.value.trim().length >= 2) {
                        resultsDropdown.classList.remove('hidden');
                    }
                });
            }
        });
    </script>
    
    <!-- Dark/Light Mode Switcher -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn) {
                const darkIcon = themeToggleBtn.querySelector('.dark-icon');
                const lightIcon = themeToggleBtn.querySelector('.light-icon');
                
                const updateToggleIcons = (isLight) => {
                    if (isLight) {
                        darkIcon.classList.add('hidden');
                        darkIcon.classList.remove('block');
                        lightIcon.classList.add('block');
                        lightIcon.classList.remove('hidden');
                    } else {
                        darkIcon.classList.add('block');
                        darkIcon.classList.remove('hidden');
                        lightIcon.classList.add('hidden');
                        lightIcon.classList.remove('block');
                    }
                };
                
                // Set initial icons state
                updateToggleIcons(document.documentElement.classList.contains('light'));
                
                themeToggleBtn.addEventListener('click', () => {
                    const isLight = document.documentElement.classList.toggle('light');
                    localStorage.setItem('theme', isLight ? 'light' : 'dark');
                    updateToggleIcons(isLight);
                });
            }
        });
    </script>

    <!-- Superadmin Gym Switcher & Notifications Handling -->
    <script>
        function switchGymContext(gymId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const formData = new FormData();
            formData.append('gym_id', gymId);

            // Sync selectors visually
            const selectDesktop = document.getElementById('gym_id');
            const selectMobile = document.getElementById('gym_id_mobile');
            if (selectDesktop) selectDesktop.value = gymId;
            if (selectMobile) selectMobile.value = gymId;

            // 1. Immediately update Aforo UI via AJAX with explicit gymId
            if (typeof fetchAforoData === 'function') {
                fetchAforoData(gymId);
            }

            fetch('/superadmin/switch-gym', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof fetchAforoData === 'function') {
                        fetchAforoData(gymId);
                    }
                    window.location.reload();
                }
            })
            .catch(err => console.error('Error al cambiar sucursal:', err));
        }

        function toggleNotificationsDropdown() {
            const dropdown = document.getElementById('notifications-dropdown');
            if (!dropdown) return;

            const isOpen = dropdown.classList.contains('open');
            if (isOpen) {
                dropdown.classList.remove('open');
            } else {
                dropdown.classList.add('open');
                loadNotifications();
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('notifications-menu-container');
            const dropdown = document.getElementById('notifications-dropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        async function loadNotifications() {
            const listEl = document.getElementById('notifications-list');
            try {
                const response = await fetch('/api/notifications/unread');
                if (!response.ok) throw new Error('Network error');
                const data = await response.json();
                
                // Update unread dot
                const unreadDot = document.getElementById('unread-dot');
                if (data.unread_count > 0) {
                    unreadDot.classList.remove('hidden');
                } else {
                    unreadDot.classList.add('hidden');
                }
                
                if (data.notifications.length === 0) {
                    listEl.innerHTML = '<div class="p-4 text-center text-xs text-slate-550">No tienes notificaciones pendientes.</div>';
                    return;
                }
                
                let html = '';
                data.notifications.forEach(n => {
                    const readClass = n.is_read ? 'opacity-65 bg-slate-900/10' : 'bg-slate-900/40 border-l-2 border-lime-500';
                    let iconColor = 'text-lime-400 bg-lime-500/10';
                    let icon = 'bell';
                    
                    if (n.type === 'membership_expiry' || n.type === 'payment_reminder') {
                        iconColor = 'text-amber-400 bg-amber-500/10';
                        icon = 'alert-triangle';
                    } else if (n.type === 'new_routine') {
                        iconColor = 'text-purple-400 bg-purple-500/10';
                        icon = 'dumbbell';
                    } else if (n.type === 'achievement') {
                        iconColor = 'text-yellow-400 bg-yellow-500/10';
                        icon = 'trophy';
                    }
                    
                    const timeAgo = formatTimeAgo(new Date(n.createdAt));

                    html += `
                        <a href="/notificaciones/${n.id}/read" class="block p-3.5 hover:bg-slate-850/60 transition-colors ${readClass}">
                            <div class="flex gap-2.5 items-start">
                                <div class="p-1.5 rounded-lg shrink-0 ${iconColor}">
                                    <i data-lucide="${icon}" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="space-y-0.5">
                                    <span class="block text-xs font-bold text-slate-200">${escapeHtml(n.title)}</span>
                                    <span class="block text-[10px] text-slate-400 line-clamp-2 leading-relaxed">${escapeHtml(n.body || '')}</span>
                                    <span class="block text-[9px] text-slate-500 font-bold uppercase mt-1">${timeAgo}</span>
                                </div>
                            </div>
                        </a>
                    `;
                });
                listEl.innerHTML = html;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } catch (err) {
                listEl.innerHTML = '<div class="p-4 text-center text-xs text-rose-500">Error al cargar notificaciones.</div>';
            }
        }

        function formatTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 1) return 'hace un momento';
            if (diffMins < 60) return `hace ${diffMins} min`;
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `hace ${diffHours} hr`;
            const diffDays = Math.floor(diffHours / 24);
            return `hace ${diffDays} días`;
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Check on load
        document.addEventListener('DOMContentLoaded', () => {
            loadNotifications();
            
            // Close dropdown if click outside
            document.addEventListener('click', (e) => {
                const container = document.getElementById('notifications-menu-container');
                const dropdown = document.getElementById('notifications-dropdown');
                if (container && !container.contains(e.target) && dropdown) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>

    <!-- Ultra-Fast Seamless SPA PJAX Navigation Engine -->
    <script>
        (function() {
            let progressBar = document.getElementById('pjax-progress-bar');
            if (!progressBar) {
                progressBar = document.createElement('div');
                progressBar.id = 'pjax-progress-bar';
                progressBar.className = 'fixed top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-lime-400 via-emerald-400 to-lime-500 z-[9999] transition-all duration-300 pointer-events-none opacity-0';
                progressBar.style.width = '0%';
                document.body.appendChild(progressBar);
            }

            function showProgress() {
                progressBar.style.width = '25%';
                progressBar.classList.remove('opacity-0');
                setTimeout(() => { progressBar.style.width = '75%'; }, 60);
            }

            function completeProgress() {
                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressBar.classList.add('opacity-0');
                    setTimeout(() => { progressBar.style.width = '0%'; }, 300);
                }, 150);
            }

            async function loadPage(url, pushState = true) {
                showProgress();
                const mainContainer = document.querySelector('main');
                if (mainContainer) {
                    mainContainer.style.opacity = '0.4';
                    mainContainer.style.transition = 'opacity 0.12s ease';
                }

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-PJAX': 'true',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        window.location.href = url;
                        return;
                    }

                    const htmlText = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(htmlText, 'text/html');

                    const newMain = doc.querySelector('main');
                    const newModals = doc.querySelector('#pjax-modals-container');
                    const modalsContainer = document.getElementById('pjax-modals-container');
                    const newTitle = doc.querySelector('title') ? doc.querySelector('title').innerText : document.title;

                    if (!newMain || !mainContainer) {
                        window.location.href = url;
                        return;
                    }

                    document.title = newTitle;
                    if (pushState) {
                        window.history.pushState({ url: url }, newTitle, url);
                    }

                    mainContainer.innerHTML = newMain.innerHTML;
                    if (modalsContainer) {
                        modalsContainer.innerHTML = newModals ? newModals.innerHTML : '';
                    }
                    mainContainer.style.opacity = '1';
                    mainContainer.classList.remove('animate-fade-in');
                    void mainContainer.offsetWidth;
                    mainContainer.classList.add('animate-fade-in');

                    // Re-execute inline scripts inside main
                    const scripts = mainContainer.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    updateSidebarActiveLinks(url);

                    if (window.lucide) {
                        window.lucide.createIcons();
                    }

                    // Close mobile menu drawer if open
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebar-overlay');
                    if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                        sidebar.classList.add('-translate-x-full');
                        if (overlay) overlay.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }

                    window.scrollTo({ top: 0, behavior: 'instant' });
                    completeProgress();
                } catch (err) {
                    console.error('PJAX Navigation Error:', err);
                    window.location.href = url;
                }
            }

            function updateSidebarActiveLinks(currentUrl) {
                const urlObj = new URL(currentUrl, window.location.origin);
                const path = urlObj.pathname;

                const navLinks = document.querySelectorAll('aside nav a[href]');
                navLinks.forEach(link => {
                    const linkUrl = new URL(link.getAttribute('href'), window.location.origin);
                    const linkPath = linkUrl.pathname;

                    const isExactMatch = (path === linkPath) || (path === '/' && linkPath === '/dashboard');
                    const isSubPathMatch = linkPath !== '/' && linkPath !== '/dashboard' && path.startsWith(linkPath);

                    const isActive = isExactMatch || isSubPathMatch;

                    if (isActive) {
                        link.className = "sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm";
                        const parentGroupDiv = link.closest('.sidebar-group-box');
                        if (parentGroupDiv) {
                            const groupContent = parentGroupDiv.querySelector('.sidebar-accordion-wrapper');
                            const chevron = parentGroupDiv.querySelector('.sidebar-chevron');
                            if (groupContent && !groupContent.classList.contains('open')) {
                                groupContent.classList.add('open');
                            }
                            if (chevron && chevron.classList.contains('-rotate-90')) {
                                chevron.classList.remove('-rotate-90');
                            }
                        }
                    } else {
                        link.className = "sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item text-slate-400 hover:text-slate-100 hover:bg-slate-850/50";
                    }
                });
            }

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.getAttribute('target') === '_blank' || link.hasAttribute('download')) {
                    return;
                }

                const targetUrl = new URL(href, window.location.origin);
                if (targetUrl.origin !== window.location.origin) return;
                if (targetUrl.pathname.includes('/logout')) return;

                e.preventDefault();
                if (targetUrl.href === window.location.href) return;

                loadPage(targetUrl.href, true);
            });

            window.addEventListener('popstate', function() {
                loadPage(window.location.href, false);
            });

            window.loadUrl = loadPage;
        })();
    </script>

    <!-- Aforo Live AJAX Updater Script -->
    <script>
        async function fetchAforoData(overrideGymId = null) {
            try {
                let url = '/api/aforo';
                if (overrideGymId) {
                    url += '?gym_id=' + encodeURIComponent(overrideGymId);
                } else {
                    const selectDesktop = document.getElementById('gym_id');
                    const selectMobile = document.getElementById('gym_id_mobile');
                    const activeVal = selectDesktop ? selectDesktop.value : (selectMobile ? selectMobile.value : null);
                    if (activeVal) {
                        url += '?gym_id=' + encodeURIComponent(activeVal);
                    }
                }
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                const data = await response.json();
                
                const countElements = document.querySelectorAll('.aforo-count-val');
                const badgeElements = document.querySelectorAll('.aforo-pct-badge-val');
                const barElements = document.querySelectorAll('.aforo-bar-fill');
                
                countElements.forEach(el => {
                    el.textContent = data.count_text;
                });

                badgeElements.forEach(el => {
                    el.textContent = data.pct_text;
                    el.className = `aforo-pct-badge-val ${data.badge_bg_class} ${data.color_class} px-1.5 py-0.5 rounded-md text-[10px] font-black tracking-wide border ${data.badge_border_class} whitespace-nowrap`;
                });
                
                barElements.forEach(el => {
                    el.style.width = Math.min(100, Math.max(2, data.percentage)) + '%';
                    el.className = `aforo-bar-fill bg-gradient-to-r ${data.gradient_class} h-full rounded-full transition-all duration-700 ease-out`;
                });
            } catch (err) {
                console.error('Error actualizando aforo en vivo:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchAforoData();
            setInterval(fetchAforoData, 30000);
        });
    </script>

    <div id="pjax-modals-container">
        @stack('modals')
    </div>
    @stack('scripts')
</body>
</html>
