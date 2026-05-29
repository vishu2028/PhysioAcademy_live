<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['name', 'location', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function getByLocation(string $location)
    {
        return static::with('items.children')->where('location', $location)->active()->first();
    }
}
