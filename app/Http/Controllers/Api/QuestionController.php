<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $questions = Question::with(['category', 'answers'])->latest()->paginate($perPage);

        return response()->json($questions);
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,qa',
            'difficulty' => 'required|in:easy,medium,hard',
            'image_path' => 'nullable|string',
            'metadata' => 'nullable|array',
            'answers' => 'required|array|min:1',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'answers.*.image_path' => 'nullable|string',
        ]);

        // Create the question
        $question = Question::create([
            'category_id' => $validated['category_id'],
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'difficulty' => $validated['difficulty'],
            'image_path' => $validated['image_path'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        // Create the answers
        foreach ($validated['answers'] as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answerData['answer_text'],
                'is_correct' => $answerData['is_correct'],
                'image_path' => $answerData['image_path'] ?? null,
            ]);
        }

        // Return the question with relations
        return response()->json(
            Question::with(['category', 'answers'])->find($question->id),
            201
        );
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        $question->load(['category', 'answers']);
        return response()->json($question);
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:question_categories,id',
            'question_text' => 'sometimes|required|string',
            'question_type' => 'sometimes|required|in:multiple_choice,true_false,qa',
            'difficulty' => 'sometimes|required|in:easy,medium,hard',
            'image_path' => 'nullable|string',
            'metadata' => 'nullable|array',
            'answers' => 'sometimes|required|array|min:1',
            'answers.*.id' => 'nullable|exists:answers,id',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'answers.*.image_path' => 'nullable|string',
        ]);

        // Update the question
        $question->update([
            'category_id' => $validated['category_id'] ?? $question->category_id,
            'question_text' => $validated['question_text'] ?? $question->question_text,
            'question_type' => $validated['question_type'] ?? $question->question_type,
            'difficulty' => $validated['difficulty'] ?? $question->difficulty,
            'image_path' => $validated['image_path'] ?? $question->image_path,
            'metadata' => $validated['metadata'] ?? $question->metadata,
        ]);

        // Update answers if provided
        if (isset($validated['answers'])) {
            // Get existing answer IDs
            $existingAnswerIds = $question->answers->pluck('id')->toArray();
            $newAnswerIds = [];

            foreach ($validated['answers'] as $answerData) {
                if (isset($answerData['id'])) {
                    // Update existing answer
                    $answer = Answer::find($answerData['id']);
                    $answer->update([
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct'],
                        'image_path' => $answerData['image_path'] ?? $answer->image_path,
                    ]);
                    $newAnswerIds[] = $answer->id;
                } else {
                    // Create new answer
                    $answer = Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct'],
                        'image_path' => $answerData['image_path'] ?? null,
                    ]);
                    $newAnswerIds[] = $answer->id;
                }
            }

            // Delete answers that are not in the new set
            $answersToDelete = array_diff($existingAnswerIds, $newAnswerIds);
            if (!empty($answersToDelete)) {
                Answer::whereIn('id', $answersToDelete)->delete();
            }
        }

        // Return the updated question
        $question->load(['category', 'answers']);
        return response()->json($question);
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question)
    {
        // Check if the question is used in any game
        if ($question->games()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete question used in games'
            ], 400);
        }

        // Delete the question (answers will be cascade deleted)
        $question->delete();

        return response()->json(null, 204);
    }

    /**
     * Filter questions by various criteria.
     */
    public function filter(Request $request)
    {
        $query = Question::with(['category', 'answers']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('question_text', 'LIKE', "%{$search}%");
        }

        if ($request->has('type')) {
            $query->where('question_type', $request->input('type'));
        }

        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->input('difficulty'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $perPage = $request->input('per_page', 10);
        $questions = $query->latest()->paginate($perPage);

        return response()->json($questions);
    }

    /**
     * Upload question image.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // 2MB max
        ]);

        $path = $request->file('image')->store('public/questions');
        $url = Storage::url($path);

        return response()->json(['url' => $url]);
    }
}
