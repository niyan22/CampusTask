<x-layouts.app title="Dashboard">
    @php
        $cards = [
            ['label' => 'Total tugas', 'value' => $stats['total'], 'icon' => 'tasks', 'tile' => 'bg-soft text-ink'],
            ['label' => 'Belum selesai', 'value' => $stats['active'], 'icon' => 'clock', 'tile' => 'bg-custard text-ultramarine'],
            ['label' => 'Selesai', 'value' => $stats['done'], 'icon' => 'check', 'tile' => 'bg-amethyst text-white'],
            ['label' => 'Terlambat', 'value' => $stats['overdue'], 'icon' => 'alert', 'tile' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300'],
        ];

        $weeklyMax = max(1, $weekly->max('count'));
    @endphp

    <h1 class="font-display text-3xl">Halo, {{ Str::before(auth()->user()->name, ' ') }}</h1>
    <p class="mt-1 text-sm text-ink/70">Ringkasan pengerjaan tugasmu · {{ now()->translatedFormat('l, d F Y') }}</p>

    {{-- Kartu statistik --}}
    <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($cards as $card)
            <div class="card p-4">
                <span class="grid size-9 place-items-center rounded-xl {{ $card['tile'] }}"><x-icon :name="$card['icon']" class="size-5" /></span>
                <dd class="mt-3 font-display text-3xl">{{ $card['value'] }}</dd>
                <dt class="text-xs font-medium text-ink/70">{{ $card['label'] }}</dt>
            </div>
        @endforeach
    </dl>

    @if ($stats['total'] === 0)
        <div class="card mt-6 border-dashed px-6 py-14 text-center">
            <p class="font-medium">Belum ada tugas untuk dihitung</p>
            <p class="mt-1 text-sm text-ink/70">Tambahkan tugas pertamamu dan statistiknya akan muncul di sini.</p>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary mt-5">Tambah Tugas</a>
        </div>
    @else
        {{-- Progres keseluruhan --}}
        <section class="card mt-6 flex flex-col items-center gap-6 p-6 sm:flex-row sm:gap-10">
            <div class="grid size-40 shrink-0 place-items-center rounded-full"
                 style="background: conic-gradient(var(--color-amethyst) {{ $stats['percent'] }}%, var(--color-soft) 0)"
                 role="img" aria-label="{{ $stats['percent'] }} persen tugas selesai">
                <div class="grid size-28 place-items-center rounded-full bg-surface text-center">
                    <div>
                        <p class="font-display text-3xl">{{ $stats['percent'] }}%</p>
                        <p class="text-xs text-ink/70">selesai</p>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <h2 class="font-display text-xl">Progres keseluruhan</h2>
                <p class="mt-1 text-sm text-ink/70">{{ $stats['done'] }} dari {{ $stats['total'] }} tugas sudah kamu selesaikan.</p>
                <ul class="mt-4 space-y-2 text-sm">
                    <li class="flex items-center justify-between rounded-xl bg-page px-3 py-2">
                        <span class="flex items-center gap-2"><span class="size-3 rounded-full bg-amethyst"></span>Selesai</span>
                        <span class="font-medium">{{ $stats['done'] }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-xl bg-page px-3 py-2">
                        <span class="flex items-center gap-2"><span class="size-3 rounded-full bg-line"></span>Belum selesai</span>
                        <span class="font-medium">{{ $stats['active'] }}</span>
                    </li>
                </ul>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- Deadline terdekat --}}
            <section class="card p-6">
                <h2 class="font-display text-xl">Deadline terdekat</h2>

                <ul class="mt-4 space-y-1">
                    @forelse ($upcoming as $task)
                        <li>
                            <a href="{{ route('tasks.edit', $task) }}" class="-mx-2 flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-soft">
                                <x-course-dot :color="$task->courseColor()" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium">{{ $task->title }}</span>
                                    <span class="block truncate text-xs text-ink/70">{{ $task->courseName() }}</span>
                                </span>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $task->isOverdue() ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' : 'bg-soft' }}">
                                    {{ $task->deadlineLabel() }}
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-ink/70">Semua tugas sudah selesai. Mantap!</li>
                    @endforelse
                </ul>
            </section>

            {{-- Grafik selesai per minggu --}}
            <section class="card p-6">
                <h2 class="font-display text-xl">Tugas selesai per minggu</h2>
                <p class="mt-1 text-xs text-ink/70">6 minggu terakhir (label = hari Senin tiap minggu)</p>

                <div class="mt-5 flex h-40 items-end gap-3" role="img"
                     aria-label="Grafik tugas selesai per minggu: {{ $weekly->map(fn ($week) => $week['label'].' '.$week['count'])->implode(', ') }}">
                    @foreach ($weekly as $week)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-medium">{{ $week['count'] }}</span>
                            <div class="w-full rounded-t-lg {{ $loop->last ? 'bg-amethyst' : 'bg-periwinkle dark:bg-[#5a45a8]' }}"
                                 style="height: {{ max(4, $week['count'] / $weeklyMax * 100) }}%"></div>
                            <span class="text-[10px] text-ink/70">{{ $week['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Progres per mata kuliah --}}
        <section class="card mt-6 p-6">
            <h2 class="font-display text-xl">Progres per mata kuliah</h2>
            <ul class="mt-5 space-y-5">
                @foreach ($courses as $name => $count)
                    @php $percent = (int) round($count['done'] / $count['total'] * 100); @endphp
                    <li>
                        <div class="flex items-baseline justify-between gap-4 text-sm">
                            <span class="flex items-center gap-2 font-medium"><x-course-dot :color="$count['color']" />{{ $name }}</span>
                            <span class="shrink-0 text-ink/70">{{ $count['done'] }}/{{ $count['total'] }} selesai</span>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-soft"
                             role="progressbar" aria-label="Progres {{ $name }}" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full" style="width: {{ $percent }}%; background-color: {{ $count['color'] }}"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.app>
