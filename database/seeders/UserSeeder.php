<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'su@ub.ac.id',
            'password' => Hash::make('123123123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // seeding lecturer
        foreach (range(1, 3) as $i) {
            DB::table('users')->insert([
                'name' => $faker->name(),
                'email' => $faker->email(),
                'password' => Hash::make('123123123'),
                'role' => 'lecturer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->insert([
            'name' => 'Ayas',
            'email' => 'anakrajin@student.ub.ac.id',
            'password' => Hash::make('123123123'),
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // seeding student
        foreach (range(1, 10) as $i) {
            DB::table('users')->insert([
                'name' => $faker->name(),
                'email' => $faker->email(),
                'password' => Hash::make('123123123'),
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
