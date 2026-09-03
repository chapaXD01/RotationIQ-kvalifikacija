<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-green-600/20 border border-green-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-white uppercase tracking-widest font-semibold">Rotation</p>
                    <h2 class="font-bold text-xl text-white tracking-tight leading-tight">
                        {{ $rotation->name }}
                    </h2>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('defence.edit', $rotation->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-yellow-600/30 hover:bg-yellow-500/50 border border-yellow-500/30 transition"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit') }}
                </a>
                <a
                    href="{{ route('defence.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <p class="text-xs font-semibold uppercase tracking-widest text-green-400/70 mb-4 px-1">
                🛡️ Court Diagram
            </p>

            <div class="rounded-2xl overflow-hidden shadow-xl border border-white/10 bg-white/5 backdrop-blur-md">
                <div class="px-5 py-4 border-b border-white/10 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span>
                    <span class="text-sm font-semibold text-white tracking-wide">Court View</span>
                    <span class="ml-auto inline-flex items-center gap-1.5 text-xs text-white">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Read-only
                    </span>
                </div>
                <div class="p-8 flex justify-center items-center min-h-[460px]">
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

                        @php $players = json_decode($rotation->players, true); @endphp

                        @foreach ($players as $player)
                            <div class="player"
                                 style="top: {{ $player['top'] }}px; left: {{ $player['left'] }}px">
                                {{ $player['role'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-4 flex flex-wrap gap-3 px-1">
                @foreach ([['RS','Right Side'],['MB','Middle Blocker'],['OH','Outside Hitter'],['S','Setter'],['L','Libero']] as $entry)
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs text-white">
                        <span class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center text-white font-bold text-[10px]">{{ $entry[0] }}</span>
                        {{ $entry[1] }}
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>