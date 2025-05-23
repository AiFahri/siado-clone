<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    public function run()
    {

        $course1 = Course::find(1);
        $course2 = Course::find(2);
        $course3 = Course::find(3);

        if ($course1) {
            Material::create([
                'title' => 'API dan Web Services',
                'content' => 'Pengenalan RESTful API, SOAP, dan penggunaan web services dalam integrasi aplikasi.',
                'course_id' => $course1->id,
            ]);
            Material::create([
                'title' => 'Middleware dan Protokol Komunikasi',
                'content' => 'Fungsi middleware, jenis-jenis middleware, serta protokol komunikasi yang umum digunakan.',
                'course_id' => $course1->id,
            ]);
        }

        if ($course2) {
            Material::create([
                'title' => 'Pemrograman Android dengan Kotlin',
                'content' => 'Dasar-dasar pemrograman Android menggunakan Kotlin, lifecycle activity, dan layout.',
                'course_id' => $course2->id,
            ]);
            Material::create([
                'title' => 'Integrasi API dan Backend',
                'content' => 'Cara aplikasi mobile berkomunikasi dengan backend melalui API, termasuk teknik asynchronous.',
                'course_id' => $course2->id,
            ]);
        }

        if ($course3) {
            Material::create([
                'title' => 'Kriptografi dan Enkripsi',
                'content' => 'Pengenalan kriptografi, metode enkripsi simetris dan asimetris, serta penerapannya.',
                'course_id' => $course3->id,
            ]);
            Material::create([
                'title' => 'Etika dan Hukum dalam Keamanan Siber',
                'content' => 'Isu etis, privasi, dan aspek hukum terkait keamanan siber dan perlindungan data.',
                'course_id' => $course3->id,
            ]);
        }
    }
}
