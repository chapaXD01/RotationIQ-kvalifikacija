<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: rgba(59, 130, 246, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6 text-white">
                    <h1>Start building ur volleyball rotations today!</h1>
                </div>
            </div>

            <section>
                <div class="mb-4 px-1 flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-300/70">Your teams</p>
                    <a href="{{ route('teams.index') }}" class="text-xs font-semibold text-blue-300 hover:text-blue-200 transition">Manage teams &rarr;</a>
                </div>

                @if ($teams->isEmpty())
                    <div class="p-8 rounded-2xl border border-dashed border-white/15 bg-white/5 text-center">
                        <p class="text-white font-medium">You're not part of any team yet.</p>
                        <p class="mt-1 text-sm text-white/55">Create one or join with a code from your coach.</p>
                        <a href="{{ route('teams.index') }}" class="mt-4 inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 border border-blue-400/40 text-sm font-bold text-white transition">
                            Go to teams
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($teams as $team)
                            <a
                                href="{{ route('teams.show', $team) }}"
                                class="group block p-5 rounded-2xl border border-white/10 bg-white/5 transition hover:border-blue-400/40 hover:bg-blue-500/10"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-white text-lg truncate">{{ $team->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-300 truncate">Coach: {{ $team->coach->name }}</p>
                                    </div>
                                    <span class="shrink-0 px-2.5 py-1 rounded-lg bg-white/10 text-xs font-semibold text-slate-300">{{ $team->members->count() }} members</span>
                                </div>
                                <div class="mt-5 pt-4 border-t border-white/10">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-300 group-hover:text-blue-200">
                                        View team
                                        <svg class="w-4 h-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: rgba(59, 130, 246, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6 text-white">
                    <h1>How to use the app?</h1>
                    <p>To start, on the navigation bar there ar 2 choices defence and attack chose one by clicking on it. After that press on CREATE NEW ROTATION.
                        then u can start creating your own rotation you can drag each player in any position by clicking and holding on the player. And the all u gotta do is press save rotation.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
