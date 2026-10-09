<?php

namespace App\Http\Controllers;

use App\Http\Requests\RotationRequest;
use App\Models\Team;

abstract class BaseRotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    abstract protected function getModel(): string;
    abstract protected function getRouteName(): string;

    // listo rotacijas kuras pieder useram VAI pieder pie teama kurā useris ir loceklis
    // (skatīties var visi teama biedri, bet edit/delete paliek tikai pie tā kurš izveidoja)
    public function index()
    {
        $model = $this->getModel();
        $rotations = $this->visibleQuery($model)->latest()->get();

        return view($this->getRouteName() . '.index', compact('rotations'));
    }

    // query priekš rotacijām kuras useris drīkst SKATĪTIES: savas + teamu kuros ir loceklis.
    // Rediģēt/dzēst drīkst tikai tas kurš izveidoja — to jopprojām parbauda edit/update/destroy.
    protected function visibleQuery(string $model)
    {
        return $model::with(['user:id,name', 'team:id,name'])
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhereIn('team_id', auth()->user()->teams()->pluck('teams.id'));
            });
    }
    //prieks create new rotation
    public function create()
    {
        $teams = $this->teamsPayload();

        return view($this->getRouteName() . '.create', compact('teams'));
    }
    // parāda rotaciju ja tā pieder useram VAI pieder pie teama kurā useris ir loceklis
    public function show($id)
    {
        $model = $this->getModel();
        $rotation = $this->visibleQuery($model)->where('id', $id)->first();

        if (!$rotation) {
            return redirect()->route($this->getRouteName() . '.index')
                ->with('error', 'Rotation not found.');
        }

        // player data is embedded JSON, not a real relation — the DB can't keep a saved
        // player's user_id in sync if they later leave the team, so a departed player can
        // silently linger in old lineups looking identical to a current one. Compare
        // against the team's CURRENT roster here so the view can flag anyone who's no
        // longer actually on the team.
        $currentRosterIds = $rotation->team_id
            ? Team::with('members')->find($rotation->team_id)?->members->pluck('id')->all() ?? []
            : [];

        return view($this->getRouteName() . '.show', compact('rotation', 'currentRosterIds'));
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
    // update gatavas rotacijas — shape/team validation and authorization now live in
    // RotationRequest (see app/Http/Requests/RotationRequest.php); this method is just
    // orchestration: take the already-validated input and persist it.
    public function update(RotationRequest $request, $id)
    {
        // every player key (user_id/role/pos/top/left/name) now has its own rule in
        // RotationRequest, so validated() reconstructs the full shape instead of silently
        // dropping fields — and, unlike raw input(), it also strips anything NOT declared
        // there, so a client can't smuggle arbitrary extra keys into the stored JSON.
        $type = $request->rotationType();
        $validated = $request->validated();
        $name = $validated['name'];
        $teamId = $validated['team_id'] ?? null;
        $players = $validated['players'];

        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $rotation->update([
            'name'    => $name,
            'players' => json_encode($players),
            'team_id' => $teamId,
            'type'    => $type,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $rotation->id,
            'name'    => $rotation->name,
            'type'    => $rotation->type,
            'team_id' => $rotation->team_id,
        ]);
    }
    //valide un saglaba rotacijas kuras pieder useram
    public function store(RotationRequest $request)
    {
        $type = $request->rotationType();
        $validated = $request->validated();
        $name = $validated['name'];
        $teamId = $validated['team_id'] ?? null;
        $players = $validated['players'];

        $model = $this->getModel();
        $rotation = $model::create([
            'name'    => $name,
            'players' => json_encode($players),
            'user_id' => auth()->id(),
            'team_id' => $teamId,
            'type'    => $type,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $rotation->id,
            'name'    => $rotation->name,
            'type'    => $rotation->type,
            'team_id' => $rotation->team_id,
        ]);
    }

    // teams kuras esošais useris parvalda (coach, manager vai assistant_manager)
    // ar rosteri gatavu priekš rotaciju JS
    protected function teamsPayload(): array
    {
        return auth()->user()->teams()
            ->with('members')
            ->get()
            ->filter(fn (Team $team) => $team->isManagedBy(auth()->user(), $team->members))
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