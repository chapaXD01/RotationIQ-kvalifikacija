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
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(TeamMessage::class)->latest();
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
}
