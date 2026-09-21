<x-layouts.app title="Jadwal Kuliah">
    <h1 class="font-display text-3xl">Jadwal Kuliah</h1>
    <p class="mt-1 text-sm text-ink/70">Jadwal mingguanmu, dari Senin sampai Minggu.</p>

    {{-- Form tambah jadwal --}}
    @if ($courses->isEmpty())
        <div class="card mt-8 border-dashed px-6 py-10 text-center">
            <p class="font-medium">Tambahkan mata kuliah dulu</p>
            <p class="mt-1 text-sm text-ink/70">Jadwal dibuat dari mata kuliah yang sudah kamu tambahkan.</p>
            <a href="{{ route('courses.create') }}" class="btn btn-primary mt-5">Tambah Mata Kuliah</a>
        </div>
    @else
        <form method="POST" action="{{ route('schedule.store') }}" class="card mt-8 p-5 sm:p-6">
            @csrf
            <h2 class="font-display text-xl">Tambah jadwal</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="course_id" class="mb-1.5 block text-sm font-medium">Mata kuliah</label>
                    <select id="course_id" name="course_id" required @error('course_id') aria-invalid="true" @enderror class="input">
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="day" class="mb-1.5 block text-sm font-medium">Hari</label>
                    <select id="day" name="day" required @error('day') aria-invalid="true" @enderror class="input">
                        @foreach (App\Models\Schedule::DAYS as $number => $name)
                            <option value="{{ $number }}" @selected((string) old('day', now()->dayOfWeekIso) === (string) $number)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('day')
                        <p class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <x-field name="room" label="Ruang" hint="(opsional)" placeholder="Contoh: R. 301" />
                <x-field name="start_time" label="Jam mulai" type="time" value="08:00" required />
                <x-field name="end_time" label="Jam selesai" type="time" value="09:40" required />
            </div>

            <button type="submit" class="btn btn-primary mt-5">
                <x-icon name="plus" class="size-4" />
                Tambah Jadwal
            </button>
        </form>
    @endif

    {{-- Jadwal per hari --}}
    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach (App\Models\Schedule::DAYS as $number => $dayName)
            @php $isToday = now()->dayOfWeekIso === $number; @endphp

            <section class="card p-4 {{ $isToday ? 'border-amethyst ring-1 ring-amethyst' : '' }}" aria-label="Jadwal hari {{ $dayName }}">
                <h2 class="flex items-center justify-between font-display text-lg">
                    {{ $dayName }}
                    @if ($isToday)
                        <span class="rounded-full bg-amethyst px-2.5 py-0.5 font-sans text-xs font-medium text-white">Hari ini</span>
                    @endif
                </h2>

                <ul class="mt-3 space-y-2">
                    @forelse ($schedules[$number] ?? [] as $schedule)
                        <li class="flex items-start gap-3 rounded-xl bg-page px-3 py-2" style="border-left: 4px solid {{ $schedule->course->color }}">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ $schedule->course->name }}</p>
                                <p class="text-xs text-ink/70">{{ $schedule->timeRange() }}@if ($schedule->room) · {{ $schedule->room }} @endif</p>
                            </div>
                            <form method="POST" action="{{ route('schedule.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Hapus jadwal {{ $schedule->course->name }}" class="rounded p-1 text-ink/40 transition hover:text-rose-600">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="py-3 text-center text-sm text-ink/50">Tidak ada kuliah</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.app>
