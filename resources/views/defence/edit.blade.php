<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-500/20 border border-yellow-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-white uppercase tracking-widest font-semibold">Editing</p>
                    <h2 class="font-bold text-xl text-white tracking-tight leading-tight">
                        {{ $rotation->name }}
                    </h2>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400/70 mb-4 px-1">
                ✏️ Modify &amp; Update
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Court panel --}}
                <div class="lg:col-span-2 rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span>
                        <span class="text-sm font-semibold text-white tracking-wide">Court View</span>
                        <span class="ml-auto text-xs text-white">Drag players to reposition</span>
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

                            @php $players = json_decode($rotation->players, true); @endphp
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

                {{-- Settings panel --}}
                <div class="rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md flex flex-col">
                    <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>
                        <span class="text-sm font-semibold text-white tracking-wide">Update Rotation</span>
                    </div>

                    <div class="p-6 flex flex-col gap-5 flex-1">

                        <div>
                            <label for="rotation-name" class="block text-xs font-semibold uppercase tracking-widest text-green-300/80 mb-2">
                                {{ __('Rotation Name') }}
                            </label>
                            <input
                                type="text"
                                id="rotation-name"
                                value="{{ $rotation->name }}"
                                class="w-full px-4 py-2.5 rounded-xl text-white text-sm bg-white/5 border border-white/10 focus:outline-none focus:ring-2 focus:ring-green-500/60 focus:border-green-500/40 transition"
                            >
                        </div>

                        <input type="hidden" id="rotationType" value="defence">

                        <div class="border-t border-white/10"></div>

                        <div class="flex flex-col gap-3">
                            <button
                                onclick="checkRotationWithVisuals()"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-700/60 hover:bg-slate-700 border border-white/10 hover:border-white/20 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Check Rotation') }}
                            </button>

                            <button
                                onclick="updateRotation({{ $rotation->id }})"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-yellow-600 hover:bg-yellow-500 border border-yellow-400/40 shadow-lg shadow-yellow-900/20 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                {{ __('Update Rotation') }}
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