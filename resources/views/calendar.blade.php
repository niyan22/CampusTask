<x-layouts.app title="Kalender">
    @php
        $weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $previous = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');

        // Tugas yang deadline-nya jatuh di bulan yang sedang dilihat (untuk daftar di bawah kalender).
        $monthTasks = $tasksByDate->collapse()->filter(fn ($task) => $task->due_date->isSameMonth($month))->sortBy('due_date');
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl">Kalender</h1>
            <p class="mt-1 text-sm text-ultramarine/70">Lihat semua deadline dalam satu pandangan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('calendar', ['month' => $previous]) }}" aria-label="Bulan sebelumnya" class="btn btn-ghost bg-white px-2.5 ring-1 ring-periwinkle">
                <x-icon name="chevron-left" />
            </a>
            <a href="{{ route('calendar') }}" class="btn btn-ghost bg-white ring-1 ring-periwinkle">Hari ini</a>
            <a href="{{ route('calendar', ['month' => $next]) }}" aria-label="Bulan berikutnya" class="btn btn-ghost bg-white px-2.5 ring-1 ring-periwinkle">
                <x-icon name="chevron-right" />
            </a>
        </div>
    </div>

    {{-- Kalender bulanan --}}
    <section class="card mt-6 overflow-hidden" aria-label="Kalender {{ $month->translatedFormat('F Y') }}">
        <h2 class="border-b border-periwinkle/60 px-4 py-3 text-center font-display text-xl sm:text-left">{{ $month->translatedFormat('F Y') }}</h2>

        <div class="grid grid-cols-7 border-b border-periwinkle/60 bg-honeydew text-center text-xs font-medium text-ultramarine/70">
            @foreach ($weekdays as $weekday)
                <div class="py-2">{{ $weekday }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach ($days as $day)
                @php
                    $dayTasks = $tasksByDate[$day->toDateString()] ?? collect();
                    $inMonth = $day->isSameMonth($month);
                @endphp
                <div class="min-h-16 border-b border-r border-periwinkle/40 p-1 sm:min-h-28 sm:p-2 {{ $inMonth ? '' : 'bg-honeydew/60' }}">
                    <span class="grid size-6 place-items-center rounded-full text-xs font-medium sm:size-7 sm:text-sm
                        {{ $day->isToday() ? 'bg-amethyst text-white' : ($inMonth ? '' : 'text-ultramarine/40') }}">
                        {{ $day->day }}
                    </span>

                    {{-- Layar besar: judul tugas. Layar kecil: titik penanda. --}}
                    <ul class="mt-1 hidden space-y-1 sm:block">
                        @foreach ($dayTasks->take(2) as $task)
                            <li>
                                <a href="{{ route('tasks.edit', $task) }}" title="{{ $task->title }}"
                                   class="block truncate rounded-md px-1.5 py-0.5 text-[11px] font-medium {{ $task->is_done ? 'bg-periwinkle/40 text-ultramarine/50 line-through' : ($task->isOverdue() ? 'bg-rose-50 text-rose-700' : 'bg-amethyst/10 text-amethyst') }}">
                                    {{ $task->title }}
                                </a>
                            </li>
                        @endforeach
                        @if ($dayTasks->count() > 2)
                            <li class="px-1.5 text-[11px] text-ultramarine/60">+{{ $dayTasks->count() - 2 }} lagi</li>
                        @endif
                    </ul>
                    @if ($dayTasks->isNotEmpty())
                        <div class="mt-1 flex justify-center gap-0.5 sm:hidden" aria-hidden="true">
                            @foreach ($dayTasks->take(3) as $task)
                                <span class="size-1.5 rounded-full {{ $task->is_done ? 'bg-periwinkle' : 'bg-amethyst' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Daftar deadline bulan ini --}}
    <section class="mt-8">
        <h2 class="font-display text-xl">Deadline bulan ini</h2>

        <ul class="mt-4 space-y-3">
            @forelse ($monthTasks as $task)
                <li>
                    <a href="{{ route('tasks.edit', $task) }}" class="card flex items-center gap-4 p-3 transition hover:border-amethyst/40 hover:shadow">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-honeydew text-center leading-tight">
                            <span>
                                <span class="block font-display text-lg">{{ $task->due_date->day }}</span>
                                <span class="block text-[10px] uppercase text-ultramarine/60">{{ $task->due_date->translatedFormat('M') }}</span>
                            </span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium {{ $task->is_done ? 'text-ultramarine/40 line-through' : '' }}">{{ $task->title }}</span>
                            <span class="block truncate text-sm text-ultramarine/70">{{ $task->course }}</span>
                        </span>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $task->is_done ? 'bg-periwinkle/40' : ($task->isOverdue() ? 'bg-rose-50 text-rose-700' : 'bg-custard/60') }}">
                            {{ $task->is_done ? 'Selesai' : $task->deadlineLabel() }}
                        </span>
                    </a>
                </li>
            @empty
                <li class="card border-dashed px-6 py-10 text-center text-sm text-ultramarine/70">Tidak ada deadline di bulan ini.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.app>
