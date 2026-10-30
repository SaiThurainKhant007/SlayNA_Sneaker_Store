<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@hasSection('title')@yield('title') - @endif{{ config('app.name') }} Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    {{-- Dark by default; a saved choice is applied before the page paints so there is no flash --}}
    <script>
        try { if (localStorage.getItem('sl-admin-theme') === 'light') document.documentElement.dataset.bsTheme = 'light'; } catch (e) {}
    </script>
</head>
<body class="sl-admin">
@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    // [route, active pattern, icon, label]
    $menu = [
        'Main' => [
            ['admin.dashboard', 'admin.dashboard', 'bi-grid-1x2-fill', 'Dashboard'],
            ['admin.products.index', 'admin.products.*', 'bi-box-seam-fill', 'Products'],
            ['admin.categories.index', 'admin.categories.*', 'bi-tags-fill', 'Categories'],
            ['admin.brands.index', 'admin.brands.*', 'bi-award-fill', 'Brands & Logos'],
        ],
        'Marketing' => [
            ['admin.promotions.index', 'admin.promotions.*', 'bi-megaphone-fill', 'Promotions'],
            ['admin.gallery.index', 'admin.gallery.*', 'bi-images', 'Model Gallery'],
        ],
        'People' => [
            ['admin.staff.index', 'admin.staff.*', 'bi-people-fill', 'Staff & Customers'],
        ],
        'Insights' => [
            ['admin.reports.index', 'admin.reports.*', 'bi-bar-chart-fill', 'Reports'],
        ],
    ];
@endphp

<aside class="sl-admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-label="Admin menu">
    <div class="sl-admin-brand">
        <a href="{{ route('admin.dashboard') }}" class="sl-logo">SLAY<span>NA</span><small>ADMIN PANEL</small></a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close menu"></button>
    </div>
    <nav class="sl-admin-nav">
        @foreach ($menu as $group => $links)
            <p class="sl-admin-nav-group">{{ $group }}</p>
            <ul>
                @foreach ($links as [$route, $pattern, $icon, $label])
                    @php $active = request()->routeIs($pattern); @endphp
                    <li>
                        <a href="{{ route($route) }}" class="{{ $active ? 'is-active' : '' }}" @if ($active) aria-current="page" @endif>
                            <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                            @if ($route === 'admin.products.index' && $adminAlerts['lowStock'] > 0)
                                <span class="sl-admin-nav-badge" title="{{ $adminAlerts['lowStock'] }} products low on stock">{{ $adminAlerts['lowStock'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
        <p class="sl-admin-nav-group">System</p>
        <ul>
            <li><a href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-shop" aria-hidden="true"></i><span>View Store</span><i class="bi bi-box-arrow-up-right ms-auto small" aria-hidden="true"></i></a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Log Out</span></button>
                </form>
            </li>
        </ul>
    </nav>
</aside>

<div class="sl-admin-main">
    <header class="sl-admin-topbar">
        <button type="button" class="sl-admin-icon-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        @if (request()->routeIs('admin.dashboard'))
            @php $hour = now()->hour; @endphp
            <div class="sl-admin-greeting">
                <strong>Good {{ $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening') }}, {{ Str::before($user->name, ' ') ?: $user->name }}</strong>
                <span>{{ now()->format('l, j F Y') }}</span>
            </div>
        @else
            <nav class="sl-admin-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i><span class="visually-hidden">Dashboard</span></a>
                <i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">@yield('title')</span>
            </nav>
        @endif

        <div class="sl-admin-topbar-actions">
            <button type="button" class="sl-admin-icon-btn sl-theme-toggle" aria-label="Switch to light theme" title="Switch theme">
                <i class="bi bi-sun-fill" aria-hidden="true"></i><i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
            </button>
            <div class="dropdown">
                <button type="button" class="sl-admin-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications ({{ $adminAlerts['total'] }})">
                    <i class="bi bi-bell-fill" aria-hidden="true"></i>
                    @if ($adminAlerts['total'] > 0)<span class="sl-admin-bell-count">{{ $adminAlerts['total'] > 99 ? '99+' : $adminAlerts['total'] }}</span>@endif
                </button>
                <div class="dropdown-menu dropdown-menu-end sl-admin-notify">
                    <h2 class="dropdown-header">Needs attention</h2>
                    <a class="dropdown-item" href="{{ route('admin.reports.index', ['type' => 'inventory']) }}">
                        <i class="bi bi-exclamation-triangle-fill text-danger" aria-hidden="true"></i>
                        <span><strong>{{ $adminAlerts['lowStock'] }}</strong> {{ Str::plural('product', $adminAlerts['lowStock']) }} low on stock</span>
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-hourglass-split text-warning" aria-hidden="true"></i>
                        <span><strong>{{ $adminAlerts['pendingOrders'] }}</strong> {{ Str::plural('order', $adminAlerts['pendingOrders']) }} waiting for staff</span>
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-credit-card-fill text-primary" aria-hidden="true"></i>
                        <span><strong>{{ $adminAlerts['pendingPayments'] }}</strong> {{ Str::plural('payment', $adminAlerts['pendingPayments']) }} to confirm</span>
                    </a>
                </div>
            </div>

            <div class="dropdown">
                <button type="button" class="sl-admin-user" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu for {{ $user->name }}">
                    <span class="sl-admin-avatar" aria-hidden="true">{{ $initials }}</span>
                    <span class="sl-admin-user-text d-none d-sm-flex"><strong>{{ $user->name }}</strong><small>Administrator</small></span>
                    <i class="bi bi-chevron-down small" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end sl-admin-user-menu">
                    <li><span class="dropdown-item-text small text-muted">{{ $user->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-shop me-2" aria-hidden="true"></i>View Store</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Log Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="sl-admin-content">
        @foreach (['success', 'error', 'info'] as $flashType)
            @if (session($flashType))
                <div class="alert alert-{{ $flashType === 'error' ? 'danger' : $flashType }} alert-dismissible fade show" role="alert">
                    {{ session($flashType) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
// Light / dark theme toggle, remembered in this browser
(() => {
    const root = document.documentElement;
    const btn = document.querySelector('.sl-theme-toggle');
    const label = () => btn.setAttribute('aria-label', root.dataset.bsTheme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme');
    btn.addEventListener('click', () => {
        root.dataset.bsTheme = root.dataset.bsTheme === 'dark' ? 'light' : 'dark';
        try { localStorage.setItem('sl-admin-theme', root.dataset.bsTheme); } catch (e) {}
        label();
    });
    label();
})();
</script>
</body>
</html>
