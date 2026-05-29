<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fbff;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Sora', sans-serif;
            background-color: var(--bg);
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 20px 50px rgba(37,99,235,0.08);
            margin: 20px;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(37,99,235,0.1);
            color: var(--primary);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        h1 {
            font-weight: 800;
            margin-bottom: 20px;
            color: #0f172a;
        }
        p {
            line-height: 1.6;
            color: #64748b;
            margin-bottom: 30px;
        }
        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #fff;
            padding: 12px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37,99,235,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-box">
            <i class="bi bi-gear-fill"></i>
        </div>
        <h1>Under Maintenance</h1>
        <p>{{ $message ?? 'We are currently performing scheduled maintenance to improve our services. Please check back with us shortly.' }}</p>
        
        <a href="{{ url('/admin/dashboard') }}" class="btn-admin">
            <i class="bi bi-lock-fill"></i>
            Admin Staff Login
        </a>
    </div>
</body>
</html>
