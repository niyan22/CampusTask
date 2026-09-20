<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Tugas contoh untuk user pertama.
     */
    public function run(): void
    {
        $user = User::firstOrFail();

        $tasks = [
            ['title' => 'Laporan praktikum CRUD Laravel', 'course' => 'Pemrograman Web', 'priority' => 'high', 'due' => 2],
            ['title' => 'Kuis Statistika bab 3', 'course' => 'Statistika', 'priority' => 'medium', 'due' => 2],
            ['title' => 'Rancang ERD sistem perpustakaan', 'course' => 'Basis Data', 'priority' => 'medium', 'due' => 5],
            ['title' => 'Latihan soal sorting', 'course' => 'Algoritma', 'priority' => 'low', 'due' => 7],
            ['title' => 'Tugas regresi linear', 'course' => 'Statistika', 'priority' => 'high', 'due' => -1],
            ['title' => 'Query JOIN dan subquery', 'course' => 'Basis Data', 'priority' => 'low', 'due' => 10],
        ];

        foreach ($tasks as $task) {
            $user->tasks()->create([
                'title' => $task['title'],
                'course' => $task['course'],
                'priority' => $task['priority'],
                'due_date' => today()->addDays($task['due']),
            ]);
        }

        $user->tasks()->createMany([
            [
                'title' => 'Presentasi kelompok UI/UX',
                'course' => 'Interaksi Manusia dan Komputer',
                'priority' => 'medium',
                'due_date' => today()->subDays(3),
                'is_done' => true,
            ],
            [
                'title' => 'Quiz algoritma greedy',
                'course' => 'Algoritma',
                'priority' => 'low',
                'due_date' => today()->subDays(6),
                'is_done' => true,
            ],
        ]);
    }
}
