<x-layouts.guest title="Reset Password">
    <h1 class="font-display text-2xl">Buat password baru</h1>
    <p class="mt-1 text-sm text-ink/70">Pilih password baru untuk akunmu.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-field name="email" label="Email" type="email" :value="$email" autocomplete="email" required />
        <x-field name="password" label="Password baru" type="password" hint="(minimal 8 karakter)" autocomplete="new-password" required autofocus />
        <x-field name="password_confirmation" label="Ulangi password baru" type="password" autocomplete="new-password" required />

        <button type="submit" class="btn btn-primary w-full">Simpan password</button>
    </form>
</x-layouts.guest>
