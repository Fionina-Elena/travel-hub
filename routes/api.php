<?php

use App\Http\Controllers\Api\AIGeneratorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\TourDateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::apiResource('categories', CategoryController::class);
    Route::get('tours/search', [TourController::class, 'search'])->name('tours.search');
    Route::apiResource('tours', TourController::class)->except(['index']);
    Route::get('tours', [TourController::class, 'index']);
    Route::apiResource('tour-dates', TourDateController::class);

    Route::post('images/upload', [ImageController::class, 'upload']);
    Route::delete('images/delete', [ImageController::class, 'delete']);

    Route::get('ai/status', [AIGeneratorController::class, 'checkStatus']);
    Route::post('ai/generate-tour', [AIGeneratorController::class, 'generateTour']);
});
