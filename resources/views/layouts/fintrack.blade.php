{{-- resources/views/layouts/fintrack.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Fintrack')</title>

    {{-- CSS khusus Fintrack --}}
    <link rel="stylesheet" href="{{ asset('fintrack/fintrack-dashboard.css') }}">
</head>
<body class="ft-body">
<div class="ft-shell">

    {{-- SIDEBAR --}}
    <aside class="ft-sidebar">
        <div class="ft-sidebar-inner">
            {{-- Logo, nanti kamu ganti ke SVG sendiri --}}
            <div class="ft-logo">
                <img src="{{ asset('fintrack/logo.svg') }}" alt="FintracR" class="ft-logo-img">
            </div>

            <div class="ft-menu-label">MENU</div>

            {{-- MENU ATAS: Dashboard, Budget & Goals, Transaction, Reports --}}
            <nav class="ft-nav">
                <a href="{{ route('dashboard') }}"
                   class="ft-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-dashboard"></span>
                    <span class="ft-nav-text">Dashboard</span>
                </a>

                <a href="{{ route('budget-goals.index') }}"
                   class="ft-nav-item {{ request()->routeIs('budget-goals.*') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-bag"></span>
                    <span class="ft-nav-text">Budget &amp; Goals</span>
                </a>

                <a href="{{ route('transactions.index') }}"
                   class="ft-nav-item {{ request()->routeIs('transactions.*') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-transaction"></span>
                    <span class="ft-nav-text">Transaction</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="ft-nav-item {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-reports"></span>
                    <span class="ft-nav-text">Reports</span>
                </a>
            </nav>

            {{-- Garis pemisah menu atas vs profile --}}
            <hr class="ft-nav-separator">

            {{-- MENU PROFILE (tengah) --}}
            <nav class="ft-nav ft-nav-secondary">
                <a href="{{ route('profile.edit') }}"
                   class="ft-nav-item {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-profile"></span>
                    <span class="ft-nav-text">Profile</span>
                </a>
            </nav>

            {{-- MENU LEAVE DI PALING BAWAH --}}
            <div class="ft-nav-bottom">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ft-nav-item ft-nav-item-leave">
                        <span class="ft-nav-icon ft-ico-leave"></span>
                        <span class="ft-nav-text">Leave</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN / KONTEN KANAN --}}
    <div class="ft-main">
        {{-- top bar (judul + notifikasi + avatar) --}}
        <header class="ft-topbar">
            <div class="ft-top-left">
                @yield('top-left')
            </div>

            <div class="ft-top-right">
                {{-- bell notifikasi (nanti kita sambung ke halaman Notifications) --}}
                <button class="ft-icon-btn" type="button">
                    <span class="ft-bell-dot"></span>
                </button>

                <div class="ft-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'J', 0, 1)) }}
                </div>
            </div>
        </header>

        <main class="ft-main-inner">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
