<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 
        'action', 
        'module', 
        'description', 
        'ip_address', 
        'user_agent', 
        'properties'
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Static helper for quick logging.
     */
    public static function log($action, $module, $description, $properties = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => strtoupper($action),
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'properties' => $properties,
        ]);
    }
}
