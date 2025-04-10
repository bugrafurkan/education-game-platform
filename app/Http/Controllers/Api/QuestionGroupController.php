<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionGroup;
use App\Models\Question;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class QuestionGroupController extends Controller
{
    /**
     * Tüm soru gruplarını listele
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $questionGroups = QuestionGroup::with(['game', 'creator', 'category'])
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'question_type' => 'required|in:multiple_choice,true_false,qa',
            'game_id' => 'required|exists:games,id',
            'category_id' => 'nullable|exists:question_categories,id',
            'question_ids' => 'required|array|min:16|max:48',
            'question_ids.*' => 'exists:questions,id',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

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
        // Görsel işleme
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/question_groups');
        }

        // Soru grubu oluştur
        $questionGroup = QuestionGroup::create([
            'name' => $validated['name'],
            'question_type' => $validated['question_type'],
            'game_id' => $validated['game_id'],
            'category_id' => $validated['category_id'] ?? null,
            'created_by' => Auth::id(),
            'image_path' => $imagePath,
        ]);

        // Soruları gruba ekle
        foreach ($validated['question_ids'] as $index => $questionId) {
            $questionGroup->questions()->attach($questionId, [
                'order' => $index + 1,
            ]);
        }

        // İlişkilerle birlikte yeni grubu döndür
        return response()->json(
            QuestionGroup::with(['game', 'creator', 'category', 'questions'])
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
        $questionGroup->load(['game', 'creator', 'category', 'questions.answers']);
        $questionGroup->loadCount('questions');

        return response()->json($questionGroup);
    }

    /**
     * Bir soru grubunu güncelle
     */
    public function update(Request $request, QuestionGroup $questionGroup)
    {
        // Form data ile gönderilmişse PUT/PATCH metodu kontrolü
        $method = $request->input('_method', $request->method());

        // Form data ile PUT/PATCH istekleri için
        if (in_array($method, ['PUT', 'PATCH']) && $request->isMethod('POST')) {
            $request->setMethod($method);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'nullable|exists:question_categories,id',
            'question_ids' => 'sometimes|required|array|min:16|max:48',
            'question_ids.*' => 'exists:questions,id',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $updateData = [];

        // Grup adını güncelle
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        // Kategori ID'sini güncelle (null olabilir)
        if (array_key_exists('category_id', $validated)) {
            $updateData['category_id'] = $validated['category_id'];
        }

        // Görseli güncelle
        if ($request->hasFile('image')) { // 'image_path' yerine 'image' olarak değiştirin
            // Eski görseli sil
            if ($questionGroup->image_path) {
                Storage::disk('public')->delete($questionGroup->image_path);
            }

            // Yeni görseli yükle
            $updateData['image_path'] = $request->file('image')->store('public/question_groups');
        }

        // Grup verilerini güncelle
        if (!empty($updateData)) {
            $questionGroup->update($updateData);
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
        $questionGroup->load(['game', 'creator', 'category', 'questions']);
        $questionGroup->loadCount('questions');

        return response()->json($questionGroup);
    }

    /**
     * Bir soru grubunu sil
     */
    public function destroy(QuestionGroup $questionGroup)
    {
        // Eğer görsel varsa sil
        if ($questionGroup->image_path) {
            Storage::disk('public')->delete($questionGroup->image_path);
        }

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
            ->with(['questions.answers', 'game', 'category'])
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

    /**
     * Görsel yükleme endpoint'i
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // 2MB max (Question ile aynı)
        ]);

        $path = $request->file('image')->store('public/question_groups');
        $url = Storage::url($path);

        return response()->json(['url' => $url]);
    }
}
