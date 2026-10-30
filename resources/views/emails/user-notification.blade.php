<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif;line-height:1.6;">
    <div style="max-width:620px;margin:32px auto;padding:0 16px;">
        <div style="background:#004AAD;padding:24px;border-radius:14px 14px 0 0;color:#fff;">
            <strong style="font-size:20px;">{{ config('app.name', 'Physio Academy') }}</strong>
        </div>
        <div style="background:#fff;padding:32px;border:1px solid #e3eaf3;border-top:0;border-radius:0 0 14px 14px;">
            <h1 style="font-size:22px;margin:0 0 16px;color:#172033;">{{ $title }}</h1>
            <p style="white-space:pre-line;margin:0 0 24px;">{{ $notificationMessage }}</p>
            @if($actionUrl)
                <a href="{{ $actionUrl }}" style="display:inline-block;background:#004AAD;color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:700;">View Details</a>
            @endif
            <p style="font-size:12px;color:#68758a;margin:28px 0 0;">This is a transactional notification from {{ config('app.name', 'Physio Academy') }}.</p>
        </div>
    </div>
</body>
</html>
