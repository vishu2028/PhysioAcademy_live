<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoubtMessage extends Model
{
    public const ROLE_STUDENT = 'student';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'doubt_id',
        'sender_id',
        'sender_role',
        'message',
        'created_at',
        'updated_at',
    ];

    public function doubt(): BelongsTo
    {
        return $this->belongsTo(Doubt::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
