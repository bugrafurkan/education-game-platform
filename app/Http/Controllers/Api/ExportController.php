<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Export;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    /**
     * Display a listing of exports.
     */
    public function index(Request $request)
    {
        $exports = Export::with(['game', 'creator'])
            ->latest()
            ->paginate(20);

        return response()->json($exports);
    }

    /**
     * Store a newly created export.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
        ]);

        $game = Game::findOrFail($validated['game_id']);

        // Check if game has questions
        if ($game->questions()->count() === 0) {
            return response()->json([
                'message' => 'Game has no questions to export'
            ], 400);
        }

        // Get latest version or start at 1.0
        $latestExport = Export::where('game_id', $game->id)
            ->orderBy('id', 'desc')
            ->first();

        $version = $latestExport ? $this->incrementVersion($latestExport->version) : '1.0';

        // Create the export
        $export = Export::create([
            'game_id' => $game->id,
            'version' => $version,
            'status' => 'pending',
            'created_by' => $request->user()->id,
            'config_snapshot' => json_decode($game->generateJsonConfig(), true),
        ]);

        // Generate config file
        $configPath = $export->generateConfigFile();
        $downloadUrl = url("api/exports/{$export->id}/download");

        // Update the export with download URL
        $export->update([
            'download_url' => $downloadUrl,
            'status' => 'completed'
        ]);

        return response()->json($export, 201);
    }

    /**
     * Display the specified export.
     */
    public function show(Export $export)
    {
        $export->load(['game', 'creator']);
        return response()->json($export);
    }

    /**
     * Upload an export to Fernus platform.
     */
    public function uploadToFernus(Export $export)
    {
        // Check if export is already complete
        if ($export->status !== 'completed') {
            return response()->json([
                'message' => 'Export is not completed yet'
            ], 400);
        }

        // Simulate Fernus upload
        $result = $export->uploadToFernus();

        if ($result) {
            return response()->json([
                'message' => 'Export uploaded to Fernus successfully',
                'fernus_url' => $export->fernus_url
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to upload export to Fernus'
            ], 500);
        }
    }

    /**
     * Download the export file.
     */
    public function download(Export $export)
    {
        $fileName = "game_{$export->game_id}_v{$export->version}.json";
        $path = storage_path("app/exports/{$fileName}");

        if (!file_exists($path)) {
            return response()->json([
                'message' => 'Export file not found'
            ], 404);
        }

        return response()->download($path, $fileName, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename={$fileName}"
        ]);
    }

    /**
     * Increment version number (e.g., 1.0 -> 1.1, 1.9 -> 2.0)
     */
    private function incrementVersion($version)
    {
        $parts = explode('.', $version);
        $major = (int) $parts[0];
        $minor = (int) $parts[1];

        $minor++;
        if ($minor >= 10) {
            $major++;
            $minor = 0;
        }

        return "{$major}.{$minor}";
    }
}
