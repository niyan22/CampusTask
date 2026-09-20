<x-layouts.guest title="Masuk">
    <h1 class="font-display text-2xl">Masuk</h1>
    <p class="mt-1 text-sm text-ultramarine/70">Selamat datang kembali.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <x-field name="email" label="Email" type="email" placeholder="nama@kampus.ac.id" autocomplete="email" required autofocus />
        <x-field name="password" label="Password" type="password" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="size-4 rounded border-periwinkle accent-amethyst">
            Ingat saya
        </label>

        <button type="submit" class="btn btn-primary w-full">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-ultramarine/70">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-amethyst hover:underline">Daftar</a>
    </p>
</x-layouts.guest>
