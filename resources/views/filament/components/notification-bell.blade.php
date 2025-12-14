@php
    $unreadCount = \App\Models\Reminder::where('user_id', auth()->id())
        ->where('is_read', false)
        ->count();
@endphp

<a href="{{ route('filament.admin.pages.notifications') }}"
    class="fi-notification-bell relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
    title="Notifikasi">
    {{-- Bell Icon --}}
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="w-6 h-6 text-gray-600 dark:text-gray-300">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
    </svg>

    {{-- Badge count --}}
    @if($unreadCount > 0)
        <span
            class="fi-notification-badge absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-red-500 rounded-full shadow-sm">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</a>
