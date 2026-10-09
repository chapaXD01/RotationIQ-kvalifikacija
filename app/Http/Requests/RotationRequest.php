<?php

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

// shared store()/update() request for both attack and defence rotations — shape
// validation, team-management authorization, and the "players actually belong to this
// team" check all lived inline in BaseRotationController before; this is the extraction
// of that (the "fat controller" problem) into the one place Laravel expects it.
class RotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $teamId = $this->input('team_id');

        if (empty($teamId)) {
            return true;
        }

        $team = Team::with('members')->find($teamId);

        return $team !== null && $team->isManagedBy($this->user(), $team->members);
    }

    public function rules(): array
    {
        $type = $this->rotationType();

        $rules = [
            'name'    => 'required|string|max:255',
            'type'    => 'nullable|in:single,sequence',
            'team_id' => 'nullable|integer|exists:teams,id',
        ];

        $prefix = $type === 'sequence' ? 'players.*.*.' : 'players.*.';

        if ($type === 'sequence') {
            $rules['players']   = 'required|array|size:6';
            $rules['players.*'] = 'array|min:1';
        } else {
            $rules['players'] = 'required|array|min:1';
        }

        $rules[$prefix . 'user_id'] = 'nullable|integer';
        $rules[$prefix . 'role']    = 'required|string|in:S,MB,OH,RS,L';
        $rules[$prefix . 'pos']     = 'required|in:1,2,3,4,5,6';
        $rules[$prefix . 'top']     = 'required|numeric|between:0,400';
        $rules[$prefix . 'left']    = 'required|numeric|between:0,500';
        $rules[$prefix . 'name']    = 'nullable|string|max:255';

        return $rules;
    }

    // cross-field business rule (not a per-field shape check) — every submitted player's
    // user_id, if given, must actually be on the selected team's roster. Runs after the
    // shape rules above pass, via Laravel's validator "after" hook.
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $teamId = $this->input('team_id');

            if (empty($teamId)) {
                return;
            }

            $players = $this->flattenPlayers($this->input('players', []), $this->rotationType());

            if (!$this->playersBelongToTeam((int) $teamId, $players)) {
                $validator->errors()->add('players', 'One or more players do not belong to the selected team.');
            }
        });
    }

    public function rotationType(): string
    {
        return $this->input('type') === 'sequence' ? 'sequence' : 'single';
    }

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
}
