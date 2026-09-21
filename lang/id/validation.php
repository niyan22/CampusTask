<?php

/*
 * Pesan validasi bahasa Indonesia (dipakai karena APP_LOCALE=id).
 * Pesan yang tidak ada di sini otomatis memakai bahasa Inggris (fallback).
 */
return [
    'required' => ':attribute wajib diisi.',
    'required_with' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'integer' => ':attribute harus berupa angka bulat.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'date_format' => ':attribute harus berformat :format.',
    'after' => ':attribute harus setelah :date.',
    'in' => ':attribute tidak valid.',
    'exists' => ':attribute tidak ditemukan.',
    'unique' => ':attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password saat ini salah.',
    'image' => ':attribute harus berupa gambar.',
    'mimes' => ':attribute harus berformat :values.',
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
        'file' => ':attribute maksimal :max KB.',
    ],
    'min' => [
        'string' => ':attribute minimal :min karakter.',
    ],
    'between' => [
        'numeric' => ':attribute harus di antara :min dan :max.',
    ],

    'attributes' => [
        'title' => 'Judul tugas',
        'course_id' => 'Mata kuliah',
        'description' => 'Catatan',
        'priority' => 'Prioritas',
        'due_date' => 'Deadline',
        'subtask' => 'Langkah',
        'name' => 'Nama',
        'email' => 'Email',
        'nim' => 'NIM',
        'major' => 'Jurusan',
        'avatar' => 'Foto profil',
        'password' => 'Password',
        'current_password' => 'Password saat ini',
        'color' => 'Warna',
        'lecturer' => 'Dosen',
        'credits' => 'SKS',
        'day' => 'Hari',
        'start_time' => 'Jam mulai',
        'end_time' => 'Jam selesai',
        'room' => 'Ruang',
    ],
];
