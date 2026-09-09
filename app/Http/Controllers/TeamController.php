<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function assignRole(Request $request, Team $team, int $userId): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $team->coach_id
                || $team->members()->whereKey($request->user()->id)->wherePivot('role', 'manager')->exists(),
            403
        );

        abort_unless($team->members()->whereKey($userId)->exists(), 404);

        $validated = $request->validate([
            'role' => ['required', 'in:manager,assistant_manager,student'],
            'position' => ['nullable', 'in:MB,OT,S,OP,L'],
        ]);

        $team->members()->updateExistingPivot($userId, [
            'role' => $validated['role'],
            'position' => $validated['position'] ?? null,
        ]);

        return back()->with('success', 'Team role updated.');
    }

    public function index(Request $request): View
    {
        $teams = $request->user()->teams()->with(['coach', 'members', 'messages.user'])->latest()->get();

        return view('teams.index', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'coach', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

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
}
