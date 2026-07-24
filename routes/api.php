<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\FAQController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ExamAidController;
use App\Http\Controllers\Api\BookmarkController;
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
    Route::get('/topics/{slug}', [TopicController::class, 'show']);
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/materials/{id}/download', [MaterialController::class, 'download'])->whereNumber('id');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/pages/{slug}', [PageController::class, 'show']);
    Route::get('/faqs', [FAQController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);
    Route::get('/exam-aids', [ExamAidController::class, 'index']);
    Route::get('/exam-aids/{id}', [ExamAidController::class, 'show'])->whereNumber('id');
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
        Route::get('/bookmarks', [BookmarkController::class, 'index']);
        Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle']);
        Route::delete('/bookmarks/{id}', [BookmarkController::class, 'destroy',])->whereNumber('id');
    });
});
