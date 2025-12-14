@extends('layouts.auth-2col')
@section('title','New Password')

@section('card')
<h2 class="text-2xl font-bold mb-6">New Password</h2>

<form method="POST" action="{{ route('password.store') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

    <div>
        <label class="text-sm font-medium">New Password</label>
        <input type="password" name="password" required
               class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <div>
        <label class="text-sm font-medium">Confirm Password</label>
        <input type="password" name="password_confirmation" required
               class="mt-1 w-full rounded-lg border px-4 py-3">
    </div>

    <button class="w-full bg-[#7BAD3E] text-white py-3 rounded-lg font-semibold">
        Change Password
    </button>
</form>
@endsection
