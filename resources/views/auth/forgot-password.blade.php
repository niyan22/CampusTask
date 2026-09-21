<x-layouts.guest title="Lupa Password">
    <h1 class="font-display text-2xl">Lupa password?</h1>
    <p class="mt-1 text-sm text-ink/70">Masukkan emailmu, nanti kami kirim link untuk membuat password baru.</p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <x-field name="email" label="Email" type="email" placeholder="nama@kampus.ac.id" autocomplete="email" required autofocus />

        <button type="submit" class="btn btn-primary w-full">Kirim link reset</button>
    </form>

    <p class="mt-6 text-center text-sm text-ink/70">
        <a href="{{ route('login') }}" class="font-medium text-accent hover:underline">&larr; Kembali ke halaman masuk</a>
    </p>
</x-layouts.guest>
