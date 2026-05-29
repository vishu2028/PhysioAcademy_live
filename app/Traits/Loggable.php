<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait Loggable
{
    /**
     * Boot the trait and register observers.
     */
    protected static function bootLoggable()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'CREATE');
        });

        static::updated(function (Model $model) {
            static::logActivity($model, 'UPDATE');
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'DELETE');
        });
    }

    /**
     * Perform the logging action.
     */
    protected static function logActivity(Model $model, $action)
    {
        if (!auth()->check()) return;

        $moduleName = class_basename($model);
        $modelName = $model->title ?? $model->name ?? $model->id;
        
        $description = "{$action} action on {$moduleName}: '{$modelName}'";

        ActivityLog::log($action, $moduleName, $description, [
            'attributes' => $model->getAttributes(),
            'original' => $model->getOriginal()
        ]);
    }
}
