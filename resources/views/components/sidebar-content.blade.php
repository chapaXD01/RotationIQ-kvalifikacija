@props(['collapsible' => true])

<div class="flex items-center gap-2.5 px-3 h-16 shrink-0">
    <a href="{{ route('dashboard') }}" class="brand-ball-link flex items-center gap-2.5 shrink-0 min-w-0">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background: rgba(37,99,235,0.25); border: 1px solid rgba(96,165,250,0.3);">
            <x-volleyball-logo class="w-5 h-5" />
        </div>
        @if ($collapsible)
            <span x-show="!sidebarCollapsed" x-cloak class="font-bold text-base text-white tracking-tight whitespace-nowrap overflow-hidden">RotationIQ</span>
        @else
            <span class="font-bold text-base text-white tracking-tight whitespace-nowrap overflow-hidden">RotationIQ</span>
        @endif
    </a>
</div>

<nav class="flex-1 flex flex-col gap-1 px-2 py-2 overflow-y-auto overflow-x-hidden">
    @php
        $navItems = [
            ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => 'Start'],
            ['route' => 'attack.index', 'pattern' => 'attack.*', 'label' => 'Attack'],
            ['route' => 'defence.index', 'pattern' => 'defence.*', 'label' => 'Defence'],
            ['route' => 'movingplayers.index', 'pattern' => 'movingplayers.*', 'label' => 'Movements'],
            ['route' => 'teams.index', 'pattern' => 'teams.*', 'label' => 'Teams'],
        ];
    @endphp

    @foreach ($navItems as $item)
        @php $active = request()->routeIs($item['pattern']); @endphp
        <a
            href="{{ route($item['route']) }}"
            title="{{ $item['label'] }}"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 border-l-2 transition {{ $active ? 'bg-blue-500/15 border-blue-400' : 'border-transparent hover:bg-white/5' }}"
            @if ($collapsible) :class="sidebarCollapsed ? 'justify-center px-0' : ''" @endif
        >
            @switch($item['label'])
                @case('Start')
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="{{ $active ? '#60a5fa' : '#94a3b8' }}" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>
                    @break
                @case('Attack')
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="{{ $active ? '#60a5fa' : '#94a3b8' }}" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    @break
                @case('Defence')
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="{{ $active ? '#60a5fa' : '#94a3b8' }}" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @break
                @case('Movements')
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="{{ $active ? '#60a5fa' : '#94a3b8' }}" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @break
                @case('Teams')
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="{{ $active ? '#60a5fa' : '#94a3b8' }}" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 3.13a4 4 0 00-3-7.13"/></svg>
            @endswitch

            @if ($collapsible)
                <span x-show="!sidebarCollapsed" x-cloak class="text-sm whitespace-nowrap overflow-hidden {{ $active ? 'font-bold text-white' : 'font-medium text-slate-300' }}">{{ $item['label'] }}</span>
            @else
                <span class="text-sm whitespace-nowrap overflow-hidden {{ $active ? 'font-bold text-white' : 'font-medium text-slate-300' }}">{{ $item['label'] }}</span>
            @endif
        </a>
    @endforeach
</nav>

@if ($collapsible)
    <button
        type="button"
        @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
        class="hidden sm:flex items-center gap-2 mx-2 mb-2 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition"
        :class="sidebarCollapsed ? 'justify-center px-0' : ''"
    >
        <svg class="w-[18px] h-[18px] shrink-0 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
        <span x-show="!sidebarCollapsed" x-cloak class="text-sm font-medium whitespace-nowrap overflow-hidden">Collapse</span>
    </button>
@endif

<div class="px-2 pb-3 shrink-0">
    <div class="flex items-center gap-2.5 rounded-xl p-2.5 overflow-hidden" style="background: rgba(255,255,255,0.045); border: 1px solid rgba(255,255,255,0.08);">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0" style="background: rgba(37,99,235,0.6);">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1" @if ($collapsible) x-show="!sidebarCollapsed" x-cloak @endif>
            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
            <a href="{{ route('profile.edit') }}" class="text-[11px] font-semibold uppercase tracking-wide text-blue-300 hover:text-blue-200 transition">{{ ucfirst(Auth::user()->role) }}</a>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="shrink-0" @if ($collapsible) x-show="!sidebarCollapsed" x-cloak @endif>
            @csrf
            <button type="submit" title="Log out" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 8H6a2 2 0 01-2-2V6a2 2 0 012-2h7"/></svg>
            </button>
        </form>
    </div>
</div>
