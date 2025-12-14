@extends('layouts.auth-fintrack')
@section('title','Forgot Password')

@section('content')
<div class="bg-[#CDF59C] rounded-3xl p-8 shadow-xl">
    <h1 class="text-xl font-extrabold mb-4">Forgot Password</h1>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <input type="email" name="email" required
               placeholder="Enter Email Address"
               class="w-full rounded-full px-4 py-3 mb-4">

        <button class="w-full bg-[#7BAD3E] text-white py-3 rounded-full font-bold">
            Next Step
        </button>
    </form>
</div>
@endsection
