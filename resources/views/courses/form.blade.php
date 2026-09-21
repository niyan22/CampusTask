{{-- Dipakai untuk tambah dan edit mata kuliah. --}}
<x-layouts.app :title="$course->exists ? 'Edit Mata Kuliah' : 'Mata Kuliah Baru'">
    <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/70 transition hover:text-accent">
        <x-icon name="chevron-left" class="size-4" /> Kembali
    </a>

    <h1 class="mt-3 font-display text-3xl">{{ $course->exists ? 'Edit Mata Kuliah' : 'Mata Kuliah Baru' }}</h1>

    <form method="POST" action="{{ $course->exists ? route('courses.update', $course) : route('courses.store') }}"
          class="card mt-6 space-y-5 p-5 sm:p-6">
        @csrf
        @if ($course->exists)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama mata kuliah" :value="$course->name" placeholder="Contoh: Basis Data" required autofocus />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="lecturer" label="Dosen" :value="$course->lecturer" hint="(opsional)" placeholder="Nama dosen pengampu" />
            <x-field name="credits" label="SKS" type="number" :value="$course->credits" hint="(opsional)" placeholder="3" min="1" max="8" />
        </div>

        {{-- Pilihan warna --}}
        <fieldset>
            <legend class="mb-1.5 block text-sm font-medium">Warna</legend>
            <div class="flex flex-wrap gap-3">
                @foreach (App\Models\Course::COLORS as $color)
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked(old('color', $course->color) === $color)>
                        <span class="block size-9 rounded-full ring-2 ring-transparent ring-offset-2 ring-offset-surface transition peer-checked:ring-ink peer-focus-visible:ring-amethyst"
                              style="background-color: {{ $color }}" title="{{ $color }}"></span>
                        <span class="sr-only">Warna {{ $color }}</span>
                    </label>
                @endforeach
            </div>
            @error('color')
                <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('courses.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary px-5">{{ $course->exists ? 'Simpan Perubahan' : 'Simpan Mata Kuliah' }}</button>
        </div>
    </form>
</x-layouts.app>
