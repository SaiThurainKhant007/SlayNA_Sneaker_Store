<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    {{-- Same light / dark choice as the store. No database or login lookups here, so this page works even when MySQL is down. --}}
    <script>
        (() => {
            let saved = null;
            try { saved = localStorage.getItem('sl-theme'); } catch (e) {}
            const dark = saved ? saved === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.bsTheme = dark ? 'dark' : 'light';
        })();
    </script>
</head>
<body>
<main class="sl-error">
    <a href="{{ url('/') }}" class="sl-logo">SLAY<span>NA</span><small>SNEAKER STORE</small></a>
    <p class="sl-error-code" aria-hidden="true">@yield('code')</p>
    <h1 class="sl-error-title">@yield('title')</h1>
    <p class="sl-error-text">@yield('message')</p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
        @yield('actions')
    </div>
</main>
</body>
</html>
