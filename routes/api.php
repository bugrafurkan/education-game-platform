<?php

use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\QuestionGroupController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuestionCategoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('grades', GradeController::class);
Route::apiResource('subjects', SubjectController::class);
Route::apiResource('units', UnitController::class);
Route::apiResource('topics', TopicController::class);

// Exports
Route::post('/exports', [ExportController::class, 'createAndTriggerBuild']);
Route::post('/exports/complete', [ExportController::class, 'markAsCompleted']);
Route::post('/exports/fail', [ExportController::class, 'markAsFailed']);

// Görsel yükleme endpoint'i
Route::post('question-groups/upload-image', [QuestionGroupController::class, 'uploadImage']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [AuthController::class, 'listUsers']);
    Route::get('/user/{id}', [AuthController::class, 'getUser']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
    Route::put('/user-update/{id}', [AuthController::class, 'updateUser']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    // Questions Categories
    Route::apiResource('categories', QuestionCategoryController::class);
    Route::get('categories/filter/{grade?}/{subject?}', [QuestionCategoryController::class, 'filter']);

    // Questions
    Route::apiResource('questions', QuestionController::class);
    Route::get('questions/filter', [QuestionController::class, 'filter']);
    Route::post('questions/upload-image', [QuestionController::class, 'uploadImage']);
    Route::get('/users/{userId}/games/{gameId}/questions', [QuestionController::class, 'getQuestionsByUserAndGame']);
    Route::get('/my-questions', [QuestionController::class, 'myQuestions']);

    // Games
    Route::apiResource('games', GameController::class);
    Route::get('games/type/{type}', [GameController::class, 'getByType']);
    Route::post('games/{game}/add-question', [GameController::class, 'addQuestion']);
    Route::delete('games/{game}/remove-question/{question}', [GameController::class, 'removeQuestion']);
    Route::put('games/{game}/update-question/{question}', [GameController::class, 'updateQuestionConfig']);
    Route::get('games/{game}/config', [GameController::class, 'getJsonConfig']);
    Route::get('games/{game}/iframe', [GameController::class, 'getIframeCode']);

    // Exports
    Route::post('/exports', [ExportController::class, 'createAndTriggerBuild']);
    Route::post('/exports/complete', [ExportController::class, 'markAsCompleted']);
    Route::post('/exports/fail', [ExportController::class, 'markAsFailed']);
    // Advertisements
    Route::apiResource('advertisements', AdvertisementController::class);
    Route::post('advertisements/upload-media', [AdvertisementController::class, 'uploadMedia']);

    // Settings
    //Route::get('/settings', [SettingsController::class, 'index']);
    //Route::put('/settings', [SettingsController::class, 'update']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/upload', [SettingsController::class, 'uploadAd']);

    // Reklam yönetimi için endpoint'ler
    Route::apiResource('advertisements', AdvertisementController::class);

    // Question Groups
    Route::apiResource('question-groups', QuestionGroupController::class);
    Route::get('question-groups/code/{code}', [QuestionGroupController::class, 'getByCode']);
    Route::get('eligible-questions', [QuestionGroupController::class, 'getEligibleQuestions']);
});

// Public Game Access (For iframe embedding)
Route::get('game-access/{game}', [GameController::class, 'publicAccess']);

// Advertisements for public games
Route::get('game-ads/{grade?}/{subject?}/{gameType?}', [AdvertisementController::class, 'getActiveAds']);
