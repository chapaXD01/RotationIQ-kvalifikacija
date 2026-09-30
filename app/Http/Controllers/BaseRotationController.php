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
        $type = $request->input('type') === 'sequence' ? 'sequence' : 'single';

        $rules = [
            'name'    => 'required|string|max:255',
            'type'    => 'nullable|in:single,sequence',
            'team_id' => 'nullable|integer|exists:teams,id',
        ];

        if ($type === 'sequence') {
            $rules['players']             = 'required|array|size:6';
            $rules['players.*']           = 'array|min:1';
            $rules['players.*.*.user_id'] = 'nullable|integer';
        } else {
            $rules['players']             = 'required|array|min:1';
            $rules['players.*.user_id']   = 'nullable|integer';
        }

        // NB: $request->validate() only returns fields that have a validation rule. Because
        // "players.*.user_id" (or "players.*.*.user_id" for sequences) only declares a rule
        // for the user_id sub-key, Laravel's validated() rebuilds every player entry with
        // ONLY that key and silently drops role/pos/top/left/name. validate() is still run
        // for its side effect (throwing 422 on bad shapes/types) but the actual players
        // payload must come from the raw request input.
        $request->validate($rules);

        $name = $request->input('name');
        $teamId = $request->input('team_id') ?: null;
        $players = $request->input('players');

        if (!empty($teamId)) {
            abort_unless($this->userManagesTeam((int) $teamId), 403);
            abort_unless($this->playersBelongToTeam((int) $teamId, $this->flattenPlayers($players, $type)), 422, 'One or more players do not belong to the selected team.');
        }

        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $rotation->update([
            'name'    => $name,
            'players' => json_encode($players),
            'team_id' => $teamId,
            'type'    => $type,
        ]);

        return response()->json(['success' => true]);
    }
    //valide un saglaba rotacijas kuras pieder useram
    public function store(Request $request)
    {
        $type = $request->input('type') === 'sequence' ? 'sequence' : 'single';

        $rules = [
            'name'    => 'required|string|max:255',
            'type'    => 'nullable|in:single,sequence',
            'team_id' => 'nullable|integer|exists:teams,id',
        ];

        if ($type === 'sequence') {
            $rules['players']             = 'required|array|size:6';
            $rules['players.*']           = 'array|min:1';
            $rules['players.*.*.user_id'] = 'nullable|integer';
        } else {
            $rules['players']             = 'required|array|min:1';
            $rules['players.*.user_id']   = 'nullable|integer';
        }

        // see the NB in update() above — validate() only enforces the rules here, the
        // actual players payload is read from the raw request so role/pos/top/left/name
        // survive (validate()'s return value would silently strip them to just user_id)
        $request->validate($rules);

        $name = $request->input('name');
        $teamId = $request->input('team_id') ?: null;
        $players = $request->input('players');

        if (!empty($teamId)) {
            abort_unless($this->userManagesTeam((int) $teamId), 403);
            abort_unless($this->playersBelongToTeam((int) $teamId, $this->flattenPlayers($players, $type)), 422, 'One or more players do not belong to the selected team.');
        }

        $model = $this->getModel();
        $model::create([
            'name'    => $name,
            'players' => json_encode($players),
            'user_id' => auth()->id(),
            'team_id' => $teamId,
            'type'    => $type,
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

    // "sequence" tipam players ir 6 apakšmasīvi (viens katrai rotacijai) — savieno tos vienā
    // sarakstā, lai varētu izmantot to pašu team-piederības parbaudi kā "single" tipam
    protected function flattenPlayers(array $players, string $type): array
    {
        if ($type !== 'sequence') {
            return $players;
        }

        $flat = [];

        foreach ($players as $slot) {
            foreach ((array) $slot as $player) {
                $flat[] = $player;
            }
        }

        return $flat;
    }

    // parbauda vai visi players[].user_id, kas tika iesutiti, tiešām pieder pie norādītā teama
    protected function playersBelongToTeam(int $teamId, array $players): bool
    {
        $team = Team::with('members')->find($teamId);

        if (!$team) {
            return false;
        }

        $rosterIds = $team->members->pluck('id')->all();

        foreach ($players as $player) {
            $userId = $player['user_id'] ?? null;

            if ($userId !== null && !in_array((int) $userId, $rosterIds, true)) {
                return false;
            }
        }

        return true;
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