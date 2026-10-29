<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Webinar extends Model
{
    protected $fillable = ['title','slug','subject','academic_year','semester','description','learning_objectives','speaker','starts_at','ends_at','duration_minutes','timezone','meeting_platform','meeting_link','capacity','price','currency','is_free','status','published_at','registration_deadline'];
    protected function casts(): array { return ['starts_at'=>'datetime','ends_at'=>'datetime','published_at'=>'datetime','registration_deadline'=>'datetime','price'=>'decimal:2','is_free'=>'boolean']; }
    public function registrations(): HasMany { return $this->hasMany(WebinarRegistration::class); }
    public function scopePublished($query) { return $query->where('status','published')->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at','>=',now()->subDay()); })->where(function ($q) { $q->whereNull('registration_deadline')->orWhere('registration_deadline','>',now()); }); }
    public function scopeRegistrationOpen($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('capacity')
                    ->orWhereRaw(
                        '(SELECT COUNT(*) FROM webinar_registrations
                          WHERE webinar_registrations.webinar_id = webinars.id
                          AND webinar_registrations.registration_status IN (?, ?)) < webinars.capacity',
                        ['pending', 'confirmed']
                    );
            });
    }
    public function hasCapacity(): bool { return is_null($this->capacity) || $this->registrations()->whereIn('registration_status',['pending','confirmed'])->count() < $this->capacity; }
    public function isPaid(): bool { return ! $this->is_free && (float)$this->price > 0; }
    public function registrationIsOpen(): bool
    {
        return $this->status === 'published'
            && (!$this->registration_deadline || now()->lt($this->registration_deadline))
            && $this->hasCapacity();
    }
}
