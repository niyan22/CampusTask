<x-layouts.guest title="Daftar">
    <h1 class="font-display text-2xl">Buat akun</h1>
    <p class="mt-1 text-sm text-ink/70">Gratis dan cuma butuh satu menit.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <x-field name="name" label="Nama" placeholder="Nama lengkap" autocomplete="name" required autofocus />
        <x-field name="email" label="Email" type="email" placeholder="nama@kampus.ac.id" autocomplete="email" required />
        <x-field name="password" label="Password" type="password" hint="(minimal 8 karakter)" autocomplete="new-password" required />
        <x-field name="password_confirmation" label="Ulangi password" type="password" autocomplete="new-password" required />

        <button type="submit" class="btn btn-primary w-full">Daftar</button>
    </form>

    <p class="mt-6 text-center text-sm text-ink/70">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-medium text-accent hover:underline">Masuk</a>
    </p>
</x-layouts.guest>
