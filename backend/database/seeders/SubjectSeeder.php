<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Primary (Sekolah Rendah)
            ['name' => 'Bahasa Melayu', 'level' => 'primary'],
            ['name' => 'Bahasa Inggeris', 'level' => 'primary'],
            ['name' => 'Matematik', 'level' => 'primary'],
            ['name' => 'Sains', 'level' => 'primary'],
            ['name' => 'Pendidikan Islam', 'level' => 'primary'],
            ['name' => 'Pendidikan Moral', 'level' => 'primary'],
            ['name' => 'Pendidikan Jasmani dan Kesihatan', 'level' => 'primary'],
            ['name' => 'Pendidikan Seni Visual', 'level' => 'primary'],
            ['name' => 'Pendidikan Muzik', 'level' => 'primary'],
            ['name' => 'Sejarah', 'level' => 'primary'],
            ['name' => 'Reka Bentuk dan Teknologi', 'level' => 'primary'],
            
            // Secondary (Sekolah Menengah)
            ['name' => 'Bahasa Melayu', 'level' => 'secondary'],
            ['name' => 'Bahasa Inggeris', 'level' => 'secondary'],
            ['name' => 'Matematik', 'level' => 'secondary'],
            ['name' => 'Sains', 'level' => 'secondary'],
            ['name' => 'Sejarah', 'level' => 'secondary'],
            ['name' => 'Pendidikan Islam', 'level' => 'secondary'],
            ['name' => 'Pendidikan Moral', 'level' => 'secondary'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['name' => $subject['name'], 'level' => $subject['level']],
                $subject
            );
        }
    }
}
