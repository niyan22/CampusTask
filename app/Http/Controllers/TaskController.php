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
     * Daftar tugas milik user.
     * ?q=kata  ?filter=active|done|overdue  ?sort=deadline|priority|newest
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');
        $sort = $request->query('sort', 'deadline');
        $search = $request->query('q');

        $tasks = $request->user()->tasks()
            ->with(['course', 'subtasks', 'source.user'])
            ->withCount('copies')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%"));
            }))
            ->when($filter === 'active', fn ($query) => $query->where('is_done', false))
            ->when($filter === 'done', fn ($query) => $query->where('is_done', true))
            ->when($filter === 'overdue', fn ($query) => $query->overdue())
            ->orderBy('is_done')
            ->when($sort === 'priority', fn ($query) => $query->orderByRaw("case priority when 'high' then 1 when 'medium' then 2 else 3 end"))
            ->when($sort === 'newest', fn ($query) => $query->latest())
            ->orderBy('due_date')
            ->paginate(10)
            ->withQueryString();

        return view('tasks.index', compact('tasks', 'filter', 'sort', 'search'));
    }

    public function create(Request $request): View
    {
        $task = new Task(['priority' => 'medium']);
        $courses = $request->user()->courses()->orderBy('name')->get();

        return view('tasks.form', compact('task', 'courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->tasks()->create($this->validateTask($request));

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function edit(Request $request, Task $task): View
    {
        Gate::authorize('manage', $task);

        $courses = $request->user()->courses()->orderBy('name')->get();

        return view('tasks.form', compact('task', 'courses'));
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
     * Tandai tugas selesai / belum selesai (dan catat kapan selesainya).
     */
    public function toggle(Task $task): RedirectResponse
    {
        Gate::authorize('manage', $task);

        $isDone = ! $task->is_done;

        $task->update(['is_done' => $isDone, 'completed_at' => $isDone ? now() : null]);

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
            'course_id' => ['nullable', Rule::exists('courses', 'id')->where('user_id', $request->user()->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'due_date' => ['required', 'date'],
        ]);
    }
}
