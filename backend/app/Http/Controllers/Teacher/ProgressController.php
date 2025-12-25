<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Evaluation;
use App\Models\Subject;
use App\Models\Subtopic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgressController extends Controller
{
    /**
     * Get progress grid for a class (GitHub-style visualization)
     * Returns students x subtopics matrix with TP values
     */
    public function show(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['topics.subtopics'])->find($request->subject_id);
        $students = $class->students;

        // Collect all subtopics for this subject
        $subtopics = [];
        foreach ($subject->topics as $topic) {
            foreach ($topic->subtopics as $subtopic) {
                $subtopics[] = [
                    'id' => $subtopic->id,
                    'code' => $subtopic->code,
                    'description' => $subtopic->description,
                    'topic_title' => $topic->title,
                ];
            }
        }

        // Get all evaluations for students in this class for this subject
        $studentIds = $students->pluck('id');
        $evaluations = Evaluation::where('teacher_id', $request->user()->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        // Build students array with evaluations keyed by subtopic_id
        $studentsData = [];
        foreach ($students as $student) {
            $studentEvals = $evaluations->get($student->id, collect());
            
            // Key evaluations by subtopic_id for easy lookup
            $evalsBySubtopic = [];
            foreach ($studentEvals as $eval) {
                // Keep the latest evaluation per subtopic
                if (!isset($evalsBySubtopic[$eval->subtopic_id]) || 
                    $eval->evaluation_date > $evalsBySubtopic[$eval->subtopic_id]['date']) {
                    $evalsBySubtopic[$eval->subtopic_id] = [
                        'tp' => $eval->tp,
                        'date' => $eval->evaluation_date,
                    ];
                }
            }

            $studentsData[] = [
                'id' => $student->id,
                'name' => $student->name,
                'evaluations' => $evalsBySubtopic,
            ];
        }

        return response()->json([
            'subtopics' => $subtopics,
            'students' => $studentsData,
        ]);
    }

    /**
     * Get summary stats for a class
     */
    public function summary(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['topics.subtopics'])->find($request->subject_id);
        $studentIds = $class->students()->pluck('students.id');

        // Count total subtopics
        $totalSubtopics = 0;
        foreach ($subject->topics as $topic) {
            $totalSubtopics += $topic->subtopics->count();
        }

        // Get all evaluations for this class/subject
        $evaluations = Evaluation::where('teacher_id', $request->user()->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $studentIds)
            ->get();

        // Calculate stats
        $tpDistribution = $evaluations->groupBy('tp')->map->count();
        $avgTp = $evaluations->avg('tp');
        $totalEvaluations = $evaluations->count();
        $studentsEvaluated = $evaluations->pluck('student_id')->unique()->count();
        $totalStudents = $studentIds->count();
        
        // Calculate completion: unique (student, subtopic) pairs evaluated / possible total
        $possibleTotal = $totalStudents * $totalSubtopics;
        $uniqueEvaluations = $evaluations->groupBy(fn($e) => $e->student_id . '-' . $e->subtopic_id)->count();
        $completionPercentage = $possibleTotal > 0 
            ? round(($uniqueEvaluations / $possibleTotal) * 100) 
            : 0;

        return response()->json([
            'total_evaluations' => $totalEvaluations,
            'students_evaluated' => $studentsEvaluated,
            'total_students' => $totalStudents,
            'total_subtopics' => $totalSubtopics,
            'average_tp' => $avgTp ? round($avgTp, 1) : null,
            'tp_distribution' => $tpDistribution,
            'completion_percentage' => $completionPercentage,
        ]);
    }
}
