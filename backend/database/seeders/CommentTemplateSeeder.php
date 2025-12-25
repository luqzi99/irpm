<?php

namespace Database\Seeders;

use App\Models\AssessmentMethod;
use App\Models\CommentTemplate;
use Illuminate\Database\Seeder;

class CommentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Pemerhatian (OBS)
            'OBS' => [
                1 => 'Murid memerlukan bimbingan sepenuhnya melalui pemerhatian.',
                2 => 'Murid dapat melaksanakan tugas dengan bimbingan berterusan melalui pemerhatian.',
                3 => 'Murid menunjukkan kefahaman asas melalui pemerhatian dengan bimbingan.',
                4 => 'Murid menunjukkan penguasaan baik melalui pemerhatian tanpa bimbingan.',
                5 => 'Murid menunjukkan penguasaan cemerlang melalui pemerhatian secara konsisten.',
                6 => 'Murid menunjukkan penguasaan cemerlang dan boleh membimbing rakan melalui pemerhatian.',
            ],
            // Lisan
            'LISAN' => [
                1 => 'Murid memerlukan bimbingan sepenuhnya untuk menjawab secara lisan.',
                2 => 'Murid dapat menjawab secara lisan dengan bimbingan berterusan.',
                3 => 'Murid menunjukkan kefahaman asas melalui jawapan lisan dengan bimbingan.',
                4 => 'Murid menunjukkan penguasaan baik melalui jawapan lisan tanpa bimbingan.',
                5 => 'Murid menunjukkan penguasaan cemerlang melalui jawapan lisan secara konsisten.',
                6 => 'Murid menunjukkan penguasaan cemerlang dan boleh membimbing rakan melalui lisan.',
            ],
            // Bertulis
            'BERTULIS' => [
                1 => 'Murid memerlukan bimbingan sepenuhnya untuk menyelesaikan tugasan bertulis.',
                2 => 'Murid dapat menyelesaikan tugasan bertulis dengan bimbingan berterusan.',
                3 => 'Murid menunjukkan kefahaman asas melalui tugasan bertulis dengan bimbingan.',
                4 => 'Murid menunjukkan penguasaan baik melalui tugasan bertulis tanpa bimbingan.',
                5 => 'Murid menunjukkan penguasaan cemerlang melalui tugasan bertulis secara konsisten.',
                6 => 'Murid menunjukkan penguasaan cemerlang dan boleh membimbing rakan dalam tugasan bertulis.',
            ],
            // Projek
            'PROJEK' => [
                1 => 'Murid memerlukan bimbingan sepenuhnya untuk menyelesaikan projek.',
                2 => 'Murid dapat menyelesaikan projek dengan bimbingan berterusan.',
                3 => 'Murid menunjukkan kefahaman asas melalui projek dengan bimbingan.',
                4 => 'Murid menunjukkan penguasaan baik melalui projek tanpa bimbingan.',
                5 => 'Murid menunjukkan penguasaan cemerlang melalui projek secara konsisten.',
                6 => 'Murid menunjukkan penguasaan cemerlang dan boleh membimbing rakan dalam projek.',
            ],
        ];

        foreach ($templates as $methodCode => $tpTemplates) {
            $method = AssessmentMethod::where('code', $methodCode)->first();
            
            if (!$method) continue;

            foreach ($tpTemplates as $tp => $templateText) {
                CommentTemplate::firstOrCreate(
                    [
                        'assessment_method_id' => $method->id,
                        'tp' => $tp,
                    ],
                    [
                        'template_text' => $templateText,
                    ]
                );
            }
        }
    }
}
