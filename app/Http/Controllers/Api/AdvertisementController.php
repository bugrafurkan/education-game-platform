<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of advertisements.
     */
    public function index()
    {
        $advertisements = Advertisement::latest()->paginate(20);
        return response()->json($advertisements);
    }

    /**
     * Store a newly created advertisement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:banner,video',
            'media_path' => 'required|string',
            'target_grade' => 'nullable|string|max:50',
            'target_subject' => 'nullable|string|max:50',
            'target_game_type' => 'nullable|string|in:jeopardy,wheel',
            'link_url' => 'nullable|string|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $advertisement = Advertisement::create($validated);

        return response()->json($advertisement, 201);
    }

    /**
     * Display the specified advertisement.
     */
    public function show(Advertisement $advertisement)
    {
        return response()->json($advertisement);
    }

    /**
     * Update the specified advertisement.
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:banner,video',
            'media_path' => 'sometimes|required|string',
            'target_grade' => 'nullable|string|max:50',
            'target_subject' => 'nullable|string|max:50',
            'target_game_type' => 'nullable|string|in:jeopardy,wheel',
            'link_url' => 'nullable|string|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $advertisement->update($validated);

        return response()->json($advertisement);
    }

    /**
     * Remove the specified advertisement.
     */
    public function destroy(Advertisement $advertisement)
    {
        $advertisement->delete();

        return response()->json(null, 204);
    }

    /**
     * Upload advertisement media (banner image or video).
     */
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'type' => 'required|in:banner,video',
            'media' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('media');

        // Validate file type based on advertisement type
        if ($request->type === 'banner') {
            $request->validate([
                'media' => 'image|mimes:jpeg,png,jpg,svg',
            ]);

            $folder = 'ads-banners';
        } else {
            $request->validate([
                'media' => 'mimetypes:video/mp4',
            ]);

            $folder = 'ads-videos';
        }

        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        // Store the file
        $path = $file->storeAs("public/{$folder}", $filename);

        // Return the URL
        return response()->json([
            'url' => Storage::url($path)
        ]);
    }

    /**
     * Get active advertisements for a game.
     */
    public function getActiveAds($grade = null, $subject = null, $gameType = null)
    {
        $ads = Advertisement::active()
            ->targeted($grade, $subject, $gameType)
            ->get();

        return response()->json($ads);
    }
}
