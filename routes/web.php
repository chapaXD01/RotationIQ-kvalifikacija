<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DefenceController;
use App\Http\Controllers\AttackController;
use App\Http\Controllers\MovingPlayerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Defence Rotations Routes
Route::resource('defence', DefenceController::class);

// Attack Rotations Routes
Route::resource('attack', AttackController::class);

// Moving Players Routes
Route::resource('movingplayers', MovingPlayerController::class);

// Animator view for a specific movement
Route::get('/movingplayers/{id}/animate', [MovingPlayerController::class, 'animate'])->name('movingplayers.animate');

Route::post('/defence-rotations', [DefenceController::class, 'store']);
Route::patch('/defence-rotations/{id}', [DefenceController::class, 'update']);
Route::post('/attack-rotations', [AttackController::class, 'store']);
Route::patch('/attack-rotations/{id}', [AttackController::class, 'update']);

require __DIR__.'/auth.php';
