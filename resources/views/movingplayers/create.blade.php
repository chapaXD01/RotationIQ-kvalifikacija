<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-purple-600/20 border border-purple-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl text-white tracking-tight">
                {{ __('Create Movement Animation') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <p class="text-xs font-semibold uppercase tracking-widest text-purple-400/70 mb-4 px-1">
                ⚡ New Movement Setup
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
                    <li><strong class="text-white">Click a player</strong> to select them — they'll get a yellow outline</li>
                    <li><strong class="text-white">Click anywhere on the court</strong> to set their destination (black dot)</li>
                    <li>Repeat for as many players as needed</li>
                    <li>Hit <strong class="text-white">Animate Movement</strong> to watch them all move together</li>
                    <li>Name it and hit <strong class="text-white">Save Movement</strong></li>
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
                                <div class="player" style="top:78px;  left:61px;" data-role="RS" data-pos="4">RS</div>
                                <div class="player" style="top:78px;  left:228px;" data-role="MB" data-pos="3">MB</div>
                                <div class="player" style="top:78px;  left:395px;" data-role="OH" data-pos="2">OH</div>
                                <div class="player" style="top:278px; left:61px;" data-role="OH" data-pos="5">OH</div>
                                <div class="player" style="top:278px; left:228px;" data-role="L"  data-pos="6">L</div>
                                <div class="player" style="top:278px; left:395px;" data-role="S"  data-pos="1">S</div>
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

                        {{-- Action buttons --}}
                        <div class="flex flex-col gap-3">
                            <button id="animateBtn"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-purple-600 hover:bg-purple-500 border border-purple-400/40 shadow-lg shadow-purple-900/30 transition">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Animate Movement
                            </button>

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

                        {{-- Status info --}}
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

                        {{-- Save section --}}
                        <div class="flex flex-col gap-3">
                            <div>
                                <label for="movement-name" class="block text-xs font-semibold uppercase tracking-widest text-purple-300/80 mb-2">
                                    {{ __('Movement Name') }}
                                </label>
                                <input type="text" id="movement-name" placeholder="e.g. Block & Cover"
                                       class="w-full px-4 py-2.5 rounded-xl text-white text-sm placeholder-white/30 bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-purple-500/60 focus:border-purple-500/40 transition">
                            </div>
                            <button onclick="saveNewMovement()"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-purple-600 hover:bg-purple-500 border border-purple-400/40 shadow-lg shadow-purple-900/30 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Movement
                            </button>
                            <a href="{{ route('movingplayers.index') }}"
                               class="w-full flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition">
                                Cancel
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
    <script>
        function saveNewMovement() {
            const name = document.getElementById('movement-name').value;
            if (!name) { alert("Please enter a movement name"); return; }
            const players = [];
            document.querySelectorAll('#court .player').forEach(player => {
                players.push({ role: player.dataset.role, pos: player.dataset.pos, top: parseFloat(player.style.top), left: parseFloat(player.style.left) });
            });
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch('/movingplayers', {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
                body: JSON.stringify({ name, players })
            })
            .then(r => { if (!r.ok) throw new Error("Server error"); return r.json(); })
            .then(() => { window.location.href = "/movingplayers"; })
            .catch(() => { alert("Failed to save movement"); });
        }
    </script>
    <script src="{{ asset('script.js') }}"></script>
</x-app-layout>