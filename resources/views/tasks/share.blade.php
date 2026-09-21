<x-layouts.app title="Bagikan Tugas">
    <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/70 transition hover:text-accent">
        <x-icon name="chevron-left" class="size-4" /> Kembali
    </a>

    <h1 class="mt-3 font-display text-3xl">Bagikan tugas</h1>
    <p class="mt-1 text-sm text-ink/70">Teman menerima salinan tugas ini di daftarnya sendiri, jadi kalian bisa saling melihat siapa yang sudah selesai.</p>

    {{-- Tugas yang dibagikan --}}
    <div class="card mt-6 p-4">
        <p class="font-medium">{{ $task->title }}</p>
        <p class="mt-0.5 flex items-center gap-2 text-sm text-ink/70">
            <x-course-dot :color="$task->courseColor()" />
            {{ $task->courseName() }} · Deadline {{ $task->due_date->translatedFormat('d M Y') }}
        </p>
    </div>

    {{-- Form bagikan --}}
    <form method="POST" action="{{ route('tasks.share.store', $task) }}" class="card mt-6 p-5 sm:p-6">
        @csrf
        <x-field name="email" label="Email teman" type="email" placeholder="teman@kampus.ac.id" hint="(harus sudah punya akun)" required />
        <button type="submit" class="btn btn-primary mt-4">
            <x-icon name="users" class="size-4" />
            Bagikan
        </button>
    </form>

    {{-- Status tiap teman --}}
    <section class="mt-8">
        <h2 class="font-display text-xl">Status teman</h2>

        <ul class="mt-4 space-y-3">
            <li class="card flex items-center gap-3 p-3">
                <x-avatar :user="auth()->user()" class="size-10" />
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-medium">{{ auth()->user()->name }} <span class="text-xs font-normal text-ink/60">(kamu)</span></span>
                </span>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $task->is_done ? 'bg-amethyst text-white' : 'bg-soft' }}">
                    {{ $task->is_done ? 'Selesai' : 'Belum selesai' }}
                </span>
            </li>

            @forelse ($copies as $copy)
                <li class="card flex items-center gap-3 p-3">
                    <x-avatar :user="$copy->user" class="size-10" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium">{{ $copy->user->name }}</span>
                        <span class="block truncate text-xs text-ink/70">{{ $copy->user->major ?: $copy->user->email }}</span>
                    </span>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $copy->is_done ? 'bg-amethyst text-white' : 'bg-soft' }}">
                        {{ $copy->is_done ? 'Selesai' : 'Belum selesai' }}
                    </span>
                </li>
            @empty
                <li class="card border-dashed px-6 py-8 text-center text-sm text-ink/70">Belum dibagikan ke siapa pun.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.app>
