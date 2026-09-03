<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
            .guest-bg {
                background-color: #0a1628;
                background-image:
                    radial-gradient(ellipse 70% 60% at 15% 15%, rgba(37, 99, 235, 0.22) 0%, transparent 60%),
                    radial-gradient(ellipse 50% 50% at 85% 75%, rgba(59, 130, 246, 0.15) 0%, transparent 55%),
                    radial-gradient(ellipse 35% 35% at 50% 50%, rgba(99, 102, 241, 0.07) 0%, transparent 50%),
                    url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.018'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
        </style>
    </head>
    <body class="guest-bg font-sans antialiased" style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px;">

        <!-- Logo -->
        <div class="mb-8 text-center">
            <a href="/" class="inline-flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl" style="background: rgba(37,99,235,0.2); border: 1px solid rgba(96,165,250,0.3); box-shadow: 0 0 40px rgba(37,99,235,0.15);">
                    🏐
                </div>
                <div>
                    <p class="font-bold text-2xl text-white tracking-tight">RotationIQ</p>
                    <p class="text-sm text-white mt-0.5">Volleyball rotation toolkit</p>
                </div>
            </a>
        </div>

        <!-- Card -->
        <div class="w-full max-w-md rounded-2xl overflow-hidden" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(20px); box-shadow: 0 25px 50px rgba(0,0,0,0.4);">
            <div class="px-8 py-8">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-8 text-xs text-white">&copy; 2026 RotationIQ</p>
    </body>
</html>