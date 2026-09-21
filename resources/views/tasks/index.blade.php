<x-layouts.app title="Tugas">
    @php
        $tabs = ['all' => 'Semua', 'active' => 'Belum selesai', 'done' => 'Selesai', 'overdue' => 'Terlambat'];
        $sorts = ['deadline' => 'Deadline terdekat', 'priority' => 'Prioritas tertinggi', 'newest' => 'Terbaru ditambahkan'];

        $priorityStyles = [
            'high' => 'bg-amethyst/10 text-accent',
            'medium' => 'bg-custard/60 text-ink dark:bg-custard/20 dark:text-custard',
            'low' => 'bg-soft text-ink',
        ];
    @endphp

    {{-- Judul halaman + tombol tambah --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl">Tugas</h1>
            <p class="mt-1 text-sm text-ink/70">Tambah, ubah, dan centang tugas kuliahmu.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Tugas Baru
        </a>
    </div>

    @error('subtask')
        <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</div>
    @enderror

    {{-- Pencarian, urutan, dan filter --}}
    <div class="mt-8 space-y-4">
        <form method="GET" action="{{ route('tasks.index') }}" role="search" class="flex flex-col gap-3 sm:flex-row">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul tugas atau mata kuliah…" aria-label="Cari tugas" class="input flex-1">
            <select name="sort" aria-label="Urutkan tugas" onchange="this.form.submit()" class="input sm:w-56">
                @foreach ($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        <nav class="flex flex-wrap gap-2" aria-label="Filter tugas">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('tasks.index', ['filter' => $key, 'q' => $search, 'sort' => $sort]) }}" @if ($filter === $key) aria-current="page" @endif
                   class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ $filter === $key ? 'bg-ultramarine text-white dark:bg-amethyst' : 'bg-surface ring-1 ring-line hover:bg-soft' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Daftar tugas --}}
    <ul class="mt-4 space-y-3">
        @forelse ($tasks as $task)
            @php
                $stepsDone = $task->subtasks->where('is_done', true)->count();
                $stepsTotal = $task->subtasks->count();
            @endphp

            <li class="card flex items-start gap-3 p-4 transition hover:border-amethyst/40 hover:shadow">
                {{-- Tombol centang --}}
                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" aria-label="{{ $task->is_done ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                            class="mt-0.5 grid size-6 place-items-center rounded-full border-2 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amethyst {{ $task->is_done ? 'border-amethyst bg-amethyst text-white' : 'border-line text-transparent hover:border-amethyst hover:text-line' }}">
                        <x-icon name="check" class="size-3.5 stroke-3" />
                    </button>
                </form>

                {{-- Isi tugas --}}
                <div class="min-w-0 flex-1">
                    <p class="font-medium {{ $task->is_done ? 'text-ink/40 line-through' : '' }}">{{ $task->title }}</p>
                    <p class="mt-0.5 flex items-center gap-2 text-sm text-ink/70">
                        <x-course-dot :color="$task->courseColor()" />
                        <span class="truncate">{{ $task->courseName() }}</span>
                    </p>

                    @if ($task->description)
                        <p class="mt-1 line-clamp-2 text-sm text-ink/70">{{ $task->description }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-medium">
                        <span class="rounded-full px-2.5 py-1 {{ $priorityStyles[$task->priority] ?? 'bg-soft' }}">{{ $task->priorityLabel() }}</span>
                        <span class="rounded-full px-2.5 py-1 {{ $task->isOverdue() ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' : 'bg-page text-ink/80' }}">
                            {{ $task->due_date->translatedFormat('d M Y') }}
                            @unless ($task->is_done)
                                · {{ $task->deadlineLabel() }}
                            @endunless
                        </span>

                        @if ($task->source)
                            <span class="inline-flex items-center gap-1 rounded-full bg-soft px-2.5 py-1">
                                <x-icon name="users" class="size-3.5" /> Dari {{ $task->source->user->name }}
                            </span>
                        @elseif ($task->copies_count > 0)
                            <a href="{{ route('tasks.share', $task) }}" class="inline-flex items-center gap-1 rounded-full bg-soft px-2.5 py-1 hover:text-accent">
                                <x-icon name="users" class="size-3.5" /> Dibagikan ke {{ $task->copies_count }} teman
                            </a>
                        @endif
                    </div>

                    {{-- Langkah-langkah (checklist), dibuka-tutup tanpa JavaScript --}}
                    <details class="mt-3" @if (session('open_task') === $task->id) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-2 text-xs font-medium text-ink/70 hover:text-accent">
                            <x-icon name="list" class="size-4" />
                            {{ $stepsTotal > 0 ? "Langkah {$stepsDone}/{$stepsTotal}" : 'Tambah langkah' }}
                            @if ($stepsTotal > 0)
                                <span class="h-1.5 w-16 overflow-hidden rounded-full bg-soft" aria-hidden="true">
                                    <span class="block h-full rounded-full bg-amethyst" style="width: {{ $stepsDone / $stepsTotal * 100 }}%"></span>
                                </span>
                            @endif
                        </summary>

                        <ul class="mt-3 space-y-2">
                            @foreach ($task->subtasks as $subtask)
                                <li class="flex items-center gap-2 text-sm">
                                    <form method="POST" action="{{ route('subtasks.toggle', [$task, $subtask]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" aria-label="{{ $subtask->is_done ? 'Tandai langkah belum selesai' : 'Tandai langkah selesai' }}"
                                                class="grid size-5 place-items-center rounded-md border transition {{ $subtask->is_done ? 'border-amethyst bg-amethyst text-white' : 'border-line text-transparent hover:border-amethyst' }}">
                                            <x-icon name="check" class="size-3 stroke-3" />
                                        </button>
                                    </form>
                                    <span class="flex-1 {{ $subtask->is_done ? 'text-ink/40 line-through' : '' }}">{{ $subtask->title }}</span>
                                    <form method="POST" action="{{ route('subtasks.destroy', [$task, $subtask]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Hapus langkah {{ $subtask->title }}" class="rounded p-1 text-ink/40 transition hover:text-rose-600">
                                            <x-icon name="trash" class="size-4" />
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('subtasks.store', $task) }}" class="mt-3 flex gap-2">
                            @csrf
                            <input type="text" name="subtask" placeholder="Tulis langkah baru…" aria-label="Langkah baru" maxlength="255" required class="input py-1.5">
                            <button type="submit" class="btn btn-ghost px-3 py-1.5 ring-1 ring-line">Tambah</button>
                        </form>
                    </details>
                </div>

                {{-- Bagikan, edit & hapus --}}
                <div class="flex shrink-0 gap-1">
                    @unless ($task->source_task_id)
                        <a href="{{ route('tasks.share', $task) }}" aria-label="Bagikan {{ $task->title }} ke teman"
                           class="rounded-lg p-2 text-ink/50 transition hover:bg-soft hover:text-accent">
                            <x-icon name="users" />
                        </a>
                    @endunless
                    <a href="{{ route('tasks.edit', $task) }}" aria-label="Edit {{ $task->title }}"
                       class="rounded-lg p-2 text-ink/50 transition hover:bg-soft hover:text-accent">
                        <x-icon name="edit" />
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Hapus tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Hapus {{ $task->title }}"
                                class="rounded-lg p-2 text-ink/50 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10">
                            <x-icon name="trash" />
                        </button>
                    </form>
                </div>
            </li>
        @empty
            <li class="card border-dashed px-6 py-14 text-center">
                <p class="font-medium">{{ $search || $filter !== 'all' ? 'Tidak ada tugas yang cocok' : 'Belum ada tugas' }}</p>
                <p class="mt-1 text-sm text-ink/70">
                    {{ $search || $filter !== 'all' ? 'Coba ubah kata kunci atau filter.' : 'Tambahkan tugas pertamamu supaya tidak ada deadline yang terlewat.' }}
                </p>
                @if (! $search && $filter === 'all')
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary mt-5">Tambah Tugas</a>
                @endif
            </li>
        @endforelse
    </ul>

    <x-pager :paginator="$tasks" />
</x-layouts.app>
