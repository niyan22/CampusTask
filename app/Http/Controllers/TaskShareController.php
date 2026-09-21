<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Tugas kelompok: membagikan tugas ke teman.
 *
 * Teman menerima SALINAN tugas di daftarnya sendiri (dengan source_task_id
 * menunjuk ke tugas asli), jadi status selesai tiap orang terpisah.
 * Pemilik tugas asli bisa melihat siapa saja yang sudah menyelesaikannya.
 */
class TaskShareController extends Controller
{
    public function show(Task $task): View
    {
        $this->authorizeOriginal($task);

        $copies = $task->copies()->with('user')->get();

        return view('tasks.share', compact('task', 'copies'));
    }

    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeOriginal($task);

        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $friend = User::where('email', $request->input('email'))->firstOrFail();

        if ($friend->is($request->user())) {
            return back()->withErrors(['email' => 'Kamu tidak bisa membagikan tugas ke dirimu sendiri.']);
        }

        if ($task->copies()->where('user_id', $friend->id)->exists()) {
            return back()->withErrors(['email' => "Tugas ini sudah dibagikan ke {$friend->name}."]);
        }

        // Mata kuliah milik teman dicocokkan lewat nama; dibuat baru kalau belum ada.
        $course = $task->course
            ? $friend->courses()->firstOrCreate(['name' => $task->course->name], ['color' => $task->course->color])
            : null;

        $friend->tasks()->create([
            'course_id' => $course?->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'due_date' => $task->due_date,
            'source_task_id' => $task->id,
        ]);

        return back()->with('success', "Tugas dibagikan ke {$friend->name}.");
    }

    /**
     * Hanya pemilik yang boleh membagikan, dan hanya tugas asli (bukan salinan).
     */
    private function authorizeOriginal(Task $task): void
    {
        Gate::authorize('manage', $task);

        abort_if($task->source_task_id !== null, 404);
    }
}
