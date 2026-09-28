<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HasnurVerif') - HasnurVerif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @auth
        @php
            $user = auth()->user();
            $initials = collect(explode(' ', trim($user->name)))
                ->filter()
                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->take(2)
                ->implode('');
        @endphp
        <div class="container">
            <nav class="navbar navbar-expand-lg hv-topbar mt-3 mb-4">
                <div class="container-fluid px-1">
                    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('img/logo_hasnur_200.png') }}" alt="Hasnur Group" height="38" width="38">
                        <span class="d-flex flex-column lh-1">
                            <span class="fw-bold">HasnurVerif</span>
                            <span class="brand-sub">HASNUR GROUP</span>
                        </span>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"
                        aria-controls="adminNav" aria-expanded="false" aria-label="Buka menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="adminNav">
                        <ul class="navbar-nav ms-lg-auto mb-2 mb-lg-0 hv-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.hospitals.*') ? 'active' : '' }}"
                                    href="{{ route('admin.hospitals.index') }}">Rumah Sakit</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.logs.verifications') ? 'active' : '' }}"
                                    href="{{ route('admin.logs.verifications') }}">Log Verifikasi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.logs.logins') ? 'active' : '' }}"
                                    href="{{ route('admin.logs.logins') }}">Log Login RS</a>
                            </li>
                            @if ($user->isSuperAdmin())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}"
                                        href="{{ route('admin.admins.index') }}">Kelola Admin</a>
                                </li>
                            @endif
                        </ul>

                        <div class="dropdown ms-2 mt-2 mt-lg-0">
                            <button class="hv-avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                title="{{ $user->name }}">
                                {{ $initials }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end hv-dropdown">
                                <li class="px-3 pt-2 pb-1 text-white-50 small">Masuk sebagai</li>
                                <li><span class="dropdown-item-text fw-bold">{{ $user->name }}</span></li>
                                <li><span class="dropdown-item-text small text-white-50">{{ $user->employee_id }} &middot; {{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    @endauth

    <main class="py-2">
        <div class="container">
            <x-flash />
            @yield('content')
        </div>
    </main>

    <footer class="text-center text-muted small py-4">
        &copy; {{ date('Y') }} Hasnur Group &mdash; HasnurVerif
    </footer>

    @stack('scripts')
</body>
</html>
