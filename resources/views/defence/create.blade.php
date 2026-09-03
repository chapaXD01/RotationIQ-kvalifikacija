<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-green-600/20 border border-green-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl text-white tracking-tight">
                {{ __('Create Defence Rotation') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <p class="text-xs font-semibold uppercase tracking-widest text-green-400/70 mb-4 px-1">
                🛡️ New Rotation Setup
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Court panel --}}
                <div class="lg:col-span-2 rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span>
                        <span class="text-sm font-semibold text-white tracking-wide">Court View</span>
                        <span class="ml-auto text-xs text-white">Drag players to position</span>
                    </div>
                    <div class="p-6 flex justify-center items-center min-h-[440px]">
                        <link rel="stylesheet" href="{{ asset('style.css') }}">
                        <div id="court" style="margin: 0; padding: 0;">
                            <div class="zones">
                                <div class="zone">4</div>
                                <div class="zone">3</div>
                                <div class="zone">2</div>
                                <div class="zone">5</div>
                                <div class="zone">6</div>
                                <div class="zone">1</div>
                            </div>
                            <svg id="lines" width="500" height="400"
                                 style="position:absolute; top:0; left:0; pointer-events:none;"></svg>
                            <div class="player" data-pos="4" data-role="RS" style="top:78px; left:61px;">RS</div>
                            <div class="player" data-pos="3" data-role="MB" style="top:78px; left:228px;">MB</div>
                            <div class="player" data-pos="2" data-role="OH" style="top:78px; left:395px;">OH</div>
                            <div class="player" data-pos="5" data-role="OH" style="top:278px; left:61px;">OH</div>
                            <div class="player" data-pos="6" data-role="L"  style="top:278px; left:228px;">L</div>
                            <div class="player" data-pos="1" data-role="S"  style="top:278px; left:395px;">S</div>
                        </div>
                    </div>
                </div>

                {{-- Settings panel --}}
                <div class="rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md flex flex-col">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span>
                        <span class="text-sm font-semibold text-white tracking-wide">Rotation Settings</span>
                    </div>

                    <div class="p-6 flex flex-col gap-5 flex-1">

                        <div>
                            <label for="rotation-name" class="block text-xs font-semibold uppercase tracking-widest text-green-300/80 mb-2">
                                {{ __('Rotation Name') }}
                            </label>
                            <input
                                type="text"
                                id="rotation-name"
                                placeholder="e.g. Block Defence #1"
                                class="w-full px-4 py-2.5 rounded-xl text-white text-sm placeholder-white/30 bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-green-500/60 focus:border-green-500/40 transition"
                            >
                        </div>

                        <div>
                            <label for="rotationType" class="block text-xs font-semibold uppercase tracking-widest text-green-300/80 mb-2">
                                {{ __('Rotation Type') }}
                            </label>
                            <select
                                id="rotationType"
                                class="w-full px-4 py-2.5 rounded-xl text-white text-sm bg-slate-800 border border-white/10 focus:outline-none focus:ring-2 focus:ring-green-500/60 transition"
                            >
                                <option value="attack"  style="background:#1e293b;">Attack Rotation</option>
                                <option value="defence" style="background:#1e293b;" selected>Defence Rotation</option>
                            </select>
                        </div>

                        <div class="border-t border-white/10"></div>

                        <div class="flex flex-col gap-3">
                            <button
                                onclick="checkRotationWithVisuals()"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-700/60 hover:bg-slate-700 border border-white/10 hover:border-white/20 transition"
                            >
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Check Rotation') }}
                            </button>

                            <button
                                onclick="rotateClockwise()"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-green-600/25 hover:bg-green-600/45 border border-green-500/30 hover:border-green-400/50 transition"
                            >
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                {{ __('Rotate Clockwise') }}
                            </button>

                            <button
                                onclick="saveRotation()"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-500 border border-green-400/40 shadow-lg shadow-green-900/30 transition"
                            >
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Save Rotation') }}
                            </button>

                            <a
                                href="{{ route('defence.index') }}"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition"
                            >
                                {{ __('Cancel') }}
                            </a>
                        </div>

                        <div id="errors" class="mt-2 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-sm hidden">
                            <p class="font-semibold mb-1 text-red-400">Please fix the following:</p>
                            <ul id="error-list" class="list-disc list-inside space-y-1"></ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('script.js') }}"></script>
</x-app-layout>