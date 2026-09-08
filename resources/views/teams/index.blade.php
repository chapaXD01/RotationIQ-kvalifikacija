<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-300/70">Squad workspace</p>
                <h2 class="mt-1 font-bold text-xl text-white tracking-tight">Teams</h2>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-sm text-white/70">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                {{ $teams->count() }} {{ $teams->count() === 1 ? 'team' : 'teams' }}
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-200 text-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-400/30 text-red-200 text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if (auth()->user()->role === 'coach')
                    <section class="p-6 rounded-2xl border border-blue-400/20 bg-blue-500/10 backdrop-blur-md">
                        <p class="text-xs font-semibold uppercase tracking-widest text-blue-300">Coach tools</p>
                        <h3 class="mt-2 text-lg font-bold text-white">Create a team</h3>
                        <p class="mt-1 text-sm text-white/65">Start a squad and give its join code to your students.</p>
                        <form method="POST" action="{{ route('teams.store') }}" class="mt-5 flex flex-col sm:flex-row gap-3">
                            @csrf
                            <input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Team name" class="flex-1 rounded-xl border-white/15 bg-white/10 text-white placeholder-white/40 focus:border-blue-400 focus:ring-blue-400">
                            <button class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 border border-blue-400/40 text-sm font-bold text-white transition">Create team</button>
                        </form>
                    </section>
                @endif

                <section class="p-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md {{ auth()->user()->role !== 'coach' ? 'lg:col-span-2' : '' }}">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-300">Join a team</p>
                    <h3 class="mt-2 text-lg font-bold text-white">Enter your coach's code</h3>
                    <p class="mt-1 text-sm text-white/65">Use the 8-character code shared by your coach.</p>
                    <form method="POST" action="{{ route('teams.join') }}" class="mt-5 flex flex-col sm:flex-row gap-3">
                        @csrf
                        <input name="join_code" required minlength="8" maxlength="8" autocapitalize="characters" placeholder="ABC12345" class="flex-1 uppercase rounded-xl border-white/15 bg-white/10 text-white placeholder-white/40 focus:border-emerald-400 focus:ring-emerald-400">
                        <button class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 border border-emerald-400/40 text-sm font-bold text-white transition">Join team</button>
                    </form>
                </section>
            </div>

            <section>
                <p class="mb-4 px-1 text-xs font-semibold uppercase tracking-widest text-blue-300/70">Your teams</p>
                @if ($teams->isEmpty())
                    <div class="p-12 rounded-2xl border border-dashed border-white/15 bg-white/5 text-center">
                        <p class="text-white font-medium">No teams yet.</p>
                        <p class="mt-1 text-sm text-white/55">Create one or join with a code from your coach.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($teams as $team)
                            <article class="p-5 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-white text-lg">{{ $team->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-300">Coach: {{ $team->coach->name }}</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg bg-white/10 text-xs font-semibold text-slate-300">{{ $team->members->count() }} members</span>
                                </div>
                                <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between">
                                    <span class="text-xs uppercase tracking-wide text-slate-400">Join code</span>
                                    <code class="text-lg font-bold tracking-[0.2em] text-slate-300">{{ $team->join_code }}</code>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($team->members as $member)
                                        <span class="px-2.5 py-1 rounded-lg bg-white/10 text-xs text-slate-300">{{ $member->name }}</span>
                                    @endforeach
                                </div>

                                <div class="mt-5 pt-4 border-t border-white/10">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Team chat</span>
                                        <span class="text-[10px] text-slate-400">{{ $team->messages->count() }} messages</span>
                                    </div>

                                    <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                                        @forelse ($team->messages->take(6) as $message)
                                            <div class="rounded-xl border border-white/10 bg-black/10 px-3 py-2">
                                                <div class="mb-1 flex items-center justify-between gap-3 text-[10px] uppercase tracking-wide text-slate-400">
                                                    <span>{{ $message->user->name }}</span>
                                                    <span>{{ $message->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-sm text-slate-200">{{ $message->message }}</p>
                                            </div>
                                        @empty
                                            <p class="text-sm text-slate-400">No messages yet. Start the conversation.</p>
                                        @endforelse
                                    </div>

                                    <form method="POST" action="{{ route('teams.messages.store', $team) }}" class="mt-4 flex gap-2">
                                        @csrf
                                        <input
                                            type="text"
                                            name="message"
                                            maxlength="2000"
                                            required
                                            placeholder="Message the team..."
                                            class="flex-1 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-blue-400 focus:ring-blue-400"
                                        >
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500 transition">
                                            Send
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
