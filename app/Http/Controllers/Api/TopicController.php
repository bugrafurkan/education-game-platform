<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;

class TopicController extends Controller
{
    public function index()
    {
        return Topic::with('unit')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        $topic = Topic::create($validated);

        return response()->json($topic, 201);
    }

    public function update(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
        ]);

        $topic->update($validated);

        return response()->json($topic);
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return response()->json(['message' => 'Konu silindi.']);
    }
}
