<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\SubjectController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Authentication APIs
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
    // Public Academic Years API
    Route::get('/academic-years', [AcademicYearController::class, 'index']);
    Route::get('/academic-years/{slug}/curriculum', [AcademicYearController::class, 'curriculum']);
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{slug}/curriculum', [SubjectController::class, 'curriculum']);
    /*
    |--------------------------------------------------------------------------
    | Protected APIs
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Authentication
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::delete('/profile', [ProfileController::class, 'destroy']);
    });
});
