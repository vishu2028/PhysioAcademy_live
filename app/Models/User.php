<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Loggable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory,HasApiTokens,  Notifiable, HasRoles, Loggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'firebase_uid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function avatarUrl(): ?string
    {
        if (! filled($this->avatar)) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['https://', 'http://'])) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    /**
     * User bookmarks.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * One-on-one doubt session bookings created by this user.
     */
    public function doubtSessionBookings(): HasMany
    {
        return $this->hasMany(DoubtSessionBooking::class);
    }

    /**
     * Doubt session bookings whose schedule was confirmed
     * by this admin user.
     */
    public function confirmedDoubtSessionBookings(): HasMany
    {
        return $this->hasMany(
            DoubtSessionBooking::class,
            'confirmed_by'
        );
    }

    public function topicRequests(): HasMany
    {
        return $this->hasMany(TopicRequest::class);
    }

    public function webinarRegistrations(): HasMany
    {
        return $this->hasMany(WebinarRegistration::class);
    }
}
