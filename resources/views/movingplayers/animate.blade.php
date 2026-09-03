<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-purple-600/20 border border-purple-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-white uppercase tracking-widest font-semibold">Animating</p>
                    <h2 class="font-bold text-xl text-white tracking-tight leading-tight">{{ $movement->name }}</h2>
                </div>
            </div>
            <a href="{{ route('movingplayers.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back to List') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <p class="text-xs font-semibold uppercase tracking-widest text-purple-400/70 mb-4 px-1">
                ⚡ Player Movement Animator
            </p>

            {{-- How-to banner --}}
            <div class="mb-6 rounded-2xl border border-purple-500/20 bg-purple-500/8 backdrop-blur-md p-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-white">How to use</span>
                </div>
                <ol class="text-white text-xs space-y-1 list-decimal list-inside">
                    <li><strong class="text-white">Click a player</strong> to select them — yellow outline appears</li>
                    <li><strong class="text-white">Click anywhere on the court</strong> to mark their destination (black dot)</li>
                    <li>Set destinations for as many players as needed</li>
                    <li>Hit <strong class="text-white">Animate Movement</strong> to watch all players move together</li>
                </ol>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Court --}}
                <div class="lg:col-span-2 rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span>
                        <span class="text-sm font-semibold text-white tracking-wide">Court View</span>
                        <span class="ml-auto text-xs text-white">Click player → click destination</span>
                    </div>
                    <div class="p-6 flex justify-center items-center min-h-[440px]">
                        <link rel="stylesheet" href="{{ asset('style.css') }}">
                        <div id="courtContainer" style="position: relative; display: inline-block;">
                            <div id="court" style="margin: 0; padding: 0; cursor: crosshair;">
                                <div class="zones">
                                    <div class="zone">4</div><div class="zone">3</div><div class="zone">2</div>
                                    <div class="zone">5</div><div class="zone">6</div><div class="zone">1</div>
                                </div>

                                @php $players = json_decode($movement->players, true); @endphp
                                @foreach ($players as $player)
                                    <div class="player"
                                         data-pos="{{ $player['pos'] }}"
                                         data-role="{{ $player['role'] }}"
                                         style="top: {{ $player['top'] }}px; left: {{ $player['left'] }}px">
                                        {{ $player['role'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Controls --}}
                <div class="rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md flex flex-col">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400 inline-block"></span>
                        <span class="text-sm font-semibold text-white tracking-wide">Controls</span>
                    </div>

                    <div class="p-6 flex flex-col gap-4 flex-1">

                        {{-- Primary action --}}
                        <button id="animateBtn"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-white bg-purple-600 hover:bg-purple-500 border border-purple-400/40 shadow-lg shadow-purple-900/30 transition">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Animate Movement
                        </button>

                        {{-- Secondary actions --}}
                        <div class="flex flex-col gap-2">
                            <button id="rotateBtn"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-purple-600/25 hover:bg-purple-600/45 border border-purple-500/30 transition">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Rotate Position
                            </button>

                            <button id="resetBtn"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-700/60 hover:bg-slate-700 border border-white/10 hover:border-white/20 transition">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                                Reset All
                            </button>

                            <button id="clearDestinationsBtn"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:text-white/90 bg-white/5 hover:bg-white/10 border border-white/10 transition">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Clear Destinations
                            </button>
                        </div>

                        <div class="border-t border-white/10"></div>

                        {{-- Live status --}}
                        <div class="flex flex-col gap-3">
                            <div class="rounded-xl bg-white/5 border border-white/10 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-widest text-white mb-1">Selected Player</p>
                                <div id="selectedInfo" class="text-sm font-medium text-white">None selected</div>
                            </div>
                            <div class="rounded-xl bg-white/5 border border-white/10 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-widest text-white mb-1">Destinations Set</p>
                                <div id="destinationCount" class="text-xl font-bold text-white">0 / 6</div>
                            </div>
                        </div>

                        <div class="border-t border-white/10"></div>

                        {{-- Quick links --}}
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('movingplayers.edit', $movement->id) }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-yellow-600/25 hover:bg-yellow-500/45 border border-yellow-500/30 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit this Movement
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        #courtContainer #court { cursor: crosshair; user-select: none; }
        #courtContainer .player { transition: left 0.6s ease, top 0.6s ease; user-select: none; cursor: pointer; }
        #courtContainer .player:hover { filter: brightness(1.1); }
        #courtContainer .player.selected { outline: 3px solid #fbbf24; box-shadow: 0 0 0 2px rgba(251,191,36,0.5); }
        .destination-marker { width: 12px; height: 12px; background: #000; border-radius: 50%; position: absolute; pointer-events: none; z-index: 10; }
        .animation-in-progress { pointer-events: none; }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('script.js') }}"></script>
</x-app-layout>