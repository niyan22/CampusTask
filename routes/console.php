<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Setiap pagi jam 07:00 (zona waktu aplikasi), kirim pengingat tugas yang deadline-nya besok.
// Jalankan penjadwal dengan: php artisan schedule:work
Schedule::command('tasks:send-reminders')->dailyAt('07:00');
