{{-- Ikon garis sederhana. Pakai: <x-icon name="calendar" class="size-5" /> --}}
@props(['name'])

<svg class="{{ $attributes->get('class', 'size-5') }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard')
            <rect x="3.5" y="3.5" width="7" height="7" rx="2" /><rect x="13.5" y="3.5" width="7" height="7" rx="2" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="2" /><rect x="13.5" y="13.5" width="7" height="7" rx="2" />
            @break
        @case('tasks')
            <rect x="5" y="4.5" width="14" height="16.5" rx="2.5" /><path d="M9.5 4.5V4a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v.5" /><path d="m9 13 2 2 4-4.5" />
            @break
        @case('calendar')
            <rect x="3.5" y="5" width="17" height="15.5" rx="3" /><path d="M8 3v4M16 3v4M3.5 10h17" />
            @break
        @case('user')
            <circle cx="12" cy="8.5" r="3.5" /><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8.5" /><path d="M12 7.5V12l3 2" />
            @break
        @case('alert')
            <path d="M12 4 3 19h18L12 4Z" /><path d="M12 10v4M12 17v.01" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('edit')
            <path d="M4 20v-3.5L16 4.5a2.1 2.1 0 0 1 3 3L7.5 20H4Z" />
            @break
        @case('trash')
            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3" />
            @break
        @case('check')
            <path d="m5 12.5 4.5 4.5L19 7.5" />
            @break
        @case('logout')
            <path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3M14 8l4 4-4 4M18 12H9" />
            @break
        @case('chevron-left')
            <path d="m15 6-6 6 6 6" />
            @break
        @case('chevron-right')
            <path d="m9 6 6 6-6 6" />
            @break
    @endswitch
</svg>
