<section class="coupon-entry-card preferred-slots-card booking-field-full" aria-labelledby="preferredSlotsTitle">
    @php($slotsRequired = true)
    <div class="mb-3">
        <h3 id="preferredSlotsTitle" class="mb-1">Select 3 Preferred Time Slots</h3>
        <small class="text-secondary">
            Providing 3 options helps us organize your session faster.
        </small>
    </div>
    <div class="preferred-slots-grid">
        @for($slotIndex = 0; $slotIndex < 3; $slotIndex++)
            @php($oldSlot = old("preferred_slots.{$slotIndex}", []))
            <div class="preferred-slot-row">
                <strong>Preference {{ $slotIndex + 1 }}</strong>
                <div class="preferred-slot-inputs">
                    <label>
                        <span>Date</span>
                        <input type="date" name="preferred_slots[{{ $slotIndex }}][date]" value="{{ $oldSlot['date'] ?? '' }}" min="{{ now(config('app.timezone'))->format('Y-m-d') }}">
                    </label>
                    <label>
                        <span>Time</span>
                        <input type="time" name="preferred_slots[{{ $slotIndex }}][time]" value="{{ $oldSlot['time'] ?? '' }}">
                    </label>
                </div>
                @error("preferred_slots.{$slotIndex}")<div class="booking-error">{{ $message }}</div>@enderror
                @error("preferred_slots.{$slotIndex}.date")<div class="booking-error">{{ $message }}</div>@enderror
                @error("preferred_slots.{$slotIndex}.time")<div class="booking-error">{{ $message }}</div>@enderror
            </div>
        @endfor
    </div>
    @error('preferred_slots')<div class="booking-error mt-2">{{ $message }}</div>@enderror
</section>
