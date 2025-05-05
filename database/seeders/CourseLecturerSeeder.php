<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseLecturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 3) as $index) {
            DB::table('course_lecturers')->insert([
                'created_at' => now(),
                'user_id' => $index,
                'course_id' => $index,
            ]);
        }
    }
}
