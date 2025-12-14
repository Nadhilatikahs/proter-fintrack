@extends('layouts.auth-fintrack')
@section('title','FinTrack')

@section('content')
<div class="bg-[#CDF59C] rounded-3xl p-8 text-center shadow-xl space-y-6">

    <img src="{{ asset('images/fintrack-logo.svg') }}"
         class="mx-auto h-10" alt="FinTrack">

    <a href="{{ route('login') }}"
       class="block w-full bg-[#7BAD3E] text-white py-3 rounded-full font-bold">
        Log In
    </a>

    <a href="{{ route('register') }}"
       class="block w-full bg-white text-[#1F2937] py-3 rounded-full font-bold">
        Sign Up
    </a>

    <a href="{{ route('password.request') }}"
       class="text-sm underline">
        Forgot Password?
    </a>
</div>
@endsection
