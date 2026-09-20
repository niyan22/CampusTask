<x-layouts.app title="Dashboard">
    @php
        $cards = [
            ['label' => 'Total tugas', 'value' => $stats['total'], 'icon' => 'tasks', 'tile' => 'bg-periwinkle/50 text-ultramarine'],
            ['label' => 'Belum selesai', 'value' => $stats['active'], 'icon' => 'clock', 'tile' => 'bg-custard text-ultramarine'],
            ['label' => 'Selesai', 'value' => $stats['done'], 'icon' => 'check', 'tile' => 'bg-amethyst text-white'],
            ['label' => 'Terlambat', 'value' => $stats['overdue'], 'icon' => 'alert', 'tile' => 'bg-rose-100 text-rose-700'],
        ];
    @endphp

    <h1 class="font-display text-3xl">Halo, {{ Str::before(auth()->user()->name, ' ') }}</h1>
    <p class="mt-1 text-sm text-ultramarine/70">Ringkasan pengerjaan tugasmu · {{ now()->translatedFormat('l, d F Y') }}</p>

    {{-- Kartu statistik --}}
    <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($cards as $card)
            <div class="card p-4">
                <span class="grid size-9 place-items-center rounded-xl {{ $card['tile'] }}"><x-icon :name="$card['icon']" class="size-5" /></span>
                <dd class="mt-3 font-display text-3xl">{{ $card['value'] }}</dd>
                <dt class="text-xs font-medium text-ultramarine/70">{{ $card['label'] }}</dt>
            </div>
        @endforeach
    </dl>

    @if ($stats['total'] === 0)
        <div class="card mt-6 border-dashed px-6 py-14 text-center">
            <p class="font-medium">Belum ada tugas untuk dihitung</p>
            <p class="mt-1 text-sm text-ultramarine/70">Tambahkan tugas pertamamu dan statistiknya akan muncul di sini.</p>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary mt-5">Tambah Tugas</a>
        </div>
    @else
        {{-- Progres keseluruhan --}}
        <section class="card mt-6 flex flex-col items-center gap-6 p-6 sm:flex-row sm:gap-10">
            <div class="grid size-40 shrink-0 place-items-center rounded-full"
                 style="background: conic-gradient(var(--color-amethyst) {{ $stats['percent'] }}%, var(--color-periwinkle) 0)"
                 role="img" aria-label="{{ $stats['percent'] }} persen tugas selesai">
                <div class="grid size-28 place-items-center rounded-full bg-white text-center">
                    <div>
                        <p class="font-display text-3xl">{{ $stats['percent'] }}%</p>
                        <p class="text-xs text-ultramarine/70">selesai</p>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <h2 class="font-display text-xl">Progres keseluruhan</h2>
                <p class="mt-1 text-sm text-ultramarine/70">
                    {{ $stats['done'] }} dari {{ $stats['total'] }} tugas sudah kamu selesaikan.
                </p>
                <ul class="mt-4 space-y-2 text-sm">
                    <li class="flex items-center justify-between rounded-xl bg-honeydew px-3 py-2">
                        <span class="flex items-center gap-2"><span class="size-3 rounded-full bg-amethyst"></span>Selesai</span>
                        <span class="font-medium">{{ $stats['done'] }}</span>
                    </li>
                    <li class="flex items-center justify-between rounded-xl bg-honeydew px-3 py-2">
                        <span class="flex items-center gap-2"><span class="size-3 rounded-full bg-periwinkle"></span>Belum selesai</span>
                        <span class="font-medium">{{ $stats['active'] }}</span>
                    </li>
                </ul>
            </div>
        </section>

        {{-- Progres per mata kuliah --}}
        <section class="card mt-6 p-6">
            <h2 class="font-display text-xl">Progres per mata kuliah</h2>
            <ul class="mt-5 space-y-5">
                @foreach ($courses as $course => $count)
                    @php $percent = (int) round($count['done'] / $count['total'] * 100); @endphp
                    <li>
                        <div class="flex items-baseline justify-between gap-4 text-sm">
                            <span class="font-medium">{{ $course }}</span>
                            <span class="shrink-0 text-ultramarine/70">{{ $count['done'] }}/{{ $count['total'] }} selesai</span>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-periwinkle/40"
                             role="progressbar" aria-label="Progres {{ $course }}" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full bg-amethyst" style="width: {{ $percent }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.app>
