<x-mail::message>
# Halo, {{ $user->name }}!

Ada **{{ $tasks->count() }} tugas** yang deadline-nya **besok**:

@foreach ($tasks as $task)
- **{{ $task->title }}** ({{ $task->courseName() }})
@endforeach

<x-mail::button :url="route('tasks.index')">
Buka Tugas Kampus
</x-mail::button>

Semangat mengerjakannya!

Email ini bisa dimatikan di halaman Akun.
</x-mail::message>
