<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameController extends Controller
{
    /**
     * Display a listing of games.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $games = Game::with('creator')->latest()->paginate($perPage);

        return response()->json($games);
    }

    /**
     * Store a newly created game.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:jeopardy,wheel',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $game = Game::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'config' => $validated['config'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($game, 201);
    }

    /**
     * Display the specified game.
     */
    public function show(Game $game)
    {
        $game->load(['creator', 'questions.answers']);
        return response()->json($game);
    }

    /**
     * Update the specified game.
     */
    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:jeopardy,wheel',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $game->update($validated);

        return response()->json($game);
    }

    /**
     * Remove the specified game.
     */
    public function destroy(Game $game)
    {
        // Check if the game has exports
        if ($game->exports()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete game with exports'
            ], 400);
        }

        $game->delete();

        return response()->json(null, 204);
    }

    /**
     * Get games by type.
     */
    public function getByType($type)
    {
        $validTypes = ['jeopardy', 'wheel'];

        if (!in_array($type, $validTypes)) {
            return response()->json([
                'message' => 'Invalid game type'
            ], 400);
        }

        $games = Game::ofType($type)->with('creator')->latest()->paginate(20);

        return response()->json($games);
    }

    /**
     * Add a question to a game.
     */
    public function addQuestion(Request $request, Game $game)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'points' => 'required|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'category_label' => 'nullable|string|max:100',
            'special_effects' => 'nullable|json',
        ]);

        // Check if question already exists
        if ($game->questions()->where('question_id', $validated['question_id'])->exists()) {
            return response()->json([
                'message' => 'Question already added to this game'
            ], 400);
        }

        // Add the question
        $game->questions()->attach($validated['question_id'], [
            'points' => $validated['points'],
            'order' => $validated['order'] ?? null,
            'category_label' => $validated['category_label'] ?? null,
            'special_effects' => $validated['special_effects'] ?? null,
        ]);

        return response()->json([
            'message' => 'Question added successfully'
        ]);
    }

    /**
     * Remove a question from a game.
     */
    public function removeQuestion(Game $game, Question $question)
    {
        // Check if question exists in the game
        if (!$game->questions()->where('question_id', $question->id)->exists()) {
            return response()->json([
                'message' => 'Question not found in this game'
            ], 404);
        }

        // Remove the question
        $game->questions()->detach($question->id);

        return response()->json([
            'message' => 'Question removed successfully'
        ]);
    }

    /**
     * Update a question's configuration in a game.
     */
    public function updateQuestionConfig(Request $request, Game $game, Question $question)
    {
        // Check if question exists in the game
        if (!$game->questions()->where('question_id', $question->id)->exists()) {
            return response()->json([
                'message' => 'Question not found in this game'
            ], 404);
        }

        $validated = $request->validate([
            'points' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'category_label' => 'nullable|string|max:100',
            'special_effects' => 'nullable|json',
        ]);

        // Update the pivot
        $game->questions()->updateExistingPivot($question->id, $validated);

        return response()->json([
            'message' => 'Question configuration updated successfully'
        ]);
    }

    /**
     * Get the JSON configuration for a game.
     */
    public function getJsonConfig(Game $game)
    {
        $jsonConfig = $game->generateJsonConfig();

        return response()->json(json_decode($jsonConfig));
    }

    /**
     * Get the iframe code for a game.
     */
    public function getIframeCode(Game $game)
    {
        $gameUrl = url("/game-access/{$game->id}");
        $iframeCode = "<iframe src=\"{$gameUrl}\" width=\"100%\" height=\"600\" frameborder=\"0\" allowfullscreen></iframe>";

        return response()->json([
            'iframe_code' => $iframeCode,
            'game_url' => $gameUrl
        ]);
    }

    /**
     * Public access to a game (for iframe embedding).
     */
    public function publicAccess(Game $game)
    {
        // Check if game is active
        if (!$game->is_active) {
            return response()->json([
                'message' => 'Game is not active'
            ], 404);
        }

        // Return the game configuration
        $config = json_decode($game->generateJsonConfig());

        return response()->json($config);
    }
}
