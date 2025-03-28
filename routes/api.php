<?php

use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuestionCategoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    // Questions Categories
    Route::apiResource('categories', QuestionCategoryController::class);
    Route::get('categories/filter/{grade?}/{subject?}', [QuestionCategoryController::class, 'filter']);

    // Questions
    Route::apiResource('questions', QuestionController::class);
    Route::get('questions/filter', [QuestionController::class, 'filter']);
    Route::post('questions/upload-image', [QuestionController::class, 'uploadImage']);

    // Games
    Route::apiResource('games', GameController::class);
    Route::get('games/type/{type}', [GameController::class, 'getByType']);
    Route::post('games/{game}/add-question', [GameController::class, 'addQuestion']);
    Route::delete('games/{game}/remove-question/{question}', [GameController::class, 'removeQuestion']);
    Route::put('games/{game}/update-question/{question}', [GameController::class, 'updateQuestionConfig']);
    Route::get('games/{game}/config', [GameController::class, 'getJsonConfig']);
    Route::get('games/{game}/iframe', [GameController::class, 'getIframeCode']);

    // Exports
    Route::apiResource('exports', ExportController::class)->except(['update', 'destroy']);
    Route::post('exports/{export}/upload-to-fernus', [ExportController::class, 'uploadToFernus']);
    Route::get('exports/{export}/download', [ExportController::class, 'download']);

    // Advertisements
    Route::apiResource('advertisements', AdvertisementController::class);
    Route::post('advertisements/upload-media', [AdvertisementController::class, 'uploadMedia']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
});

// Public Game Access (For iframe embedding)
Route::get('game-access/{game}', [GameController::class, 'publicAccess']);

// Advertisements for public games
Route::get('game-ads/{grade?}/{subject?}/{gameType?}', [AdvertisementController::class, 'getActiveAds']);
