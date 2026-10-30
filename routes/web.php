<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoubtController as AdminDoubtController;
use App\Http\Controllers\Admin\DoubtSessionBookingController as AdminDoubtSessionBookingController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\DoubtController;
use App\Http\Controllers\DoubtSessionBookingController;
use App\Http\Controllers\WebinarController;
use App\Http\Controllers\GroupWebinarBookingController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\CommunityActivityController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\TrendingTopicController;
use App\Http\Controllers\Admin\CommunityAndAnnouncementsController;
use App\Http\Controllers\Admin\AboutTimelineController;
use App\Http\Controllers\Admin\AboutContentController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\ExamAidBannerController;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [FrontendController::class, 'home']
)->name('home');

Route::get(
    '/about',
    [FrontendController::class, 'about']
)->name('about');

Route::get(
    '/contact',
    [FrontendController::class, 'contact']
)->name('contact');

Route::post(
    '/contact',
    [FrontendController::class, 'contactSubmit']
)->name('contact.submit');

Route::get(
    '/subjects',
    [FrontendController::class, 'topics']
)->name('topics.index');

Route::get(
    '/topic/{slug}',
    [FrontendController::class, 'topicShow']
)->name('topics.show');

Route::get(
    '/academic-years/{year?}',
    [FrontendController::class, 'topicsByYear']
)->name('topics.year');

Route::get(
    '/exam-aid',
    [FrontendController::class, 'examAid']
)->name('exam-aid');

Route::get(
    '/exam-aid/search',
    [FrontendController::class, 'examAidSearch']
)->name('exam-aid.search');

Route::get(
    '/exam-aid/category/{slug}',
    [FrontendController::class, 'examAidCategory']
)->name('exam-aid.category');

Route::get(
    '/search',
    [FrontendController::class, 'search']
)->name('search');

Route::get(
    '/search/suggestions',
    [FrontendController::class, 'searchSuggestions']
)->name('search.suggestions');

Route::get(
    '/faq',
    [FrontendController::class, 'faq']
)->name('faq');

Route::get('/webinars', [WebinarController::class, 'index'])->name('webinars.index');
Route::get('/webinars/{webinar:slug}', [WebinarController::class, 'show'])->name('webinars.show');
Route::get('/group-webinars/invitations/{token}', [GroupWebinarBookingController::class, 'invitation'])
    ->middleware('throttle:30,1')
    ->name('group-webinars.invitation');
Route::post('/group-webinars/invitations/{token}/payment', [GroupWebinarBookingController::class, 'startPayment'])
    ->middleware('throttle:10,1')
    ->name('group-webinars.payment.start');
Route::post('/group-webinars/invitations/{token}/coupon', [GroupWebinarBookingController::class, 'quoteCoupon'])
    ->middleware('throttle:10,1')
    ->name('group-webinars.coupon.quote');
Route::get('/group-webinars/invitations/{token}/payment/{payment}', [GroupWebinarBookingController::class, 'payment'])
    ->middleware('throttle:30,1')
    ->name('group-webinars.payment');
Route::post('/group-webinars/invitations/{token}/payment/{payment}/verify', [GroupWebinarBookingController::class, 'verifyPayment'])
    ->middleware('throttle:10,1')
    ->name('group-webinars.payment.verify');
Route::post('/group-webinars/invitations/{token}/payment/{payment}/failure', [GroupWebinarBookingController::class, 'reportPaymentFailure'])
    ->middleware('throttle:10,1')
    ->name('group-webinars.payment.failure');
Route::post('/group-webinars/invitations/{token}/payment/{payment}/abandon', [GroupWebinarBookingController::class, 'abandonPayment'])
    ->middleware('throttle:10,1')
    ->name('group-webinars.payment.abandon');
Route::post('/group-webinars/invitations/resend', [GroupWebinarBookingController::class, 'resendInvitation'])
    ->middleware('throttle:5,1')
    ->name('group-webinars.invitation.resend');

/*
|--------------------------------------------------------------------------
| Authenticated Student Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/download/material/{id}',
        [FrontendController::class, 'downloadMaterial']
    )->name('materials.download');

    Route::get(
        '/bookmarks',
        [FrontendController::class, 'bookmarks']
    )->name('bookmarks');

    Route::post(
        '/bookmarks/toggle',
        [\App\Http\Controllers\BookmarkController::class, 'toggle']
    )->name('bookmarks.toggle');

    Route::delete(
        '/bookmarks/{id}',
        [\App\Http\Controllers\BookmarkController::class, 'remove']
    )->name('bookmarks.remove');
    /*
 * Student Notifications
 */
    Route::post(
        '/notifications/{notification}/open',
        [NotificationController::class, 'open']
    )->name('notifications.open');

    Route::get(
        '/notifications/poll',
        [NotificationController::class, 'poll']
    )->name('notifications.poll');

    /*
     * Student Doubts
     */
    Route::post(
        '/doubts',
        [DoubtController::class, 'store']
    )->name('doubts.store');

    Route::post(
        '/topic-requests',
        [\App\Http\Controllers\TopicRequestController::class, 'store']
    )->name('topic-requests.store');

    Route::get(
        '/my-topic-requests',
        [\App\Http\Controllers\TopicRequestController::class, 'mine']
    )->name('topic-requests.mine');

    Route::get(
        '/my-doubts',
        [DoubtController::class, 'myDoubts']
    )->name('doubts.mine');

    /*
     * One-on-One Doubt Session Bookings
     */
    Route::get(
        '/doubt-sessions/history',
        [DoubtSessionBookingController::class, 'history']
    )->name('doubt-sessions.history');

    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon'])
        ->middleware('throttle:20,1')
        ->name('coupons.validate');

    Route::get(
        '/doubt-sessions/book',
        [DoubtSessionBookingController::class, 'create']
    )->name('doubt-sessions.create');

    Route::get('/group-webinars/request', [GroupWebinarBookingController::class, 'create'])
        ->name('group-webinars.create');
    Route::post('/group-webinars/request', [GroupWebinarBookingController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('group-webinars.store');

    Route::post(
        '/doubt-sessions',
        [DoubtSessionBookingController::class, 'store']
    )->name('doubt-sessions.store');

    Route::get(
        '/doubt-sessions/{booking}/payment',
        [DoubtSessionBookingController::class, 'payment']
    )->name('doubt-sessions.payment');

    Route::post(
        '/doubt-sessions/{booking}/payment/verify',
        [DoubtSessionBookingController::class, 'verifyPayment']
    )->name('doubt-sessions.payment.verify');

    Route::get(
        '/doubt-sessions/{booking}/success',
        [DoubtSessionBookingController::class, 'success']
    )->name('doubt-sessions.success');

    Route::get('/my-webinars', [WebinarController::class, 'myWebinars'])->name('webinars.my');
    Route::post('/webinars/{webinar:slug}/register', [WebinarController::class, 'register'])->name('webinars.register');
    Route::post('/webinar-registrations/{registration}/payment/verify', [WebinarController::class, 'verify'])->name('webinars.payment.verify');
    Route::get('/webinar-registrations/{registration}/join', [WebinarController::class, 'join'])->name('webinars.join');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:super_admin|admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        /*
         * Dashboard
         */
        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

        /*
         * CMS Content Resources
         */
        Route::post(
            'pages/upload-image',
            [
                \App\Http\Controllers\Admin\PageController::class,
                'uploadImage',
            ]
        )->name('pages.upload_image');

        Route::resource(
            'pages',
            \App\Http\Controllers\Admin\PageController::class
        );

        Route::resource(
            'page-sections',
            \App\Http\Controllers\Admin\PageSectionController::class
        )->parameters([
            'page-sections' => 'section',
        ]);

        Route::resource(
            'page-section-items',
            \App\Http\Controllers\Admin\PageSectionItemController::class
        )->only([
            'create',
            'store',
            'edit',
            'update',
            'destroy',
        ]);

        Route::resource(
            'hero',
            \App\Http\Controllers\Admin\HeroController::class
        );

        Route::patch(
            'features/section-toggle',
            [FeatureController::class, 'sectionToggle']
        )->name('features.section-toggle');

        Route::resource(
            'features',
            \App\Http\Controllers\Admin\FeatureController::class
        );

        /*
         * Academic Management
         */
        Route::resource(
            'subjects',
            \App\Http\Controllers\Admin\SubjectController::class
        );

        Route::resource(
            'academic-years',
            \App\Http\Controllers\Admin\AcademicYearController::class
        );

        Route::get(
            'units/by-subject/{subject}',
            [
                \App\Http\Controllers\Admin\UnitController::class,
                'bySubject',
            ]
        )->name('units.by-subject');

        Route::get(
            'parent-topics/by-topic/{unitTopic}',
            [
                \App\Http\Controllers\Admin\ParentTopicController::class,
                'byTopic',
            ]
        )->name('parent-topics.by-topic');

        Route::resource(
            'parent-topics',
            \App\Http\Controllers\Admin\ParentTopicController::class
        );

        Route::patch(
            'units/{unit}/toggle-status',
            [
                \App\Http\Controllers\Admin\UnitController::class,
                'toggleStatus',
            ]
        )->name('units.toggle-status');

        Route::resource(
            'units',
            \App\Http\Controllers\Admin\UnitController::class
        );

        Route::get(
            'unit-topics/by-unit/{unit}',
            [
                \App\Http\Controllers\Admin\UnitTopicController::class,
                'byUnit',
            ]
        )->name('unit-topics.by-unit');

        Route::resource(
            'unit-topics',
            \App\Http\Controllers\Admin\UnitTopicController::class
        );

        Route::post(
            'topics/upload-image',
            [
                \App\Http\Controllers\Admin\TopicController::class,
                'uploadImage',
            ]
        )->name('topics.upload_image');

        Route::resource(
            'topics',
            \App\Http\Controllers\Admin\TopicController::class
        );

        Route::resource(
            'exam-aid-categories',
            \App\Http\Controllers\Admin\ExamAidCategoryController::class
        )->except(['show']);

        Route::resource(
            'exam-aids',
            \App\Http\Controllers\Admin\ExamAidController::class
        );

        Route::resource(
            'team-members',
            \App\Http\Controllers\Admin\TeamMemberController::class
        )->parameters(['team-members' => 'teamMember'])->except(['show']);

        Route::get(
            'exam-prep',
            [\App\Http\Controllers\Admin\ExamPrepController::class, 'index']
        )->name('exam-prep.index');

        Route::put(
            'exam-prep/settings',
            [\App\Http\Controllers\Admin\ExamPrepController::class, 'updateSettings']
        )->name('exam-prep.settings');

        Route::resource(
            'exam-prep-cards',
            \App\Http\Controllers\Admin\ExamPrepController::class
        )->parameters(['exam-prep-cards' => 'examPrepCard'])->except(['index', 'show']);

        Route::get(
            'resources-section',
            [\App\Http\Controllers\Admin\ResourcesSectionController::class, 'index']
        )->name('resources-section.index');

        Route::patch(
            'resources-section/section-toggle',
            [\App\Http\Controllers\Admin\ResourcesSectionController::class, 'sectionToggle']
        )->name('resources-section.section-toggle');

        Route::patch(
            'testimonials/section-toggle',
            [
                \App\Http\Controllers\Admin\TestimonialController::class,
                'sectionToggle',
            ]
        )->name('testimonials.section-toggle');

        Route::resource(
            'testimonials',
            \App\Http\Controllers\Admin\TestimonialController::class
        );

        Route::resource(
            'faqs',
            \App\Http\Controllers\Admin\FAQController::class
        );

        /*
         * Messages
         */
        Route::resource(
            'messages',
            \App\Http\Controllers\Admin\MessageController::class
        )->only([
            'index',
            'show',
            'destroy',
        ]);

        /*
         * Student Doubts
         */
        Route::resource(
            'doubts',
            AdminDoubtController::class
        )->only([
            'index',
            'update',
            'destroy',
        ]);

        Route::get(
            'topic-requests',
            [\App\Http\Controllers\Admin\TopicRequestController::class, 'index']
        )->name('topic-requests.index');

        Route::patch(
            'topic-requests/{topicRequest}',
            [\App\Http\Controllers\Admin\TopicRequestController::class, 'update']
        )->name('topic-requests.update');

        /*
         * One-on-One Doubt Session Bookings
         */
        Route::resource(
            'doubt-session-bookings',
            AdminDoubtSessionBookingController::class
        )->only([
            'index',
            'show',
            'update',
        ]);

        Route::resource('webinars', \App\Http\Controllers\Admin\WebinarController::class)->except(['destroy']);
        Route::patch('webinar-registrations/{registration}', [\App\Http\Controllers\Admin\WebinarController::class, 'updateRegistration'])->name('webinar-registrations.update');
        Route::get('group-webinars', [\App\Http\Controllers\Admin\GroupWebinarRequestController::class, 'index'])
            ->name('group-webinars.index');
        Route::get('group-webinars/{groupWebinar}', [\App\Http\Controllers\Admin\GroupWebinarRequestController::class, 'show'])
            ->name('group-webinars.show');
        Route::patch('group-webinars/{groupWebinar}', [\App\Http\Controllers\Admin\GroupWebinarRequestController::class, 'update'])
            ->name('group-webinars.update');
        Route::post('group-webinar-participants/{participant}/resend', [\App\Http\Controllers\Admin\GroupWebinarRequestController::class, 'resendInvitation'])
            ->name('group-webinars.participants.resend');

        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)
            ->except(['show']);
        Route::patch('coupons/{coupon}/toggle', [\App\Http\Controllers\Admin\CouponController::class, 'toggle'])
            ->name('coupons.toggle');

        /*
         * Media and Layout
         */
        Route::resource(
            'media',
            \App\Http\Controllers\Admin\MediaController::class
        )->only([
            'index',
            'store',
            'destroy',
        ]);

        Route::resource(
            'sliders',
            \App\Http\Controllers\Admin\SliderController::class
        );

        Route::resource(
            'banners',
            \App\Http\Controllers\Admin\BannerController::class
        );

        /*
         * Navigation Menus
         */
        Route::resource(
            'menus',
            \App\Http\Controllers\Admin\MenuController::class
        );

        /*
         * Settings
         */
        Route::get(
            '/settings',
            [
                \App\Http\Controllers\Admin\SettingController::class,
                'index',
            ]
        )->name('settings.index');

        Route::post(
            '/settings',
            [
                \App\Http\Controllers\Admin\SettingController::class,
                'update',
            ]
        )->name('settings.update');

        /*
         * User Management
         */
        Route::resource(
            'users',
            \App\Http\Controllers\Admin\UserController::class
        );

        /*
         * Activity Logs
         */
        Route::get(
            '/activity-logs',
            [
                \App\Http\Controllers\Admin\ActivityLogController::class,
                'index',
            ]
        )->name('activity-logs.index');

         /*
         * Community & Announcements
         */
        Route::get(
            'community-and-announcements',
            [CommunityAndAnnouncementsController::class, 'index']
        )->name('community_and_announcements.index');
        Route::resource(
            'community-activities',
            CommunityActivityController::class
        )->names('community');


        Route::resource(
            'announcements',
            AnnouncementController::class
        )->names('announcements');


        Route::resource(
            'trending-topics',
            TrendingTopicController::class
        )->names('trending');
         /*
         * About Timelines
         */
        Route::resource(
            'about',
            AboutController::class
        )->only(['index'])->names('about');

        Route::resource(
            'about-content',
            AboutContentController::class
        )->except(['show'])->names('about_content');


        Route::resource(
            'about-timelines',
            AboutTimelineController::class
        )->except(['show'])->names('about_timelines');
        // Exam aid main banner section
        Route::resource(
            'exam-aid-banner',
            ExamAidBannerController::class
        )->names('exam-aid-banner');
    });

/*
|--------------------------------------------------------------------------
| User Dashboard Redirect
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->get('/dashboard', function () {
        if (
            auth()->user()->hasRole('admin')
            || auth()->user()->hasRole('super_admin')
        ) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    });

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Dynamic CMS Page
|--------------------------------------------------------------------------
|
| This catch-all route must remain at the bottom.
|
*/

Route::get(
    '/{slug}',
    [FrontendController::class, 'dynamicPage']
)->name('page.show');
