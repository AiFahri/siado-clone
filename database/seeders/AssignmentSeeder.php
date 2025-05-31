<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignments = [
            [
                'title' => 'Buat 2 Website dan Integrasikan',
                'description' => 'Mahasiswa diminta untuk membuat dua website sederhana dengan fungsi berbeda (misalnya: website katalog produk dan website checkout). Setelah keduanya selesai, integrasikan kedua website tersebut menggunakan API atau mekanisme request-response dasar, sehingga data dari website pertama bisa digunakan di website kedua.',
                'due_date' => now()->addDays(rand(2, 4)),
                'created_at' => now(),
                'updated_at' => now(),
                'course_id' => 1,
            ],
            [
                'title' => 'Membuat Aplikasi To-Do List',
                'description' => 'Kembangkan sebuah aplikasi to-do list sederhana menggunakan Flutter atau React Native. Aplikasi harus memiliki fitur tambah, ubah, hapus tugas, serta menyimpan data secara lokal menggunakan SQLite.',
                'due_date' => now()->addDays(rand(2, 4)),
                'created_at' => now(),
                'updated_at' => now(),
                'course_id' => 2,
            ],
            [
                'title' => 'Analisis Kerentanan Web',
                'description' => 'Lakukan analisis sederhana terhadap aplikasi web yang rentan (gunakan DVWA atau OWASP Juice Shop). Identifikasi minimal 3 jenis serangan umum (seperti XSS, SQL Injection), dan buat laporan serta simulasi eksploitasi.',
                'due_date' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
                'course_id' => 3,
            ],
            [
                'title' => 'Integrasi API Publik dalam Aplikasi',
                'description' => 'Bangun aplikasi mobile yang memanfaatkan API publik (seperti OpenWeatherMap atau NewsAPI). Aplikasi harus menampilkan data real-time dan memiliki tampilan UI yang responsif untuk berbagai ukuran layar.',
                'due_date' => now()->addDays(6),
                'created_at' => now(),
                'updated_at' => now(),
                'course_id' => 2,
            ]
        ];

        DB::table('assignments')->insert($assignments);
    }
}
