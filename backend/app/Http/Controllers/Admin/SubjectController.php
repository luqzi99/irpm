<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Topic;
use App\Models\Subtopic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    /**
     * List all subjects with topics count
     */
    public function index(): JsonResponse
    {
        $subjects = Subject::withCount('topics')->get();

        return response()->json($subjects);
    }

    /**
     * Get subject with topics and subtopics
     */
    public function show(Subject $subject): JsonResponse
    {
        $subject->load(['topics.subtopics']);

        return response()->json($subject);
    }

    /**
     * Get topics for a subject (with subtopics)
     * For teacher TP input - auto uses current academic year
     */
    public function topics(Request $request, Subject $subject): JsonResponse
    {
        $academicYear = $request->query('year', date('Y'));
        
        // Get sections for current academic year with topics and subtopics
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
            ->get();
        
        // If sections exist, flatten topics from sections
        if ($sections->isNotEmpty()) {
            $topics = $sections->flatMap(function($section) {
                return $section->topics->map(function($topic) use ($section) {
                    return [
                        'id' => $topic->id,
                        'title' => $topic->title,
                        'standard_kandungan' => $topic->standard_kandungan,
                        'section_title' => "{$section->title_code} {$section->title_name}",
                        'subtopics' => $topic->subtopics,
                    ];
                });
            });
        } else {
            // Fallback: Get topics directly (for data before sections migration)
            // Use simple ordering to avoid cast errors when standard_kandungan contains text
            $topics = Topic::where('subject_id', $subject->id)
                ->with(['subtopics' => function($sq) {
                    $sq->orderBy('sequence')->orderBy('id');
                }])
                ->orderBy('sequence')
                ->orderBy('id')
                ->get()
                ->map(fn($topic) => [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'standard_kandungan' => $topic->standard_kandungan,
                    'section_title' => null,
                    'subtopics' => $topic->subtopics,
                ]);
        }

        return response()->json($topics);
    }
}
