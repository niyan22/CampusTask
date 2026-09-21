<x-layouts.guest title="Masuk">
    <h1 class="font-display text-2xl">Masuk</h1>
    <p class="mt-1 text-sm text-ink/70">Selamat datang kembali.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <x-field name="email" label="Email" type="email" placeholder="nama@kampus.ac.id" autocomplete="email" required autofocus />
        <x-field name="password" label="Password" type="password" autocomplete="current-password" required />

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="size-4 rounded border-line accent-amethyst">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="font-medium text-accent hover:underline">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-full">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-ink/70">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-accent hover:underline">Daftar</a>
    </p>
</x-layouts.guest>
