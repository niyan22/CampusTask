# CampusTask — Ringkasan Proyek

Aplikasi web sederhana untuk mengatur tugas kuliah: catat tugas, lihat deadline di kalender, dan pantau progres pengerjaan lewat dashboard. Dibuat dengan **Laravel 13**, **Tailwind CSS 4**, dan database **SQLite**.

## Fitur

| Halaman | Fungsi |
|---|---|
| **Login / Daftar** | Buat akun dan masuk. Login dibatasi 5 percobaan per menit. |
| **Dashboard** | Hanya statistik: total, selesai, belum selesai, terlambat, persentase progres (donut), dan progres per mata kuliah. |
| **Tugas** | Tambah, edit, hapus, centang selesai, cari (judul/mata kuliah), dan filter (semua / belum selesai / selesai / terlambat). |
| **Kalender** | Kalender bulanan yang menandai deadline, navigasi antar bulan, dan daftar deadline bulan itu. |
| **Akun** | Ubah nama, email, NIM, jurusan, dan password (wajib isi password lama), lalu simpan. |

Setiap user hanya melihat dan mengelola tugasnya sendiri. Tugas milik orang lain dijawab `404`.

## Cara menjalankan

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Akun demo dari seeder: **`mahasiswa@example.com`** / **`password`**

Menjalankan test:

```bash
php artisan test
```

## Struktur kode (yang perlu dipelajari)

Alurnya: `routes/web.php` → **Controller** → **Model** → **View (Blade)**.

### Backend

| File | Isi |
|---|---|
| `routes/web.php` | Daftar semua URL. Grup `guest` untuk login/daftar, grup `auth` untuk halaman yang butuh login. |
| `app/Http/Controllers/AuthController.php` | Login, daftar, logout. |
| `app/Http/Controllers/DashboardController.php` | Menghitung statistik dari tugas milik user. |
| `app/Http/Controllers/TaskController.php` | CRUD tugas + centang selesai. Validasi ada di satu method `validateTask()`. |
| `app/Http/Controllers/CalendarController.php` | Menyusun grid kalender (Senin–Minggu) dan mengelompokkan tugas per tanggal. |
| `app/Http/Controllers/ProfileController.php` | Menyimpan perubahan profil dan password. |
| `app/Models/Task.php` | Model tugas: daftar prioritas, scope `overdue`, helper `isOverdue()` dan `deadlineLabel()`. |
| `app/Models/User.php` | Model user + relasi `tasks()`. |
| `app/Policies/TaskPolicy.php` | Aturan "hanya pemilik yang boleh mengubah tugas". |
| `lang/id/validation.php` | Pesan error validasi dalam bahasa Indonesia. |

### Database

| Tabel | Kolom penting |
|---|---|
| `users` | `name`, `email`, `password`, `nim`, `major` |
| `tasks` | `user_id`, `title`, `course`, `description`, `priority` (low/medium/high), `due_date`, `is_done` |

Migrasi ada di `database/migrations/`. Seeder (`DatabaseSeeder`, `TaskSeeder`) membuat akun dan tugas contoh.

### Tampilan (`resources/views/`)

| File | Isi |
|---|---|
| `components/layouts/app.blade.php` | Layout utama: sidebar (desktop) dan menu bawah (HP). |
| `components/layouts/guest.blade.php` | Layout login/daftar. |
| `components/icon.blade.php` | Ikon SVG. Pakai: `<x-icon name="calendar" />` |
| `components/field.blade.php` | Input + label + pesan error. Pakai: `<x-field name="email" label="Email" />` |
| `dashboard.blade.php`, `calendar.blade.php` | Halaman dashboard dan kalender. |
| `tasks/index.blade.php`, `tasks/form.blade.php` | Daftar tugas dan form (satu form untuk tambah **dan** edit). |
| `auth/`, `profile/` | Halaman login, daftar, dan akun. |

### Warna (dari palet gambar)

Didefinisikan di `resources/css/app.css` dan dipakai sebagai class Tailwind:

| Nama | Hex | Contoh class |
|---|---|---|
| Honeydew | `#F6FFE9` | `bg-honeydew` (latar halaman) |
| Vanilla Custard | `#F2E0A4` | `bg-custard` |
| Periwinkle | `#CAC5E5` | `border-periwinkle` |
| Amethyst | `#A230A4` | `bg-amethyst` (tombol utama) |
| Dark Ultramarine | `#290087` | `bg-ultramarine`, `text-ultramarine` |

Kelas bantu `btn`, `btn-primary`, `btn-ghost`, dan `card` juga ada di file yang sama.

## Pengujian

36 test di `tests/Feature/` (`AuthTest`, `TaskTest`, `DashboardTest`, `CalendarTest`, `ProfileTest`) mencakup login, CRUD tugas, isolasi antar user, validasi, statistik dashboard, kalender, dan profil.

## Catatan

- Zona waktu `Asia/Jakarta` dan bahasa `id` diatur di `config/app.php` dan `.env`.
- File `.env`, `vendor/`, `node_modules/`, dan database SQLite **tidak** ikut di repository. Buat ulang lewat langkah "Cara menjalankan".
- Folder `.claude/`, `CLAUDE.md`, `AGENTS.md`, `boost.json`, dan `.mcp.json` berasal dari Laravel Boost (panduan untuk asisten AI). Aplikasi tetap berjalan tanpa semuanya.
