<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\ExamAid;
use App\Models\ExamAidCategory;
use App\Models\ExamAidMaterial;
use App\Models\LearningMaterial;
use App\Models\Page;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\Webinar;
use App\Services\SeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            Topic::class,
            LearningMaterial::class,
            ExamAidMaterial::class,
            Subject::class,
            AcademicYear::class,
            Unit::class,
            ExamAid::class,
            ExamAidCategory::class,
            Page::class,
            Webinar::class,
        ] as $modelClass) {
            $modelClass::saving(function (Model $model): void {
                app(SeoMetadata::class)->synchronize($model);
            });
        }

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Notifications\Events\NotificationSent::class,
            \App\Listeners\DeliverUserNotificationByEmail::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\LogAdminActivity::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            \App\Listeners\LogAdminActivity::class
        );
    }
}
