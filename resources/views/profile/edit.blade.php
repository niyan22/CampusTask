<x-layouts.app title="Akun">
    <h1 class="font-display text-3xl">Akun</h1>
    <p class="mt-1 text-sm text-ink/70">Kelola data dirimu dan keamanan akun.</p>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
        @csrf
        @method('PUT')

        {{-- Data diri --}}
        <section class="card p-5 sm:p-6">
            <div class="flex items-center gap-4">
                <x-avatar :user="$user" class="size-16 text-2xl" />
                <div class="min-w-0">
                    <h2 class="truncate font-display text-xl">{{ $user->name }}</h2>
                    <p class="truncate text-sm text-ink/70">{{ $user->email }}</p>
                </div>
            </div>

            {{-- Foto profil --}}
            <div class="mt-6">
                <label for="avatar" class="mb-1.5 block text-sm font-medium">Foto profil <span class="font-normal text-ink/60">(JPG, PNG, atau WEBP, maks. 2 MB)</span></label>
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                       @error('avatar') aria-invalid="true" @enderror
                       class="input file:mr-4 file:rounded-lg file:border-0 file:bg-soft file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-ink">
                @error('avatar')
                    <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror

                @if ($user->avatar)
                    <label class="mt-3 flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_avatar" value="1" class="size-4 rounded border-line accent-amethyst">
                        Hapus foto saat ini
                    </label>
                @endif
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <x-field name="name" label="Nama" :value="$user->name" autocomplete="name" required />
                <x-field name="email" label="Email" type="email" :value="$user->email" autocomplete="email" required />
                <x-field name="nim" label="NIM" :value="$user->nim" hint="(opsional)" placeholder="Contoh: 2310123456" />
                <x-field name="major" label="Jurusan" :value="$user->major" hint="(opsional)" placeholder="Contoh: Teknik Informatika" />
            </div>
        </section>

        {{-- Pengingat email --}}
        <section class="card p-5 sm:p-6">
            <h2 class="font-display text-xl">Pengingat</h2>

            <label class="mt-4 flex items-start gap-3">
                <input type="checkbox" name="remind_by_email" value="1" @checked($errors->any() ? old('remind_by_email') : $user->remind_by_email) class="mt-1 size-4 rounded border-line accent-amethyst">
                <span>
                    <span class="block text-sm font-medium">Kirim email pengingat</span>
                    <span class="block text-sm text-ink/70">Setiap pagi jam 07.00, kamu dapat email berisi tugas yang deadline-nya besok.</span>
                </span>
            </label>
        </section>

        {{-- Ganti password --}}
        <section class="card p-5 sm:p-6">
            <h2 class="font-display text-xl">Ganti password</h2>
            <p class="mt-1 text-sm text-ink/70">Kosongkan bagian ini kalau tidak ingin mengganti password.</p>

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

    <form method="POST" action="{{ route('logout') }}" class="mt-10 border-t border-line pt-6">
        @csrf
        <button type="submit" class="btn btn-ghost">
            <x-icon name="logout" class="size-4" />
            Keluar dari akun
        </button>
    </form>
</x-layouts.app>
