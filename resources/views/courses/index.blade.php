<x-layouts.app title="Mata Kuliah">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl">Mata Kuliah</h1>
            <p class="mt-1 text-sm text-ink/70">Atur mata kuliahmu. Warnanya dipakai di tugas, kalender, dan jadwal.</p>
        </div>
        <a href="{{ route('courses.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Mata Kuliah Baru
        </a>
    </div>

    <ul class="mt-8 grid gap-3 sm:grid-cols-2">
        @forelse ($courses as $course)
            <li class="card flex items-start gap-3 overflow-hidden p-4" style="border-left: 4px solid {{ $course->color }}">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ $course->name }}</p>
                    <p class="mt-0.5 truncate text-sm text-ink/70">
                        {{ $course->lecturer ?: 'Dosen belum diisi' }}@if ($course->credits) · {{ $course->credits }} SKS @endif
                    </p>
                    <p class="mt-3 text-xs font-medium text-ink/70">
                        {{ $course->pending_tasks_count }} tugas belum selesai · {{ $course->schedules_count }} jadwal
                    </p>
                </div>

                <div class="flex shrink-0 gap-1">
                    <a href="{{ route('courses.edit', $course) }}" aria-label="Edit {{ $course->name }}"
                       class="rounded-lg p-2 text-ink/50 transition hover:bg-soft hover:text-accent">
                        <x-icon name="edit" />
                    </a>
                    <form method="POST" action="{{ route('courses.destroy', $course) }}"
                          onsubmit="return confirm('Hapus mata kuliah ini? Tugasnya tetap ada, tapi jadwalnya ikut terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Hapus {{ $course->name }}"
                                class="rounded-lg p-2 text-ink/50 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10">
                            <x-icon name="trash" />
                        </button>
                    </form>
                </div>
            </li>
        @empty
            <li class="card border-dashed px-6 py-14 text-center sm:col-span-2">
                <p class="font-medium">Belum ada mata kuliah</p>
                <p class="mt-1 text-sm text-ink/70">Tambahkan mata kuliahmu supaya tugas dan jadwal lebih rapi.</p>
                <a href="{{ route('courses.create') }}" class="btn btn-primary mt-5">Tambah Mata Kuliah</a>
            </li>
        @endforelse
    </ul>
</x-layouts.app>
