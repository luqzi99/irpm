<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeachingSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    /**
     * Get all teaching schedules for current teacher
     */
    public function index(Request $request): JsonResponse
    {
        $schedules = TeachingSchedule::where('teacher_id', $request->user()->id)
            ->with(['classRoom', 'subject'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'day_of_week' => $s->day_of_week,
                'day_name' => $s->day_name,
                'start_time' => $s->start_time->format('H:i'),
                'class_id' => $s->class_id,
                'class_name' => $s->classRoom->name,
                'subject_id' => $s->subject_id,
                'subject_name' => $s->subject->name,
            ]);

        return response()->json($schedules);
    }

    /**
     * Create a new teaching schedule
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
        ]);

        $schedule = TeachingSchedule::create([
            'teacher_id' => $request->user()->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
        ]);

        $schedule->load(['classRoom', 'subject']);

        return response()->json([
            'id' => $schedule->id,
            'day_of_week' => $schedule->day_of_week,
            'day_name' => $schedule->day_name,
            'start_time' => $schedule->start_time->format('H:i'),
            'class_id' => $schedule->class_id,
            'class_name' => $schedule->classRoom->name,
            'subject_id' => $schedule->subject_id,
            'subject_name' => $schedule->subject->name,
        ], 201);
    }

    /**
     * Delete a teaching schedule
     */
    public function destroy(Request $request, TeachingSchedule $schedule): JsonResponse
    {
        if ($schedule->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $schedule->delete();

        return response()->json(['message' => 'Jadual berjaya dipadam.']);
    }

    /**
     * Get current class based on day and time
     * Returns the schedule entry that matches current day and is closest to current time
     */
    public function current(Request $request): JsonResponse
    {
        $now = now();
        $currentDay = $now->dayOfWeekIso; // 1=Monday, 7=Sunday
        $currentTime = $now->format('H:i');

        // Get today's schedules for this teacher
        $todaySchedules = TeachingSchedule::where('teacher_id', $request->user()->id)
            ->where('day_of_week', $currentDay)
            ->with(['classRoom', 'subject'])
            ->orderBy('start_time')
            ->get();

        if ($todaySchedules->isEmpty()) {
            return response()->json(null);
        }

        // Find the current or most recent class (within 2 hours)
        $currentSchedule = null;
        foreach ($todaySchedules as $schedule) {
            $scheduleTime = $schedule->start_time->format('H:i');
            
            // Check if this class is currently happening or just finished (within 2 hours)
            $scheduleCarbon = $now->copy()->setTimeFromTimeString($scheduleTime);
            $diffMinutes = $now->diffInMinutes($scheduleCarbon, false);
            
            // Class started up to 2 hours ago or starts within 30 minutes
            if ($diffMinutes >= -120 && $diffMinutes <= 30) {
                $currentSchedule = $schedule;
                break;
            }
        }

        if (!$currentSchedule) {
            // Fallback: get the nearest upcoming class
            foreach ($todaySchedules as $schedule) {
                $scheduleTime = $schedule->start_time->format('H:i');
                if ($scheduleTime >= $currentTime) {
                    $currentSchedule = $schedule;
                    break;
                }
            }
        }

        if (!$currentSchedule) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $currentSchedule->id,
            'class_id' => $currentSchedule->class_id,
            'class_name' => $currentSchedule->classRoom->name,
            'subject_id' => $currentSchedule->subject_id,
            'subject_name' => $currentSchedule->subject->name,
            'start_time' => $currentSchedule->start_time->format('H:i'),
        ]);
    }

    /**
     * Get unique subjects that teacher is teaching
     */
    public function mySubjects(Request $request): JsonResponse
    {
        $subjects = TeachingSchedule::where('teacher_id', $request->user()->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'level' => $s->level,
            ]);

        return response()->json($subjects);
    }
}
