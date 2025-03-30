<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $generalSettings = Setting::where('category', 'general')->pluck('value', 'key')->toArray();
        $adSettings = Setting::where('category', 'advertisements')->pluck('value', 'key')->toArray();

        return response()->json([
            'general' => $generalSettings,
            'advertisements' => $adSettings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'general' => 'sometimes|array',
            'advertisements' => 'sometimes|array',
        ]);

        // Her kategori için ayarları güncelle
        foreach ($validated as $category => $settings) {
            foreach ($settings as $key => $value) {
                // Dizileri JSON olarak kaydet
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                Setting::updateOrCreate(
                    ['category' => $category, 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        return $this->index();
    }

    public function uploadAd(Request $request)
    {
        $request->validate([
            'app_name' => 'sometimes|string|max:255',
            'logo_url' => 'sometimes|string|max:255',
            'theme_color' => 'sometimes|string|max:255',
            'ads_enabled' => 'required|in:true,false',
            'ad_type' => 'required|in:image,video',
            'ad_file' => 'required|file|max:20480', // 20MB maksimum dosya boyutu
        ]);

        // Genel ayarları güncelle
        if ($request->has('app_name')) {
            Setting::updateOrCreate(
                ['category' => 'general', 'key' => 'app_name'],
                ['value' => $request->app_name]
            );
        }

        if ($request->has('logo_url')) {
            Setting::updateOrCreate(
                ['category' => 'general', 'key' => 'logo_url'],
                ['value' => $request->logo_url]
            );
        }

        if ($request->has('theme_color')) {
            Setting::updateOrCreate(
                ['category' => 'general', 'key' => 'theme_color'],
                ['value' => $request->theme_color]
            );
        }

        // Reklam ayarlarını güncelle
        Setting::updateOrCreate(
            ['category' => 'advertisements', 'key' => 'ads_enabled'],
            ['value' => $request->ads_enabled]
        );

        Setting::updateOrCreate(
            ['category' => 'advertisements', 'key' => 'ad_type'],
            ['value' => $request->ad_type]
        );

        // Dosya yükleme işlemi
        if ($request->hasFile('ad_file')) {
            $file = $request->file('ad_file');

            // Dosya türünü kontrol et
            $fileType = $request->ad_type;
            $allowedMimeTypes = $fileType === 'image'
                ? ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml']
                : ['video/mp4', 'video/webm', 'video/ogg'];

            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                return response()->json([
                    'message' => 'Geçersiz dosya türü. Lütfen ' .
                        ($fileType === 'image' ? 'bir görsel' : 'bir video') .
                        ' dosyası yükleyin.'
                ], 422);
            }

            // Eski dosyayı sil (eğer varsa)
            $oldFilePath = Setting::where('category', 'advertisements')
                ->where('key', 'ad_file_path')
                ->value('value');

            if ($oldFilePath && Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }

            // Yeni dosyayı kaydet
            $path = $file->store('ads', 'public');

            // Dosya yolunu kaydet
            Setting::updateOrCreate(
                ['category' => 'advertisements', 'key' => 'ad_file_path'],
                ['value' => $path]
            );

            // Dosyanın URL'ini kaydet
            $url = Storage::url($path);
            Setting::updateOrCreate(
                ['category' => 'advertisements', 'key' => 'ad_file_url'],
                ['value' => $url]
            );
        }

        return $this->index();
    }
}
