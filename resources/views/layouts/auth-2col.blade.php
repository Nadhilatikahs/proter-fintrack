<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Judul halaman --}}
    <title>@yield('title', 'FinTrack')</title>

    {{-- WAJIB: Vite (CSS + JS, termasuk CSRF & session) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Background animation (aman, hanya CSS) --}}
    <style>
        .animated-bg {
            background:
                radial-gradient(circle at 20% 20%, #7BAD3E, transparent 40%),
                radial-gradient(circle at 80% 30%, #EFF6D2, transparent 45%),
                radial-gradient(circle at 50% 80%, #ffffff, transparent 55%),
                linear-gradient(180deg, #ffffff 0%, #EFF6D2 60%, #1F2937 100%);
            background-size: 200% 200%;
            animation: gradientMove 18s ease infinite;
        }

        @keyframes gradientMove {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>

<body class="min-h-screen animated-bg flex items-center justify-center px-6">

    {{-- Container utama --}}
    <div class="w-full max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-14 items-center">

            {{-- LEFT: Branding (desktop only) --}}
            <div class="hidden md:flex flex-col justify-center">
                <img
                    src="{{ asset('images/fintrack-logo.svg') }}"
                    class="w-44 mb-8"
                    alt="FinTrack"
                >

                <h1 class="text-4xl font-bold text-[#1F2937] leading-tight">
                    Grow your money,<br>
                    track your life
                </h1>

                <p class="mt-6 text-[#1F2937]/80 leading-relaxed max-w-md">
                    FinTrack membantu kamu mencatat dan memantau keuangan secara real-time.
                </p>

                <div class="mt-8">
                    <span class="inline-block bg-[#7BAD3E] text-white px-5 py-3 rounded-lg font-semibold">
                        Smart Finance, Real Control
                    </span>
                </div>
            </div>

            {{-- RIGHT: Card Form --}}
            <div class="flex justify-center">
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-9">
                    @yield('card')
                </div>
            </div>

        </div>
    </div>

</body>
</html>
