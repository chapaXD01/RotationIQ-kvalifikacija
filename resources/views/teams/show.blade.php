<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('teams.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-blue-300/70 hover:text-blue-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    All teams
                </a>
                <h2 class="mt-1 font-bold text-xl text-white tracking-tight">{{ $team->name }}</h2>
            </div>
            <div class="flex items-center gap-2 text-sm text-white/70">
                <span class="px-2.5 py-1 rounded-lg bg-white/10 text-xs font-semibold text-slate-300">{{ $team->members->count() }} members</span>
            </div>
        </div>
    </x-slot>

    @php
        $currentMember = $team->members->firstWhere('id', auth()->id());
        $currentTeamRole = $currentMember?->pivot->role;
        $isCoach = auth()->user()->role === 'coach' && auth()->id() === $team->coach_id;
        $canManageTeam = $isCoach || in_array($currentTeamRole, ['manager', 'assistant_manager'], true);
        $canManageRoles = $isCoach || $currentTeamRole === 'manager';
        $canLeaveTeam = $currentMember && ! $isCoach;
        $canDisbandTeam = $isCoach;
        $manager = $team->manager();
        $assistantManagers = $team->members->filter(fn ($member) => $member->pivot->role === 'assistant_manager');
        $students = $team->students();
        $canPostAnnouncements = $isCoach || $currentTeamRole === 'manager';
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-200 text-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-400/30 text-red-200 text-sm">{{ $errors->first() }}</div>
            @endif

            <section class="p-6 rounded-2xl border border-amber-400/20 bg-amber-500/5 backdrop-blur-md">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-300/80">Announcements</span>
                    <span class="text-[10px] text-slate-400">{{ $team->announcements->count() }}</span>
                </div>

                @if ($canPostAnnouncements)
                    <form method="POST" action="{{ route('teams.announcements.store', $team) }}" class="mb-4 flex flex-col sm:flex-row gap-2">
                        @csrf
                        <textarea
                            name="message"
                            rows="2"
                            maxlength="2000"
                            required
                            placeholder="Post an announcement to the team..."
                            class="flex-1 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-amber-400 focus:ring-amber-400"
                        ></textarea>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500 transition">
                            Post
                        </button>
                    </form>
                @endif

                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse ($team->announcements as $announcement)
                        <div class="rounded-xl border border-amber-400/10 bg-black/10 px-3 py-2.5" x-data="{ confirmDelete: false }">
                            <div class="mb-1 flex items-center justify-between gap-3 text-[10px] uppercase tracking-wide text-slate-400">
                                <span>{{ $announcement->user->name }}</span>
                                <span class="flex items-center gap-2">
                                    {{ $announcement->created_at->diffForHumans() }}
                                    @if ($canPostAnnouncements)
                                        <button type="button" @click="confirmDelete = true" class="text-red-300/70 hover:text-red-300" title="Delete announcement">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </span>
                            </div>
                            <p class="text-sm text-slate-200 whitespace-pre-line">{{ $announcement->message }}</p>

                            @if ($canPostAnnouncements)
                                <div
                                    x-cloak
                                    x-show="confirmDelete"
                                    x-transition
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                                    @click.self="confirmDelete = false"
                                    @keydown.escape.window="confirmDelete = false"
                                >
                                    <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-slate-900 p-5 shadow-2xl shadow-black/40">
                                        <h4 class="text-base font-bold text-white">Delete this announcement?</h4>
                                        <p class="mt-2 text-sm text-slate-400">Students will no longer be able to see it. This can't be undone.</p>
                                        <form method="POST" action="{{ route('teams.announcements.destroy', [$team, $announcement]) }}" class="mt-5 flex justify-end gap-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" @click="confirmDelete = false" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:text-white">Cancel</button>
                                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No announcements yet.</p>
                    @endforelse
                </div>
            </section>

            <div
                class="grid grid-cols-1 lg:grid-cols-5 gap-6"
                @if ($canManageTeam)
                    x-data="{
                        menuOpen: false,
                        menuX: 0,
                        menuY: 0,
                        selectedMember: {},
                        memberRoleUrl: @js(route('teams.members.role', [$team, '__USER__'])),
                        memberPositionUrl: @js(route('teams.members.position', [$team, '__USER__'])),
                        memberAttendanceUrl: @js(route('teams.members.attendance', [$team, '__USER__'])),
                        openMemberMenu(event, member) {
                            this.selectedMember = member;
                            const menuWidth = 256;
                            const menuHeight = 470;
                            const gap = 12;
                            const rightX = event.clientX + gap;
                            const leftX = event.clientX - menuWidth - gap;
                            const belowY = event.clientY + gap;
                            const aboveY = event.clientY - menuHeight - gap;
                            this.menuX = rightX + menuWidth <= window.innerWidth
                                ? rightX
                                : Math.max(8, leftX);
                            this.menuY = belowY + menuHeight <= window.innerHeight
                                ? belowY
                                : Math.max(8, aboveY);
                            this.menuOpen = true;
                        }
                    }"
                    @click.outside="menuOpen = false"
                    @keydown.escape.window="menuOpen = false"
                @endif
            >
                {{-- Left column: team info + roster --}}
                <div class="lg:col-span-3 space-y-6">
                    <section class="p-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-white text-lg">{{ $team->name }}</h3>
                                <p class="mt-1 text-sm text-slate-300">Coach: {{ $team->coach->name }}</p>
                            </div>
                        </div>
                        <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between">
                            <span class="text-xs uppercase tracking-wide text-slate-400">Join code</span>
                            <code class="text-lg font-bold tracking-[0.2em] text-slate-300">{{ $team->join_code }}</code>
                        </div>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Manager</p>
                                <div class="flex flex-wrap gap-2">
                                    @if ($manager)
                                        <span class="px-2.5 py-1 rounded-lg bg-white/10 text-xs text-slate-300">{{ $manager->name }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">No manager assigned</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Assistant manager</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($assistantManagers as $member)
                                        <span class="px-2.5 py-1 rounded-lg bg-white/10 text-xs text-slate-300">{{ $member->name }}</span>
                                    @empty
                                        <span class="text-xs text-slate-400">No assistant managers</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Students</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($students as $member)
                                    <span class="px-2.5 py-1 rounded-lg bg-white/10 text-xs text-slate-300">{{ $member->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">No students yet</span>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <section class="p-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Roster</p>
                            @if ($canManageTeam)
                                <span class="text-[10px] text-slate-500">Right-click a member to edit</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($team->members as $member)
                                @php
                                    $memberRole = $member->pivot->role ?? 'student';
                                    $memberPosition = $member->pivot->position;
                                    $memberAttendance = $member->pivot->attendance ?? 'present';
                                @endphp
                                @if ($canManageTeam)
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 rounded-lg border border-white/5 bg-black/10 px-3 py-2.5 text-left transition hover:border-blue-400/40 hover:bg-blue-500/10"
                                        @contextmenu.prevent="openMemberMenu($event, { id: {{ $member->id }}, name: @js($member->name), role: @js($memberRole), position: @js($memberPosition), attendance: @js($memberAttendance) })"
                                    >
                                @else
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-white/5 bg-black/10 px-3 py-2.5">
                                @endif
                                        <span class="min-w-0 truncate text-sm text-slate-200">{{ $member->name }}</span>
                                        <span class="flex shrink-0 items-center gap-1.5">
                                            @if ($memberPosition)
                                                <span class="rounded bg-blue-500/20 px-1.5 py-0.5 text-[10px] font-bold text-blue-200">{{ $memberPosition }}</span>
                                            @endif
                                            <span class="rounded bg-emerald-500/15 px-1.5 py-0.5 text-[10px] capitalize text-emerald-200">{{ $memberAttendance }}</span>
                                            <span class="text-[10px] capitalize text-slate-500">{{ str_replace('_', ' ', $memberRole) }}</span>
                                        </span>
                                @if ($canManageTeam)
                                    </button>
                                @else
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>

                    @if ($canLeaveTeam || $canDisbandTeam)
                        <section class="p-6 rounded-2xl border border-red-400/20 bg-red-500/5 backdrop-blur-md">
                            <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-red-300/70">Danger zone</p>

                            @if ($canLeaveTeam)
                                <div x-data="{ confirmLeave: false }">
                                    <button
                                        type="button"
                                        @click="confirmLeave = true"
                                        class="w-full rounded-xl border border-red-400/30 bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/20"
                                    >
                                        Leave team
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="confirmLeave"
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                                        @click.self="confirmLeave = false"
                                        @keydown.escape.window="confirmLeave = false"
                                    >
                                        <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-slate-900 p-5 shadow-2xl shadow-black/40">
                                            <h4 class="text-base font-bold text-white">Leave {{ $team->name }}?</h4>
                                            <p class="mt-2 text-sm text-slate-400">You'll lose access to this team's roster, rotations, and chat unless you rejoin with the join code.</p>
                                            <form method="POST" action="{{ route('teams.leave', $team) }}" class="mt-5 flex justify-end gap-3">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" @click="confirmLeave = false" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:text-white">Cancel</button>
                                                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Leave team</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($canDisbandTeam)
                                <div class="{{ $canLeaveTeam ? 'mt-3' : '' }}" x-data="{ confirmDisband: false }">
                                    <button
                                        type="button"
                                        @click="confirmDisband = true"
                                        class="w-full rounded-xl border border-red-400/30 bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/20"
                                    >
                                        Disband team
                                    </button>

                                    <div
                                        x-cloak
                                        x-show="confirmDisband"
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                                        @click.self="confirmDisband = false"
                                        @keydown.escape.window="confirmDisband = false"
                                    >
                                        <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-slate-900 p-5 shadow-2xl shadow-black/40">
                                            <h4 class="text-base font-bold text-white">Disband {{ $team->name }}?</h4>
                                            <p class="mt-2 text-sm text-slate-400">This permanently deletes the team, removes every member, and erases the team chat. This can't be undone.</p>
                                            <form method="POST" action="{{ route('teams.destroy', $team) }}" class="mt-5 flex justify-end gap-3">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" @click="confirmDisband = false" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-300 hover:text-white">Cancel</button>
                                                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Disband team</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif
                </div>

                {{-- Right column: team chat --}}
                <div class="lg:col-span-2">
                    <section class="p-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-md flex flex-col h-full">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Team chat</span>
                            <span class="text-[10px] text-slate-400">{{ $team->messages->count() }} messages</span>
                        </div>

                        <div class="space-y-2 flex-1 min-h-[24rem] max-h-[36rem] overflow-y-auto pr-1">
                            @forelse ($team->messages as $message)
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
                    </section>
                </div>

                @if ($canManageTeam)
                    <div
                        x-cloak
                        x-show="menuOpen"
                        x-transition
                        class="fixed z-50 w-64 rounded-xl border border-white/15 bg-slate-900 p-3 shadow-2xl shadow-black/40"
                        :style="`left: ${menuX}px; top: ${menuY}px`"
                    >
                        <p class="mb-3 truncate text-sm font-semibold text-white" x-text="selectedMember.name"></p>
                        @if ($canManageRoles)
                            <form method="POST" x-bind:action="memberRoleUrl.replace('__USER__', selectedMember.id)" @submit="menuOpen = false">
                                @csrf
                                <label class="mb-1 block text-[10px] font-semibold uppercase tracking-widest text-slate-400">Team role</label>
                                <select name="role" x-model="selectedMember.role" class="mb-2 w-full rounded-lg border-white/10 bg-white/10 text-sm text-white focus:border-blue-400 focus:ring-blue-400">
                                    <option value="student">Student</option>
                                    <option value="assistant_manager">Assistant manager</option>
                                    @if ($isCoach)
                                        <option value="manager">Manager</option>
                                    @endif
                                </select>
                                <button type="submit" class="w-full rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">Save role</button>
                            </form>
                        @endif

                        <form method="POST" x-bind:action="memberPositionUrl.replace('__USER__', selectedMember.id)" @submit="menuOpen = false" class="{{ $canManageRoles ? 'mt-3 border-t border-white/10 pt-3' : '' }}">
                            @csrf
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-widest text-slate-400">Playing position</label>
                            <select name="position" x-model="selectedMember.position" class="w-full rounded-lg border-white/10 bg-white/10 text-sm text-white focus:border-blue-400 focus:ring-blue-400">
                                <option value="">No position</option>
                                <option value="S">S - Setter</option>
                                <option value="MB">MB - Middle blocker</option>
                                <option value="OT">OT - Outside hitter</option>
                                <option value="RS">RS - Right side</option>
                                <option value="L">L - Libero</option>
                            </select>
                            <button type="submit" class="mt-2 w-full rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">Save position</button>
                        </form>

                        <form method="POST" x-bind:action="memberAttendanceUrl.replace('__USER__', selectedMember.id)" @submit="menuOpen = false" class="mt-3 border-t border-white/10 pt-3">
                            @csrf
                            <label class="mb-1 block text-[10px] font-semibold uppercase tracking-widest text-slate-400">Attendance</label>
                            <select name="attendance" x-model="selectedMember.attendance" class="w-full rounded-lg border-white/10 bg-white/10 text-sm text-white focus:border-blue-400 focus:ring-blue-400">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="substitute">Substitute</option>
                            </select>
                            <button type="submit" class="mt-2 w-full rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">Save attendance</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
