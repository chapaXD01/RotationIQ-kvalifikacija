<?php

namespace App\Http\Controllers;

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
        return view($this->getRouteName() . '.create');
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

        return view($this->getRouteName() . '.edit', compact('rotation'));
    }
    // update gatavas rotacijas
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'players' => 'required|array|min:1'
        ]);

        $model = $this->getModel();
        $rotation = $model::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $rotation->update([
            'name'    => $validated['name'],
            'players' => json_encode($validated['players'])
        ]);

        return response()->json(['success' => true]);
    }
    //valide un saglaba rotacijas kuras pieder useram
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'players' => 'required|array|min:1'
        ]);

        $model = $this->getModel();
        $model::create([
            'name'    => $validated['name'],
            'players' => json_encode($validated['players']),
            'user_id' => auth()->id()
        ]);

        return response()->json(['success' => true]);
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