<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionGroup;
use App\Models\Question;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionGroupController extends Controller
{
    /**
     * Tüm soru gruplarını listele
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $questionGroups = QuestionGroup::with(['game', 'creator'])
            ->withCount('questions')
            ->latest()
            ->paginate($perPage);

        return response()->json($questionGroups);
    }

    /**
     * Yeni bir soru grubu oluştur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'question_type' => 'required|in:multiple_choice,true_false,qa',
            'game_id' => 'required|exists:games,id',
            'question_ids' => 'required|array|min:16|max:48',
            'question_ids.*' => 'exists:questions,id',
        ]);

        // Soru tipi ve oyuna göre soruları kontrol et
        $questions = Question::whereIn('id', $validated['question_ids'])
            ->get();

        // Tüm sorular seçilen tipe uygun mu?
        $invalidTypeQuestions = $questions->filter(function ($question) use ($validated) {
            return $question->question_type !== $validated['question_type'];
        });

        if ($invalidTypeQuestions->count() > 0) {
            return response()->json([
                'message' => 'Some questions do not match the selected type',
                'invalid_questions' => $invalidTypeQuestions->pluck('id'),
            ], 422);
        }

        // Soru grubu oluştur
        $questionGroup = QuestionGroup::create([
            'name' => $validated['name'],
            'question_type' => $validated['question_type'],
            'game_id' => $validated['game_id'],
            'created_by' => Auth::id(),
        ]);

        // Soruları gruba ekle
        foreach ($validated['question_ids'] as $index => $questionId) {
            $questionGroup->questions()->attach($questionId, [
                'order' => $index + 1,
            ]);
        }

        // İlişkilerle birlikte yeni grubu döndür
        return response()->json(
            QuestionGroup::with(['game', 'creator', 'questions'])
                ->withCount('questions')
                ->find($questionGroup->id),
            201
        );
    }

    /**
     * Belirli bir soru grubunu göster
     */
    public function show(QuestionGroup $questionGroup)
    {
        $questionGroup->load(['game', 'creator', 'questions.answers']);
        $questionGroup->loadCount('questions');

        return response()->json($questionGroup);
    }

    /**
     * Bir soru grubunu güncelle
     */
    public function update(Request $request, QuestionGroup $questionGroup)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'question_ids' => 'sometimes|required|array|min:16|max:48',
            'question_ids.*' => 'exists:questions,id',
        ]);

        // Grup adını güncelle
        if (isset($validated['name'])) {
            $questionGroup->update([
                'name' => $validated['name'],
            ]);
        }

        // Soruları güncelle
        if (isset($validated['question_ids'])) {
            // Tüm sorular doğru tipe sahip mi kontrol et
            $questions = Question::whereIn('id', $validated['question_ids'])
                ->get();

            $invalidTypeQuestions = $questions->filter(function ($question) use ($questionGroup) {
                return $question->question_type !== $questionGroup->question_type;
            });

            if ($invalidTypeQuestions->count() > 0) {
                return response()->json([
                    'message' => 'Some questions do not match the group question type',
                    'invalid_questions' => $invalidTypeQuestions->pluck('id'),
                ], 422);
            }

            // Mevcut soruları kaldır ve yenilerini ekle
            $questionGroup->questions()->detach();

            foreach ($validated['question_ids'] as $index => $questionId) {
                $questionGroup->questions()->attach($questionId, [
                    'order' => $index + 1,
                ]);
            }
        }

        // Güncellenmiş grubu döndür
        $questionGroup->load(['game', 'creator', 'questions']);
        $questionGroup->loadCount('questions');

        return response()->json($questionGroup);
    }

    /**
     * Bir soru grubunu sil
     */
    public function destroy(QuestionGroup $questionGroup)
    {
        // Grup sorularının ilişkisi pivot tablosunda otomatik silinecek (cascade)
        $questionGroup->delete();

        return response()->json(null, 204);
    }

    /**
     * Kod ile soru grubunu ve sorularını getir
     */
    public function getByCode($code)
    {
        $questionGroup = QuestionGroup::where('code', $code)
            ->with(['questions.answers', 'game'])
            ->withCount('questions')
            ->firstOrFail();

        return response()->json($questionGroup);
    }

    /**
     * Belirli bir oyun ve soru tipine uyan soruları getir (grup oluşturma için)
     */
    public function getEligibleQuestions(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'question_type' => 'required|in:multiple_choice,true_false,qa',
        ]);

        $questions = Question::with(['category'])
            ->where('question_type', $validated['question_type'])
            ->whereHas('games', function ($query) use ($validated) {
                $query->where('games.id', $validated['game_id']);
            })
            ->latest()
            ->paginate(30);

        return response()->json($questions);
    }
}
