<nav x-data="{ open: false }" class="relative" style="background: rgba(10, 22, 40, 0.75); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.08); z-index: 50;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left: Logo + Nav links -->
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg" style="background: rgba(37,99,235,0.25); border: 1px solid rgba(96,165,250,0.3);">
                        🏐
                    </div>
                    <span class="font-bold text-base text-white tracking-tight">RotationIQ</span>
                </a>

                <!-- Desktop nav links -->
                <div class="hidden sm:flex items-center gap-1">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                        class="!text-white hover:!text-white !rounded-lg !px-3 !py-1.5 !text-sm transition">
                        {{ __('Start') }}
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('attack.index')" :active="request()->routeIs('attack.*')"
                            class="!text-white hover:!text-white !rounded-lg !px-3 !py-1.5 !text-sm transition">
                            {{ __('Attack') }}
                        </x-nav-link>
                        <x-nav-link :href="route('defence.index')" :active="request()->routeIs('defence.*')"
                            class="!text-white hover:!text-white !rounded-lg !px-3 !py-1.5 !text-sm transition">
                            {{ __('Defence') }}
                        </x-nav-link>
                        <x-nav-link :href="route('movingplayers.index')" :active="request()->routeIs('movingplayers.*')"
                            class="!text-white hover:!text-white !rounded-lg !px-3 !py-1.5 !text-sm transition">
                            {{ __('Movements') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Right: User dropdown or guest buttons -->
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-medium text-white hover:text-white transition" style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: rgba(37,99,235,0.6);">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="px-4 py-1.5 rounded-lg text-sm font-medium text-white hover:text-white transition" style="border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.05);">
                        {{ __('Log In') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-1.5 rounded-lg text-sm font-semibold text-white transition" style="background: #2563eb; border: 1px solid rgba(96,165,250,0.4); box-shadow: 0 2px 12px rgba(37,99,235,0.35);">
                        {{ __('Register') }}
                    </a>
                @endguest
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-white hover:text-white hover:bg-white/10 focus:outline-none transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden" style="border-top: 1px solid rgba(255,255,255,0.08);">
        <div class="px-4 pt-3 pb-2 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Start') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('attack.index')" :active="request()->routeIs('attack.*')">
                    {{ __('Attack') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('defence.index')" :active="request()->routeIs('defence.*')">
                    {{ __('Defence') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('movingplayers.index')" :active="request()->routeIs('movingplayers.*')">
                    {{ __('Movements') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        @auth
            <div class="px-4 pt-3 pb-4" style="border-top: 1px solid rgba(255,255,255,0.08);">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background: rgba(37,99,235,0.6);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-white">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth

        @guest
            <div class="px-4 pt-3 pb-4 space-y-2" style="border-top: 1px solid rgba(255,255,255,0.08);">
                <x-responsive-nav-link :href="route('login')">{{ __('Log In') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">{{ __('Register') }}</x-responsive-nav-link>
            </div>
        @endguest
    </div>
</nav>