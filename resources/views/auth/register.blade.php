@extends('layouts.auth-fintrack')
@section('title','Register - FinTrack')

@section('content')
<div class="w-full max-w-md">

    <div class="rounded-3xl bg-[var(--ft-soft)]/60 border border-black/5 shadow-xl overflow-hidden">

        <div class="px-10 pt-10 pb-6 bg-[var(--ft-bg)]">
            <div class="flex justify-center">
                <img src="{{ asset('images/fintrack-logo.svg') }}" class="h-10" alt="FinTrack">
            </div>
            <h1 class="text-center text-2xl font-extrabold mt-6">Create Account</h1>
        </div>

        <div class="px-8 pb-8 pt-6">
            <form method="POST" action="{{ route('register') }}" class="space-y-3">
                @csrf

                {{-- Full Name --}}
                <div>
                    <label class="text-sm font-semibold">Full Name</label>
                    <input name="name" type="text" value="{{ old('name') }}" required
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]">
                    @error('name') <div class="text-sm text-red-600 mt-2">{{ $message }}</div> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="text-sm font-semibold">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" required
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]">
                    @error('email') <div class="text-sm text-red-600 mt-2">{{ $message }}</div> @enderror
                </div>

                {{-- Mobile (opsional UI, kalau belum ada di DB jangan dipaksa) --}}
                <div>
                    <label class="text-sm font-semibold">Mobile Number (optional)</label>
                    <input name="phone" type="text" value="{{ old('phone') }}"
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]">
                </div>

                {{-- Password --}}
                <div>
                    <label class="text-sm font-semibold">Password</label>
                    <input name="password" type="password" required
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]">
                    @error('password') <div class="text-sm text-red-600 mt-2">{{ $message }}</div> @enderror
                </div>

                {{-- Confirm --}}
                <div>
                    <label class="text-sm font-semibold">Confirm Password</label>
                    <input name="password_confirmation" type="password" required
                        class="mt-2 w-full rounded-full px-4 py-3 bg-white/70 border border-black/10
                               focus:outline-none focus:ring-2 focus:ring-[var(--ft-green)]">
                </div>

                <button type="submit"
                    class="w-full mt-2 rounded-full py-3 font-bold text-white bg-[var(--ft-green)] hover:opacity-90 transition">
                    Sign Up
                </button>

                <div class="text-center text-xs opacity-70 mt-2">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-bold underline underline-offset-4">Log In</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
