<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Statistik pengerjaan seluruh tugas milik user.
     */
    public function __invoke(Request $request): View
    {
        $tasks = $request->user()->tasks()->with('course')->get();

        $total = $tasks->count();
        $done = $tasks->where('is_done', true)->count();

        $stats = [
            'total' => $total,
            'done' => $done,
            'active' => $total - $done,
            'overdue' => $tasks->filter->isOverdue()->count(),
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];

        // Lima tugas belum selesai dengan deadline paling dekat (yang terlambat ikut, paling atas).
        $upcoming = $tasks->where('is_done', false)->sortBy('due_date')->take(5);

        $courses = $tasks->groupBy(fn ($task) => $task->courseName())
            ->map(fn ($group) => [
                'total' => $group->count(),
                'done' => $group->where('is_done', true)->count(),
                'color' => $group->first()->courseColor(),
            ])
            ->sortKeys();

        // Jumlah tugas yang selesai di tiap minggu, 6 minggu terakhir (Senin - Minggu).
        $weekly = collect(range(5, 0))->map(function (int $weeksAgo) use ($tasks) {
            $start = now()->subWeeks($weeksAgo)->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

            return [
                'label' => $start->translatedFormat('d M'),
                'count' => $tasks->filter(fn ($task) => $task->completed_at?->between($start, $end))->count(),
            ];
        });

        return view('dashboard', compact('stats', 'upcoming', 'courses', 'weekly'));
    }
}
