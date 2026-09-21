@props(['title' => 'Dashboard'])

@php
    // Menu utama. 'match' dipakai untuk menandai menu yang sedang aktif.
    $menu = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'match' => 'dashboard'],
        ['label' => 'Tugas', 'icon' => 'tasks', 'route' => 'tasks.index', 'match' => 'tasks.*'],
        ['label' => 'Kalender', 'icon' => 'calendar', 'route' => 'calendar', 'match' => 'calendar*'],
        ['label' => 'Jadwal', 'icon' => 'schedule', 'route' => 'schedule.index', 'match' => 'schedule.*'],
        ['label' => 'Mata Kuliah', 'icon' => 'book', 'route' => 'courses.index', 'match' => 'courses.*'],
    ];
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Tugas Kampus</title>
    <x-theme-script />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-page font-sans text-ink antialiased">

    {{-- Sidebar (layar besar) --}}
    <aside class="fixed inset-y-0 left-0 z-20 hidden w-64 flex-col bg-ultramarine px-4 py-6 text-periwinkle lg:flex">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 font-display text-xl text-custard">
            <span class="grid size-9 place-items-center rounded-xl bg-custard text-ultramarine"><x-icon name="tasks" /></span>
            Tugas Kampus
        </a>

        <nav class="mt-10 space-y-1" aria-label="Menu utama">
            @foreach ($menu as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-amethyst text-white' : 'hover:bg-white/10 hover:text-white' }}">
                    <x-icon :name="$item['icon']" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-1 border-t border-white/10 pt-4">
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-white/10 {{ request()->routeIs('profile.*') ? 'bg-white/10' : '' }}">
                <x-avatar :user="$user" class="size-10" />
                <span class="min-w-0">
                    <span class="block truncate text-sm font-medium text-white">{{ $user->name }}</span>
                    <span class="block truncate text-xs text-periwinkle/80">{{ $user->major ?: $user->email }}</span>
                </span>
            </a>

            <div class="flex items-center gap-1">
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm transition hover:bg-white/10 hover:text-white">
                        <x-icon name="logout" />
                        Keluar
                    </button>
                </form>
                <x-theme-toggle class="hover:bg-white/10 hover:text-white" />
            </div>
        </div>
    </aside>

    {{-- Header (layar kecil) --}}
    <header class="flex items-center justify-between bg-ultramarine px-4 py-3 text-periwinkle lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-display text-lg text-custard">
            <span class="grid size-8 place-items-center rounded-lg bg-custard text-ultramarine"><x-icon name="tasks" class="size-4" /></span>
            Tugas Kampus
        </a>
        <div class="flex items-center gap-1">
            <x-theme-toggle class="hover:bg-white/10" />
            <a href="{{ route('profile.edit') }}" aria-label="Akun"><x-avatar :user="$user" class="size-8 text-sm" /></a>
        </div>
    </header>

    {{-- Isi halaman --}}
    <main class="lg:pl-64">
        <div class="mx-auto max-w-4xl px-4 py-8 pb-28 sm:px-8 lg:pb-10">
            @if (session('success'))
                <div role="status" class="mb-6 rounded-xl border border-custard bg-custard/40 px-4 py-3 text-sm font-medium text-ink dark:border-custard/30 dark:bg-custard/10 dark:text-custard">
                    {{ session('success') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    {{-- Menu bawah (layar kecil) --}}
    <nav class="fixed inset-x-0 bottom-0 z-20 flex bg-ultramarine pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Menu utama">
        @foreach ($menu as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
               class="flex flex-1 flex-col items-center gap-1 py-2.5 text-[10px] font-medium transition {{ $active ? 'text-custard' : 'text-periwinkle' }}">
                <x-icon :name="$item['icon']" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</body>
</html>
