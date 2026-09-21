<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Tugas contoh untuk akun demo, lengkap dengan langkah-langkah,
     * tugas yang sudah selesai (untuk grafik), dan satu tugas yang dibagikan ke teman.
     */
    public function run(): void
    {
        $user = User::where('email', 'mahasiswa@example.com')->firstOrFail();
        $friend = User::where('email', 'teman@example.com')->firstOrFail();

        $courseId = fn (string $name) => $user->courses()->where('name', $name)->value('id');

        // [judul, mata kuliah, prioritas, deadline (hari dari hari ini)]
        $pending = [
            ['Laporan praktikum CRUD Laravel', 'Pemrograman Web', 'high', 2],
            ['Kuis Statistika bab 3', 'Statistika', 'medium', 2],
            ['Rancang ERD sistem perpustakaan', 'Basis Data', 'medium', 5],
            ['Latihan soal sorting', 'Algoritma', 'low', 7],
            ['Tugas regresi linear', 'Statistika', 'high', -1],
            ['Query JOIN dan subquery', 'Basis Data', 'low', 10],
        ];

        foreach ($pending as [$title, $course, $priority, $days]) {
            $user->tasks()->create([
                'title' => $title,
                'course_id' => $courseId($course),
                'priority' => $priority,
                'due_date' => today()->addDays($days),
            ]);
        }

        // [judul, mata kuliah, selesai berapa hari yang lalu]
        $done = [
            ['Presentasi kelompok UI/UX', 'Interaksi Manusia dan Komputer', 1],
            ['Quiz algoritma greedy', 'Algoritma', 3],
            ['Resume bab 2 Basis Data', 'Basis Data', 9],
            ['Praktikum HTML dasar', 'Pemrograman Web', 12],
            ['Tugas probabilitas', 'Statistika', 17],
            ['Laporan observasi pengguna', 'Interaksi Manusia dan Komputer', 25],
        ];

        foreach ($done as [$title, $course, $daysAgo]) {
            $user->tasks()->create([
                'title' => $title,
                'course_id' => $courseId($course),
                'priority' => 'medium',
                'due_date' => today()->subDays($daysAgo - 1),
                'is_done' => true,
                'completed_at' => now()->subDays($daysAgo),
            ]);
        }

        // Langkah-langkah pada tugas pertama dan ketiga.
        $laporan = $user->tasks()->where('title', 'Laporan praktikum CRUD Laravel')->firstOrFail();
        $laporan->subtasks()->createMany([
            ['title' => 'Buat migrasi dan model', 'is_done' => true],
            ['title' => 'Buat controller dan view', 'is_done' => true],
            ['title' => 'Tulis laporan dan screenshot', 'is_done' => false],
        ]);

        $erd = $user->tasks()->where('title', 'Rancang ERD sistem perpustakaan')->firstOrFail();
        $erd->subtasks()->createMany([
            ['title' => 'Tentukan entitas', 'is_done' => true],
            ['title' => 'Gambar relasi antar tabel', 'is_done' => false],
        ]);

        // Tugas kelompok: laporan praktikum dibagikan ke teman (dan temannya sudah selesai).
        $friendCourse = $friend->courses()->create([
            'name' => 'Pemrograman Web',
            'color' => $laporan->course->color,
        ]);

        $friend->tasks()->create([
            'title' => $laporan->title,
            'course_id' => $friendCourse->id,
            'priority' => $laporan->priority,
            'due_date' => $laporan->due_date,
            'source_task_id' => $laporan->id,
            'is_done' => true,
            'completed_at' => now()->subHours(3),
        ]);
    }
}
