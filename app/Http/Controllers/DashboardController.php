<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Statistik pengerjaan seluruh tugas milik user.
     */
    public function __invoke(Request $request): View
    {
        $tasks = $request->user()->tasks()->get();

        $total = $tasks->count();
        $done = $tasks->where('is_done', true)->count();

        $stats = [
            'total' => $total,
            'done' => $done,
            'active' => $total - $done,
            'overdue' => $tasks->filter->isOverdue()->count(),
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];

        $courses = $tasks->groupBy('course')
            ->map(fn ($group) => [
                'total' => $group->count(),
                'done' => $group->where('is_done', true)->count(),
            ])
            ->sortKeys();

        return view('dashboard', compact('stats', 'courses'));
    }
}
