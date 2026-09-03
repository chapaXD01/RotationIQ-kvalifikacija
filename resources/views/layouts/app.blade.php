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
                    radial-gradient(ellipse 60% 40% at 80% 80%, rgba(59, 130, 246, 0.12) 0%, transparent 55%),
                    radial-gradient(ellipse 40% 30% at 60% 30%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                    url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.018'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
        </style>
    </head>
    <body class="app-bg font-sans antialiased" style="margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh;">

        <div style="flex: 1; overflow-y: auto;">
            @include('layouts.navigation')

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
                        <span class="text-2xl">🏐</span>
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
                            <span class="text-sm text-white">rotationIQ@gmail.com</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.15);border:0.5px solid rgba(96,165,250,0.25);">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-white">+371 69-999-999</span>
                        </div>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-6 pt-5 text-center text-xs text-white" style="border-top:1px solid rgba(255,255,255,0.07);">
                    &copy; 2026 RotationIQ. All rights reserved.
                </div>
            </div>
        </footer>
    </body>
</html>