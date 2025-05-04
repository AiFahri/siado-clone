<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course_id = 1;
        foreach (range(1, 11) as $index) {

            DB::table('course_enrollments')->insert([
                'created_at' => now(),
                'user_id' => $index,
                'course_id' => $course_id
            ]);

            if ($index % 4 == 0) {
                $course_id++;
            }
        }
    }
}
