@extends('layouts.auth-2col')
@section('title','Register | FinTrack')

@section('card')
<h2 class="text-2xl font-bold text-[#1F2937] mb-6">
    Create Account
</h2>

{{-- FLASH SUCCESS (DARI REGISTER) --}}
@if (session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
        <p class="text-sm text-green-700 font-medium">
            {{ session('success') }}
        </p>
    </div>
@endif

{{-- ERROR LOGIN --}}
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
        <p class="text-sm text-red-700">
            Email atau password salah.
        </p>
    </div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    {{-- FULL NAME --}}
    <div>
        <label class="text-sm font-medium text-gray-700">Full Name</label>
        <input
            name="name"
            value="{{ old('name') }}"
            required
            class="mt-1 w-full rounded-lg border px-4 py-3
                @error('name') border-red-500 @else border-gray-300 @enderror
                focus:ring-[#7BAD3E] focus:border-[#7BAD3E]"
        >

        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- EMAIL --}}
    <div>
        <label class="text-sm font-medium text-gray-700">Email</label>
        <input
            name="email"
            type="email"
            value="{{ old('email') }}"
            required
            class="mt-1 w-full rounded-lg border px-4 py-3
                @error('email') border-red-500 @else border-gray-300 @enderror
                focus:ring-[#7BAD3E] focus:border-[#7BAD3E]"
        >

        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- PASSWORD --}}
    <div>
        <label class="text-sm font-medium text-gray-700">Password</label>
        <input
            name="password"
            type="password"
            required
            class="mt-1 w-full rounded-lg border px-4 py-3
                @error('password') border-red-500 @else border-gray-300 @enderror
                focus:ring-[#7BAD3E] focus:border-[#7BAD3E]"
        >

        {{-- Password rule hint --}}
        <p class="mt-1 text-xs text-gray-500">
            Minimal 5 karakter, wajib huruf besar, angka, dan simbol.
        </p>

        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- CONFIRM PASSWORD --}}
    <div>
        <label class="text-sm font-medium text-gray-700">Confirm Password</label>
        <input
            name="password_confirmation"
            type="password"
            required
            class="mt-1 w-full rounded-lg border px-4 py-3 border-gray-300
                focus:ring-[#7BAD3E] focus:border-[#7BAD3E]"
        >
    </div>

    {{-- SUBMIT --}}
    <button
        type="submit"
        class="w-full bg-[#7BAD3E] hover:bg-[#6a9a36] transition
               text-white py-3 rounded-lg font-semibold"
    >
        Sign Up
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    Sudah punya akun?
    <a href="{{ route('login') }}" class="text-[#7BAD3E] font-semibold hover:underline">
