<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <title>{{ config('app.name', 'TradeGate') }} — @yield('title', 'Login')</title>

    {{-- Local assets first so login never waits on Google Fonts / slow network --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('CSS/erp-responsive.css') }}" />
    <link rel="stylesheet" href="{{ asset('CSS/style.css') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'" />

    <style>
        :root {
            --tg-navy: #0f2747;
            --tg-navy-deep: #0a1c33;
            --tg-navy-mid: #1a4a8a;
            --tg-gold: #c9a227;
            --tg-gold-soft: #e8d48b;
            --tg-ice: #eef3f8;
            --tg-mist: #f7f9fc;
            --tg-line: #d5dde8;
            --tg-muted: #5a6b7d;
            --tg-font: "Source Sans 3", "Segoe UI", system-ui, sans-serif;
            --tg-display: "Outfit", "Segoe UI", "Source Sans 3", sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
        }

        body.auth-body {
            font-family: var(--tg-font);
            color: var(--tg-navy);
            background: var(--tg-mist);
            min-height: 100vh;
            min-height: 100dvh;
        }

        @yield('auth-styles')
    </style>
</head>
<body class="erp-ui auth-body">
    @yield('content')

    <script src="{{ asset('vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('JS/global.js') }}"></script>
    @yield('auth-scripts')
</body>
</html>
