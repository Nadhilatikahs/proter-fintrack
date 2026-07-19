@extends('layouts.auth-2col')
@section('title','Register | FinTrack')

@section('card')
    <h2 class="text-2xl font-bold text-[#1F2937]">
        Create Account
    </h2>
    <p class="mt-1 text-sm text-[#1F2937]/70">
        Daftar dulu, nanti lanjut login.
    </p>

    {{-- GLOBAL ERRORS --}}
    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <div class="font-semibold mb-2">Ada yang perlu dibenerin:</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf

        {{-- Full Name --}}
        <div>
            <label class="text-sm font-medium text-[#1F2937]">Full Name</label>
            <input
                name="name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                       focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]/40"
            >
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="text-sm font-medium text-[#1F2937]">Email</label>
            <input
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                       focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]/40"
            >
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="text-sm font-medium text-[#1F2937]">Password</label>
            <input
                name="password"
                type="password"
                required
                autocomplete="new-password"
                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                       focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]/40"
            >
            <p class="mt-2 text-xs text-[#1F2937]/60">
                Minimal 5 karakter, wajib huruf besar, angka, dan simbol.
            </p>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label class="text-sm font-medium text-[#1F2937]">Confirm Password</label>
            <input
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3
                       focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]/40"
            >
        </div>

        <button
            type="submit"
            class="mt-2 w-full rounded-xl bg-[#7BAD3E] py-3 font-semibold text-white
                   hover:bg-[#6a9c34] transition"
        >
            Sign Up
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-[#1F2937]/70">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-[#7BAD3E] font-semibold hover:underline">
            Log In
        </a>
    </p>
@endsection
