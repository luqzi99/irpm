<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Evaluation;
use App\Models\AuditLog;
use App\Models\AssessmentMethod;
use App\Models\CommentTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    /**
     * Bulk store evaluations (TP Quick Input)
     * This is the HEART of iRPM - 1 tap = 1 record
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'subtopic_id' => 'required|exists:subtopics,id',
            'assessment_method_id' => 'nullable|exists:assessment_methods,id',
            'evaluations' => 'required|array|min:1',
            'evaluations.*.student_id' => 'required|exists:students,id',
            'evaluations.*.tp' => 'required|integer|min:1|max:6',
        ]);

        $user = $request->user();
        $class = ClassRoom::find($request->class_id);

        // Verify ownership
        if ($class->teacher_id !== $user->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        // Get subtopic to find topic_id
        $subtopic = \App\Models\Subtopic::with('topic')->find($request->subtopic_id);

        // Get auto-comment template if assessment method provided
        $commentTemplate = null;
        if ($request->assessment_method_id) {
            $method = AssessmentMethod::find($request->assessment_method_id);
        }

        $createdEvaluations = [];
        $today = now()->toDateString();

        foreach ($request->evaluations as $evalData) {
            // Generate auto comment
            $autoComment = null;
            if ($request->assessment_method_id) {
                $template = CommentTemplate::where('assessment_method_id', $request->assessment_method_id)
                    ->where('tp', $evalData['tp'])
                    ->first();
                if ($template) {
                    $autoComment = $template->template_text;
                }
            }

            $evaluation = Evaluation::create([
                'student_id' => $evalData['student_id'],
                'teacher_id' => $user->id,
                'subject_id' => $request->subject_id,
                'topic_id' => $subtopic->topic_id,
                'subtopic_id' => $request->subtopic_id,
                'assessment_method_id' => $request->assessment_method_id,
                'tp' => $evalData['tp'],
                'auto_comment' => $autoComment,
                'custom_comment' => $evalData['custom_comment'] ?? null,
                'evaluation_date' => $today,
            ]);

            $createdEvaluations[] = $evaluation;

            // Audit log
            AuditLog::log($user->id, AuditLog::ACTION_RECORD_TP, 'Evaluation', $evaluation->id);
        }

        return response()->json([
            'message' => 'TP berjaya disimpan.',
            'count' => count($createdEvaluations),
        ], 201);
    }

    /**
     * Get evaluations for a class/subject/subtopic
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'subtopic_id' => 'nullable|exists:subtopics,id',
        ]);

        $user = $request->user();
        $class = ClassRoom::find($request->class_id);

        if ($class->teacher_id !== $user->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        // Get all students in class
        $studentIds = $class->students()->pluck('students.id');

        $query = Evaluation::where('teacher_id', $user->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $studentIds);

        if ($request->subtopic_id) {
            $query->where('subtopic_id', $request->subtopic_id);
        }

        $evaluations = $query->with(['student', 'subtopic'])
            ->orderBy('evaluation_date', 'desc')
            ->get()
            ->map(function ($eval) {
                return [
                    'id' => $eval->id,
                    'student_id' => $eval->student_id,
                    'student_name' => $eval->student->name,
                    'subtopic_code' => $eval->subtopic->code,
                    'tp' => $eval->tp,
                    'tp_color' => $eval->tp_color,
                    'comment' => $eval->comment,
                    'evaluation_date' => $eval->evaluation_date->format('Y-m-d'),
                ];
            });

        return response()->json($evaluations);
    }

    /**
     * Get latest TP for each student in a subtopic (for quick input view)
     */
    public function latestBySubtopic(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'subtopic_id' => 'required|exists:subtopics,id',
        ]);

        $user = $request->user();
        $class = ClassRoom::with('students')->find($request->class_id);

        if ($class->teacher_id !== $user->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        // Get latest evaluation for each student
        $result = $class->students->map(function ($student) use ($request, $user) {
            $latestEval = Evaluation::where('teacher_id', $user->id)
                ->where('student_id', $student->id)
                ->where('subject_id', $request->subject_id)
                ->where('subtopic_id', $request->subtopic_id)
                ->orderBy('evaluation_date', 'desc')
                ->first();

            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'tp' => $latestEval?->tp,
                'tp_color' => $latestEval?->tp_color,
            ];
        });

        return response()->json($result);
    }
}
