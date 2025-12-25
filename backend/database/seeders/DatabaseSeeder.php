<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@irpm.my'],
            [
                'name' => 'Admin iRPM',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create test guru
        User::firstOrCreate(
            ['email' => 'guru@irpm.my'],
            [
                'name' => 'Cikgu Test',
                'password' => Hash::make('guru123'),
                'role' => 'guru',
            ]
        );

        // Run other seeders
        $this->call([
            SubjectSeeder::class,
            AssessmentMethodSeeder::class,
            CommentTemplateSeeder::class,
            TopicSeeder::class,
        ]);
    }
}
