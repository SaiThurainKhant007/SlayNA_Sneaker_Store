<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') - @endif{{ config('app.name') }}</title>
    {{-- Search results and link previews (Facebook, Viber, Messenger); product pages set their own description and photo --}}
    <meta name="description" content="@yield('description', 'Authentic sneakers from Nike, Adidas, Jordan and more, delivered across Yangon and Myanmar. Pay by KPay, AYA Pay or cash on delivery.')">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@hasSection('title')@yield('title') - @endif{{ config('app.name') }}">
    <meta property="og:description" content="@yield('description', 'Authentic sneakers from Nike, Adidas, Jordan and more, delivered across Yangon and Myanmar.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('img/hero-shoe-1.png'))">
    <meta name="twitter:card" content="summary_large_image">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    {{-- Light / dark mode: the saved choice, otherwise the device setting; applied before the page paints --}}
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
<nav class="navbar navbar-expand-xl sl-nav sticky-top">
    <div class="container">
        <a class="navbar-brand sl-logo" href="{{ route('home') }}">
            SLAY<span>NA</span><small>SNEAKER STORE</small>
        </a>
        <div class="d-flex align-items-center ms-auto d-xl-none">
            <button type="button" class="sl-theme-btn" data-theme-toggle aria-label="Switch to dark mode" title="Light / dark mode"><i class="bi bi-moon-stars-fill" aria-hidden="true"></i><i class="bi bi-sun-fill" aria-hidden="true"></i></button>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle menu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-xl-4 me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.index', 'products.show') ? 'active' : '' }}" href="{{ route('products.index') }}">Shop</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.new') ? 'active' : '' }}" href="{{ route('products.new') }}">New Arrivals</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('brands') ? 'active' : '' }}" href="{{ route('brands') }}">Brands</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a></li>
                @auth
                    @if (auth()->user()->isStaff())
                        <li class="nav-item"><a class="nav-link" href="{{ route('staff.dashboard') }}">Staff</a></li>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>
                    @endif
                @endauth
            </ul>

            <form class="sl-search me-xl-2" action="{{ route('products.index') }}" method="GET">
                <i class="bi bi-search"></i>
                <input type="text" name="q" placeholder="Search sneakers..." value="{{ request('q') }}">
            </form>

            <ul class="navbar-nav align-items-xl-center sl-icons">
                <li class="nav-item d-none d-xl-flex align-items-center"><button type="button" class="sl-theme-btn" data-theme-toggle aria-label="Switch to dark mode" title="Light / dark mode"><i class="bi bi-moon-stars-fill" aria-hidden="true"></i><i class="bi bi-sun-fill" aria-hidden="true"></i></button></li>
                @auth
                    @php $me = auth()->user(); @endphp
                    @if ($me->isCustomer())
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('cart.index') }}" title="Cart">
                                <i class="bi bi-cart3"></i>
                                <span class="sl-badge">{{ $me->cartItems()->sum('quantity') }}</span>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <button type="button" class="nav-link btn btn-link" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu" title="My Account">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end sl-user-menu">
                            <li class="px-3 pt-1 pb-2">
                                <strong class="d-block">{{ $me->name }}</strong>
                                <small class="text-muted">{{ $me->email }}</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @if ($me->isCustomer())
                                <li><a class="dropdown-item" href="{{ route('account.dashboard') }}"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>My Account</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.orders') }}"><i class="bi bi-bag me-2" aria-hidden="true"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.saved-cards') }}"><i class="bi bi-credit-card me-2" aria-hidden="true"></i>Saved Cards</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ $me->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}"><i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>{{ $me->isAdmin() ? 'Admin' : 'Staff' }} Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>My Profile</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}" title="Login"><i class="bi bi-person-fill"></i></a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
@yield('hero')
<main class="container my-4">
    @foreach (['success', 'error', 'info'] as $flashType)
        @if (session($flashType))
            <div class="alert alert-{{ $flashType === 'error' ? 'danger' : $flashType }} alert-dismissible fade show" role="alert">
                {{ session($flashType) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>
@php $shop = config('shop'); @endphp
<footer class="sl-footer mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('home') }}" class="sl-logo sl-footer-logo">SLAY<span>NA</span><small>SNEAKER STORE</small></a>
                <p class="sl-footer-tagline">Authentic sneakers from the world's top brands, delivered across Yangon and Myanmar.</p>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h2 class="sl-footer-title">Shop</h2>
                <ul class="sl-footer-links">
                    <li><a href="{{ route('products.index') }}">All Sneakers</a></li>
                    <li><a href="{{ route('products.new') }}">New Arrivals</a></li>
                    <li><a href="{{ route('brands') }}">Brands</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h2 class="sl-footer-title">SlayNa</h2>
                <ul class="sl-footer-links">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    @guest
                        <li><a href="{{ route('login') }}">Login</a></li>
                        <li><a href="{{ route('register') }}">Register</a></li>
                    @else
                        @php $user = auth()->user(); @endphp
                        <li><a href="{{ $user->isAdmin() ? route('admin.dashboard') : ($user->isStaff() ? route('staff.dashboard') : route('account.dashboard')) }}">{{ $user->isCustomer() ? 'My Account' : 'Dashboard' }}</a></li>
                    @endguest
                </ul>
            </div>

            <div class="col-md-6 col-lg-4">
                <h2 class="sl-footer-title">Visit &amp; Contact</h2>
                <ul class="sl-footer-contact">
                    <li>
                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                        <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($shop['address']) }}" target="_blank" rel="noopener">{{ $shop['address'] }}</a>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        <a href="mailto:{{ $shop['email'] }}">{{ $shop['email'] }}</a>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                        <a href="tel:{{ preg_replace('/\s+/', '', $shop['phone']) }}">{{ $shop['phone'] }}</a>
                    </li>
                    <li>
                        <i class="bi bi-clock-fill" aria-hidden="true"></i>
                        <span>{{ $shop['hours'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="sl-footer-bottom">
        <div class="container">
            &copy; {{ date('Y') }} SlayNa Sneaker Store &mdash; Information Systems (Yangon, Myanmar)
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
// Light / dark toggle: remembers the choice; until then, follows the device setting live
(() => {
    const root = document.documentElement;
    const buttons = document.querySelectorAll('[data-theme-toggle]');
    const label = () => buttons.forEach(b => b.setAttribute('aria-label', root.dataset.bsTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'));
    buttons.forEach(b => b.addEventListener('click', () => {
        root.dataset.bsTheme = root.dataset.bsTheme === 'dark' ? 'light' : 'dark';
        try { localStorage.setItem('sl-theme', root.dataset.bsTheme); } catch (e) {}
        label();
    }));
    matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        let saved = null;
        try { saved = localStorage.getItem('sl-theme'); } catch (err) {}
        if (!saved) { root.dataset.bsTheme = e.matches ? 'dark' : 'light'; label(); }
    });
    label();
})();

// Brand logos: flag transparent, mostly black/grey logos with .sl-logo-dark so dark mode can draw them white.
// Logos with a solid background, or with real colour, are left as uploaded.
(() => {
    const check = img => {
        try {
            const size = 48, c = document.createElement('canvas');
            c.width = c.height = size;
            const ctx = c.getContext('2d', { willReadFrequently: true });
            ctx.drawImage(img, 0, 0, size, size);
            const px = ctx.getImageData(0, 0, size, size).data;
            let clear = 0, solid = 0, dark = 0;
            for (let i = 0; i < px.length; i += 4) {
                const [r, g, b, a] = [px[i], px[i + 1], px[i + 2], px[i + 3]];
                if (a < 20) { clear++; continue; }
                if (a < 128) continue;
                solid++;
                if (0.299 * r + 0.587 * g + 0.114 * b < 90 && Math.max(r, g, b) - Math.min(r, g, b) < 50) dark++;
            }
            const total = px.length / 4;
            if (clear / total > 0.1 && solid && dark / solid > 0.6) img.classList.add('sl-logo-dark');
        } catch (e) { /* image from another origin: leave it as is */ }
    };
    document.querySelectorAll('.sl-marquee-track img, .sl-brand-logo img').forEach(img => {
        img.complete && img.naturalWidth ? check(img) : img.addEventListener('load', () => check(img), { once: true });
    });
})();
</script>
</body>
</html>
