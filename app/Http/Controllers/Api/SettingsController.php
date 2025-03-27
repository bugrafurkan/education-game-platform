<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $generalSettings = Setting::where('category', 'general')->pluck('value', 'key')->toArray();
        $fernusSettings = Setting::where('category', 'fernus')->pluck('value', 'key')->toArray();
        $adSettings = Setting::where('category', 'advertisements')->pluck('value', 'key')->toArray();

        return response()->json([
            'general' => $generalSettings,
            'fernus' => $fernusSettings,
            'advertisements' => $adSettings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'general' => 'sometimes|array',
            'fernus' => 'sometimes|array',
            'advertisements' => 'sometimes|array',
        ]);

        // Her kategori için ayarları güncelle
        foreach ($validated as $category => $settings) {
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['category' => $category, 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        return $this->index();
    }
}
