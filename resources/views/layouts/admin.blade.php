@php
    $isSuperAdmin = auth()->check() && auth()->user()->role === 'superadmin';
    $userGymId = auth()->check() ? auth()->user()->gym_id : null;
    $defaultGymContext = $isSuperAdmin ? 'all' : $userGymId;
    $activeGymId = session('superadmin_gym_id', $defaultGymContext);
    if (!$activeGymId) {
        $activeGymId = $defaultGymContext;
    }

    $activeGymLogo = null;
    if ($activeGymId === 'all') {
        $activeGymName = 'Todas las Sucursales';
    } else {
        if ($userGymId && $activeGymId == $userGymId && auth()->user()->gym) {
            $activeGymName = auth()->user()->gym->name;
            $activeGymLogo = auth()->user()->gym->logo_url;
        } else {
            $gymRecord = \App\Models\Gym::where('id', $activeGymId)->first(['name', 'logo_url']);
            $activeGymName = $gymRecord->name ?? 'Vista General';
            $activeGymLogo = $gymRecord->logo_url ?? null;
        }
    }

    // Calculate real Aforo / Gym Capacity based on SaaS Plan max_users from gym_subscriptions & saas_subscription_plans
    $getGymActivePlanHelper = function($gId) {
        if (!$gId || $gId === 'all') return null;
        if (\Illuminate\Support\Facades\Schema::hasTable('gym_subscriptions')) {
            $sub = \DB::table('gym_subscriptions')
                ->where('gym_id', $gId)
                ->whereIn('status', ['active', 'trialing'])
                ->latest('id')
                ->first();
            if ($sub && $sub->plan_id) {
                $plan = \App\Models\SaasSubscriptionPlan::find($sub->plan_id);
                if ($plan) return $plan;
            }
        }
        $gym = \App\Models\Gym::find($gId);
        if ($gym && $gym->current_plan_id) {
            return \App\Models\SaasSubscriptionPlan::find($gym->current_plan_id);
        }
        return null;
    };

    if ($activeGymId === 'all') {
        $aforoCurrentUsers = \App\Models\User::where('role', 'member')->count();
        $allGymsList = \App\Models\Gym::all();
        $aforoMaxUsersSum = 0;
        $hasUnlimitedPlan = false;
        foreach ($allGymsList as $g) {
            $plan = $getGymActivePlanHelper($g->id);
            if ($plan) {
                if (is_null($plan->max_users)) {
                    $hasUnlimitedPlan = true;
                } else {
                    $aforoMaxUsersSum += (int)$plan->max_users;
                }
            } else {
                $aforoMaxUsersSum += 50;
            }
        }
        if ($hasUnlimitedPlan) {
            $aforoMaxUsersNum = null;
            $aforoMaxUsers = 'Ilimitado';
        } else {
            $aforoMaxUsersNum = $aforoMaxUsersSum > 0 ? $aforoMaxUsersSum : 50;
            $aforoMaxUsers = $aforoMaxUsersNum;
        }
    } else {
        $aforoCurrentUsers = \App\Models\User::where('gym_id', $activeGymId)->where('role', 'member')->count();
        $plan = $getGymActivePlanHelper($activeGymId);
        if ($plan) {
            if (is_null($plan->max_users)) {
                $aforoMaxUsersNum = null;
                $aforoMaxUsers = 'Ilimitado';
            } else {
                $aforoMaxUsersNum = (int)$plan->max_users;
                $aforoMaxUsers = $aforoMaxUsersNum;
            }
        } else {
            $aforoMaxUsersNum = 50;
            $aforoMaxUsers = 50;
        }
    }

    if (is_null($aforoMaxUsersNum)) {
        $aforoPercentage = 100;
        $aforoPctFormatted = '∞';
        $aforoCountText = "{$aforoCurrentUsers} / ∞";
    } else {
        $aforoPercentage = $aforoMaxUsersNum > 0 ? round(($aforoCurrentUsers / $aforoMaxUsersNum) * 100, 1) : 0;
        $aforoPctFormatted = (floor($aforoPercentage) == $aforoPercentage) ? (int)$aforoPercentage : $aforoPercentage;
        $aforoCountText = "{$aforoCurrentUsers}/{$aforoMaxUsersNum}";
    }
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GymOS') - Panel de Administración</title>

    <!-- Font Size Accessibility Scaler (Text-Only Scaling) -->
    <style id="typography-font-scaler">
        html.font-scale-small { --font-scale: 0.875; }
        html.font-scale-normal { --font-scale: 1; }
        html.font-scale-large { --font-scale: 1.18; }
        html.font-scale-xlarge { --font-scale: 1.35; }

        html[class*="font-scale-"] .text-\[9px\] { font-size: calc(9px * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-\[10px\] { font-size: calc(10px * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-\[11px\] { font-size: calc(11px * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-xs { font-size: calc(0.75rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-sm { font-size: calc(0.875rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-base { font-size: calc(1rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-lg { font-size: calc(1.125rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-xl { font-size: calc(1.25rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-2xl { font-size: calc(1.5rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] .text-3xl { font-size: calc(1.875rem * var(--font-scale, 1)) !important; }
        html[class*="font-scale-"] p, 
        html[class*="font-scale-"] span, 
        html[class*="font-scale-"] td, 
        html[class*="font-scale-"] th, 
        html[class*="font-scale-"] label, 
        html[class*="font-scale-"] input, 
        html[class*="font-scale-"] select, 
        html[class*="font-scale-"] button, 
        html[class*="font-scale-"] h1, 
        html[class*="font-scale-"] h2, 
        html[class*="font-scale-"] h3, 
        html[class*="font-scale-"] h4 {
            line-height: 1.35;
        }
    </style>
    <script>
        (function() {
            window.committedFontSize = localStorage.getItem('gym_app_font_size') || 'normal';

            window.previewAppFontSize = function(sizeKey) {
                const keys = ['small', 'normal', 'large', 'xlarge'];
                const key = keys.includes(sizeKey) ? sizeKey : 'normal';
                
                keys.forEach(k => document.documentElement.classList.remove('font-scale-' + k));
                document.documentElement.classList.add('font-scale-' + key);
                document.documentElement.style.fontSize = '100%';
            };

            window.commitAppFontSize = function(sizeKey) {
                const keys = ['small', 'normal', 'large', 'xlarge'];
                const key = keys.includes(sizeKey) ? sizeKey : 'normal';
                
                window.committedFontSize = key;
                localStorage.setItem('gym_app_font_size', key);
                window.previewAppFontSize(key);
            };

            window.revertAppFontSize = function() {
                const saved = localStorage.getItem('gym_app_font_size') || 'normal';
                window.committedFontSize = saved;
                window.previewAppFontSize(saved);
            };

            window.applyAppFontSize = window.commitAppFontSize;

            // Apply committed font size on initial script execution
            window.revertAppFontSize();
        })();
    </script>

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
            --bg-body: #edf2f7; /* Soft soothing titanium slate background - NOT blinding pure white */
            
            /* Override Tailwind CSS v4 variables with balanced elegant slate tones */
            --color-slate-950: #e2e8f0;
            --color-slate-900: #f8fafc;
            --color-slate-855: #cbd5e1;
            --color-slate-850: #d8e2ec;
            --color-slate-800: #cbd5e1;
            --color-slate-750: #94a3b8;
            --color-slate-700: #64748b;
            --color-slate-600: #64748b;
            --color-slate-550: #475569;
            --color-slate-500: #64748b;
            
            --color-slate-400: #475569; /* Explicitly requested: crisp readable slate tone */
            --color-slate-300: #334155;
            --color-slate-200: #1e293b;
            --color-slate-100: #0f172a;

            /* High-contrast readable accents for Light Mode */
            --color-lime-400: #4d7c0f;
            --color-lime-500: #65a30d;
            --color-emerald-400: #047857;
            --color-emerald-500: #059669;
            --color-amber-400: #b45309;
            --color-amber-500: #d97706;
            --color-rose-400: #be123c;
            --color-rose-500: #e11d48;
            --color-purple-400: #6b21a8;
            --color-purple-500: #7e22ce;
            --color-cyan-400: #0e7490;
            --color-cyan-500: #0891b2;
            --color-blue-400: #1d4ed8;
            --color-blue-500: #2563eb;
        }

        /* Light mode component styling overrides */
        html.light body {
            color: #1e293b;
        }

        html.light .bg-slate-900,
        html.light .bg-slate-900\/30,
        html.light .bg-slate-900\/40,
        html.light .bg-slate-900\/60,
        html.light .bg-slate-900\/80,
        html.light .bg-slate-900\/90,
        html.light .bg-slate-900\/95 {
            background-color: #f8fafc !important;
            border-color: #d1d9e2 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
        }

        html.light .bg-slate-950,
        html.light .bg-slate-950\/20,
        html.light .bg-slate-950\/40,
        html.light .bg-slate-950\/50,
        html.light .bg-slate-950\/60,
        html.light .bg-slate-950\/80,
        html.light .bg-slate-950\/85 {
            background-color: #e9eff5 !important;
            border-color: #cbd5e1 !important;
        }

        html.light #sidebar {
            background-color: #f8fafc !important;
            border-right-color: #d1d9e2 !important;
            box-shadow: 2px 0 12px -3px rgba(0, 0, 0, 0.05);
        }

        html.light header {
            background-color: rgba(248, 250, 252, 0.92) !important;
            border-bottom-color: #d1d9e2 !important;
        }

        html.light input,
        html.light select,
        html.light textarea {
            background-color: #eef2f6 !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        html.light input::placeholder,
        html.light textarea::placeholder {
            color: #64748b !important;
        }

        html.light input:focus,
        html.light select:focus,
        html.light textarea:focus {
            background-color: #ffffff !important;
            border-color: #65a30d !important;
            box-shadow: 0 0 0 2px rgba(101, 163, 13, 0.15) !important;
        }

        html.light .sidebar-group-box {
            background-color: #f1f5f9 !important;
            border-color: #d8e2ec !important;
        }

        html.light .sidebar-link {
            color: #475569 !important;
        }

        html.light .sidebar-link:hover:not(.active-nav-link) {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        html.light .active-nav-link {
            background: linear-gradient(to right, rgba(101, 163, 13, 0.15), rgba(101, 163, 13, 0.04)) !important;
            color: #3f6212 !important;
            font-weight: 700 !important;
            border-left: 3px solid #65a30d;
        }

        html.light thead tr {
            background-color: #e2e8f0 !important;
            color: #334155 !important;
            border-bottom-color: #cbd5e1 !important;
        }

        html.light tbody tr:hover {
            background-color: #f1f5f9 !important;
        }

        html.light ::-webkit-scrollbar-track {
            background: #e2e8f0 !important;
        }
        html.light ::-webkit-scrollbar-thumb {
            background: #94a3b8 !important;
            border-color: #cbd5e1 !important;
        }
        html.light ::-webkit-scrollbar-thumb:hover {
            background: #64748b !important;
        }
        html.light * {
            scrollbar-color: #94a3b8 #e2e8f0;
        }

        /* Light mode modals backdrop */
        html.light .fixed.inset-0.z-50,
        html.light .fixed.inset-0.z-\[9999\] {
            background-color: rgba(15, 23, 42, 0.55) !important;
            backdrop-filter: blur(6px) !important;
        }

        /* Dropdowns & Command Palette Animation */
        #notifications-dropdown {
            display: none;
            opacity: 0;
            transform: translateY(-8px) scale(0.98);
            transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1), transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #notifications-dropdown.open {
            display: block;
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .search-palette-item.active-item {
            background-color: rgba(163, 230, 53, 0.08) !important;
            border-left-color: #84cc16 !important;
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
                transform: none;
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
    <div class="min-h-screen bg-slate-950 flex flex-col">

        <!-- Mobile Header Bar (Visible on mobile only) -->
        <header class="md:hidden flex items-center justify-between px-4 sm:px-6 py-4 bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
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
                            @php
                                $sidebarRoleTitle = match(auth()->user()->role) {
                                    'superadmin' => 'SuperAdmin',
                                    'admin' => 'Admin',
                                    'cajero' => 'Cajero',
                                    'trainer' => 'Coach',
                                    default => 'Usuario'
                                };
                            @endphp
                            <h4 class="font-bold text-xs text-slate-100 truncate tracking-wide">{{ $sidebarRoleTitle }} {{ auth()->user()->profile->first_name ?? 'Usuario' }}</h4>
                            <p class="text-[10px] text-lime-400 font-semibold truncate uppercase tracking-widest mt-0.5">{{ auth()->user()->gym->name ?? ($isSuperAdmin ? 'Superadministrador Global' : 'Sin Gimnasio') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Options -->
                <nav class="space-y-4">
                    @php
                        $isPrincipalActive = Request::is('dashboard') || Request::is('/') || Request::is('clientes*') || Request::is('asistencia*');
                        $isCajaActive = Request::is('tienda*') || Request::is('finanzas*') || Request::is('cierre-caja*');
                        $isEntrenamientoActive = Request::is('rutinas*') || Request::is('nutricion*') || Request::is('ingredientes*') || Request::is('recetas*') || Request::is('ejercicios*') || Request::is('equipamiento*') || Request::is('clases*') || Request::is('retos*') || Request::is('notificaciones*');
                        $isSaaSActive = Request::is('staff*') || Request::is('cajeros*') || Request::is('permisos*');
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
                                @if(auth()->user()->hasPermission('dashboard.view'))
                                    <a href="{{ url('/dashboard') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('dashboard') || Request::is('/') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Dashboard</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('clientes.view'))
                                    <a href="{{ url('/clientes') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('clientes*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="users" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Mis Clientes</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('asistencia.view'))
                                    <a href="{{ url('/asistencia') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('asistencia*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="calendar-check" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Control Asistencia</span>
                                    </a>
                                @endif
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
                                @if(auth()->user()->hasPermission('tienda.pos_access'))
                                    <a href="{{ url('/tienda/pos') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/pos') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="shopping-cart" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Venta Nueva (POS)</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('tienda.products_view') || auth()->user()->hasPermission('tienda.products_manage'))
                                    <a href="{{ url('/tienda/productos') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/productos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="package" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Inventario Tienda</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('tienda.stock_movements_view'))
                                    <a href="{{ url('/tienda/movimientos') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/movimientos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="activity" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Auditoría Stock</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('tienda.sales_history_view'))
                                    <a href="{{ url('/tienda/ventas') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('tienda/ventas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="receipt" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Historial Ventas</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('finanzas.view'))
                                    <a href="{{ url('/finanzas') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ (Request::is('finanzas') || Request::is('finanzas/export') || (Request::is('finanzas*') && !Request::is('finanzas/pasarelas*') && !Request::is('finanzas/tasa-cambio*'))) ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Finanzas & Pagos</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('finanzas.gateways_manage'))
                                    <a href="{{ url('/finanzas/pasarelas') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('finanzas/pasarelas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="qr-code" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Pasarelas de Pago</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('finanzas.exchange_rate_manage'))
                                    <a href="{{ url('/finanzas/tasa-cambio') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('finanzas/tasa-cambio*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="coins" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Tasa de Cambio (VES)</span>
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('cierre_caja.view'))
                                    <a href="{{ url('/cierre-caja') }}" 
                                       class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('cierre-caja*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                        <i data-lucide="calculator" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                        <span>Cierre de Caja</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Group 3: Entrenamiento & Nutrición (Recuadro Box) -->
                    @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'trainer']) || auth()->user()->hasPermission('rutinas.view') || auth()->user()->hasPermission('nutricion.view') || auth()->user()->hasPermission('catalogos.manage') || auth()->user()->hasPermission('clases.manage') || auth()->user()->hasPermission('retos.manage'))
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
                                    @if(auth()->user()->hasPermission('rutinas.view') || auth()->user()->hasPermission('rutinas.manage'))
                                        <a href="{{ url('/rutinas') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('rutinas*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="dumbbell" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Planes de Rutinas</span>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('nutricion.view') || auth()->user()->hasPermission('nutricion.manage'))
                                        <a href="{{ url('/nutricion') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('nutricion*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="apple" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Planes de Nutrición</span>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('catalogos.manage'))
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
                                    @endif
                                    @if(auth()->user()->hasPermission('clases.manage'))
                                        <a href="{{ url('/clases') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('clases*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="calendar-heart" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Clases & Eventos</span>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('retos.manage'))
                                        <a href="{{ url('/retos') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('retos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="trophy" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Retos & Incentivos</span>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('notificaciones.send'))
                                        <a href="{{ url('/notificaciones') }}" 
                                           class="sidebar-link flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('notificaciones*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <span class="flex items-center gap-3">
                                                <i data-lucide="bell" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                                <span>Notificaciones</span>
                                            </span>
                                            <span class="px-2 py-0.5 text-[9px] font-extrabold bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-full">Pro</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Group 4: Configuración & Administración (Recuadro Box) -->
                    @if(in_array(auth()->user()->role, ['admin', 'superadmin']) || auth()->user()->hasPermission('permisos.manage') || auth()->user()->hasPermission('staff.view') || auth()->user()->hasPermission('cajeros.view'))
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
                                    @if(auth()->user()->hasPermission('staff.view') || auth()->user()->hasPermission('staff.manage'))
                                        <a href="{{ url('/staff') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('staff*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="users-2" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Entrenadores (Staff)</span>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('cajeros.view') || auth()->user()->hasPermission('cajeros.manage'))
                                        <a href="{{ url('/cajeros') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('cajeros*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="calculator" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Cajeros (Recepción)</span>
                                        </a>
                                    @endif
                                    @if(in_array(auth()->user()->role, ['admin', 'superadmin']) || auth()->user()->hasPermission('permisos.manage'))
                                        <a href="{{ url('/permisos') }}" 
                                           class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ Request::is('permisos*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }}">
                                            <i data-lucide="shield-check" class="w-4 h-4 text-slate-500 group-hover/item:text-lime-400 group-hover/item:scale-110 transition-all duration-200"></i>
                                            <span>Matriz de Permisos</span>
                                        </a>
                                    @endif
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
                                <span class="aforo-count-val text-slate-300 font-extrabold text-[11px] whitespace-nowrap tracking-tight">{{ $aforoCountText }}</span>
                                <span class="aforo-pct-badge-val {{ $badgeBg }} {{ $textColor }} px-1.5 py-0.5 rounded-md text-[10px] font-black tracking-wide border {{ $badgeBorder }} whitespace-nowrap">
                                    {{ is_null($aforoMaxUsersNum) ? 'Ilimitado' : $aforoPctFormatted . '%' }}
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
                    <a href="{{ route('configuracion.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium group/item {{ request()->routeIs('configuracion.*') ? 'active-nav-link bg-gradient-to-r from-lime-500/10 to-emerald-500/5 text-lime-400 font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-850/50' }} transition-colors">
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
        <div class="flex-1 flex flex-col md:pl-72 min-h-screen min-w-0 w-full">
            
            <!-- Top Navbar / Header -->
            <header class="sticky top-0 z-20 bg-slate-950/85 backdrop-blur-md border-b border-slate-800/60 px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between w-full">
                
                <!-- Quick Search & Gym Switcher for Superadmin -->
                <div class="flex items-center gap-3 lg:gap-4 flex-1 max-w-2xl min-w-0">
                    <form action="{{ route('global.search') }}" method="GET" class="relative w-full max-w-sm sm:max-w-md lg:max-w-lg m-0" id="global-search-form">
                        <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                        <input type="text" 
                               name="q" 
                               id="global-search-input"
                               autocomplete="off"
                               value="{{ request('q') }}"
                               placeholder="Buscar atletas, staff, productos, rutinas..." 
                               class="w-full pl-10 pr-20 py-2 text-sm bg-slate-900/90 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/60 focus:ring-2 focus:ring-lime-500/20 transition-all shadow-inner">
                        
                        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5 pointer-events-auto">
                            <button type="button" id="global-search-clear" class="p-1 text-slate-500 hover:text-slate-200 hidden transition-colors" title="Limpiar búsqueda">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                            <kbd class="hidden md:inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono text-slate-400 bg-slate-800/80 border border-slate-700/80 rounded shadow-xs select-none">Ctrl K</kbd>
                        </div>
                        
                        <!-- Live Autocomplete Command Palette Dropdown -->
                        <div id="live-search-results" class="absolute top-full left-0 right-0 sm:right-auto sm:w-[480px] md:w-[540px] mt-2 bg-slate-900/98 backdrop-blur-xl border border-slate-800 rounded-2xl shadow-2xl z-50 overflow-hidden hidden max-h-[460px] overflow-y-auto divide-y divide-slate-800/50">
                            <!-- Injected by JS -->
                        </div>
                    </form>

                    @if(auth()->user()->role === 'superadmin')
                        @php
                            $allGyms = \App\Models\Gym::orderBy('name')->get();
                            $activeGymId = session('superadmin_gym_id', 'all');
                        @endphp
                        <div class="hidden xl:flex items-center gap-2 shrink-0">
                            <label for="gym_id" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Sucursal:</label>
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
                            $activeGymId = session('superadmin_gym_id', 'all');
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
                    @endif
                </div>

                <!-- Right items: Actions, Notifications, Profile -->
                <div class="flex items-center gap-2.5 sm:gap-3 md:gap-4 shrink-0">
                    @php
                        $navbarCurrentRate = \App\Services\ExchangeRateService::getCurrentRate($activeGymId ?? null);
                    @endphp
                    <!-- Exchange Rate Navbar Badge -->
                    <a href="{{ route('tasas_cambio.index') }}" 
                       class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-900/90 hover:bg-slate-850 border border-slate-800 hover:border-slate-700 rounded-xl text-xs transition-all group shadow-sm" 
                       title="Factor Cambiario Oficial (Click para administrar)">
                        <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>
                        <span class="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider">BCV:</span>
                        <span class="text-xs font-black text-lime-400 font-mono">Bs. {{ number_format($navbarCurrentRate, 2, ',', '.') }}</span>
                    </a>

                    <!-- Theme Toggle Button -->
                    <button id="theme-toggle" class="p-2.5 bg-slate-900 hover:bg-slate-850 text-slate-300 hover:text-slate-100 rounded-xl border border-slate-850 hover:border-slate-700 transition-colors focus:outline-none cursor-pointer" title="Cambiar tema">
                        <i data-lucide="moon" class="w-4 h-4 dark-icon block"></i>
                        <i data-lucide="sun" class="w-4 h-4 light-icon hidden"></i>
                    </button>

                    <!-- Notifications Dropdown Trigger -->
                    <div class="relative inline-block text-left" id="notifications-menu-container">
                        <button type="button" id="notifications-trigger-btn" onclick="window.toggleNotificationsDropdown(event)" class="relative p-2.5 bg-slate-900 hover:bg-slate-850 text-slate-300 hover:text-slate-100 rounded-xl border border-slate-850 hover:border-slate-700 transition-colors focus:outline-none cursor-pointer" title="Centro de Notificaciones">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span id="unread-dot" class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-slate-900 hidden animate-pulse"></span>
                        </button>
                        
                        <!-- Dropdown Panel (Spacious, Organized & Polished) -->
                        <div id="notifications-dropdown" class="absolute right-0 mt-3 w-[350px] sm:w-[420px] md:w-[460px] bg-slate-900/98 backdrop-blur-xl border border-slate-800 rounded-2xl shadow-2xl z-50 py-0 overflow-hidden">
                            <!-- Header -->
                            <div class="px-4 py-3 border-b border-slate-800/80 bg-slate-950/40 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="bell" class="w-4 h-4 text-lime-400"></i>
                                    <span class="text-xs font-bold text-slate-100" id="notifications-dropdown-header-title">{{ $isSuperAdmin ? 'Auditoría & Bitácora' : 'Notificaciones' }}</span>
                                    <span id="notifications-count-badge" class="px-2 py-0.5 text-[9px] font-extrabold bg-lime-500/10 text-lime-400 border border-lime-500/20 rounded-full">0 nuevas</span>
                                </div>
                                <button type="button" onclick="window.markAllNotificationsAsRead(event)" class="text-[10px] text-lime-400 hover:text-lime-300 font-bold uppercase transition-colors flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                    <span>{{ $isSuperAdmin ? 'Marcar vistas' : 'Leer todas' }}</span>
                                </button>
                            </div>
                            
                            <!-- Notifications Scrollable List -->
                            <div id="notifications-list" class="max-h-[380px] overflow-y-auto divide-y divide-slate-800/50">
                                <div class="p-8 text-center text-xs text-slate-500 flex flex-col items-center gap-2">
                                    <div class="w-5 h-5 border-2 border-lime-400 border-t-transparent rounded-full animate-spin"></div>
                                    <span>Cargando notificaciones...</span>
                                </div>
                            </div>
                            
                            <!-- Footer Link -->
                            <div class="p-2.5 border-t border-slate-800/80 bg-slate-950/40 text-center">
                                <a href="{{ $isSuperAdmin ? route('superadmin.audit.index') : route('notificaciones.index') }}" id="notifications-dropdown-footer-link" class="inline-flex items-center justify-center gap-1.5 w-full text-xs text-slate-400 hover:text-lime-400 font-bold py-1.5 transition-colors">
                                    <span>{{ $isSuperAdmin ? 'Ver toda la Auditoría & Bitácoras' : 'Ver historial completo de notificaciones' }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile quick dropdown (Desktop) -->
                    <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-800/80">
                        <div class="text-right hidden xl:block">
                            @php
                                $roleDisplay = match(auth()->user()->role) {
                                    'superadmin' => 'SuperAdmin',
                                    'admin' => 'Admin',
                                    'cajero' => 'Cajero',
                                    'trainer' => 'Coach',
                                    default => 'Usuario'
                                };
                            @endphp
                            <span class="block text-xs font-bold text-slate-200">{{ $roleDisplay }} {{ auth()->user()->profile->first_name ?? '' }}</span>
                            <span class="block text-[10px] text-lime-400 font-semibold flex items-center justify-end gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>
                                Online
                            </span>
                        </div>
                        <img src="{{ auth()->user()->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" 
                             alt="Avatar" 
                             class="w-9 h-9 rounded-xl object-cover ring-2 ring-lime-500/20 shrink-0">
                    </div>
                </div>
            </header>

            <!-- Main Dynamic Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full max-w-[1600px] mx-auto min-w-0 animate-fade-in">
                <!-- Dynamically injected screen content -->
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="mt-auto py-6 px-8 border-t border-slate-900/60 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} GymFlow OS. Creado para entrenadores de élite.</p>
            </footer>

        </div>
    </div>

    <!-- Global Event Lifecycle Interceptor & Universal Modal Helper -->
    <script>
        (function() {
            // Intercept document.addEventListener for DOMContentLoaded to execute immediately if document is already ready (e.g. during PJAX / AJAX)
            const origDocAddEventListener = document.addEventListener;
            document.addEventListener = function(type, listener, options) {
                if (type === 'DOMContentLoaded' && (document.readyState === 'interactive' || document.readyState === 'complete')) {
                    try {
                        setTimeout(() => listener.call(document, new Event('DOMContentLoaded')), 0);
                    } catch (e) {
                        console.error('Error executing immediate DOMContentLoaded listener:', e);
                    }
                    return; // Prevent piling up stale listeners across PJAX navigations
                }
                return origDocAddEventListener.call(document, type, listener, options);
            };

            // Intercept window.addEventListener for load event
            const origWinAddEventListener = window.addEventListener;
            window.addEventListener = function(type, listener, options) {
                if (type === 'load' && document.readyState === 'complete') {
                    try {
                        setTimeout(() => listener.call(window, new Event('load')), 0);
                    } catch (e) {
                        console.error('Error executing immediate load listener:', e);
                    }
                    return; // Prevent piling up stale listeners across PJAX navigations
                }
                return origWinAddEventListener.call(window, type, listener, options);
            };

            // Auto-relocate all modals to document.body so they are NEVER trapped inside transformed containers
            function relocateAllModals() {
                const modals = document.querySelectorAll('.fixed.inset-0, [id$="-modal"], [id^="modal-"]');
                modals.forEach(modal => {
                    if (modal && modal.parentElement && modal.parentElement !== document.body && modal.id !== 'sidebar-overlay') {
                        document.body.appendChild(modal);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', relocateAllModals);
            window.addEventListener('load', relocateAllModals);
            setTimeout(relocateAllModals, 50);
            setTimeout(relocateAllModals, 300);

            // Universal Robust Viewport-Centered Modal Toggle Helper (Accessible on every view)
            window.toggleModal = function(modalId) {
                if (!modalId) return;
                const modal = document.getElementById(modalId);
                if (!modal) {
                    console.warn('Modal not found:', modalId);
                    return;
                }

                // Crucial: Move modal to document.body so position:fixed is ALWAYS relative to browser viewport,
                // never trapped or displaced by parent transforms, animations, or page scroll!
                if (modal.parentElement !== document.body && modal.id !== 'sidebar-overlay') {
                    document.body.appendChild(modal);
                }

                const isOpening = modal.classList.contains('hidden') || modal.style.display === 'none';
                if (isOpening) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.style.position = 'fixed';
                    modal.style.top = '0';
                    modal.style.left = '0';
                    modal.style.right = '0';
                    modal.style.bottom = '0';
                    modal.style.width = '100vw';
                    modal.style.height = '100vh';
                    modal.style.zIndex = '9999';
                    modal.style.display = 'flex';
                    modal.style.alignItems = 'center';
                    modal.style.justifyContent = 'center';
                    modal.style.overflowY = 'auto';
                    modal.style.padding = '1rem';
                    document.body.classList.add('overflow-hidden');
                    document.body.style.overflow = 'hidden';

                    // Ensure inner modal dialog has max-height and auto scroll if viewport is short
                    const dialogCard = modal.querySelector('.bg-slate-900, [class*="max-w-"], .modal-content');
                    if (dialogCard) {
                        dialogCard.style.margin = 'auto';
                        dialogCard.style.maxHeight = '90vh';
                        dialogCard.style.overflowY = 'auto';
                    }

                    setTimeout(() => {
                        const firstInput = modal.querySelector('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
                        if (firstInput) firstInput.focus();
                    }, 50);
                } else {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.style.display = 'none';

                    // Check if any other root modal overlay is genuinely open
                    const otherOpenModals = Array.from(document.querySelectorAll('.fixed.inset-0, [id$="-modal"], [id^="modal-"]')).filter(m => {
                        if (m === modal || m.id === 'sidebar-overlay') return false;
                        const isOverlay = m.classList.contains('fixed') || m.classList.contains('inset-0') || m.style.position === 'fixed';
                        if (!isOverlay) return false;
                        return !m.classList.contains('hidden') && m.style.display !== 'none';
                    });

                    if (otherOpenModals.length === 0) {
                        document.body.classList.remove('overflow-hidden');
                        document.body.style.overflow = '';
                        document.documentElement.style.overflow = '';
                    }
                }
                if (window.lucide) window.lucide.createIcons();
            };

            window.openModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal && (modal.classList.contains('hidden') || modal.style.display === 'none')) {
                    window.toggleModal(modalId);
                }
            };

            window.closeModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal && (!modal.classList.contains('hidden') && modal.style.display !== 'none')) {
                    window.toggleModal(modalId);
                }
            };

            // Global aliases
            window.toggleModalDialog = window.toggleModal;

            // Global ESC key listener to close active modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const openModals = Array.from(document.querySelectorAll('.fixed.inset-0, [id$="-modal"], [id^="modal-"]')).filter(m => {
                        if (m.id === 'sidebar-overlay') return false;
                        const isOverlay = m.classList.contains('fixed') || m.classList.contains('inset-0') || m.style.position === 'fixed';
                        if (!isOverlay) return false;
                        return !m.classList.contains('hidden') && m.style.display !== 'none';
                    });
                    openModals.forEach(m => {
                        if (m.id && typeof window.toggleModal === 'function') {
                            window.toggleModal(m.id);
                        }
                    });
                }
            });

            // Global Click outside dialog backdrop listener
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id && e.target.id !== 'sidebar-overlay' && (e.target.classList && e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) && !e.target.classList.contains('hidden') && e.target.style.display !== 'none') {
                    // Clicked directly on the modal backdrop container (not on inner card or inputs)
                    window.toggleModal(e.target.id);
                }
            });
        })();

        // Toggle Sidebar Script (Clean Accordion & Auto-close non-active groups)
        const allSidebarGroups = ['group-principal', 'group-caja', 'group-entrenamiento', 'group-saas', 'group-superadmin'];

        function toggleSidebarGroup(groupId) {
            const content = document.getElementById(groupId);
            const chevron = document.getElementById('chevron-' + groupId);
            if (!content) return;

            const isCurrentlyOpen = content.classList.contains('open');

            if (isCurrentlyOpen) {
                content.classList.remove('open');
                if (chevron) chevron.classList.add('-rotate-90');
            } else {
                // Open this group and close all other groups for a clean accordion experience
                allSidebarGroups.forEach(otherId => {
                    const otherContent = document.getElementById(otherId);
                    const otherChevron = document.getElementById('chevron-' + otherId);
                    if (otherContent) {
                        if (otherId === groupId) {
                            otherContent.classList.add('open');
                            if (otherChevron) otherChevron.classList.remove('-rotate-90');
                        } else {
                            otherContent.classList.remove('open');
                            if (otherChevron) otherChevron.classList.add('-rotate-90');
                        }
                    }
                });
            }
        }

        function syncSidebarGroupStates() {
            // Clean up any stale localStorage keys from previous sessions
            allSidebarGroups.forEach(groupId => {
                try {
                    localStorage.removeItem('sidebar_group_' + groupId);
                } catch (e) {}
            });

            // Ensure ONLY the active group (the one containing the current active route) is open
            allSidebarGroups.forEach(groupId => {
                const content = document.getElementById(groupId);
                const chevron = document.getElementById('chevron-' + groupId);
                if (!content) return;

                const hasActiveLink = content.querySelector('.active-nav-link') !== null;

                if (hasActiveLink) {
                    content.classList.add('open');
                    if (chevron) chevron.classList.remove('-rotate-90');
                } else {
                    content.classList.remove('open');
                    if (chevron) chevron.classList.add('-rotate-90');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            syncSidebarGroupStates();
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

            // Live Autocomplete Search Logic (Command Palette Style)
            const searchInput = document.getElementById('global-search-input');
            const resultsDropdown = document.getElementById('live-search-results');
            const searchClearBtn = document.getElementById('global-search-clear');
            let debounceTimer;
            let currentHighlightIndex = -1;

            // Global Keyboard Shortcut: Ctrl + K or / to focus search
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
            });

            if (searchClearBtn && searchInput) {
                searchClearBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    searchClearBtn.classList.add('hidden');
                    resultsDropdown.classList.add('hidden');
                    resultsDropdown.innerHTML = '';
                    searchInput.focus();
                });
            }

            if (searchInput && resultsDropdown) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    const query = searchInput.value.trim();

                    if (searchClearBtn) {
                        if (query.length > 0) {
                            searchClearBtn.classList.remove('hidden');
                        } else {
                            searchClearBtn.classList.add('hidden');
                        }
                    }

                    if (query.length < 2) {
                        resultsDropdown.classList.add('hidden');
                        resultsDropdown.innerHTML = '';
                        currentHighlightIndex = -1;
                        return;
                    }

                    // Show temporary loading indicator
                    resultsDropdown.innerHTML = `
                        <div class="p-6 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                            <div class="w-4 h-4 border-2 border-lime-400 border-t-transparent rounded-full animate-spin"></div>
                            <span>Buscando en la base de datos...</span>
                        </div>
                    `;
                    resultsDropdown.classList.remove('hidden');

                    debounceTimer = setTimeout(() => {
                        fetch(`/api/search/live?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                currentHighlightIndex = -1;
                                if (!data || data.total === 0) {
                                    resultsDropdown.innerHTML = `
                                        <div class="p-6 text-center">
                                            <div class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-400">
                                                <i data-lucide="search-x" class="w-5 h-5"></i>
                                            </div>
                                            <p class="text-xs font-bold text-slate-300">No se encontraron resultados para "${escapeHtml(query)}"</p>
                                            <p class="text-[10px] text-slate-500 mt-1">Prueba buscando por nombre, cédula (CI), teléfono, producto o rutina.</p>
                                        </div>
                                    `;
                                    resultsDropdown.classList.remove('hidden');
                                    if (window.lucide) window.lucide.createIcons();
                                    return;
                                }

                                let html = '';

                                // 1. Accesos Rápidos
                                if (data.shortcuts && data.shortcuts.length > 0) {
                                    html += `
                                        <div class="p-2">
                                            <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                                <i data-lucide="zap" class="w-3 h-3 text-amber-400"></i> Accesos Rápidos
                                            </div>
                                            <div class="mt-1 space-y-0.5">
                                    `;
                                    data.shortcuts.forEach(sc => {
                                        html += `
                                            <a href="${sc.url}" class="search-palette-item flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-slate-800/80 transition-colors group">
                                                <div class="flex items-center gap-2.5 overflow-hidden">
                                                    <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-lime-400 shrink-0">
                                                        <i data-lucide="${sc.icon}" class="w-4 h-4"></i>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <span class="block text-xs font-bold text-slate-200 group-hover:text-lime-400 transition-colors truncate">${escapeHtml(sc.title)}</span>
                                                        <span class="block text-[10px] text-slate-400 truncate">${escapeHtml(sc.subtitle)}</span>
                                                    </div>
                                                </div>
                                                <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 uppercase tracking-wider shrink-0">${escapeHtml(sc.badge)}</span>
                                            </a>
                                        `;
                                    });
                                    html += `</div></div>`;
                                }

                                // 2. Clientes / Atletas
                                if (data.clients && data.clients.length > 0) {
                                    html += `
                                        <div class="p-2">
                                            <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                                <i data-lucide="users" class="w-3 h-3 text-lime-400"></i> Atletas / Clientes (${data.clients.length})
                                            </div>
                                            <div class="mt-1 space-y-0.5">
                                    `;
                                    data.clients.forEach(c => {
                                        const dniPill = c.dni ? `<span class="font-mono text-slate-400">CI: ${escapeHtml(c.dni)}</span>` : '';
                                        const phonePill = c.phone ? `<span>Tel: ${escapeHtml(c.phone)}</span>` : '';
                                        html += `
                                            <a href="${c.url}" class="search-palette-item flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-slate-800/80 transition-colors group">
                                                <div class="flex items-center gap-2.5 overflow-hidden">
                                                    <img src="${c.photo}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-700">
                                                    <div class="overflow-hidden">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-bold text-xs text-slate-200 group-hover:text-lime-400 transition-colors truncate">${escapeHtml(c.name)}</span>
                                                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-sm ${c.status_class}">${escapeHtml(c.membership_status)}</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                                            <span class="truncate max-w-[140px]">${escapeHtml(c.email)}</span>
                                                            ${dniPill ? `<span class="hidden sm:inline">&bull;</span> ${dniPill}` : ''}
                                                            ${phonePill ? `<span class="hidden sm:inline">&bull;</span> ${phonePill}` : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-950 px-1.5 py-0.5 rounded border border-slate-800 shrink-0">${escapeHtml(c.gym_name)}</span>
                                            </a>
                                        `;
                                    });
                                    html += `</div></div>`;
                                }

                                // 3. Personal & Staff
                                if (data.staff && data.staff.length > 0) {
                                    html += `
                                        <div class="p-2">
                                            <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                                <i data-lucide="shield-check" class="w-3 h-3 text-blue-400"></i> Equipo & Personal (${data.staff.length})
                                            </div>
                                            <div class="mt-1 space-y-0.5">
                                    `;
                                    data.staff.forEach(s => {
                                        html += `
                                            <a href="${s.url}" class="search-palette-item flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-slate-800/80 transition-colors group">
                                                <div class="flex items-center gap-2.5 overflow-hidden">
                                                    <img src="${s.photo}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-700">
                                                    <div class="overflow-hidden">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-bold text-xs text-slate-200 group-hover:text-blue-400 transition-colors truncate">${escapeHtml(s.name)}</span>
                                                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-sm border ${s.role_color}">${escapeHtml(s.role_label)}</span>
                                                        </div>
                                                        <span class="block text-[10px] text-slate-400 truncate">${escapeHtml(s.email)}</span>
                                                    </div>
                                                </div>
                                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-950 px-1.5 py-0.5 rounded border border-slate-800 shrink-0">${escapeHtml(s.gym_name)}</span>
                                            </a>
                                        `;
                                    });
                                    html += `</div></div>`;
                                }

                                // 4. Productos de Tienda
                                if (data.products && data.products.length > 0) {
                                    html += `
                                        <div class="p-2">
                                            <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                                <i data-lucide="package" class="w-3 h-3 text-amber-400"></i> Tienda & Productos (${data.products.length})
                                            </div>
                                            <div class="mt-1 space-y-0.5">
                                    `;
                                    data.products.forEach(p => {
                                        html += `
                                            <a href="${p.url}" class="search-palette-item flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-slate-800/80 transition-colors group">
                                                <div class="flex items-center gap-2.5 overflow-hidden">
                                                    <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-amber-400 shrink-0 font-mono font-bold text-[10px]">
                                                        <i data-lucide="tag" class="w-4 h-4"></i>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <span class="block text-xs font-bold text-slate-200 group-hover:text-amber-400 transition-colors truncate">${escapeHtml(p.name)}</span>
                                                        <span class="block text-[10px] text-slate-400">SKU: <span class="font-mono text-slate-300">${escapeHtml(p.sku)}</span> &bull; Stock: <strong class="${p.stock <= 5 ? 'text-rose-400' : 'text-slate-300'}">${p.stock} unid.</strong></span>
                                                    </div>
                                                </div>
                                                <span class="text-xs font-black text-lime-400 font-mono shrink-0">$${p.price}</span>
                                            </a>
                                        `;
                                    });
                                    html += `</div></div>`;
                                }

                                // 5. Rutinas y Dietas
                                if ((data.routines && data.routines.length > 0) || (data.meal_plans && data.meal_plans.length > 0)) {
                                    html += `
                                        <div class="p-2">
                                            <div class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                                <i data-lucide="dumbbell" class="w-3 h-3 text-purple-400"></i> Rutinas & Nutrición
                                            </div>
                                            <div class="mt-1 space-y-0.5">
                                    `;
                                    if (data.routines) {
                                        data.routines.forEach(r => {
                                            html += `
                                                <a href="${r.url}" class="search-palette-item flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-slate-800/80 transition-colors group">
                                                    <div class="flex items-center gap-2 overflow-hidden">
                                                        <i data-lucide="activity" class="w-3.5 h-3.5 text-purple-400 shrink-0"></i>
                                                        <span class="text-xs font-bold text-slate-200 group-hover:text-purple-400 transition-colors truncate">${escapeHtml(r.name)}</span>
                                                    </div>
                                                    <span class="text-[9px] font-bold text-slate-400 bg-slate-950 px-1.5 py-0.5 rounded border border-slate-800 shrink-0">${escapeHtml(r.duration)}</span>
                                                </a>
                                            `;
                                        });
                                    }
                                    if (data.meal_plans) {
                                        data.meal_plans.forEach(m => {
                                            html += `
                                                <a href="${m.url}" class="search-palette-item flex items-center justify-between gap-3 px-3 py-2 rounded-xl hover:bg-slate-800/80 transition-colors group">
                                                    <div class="flex items-center gap-2 overflow-hidden">
                                                        <i data-lucide="apple" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                                                        <span class="text-xs font-bold text-slate-200 group-hover:text-emerald-400 transition-colors truncate">${escapeHtml(m.name)}</span>
                                                    </div>
                                                    <span class="text-[9px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20 shrink-0">${escapeHtml(m.calories)}</span>
                                                </a>
                                            `;
                                        });
                                    }
                                    html += `</div></div>`;
                                }

                                // Footer: Full search prompt
                                html += `
                                    <div class="p-2.5 bg-slate-950/60 border-t border-slate-800/80 text-center">
                                        <button type="submit" form="global-search-form" class="w-full text-xs font-bold text-slate-400 hover:text-lime-400 py-1 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                                            <span>Ver todos los resultados para "${escapeHtml(query)}"</span>
                                            <kbd class="px-1.5 py-0.5 text-[9px] font-mono bg-slate-800 border border-slate-700 rounded text-slate-400">↵ Enter</kbd>
                                        </button>
                                    </div>
                                `;

                                resultsDropdown.innerHTML = html;
                                resultsDropdown.classList.remove('hidden');
                                if (window.lucide) window.lucide.createIcons();
                            })
                            .catch(err => {
                                console.error('Error fetching live search:', err);
                            });
                    }, 150);
                });

                // Keyboard Arrow Navigation
                searchInput.addEventListener('keydown', (e) => {
                    const items = resultsDropdown.querySelectorAll('.search-palette-item');
                    if (items.length === 0 || resultsDropdown.classList.contains('hidden')) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        currentHighlightIndex = (currentHighlightIndex + 1) % items.length;
                        updateHighlight(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        currentHighlightIndex = (currentHighlightIndex - 1 + items.length) % items.length;
                        updateHighlight(items);
                    } else if (e.key === 'Enter') {
                        if (currentHighlightIndex >= 0 && items[currentHighlightIndex]) {
                            e.preventDefault();
                            items[currentHighlightIndex].click();
                        }
                    } else if (e.key === 'Escape') {
                        resultsDropdown.classList.add('hidden');
                    }
                });

                function updateHighlight(items) {
                    items.forEach((item, idx) => {
                        if (idx === currentHighlightIndex) {
                            item.classList.add('active-item');
                            item.scrollIntoView({ block: 'nearest' });
                        } else {
                            item.classList.remove('active-item');
                        }
                    });
                }

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                        resultsDropdown.classList.add('hidden');
                    }
                });

                // Re-open on focus if query length is valid
                searchInput.addEventListener('focus', () => {
                    if (searchInput.value.trim().length >= 2 && resultsDropdown.innerHTML.trim().length > 0) {
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

        window.toggleNotificationsDropdown = function(event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            const dropdown = document.getElementById('notifications-dropdown');
            if (!dropdown) return;

            const isOpen = dropdown.classList.contains('open');
            if (isOpen) {
                dropdown.classList.remove('open');
            } else {
                dropdown.classList.add('open');
                window.loadNotifications();
            }
        };

        window.markAllNotificationsAsRead = async function(event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch('/notificaciones/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                // Update UI visually
                const unreadDot = document.getElementById('unread-dot');
                if (unreadDot) unreadDot.classList.add('hidden');
                
                const badge = document.getElementById('notifications-count-badge');
                if (badge) badge.textContent = '0 nuevas';

                const listEl = document.getElementById('notifications-list');
                if (listEl) {
                    listEl.querySelectorAll('[data-notification-item]').forEach(item => {
                        item.classList.add('opacity-60');
                        item.classList.remove('border-l-2', 'border-lime-500', 'border-amber-500', 'bg-slate-900/60');
                    });
                }
            } catch (err) {
                console.error('Error al marcar notificaciones como leídas:', err);
            }
        };

        // Close dropdown when clicking outside safely
        if (!window._notificationsClickListenerAttached) {
            document.addEventListener('click', function(e) {
                const container = document.getElementById('notifications-menu-container');
                const dropdown = document.getElementById('notifications-dropdown');
                if (container && dropdown && dropdown.classList.contains('open')) {
                    if (!container.contains(e.target)) {
                        dropdown.classList.remove('open');
                    }
                }
            });
            window._notificationsClickListenerAttached = true;
        }

        window.loadNotifications = async function() {
            const listEl = document.getElementById('notifications-list');
            if (!listEl) return;

            try {
                const response = await fetch('/api/notifications/unread', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) throw new Error('Network error');
                const data = await response.json();
                
                // Update unread dot & badge
                const unreadDot = document.getElementById('unread-dot');
                if (unreadDot) {
                    if (data.unread_count > 0) {
                        unreadDot.classList.remove('hidden');
                    } else {
                        unreadDot.classList.add('hidden');
                    }
                }

                const countBadge = document.getElementById('notifications-count-badge');
                if (countBadge) {
                    countBadge.textContent = `${data.unread_count || 0} nuevas`;
                }

                // Adjust title and link if superadmin
                const titleHeader = document.getElementById('notifications-dropdown-header-title');
                const footerLink = document.getElementById('notifications-dropdown-footer-link');
                if (data.is_superadmin_audit) {
                    if (titleHeader) titleHeader.textContent = 'Auditoría & Bitácora';
                    if (footerLink) {
                        footerLink.textContent = 'Ver toda la Auditoría & Bitácoras';
                        footerLink.href = '{{ route('superadmin.audit.index') }}';
                    }
                }
                
                if (!data.notifications || data.notifications.length === 0) {
                    const emptyText = data.is_superadmin_audit ? 'No hay registros de auditoría recientes.' : 'Estás al día. No tienes notificaciones pendientes.';
                    listEl.innerHTML = `
                        <div class="p-8 text-center text-xs text-slate-400 flex flex-col items-center gap-2.5">
                            <div class="w-10 h-10 rounded-full bg-slate-800/80 border border-slate-700 flex items-center justify-center text-slate-500">
                                <i data-lucide="bell-off" class="w-5 h-5"></i>
                            </div>
                            <span class="font-bold text-slate-300">${emptyText}</span>
                        </div>
                    `;
                    if (window.lucide) window.lucide.createIcons();
                    return;
                }
                
                let html = '';
                data.notifications.forEach(n => {
                    const isAudit = n.type === 'audit_log';
                    const isUnread = !n.is_read;
                    const readClass = isUnread 
                        ? 'bg-slate-800/30 border-l-3 ' + (isAudit ? 'border-amber-400' : 'border-lime-400') 
                        : 'opacity-65 hover:opacity-90';
                    
                    let iconColor = 'text-lime-400 bg-lime-500/10 border-lime-500/20';
                    let icon = 'bell';
                    
                    if (isAudit) {
                        if (n.action_type === 'INSERT') {
                            iconColor = 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
                            icon = 'plus-circle';
                        } else if (n.action_type === 'UPDATE') {
                            iconColor = 'text-amber-400 bg-amber-500/10 border-amber-500/20';
                            icon = 'edit-3';
                        } else if (n.action_type === 'DELETE' || n.action_type === 'LOGIN_FAILED') {
                            iconColor = 'text-rose-400 bg-rose-500/10 border-rose-500/20';
                            icon = n.action_type === 'DELETE' ? 'trash-2' : 'alert-octagon';
                        } else {
                            iconColor = 'text-blue-400 bg-blue-500/10 border-blue-500/20';
                            icon = 'file-text';
                        }
                    } else if (n.type === 'membership_expiry' || n.type === 'payment_reminder') {
                        iconColor = 'text-amber-400 bg-amber-500/10 border-amber-500/20';
                        icon = 'alert-triangle';
                    } else if (n.type === 'new_routine') {
                        iconColor = 'text-purple-400 bg-purple-500/10 border-purple-500/20';
                        icon = 'dumbbell';
                    } else if (n.type === 'achievement') {
                        iconColor = 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20';
                        icon = 'trophy';
                    }
                    
                    const timeAgo = formatTimeAgo(new Date(n.createdAt));
                    const contextPill = n.recipient_name ? `<span class="inline-block text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold mr-1">${escapeHtml(n.recipient_name)}</span>` : '';
                    const gymPill = n.gym_name ? `<span class="inline-block text-[9px] px-1.5 py-0.5 rounded bg-slate-950 text-slate-400 font-bold uppercase tracking-wider border border-slate-800">${escapeHtml(n.gym_name)}</span>` : '';
                    const clickUrl = n.url || `/notificaciones/${n.id}/read`;

                    html += `
                        <a href="${clickUrl}" data-notification-item class="block p-3.5 hover:bg-slate-800/60 transition-all ${readClass} group">
                            <div class="flex gap-3 items-start">
                                <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center border ${iconColor}">
                                    <i data-lucide="${icon}" class="w-4 h-4"></i>
                                </div>
                                <div class="space-y-1 min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="block text-xs font-bold text-slate-100 group-hover:text-lime-400 transition-colors truncate">${escapeHtml(n.title)}</span>
                                        ${isUnread ? '<span class="w-2 h-2 rounded-full bg-lime-400 shrink-0"></span>' : ''}
                                    </div>
                                    <span class="block text-[11px] text-slate-400 line-clamp-2 leading-relaxed">${escapeHtml(n.body || '')}</span>
                                    <div class="flex items-center gap-1.5 mt-1.5 text-[9px] text-slate-400 font-medium">
                                        <span class="flex items-center gap-1 text-slate-400"><i data-lucide="clock" class="w-2.5 h-2.5"></i> ${timeAgo}</span>
                                        ${contextPill}
                                        ${gymPill}
                                    </div>
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
                if (listEl) listEl.innerHTML = '<div class="p-6 text-center text-xs text-rose-400 font-bold">Error al cargar notificaciones.</div>';
            }
        };

        // Load notifications status on initial boot
        window.loadNotifications();

        function formatTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 1) return 'hace un momento';
            if (diffMins < 60) return `hace ${diffMins} min`;
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `hace ${diffHours} hr`;
            const diffDays = Math.floor(diffHours / 24);
            return `hace ${diffDays} d`;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Standard navigation helper
        window.loadUrl = function(url) {
            if (url && url !== window.location.href) {
                window.location.href = url;
            } else {
                window.location.reload();
            }
        };
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

    <!-- ================= UNIVERSAL FLOATING TOAST NOTIFICATIONS (TOP-RIGHT) ================= -->
    <div id="global-toast-container" class="fixed top-5 right-5 z-[999999] flex flex-col gap-3 pointer-events-none max-w-sm sm:max-w-md w-full px-4 sm:px-0"></div>

    <script>
        window.showToast = function(message, type = 'success', duration = 4000) {
            if (!message) return;
            const container = document.getElementById('global-toast-container');
            if (!container) return;

            const configs = {
                success: {
                    icon: 'check-circle-2',
                    border: 'border-lime-500/35',
                    bg: 'bg-slate-900/95',
                    badgeBg: 'bg-lime-500/10 border-lime-500/25 text-lime-400',
                    barBg: 'bg-gradient-to-r from-lime-500 to-emerald-500',
                    title: 'Éxito',
                    glow: 'shadow-lime-500/10',
                    iconColor: 'text-lime-400'
                },
                error: {
                    icon: 'alert-circle',
                    border: 'border-rose-500/35',
                    bg: 'bg-slate-900/95',
                    badgeBg: 'bg-rose-500/10 border-rose-500/25 text-rose-400',
                    barBg: 'bg-gradient-to-r from-rose-500 to-red-600',
                    title: 'Error',
                    glow: 'shadow-rose-500/10',
                    iconColor: 'text-rose-400'
                },
                warning: {
                    icon: 'alert-triangle',
                    border: 'border-amber-500/35',
                    bg: 'bg-slate-900/95',
                    badgeBg: 'bg-amber-500/10 border-amber-500/25 text-amber-400',
                    barBg: 'bg-gradient-to-r from-amber-500 to-yellow-500',
                    title: 'Atención',
                    glow: 'shadow-amber-500/10',
                    iconColor: 'text-amber-400'
                },
                info: {
                    icon: 'info',
                    border: 'border-cyan-500/35',
                    bg: 'bg-slate-900/95',
                    badgeBg: 'bg-cyan-500/10 border-cyan-500/25 text-cyan-400',
                    barBg: 'bg-gradient-to-r from-cyan-500 to-sky-500',
                    title: 'Información',
                    glow: 'shadow-cyan-500/10',
                    iconColor: 'text-cyan-400'
                }
            };

            const cfg = configs[type] || configs.success;
            const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);

            const toastEl = document.createElement('div');
            toastEl.id = toastId;
            toastEl.className = `pointer-events-auto w-full ${cfg.bg} border ${cfg.border} rounded-2xl p-4 shadow-2xl ${cfg.glow} backdrop-blur-xl transition-all duration-300 transform translate-x-12 opacity-0 flex flex-col gap-2 relative overflow-hidden`;

            const escapeFunc = typeof escapeHtml === 'function' ? escapeHtml : (str) => {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return String(str).replace(/[&<>"']/g, m => map[m]);
            };

            toastEl.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl ${cfg.badgeBg} border shrink-0 mt-0.5 shadow-sm">
                        <i data-lucide="${cfg.icon}" class="w-4 h-4 ${cfg.iconColor}"></i>
                    </div>
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-200">${cfg.title}</span>
                        </div>
                        <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5 break-words">${escapeFunc(message)}</p>
                    </div>
                    <button type="button" onclick="window.dismissToast('${toastId}')" class="text-slate-500 hover:text-slate-200 p-1 rounded-lg hover:bg-slate-800 transition-colors shrink-0 -mr-1 -mt-1" title="Cerrar">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div class="w-full bg-slate-950/70 h-1 rounded-full overflow-hidden mt-1">
                    <div id="${toastId}-bar" class="${cfg.barBg} h-full rounded-full w-full" style="transition: width ${duration}ms linear;"></div>
                </div>
            `;

            container.appendChild(toastEl);
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Animate in
            requestAnimationFrame(() => {
                toastEl.classList.remove('translate-x-12', 'opacity-0');
                toastEl.classList.add('translate-x-0', 'opacity-100');
                const bar = document.getElementById(`${toastId}-bar`);
                if (bar) {
                    requestAnimationFrame(() => {
                        bar.style.width = '0%';
                    });
                }
            });

            // Auto dismiss timer
            const timeout = setTimeout(() => {
                window.dismissToast(toastId);
            }, duration);

            toastEl._timer = timeout;
        };

        window.dismissToast = function(toastId) {
            const toastEl = document.getElementById(toastId);
            if (!toastEl) return;
            if (toastEl._timer) clearTimeout(toastEl._timer);

            toastEl.classList.remove('translate-x-0', 'opacity-100');
            toastEl.classList.add('translate-x-12', 'opacity-0');
            setTimeout(() => {
                if (toastEl.parentNode) {
                    toastEl.parentNode.removeChild(toastEl);
                }
            }, 300);
        };

        window.toast = window.showToast;
        window.showNotificationToast = window.showToast;

        // Auto-show Laravel session flash messages as toasts on page load
        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                window.showToast(@json(session('success')), 'success');
            @endif
            @if(session('error'))
                window.showToast(@json(session('error')), 'error');
            @endif
            @if(session('warning'))
                window.showToast(@json(session('warning')), 'warning');
            @endif
            @if(session('info'))
                window.showToast(@json(session('info')), 'info');
            @endif
            @if(session('status'))
                window.showToast(@json(session('status')), 'info');
            @endif
            @if(isset($errors) && $errors->any())
                @foreach($errors->all() as $error)
                    window.showToast(@json($error), 'error');
                @endforeach
            @endif
        });
    </script>

    <div>
        @stack('modals')
    </div>
    @stack('scripts')
</body>
</html>
