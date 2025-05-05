<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'code' => 'TIS-2025',
                'name' => 'Teknologi Integrasi Sistem',
                'credits' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PAM-2025',
                'name' => 'Pengembangan Aplikasi Mobile',
                'credits' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KS-2025',
                'name' => 'Keamanan Siber',
                'credits' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('courses')->insert($courses);
    }
}
