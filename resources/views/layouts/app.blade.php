<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100%;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>RotationIQ</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .app-bg {
                background-color: #0a1628;
                background-image:
                    radial-gradient(ellipse 80% 50% at 20% 10%, rgba(37, 99, 235, 0.18) 0%, transparent 60%),
                    radial-gradient(ellipse 75% 55% at 85% 15%, rgba(217, 160, 91, 0.26) 0%, transparent 60%),
                    radial-gradient(ellipse 40% 30% at 60% 30%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                    url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.018'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                /* the radial blooms are positioned as % of the whole background area — without
                   "fixed" they anchor to the full document height, so a taller page (like the
                   dashboard once it has more sections) drags them down into view somewhere
                   they were never meant to show. Anchoring to the viewport keeps them stable. */
                background-attachment: fixed;
            }

            @keyframes brand-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            .brand-ball { animation: brand-spin 7s linear infinite; transform-origin: 50% 50%; }
            .brand-ball-link:hover .brand-ball { animation-duration: 1.1s; }
        </style>
    </head>
    <body
        class="app-bg font-sans antialiased"
        style="margin: 0; padding: 0;"
        x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', mobileSidebarOpen: false }"
    >
        <div style="display: flex; min-height: 100vh;">

            @auth
                <!-- Desktop collapsible sidebar -->
                <aside
                    :class="sidebarCollapsed ? 'sm:w-20' : 'sm:w-64'"
                    class="hidden sm:flex sm:flex-col shrink-0 transition-all duration-200"
                    style="background: rgba(0,0,0,0.22); border-right: 1px solid rgba(255,255,255,0.08);"
                >
                    <x-sidebar-content />
                </aside>

                <!-- Mobile off-canvas sidebar -->
                <div x-show="mobileSidebarOpen" x-cloak class="fixed inset-0 z-50 sm:hidden" role="dialog" aria-modal="true">
                    <div
                        class="fixed inset-0 bg-black/60"
                        x-show="mobileSidebarOpen"
                        x-transition.opacity
                        @click="mobileSidebarOpen = false"
                    ></div>
                    <aside
                        class="relative flex flex-col w-64 h-full"
                        style="background: #0a1628; border-right: 1px solid rgba(255,255,255,0.08);"
                        x-show="mobileSidebarOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        @click.outside="mobileSidebarOpen = false"
                    >
                        <x-sidebar-content :collapsible="false" />
                    </aside>
                </div>
            @endauth

            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; min-height: 100vh;">

                <!-- Top bar: shown always for guests, hamburger-only strip for auth users on mobile -->
                <div
                    class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 {{ auth()->check() ? 'sm:hidden' : '' }}"
                    style="background: rgba(10, 22, 40, 0.75); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.08); z-index: 50;"
                >
                    <div class="flex items-center gap-3">
                        @auth
                            <button @click="mobileSidebarOpen = true" class="p-2 -ml-2 rounded-lg text-white hover:bg-white/10 transition" aria-label="Open menu">
                                <svg class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                        @endauth
                        <a href="{{ route('dashboard') }}" class="brand-ball-link flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(37,99,235,0.25); border: 1px solid rgba(96,165,250,0.3);">
                                <x-volleyball-logo class="w-4 h-4" />
                            </div>
                            <span class="font-bold text-sm text-white tracking-tight">RotationIQ</span>
                        </a>
                    </div>
                    @guest
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium text-white hover:text-white transition" style="border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.05);">
                                {{ __('Log In') }}
                            </a>
                            <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white transition" style="background: #2563eb; border: 1px solid rgba(96,165,250,0.4); box-shadow: 0 2px 12px rgba(37,99,235,0.35);">
                                {{ __('Register') }}
                            </a>
                        </div>
                    @endguest
                </div>

                <div style="flex: 1; overflow-y: auto;">
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="relative" style="background-color: rgba(255,255,255,0.04); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.08); z-index: 10;">
                            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>

                <!-- Footer -->
                <footer class="mt-auto w-full" style="background-color: rgba(0,0,0,0.35); backdrop-filter: blur(12px); border-top: 1px solid rgba(255,255,255,0.08);">
                    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                            <!-- Brand -->
                            <div class="flex items-center gap-3">
                                <span class="brand-ball-link" style="display: inline-flex; width: 28px; height: 28px;">
                                    <x-volleyball-logo class="w-7 h-7" />
                                </span>
                                <div>
                                    <p class="font-bold text-white text-sm">RotationIQ</p>
                                    <p class="text-xs text-white">Volleyball rotation toolkit</p>
                                </div>
                            </div>

                            <!-- Quick Links -->
                            <div class="flex flex-col gap-2">
                                <p class="text-xs font-semibold text-white uppercase tracking-wider">Quick Links</p>
                                <a href="/" class="text-sm text-white hover:text-blue-400 transition">About Us</a>
                            </div>

                            <!-- Contact -->
                            <div class="flex flex-col sm:flex-row gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.15);border:0.5px solid rgba(96,165,250,0.25);">
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-white">petersonsrenars0gmail.com</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.15);border:0.5px solid rgba(96,165,250,0.25);">
                                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-white">+371 25-691-978</span>
                                </div>
                            </div>
                        </div>

                        <!-- Copyright -->
                        <div class="mt-6 pt-5 text-center text-xs text-white" style="border-top:1px solid rgba(255,255,255,0.07);">
                            &copy; 2026 RotationIQ. All rights reserved.
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>