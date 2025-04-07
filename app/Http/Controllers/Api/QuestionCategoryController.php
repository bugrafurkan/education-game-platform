<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

class QuestionCategoryController extends Controller
{
    public function index()
    {
        $categories = QuestionCategory::with(['grade', 'subject', 'unit', 'topic'])->get();
        return response()->json($categories);
    }

    public function show(QuestionCategory $category)
    {
        $category->load(['grade', 'subject', 'unit', 'topic']);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'unit_id' => 'required|exists:units,id',
            'topic_id' => 'required|exists:topics,id',
        ]);

        $category = QuestionCategory::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, QuestionCategory $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'grade_id' => 'sometimes|exists:grades,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'unit_id' => 'sometimes|exists:units,id',
            'topic_id' => 'sometimes|exists:topics,id',
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(QuestionCategory $category)
    {
        // Eğer kategoriye ait soru varsa silinemez
        if ($category->questions()->exists()) {
            return response()->json([
                'message' => 'Bu kategoriye ait sorular bulunduğu için silinemez.'
            ], 400);
        }

        $category->delete();
        return response()->json(null, 204);
    }

    public function filter($gradeId = null, $subjectId = null)
    {
        $query = QuestionCategory::with(['grade', 'subject', 'unit', 'topic']);

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return response()->json($query->get());
    }

    // UNITY tarafı
    public function unityIndex()
    {
        $categories = QuestionCategory::with(['grade', 'subject', 'unit', 'topic'])->get();
        return response()->json($categories);
    }

    public function unityShow(QuestionCategory $category)
    {
        $category->load(['grade', 'subject', 'unit', 'topic']);
        return response()->json($category);
    }

    public function unityFilter($gradeId = null, $subjectId = null)
    {
        $query = QuestionCategory::with(['grade', 'subject', 'unit', 'topic']);

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return response()->json($query->get());
    }
}

