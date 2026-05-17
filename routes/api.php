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

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('tours/search', [TourController::class, 'search'])->name('tours.search');
    Route::get('tours', [TourController::class, 'index']);
    Route::post('tours', [TourController::class, 'store']);
    Route::get('tours/{id}', [TourController::class, 'show']);
    Route::put('tours/{id}', [TourController::class, 'update']);
    Route::delete('tours/{id}', [TourController::class, 'destroy']);

    Route::get('tour-dates', [TourDateController::class, 'index']);
    Route::post('tour-dates', [TourDateController::class, 'store']);
    Route::put('tour-dates/{id}', [TourDateController::class, 'update']);
    Route::delete('tour-dates/{id}', [TourDateController::class, 'destroy']);

    Route::post('images/upload', [ImageController::class, 'upload']);
    Route::delete('images/delete', [ImageController::class, 'delete']);

    Route::get('ai/status', [AIGeneratorController::class, 'checkStatus']);
    Route::post('ai/generate-tour', [AIGeneratorController::class, 'generateTour']);
});