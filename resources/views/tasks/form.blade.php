{{-- Dipakai untuk tambah (tugas baru) dan edit (tugas yang sudah ada). --}}
<x-layouts.app :title="$task->exists ? 'Edit Tugas' : 'Tugas Baru'">
    <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ultramarine/70 transition hover:text-amethyst">
        <x-icon name="chevron-left" class="size-4" /> Kembali
    </a>

    <h1 class="mt-3 font-display text-3xl">{{ $task->exists ? 'Edit Tugas' : 'Tugas Baru' }}</h1>

    <form method="POST" action="{{ $task->exists ? route('tasks.update', $task) : route('tasks.store') }}"
          class="card mt-6 space-y-5 p-5 sm:p-6">
        @csrf
        @if ($task->exists)
            @method('PUT')
        @endif

        <x-field name="title" label="Judul tugas" :value="$task->title" placeholder="Contoh: Laporan praktikum Basis Data" required autofocus />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="course" label="Mata kuliah" :value="$task->course" placeholder="Contoh: Basis Data" required />
            <x-field name="due_date" label="Deadline" type="date" :value="$task->due_date?->format('Y-m-d') ?? today()->addDay()->format('Y-m-d')" required />
        </div>

        <fieldset>
            <legend class="mb-1.5 block text-sm font-medium">Prioritas</legend>
            <div class="grid grid-cols-3 gap-3">
                @foreach (App\Models\Task::PRIORITIES as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="priority" value="{{ $value }}" class="peer sr-only" @checked(old('priority', $task->priority) === $value)>
                        <span class="block rounded-xl border border-periwinkle px-4 py-2.5 text-center text-sm font-medium text-ultramarine/70 transition hover:bg-periwinkle/20 peer-checked:border-amethyst peer-checked:bg-amethyst/10 peer-checked:text-amethyst peer-focus-visible:outline-2 peer-focus-visible:outline-amethyst">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('priority')
                <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium">Catatan <span class="font-normal text-ultramarine/60">(opsional)</span></label>
            <textarea id="description" name="description" rows="4" placeholder="Detail tugas, link referensi, dll."
                      @error('description') aria-invalid="true" @enderror
                      class="w-full rounded-xl border border-periwinkle bg-white px-4 py-2.5 text-sm placeholder:text-ultramarine/40 focus:border-amethyst focus:outline-2 focus:outline-amethyst/30 aria-invalid:border-rose-400">{{ old('description', $task->description) }}</textarea>
            @error('description')
                <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary px-5">{{ $task->exists ? 'Simpan Perubahan' : 'Simpan Tugas' }}</button>
        </div>
    </form>
</x-layouts.app>
