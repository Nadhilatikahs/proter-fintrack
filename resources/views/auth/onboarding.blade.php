@extends('layouts.auth-fintrack')
@section('title','Welcome')

@section('content')

<script>
    if (localStorage.getItem('fintrack_intro_done') === 'true') {
        window.location.href = "{{ route('auth.choice') }}";
    }
</script>

<div
    x-data="onboarding()"
    x-init="init()"
    class="relative overflow-hidden bg-[#CDF59C] rounded-3xl shadow-xl p-8 select-none"
>

    {{-- SLIDES --}}
    <div
        class="flex transition-transform duration-300 ease-out"
        :style="`transform: translateX(-${active * 100}%);`"
        @touchstart="start($event.touches[0].clientX)"
        @touchmove="move($event.touches[0].clientX)"
        @touchend="end()"
        @mousedown.prevent="start($event.clientX)"
        @mousemove.prevent="dragging && move($event.clientX)"
        @mouseup.prevent="end()"
        @mouseleave="dragging && end()"
    >

        {{-- SLIDE 1 --}}
        <div class="w-full flex-shrink-0 text-center space-y-4 px-2">
            <h1 class="text-2xl font-extrabold text-[#1F2937]">
                Welcome To FinTrack
            </h1>

            <div class="text-6xl">💰</div>

            <p class="text-sm text-[#4B5563]">
                Grow your money, track your life
            </p>
        </div>


        {{-- SLIDE 2 --}}
        <div class="w-full flex-shrink-0 text-center space-y-6">
            <h1 class="text-xl font-extrabold">
                Manage your finances independently
            </h1>
            <div class="text-6xl">📱</div>
            <p class="text-sm opacity-80">
                Track income, expenses, and goals in one place
            </p>
        </div>

    </div>

    {{-- INDICATOR --}}
    <div class="flex justify-center gap-2 mt-6">
        <template x-for="(s, i) in slides" :key="i">
            <div
                class="h-2 w-2 rounded-full"
                :class="active === i ? 'bg-[#7BAD3E]' : 'bg-gray-300'"
            ></div>
        </template>
    </div>

    {{-- ACTION --}}
    <div class="mt-6 space-y-3">
        <button
            x-show="active === slides.length - 1"
            class="w-full bg-[#7BAD3E] text-white py-3 rounded-full font-bold hover:brightness-95 transition">
            Get Started
        </button>


        <button
            @click="skip()"
            class="w-full text-sm text-[#4B5563] underline">
            Skip
        </button>

    </div>

</div>

<script>
function onboarding() {
    return {
        slides: [1,2],
        active: 0,
        startX: 0,
        currentX: 0,
        dragging: false,

        init() {},

        start(x) {
            this.startX = x
            this.dragging = true
        },

        move(x) {
            if (!this.dragging) return
            this.currentX = x
        },

        end() {
            if (!this.dragging) return

            const diff = this.startX - this.currentX

            if (diff > 50 && this.active < this.slides.length - 1) {
                this.active++
            }
            if (diff < -50 && this.active > 0) {
                this.active--
            }

            this.dragging = false
            this.startX = 0
            this.currentX = 0
        },

        skip() {
            localStorage.setItem('fintrack_intro_done','true')
            window.location.href = "{{ route('auth.choice') }}"
        },

        finish() {
            localStorage.setItem('fintrack_intro_done','true')
            window.location.href = "{{ route('auth.choice') }}"
        }
    }
}
</script>

@endsection
