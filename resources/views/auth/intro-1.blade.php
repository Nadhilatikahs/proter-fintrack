@extends('layouts.auth-fintrack')
@section('title','Welcome')
<script>
    if (localStorage.getItem('fintrack_intro_done') === 'true') {
        window.location.href = "{{ route('auth.choice') }}";
    }
</script>

@section('content')
<div class="bg-[#CDF59C] rounded-3xl p-8 text-center shadow-xl">
    <h1 class="text-2xl font-extrabold text-[#1F2937]">
        Welcome To<br>FinTrack
    </h1>

    <div class="my-10 flex justify-center">
        {{-- icon ilustrasi --}}
        <div class="w-24 h-24 rounded-full bg-white flex items-center justify-center">
            💰
        </div>
    </div>

    <a href="{{ route('intro.2') }}"
       class="block w-full bg-[#7BAD3E] text-white py-3 rounded-full font-bold">
        Next >>
        <a href="{{ route('auth.choice') }}"
        onclick="localStorage.setItem('fintrack_intro_done','true')"
        class="block mt-4 text-sm underline">
            Skip
        </a>

    </a>
</div>
@endsection
