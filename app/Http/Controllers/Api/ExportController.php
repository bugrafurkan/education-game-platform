<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Export;
use App\Models\QuestionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExportController extends Controller
{
    public function createAndTriggerBuild(Request $request)
    {
        $request->validate([
            'question_group_id' => 'required|exists:question_groups,id',
            'game_id' => 'required|exists:games,id',
        ]);

        // Soru grubu + soruları al
        $group = QuestionGroup::with('questions.answers')->findOrFail($request->question_group_id);

        // JSON verisi oluştur
        $questions = $group->questions->map(function ($question) {
            $answers = $question->answers->map(function ($answer) {
                return [
                    'text' => $answer->answer_text,
                    'is_correct' => $answer->is_correct,
                    'image' => $answer->image_path ? asset('storage/' . $answer->image_path) : null,
                ];
            });

            return [
                'id' => $question->id,
                'text' => $question->question_text,
                'type' => $question->question_type,
                'difficulty' => $question->difficulty,
                'image' => $question->image_path ? asset('storage/' . $question->image_path) : null,
                'options' => $answers,
            ];
        });

        $config = [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
            ],
            'questions' => $questions,
        ];

        // Export kaydını oluştur
        $export = Export::create([
            'question_group_id' => $group->id,
            'game_id' => $request->game_id,
            'status' => 'pending',
            'requested_at' => now(),
            'config_snapshot' => $config,
        ]);

        // Burada Jenkins'e build isteği gönderilebilir (örnek)
        /*
        Http::asForm()->post(env('JENKINS_EXPORT_URL'), [
        'token' => env('JENKINS_API_TOKEN'),
        'EXPORT_ID' => $export->id,
        'QUESTION_GROUP_ID' => $export->question_group_id,
        'GAME_ID' => $export->game_id,
    ]);
        */

        return response()->json([
            'message' => 'Export kaydı oluşturuldu, build süreci başlatıldı.',
            'export_id' => $export->id,
        ]);
    }

    // Build tamamlandıktan sonra Jenkins bu metodu çağırabilir
    public function markAsCompleted(Request $request)
    {
        $request->validate([
            'export_id' => 'required|exists:exports,id',
            'output_url' => 'required|url',
        ]);

        $export = Export::findOrFail($request->export_id);
        $export->update([
            'status' => 'done',
            'output_url' => $request->output_url,
            'completed_at' => now(),
        ]);

        return response()->json(['message' => 'Export tamamlandı.']);
    }

    // Hata durumunda Jenkins burayı çağırabilir
    public function markAsFailed(Request $request)
    {
        $request->validate([
            'export_id' => 'required|exists:exports,id',
            'error_message' => 'required|string',
        ]);

        $export = Export::findOrFail($request->export_id);
        $export->update([
            'status' => 'failed',
            'error_message' => $request->error_message,
        ]);

        return response()->json(['message' => 'Export başarısız olarak işaretlendi.']);
    }

    /**
     * Tek bir export kaydını getir
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $export = Export::findOrFail($id);
        return response()->json($export);
    }

    /**
     * Tüm export kayıtlarını getir (sayfalama ile)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $exports = Export::orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($exports);
    }

    /**
     * Export dosyasını indirme
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id)
    {
        $export = Export::findOrFail($id);

        // Eğer output_url bir dosya yoluysa
        if ($export->output_url && file_exists(public_path($export->output_url))) {
            return response()->download(public_path($export->output_url));
        }

        // Eğer output_url bir URL ise, kullanıcıyı yönlendir
        if ($export->output_url && filter_var($export->output_url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'download_url' => $export->output_url
            ]);
        }

        return response()->json([
            'error' => 'İndirilebilir dosya bulunamadı.'
        ], 404);
    }



    /**
     * Export işlemini iptal et
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        $export = Export::findOrFail($id);

        // Sadece bekleyen veya işlemdeki exportlar iptal edilebilir
        if (!in_array($export->status, ['pending', 'processing'])) {
            return response()->json([
                'error' => 'Sadece bekleyen veya işlemdeki exportlar iptal edilebilir.'
            ], 400);
        }

        // Burada Jenkins'teki işi iptal etmek için ek adımlar olabilir

        $export->update([
            'status' => 'failed',
            'error_message' => 'Export işlemi kullanıcı tarafından iptal edildi.'
        ]);

        return response()->json([
            'message' => 'Export işlemi iptal edildi.'
        ]);
    }

    /**
     * Export işlemini yeniden dene
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function retry($id)
    {
        $export = Export::findOrFail($id);

        // Sadece başarısız olan exportlar yeniden denenebilir
        if ($export->status !== 'failed') {
            return response()->json([
                'error' => 'Sadece başarısız olan exportlar yeniden denenebilir.'
            ], 400);
        }

        // Export durumunu güncelle
        $export->update([
            'status' => 'pending',
            'error_message' => null
        ]);

        // Jenkins'e yeniden build isteği gönder
        /*
        Http::asForm()->post(env('JENKINS_EXPORT_URL'), [
            'token' => env('JENKINS_API_TOKEN'),
            'EXPORT_ID' => $export->id,
            'QUESTION_GROUP_ID' => $export->question_group_id,
            'GAME_ID' => $export->game_id,
        ]);
        */

        return response()->json([
            'message' => 'Export işlemi yeniden başlatıldı.',
            'export' => $export
        ]);
    }
}
