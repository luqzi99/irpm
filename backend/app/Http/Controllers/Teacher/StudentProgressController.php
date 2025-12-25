<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Evaluation;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentProgressController extends Controller
{
    /**
     * Get detailed progress for a specific student
     */
    public function show(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['topics.subtopics'])->find($request->subject_id);
        $teacher = $request->user();

        // Get all evaluations for this student in this subject
        $evaluations = Evaluation::where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->where('subject_id', $request->subject_id)
            ->get()
            ->keyBy('subtopic_id');

        // Build topic-based progress
        $topicsProgress = [];
        $totalTp = 0;
        $totalCount = 0;

        foreach ($subject->topics as $topic) {
            $topicTpSum = 0;
            $topicCount = 0;
            $subtopicsData = [];

            foreach ($topic->subtopics as $subtopic) {
                $eval = $evaluations->get($subtopic->id);
                $tp = $eval?->tp;

                $subtopicsData[] = [
                    'id' => $subtopic->id,
                    'code' => $subtopic->code,
                    'description' => $subtopic->description,
                    'tp' => $tp,
                    'date' => $eval?->evaluation_date?->format('d/m/Y'),
                ];

                if ($tp) {
                    $topicTpSum += $tp;
                    $topicCount++;
                    $totalTp += $tp;
                    $totalCount++;
                }
            }

            $topicsProgress[] = [
                'id' => $topic->id,
                'sequence' => $topic->sequence,
                'title' => $topic->title,
                'average_tp' => $topicCount > 0 ? round($topicTpSum / $topicCount, 1) : null,
                'subtopics' => $subtopicsData,
            ];
        }

        // Calculate overall stats
        $overallTp = $totalCount > 0 ? round($totalTp / $totalCount, 1) : null;
        $performanceLevel = $this->getPerformanceLevel($overallTp);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
            ],
            'summary' => [
                'total_evaluations' => $totalCount,
                'overall_tp' => $overallTp,
                'performance_level' => $performanceLevel,
            ],
            'topics' => $topicsProgress,
        ]);
    }

    /**
     * Get performance level in Malay
     */
    private function getPerformanceLevel(?float $tp): string
    {
        if (!$tp) return 'Belum dinilai';
        if ($tp >= 5.5) return 'Cemerlang';
        if ($tp >= 4.5) return 'Baik';
        if ($tp >= 3.5) return 'Memuaskan';
        if ($tp >= 2.5) return 'Sederhana';
        if ($tp >= 1.5) return 'Lemah';
        return 'Sangat Lemah';
    }
}
