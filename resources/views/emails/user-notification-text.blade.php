{{ brand_text(config('app.name', 'PhysioSource')) }}

{{ $title }}

{{ $notificationMessage }}
@foreach($details as $label => $value)
{{ $label }}: {{ $value }}
@endforeach
@if($actionUrl)
View details: {{ $actionUrl }}
@endif
