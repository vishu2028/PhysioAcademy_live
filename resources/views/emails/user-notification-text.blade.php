{{ brand_text(config('app.name', 'PhysioSource')) }}

{{ $title }}

{{ $notificationMessage }}
@if($actionUrl)
View details: {{ $actionUrl }}
@endif
