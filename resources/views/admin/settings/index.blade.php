@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold">System Settings</h2>
    <p class="text-secondary">Manage your website configuration and global preferences.</p>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="list-group list-group-flush" id="settings-tabs" role="tablist">
                <a class="list-group-item list-group-item-action active py-3 border-0" id="general-tab" data-bs-toggle="list" href="#general" role="tab">
                    <i class="bi bi-gear me-2"></i> General Settings
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0" id="seo-tab" data-bs-toggle="list" href="#seo" role="tab">
                    <i class="bi bi-search me-2"></i> SEO & Meta
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0" id="theme-tab" data-bs-toggle="list" href="#theme" role="tab">
                    <i class="bi bi-palette me-2"></i> Theme & Brand
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0" id="landing-tab" data-bs-toggle="list" href="#landing" role="tab">
                    <i class="bi bi-window-sidebar me-2"></i> Landing Page
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0"
                   id="doubt-session-tab"
                   data-bs-toggle="list"
                   href="#doubt-session"
                   role="tab">
                    <i class="bi bi-camera-video me-2"></i>
                    Doubt Sessions
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0" id="social-tab" data-bs-toggle="list" href="#social" role="tab">
                    <i class="bi bi-share me-2"></i> Social Links
                </a>
                <a class="list-group-item list-group-item-action py-3 border-0 text-danger" id="advanced-tab" data-bs-toggle="list" href="#advanced" role="tab">
                    <i class="bi bi-shield-lock me-2"></i> Security
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="tab-content">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <h5 class="fw-bold mb-4">General Configuration</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Site Name</label>
                                <input type="text" name="settings[site_name]" class="form-control rounded-3" value="{{ get_setting('site_name') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Contact Email</label>
                                <input type="email" name="settings[contact_email]" class="form-control rounded-3" value="{{ get_setting('contact_email') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Contact Phone</label>
                                <input
                                    type="text"
                                    name="settings[site_phone]"
                                    class="form-control rounded-3"
                                    value="{{ old('settings.site_phone', get_setting('site_phone')) }}"
                                    placeholder="+91 98765 43210"
                                >
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Contact URL</label>
                                <input
                                    type="url"
                                    name="settings[contact_url]"
                                    class="form-control rounded-3"
                                    value="{{ old('settings.contact_url', get_setting('contact_url', 'https://www.physioacademy.com')) }}"
                                    placeholder="https://www.physioacademy.com"
                                >
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Footer Description</label>
                                <textarea name="settings[footer_text]" class="form-control rounded-3" rows="3">{{ get_setting('footer_text') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Site Logo</label>
                                @if(get_setting('site_logo'))
                                    <div class="mb-3 p-2 border rounded-3 bg-light d-inline-block">
                                        <img src="{{ asset('storage/' . get_setting('site_logo')) }}" alt="Logo" style="max-height: 50px;">
                                    </div>
                                @endif
                                <input type="file" name="site_logo" class="form-control rounded-3">
                                <p class="small text-muted mt-2">Recommended size: 200x50px (.png, .svg, .jpg)</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Site Favicon</label>
                                @if(get_setting('site_favicon'))
                                    <div class="mb-3 p-2 border rounded-3 bg-light d-inline-block">
                                        <img src="{{ asset('storage/' . get_setting('site_favicon')) }}" alt="Favicon" style="max-height: 32px;">
                                    </div>
                                @endif
                                <input type="file" name="site_favicon" class="form-control rounded-3">
                                <p class="small text-muted mt-2">Recommended size: 32x32px (.ico, .png, .svg)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Color & Brand -->
                    <div class="tab-pane fade" id="theme" role="tabpanel">
                        <h5 class="fw-bold mb-4">Theme & Branding</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Primary Color</label>
                                <input type="color" name="settings[primary_color]" class="form-control form-control-color w-100 rounded-3 p-1" value="{{ get_setting('primary_color', '#4f46e5') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Secondary Color</label>
                                <input type="color" name="settings[secondary_color]" class="form-control form-control-color w-100 rounded-3 p-1" value="{{ get_setting('secondary_color', '#10b981') }}">
                            </div>
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <h5 class="fw-bold mb-4">SEO Settings</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Global Meta Keywords</label>
                                <input type="text" name="settings[meta_keywords]" class="form-control rounded-3" placeholder="keyword1, keyword2" value="{{ get_setting('meta_keywords') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Global Meta Description</label>
                                <textarea name="settings[site_description]" class="form-control rounded-3" rows="4">{{ get_setting('site_description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Landing Page Content -->
                    <div class="tab-pane fade" id="landing" role="tabpanel">
                        <h5 class="fw-bold mb-4">Homepage Specific Content</h5>
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted d-block">About Section (Homepage)</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small font-weight-normal">About Title</label>
                                        <input type="text" name="settings[about_title]" class="form-control rounded-3" value="{{ get_setting('about_title', 'The Study Companion You Need') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small font-weight-normal">About Badge</label>
                                        <input type="text" name="settings[about_badge]" class="form-control rounded-3" value="{{ get_setting('about_badge', 'Why Choose Us') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small font-weight-normal">About Description (HTML allowed)</label>
                                        <textarea name="settings[about_content]" class="form-control rounded-3" rows="5">{{ get_setting('about_content') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr>
                                <label class="form-label small fw-bold text-muted d-block">Footer Information</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small font-weight-normal">Copyright Text</label>
                                        <input type="text" name="settings[copyright_text]" class="form-control rounded-3" value="{{ get_setting('copyright_text', '© 2024 Physio Academy. All rights reserved.') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small font-weight-normal">Contact Address</label>
                                        <input type="text" name="settings[contact_address]" class="form-control rounded-3" value="{{ get_setting('contact_address') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- One-on-One Doubt Session -->
                    <div class="tab-pane fade"
                         id="doubt-session"
                         role="tabpanel"
                         aria-labelledby="doubt-session-tab">

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    One-on-One Doubt Session
                                </h5>

                                <p class="text-secondary small mb-0">
                                    Manage session availability, pricing and duration.
                                </p>
                            </div>

                            <span class="badge rounded-pill px-3 py-2
            {{ get_setting('doubt_session_enabled', '0') == '1'
                ? 'bg-success-subtle text-success'
                : 'bg-secondary-subtle text-secondary' }}">

            {{ get_setting('doubt_session_enabled', '0') == '1'
                ? 'Sessions Enabled'
                : 'Sessions Disabled' }}
        </span>
                        </div>

                        <div class="row g-4">
                            <!-- Enable / Disable -->
                            <div class="col-12">
                                <div class="p-4 rounded-4 border bg-light">
                                    <div class="form-check form-switch mb-2">
                                        <input
                                            type="hidden"
                                            name="settings[doubt_session_enabled]"
                                            value="0"
                                        >

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="settings[doubt_session_enabled]"
                                            id="doubtSessionEnabled"
                                            value="1"
                                            {{ old(
                                                'settings.doubt_session_enabled',
                                                get_setting('doubt_session_enabled', '0')
                                            ) == '1' ? 'checked' : '' }}
                                        >

                                        <label
                                            class="form-check-label fw-bold"
                                            for="doubtSessionEnabled"
                                        >
                                            Enable One-on-One Doubt Sessions
                                        </label>
                                    </div>

                                    <div class="small text-muted">
                                        When disabled, students will not be able to create
                                        new one-on-one session bookings.
                                    </div>

                                    @error('settings.doubt_session_enabled')
                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Free Session -->
                            <div class="col-12">
                                <div class="p-4 rounded-4 border">
                                    <div class="form-check form-switch mb-2">
                                        <input
                                            type="hidden"
                                            name="settings[doubt_session_is_free]"
                                            value="0"
                                        >

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="settings[doubt_session_is_free]"
                                            id="doubtSessionIsFree"
                                            value="1"
                                            {{ old(
                                                'settings.doubt_session_is_free',
                                                get_setting('doubt_session_is_free', '0')
                                            ) == '1' ? 'checked' : '' }}
                                        >

                                        <label
                                            class="form-check-label fw-bold"
                                            for="doubtSessionIsFree"
                                        >
                                            Offer This Session for Free
                                        </label>
                                    </div>

                                    <div class="small text-muted">
                                        Free sessions will be booked without opening the
                                        Razorpay payment gateway.
                                    </div>

                                    @error('settings.doubt_session_is_free')
                                    <div class="text-danger small mt-2">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="col-md-6">
                                <div id="doubtSessionPriceWrapper">
                                    <label
                                        for="doubtSessionPrice"
                                        class="form-label small fw-bold text-muted"
                                    >
                                        Session Price (₹)
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>

                                        <input
                                            type="number"
                                            name="settings[doubt_session_price]"
                                            id="doubtSessionPrice"
                                            class="form-control rounded-end-3
                            @error('settings.doubt_session_price')
                                is-invalid
                            @enderror"
                                            value="{{ old(
                            'settings.doubt_session_price',
                            get_setting('doubt_session_price')
                        ) }}"
                                            min="1"
                                            max="100000"
                                            step="0.01"
                                            placeholder="For example: 100"
                                        >

                                        @error('settings.doubt_session_price')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="form-text">
                                        This amount will later be charged through Razorpay
                                        for paid sessions.
                                    </div>

                                    <div
                                        id="freeSessionPriceMessage"
                                        class="alert alert-info border-0 rounded-3 small mt-3 mb-0 d-none"
                                    >
                                        <i class="bi bi-info-circle me-1"></i>
                                        Price is not required because this session is free.
                                    </div>
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-6">
                                <label
                                    for="doubtSessionDuration"
                                    class="form-label small fw-bold text-muted"
                                >
                                    Session Duration
                                </label>

                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="settings[doubt_session_duration_minutes]"
                                        id="doubtSessionDuration"
                                        class="form-control
                        @error('settings.doubt_session_duration_minutes')
                            is-invalid
                        @enderror"
                                        value="{{ old(
                        'settings.doubt_session_duration_minutes',
                        get_setting(
                            'doubt_session_duration_minutes',
                            '60'
                        )
                    ) }}"
                                        min="15"
                                        max="480"
                                        step="15"
                                        placeholder="60"
                                    >

                                    <span class="input-group-text">
                    Minutes
                </span>

                                    @error('settings.doubt_session_duration_minutes')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-text">
                                    The admin will confirm the actual session date and time
                                    after the booking is submitted.
                                </div>
                            </div>

                            <!-- Flow Information -->
                            <div class="col-12">
                                <div class="alert alert-primary border-0 rounded-4 mb-0">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bi bi-diagram-3-fill fs-4"></i>

                                        <div>
                                            <div class="fw-bold mb-1">
                                                Booking Flow
                                            </div>

                                            <div class="small">
                                                Free sessions will be submitted directly.
                                                Paid sessions will require Razorpay payment.
                                                The admin will confirm the session date,
                                                time and meeting details afterward.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Social Links -->
                    <div class="tab-pane fade" id="social" role="tabpanel">
                        <h5 class="fw-bold mb-4">Social Links</h5>

                        <div class="row g-3">
                            @forelse($socialSettings as $setting)
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">
                                        {{ $setting->label }}
                                    </label>

                                    <input
                                        type="text"
                                        name="settings[{{ $setting->key }}]"
                                        class="form-control rounded-3"
                                        value="{{ old('settings.' . $setting->key, get_setting($setting->key)) }}"
                                        placeholder="https://example.com"
                                    >
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning rounded-4 mb-0">
                                        No social settings found. Please add social settings in database.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Security & Advanced -->
                    <div class="tab-pane fade" id="advanced" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="fw-bold mb-0 text-danger">Security & Maintenance Mode</h5>
                            <span class="badge {{ get_setting('maintenance_mode') == '1' ? 'bg-danger' : 'bg-success' }} px-3 py-2 rounded-pill shadow-sm">
                                System Status: {{ get_setting('maintenance_mode') == '1' ? 'MAINTENANCE ACTIVE' : 'SYSTEM LIVE' }}
                            </span>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="p-4 rounded-4 border {{ get_setting('maintenance_mode') == '1' ? 'border-danger bg-danger-subtle bg-opacity-10' : 'border-light-subtle bg-light' }} mb-4 text-center">
                                    <div class="py-3">
                                        <i class="bi {{ get_setting('maintenance_mode') == '1' ? 'bi-shield-lock-fill text-danger' : 'bi-shield-check text-success' }} display-4 mb-3 d-block"></i>
                                        <h6 class="fw-bold mb-3">Switch System Status</h6>

                                        <div class="btn-group rounded-pill overflow-hidden p-1 bg-white shadow-sm" role="group" style="border: 1px solid #e2e8f0;">
                                            <input type="radio" class="btn-check" name="settings[maintenance_mode]" id="mModeOff" value="0" {{ get_setting('maintenance_mode') != '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success border-0 rounded-pill px-4 py-2 fw-bold" for="mModeOff">
                                                <i class="bi bi-play-fill me-1"></i> GO LIVE
                                            </label>

                                            <input type="radio" class="btn-check" name="settings[maintenance_mode]" id="mModeOn" value="1" {{ get_setting('maintenance_mode') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-danger border-0 rounded-pill px-4 py-2 fw-bold" for="mModeOn">
                                                <i class="bi bi-pause-fill me-1"></i> ACTIVATE MAINTENANCE
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-start bg-white p-4 rounded-4 shadow-sm border">
                                        <label class="form-label small fw-bold text-muted">Custom Maintenance Message</label>
                                        <textarea name="settings[maintenance_message]" class="form-control rounded-3 border-light" rows="3" placeholder="We are currently under maintenance. Please check back later.">{{ get_setting('maintenance_message') }}</textarea>
                                        <div class="form-text x-small mt-2">
                                            <i class="bi bi-info-circle me-1"></i> This message will be displayed prominently on the maintenance landing page.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-shield-shaded me-2"></i> Content Protection System</h5>
                                    <div class="bg-light p-4 rounded-4 border border-light-subtle">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="settings[enable_content_protection]" id="enableProtection" value="1" {{ get_setting('enable_content_protection') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold" for="enableProtection">Enable Global Protection</label>
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="settings[protection_disable_right_click]" id="disableRightClick" value="1" {{ get_setting('protection_disable_right_click') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="disableRightClick">Disable Right Click</label>
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="settings[protection_disable_devtools]" id="disableDevTools" value="1" {{ get_setting('protection_disable_devtools') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="disableDevTools">Disable DevTools Shortcuts</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="settings[protection_disable_copy]" id="disableCopy" value="1" {{ get_setting('protection_disable_copy') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="disableCopy">Disable Copy & Text Selection</label>
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="settings[protection_disable_drag]" id="disableDrag" value="1" {{ get_setting('protection_disable_drag') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="disableDrag">Disable Image Dragging</label>
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="settings[protection_enable_watermark]" id="enableWatermark" value="1" {{ get_setting('protection_enable_watermark') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="enableWatermark">Enable Dynamic Watermark</label>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="alert alert-info border-0 rounded-4 x-small mb-0">
                                                    <i class="bi bi-info-circle-fill me-2"></i> Protection settings apply to study materials, dynamic pages, and premium content when enabled.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-top pt-4">
                    <button type="submit" class="btn btn-primary rounded-3 px-5 py-2">
                        Save Configuration <i class="bi bi-save ms-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const freeSessionCheckbox = document.getElementById(
            'doubtSessionIsFree'
        );

        const priceInput = document.getElementById(
            'doubtSessionPrice'
        );

        const priceWrapper = document.getElementById(
            'doubtSessionPriceWrapper'
        );

        const freePriceMessage = document.getElementById(
            'freeSessionPriceMessage'
        );

        if (!freeSessionCheckbox || !priceInput) {
            return;
        }

        function updateDoubtSessionPriceField() {
            const isFree = freeSessionCheckbox.checked;

            priceInput.disabled = isFree;

            if (priceWrapper) {
                priceWrapper.classList.toggle(
                    'opacity-50',
                    isFree
                );
            }

            if (freePriceMessage) {
                freePriceMessage.classList.toggle(
                    'd-none',
                    !isFree
                );
            }
        }

        freeSessionCheckbox.addEventListener(
            'change',
            updateDoubtSessionPriceField
        );

        updateDoubtSessionPriceField();
    });
</script>
