<?php

namespace Database\Seeders;

use App\Models\AssessmentMethod;
use Illuminate\Database\Seeder;

class AssessmentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['code' => 'OBS', 'name' => 'Pemerhatian'],
            ['code' => 'LISAN', 'name' => 'Lisan'],
            ['code' => 'BERTULIS', 'name' => 'Bertulis'],
            ['code' => 'PROJEK', 'name' => 'Projek'],
        ];

        foreach ($methods as $method) {
            AssessmentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
