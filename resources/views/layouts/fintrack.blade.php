{{-- resources/views/layouts/fintrack.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Fintrack')</title>

    {{-- CSS khusus Fintrack --}}
    <link rel="stylesheet" href="{{ asset('fintrack/fintrack-dashboard.css') }}">
    
    {{-- Tailwind CSS CDN (jika diperlukan) --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="ft-body">
<div class="ft-shell">

    {{-- SIDEBAR --}}
    <aside class="ft-sidebar">
        <div class="ft-sidebar-inner">
            {{-- Logo --}}
            <div class="ft-logo">
                <img src="{{ asset('images/fintrack-logo.svg') }}" alt="Fintrack">
            </div>

            <div class="ft-menu-label">MENU</div>

            {{-- MENU ATAS: Dashboard, Budget & Goals, Transaction, Reports --}}
            <nav class="ft-nav">
                <a href="{{ route('dashboard') }}"
                   class="ft-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-dashboard"></span>
                    <span class="ft-nav-text">Dashboard</span>
                </a>

                <a href="{{ url('/admin/budget-goals-overview') }}"
                   class="ft-nav-item {{ request()->is('admin/budget-goals-overview') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-bag"></span>
                    <span class="ft-nav-text">Budget &amp; Goals</span>
                </a>

                <a href="{{ url('/admin/transactions-overview') }}"
                   class="ft-nav-item {{ request()->is('admin/transactions-overview') ? 'is-active' : '' }}">
                    <span class="ft-nav-icon ft-ico-transaction"></span>
                    <span class="ft-nav-text">Transaction</span>
                </a>

                <a href="{{ url('/admin/reports') }}"
                   class="ft-nav-item {{ request()->is('admin/reports') ? 'is-active' : '' }}">
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

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Custom scripts jika ada --}}
@if(file_exists(public_path('js/fintrack/reports.js')))
<script src="{{ asset('js/fintrack/reports.js') }}"></script>
@endif

@stack('scripts')
</body>
</html>
