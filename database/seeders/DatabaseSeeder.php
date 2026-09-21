<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Akun demo (password keduanya: "password"):
     * - mahasiswa@example.com  -> punya banyak data contoh
     * - teman@example.com      -> dipakai untuk mencoba fitur bagikan tugas
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@example.com',
            'nim' => '2310123456',
            'major' => 'Teknik Informatika',
        ]);

        User::factory()->create([
            'name' => 'Teman Demo',
            'email' => 'teman@example.com',
            'nim' => '2310654321',
            'major' => 'Teknik Informatika',
        ]);

        $this->call([CourseSeeder::class, TaskSeeder::class]);
    }
}
