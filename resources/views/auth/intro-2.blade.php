@extends('layouts.auth-fintrack')
@section('title','Get Started')

@section('content')
<div class="bg-[#CDF59C] rounded-3xl p-8 text-center shadow-xl">
    <h1 class="text-xl font-extrabold text-[#1F2937]">
        Are you ready to manage<br>
        your finances independently?
    </h1>

    <div class="my-10 flex justify-center">
        📱
    </div>

    <a href="{{ route('auth.choice') }}"
    onclick="localStorage.setItem('fintrack_intro_done','true')"
    class="block w-full bg-[#7BAD3E] text-white py-3 rounded-full font-bold">
        Next >>
    </a>

</div>
@endsection
