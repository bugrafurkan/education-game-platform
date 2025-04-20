<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\QuestionGroup;
use Illuminate\Support\Facades\Log;

class JenkinsController extends Controller
{
    /**
     * Jenkins sunucu bilgileri
     */
    protected $jenkinsUrl;
    protected $jenkinsUser;
    protected $jenkinsToken;

    public function __construct()
    {
        $this->jenkinsUrl = env('JENKINS_URL', 'http://your-jenkins-server:8080');
        $this->jenkinsUser = env('JENKINS_USER', 'jenkins_user');
        $this->jenkinsToken = env('JENKINS_TOKEN', 'your-api-token');
    }

    /**
     * Soru grubu için iframe oluşturma işlemini başlat
     */
    public function createIframe($groupId)
    {
        // Soru grubunu veritabanından al
        $questionGroup = QuestionGroup::findOrFail($groupId);

        // Soru grubu özel kodu yoksa oluştur
        if (empty($questionGroup->code)) {
            $questionGroup->code = $questionGroup->generateUniqueCode();
            $questionGroup->save();
        }

        // Jenkins job tetikleme parametreleri
        $params = [
            'CODE' => $questionGroup->code,
            'GROUP_NAME' => $questionGroup->name,
            'API_TOKEN' => $this->generateApiToken($questionGroup->id),
            'GROUP_ID' => $questionGroup->id
        ];

        try {
            // Jenkins API'yi çağırarak job'u başlat
            $response = Http::withBasicAuth($this->jenkinsUser, $this->jenkinsToken)
                ->post("{$this->jenkinsUrl}/job/question-group-json-creator/buildWithParameters", $params);

            // İşlemin durumunu güncelle
            if ($response->successful()) {
                $questionGroup->iframe_status = 'processing';
                $questionGroup->save();

                return response()->json([
                    'success' => true,
                    'message' => 'İframe oluşturma işlemi başlatıldı.',
                    'job_id' => $response->header('Location')
                ]);
            } else {
                Log::error('Jenkins job başlatma hatası', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Jenkins job başlatılamadı: ' . $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Jenkins bağlantı hatası', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Jenkins sunucusuna bağlanılamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Jenkins'ten gelen callback işleme
     */
    public function jenkinsCallback(Request $request)
    {
        // Jenkins'ten gelen verileri doğrula
        $request->validate([
            'group_id' => 'required|integer',
            'status' => 'required|string',
            'iframe_url' => 'required_if:status,success|string'
        ]);

        // Soru grubunu bul
        $questionGroup = QuestionGroup::findOrFail($request->group_id);

        // İşlem başarılıysa
        if ($request->status === 'success') {
            // İframe bilgilerini kaydet
            $questionGroup->iframe_url = $request->iframe_url;
            $questionGroup->iframe_code = $this->generateIframeCode(
                $request->iframe_url,
                $questionGroup->name
            );
            $questionGroup->iframe_status = 'completed';
            $questionGroup->save();

            return response()->json([
                'success' => true,
                'message' => 'İframe başarıyla oluşturuldu.'
            ]);
        } else {
            // Başarısızlık durumunda
            $questionGroup->iframe_status = 'failed';
            $questionGroup->save();

            Log::error('Jenkins iframe oluşturma hatası', [
                'group_id' => $request->group_id,
                'error' => $request->error ?? 'Bilinmeyen hata'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'İframe oluşturma başarısız: ' . ($request->error ?? 'Bilinmeyen hata')
            ]);
        }
    }

    /**
     * API token oluşturma (geçici erişim için)
     */
    private function generateApiToken($groupId)
    {
        // Gerçek uygulamada daha güvenli bir yöntem kullanılmalı
        return hash('sha256', $groupId . env('APP_KEY') . time());
    }

    /**
     * İframe HTML kodu oluşturma
     */
    private function generateIframeCode($url, $title)
    {
        return '<iframe
            src="' . $url . '"
            width="960"
            height="600"
            style="border:none;"
            allow="autoplay; fullscreen; pointer-lock; gamepad"
            title="Soru Grubu: ' . htmlspecialchars($title) . '">
        </iframe>';
    }
}
