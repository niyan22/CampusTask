<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Jadwal kuliah mingguan, dikelompokkan per hari.
     */
    public function index(Request $request): View
    {
        $schedules = $request->user()->schedules()
            ->with('course')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        $courses = $request->user()->courses()->orderBy('name')->get();

        return view('schedules.index', compact('schedules', 'courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', Rule::exists('courses', 'id')->where('user_id', $request->user()->id)],
            'day' => ['required', Rule::in(array_keys(Schedule::DAYS))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);

        $request->user()->schedules()->create($data);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        Gate::authorize('manage', $schedule);

        $schedule->delete();

        return back()->with('success', 'Jadwal dihapus.');
    }
}
