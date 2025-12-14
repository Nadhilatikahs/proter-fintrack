@extends('layouts.auth-2col')
@section('title','Login | FinTrack')

@section('card')
<h2 class="text-2xl font-bold text-[#1F2937] mb-1">
    Masuk ke FinTrack
</h2>
<p class="text-[#1F2937]/70 mb-7">
    Kelola keuanganmu dengan lebih teratur
</p>

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label class="text-sm font-medium text-[#1F2937]">Email</label>
        <input type="email" name="email" required
               class="mt-1 w-full rounded-lg border px-4 py-3
                      focus:ring-2 focus:ring-[#7BAD3E]">
    </div>

    <div>
        <label class="text-sm font-medium text-[#1F2937]">Password</label>
        <input type="password" name="password" required
               class="mt-1 w-full rounded-lg border px-4 py-3
                      focus:ring-2 focus:ring-[#7BAD3E]">
    </div>

    <div class="flex justify-between text-sm">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="remember"> Ingat saya
        </label>
        <a href="{{ route('password.request') }}"
           class="text-[#7BAD3E] font-medium hover:underline">
            Lupa password?
        </a>
    </div>

    <button class="w-full bg-[#7BAD3E] text-white py-3 rounded-lg font-semibold">
        Masuk
    </button>
</form>

<p class="mt-6 text-center text-sm text-[#1F2937]/70">
    Belum punya akun?
    <a href="{{ route('register') }}"
       class="text-[#7BAD3E] font-semibold hover:underline">
        Daftar sekarang
    </a>
</p>
@endsection
