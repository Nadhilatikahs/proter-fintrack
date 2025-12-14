@extends('layouts.auth-2col')
@section('title','Forgot Password')

@section('card')
<h2 class="text-2xl font-bold mb-4">Forgot Password</h2>

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf
    <div>
        <label class="text-sm font-medium">Email</label>
        <input type="email" name="email" required
               class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <button class="w-full bg-[#7BAD3E] text-white py-3 rounded-lg font-semibold">
        Next Step
    </button>
</form>
@endsection
