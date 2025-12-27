<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Topic;
use App\Models\Subtopic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DskpController extends Controller
{
    /**
     * Get all education levels with subject counts
     */
    public function levels(): JsonResponse
    {
        $levels = [
            ['id' => 'tahun_1', 'name' => 'Tahun 1', 'type' => 'primary'],
            ['id' => 'tahun_2', 'name' => 'Tahun 2', 'type' => 'primary'],
            ['id' => 'tahun_3', 'name' => 'Tahun 3', 'type' => 'primary'],
            ['id' => 'tahun_4', 'name' => 'Tahun 4', 'type' => 'primary'],
            ['id' => 'tahun_5', 'name' => 'Tahun 5', 'type' => 'primary'],
            ['id' => 'tahun_6', 'name' => 'Tahun 6', 'type' => 'primary'],
            ['id' => 'tingkatan_1', 'name' => 'Tingkatan 1', 'type' => 'secondary'],
            ['id' => 'tingkatan_2', 'name' => 'Tingkatan 2', 'type' => 'secondary'],
            ['id' => 'tingkatan_3', 'name' => 'Tingkatan 3', 'type' => 'secondary'],
            ['id' => 'tingkatan_4', 'name' => 'Tingkatan 4', 'type' => 'secondary'],
            ['id' => 'tingkatan_5', 'name' => 'Tingkatan 5', 'type' => 'secondary'],
        ];

        // Add subject counts
        foreach ($levels as &$level) {
            $level['subjects_count'] = Subject::where('education_level', $level['id'])->count();
        }

        return response()->json($levels);
    }

    /**
     * Get subjects for a specific education level
     */
    public function subjects(string $level): JsonResponse
    {
        $subjects = Subject::where('education_level', $level)
            ->withCount(['topics'])
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'topics_count' => $s->topics_count,
            ]);

        return response()->json($subjects);
    }

    /**
     * Get sections, topics and subtopics for a subject
     */
    public function subjectDetail(Request $request, Subject $subject): JsonResponse
    {
        $academicYear = $request->query('year', date('Y'));
        
        // Get available academic years for this subject
        $availableYears = Section::where('subject_id', $subject->id)
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');
        
        // Get sections with topics and subtopics for selected year
        $sections = Section::where('subject_id', $subject->id)
            ->where('academic_year', $academicYear)
            ->with(['topics' => function($q) {
                $q->with(['subtopics' => function($sq) {
                    $sq->orderByRaw("CAST(SPLIT_PART(code, '.', 1) AS INTEGER)")
                       ->orderByRaw("CAST(SPLIT_PART(code, '.', 2) AS INTEGER)")
                       ->orderByRaw("CAST(SPLIT_PART(code, '.', 3) AS INTEGER)");
                }])
                ->orderByRaw("CAST(SPLIT_PART(standard_kandungan, '.', 1) AS INTEGER)")
                ->orderByRaw("CAST(SPLIT_PART(standard_kandungan, '.', 2) AS INTEGER)");
            }])
            ->orderByRaw("CAST(SPLIT_PART(title_code, '.', 1) AS INTEGER)")
            ->get()
            ->map(fn($sec) => [
                'id' => $sec->id,
                'theme' => $sec->theme,
                'title_code' => $sec->title_code,
                'title_name' => $sec->title_name,
                'full_title' => "{$sec->title_code} {$sec->title_name}",
                'topics' => $sec->topics->map(fn($t) => [
                    'id' => $t->id,
                    'code' => $t->standard_kandungan ?? $t->sequence,
                    'name' => $t->title,
                    'subtopics' => $t->subtopics->map(fn($st) => [
                        'id' => $st->id,
                        'code' => $st->code,
                        'name' => $st->description,
                        'tp_max' => $st->tp_max ?? 6,
                        'tp_descriptions' => $st->tp_descriptions ?? [],
                    ]),
                ]),
            ]);

        return response()->json([
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'education_level' => $subject->education_level,
            ],
            'academic_year' => (int) $academicYear,
            'available_years' => $availableYears,
            'sections' => $sections,
        ]);
    }

    /**
     * Create or update a subject
     */
    public function storeSubject(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'education_level' => 'required|string',
        ]);

        $subject = Subject::updateOrCreate(
            [
                'code' => $request->code,
                'education_level' => $request->education_level,
            ],
            [
                'name' => $request->name,
                'level' => str_contains($request->education_level, 'tahun') ? 'primary' : 'secondary',
            ]
        );

        return response()->json([
            'message' => 'Subjek berjaya disimpan.',
            'subject' => $subject,
        ]);
    }

    /**
     * Delete a subject (and its topics/subtopics)
     */
    public function deleteSubject(Subject $subject): JsonResponse
    {
        // Delete related topics and subtopics (cascade will handle this)
        $subject->delete();

        return response()->json([
            'message' => 'Subjek dan DSKP berjaya dipadam.',
        ]);
    }

    /**
     * Store topic - uses 'title' field in DB
     */
    public function storeTopic(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ]);

        // Get subject to determine year
        $subject = Subject::findOrFail($request->subject_id);
        $year = (int) filter_var($subject->education_level, FILTER_SANITIZE_NUMBER_INT) ?: 1;

        // Get next sequence number
        $nextSequence = Topic::where('subject_id', $request->subject_id)->max('sequence') + 1;

        $topic = Topic::create([
            'subject_id' => $request->subject_id,
            'title' => $request->name,
            'year' => $year,
            'sequence' => $nextSequence,
            'standard_kandungan' => $request->code,
        ]);

        return response()->json([
            'message' => 'Topik berjaya disimpan.',
            'topic' => $topic,
        ]);
    }

    /**
     * Store subtopic - uses 'description' field in DB
     */
    public function storeSubtopic(Request $request): JsonResponse
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ]);

        // Get next sequence number
        $nextSequence = Subtopic::where('topic_id', $request->topic_id)->max('sequence') + 1;

        $subtopic = Subtopic::create([
            'topic_id' => $request->topic_id,
            'code' => $request->code,
            'description' => $request->name,
            'sequence' => $nextSequence,
        ]);

        return response()->json([
            'message' => 'Subtopik berjaya disimpan.',
            'subtopic' => $subtopic,
        ]);
    }

    /**
     * Update topic
     */
    public function updateTopic(Request $request, Topic $topic): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'theme' => 'sometimes|string|max:255',
        ]);

        $topic->update($request->only(['title', 'theme']));

        return response()->json([
            'message' => 'Topik berjaya dikemaskini.',
            'topic' => $topic,
        ]);
    }

    /**
     * Update subtopic
     */
    public function updateSubtopic(Request $request, Subtopic $subtopic): JsonResponse
    {
        $request->validate([
            'description' => 'sometimes|string|max:500',
            'tp_max' => 'sometimes|integer|min:1|max:6',
        ]);

        $subtopic->update($request->only(['description', 'tp_max']));

        return response()->json([
            'message' => 'Subtopik berjaya dikemaskini.',
            'subtopic' => $subtopic,
        ]);
    }

    /**
     * Delete topic (only if no evaluations)
     */
    public function deleteTopic(Topic $topic): JsonResponse
    {
        // Check if any subtopics have evaluations
        $hasEvaluations = \App\Models\Evaluation::whereIn('subtopic_id', $topic->subtopics()->pluck('id'))->exists();
        
        if ($hasEvaluations) {
            return response()->json([
                'message' => 'Tidak boleh padam topik ini kerana sudah ada rekod penilaian murid.',
            ], 422);
        }

        $topic->subtopics()->delete();
        $topic->delete();

        return response()->json([
            'message' => 'Topik berjaya dipadam.',
        ]);
    }

    /**
     * Delete subtopic (only if no evaluations)
     */
    public function deleteSubtopic(Subtopic $subtopic): JsonResponse
    {
        $hasEvaluations = \App\Models\Evaluation::where('subtopic_id', $subtopic->id)->exists();
        
        if ($hasEvaluations) {
            return response()->json([
                'message' => 'Tidak boleh padam subtopik ini kerana sudah ada rekod penilaian murid.',
            ], 422);
        }

        $subtopic->delete();

        return response()->json([
            'message' => 'Subtopik berjaya dipadam.',
        ]);
    }

    /**
     * Preview DSKP from CSV file before import
     * Returns parsed data for user verification
     */
    public function previewCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $lines = explode("\n", $content);
        
        $preview = [
            'sections' => [],
            'topics' => [],
            'subtopics' => [],
            'raw_rows' => [],
            'errors' => [],
        ];
        
        $sectionsSeen = [];
        $topicsSeen = [];
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip header row
            if ($index === 0 && (str_contains(strtolower($line), 'theme') || str_contains(strtolower($line), 'tema') || str_contains(strtolower($line), 'topic'))) continue;
            
            // Check for tab separator
            if (!str_contains($line, "\t")) {
                $preview['errors'][] = "Baris " . ($index + 1) . ": Tiada tab separator ditemui. Pastikan fail menggunakan format tab-separated.";
                continue;
            }
            
            $parts = explode("\t", $line);
            
            if (count($parts) < 4) {
                $preview['errors'][] = "Baris " . ($index + 1) . ": Kurang daripada 4 lajur (hanya " . count($parts) . " lajur)";
                continue;
            }
            
            $theme = trim($parts[0] ?? '');
            $titleRaw = trim($parts[1] ?? '');
            $skRaw = trim($parts[2] ?? '');
            $spRaw = trim($parts[3] ?? '');
            
            // Parse title
            preg_match('/^([\d.]+)\s+(.+)$/', $titleRaw, $titleMatches);
            $titleCode = $titleMatches[1] ?? '';
            $titleName = $titleMatches[2] ?? $titleRaw;
            
            // Parse SK
            preg_match('/^([\d.]+)\s+(.+)$/', $skRaw, $skMatches);
            $skCode = $skMatches[1] ?? '';
            $skName = $skMatches[2] ?? $skRaw;
            
            // Parse SP
            preg_match('/^([\d.]+)\s+(.+)$/', $spRaw, $spMatches);
            $spCode = $spMatches[1] ?? '';
            $spName = $spMatches[2] ?? $spRaw;
            
            // Track unique sections
            $sectionKey = "{$titleCode}";
            if (!empty($titleCode) && !isset($sectionsSeen[$sectionKey])) {
                $sectionsSeen[$sectionKey] = true;
                $preview['sections'][] = [
                    'theme' => $theme,
                    'title_code' => $titleCode,
                    'title_name' => $titleName,
                ];
            }
            
            // Track unique topics
            $topicKey = "{$skCode}";
            if (!empty($skCode) && !isset($topicsSeen[$topicKey])) {
                $topicsSeen[$topicKey] = true;
                $preview['topics'][] = [
                    'code' => $skCode,
                    'name' => $skName,
                    'section' => $titleCode,
                ];
            }
            
            // Track all subtopics
            if (!empty($spCode)) {
                $preview['subtopics'][] = [
                    'code' => $spCode,
                    'name' => $spName,
                    'topic' => $skCode,
                ];
            }
            
            // First 20 raw rows for preview table
            if (count($preview['raw_rows']) < 20) {
                $preview['raw_rows'][] = [
                    'row' => $index + 1,
                    'theme' => $theme,
                    'title' => $titleRaw,
                    'topic' => $skRaw,
                    'subtopic' => $spRaw,
                ];
            }
        }
        
        $preview['summary'] = [
            'total_sections' => count($preview['sections']),
            'total_topics' => count($preview['topics']),
            'total_subtopics' => count($preview['subtopics']),
            'total_errors' => count($preview['errors']),
        ];

        return response()->json($preview);
    }

    /**
     * Import DSKP from tab-separated file
     * Format: theme | title | topic (SK) | subtopic (SP)
     * Example: SAINS HAYAT | 2.0 MANUSIA | 2.1 Sistem Rangka | 2.1.1 Description
     */
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year' => 'sometimes|integer',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $academicYear = $request->academic_year ?? (int) date('Y');

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $lines = explode("\n", $content);
        
        $imported = ['sections' => 0, 'topics' => 0, 'subtopics' => 0];
        $sectionCache = [];
        $topicCache = [];
        $currentSection = null;
        $currentTopic = null;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Skip header row if present
            if ($index === 0 && (str_contains(strtolower($line), 'theme') || str_contains(strtolower($line), 'tema') || str_contains(strtolower($line), 'topic'))) continue;
            
            // Require tab separator
            if (!str_contains($line, "\t")) continue;
            
            $parts = explode("\t", $line);
            if (count($parts) < 4) continue;
            
            // Column mapping (4 columns):
            // 0: Theme (e.g., "SAINS HAYAT")
            // 1: Title (e.g., "2.0 MANUSIA")
            // 2: Topic/SK (e.g., "2.1 Sistem Rangka")
            // 3: Subtopic/SP (e.g., "2.1.1 Memerihalkan...")
            
            $theme = trim($parts[0] ?? '');
            $titleRaw = trim($parts[1] ?? '');
            $skRaw = trim($parts[2] ?? '');
            $spRaw = trim($parts[3] ?? '');
            
            // Parse title_code and title_name from "1.0 KEMAHIRAN SAINTIFIK"
            preg_match('/^([\d.]+)\s+(.+)$/', $titleRaw, $titleMatches);
            $titleCode = $titleMatches[1] ?? '0.0';
            $titleName = $titleMatches[2] ?? $titleRaw;
            
            // Create or get Section
            $sectionKey = "{$subject->id}_{$academicYear}_{$titleCode}";
            if (!isset($sectionCache[$sectionKey])) {
                $section = Section::firstOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'academic_year' => $academicYear,
                        'title_code' => $titleCode,
                    ],
                    [
                        'theme' => $theme,
                        'title_name' => $titleName,
                        'sequence' => count($sectionCache) + 1,
                    ]
                );
                $sectionCache[$sectionKey] = $section;
                if ($section->wasRecentlyCreated) {
                    $imported['sections']++;
                }
            }
            $currentSection = $sectionCache[$sectionKey];
            
            // Parse SK (Standard Kandungan) - Topic
            if (!empty($skRaw)) {
                preg_match('/^([\d.]+)\s+(.+)$/', $skRaw, $skMatches);
                $topicCode = $skMatches[1] ?? '';
                $topicName = $skMatches[2] ?? $skRaw;
                
                if (!empty($topicCode)) {
                    $topicKey = "{$currentSection->id}_{$topicCode}";
                    if (!isset($topicCache[$topicKey])) {
                        $topic = Topic::firstOrCreate(
                            [
                                'section_id' => $currentSection->id,
                                'standard_kandungan' => $topicCode,
                            ],
                            [
                                'subject_id' => $subject->id,
                                'title' => $topicName,
                                'theme' => "{$theme} > {$titleRaw}",
                                'year' => $academicYear,
                                'sequence' => count($topicCache) + 1,
                            ]
                        );
                        $topicCache[$topicKey] = $topic;
                        if ($topic->wasRecentlyCreated) {
                            $imported['topics']++;
                        }
                    }
                    $currentTopic = $topicCache[$topicKey];
                }
            }
            
            // Parse SP (Standard Pembelajaran) - Subtopic
            if ($currentTopic && !empty($spRaw)) {
                preg_match('/^([\d.]+)\s+(.+)$/', $spRaw, $spMatches);
                $subtopicCode = $spMatches[1] ?? '';
                $subtopicDesc = $spMatches[2] ?? $spRaw;
                
                if (!empty($subtopicCode)) {
                    $subtopic = Subtopic::firstOrCreate(
                        [
                            'topic_id' => $currentTopic->id,
                            'code' => $subtopicCode,
                        ],
                        [
                            'description' => $subtopicDesc,
                            'sequence' => Subtopic::where('topic_id', $currentTopic->id)->count() + 1,
                            'tp_max' => 6,
                        ]
                    );
                    if ($subtopic->wasRecentlyCreated) {
                        $imported['subtopics']++;
                    }
                }
            }
        }

        return response()->json([
            'message' => "Import berjaya: {$imported['sections']} seksyen, {$imported['topics']} topik, {$imported['subtopics']} subtopik.",
            'imported' => $imported,
        ]);
    }

    /**
     * Download example CSV template
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dskp_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header with TP columns
            fputcsv($file, ['topic_code', 'topic_name', 'subtopic_code', 'subtopic_description', 'tp_max', 'tp1', 'tp2', 'tp3', 'tp4', 'tp5', 'tp6']);
            
            // Example: 4-level TP
            fputcsv($file, ['1.0', 'Kemahiran Mendengar', '1.1', 'Mendengar dan respons', '4', 'Boleh mendengar', 'Boleh memahami', 'Boleh bertindak balas', 'Boleh mengaplikasi', '', '']);
            fputcsv($file, ['1.0', 'Kemahiran Mendengar', '1.2', 'Mendengar dengan aktif', '4', 'Tahap asas', 'Tahap memuaskan', 'Tahap baik', 'Tahap cemerlang', '', '']);
            
            // Example: 6-level TP
            fputcsv($file, ['2.0', 'Kemahiran Membaca', '2.1', 'Membaca pelbagai bahan', '6', 'Boleh membaca', 'Boleh memahami', 'Boleh menganalisis', 'Boleh mentafsir', 'Boleh menilai', 'Boleh mencipta']);
            fputcsv($file, ['2.0', 'Kemahiran Membaca', '2.2', 'Membaca dengan lancar', '6', 'Asas', 'Memuaskan', 'Baik', 'Sangat baik', 'Cemerlang', 'Terpuji']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

