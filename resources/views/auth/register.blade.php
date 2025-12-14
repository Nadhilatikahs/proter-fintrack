@extends('layouts.auth-2col')
@section('title','Register | FinTrack')

@section('card')
<h2 class="text-2xl font-bold text-[#1F2937] mb-6">
    Create Account
</h2>

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label class="text-sm font-medium">Full Name</label>
        <input name="name" required class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <div>
        <label class="text-sm font-medium">Email</label>
        <input name="email" type="email" required class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <div>
        <label class="text-sm font-medium">Password</label>
        <input name="password" type="password" required class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <div>
        <label class="text-sm font-medium">Confirm Password</label>
        <input name="password_confirmation" type="password" required class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <button class="w-full bg-[#7BAD3E] text-white py-3 rounded-lg font-semibold">
        Sign Up
    </button>
</form>

<p class="mt-6 text-center text-sm">
    Sudah punya akun?
    <a href="{{ route('login') }}" class="text-[#7BAD3E] font-semibold hover:underline">
        Log In
    </a>
</p>
@endsection
