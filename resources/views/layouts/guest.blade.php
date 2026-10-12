<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script>
            document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'dark');
        </script>

        <title>RotationIQ</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .guest-bg {
                background-color: var(--bg-page);
                background-image: var(--bg-page-image);
            }

            @keyframes brand-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            .brand-ball { animation: brand-spin 7s linear infinite; transform-origin: 50% 50%; }
            .brand-ball-link:hover .brand-ball { animation-duration: 1.1s; }
        </style>
    </head>
    <body
        class="guest-bg font-sans antialiased"
        style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; position: relative;"
        x-data="{
            theme: localStorage.getItem('theme') || 'dark',
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', this.theme);
                document.documentElement.setAttribute('data-theme', this.theme);
            }
        }"
    >
        <x-theme-toggle class="!fixed top-4 right-4" />

        <!-- Logo -->
        <div class="mb-8 text-center">
            <a href="/" class="brand-ball-link inline-flex flex-col items-center gap-3">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background: rgba(37,99,235,0.2); border: 1px solid rgba(96,165,250,0.3); box-shadow: 0 0 40px rgba(37,99,235,0.15);">
                    <x-volleyball-logo class="w-9 h-9" />
                </div>
                <div>
                    <p class="font-bold text-2xl text-white tracking-tight">RotationIQ</p>
                    <p class="text-sm text-white mt-0.5">Volleyball rotation toolkit</p>
                </div>
            </a>
        </div>

        <!-- Card -->
        <div class="w-full max-w-md rounded-2xl overflow-hidden" style="background: var(--bg-card); border: 1px solid var(--border-subtle); backdrop-filter: blur(20px); box-shadow: 0 25px 50px var(--shadow-card);">
            <div class="px-8 py-8">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-8 text-xs text-white">&copy; 2026 RotationIQ</p>
    </body>
</html>