<x-layouts.app title="Akun">
    <h1 class="font-display text-3xl">Akun</h1>
    <p class="mt-1 text-sm text-ultramarine/70">Kelola data dirimu dan keamanan akun.</p>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('PUT')

        {{-- Data diri --}}
        <section class="card p-5 sm:p-6">
            <div class="flex items-center gap-4">
                <span class="grid size-14 shrink-0 place-items-center rounded-full bg-ultramarine font-display text-2xl text-custard">{{ $user->initial() }}</span>
                <div class="min-w-0">
                    <h2 class="truncate font-display text-xl">{{ $user->name }}</h2>
                    <p class="truncate text-sm text-ultramarine/70">{{ $user->email }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <x-field name="name" label="Nama" :value="$user->name" autocomplete="name" required />
                <x-field name="email" label="Email" type="email" :value="$user->email" autocomplete="email" required />
                <x-field name="nim" label="NIM" :value="$user->nim" hint="(opsional)" placeholder="Contoh: 2310123456" />
                <x-field name="major" label="Jurusan" :value="$user->major" hint="(opsional)" placeholder="Contoh: Teknik Informatika" />
            </div>
        </section>

        {{-- Ganti password --}}
        <section class="card p-5 sm:p-6">
            <h2 class="font-display text-xl">Ganti password</h2>
            <p class="mt-1 text-sm text-ultramarine/70">Kosongkan bagian ini kalau tidak ingin mengganti password.</p>

            <div class="mt-6 space-y-5">
                <x-field name="current_password" label="Password saat ini" type="password" autocomplete="current-password" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="password" label="Password baru" type="password" hint="(minimal 8 karakter)" autocomplete="new-password" />
                    <x-field name="password_confirmation" label="Ulangi password baru" type="password" autocomplete="new-password" />
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary px-6">Simpan Perubahan</button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-10 border-t border-periwinkle/60 pt-6">
        @csrf
        <button type="submit" class="btn btn-ghost">
            <x-icon name="logout" class="size-4" />
            Keluar dari akun
        </button>
    </form>
</x-layouts.app>
