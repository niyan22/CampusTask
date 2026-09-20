<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Akun demo: mahasiswa@example.com / password
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@example.com',
            'nim' => '2310123456',
            'major' => 'Teknik Informatika',
        ]);

        $this->call(TaskSeeder::class);
    }
}
