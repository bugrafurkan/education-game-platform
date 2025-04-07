<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
    public function index()
    {
        return Grade::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $grade = Grade::create($validated);

        return response()->json($grade, 201);
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $grade->update($validated);

        return response()->json($grade);
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json(['message' => 'Sınıf silindi.']);
    }
}
