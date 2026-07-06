<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\DoubtController;
use App\Http\Controllers\Admin\DoubtController as AdminDoubtController;
// use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;


// ─── Frontend Routes (CMS-driven) ───
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontendController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/subjects', [FrontendController::class, 'topics'])->name('topics.index');
Route::get('/topic/{slug}', [FrontendController::class, 'topicShow'])->name('topics.show');
Route::get('/academic-years/{year?}', [FrontendController::class, 'topicsByYear'])->name('topics.year');
Route::get('/exam-aid', [FrontendController::class, 'examAid'])->name('exam-aid');
Route::get('/search', [FrontendController::class, 'search'])->name('search');
Route::get('/search/suggestions', [FrontendController::class, 'searchSuggestions'])->name('search.suggestions');
Route::get('/faq', [FrontendController::class, 'faq'])->name('faq');

Route::middleware('auth')->group(function() {
    Route::get('/download/material/{id}', [FrontendController::class, 'downloadMaterial'])->name('materials.download');
    Route::get('/bookmarks', [FrontendController::class, 'bookmarks'])->name('bookmarks');
    Route::post('/bookmarks/toggle', [\App\Http\Controllers\BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::delete('/bookmarks/{id}', [\App\Http\Controllers\BookmarkController::class, 'remove'])->name('bookmarks.remove');
    // Student Doubts
    Route::post('/doubts', [DoubtController::class, 'store'])->name('doubts.store');
    Route::get('/my-doubts', [DoubtController::class, 'myDoubts'])->name('doubts.mine');
});

// ─── Profile Routes ───
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Admin Routes (Super Admin + Admin) ───
Route::middleware(['auth', 'role:super_admin|admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CMS Content Resources
    Route::post('pages/upload-image', [\App\Http\Controllers\Admin\PageController::class, 'uploadImage'])->name('pages.upload_image');
    Route::resource('pages', \App\Http\Controllers\Admin\PageController::class);
    Route::resource('page-sections', \App\Http\Controllers\Admin\PageSectionController::class)->parameters(['page-sections' => 'section']);
    Route::resource('page-section-items', \App\Http\Controllers\Admin\PageSectionItemController::class)->only(['create','store','edit','update','destroy']);
    Route::resource('hero', \App\Http\Controllers\Admin\HeroController::class);
    Route::patch('features/section-toggle', [FeatureController::class, 'sectionToggle'])
        ->name('features.section-toggle');
    Route::resource('features', \App\Http\Controllers\Admin\FeatureController::class);

    Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);
    Route::resource('academic-years', \App\Http\Controllers\Admin\AcademicYearController::class);
    Route::get('units/by-subject/{subject}', [\App\Http\Controllers\Admin\UnitController::class, 'bySubject'])
        ->name('units.by-subject');
    Route::patch('units/{unit}/toggle-status', [\App\Http\Controllers\Admin\UnitController::class, 'toggleStatus'])
        ->name('units.toggle-status');

    Route::resource('units', \App\Http\Controllers\Admin\UnitController::class);
    Route::get('unit-topics/by-unit/{unit}', [\App\Http\Controllers\Admin\UnitTopicController::class, 'byUnit'])
        ->name('unit-topics.by-unit');

    Route::resource('unit-topics', \App\Http\Controllers\Admin\UnitTopicController::class);
    Route::post('topics/upload-image', [\App\Http\Controllers\Admin\TopicController::class, 'uploadImage'])->name('topics.upload_image');
    Route::resource('topics', \App\Http\Controllers\Admin\TopicController::class);
    Route::patch('testimonials/section-toggle', [\App\Http\Controllers\Admin\TestimonialController::class, 'sectionToggle'])
        ->name('testimonials.section-toggle');
    Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);
    Route::resource('faqs', \App\Http\Controllers\Admin\FAQController::class);

    // Messages (index, show, delete only)
    Route::resource('messages', \App\Http\Controllers\Admin\MessageController::class)->only(['index', 'show', 'destroy']);
    // Student Doubts
    Route::resource('doubts', AdminDoubtController::class)->only(['index', 'update', 'destroy']);

    // Media & Layout
    Route::resource('media', \App\Http\Controllers\Admin\MediaController::class)->only(['index', 'store', 'destroy']);
    Route::resource('sliders', \App\Http\Controllers\Admin\SliderController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);

    // Navigation Menus
    Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class);

    // Settings (Super Admin only for critical settings)
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// ─── User Dashboard Redirect ───
Route::middleware(['auth'])->get('/dashboard', function () {
    if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
});

require __DIR__.'/auth.php';

Route::get('/{slug}', [FrontendController::class, 'dynamicPage'])->name('page.show');
