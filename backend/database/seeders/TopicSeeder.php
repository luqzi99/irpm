<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Topic;
use App\Models\Subtopic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Seed sample DSKP data for demo
     * Based on Sains Tahun 5 structure
     */
    public function run(): void
    {
        // Get Sains Primary subject
        $sains = Subject::where('name', 'Sains')->where('level', 'primary')->first();
        
        if (!$sains) {
            $this->command->info('Subject Sains not found, skipping TopicSeeder');
            return;
        }

        $year = date('Y');

        // Topic 1: Kemahiran Saintifik
        $topic1 = Topic::firstOrCreate([
            'subject_id' => $sains->id,
            'year' => $year,
            'title' => 'Kemahiran Saintifik',
        ], [
            'theme' => 'Inkuiri dalam Sains',
            'standard_kandungan' => 'Murid boleh menjalankan penyiasatan saintifik',
            'sequence' => 1,
        ]);

        // Subtopics for Topic 1
        $this->createSubtopics($topic1, [
            ['1.1.1', 'Memerhati objek dan fenomena menggunakan deria'],
            ['1.1.2', 'Mengelas objek berdasarkan ciri sepunya'],
            ['1.1.3', 'Mengukur dengan menggunakan alat yang sesuai'],
            ['1.2.1', 'Membuat inferens berdasarkan pemerhatian'],
            ['1.2.2', 'Meramal apa yang akan berlaku'],
        ]);

        // Topic 2: Sains Hayat
        $topic2 = Topic::firstOrCreate([
            'subject_id' => $sains->id,
            'year' => $year,
            'title' => 'Sains Hayat',
        ], [
            'theme' => 'Manusia dan Benda Hidup',
            'standard_kandungan' => 'Murid mengetahui kepentingan penjagaan kesihatan',
            'sequence' => 2,
        ]);

        $this->createSubtopics($topic2, [
            ['2.1.1', 'Mengenal pasti organ utama dalam sistem pernafasan'],
            ['2.1.2', 'Menerangkan fungsi organ dalam sistem pernafasan'],
            ['2.2.1', 'Mengenal pasti organ utama dalam sistem pencernaan'],
            ['2.2.2', 'Menerangkan fungsi organ dalam sistem pencernaan'],
        ]);

        // Topic 3: Sains Fizikal
        $topic3 = Topic::firstOrCreate([
            'subject_id' => $sains->id,
            'year' => $year,
            'title' => 'Sains Fizikal',
        ], [
            'theme' => 'Daya dan Gerakan',
            'standard_kandungan' => 'Murid memahami konsep daya dan gerakan',
            'sequence' => 3,
        ]);

        $this->createSubtopics($topic3, [
            ['3.1.1', 'Mengenal pasti jenis-jenis daya'],
            ['3.1.2', 'Menerangkan kesan daya terhadap objek'],
            ['3.2.1', 'Mengenal pasti faktor yang mempengaruhi gerakan'],
        ]);

        // Also add Matematik topics
        $matematik = Subject::where('name', 'Matematik')->where('level', 'primary')->first();
        
        if ($matematik) {
            $mathTopic1 = Topic::firstOrCreate([
                'subject_id' => $matematik->id,
                'year' => $year,
                'title' => 'Nombor dan Operasi',
            ], [
                'theme' => 'Nombor Bulat',
                'standard_kandungan' => 'Murid boleh membaca dan menulis nombor',
                'sequence' => 1,
            ]);

            $this->createSubtopics($mathTopic1, [
                ['1.1.1', 'Menyebut nilai nombor hingga 1,000,000'],
                ['1.1.2', 'Menulis nombor dalam angka dan perkataan'],
                ['1.2.1', 'Menentukan nilai tempat digit'],
                ['1.2.2', 'Membanding dan menyusun nombor'],
            ]);

            $mathTopic2 = Topic::firstOrCreate([
                'subject_id' => $matematik->id,
                'year' => $year,
                'title' => 'Operasi Asas',
            ], [
                'theme' => 'Tambah dan Tolak',
                'standard_kandungan' => 'Murid boleh menyelesaikan operasi tambah dan tolak',
                'sequence' => 2,
            ]);

            $this->createSubtopics($mathTopic2, [
                ['2.1.1', 'Menambah nombor dalam lingkungan 1,000,000'],
                ['2.1.2', 'Menolak nombor dalam lingkungan 1,000,000'],
                ['2.2.1', 'Menyelesaikan masalah melibatkan tambah'],
                ['2.2.2', 'Menyelesaikan masalah melibatkan tolak'],
            ]);
        }

        $this->command->info('Sample DSKP data seeded successfully!');
    }

    private function createSubtopics(Topic $topic, array $subtopics): void
    {
        foreach ($subtopics as $index => $data) {
            Subtopic::firstOrCreate([
                'topic_id' => $topic->id,
                'code' => $data[0],
            ], [
                'description' => $data[1],
                'sequence' => $index + 1,
            ]);
        }
    }
}
