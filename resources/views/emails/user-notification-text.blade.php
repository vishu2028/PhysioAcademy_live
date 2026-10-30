{{ config('app.name', 'Physio Academy') }}

{{ $title }}

{{ $notificationMessage }}
@if($actionUrl)
View details: {{ $actionUrl }}
@endif
