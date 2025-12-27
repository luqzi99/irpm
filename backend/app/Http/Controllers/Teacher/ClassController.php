<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\TeachingSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    /**
     * List all classes for current teacher
     */
    public function index(Request $request): JsonResponse
    {
        $classes = ClassRoom::where('teacher_id', $request->user()->id)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return response()->json($classes);
    }

    /**
     * Get today's classes
     */
    public function today(Request $request): JsonResponse
    {
        $schedules = TeachingSchedule::with(['classRoom', 'subject'])
            ->where('teacher_id', $request->user()->id)
            ->today()
            ->orderBy('start_time')
            ->get();

        return response()->json($schedules);
    }

    /**
     * Create a new class
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check subscription limit
        if (!$user->canCreateClass()) {
            $limits = $user->getPlanLimits();
            return response()->json([
                'message' => "Had kelas dicapai ({$limits['classes']} kelas). Sila naik taraf langganan.",
                'subscription_limit' => true,
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $class = ClassRoom::create([
            'teacher_id' => $user->id,
            'name' => $request->name,
            'year' => $request->year,
        ]);

        return response()->json($class, 201);
    }

    /**
     * Get class with students
     */
    public function show(Request $request, ClassRoom $class): JsonResponse
    {
        // Ensure teacher owns this class
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $class->load('students');

        return response()->json($class);
    }

    /**
     * Update class
     */
    public function update(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'year' => 'sometimes|integer|min:2020|max:2030',
        ]);

        $class->update($request->only(['name', 'year']));

        return response()->json($class);
    }

    /**
     * Delete class
     */
    public function destroy(Request $request, ClassRoom $class): JsonResponse
    {
        if ($class->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $class->delete();

        return response()->json(['message' => 'Kelas dipadam.']);
    }
}
