<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Langkah-langkah kecil (checklist) di dalam sebuah tugas.
 * Rutenya di-scope, jadi {subtask} pasti milik {task}.
 * "open_task" membuat daftar langkah tetap terbuka setelah halaman dimuat ulang.
 */
class SubtaskController extends Controller
{
    public function store(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $data = $request->validate(['subtask' => ['required', 'string', 'max:255']]);

        $task->subtasks()->create(['title' => $data['subtask']]);

        return back()->with('open_task', $task->id);
    }

    public function toggle(Task $task, Subtask $subtask): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $subtask->update(['is_done' => ! $subtask->is_done]);

        return back()->with('open_task', $task->id);
    }

    public function destroy(Task $task, Subtask $subtask): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $subtask->delete();

        return back()->with('open_task', $task->id);
    }
}
