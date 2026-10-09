<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['coach_id', 'name', 'join_code'];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withPivot('position')
            ->withPivot('attendance')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(TeamMessage::class)->oldest()->oldest('id');
    }

    public function announcements()
    {
        return $this->hasMany(TeamAnnouncement::class)->latest()->latest('id');
    }

    public function manager()
    {
        return $this->members()->where(function ($query) {
            $query->where('team_user.role', 'manager')
                ->orWhere('users.id', $this->coach_id);
        })->first();
    }

    public function assistantManagers()
    {
        return $this->members()->wherePivot('role', 'assistant_manager')->get();
    }

    public function students()
    {
        return $this->members()
            ->wherePivot('role', 'student')
            ->where('users.id', '!=', $this->coach_id)
            ->get();
    }

    // whether $user is this team's coach, manager, or assistant manager — i.e. allowed to
    // manage its roster/rotations. Pass $members (already-loaded) to avoid a query when the
    // caller already has them; moved here from BaseRotationController so it's reusable from
    // anywhere that needs the same check (rotation authorization, team settings, etc.)
    public function isManagedBy(User $user, $members = null): bool
    {
        if ($this->coach_id === $user->id) {
            return true;
        }

        $members ??= $this->members;
        $role = $members->firstWhere('id', $user->id)?->pivot->role;

        return in_array($role, ['manager', 'assistant_manager'], true);
    }
}
