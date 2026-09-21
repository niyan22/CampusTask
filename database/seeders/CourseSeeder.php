<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Mata kuliah dan jadwal contoh untuk akun demo.
     */
    public function run(): void
    {
        $user = User::where('email', 'mahasiswa@example.com')->firstOrFail();

        $courses = [
            ['name' => 'Pemrograman Web', 'color' => '#A230A4', 'lecturer' => 'Dr. Andi Wijaya', 'credits' => 3],
            ['name' => 'Basis Data', 'color' => '#2F80D0', 'lecturer' => 'Siti Rahma, M.Kom.', 'credits' => 3],
            ['name' => 'Algoritma', 'color' => '#2E9C6B', 'lecturer' => 'Budi Hartono, M.T.', 'credits' => 4],
            ['name' => 'Statistika', 'color' => '#E67A2E', 'lecturer' => 'Dewi Lestari, M.Si.', 'credits' => 2],
            ['name' => 'Interaksi Manusia dan Komputer', 'color' => '#D6457B', 'lecturer' => 'Rina Kusuma, M.Kom.', 'credits' => 3],
        ];

        foreach ($courses as $course) {
            $user->courses()->create($course);
        }

        // [nama mata kuliah, hari (1 = Senin), mulai, selesai, ruang]
        $schedules = [
            ['Pemrograman Web', 1, '08:00', '10:30', 'Lab 2'],
            ['Basis Data', 2, '10:00', '12:30', 'R. 301'],
            ['Algoritma', 3, '13:00', '15:30', 'R. 204'],
            ['Statistika', 4, '08:00', '09:40', 'R. 105'],
            ['Pemrograman Web', 4, '13:00', '14:40', 'Lab 2'],
            ['Interaksi Manusia dan Komputer', 5, '09:00', '11:30', 'Lab 1'],
        ];

        foreach ($schedules as [$courseName, $day, $start, $end, $room]) {
            $user->schedules()->create([
                'course_id' => $user->courses()->where('name', $courseName)->value('id'),
                'day' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'room' => $room,
            ]);
        }
    }
}
