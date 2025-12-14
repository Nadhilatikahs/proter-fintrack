@extends('layouts.auth-fintrack')
@section('title','Login - FinTrack')

@section('content')
<div class="w-full max-w-md">

    {{-- CARD --}}
    <div class="rounded-3xl bg-[var(--ft-soft)]/60 border border-black/5 shadow-xl overflow-hidden">

        {{-- TOP --}}
        <div class="px-10 pt-10 pb-6 bg-[var(--ft-bg)]">
            <div class="flex justify-center">
                <img src="{{ asset('images/fintrack-logo.svg') }}" class="h-10" alt="FinTrack">
            </div>
            <h1 class="text-center text-2xl font-extrabold mt-6">Welcome</h1>
        </div>

        {{-- FORM AREA --}}
        <div class="px-8 pb-8 pt-6">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="text-sm font-semibold">Username or Email</label>
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required autofocus
                        placeholder="example@fintrack.com"
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]"
                    >
                    @error('email')
                        <div class="text-sm text-red-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="text-sm font-semibold">Password</label>
                    <input
                        name="password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]"
                    >
                    @error('password')
                        <div class="text-sm text-red-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- BUTTON --}}
                <button
                    type="submit"
                    class="w-full rounded-full py-3 font-bold text-white bg-[var(--ft-green)] hover:opacity-90 transition"
                >
                    Log In
                </button>

                {{-- FORGOT --}}
                @if (Route::has('password.request'))
                <div class="text-center">
                    <a class="text-xs font-semibold underline underline-offset-4"
                       href="{{ route('password.request') }}">
                        Forgot Password?
                    </a>
                </div>
                @endif

                {{-- SOCIAL PLACEHOLDER (opsional) --}}
                <div class="pt-2 text-center text-xs opacity-70">
                    Or log in with
                </div>
                <div class="flex justify-center gap-4">
                    <button type="button" class="h-10 w-10 rounded-full bg-white/60 border border-black/10">G</button>
                    <button type="button" class="h-10 w-10 rounded-full bg-white/60 border border-black/10">f</button>
                </div>
            </form>
        </div>
    </div>

    {{-- LINK REGISTER --}}
    <div class="text-center mt-5 text-sm">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold underline underline-offset-4">Sign Up</a>
    </div>
</div>
@endsection
