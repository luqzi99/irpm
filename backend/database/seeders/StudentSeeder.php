<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Seed sample students for demo/testing
     * IC format: YYMMDD-SS-NNNN (12 digits without dashes)
     */
    public function run(): void
    {
        $students = [
            // Year 6 students (born 2012-2013)
            ['ic' => '120315071234', 'name' => 'Ahmad bin Abu', 'school_name' => 'SK Taman Melati'],
            ['ic' => '120822081235', 'name' => 'Siti Aminah binti Kamal', 'school_name' => 'SK Taman Melati'],
            ['ic' => '130105011236', 'name' => 'Muhammad Haziq bin Hassan', 'school_name' => 'SK Taman Melati'],
            ['ic' => '120617021237', 'name' => 'Nurul Aisyah binti Rahman', 'school_name' => 'SK Taman Melati'],
            ['ic' => '130412031238', 'name' => 'Amir Farhan bin Yusof', 'school_name' => 'SK Taman Melati'],
            
            // Year 5 students (born 2013-2014)
            ['ic' => '130928041239', 'name' => 'Aisyah binti Mohd Ali', 'school_name' => 'SK Taman Melati'],
            ['ic' => '140203051240', 'name' => 'Danish Irfan bin Zainal', 'school_name' => 'SK Taman Melati'],
            ['ic' => '131115061241', 'name' => 'Fatimah Zahra binti Ismail', 'school_name' => 'SK Taman Melati'],
            ['ic' => '140507071242', 'name' => 'Harith Adam bin Sulaiman', 'school_name' => 'SK Taman Melati'],
            ['ic' => '130730081243', 'name' => 'Irdina Sofea binti Ahmad', 'school_name' => 'SK Taman Melati'],
            
            // More students for testing
            ['ic' => '121224091244', 'name' => 'Khairul Anwar bin Osman', 'school_name' => 'SK Taman Melati'],
            ['ic' => '130618101245', 'name' => 'Lina Mardhiah binti Razak', 'school_name' => 'SK Taman Melati'],
            ['ic' => '140102111246', 'name' => 'Muhammad Arif bin Bakar', 'school_name' => 'SK Taman Melati'],
            ['ic' => '121009121247', 'name' => 'Nur Hidayah binti Hamdan', 'school_name' => 'SK Taman Melati'],
            ['ic' => '130425101248', 'name' => 'Omar Faruk bin Abdullah', 'school_name' => 'SK Taman Melati'],
        ];

        foreach ($students as $student) {
            Student::findOrCreateByIc(
                $student['ic'],
                $student['name'],
                $student['school_name']
            );
        }

        $this->command->info('15 sample students seeded successfully!');
    }
}
