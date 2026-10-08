<?php

namespace App\Http\Controllers;

use App\Models\MovingPlayer;
use Illuminate\Http\Request;

class MovingPlayerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $movements = MovingPlayer::where('user_id', auth()->id())->latest()->get();

        return view('movingplayers.index', compact('movements'));
    }

    public function create()
    {
        return view('movingplayers.create');
    }

    public function show($id)
    {
        $movement = MovingPlayer::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$movement) {
            return redirect()->route('movingplayers.index')->with('error', 'Movement not found.');
        }

        return view('movingplayers.show', compact('movement'));
    }

    public function edit($id)
    {
        $movement = MovingPlayer::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$movement) {
            return redirect()->route('movingplayers.index')->with('error', 'Movement not found.');
        }

        return view('movingplayers.edit', compact('movement'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->playerValidationRules());

        $movement = MovingPlayer::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $movement->update([
            'name' => $validated['name'],
            'players' => json_encode($validated['players'])
        ]);

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->playerValidationRules());

        MovingPlayer::create([
            'name' => $validated['name'],
            'players' => json_encode($validated['players']),
            'user_id' => auth()->id()
        ]);

        return response()->json(['success' => true]);
    }

    // same shape/type validation added to BaseRotationController — role/pos/top/left
    // were never checked here, so a malformed request could save junk court data the
    // same way the old corrupted rotation row happened
    protected function playerValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'players' => 'required|array|min:1',
            'players.*.role' => 'required|string|in:S,MB,OH,RS,L',
            'players.*.pos' => 'required|in:1,2,3,4,5,6',
            'players.*.top' => 'required|numeric|between:0,400',
            'players.*.left' => 'required|numeric|between:0,500',
        ];
    }

    public function destroy($id)
    {
        $movement = MovingPlayer::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $movement->delete();

        return redirect()->route('movingplayers.index')->with('success', 'Movement deleted successfully.');
    }

    public function animate($id)
    {
        $movement = MovingPlayer::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$movement) {
            return redirect()->route('movingplayers.index')->with('error', 'Movement not found.');
        }

        return view('movingplayers.animate', compact('movement'));
    }
}
