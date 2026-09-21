<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Kalender bulanan yang menandai deadline tugas (?month=2026-09).
     */
    public function __invoke(Request $request): View
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        $month = Carbon::createFromFormat('!Y-m', $request->query('month', now()->format('Y-m')));

        // Grid dimulai Senin dan berakhir Minggu, jadi bisa memuat hari dari bulan sebelum/sesudahnya.
        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $tasksByDate = $request->user()->tasks()
            ->with('course')
            ->whereDate('due_date', '>=', $gridStart)
            ->whereDate('due_date', '<=', $gridEnd)
            ->orderBy('is_done')
            ->get()
            ->groupBy(fn ($task) => $task->due_date->toDateString());

        $days = CarbonPeriod::create($gridStart, $gridEnd);

        return view('calendar', compact('month', 'days', 'tasksByDate'));
    }
}
