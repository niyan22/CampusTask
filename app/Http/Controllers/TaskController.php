<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Daftar tugas milik user, bisa dicari (?q=) dan difilter (?filter=active|done|overdue).
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');
        $search = $request->query('q');

        $tasks = $request->user()->tasks()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('course', 'like', "%{$search}%");
            }))
            ->when($filter === 'active', fn ($query) => $query->where('is_done', false))
            ->when($filter === 'done', fn ($query) => $query->where('is_done', true))
            ->when($filter === 'overdue', fn ($query) => $query->overdue())
            ->orderBy('is_done')
            ->orderBy('due_date')
            ->get();

        return view('tasks.index', compact('tasks', 'filter', 'search'));
    }

    public function create(): View
    {
        $task = new Task(['priority' => 'medium']);

        return view('tasks.form', compact('task'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->tasks()->create($this->validateTask($request));

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function edit(Task $task): View
    {
        Gate::authorize('manage', $task);

        return view('tasks.form', compact('task'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $task->update($this->validateTask($request));

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Tandai tugas selesai / belum selesai.
     */
    public function toggle(Task $task): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $task->update(['is_done' => ! $task->is_done]);

        return back();
    }

    /**
     * Aturan validasi dipakai bersama oleh store() dan update().
     * Pesan error ada di lang/id/validation.php.
     *
     * @return array<string, mixed>
     */
    private function validateTask(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'course' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'due_date' => ['required', 'date'],
        ]);
    }
}
