{{-- Titik berwarna penanda mata kuliah. Pakai: <x-course-dot :color="$course->color" /> --}}
@props(['color'])

<span {{ $attributes->merge(['class' => 'inline-block size-2.5 shrink-0 rounded-full']) }} style="background-color: {{ $color }}" aria-hidden="true"></span>
