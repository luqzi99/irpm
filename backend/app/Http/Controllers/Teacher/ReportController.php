<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Evaluation;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    /**
     * Generate class report data (for PDF/print)
     */
    public function classReport(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['topics.subtopics'])->find($request->subject_id);
        $students = $class->students;
        $teacher = $request->user();

        // Get all evaluations
        $studentIds = $students->pluck('id');
        $evaluations = Evaluation::where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        // Build report data
        $reportData = [];
        foreach ($students as $student) {
            $studentEvals = $evaluations->get($student->id, collect());
            
            // Calculate average TP
            $avgTp = $studentEvals->avg('tp');
            
            // Get latest evaluation with comment
            $latestWithComment = $studentEvals
                ->filter(fn($e) => !empty($e->auto_comment))
                ->sortByDesc('evaluation_date')
                ->first();

            $reportData[] = [
                'name' => $student->name,
                'evaluations_count' => $studentEvals->count(),
                'average_tp' => $avgTp ? round($avgTp, 1) : null,
                'tp_level' => $avgTp ? $this->getTpLevel($avgTp) : 'Belum dinilai',
                'comment' => $latestWithComment?->auto_comment ?? 'Tiada ulasan.',
            ];
        }

        // Sort by name
        usort($reportData, fn($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json([
            'report' => [
                'class_name' => $class->name,
                'year' => $class->year,
                'subject' => $subject->name,
                'teacher' => $teacher->name,
                'generated_at' => now()->format('d/m/Y H:i'),
                'total_students' => count($reportData),
            ],
            'students' => $reportData,
        ]);
    }

    /**
     * Export to CSV (Excel-compatible)
     */
    public function exportCsv(Request $request, ClassRoom $class): Response
    {
        if ($class->teacher_id !== $request->user()->id) {
            abort(403, 'Tidak dibenarkan.');
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['topics.subtopics'])->find($request->subject_id);
        $students = $class->students;
        $teacher = $request->user();

        // Collect subtopics
        $subtopics = [];
        foreach ($subject->topics as $topic) {
            foreach ($topic->subtopics as $subtopic) {
                $subtopics[] = $subtopic;
            }
        }

        // Get evaluations
        $studentIds = $students->pluck('id');
        $evaluations = Evaluation::where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        // Build CSV
        $csv = [];
        
        // BOM for Excel UTF-8 compatibility
        $bom = "\xEF\xBB\xBF";
        
        // Header row
        $header = ['Bil', 'Nama Murid'];
        foreach ($subtopics as $st) {
            $header[] = $st->code;
        }
        $header[] = 'Purata TP';
        $header[] = 'Tahap';
        $csv[] = implode(',', $header);

        // Data rows
        $bil = 1;
        foreach ($students->sortBy('name') as $student) {
            $studentEvals = $evaluations->get($student->id, collect());
            
            $row = [$bil++, '"' . $student->name . '"'];
            
            foreach ($subtopics as $st) {
                $eval = $studentEvals->firstWhere('subtopic_id', $st->id);
                $row[] = $eval?->tp ?? '-';
            }
            
            $avgTp = $studentEvals->avg('tp');
            $row[] = $avgTp ? round($avgTp, 1) : '-';
            $row[] = $avgTp ? $this->getTpLevel($avgTp) : 'Belum dinilai';
            
            $csv[] = implode(',', $row);
        }

        $content = $bom . implode("\n", $csv);
        $filename = "Laporan_{$class->name}_{$subject->name}_" . now()->format('Ymd') . ".csv";

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get TP level description in Malay
     */
    private function getTpLevel(float $tp): string
    {
        if ($tp >= 5.5) return 'Cemerlang';
        if ($tp >= 4.5) return 'Baik';
        if ($tp >= 3.5) return 'Memuaskan';
        if ($tp >= 2.5) return 'Sederhana';
        if ($tp >= 1.5) return 'Lemah';
        return 'Sangat Lemah';
    }
}
