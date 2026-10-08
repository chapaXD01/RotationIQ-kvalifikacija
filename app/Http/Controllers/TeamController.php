<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamAnnouncement;
use App\Models\TeamMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function updateRole(Request $request, Team $team, int $userId): RedirectResponse
    {
        $actorRole = $this->authorizeRoleUpdate($request, $team, $userId);

        $validated = $request->validate([
            'role' => ['required', 'in:manager,assistant_manager,student'],
        ]);

        if ($actorRole !== 'coach') {
            abort_unless($validated['role'] !== 'manager', 403);
        }

        $team->members()->updateExistingPivot($userId, $validated);

        return back()->with('success', 'Team member role updated.');
    }

    public function updatePosition(Request $request, Team $team, int $userId): RedirectResponse
    {
        $this->authorizeRosterUpdate($request, $team, $userId);

        $validated = $request->validate([
            'position' => ['nullable', 'in:MB,OH,S,RS,L'],
        ]);

        $team->members()->updateExistingPivot($userId, $validated);

        return back()->with('success', 'Team member position updated.');
    }

    public function updateAttendance(Request $request, Team $team, int $userId): RedirectResponse
    {
        $this->authorizeRosterUpdate($request, $team, $userId);

        $validated = $request->validate([
            'attendance' => ['required', 'in:present,absent,substitute'],
        ]);

        $team->members()->updateExistingPivot($userId, $validated);

        return back()->with('success', 'Team member attendance updated.');
    }

    private function authorizeRoleUpdate(Request $request, Team $team, int $userId): string
    {
        $currentRole = $this->authorizeRosterUpdate($request, $team, $userId);

        if ($request->user()->role === 'coach' && $request->user()->id === $team->coach_id) {
            return 'coach';
        }

        $actorRole = $team->members()
            ->whereKey($request->user()->id)
            ->value('team_user.role');

        abort_unless($actorRole === 'manager', 403);
        abort_unless(in_array($currentRole, ['student', 'assistant_manager'], true), 403);

        return $actorRole;
    }

    private function authorizeRosterUpdate(Request $request, Team $team, int $userId): ?string
    {
        $member = $team->members()->whereKey($userId)->first();
        abort_unless($member, 404);

        if ($request->user()->role === 'coach' && $request->user()->id === $team->coach_id) {
            return $member->pivot->role;
        }

        $actorRole = $team->members()
            ->whereKey($request->user()->id)
            ->value('team_user.role');

        abort_unless(in_array($actorRole, ['manager', 'assistant_manager'], true), 403);

        if ($actorRole === 'assistant_manager') {
            abort_unless($member->pivot->role === 'student', 403);
        }

        return $member->pivot->role;
    }

    public function index(Request $request): View
    {
        $teams = $request->user()->teams()->with(['coach', 'members'])->latest()->get();

        return view('teams.index', compact('teams'));
    }

    public function show(Request $request, Team $team): View
    {
        $isMember = $team->members()->whereKey($request->user()->id)->exists();
        abort_unless($isMember || $request->user()->id === $team->coach_id, 403);

        $team->load(['coach', 'members', 'announcements.user']);

        // only load the most recent page of chat history up front — a long-running team
        // chat could otherwise mean loading thousands of messages on every page view.
        // Oldest-first for display; "hasMoreMessages" tells the view whether a
        // "load earlier" button is worth showing.
        // NB: the messages() relation already orders oldest()->oldest('id') — reorder()
        // clears that first, otherwise the relation's own order wins over ours and this
        // silently returns the OLDEST 50 messages instead of the newest 50.
        $messages = $team->messages()->with('user')
            ->reorder()->orderByDesc('created_at')->orderByDesc('id')
            ->limit(50)->get()->reverse()->values();
        $hasMoreMessages = $messages->isNotEmpty()
            && $team->messages()->where('id', '<', $messages->first()->id)->exists();

        return view('teams.show', compact('team', 'messages', 'hasMoreMessages'));
    }

    // older chat history, fetched on demand via the "load earlier messages" button
    // instead of loading the whole conversation up front (see show() above)
    public function loadMoreMessages(Request $request, Team $team): \Illuminate\Http\JsonResponse
    {
        $isMember = $team->members()->whereKey($request->user()->id)->exists();
        abort_unless($isMember || $request->user()->id === $team->coach_id, 403);

        $validated = $request->validate([
            'before_id' => ['required', 'integer'],
        ]);

        $messages = $team->messages()
            ->with('user:id,name')
            ->where('id', '<', $validated['before_id'])
            ->reorder()->orderByDesc('created_at')->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        $hasMore = $messages->isNotEmpty()
            && $team->messages()->where('id', '<', $messages->first()->id)->exists();

        return response()->json([
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'user_name' => $message->user->name,
                'message' => $message->message,
                'created_at_human' => $message->created_at->diffForHumans(),
            ]),
            'has_more' => $hasMore,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'coach', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);
 // join code generator
        do {
            $joinCode = Str::upper(Str::random(8));
        } while (Team::where('join_code', $joinCode)->exists());

        $team = Team::create([
            'coach_id' => $request->user()->id,
            'name' => $validated['name'],
            'join_code' => $joinCode,
        ]);

        $team->members()->attach($request->user()->id, ['role' => 'manager']);

        return to_route('teams.index')->with('success', 'Team created. Share the join code with your students.');
    }

    public function join(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'join_code' => ['required', 'string', 'size:8'],
        ]);

        $team = Team::where('join_code', Str::upper($validated['join_code']))->first();

        if (! $team) {
            return to_route('teams.index')->withErrors(['join_code' => 'That join code is not valid.']);
        }

        if ($team->members()->whereKey($request->user()->id)->exists()) {
            return to_route('teams.index')->with('success', 'You are already a member of this team.');
        }

        $team->members()->attach($request->user()->id, ['role' => 'student']);

        return to_route('teams.index')->with('success', "You joined {$team->name}.");
    }

    public function leave(Request $request, Team $team): RedirectResponse
    {
        abort_if($request->user()->id === $team->coach_id, 403, 'The coach cannot leave their own team.');

        abort_unless($team->members()->whereKey($request->user()->id)->exists(), 404);

        $team->members()->detach($request->user()->id);

        return to_route('teams.index')->with('success', "You left {$team->name}.");
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        abort_unless($request->user()->id === $team->coach_id, 403);

        $team->delete();

        return to_route('teams.index')->with('success', "{$team->name} has been disbanded.");
    }

    public function storeMessage(Request $request, Team $team): RedirectResponse
    {
        abort_unless($team->members()->whereKey($request->user()->id)->exists(), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        TeamMessage::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'message' => trim($validated['message']),
        ]);

        return back()->with('success', 'Message sent.');
    }

    public function storeAnnouncement(Request $request, Team $team): RedirectResponse
    {
        abort_unless($this->canManageAnnouncements($request, $team), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        TeamAnnouncement::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'message' => trim($validated['message']),
        ]);

        return back()->with('success', 'Announcement posted.');
    }

    // deliberately NOT restricted to the announcement's own author: any manager/coach can
    // remove any announcement, same as they can already edit any player's roster role,
    // position or attendance. A coach needs to be able to take down a manager's post while
    // that manager is away, not be blocked because they didn't write it themselves.
    public function destroyAnnouncement(Request $request, Team $team, TeamAnnouncement $announcement): RedirectResponse
    {
        abort_unless($announcement->team_id === $team->id, 404);
        abort_unless($this->canManageAnnouncements($request, $team), 403);

        $announcement->delete();

        return back()->with('success', 'Announcement removed.');
    }

    private function canManageAnnouncements(Request $request, Team $team): bool
    {
        if ($request->user()->id === $team->coach_id) {
            return true;
        }

        $role = $team->members()->whereKey($request->user()->id)->value('team_user.role');

        return $role === 'manager';
    }
}
