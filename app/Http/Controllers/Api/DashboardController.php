<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Game;
use App\Models\QuestionCategory;
use App\Models\Export;

class DashboardController extends Controller
{
    public function getStats()
    {
        $stats = [
            'questionCount' => Question::count(),
            'gameCount' => Game::count(),
            'categoryCount' => QuestionCategory::count(),
            'exportCount' => Export::count(),
            'recentQuestions' => Question::latest()->take(5)->get(['id', 'question_text', 'created_at']),
            'recentGames' => Game::latest()->take(5)->get(['id', 'name', 'type', 'created_at']),
        ];

        return response()->json($stats);
    }
}
