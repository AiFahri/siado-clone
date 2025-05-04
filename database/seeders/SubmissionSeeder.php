<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $submissions = [
            [
                'answer' => 'https://github.com/anakrajin/to-do-list',
                'file_url' => null,
                'grade' => null,
                'feedback' => null,
                'created_at' => now()->addHours(10),
                'updated_at' => now()->addHours(10),
                'user_id' => 5,
                'assignment_id' => 2,
            ],
            [
                'answer' => 'https://github.com/anakrajin/integration-api-on-mobile-apps',
                'file_url' => null,
                'grade' => null,
                'feedback' => null,
                'created_at' => now()->addDays(1)->addHours(17),
                'updated_at' => now()->addDays(1)->addHours(17),
                'user_id' => 5,
                'assignment_id' => 4,
            ]
        ];

        DB::table('submissions')->insert($submissions);
    }
}
