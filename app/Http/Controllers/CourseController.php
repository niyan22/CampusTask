<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = $request->user()->courses()
            ->withCount([
                'tasks as pending_tasks_count' => fn ($query) => $query->where('is_done', false),
                'schedules',
            ])
            ->orderBy('name')
            ->get();

        return view('courses.index', compact('courses'));
    }

    public function create(): View
    {
        $course = new Course(['color' => Course::COLORS[0]]);

        return view('courses.form', compact('course'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->courses()->create($this->validateCourse($request));

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(Course $course): View
    {
        Gate::authorize('manage', $course);

        return view('courses.form', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('manage', $course);

        $course->update($this->validateCourse($request, $course));

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    /**
     * Tugas yang memakai mata kuliah ini tetap ada (jadi "tanpa mata kuliah"),
     * sedangkan jadwal kuliahnya ikut terhapus.
     */
    public function destroy(Course $course): RedirectResponse
    {
        Gate::authorize('manage', $course);

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah dihapus. Tugasnya tetap ada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCourse(Request $request, ?Course $course = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('courses')->where('user_id', $request->user()->id)->ignore($course),
            ],
            'color' => ['required', Rule::in(Course::COLORS)],
            'lecturer' => ['nullable', 'string', 'max:100'],
            'credits' => ['nullable', 'integer', 'between:1,8'],
        ]);
    }
}
