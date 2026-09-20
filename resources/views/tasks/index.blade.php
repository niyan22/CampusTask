<x-layouts.app title="Tugas">
    @php
        $tabs = ['all' => 'Semua', 'active' => 'Belum selesai', 'done' => 'Selesai', 'overdue' => 'Terlambat'];

        $priorityStyles = [
            'high' => 'bg-amethyst/10 text-amethyst',
            'medium' => 'bg-custard/60 text-ultramarine',
            'low' => 'bg-periwinkle/40 text-ultramarine',
        ];
    @endphp

    {{-- Judul halaman + tombol tambah --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl">Tugas</h1>
            <p class="mt-1 text-sm text-ultramarine/70">Tambah, ubah, dan centang tugas kuliahmu.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Tugas Baru
        </a>
    </div>

    {{-- Pencarian + filter --}}
    <div class="mt-8 space-y-4">
        <form method="GET" action="{{ route('tasks.index') }}" role="search">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul tugas atau mata kuliah…" aria-label="Cari tugas"
                   class="w-full rounded-xl border border-periwinkle bg-white px-4 py-2.5 text-sm placeholder:text-ultramarine/40 focus:border-amethyst focus:outline-2 focus:outline-amethyst/30">
        </form>

        <nav class="flex flex-wrap gap-2" aria-label="Filter tugas">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('tasks.index', ['filter' => $key, 'q' => $search]) }}" @if ($filter === $key) aria-current="page" @endif
                   class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ $filter === $key ? 'bg-ultramarine text-white' : 'bg-white ring-1 ring-periwinkle hover:bg-periwinkle/30' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Daftar tugas --}}
    <ul class="mt-4 space-y-3">
        @forelse ($tasks as $task)
            <li class="card flex items-start gap-3 p-4 transition hover:border-amethyst/40 hover:shadow">
                {{-- Tombol centang --}}
                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" aria-label="{{ $task->is_done ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                            class="mt-0.5 grid size-6 place-items-center rounded-full border-2 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amethyst {{ $task->is_done ? 'border-amethyst bg-amethyst text-white' : 'border-periwinkle text-transparent hover:border-amethyst hover:text-periwinkle' }}">
                        <x-icon name="check" class="size-3.5 stroke-3" />
                    </button>
                </form>

                {{-- Isi tugas --}}
                <div class="min-w-0 flex-1">
                    <p class="font-medium {{ $task->is_done ? 'text-ultramarine/40 line-through' : '' }}">{{ $task->title }}</p>
                    <p class="text-sm text-ultramarine/70">{{ $task->course }}</p>

                    @if ($task->description)
                        <p class="mt-1 line-clamp-2 text-sm text-ultramarine/70">{{ $task->description }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-medium">
                        <span class="rounded-full px-2.5 py-1 {{ $priorityStyles[$task->priority] ?? 'bg-periwinkle/40' }}">{{ $task->priorityLabel() }}</span>
                        <span class="rounded-full px-2.5 py-1 {{ $task->isOverdue() ? 'bg-rose-50 text-rose-700' : 'bg-honeydew text-ultramarine/80' }}">
                            {{ $task->due_date->translatedFormat('d M Y') }}
                            @unless ($task->is_done)
                                · {{ $task->deadlineLabel() }}
                            @endunless
                        </span>
                    </div>
                </div>

                {{-- Edit & hapus --}}
                <div class="flex shrink-0 gap-1">
                    <a href="{{ route('tasks.edit', $task) }}" aria-label="Edit {{ $task->title }}"
                       class="rounded-lg p-2 text-ultramarine/50 transition hover:bg-periwinkle/40 hover:text-amethyst">
                        <x-icon name="edit" />
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Hapus tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Hapus {{ $task->title }}"
                                class="rounded-lg p-2 text-ultramarine/50 transition hover:bg-rose-50 hover:text-rose-600">
                            <x-icon name="trash" />
                        </button>
                    </form>
                </div>
            </li>
        @empty
            <li class="card border-dashed px-6 py-14 text-center">
                <p class="font-medium">{{ $search || $filter !== 'all' ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}</p>
                <p class="mt-1 text-sm text-ultramarine/70">
                    {{ $search || $filter !== 'all' ? 'Coba ubah kata kunci atau filter.' : 'Tambahkan tugas pertamamu supaya tidak ada deadline yang terlewat.' }}
                </p>
                @if (! $search && $filter === 'all')
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary mt-5">Tambah Tugas</a>
                @endif
            </li>
        @endforelse
    </ul>
</x-layouts.app>
