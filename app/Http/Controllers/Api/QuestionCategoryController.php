<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

class QuestionCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = QuestionCategory::orderBy('grade')->orderBy('subject')->orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|string|max:50',
            'subject' => 'required|string|max:50',
            'unit' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category = QuestionCategory::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified category.
     */
    public function show(QuestionCategory $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, QuestionCategory $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'grade' => 'sometimes|required|string|max:50',
            'subject' => 'sometimes|required|string|max:50',
            'unit' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(QuestionCategory $category)
    {
        // Check if the category has questions
        if ($category->questions()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with questions'
            ], 400);
        }

        $category->delete();

        return response()->json(null, 204);
    }

    /**
     * Filter categories by grade and subject
     */
    public function filter($grade = null, $subject = null)
    {
        $categories = QuestionCategory::filter($grade, $subject)->get();

        return response()->json($categories);
    }
}
