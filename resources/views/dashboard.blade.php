<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: rgba(59, 130, 246, 0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="p-6 text-white">
                    <h1>Start building ur volleyball rotations today!</h1>
                </div>
            </div>

            {{-- Stats --}}
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="group p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-white/20 hover:bg-white/10 hover:shadow-lg hover:shadow-black/20">
                    <p class="text-2xl font-bold text-white transition-transform duration-200 origin-left group-hover:scale-110">{{ $stats['teams'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">{{ Str::plural('Team', $stats['teams']) }}</p>
                </div>
                <div class="group p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-blue-400/30 hover:bg-blue-500/10 hover:shadow-lg hover:shadow-blue-900/20">
                    <p class="text-2xl font-bold text-white transition-transform duration-200 origin-left group-hover:scale-110">{{ $stats['attack'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-blue-400/70">Attack {{ Str::plural('Rotation', $stats['attack']) }}</p>
                </div>
                <div class="group p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-green-400/30 hover:bg-green-500/10 hover:shadow-lg hover:shadow-green-900/20">
                    <p class="text-2xl font-bold text-white transition-transform duration-200 origin-left group-hover:scale-110">{{ $stats['defence'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-green-400/70">Defence {{ Str::plural('Rotation', $stats['defence']) }}</p>
                </div>
                <div class="group p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-white/20 hover:bg-white/10 hover:shadow-lg hover:shadow-black/20">
                    <p class="text-2xl font-bold text-white transition-transform duration-200 origin-left group-hover:scale-110">{{ $stats['attack'] + $stats['defence'] }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Total Rotations</p>
                </div>
            </section>

            {{-- Quick actions --}}
            <section>
                <p class="mb-4 px-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Quick actions</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('attack.create') }}" class="group flex items-center gap-4 p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-blue-400/40 hover:bg-blue-500/10 hover:shadow-lg hover:shadow-blue-900/20">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-blue-600/20 border border-blue-500/30 flex-shrink-0 transition duration-200 group-hover:scale-110">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-white text-sm">New Attack Rotation</p>
                            <p class="text-xs text-slate-400">Build a fresh formation</p>
                        </div>
                        <svg class="w-4 h-4 text-blue-300 ml-auto flex-shrink-0 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('defence.create') }}" class="group flex items-center gap-4 p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-green-400/40 hover:bg-green-500/10 hover:shadow-lg hover:shadow-green-900/20">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-green-600/20 border border-green-500/30 flex-shrink-0 transition duration-200 group-hover:scale-110">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-white text-sm">New Defence Rotation</p>
                            <p class="text-xs text-slate-400">Build a fresh formation</p>
                        </div>
                        <svg class="w-4 h-4 text-green-300 ml-auto flex-shrink-0 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('teams.index') }}" class="group flex items-center gap-4 p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md transition duration-200 hover:-translate-y-1 hover:border-purple-400/40 hover:bg-purple-500/10 hover:shadow-lg hover:shadow-purple-900/20">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-purple-600/20 border border-purple-500/30 flex-shrink-0 transition duration-200 group-hover:scale-110">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 3.13a4 4 0 00-3-7.13"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-white text-sm">Manage Teams</p>
                            <p class="text-xs text-slate-400">Roster, roles &amp; announcements</p>
                        </div>
                        <svg class="w-4 h-4 text-purple-300 ml-auto flex-shrink-0 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Recent rotations --}}
                <section class="lg:col-span-2">
                    <div class="mb-4 px-1 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Recent rotations</p>
                    </div>

                    @if ($recentRotations->isEmpty())
                        <div class="p-8 rounded-2xl border border-dashed border-white/15 bg-white/5 text-center">
                            <p class="text-white font-medium">No rotations yet.</p>
                            <p class="mt-1 text-sm text-white/55">Create your first attack or defence rotation above.</p>
                        </div>
                    @else
                        <div class="rounded-2xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur-md divide-y divide-white/10">
                            @foreach ($recentRotations as $rotation)
                                <a
                                    href="{{ route($rotation['kind'] . '.show', $rotation['id']) }}"
                                    class="group flex items-center gap-4 p-4 transition hover:bg-white/5"
                                >
                                    <div class="flex items-center justify-center w-9 h-9 rounded-lg flex-shrink-0 transition duration-200 group-hover:scale-110 {{ $rotation['kind'] === 'attack' ? 'bg-blue-600/20 border border-blue-500/30' : 'bg-green-600/20 border border-green-500/30' }}">
                                        @if ($rotation['kind'] === 'attack')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-white text-sm truncate">{{ $rotation['name'] }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ ucfirst($rotation['kind']) }}
                                            @if ($rotation['type'] === 'sequence')
                                                &middot; Sequence
                                            @endif
                                            &middot; {{ $rotation['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-500 flex-shrink-0 transition group-hover:translate-x-0.5 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Team announcements --}}
                <section>
                    <p class="mb-4 px-1 text-xs font-semibold uppercase tracking-widest text-slate-400">Latest announcements</p>

                    @if ($announcements->isEmpty())
                        <div class="p-6 rounded-2xl border border-dashed border-white/15 bg-white/5 text-center">
                            <p class="text-sm text-white/55">No announcements from your teams yet.</p>
                        </div>
                    @else
                        <div class="rounded-2xl overflow-hidden border border-white/10 bg-white/5 backdrop-blur-md divide-y divide-white/10">
                            @foreach ($announcements as $announcement)
                                <div class="p-4 transition hover:bg-white/5">
                                    <p class="text-sm text-white leading-snug">{{ $announcement->message }}</p>
                                    <p class="mt-2 text-xs text-slate-400">
                                        {{ $announcement->user->name ?? 'Unknown' }}
                                        @if ($announcement->team)
                                            &middot; {{ $announcement->team->name }}
                                        @endif
                                        &middot; {{ $announcement->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
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
                                class="group block p-5 rounded-2xl border border-white/10 bg-white/5 transition duration-200 hover:-translate-y-1 hover:border-blue-400/40 hover:bg-blue-500/10 hover:shadow-lg hover:shadow-blue-900/20"
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
                    <h1 class="text-lg font-bold text-white">How to use RotationIQ</h1>
                    <p class="mt-1 mb-5 text-sm text-white/70">A quick tour of what you can do.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-xs font-bold flex-shrink-0">1</span>
                            <p><span class="font-semibold">Create or join a team</span> — go to Teams, create one as a coach or join with a code, then set each player's position on the roster.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-xs font-bold flex-shrink-0">2</span>
                            <p><span class="font-semibold">Build a rotation</span> — open Attack or Defence, press "Create New Rotation", pick a team, and drag players onto the court.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-xs font-bold flex-shrink-0">3</span>
                            <p><span class="font-semibold">Single or Sequence</span> — a Single rotation is one formation; Sequence lets you build and edit all 6 rotations of a game, tab by tab.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-xs font-bold flex-shrink-0">4</span>
                            <p><span class="font-semibold">Check &amp; rotate</span> — use "Check Rotation" to catch overlap errors, or "Rotate Clockwise" to preview the next rotation before saving.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-xs font-bold flex-shrink-0">5</span>
                            <p><span class="font-semibold">Share with your team</span> — any rotation saved to a team is visible to its members; only the creator can edit or delete it.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/15 text-xs font-bold flex-shrink-0">6</span>
                            <p><span class="font-semibold">Stay in the loop</span> — coaches and managers can post announcements on the team page for everyone to see.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
