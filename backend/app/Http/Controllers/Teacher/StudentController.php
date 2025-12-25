<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * List students in a class
     */
    public function index(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $students = $class->students()->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'school_name' => $student->school_name,
            ];
        });

        return response()->json($students);
    }

    /**
     * Add student to class (find or create by IC)
     */
    public function store(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'ic' => 'required|string|min:12|max:12',
            'name' => 'required|string|max:255',
            'school_name' => 'nullable|string|max:255',
        ]);

        // Find or create student by IC
        $student = Student::findOrCreateByIc(
            $request->ic,
            $request->name,
            $request->school_name
        );

        // Attach to class if not already
        if (!$class->students()->where('student_id', $student->id)->exists()) {
            $class->students()->attach($student->id);
        }

        return response()->json([
            'id' => $student->id,
            'name' => $student->name,
            'school_name' => $student->school_name,
        ], 201);
    }

    /**
     * Lookup student by IC (for auto-fill when adding)
     */
    public function lookupByIc(Request $request): JsonResponse
    {
        $request->validate([
            'ic' => 'required|string|min:12|max:12',
        ]);

        $icHash = hash('sha256', $request->ic);
        $student = \App\Models\Student::where('ic_hash', $icHash)->first();

        if ($student) {
            return response()->json([
                'found' => true,
                'id' => $student->id,
                'name' => $student->name,
                'school_name' => $student->school_name,
            ]);
        }

        return response()->json([
            'found' => false,
        ]);
    }

    /**
     * Remove student from class
     */
    public function destroy(Request $request, ClassRoom $class, Student $student): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $class->students()->detach($student->id);

        return response()->json(['message' => 'Murid dikeluarkan dari kelas.']);
    }
}
