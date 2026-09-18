<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

abstract class BaseRotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    abstract protected function getModel(): string;
    abstract protected function getRouteName(): string;

    // listo visas rotacijas visiem useriem
    public function index()
    {
        $model = $this->getModel();
        $rotations = $model::where('user_id', auth()->id())->latest()->get();

        return view($this->getRouteName() . '.index', compact('rotations'));
    }
    //prieks create new rotation
    public function create()
    {
        $teams = $this->teamsPayload();

        return view($this->getRouteName() . '.create', compact('teams'));
    }
    // parāda rotaciju tikai ja pieder useram
    public function show($id)
    {
        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$rotation) {
            return redirect()->route($this->getRouteName() . '.index')
                ->with('error', 'Rotation not found.');
        }

        return view($this->getRouteName() . '.show', compact('rotation'));
    }
    //parada edit formu tikai ja rotacija pieder useram
    public function edit($id)
    {
        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$rotation) {
            return redirect()->route($this->getRouteName() . '.index')
                ->with('error', 'Rotation not found.');
        }

        $teams = $this->teamsPayload();

        return view($this->getRouteName() . '.edit', compact('rotation', 'teams'));
    }
    // update gatavas rotacijas
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'players' => 'required|array|min:1',
            'team_id' => 'nullable|integer|exists:teams,id',
        ]);

        if (!empty($validated['team_id'])) {
            abort_unless($this->userManagesTeam((int) $validated['team_id']), 403);
        }

        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $rotation->update([
            'name'    => $validated['name'],
            'players' => json_encode($validated['players']),
            'team_id' => $validated['team_id'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }
    //valide un saglaba rotacijas kuras pieder useram
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'players' => 'required|array|min:1',
            'team_id' => 'nullable|integer|exists:teams,id',
        ]);

        if (!empty($validated['team_id'])) {
            abort_unless($this->userManagesTeam((int) $validated['team_id']), 403);
        }

        $model = $this->getModel();
        $model::create([
            'name'    => $validated['name'],
            'players' => json_encode($validated['players']),
            'user_id' => auth()->id(),
            'team_id' => $validated['team_id'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    // teams kuras esošais useris parvalda (coach, manager vai assistant_manager)
    // ar rosteri gatavu priekš rotaciju JS
    protected function teamsPayload(): array
    {
        return auth()->user()->teams()
            ->with('members')
            ->get()
            ->filter(fn (Team $team) => $this->userManagesTeam($team, $team->members))
            ->map(fn (Team $team) => [
                'id' => $team->id,
                'name' => $team->name,
                'players' => $team->members->map(fn ($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'position' => $member->pivot->position,
                ])->values(),
            ])
            ->values()
            ->all();
    }

    // parbauda vai useris ir sī teama coach, manager vai assistant_manager
    protected function userManagesTeam($team, $members = null): bool
    {
        if (is_int($team)) {
            $team = Team::with('members')->find($team);
        }

        if (!$team) {
            return false;
        }

        if ($team->coach_id === auth()->id()) {
            return true;
        }

        $members ??= $team->members;
        $role = $members->firstWhere('id', auth()->id())?->pivot->role;

        return in_array($role, ['manager', 'assistant_manager'], true);
    }
    //Delete == gone forever!!!
    public function destroy($id)
    {
        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $rotation->delete();

        return redirect()->route($this->getRouteName() . '.index')
            ->with('success', 'Rotation deleted successfully.');
    }
}