@props(['title' => 'Masuk'])

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
<body class="min-h-screen bg-ultramarine bg-aurora font-sans text-ink antialiased">
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-custard text-ultramarine"><x-icon name="tasks" class="size-7" /></span>
                <p class="mt-4 font-display text-3xl text-custard">Tugas Kampus</p>
                <p class="mt-1 text-sm text-periwinkle">Atur tugas kuliahmu, tanpa ada deadline yang terlewat.</p>
            </div>

            <div class="rounded-3xl bg-surface p-6 shadow-2xl shadow-black/20 sm:p-8">
                @if (session('status'))
                    <div role="status" class="mb-5 rounded-xl border border-custard bg-custard/40 px-4 py-3 text-sm font-medium text-ink dark:border-custard/30 dark:bg-custard/10 dark:text-custard">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </main>
</body>
</html>
