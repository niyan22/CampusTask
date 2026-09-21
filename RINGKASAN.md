# CampusTask — Ringkasan Proyek

Aplikasi web sederhana untuk mengatur tugas kuliah: catat tugas, lihat deadline di kalender, atur mata kuliah dan jadwal, bagikan tugas kelompok ke teman, dan pantau progres lewat dashboard. Dibuat dengan **Laravel 13**, **Tailwind CSS 4**, dan database **SQLite**. Tersedia mode terang dan gelap.

## Fitur

| Halaman | Fungsi |
|---|---|
| **Login / Daftar** | Buat akun dan masuk. Login dibatasi 5 percobaan per menit. |
| **Lupa password** | Kirim link reset lewat email, lalu buat password baru. Jawabannya sama untuk email terdaftar maupun tidak, jadi tidak bisa dipakai menebak akun. |
| **Dashboard** | Statistik: total, selesai, belum selesai, terlambat, persentase progres (donut), **5 deadline terdekat**, **grafik tugas selesai per minggu** (6 minggu), dan progres per mata kuliah. |
| **Tugas** | Tambah, edit, hapus, centang selesai, cari (judul/mata kuliah), filter, **urutkan** (deadline / prioritas / terbaru), dan **paginasi** (10 per halaman). Tiap tugas punya **langkah-langkah (checklist)** dan bisa **dibagikan ke teman**. |
| **Kalender** | Kalender bulanan yang menandai deadline dengan warna mata kuliah, navigasi antar bulan, daftar deadline bulan itu, dan tombol **Ekspor .ics** (untuk Google Calendar / Apple Calendar / Outlook). |
| **Jadwal** | Jadwal kuliah mingguan (Senin–Minggu): hari, jam, dan ruang. Hari ini ditandai. |
| **Mata Kuliah** | Tambah, edit, hapus mata kuliah dengan nama, dosen, SKS, dan **warna**. Warnanya dipakai di tugas, kalender, jadwal, dan dashboard. |
| **Akun** | Ubah nama, email, NIM, jurusan, **foto profil**, pengaturan **email pengingat**, dan password (wajib isi password lama). |
| **Tema** | Tombol terang/gelap di sidebar (atau header di HP). Pilihan tersimpan di browser. |

Setiap user hanya melihat dan mengelola datanya sendiri. Data milik orang lain dijawab `404`.

### Cara kerja fitur yang butuh penjelasan

- **Tugas kelompok:** membagikan tugas membuat **salinan** di daftar teman (kolom `source_task_id` menunjuk ke tugas asli). Status selesai tiap orang terpisah, dan pemilik tugas asli bisa melihat siapa saja yang sudah selesai di halaman Bagikan. Hanya tugas asli yang bisa dibagikan, bukan salinan.
- **Pengingat email:** setiap pagi 07.00 (zona waktu `Asia/Jakarta`), command `tasks:send-reminders` mengirim satu email per user berisi tugas yang deadline-nya besok. Bisa dimatikan di halaman Akun.
- **Grafik mingguan:** memakai kolom `completed_at`, yang diisi saat tugas dicentang selesai.

## Cara menjalankan

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

`storage:link` diperlukan supaya foto profil bisa tampil.

Untuk pengingat email, jalankan penjadwal di terminal lain (biarkan tetap terbuka):

```bash
php artisan schedule:work
```

Atau coba kirim pengingat sekarang juga:

```bash
php artisan tasks:send-reminders
```

Di `.env`, `MAIL_MAILER=log` berarti email **tidak benar-benar terkirim**, tapi ditulis ke `storage/logs/laravel.log`. Ini aman untuk belajar dan mencoba. Untuk mengirim email sungguhan (termasuk link lupa password), isi `MAIL_MAILER=smtp` beserta `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, dan `MAIL_PASSWORD`.

### Akun demo (password keduanya: `password`)

| Email | Keterangan |
|---|---|
| `mahasiswa@example.com` | Punya banyak data contoh (mata kuliah, jadwal, tugas, langkah-langkah). |
| `teman@example.com` | Dipakai untuk mencoba **Bagikan tugas**. Sudah menerima satu tugas dari akun di atas. |

### Menjalankan test

```bash
php artisan test
```

## Struktur kode (yang perlu dipelajari)

Alurnya: `routes/web.php` → **Controller** → **Model** → **View (Blade)**.

### Backend

| File | Isi |
|---|---|
| `routes/web.php` | Daftar semua URL. Grup `guest` untuk login/daftar/lupa password, grup `auth` untuk halaman yang butuh login. |
| `routes/console.php` | Jadwal otomatis: pengingat email setiap hari jam 07.00. |
| `app/Http/Controllers/AuthController.php` | Login, daftar, logout. |
| `app/Http/Controllers/PasswordResetController.php` | Lupa password dan reset password. |
| `app/Http/Controllers/DashboardController.php` | Menghitung statistik, deadline terdekat, dan grafik mingguan. |
| `app/Http/Controllers/TaskController.php` | CRUD tugas, centang selesai, cari, filter, urutkan, dan paginasi. |
| `app/Http/Controllers/SubtaskController.php` | Langkah-langkah (checklist) di dalam tugas. |
| `app/Http/Controllers/TaskShareController.php` | Membagikan tugas ke teman dan melihat statusnya. |
| `app/Http/Controllers/CourseController.php` | CRUD mata kuliah. |
| `app/Http/Controllers/ScheduleController.php` | Jadwal kuliah mingguan. |
| `app/Http/Controllers/CalendarController.php` | Menyusun grid kalender (Senin–Minggu) dan mengelompokkan tugas per tanggal. |
| `app/Http/Controllers/CalendarExportController.php` | Membuat file `.ics` dari tugas yang belum selesai. |
| `app/Http/Controllers/ProfileController.php` | Menyimpan profil, foto, pengingat, dan password. |
| `app/Console/Commands/SendDeadlineReminders.php` | Command `tasks:send-reminders`. |
| `app/Mail/DeadlineReminder.php` | Email pengingat (tampilannya di `resources/views/mail/`). |
| `app/Models/` | `Task`, `Course`, `Schedule`, `Subtask`, `User` beserta relasinya. |
| `app/Policies/OwnerPolicy.php` | Satu aturan "hanya pemilik yang boleh mengubah" untuk Task, Course, dan Schedule (didaftarkan di `AppServiceProvider`). |
| `lang/id/`, `lang/id.json` | Pesan validasi, pesan reset password, dan teks email dalam bahasa Indonesia. |

### Database

| Tabel | Kolom penting |
|---|---|
| `users` | `name`, `email`, `password`, `nim`, `major`, `avatar`, `remind_by_email` |
| `courses` | `user_id`, `name`, `color`, `lecturer`, `credits` |
| `schedules` | `user_id`, `course_id`, `day` (1 = Senin … 7 = Minggu), `start_time`, `end_time`, `room` |
| `tasks` | `user_id`, `course_id`, `title`, `description`, `priority` (low/medium/high), `due_date`, `is_done`, `completed_at`, `source_task_id` |
| `subtasks` | `task_id`, `title`, `is_done` |

Menghapus mata kuliah **tidak** menghapus tugasnya (tugas menjadi "tanpa mata kuliah"), tapi jadwalnya ikut terhapus. Migrasi ada di `database/migrations/`. Migrasi `add_course_sharing_and_completion_to_tasks_table` juga memindahkan nama mata kuliah lama (teks) ke tabel `courses`, jadi data lama tidak hilang.

### Tampilan (`resources/views/`)

| File | Isi |
|---|---|
| `components/layouts/app.blade.php` | Layout utama: sidebar (desktop) dan menu bawah (HP). |
| `components/layouts/guest.blade.php` | Layout login/daftar/lupa password. |
| `components/*.blade.php` | Komponen kecil yang dipakai ulang: `x-icon`, `x-field`, `x-avatar`, `x-course-dot`, `x-pager`, `x-theme-toggle`, `x-theme-script`. |
| `dashboard`, `calendar` | Halaman dashboard dan kalender. |
| `tasks/`, `courses/`, `schedules/` | Daftar dan form (satu form untuk tambah **dan** edit), plus halaman bagikan tugas. |
| `auth/`, `profile/`, `mail/` | Login, daftar, lupa/reset password, halaman akun, dan email pengingat. |

### Warna dan tema

Palet dari gambar, didefinisikan di `resources/css/app.css` dan dipakai sebagai class Tailwind:

| Nama | Hex | Contoh class |
|---|---|---|
| Honeydew | `#F6FFE9` | `bg-honeydew` |
| Vanilla Custard | `#F2E0A4` | `bg-custard` |
| Periwinkle | `#CAC5E5` | `bg-periwinkle` |
| Amethyst | `#A230A4` | `bg-amethyst` (tombol utama) |
| Dark Ultramarine | `#290087` | `bg-ultramarine` (sidebar) |

Untuk mendukung mode gelap, tampilan memakai **warna semantik** yang nilainya berganti mengikuti tema: `bg-page` (latar), `bg-surface` (kartu), `text-ink` (teks), `border-line` (garis), `bg-soft` (chip/jalur progres), `text-accent` (teks ungu). Nilai terang dan gelapnya ada di satu tempat, yaitu `app.css`. Kelas bantu `btn`, `btn-primary`, `btn-ghost`, `card`, dan `input` juga di file itu.

## Pengujian

98 test di `tests/Feature/` mencakup login, lupa password, CRUD tugas (termasuk urutan dan paginasi), langkah-langkah, bagikan tugas, mata kuliah, jadwal, kalender dan ekspor `.ics`, dashboard, profil dan upload foto, serta pengingat email. Isolasi antar user diuji di setiap fitur.

## Catatan

- Zona waktu `Asia/Jakarta` dan bahasa `id` diatur di `config/app.php` dan `.env`.
- File `.env`, `vendor/`, `node_modules/`, `public/build`, `public/storage`, dan database SQLite **tidak** ikut di repository. Buat ulang lewat langkah "Cara menjalankan".
- Foto profil disimpan di `storage/app/public/avatars`.
- Folder `.claude/`, `CLAUDE.md`, `AGENTS.md`, `boost.json`, dan `.mcp.json` berasal dari Laravel Boost (panduan untuk asisten AI). Aplikasi tetap berjalan tanpa semuanya.
