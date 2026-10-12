{{-- Dark/light toggle. Expects an ancestor Alpine scope exposing `theme` ('dark'|'light')
     and a `toggleTheme()` method — the shared <body> x-data in app.blade.php provides
     this everywhere auth/guest pages render; guest.blade.php defines its own copy since
     it's a separate standalone layout. --}}
@props(['class' => ''])

<button
    type="button"
    @click="toggleTheme()"
    title="Switch theme"
    aria-label="Switch between dark and light mode"
    {{ $attributes->merge(['class' => 'p-2 rounded-lg transition hover:bg-white/10 ' . $class]) }}
>
    <svg x-show="theme === 'dark'" x-cloak class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <svg x-show="theme === 'light'" x-cloak class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 1020.354 15.354z"/>
    </svg>
</button>
