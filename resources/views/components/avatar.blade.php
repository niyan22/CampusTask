{{-- Foto profil, atau huruf pertama nama kalau belum upload. Pakai: <x-avatar :user="$user" class="size-10" /> --}}
@props(['user'])

@if ($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="Foto {{ $user->name }}" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => 'grid shrink-0 place-items-center rounded-full bg-periwinkle font-semibold text-ultramarine']) }}>{{ $user->initial() }}</span>
@endif
