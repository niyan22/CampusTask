<?php

/*
 * Pesan validasi bahasa Indonesia (dipakai karena APP_LOCALE=id).
 * Pesan yang tidak ada di sini otomatis memakai bahasa Inggris (fallback).
 */
return [
    'required' => ':attribute wajib diisi.',
    'required_with' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'date_format' => ':attribute harus berformat :format.',
    'in' => ':attribute tidak valid.',
    'unique' => ':attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password saat ini salah.',
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
    ],
    'min' => [
        'string' => ':attribute minimal :min karakter.',
    ],

    'attributes' => [
        'title' => 'Judul tugas',
        'course' => 'Mata kuliah',
        'description' => 'Catatan',
        'priority' => 'Prioritas',
        'due_date' => 'Deadline',
        'name' => 'Nama',
        'email' => 'Email',
        'nim' => 'NIM',
        'major' => 'Jurusan',
        'password' => 'Password',
        'current_password' => 'Password saat ini',
    ],
];
