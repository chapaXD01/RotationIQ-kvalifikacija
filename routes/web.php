<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DefenceController;
use App\Http\Controllers\AttackController;
use App\Http\Controllers\MovingPlayerController;
use App\Http\Controllers\TeamController;
use App\Models\AttackRotation;
use App\Models\DefenceRotation;
use App\Models\TeamAnnouncement;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    $userId = auth()->id();
    $teams = auth()->user()->teams()->with(['coach', 'members'])->latest()->get();

    $stats = [
        'teams' => $teams->count(),
        'attack' => AttackRotation::where('user_id', $userId)->count(),
        'defence' => DefenceRotation::where('user_id', $userId)->count(),
    ];

    $recentRotations = AttackRotation::where('user_id', $userId)->latest()->limit(5)->get()
        ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'type' => $r->type, 'kind' => 'attack', 'created_at' => $r->created_at])
        ->concat(
            DefenceRotation::where('user_id', $userId)->latest()->limit(5)->get()
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'type' => $r->type, 'kind' => 'defence', 'created_at' => $r->created_at])
        )
        ->sortByDesc('created_at')
        ->take(5)
        ->values();

    $announcements = TeamAnnouncement::whereIn('team_id', $teams->pluck('id'))
        ->with(['user:id,name', 'team:id,name'])
        ->latest()
        ->limit(4)
        ->get();

    return view('dashboard', compact('teams', 'stats', 'recentRotations', 'announcements'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/join', [TeamController::class, 'join'])->name('teams.join');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams/{team}/members/{user}/role', [TeamController::class, 'updateRole'])->name('teams.members.role');
    Route::post('/teams/{team}/members/{user}/position', [TeamController::class, 'updatePosition'])->name('teams.members.position');
    Route::post('/teams/{team}/members/{user}/attendance', [TeamController::class, 'updateAttendance'])->name('teams.members.attendance');
    Route::get('/teams/{team}/messages', [TeamController::class, 'loadMoreMessages'])->name('teams.messages.index');
    Route::post('/teams/{team}/messages', [TeamController::class, 'storeMessage'])->name('teams.messages.store')->middleware('throttle:10,1');
    Route::post('/teams/{team}/announcements', [TeamController::class, 'storeAnnouncement'])->name('teams.announcements.store');
    Route::delete('/teams/{team}/announcements/{announcement}', [TeamController::class, 'destroyAnnouncement'])->name('teams.announcements.destroy');
    Route::delete('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    // Defence Rotations Routes
    Route::resource('defence', DefenceController::class);

    // Attack Rotations Routes
    Route::resource('attack', AttackController::class);

    // Moving Players Routes
    Route::resource('movingplayers', MovingPlayerController::class);

    // Animator view for a specific movement
    Route::get('/movingplayers/{id}/animate', [MovingPlayerController::class, 'animate'])->name('movingplayers.animate');
});

require __DIR__.'/auth.php';
