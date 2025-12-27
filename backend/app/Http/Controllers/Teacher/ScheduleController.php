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
                'end_time' => $s->end_time?->format('H:i'),
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
        $user = $request->user();
        
        // Check subscription limit
        if (!$user->canCreateSchedule()) {
            $limits = $user->getPlanLimits();
            return response()->json([
                'message' => "Had jadual dicapai ({$limits['schedules']} jadual). Sila naik taraf langganan.",
                'subscription_limit' => true,
            ], 403);
        }

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        $schedule = TeachingSchedule::create([
            'teacher_id' => $user->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $schedule->load(['classRoom', 'subject']);

        return response()->json([
            'id' => $schedule->id,
            'day_of_week' => $schedule->day_of_week,
            'day_name' => $schedule->day_name,
            'start_time' => $schedule->start_time->format('H:i'),
            'end_time' => $schedule->end_time?->format('H:i'),
            'class_id' => $schedule->class_id,
            'class_name' => $schedule->classRoom->name,
            'subject_id' => $schedule->subject_id,
            'subject_name' => $schedule->subject->name,
        ], 201);
    }

    /**
     * Update a teaching schedule
     */
    public function update(Request $request, TeachingSchedule $schedule): JsonResponse
    {
        if ($schedule->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak dibenarkan.'], 403);
        }

        $request->validate([
            'class_id' => 'sometimes|exists:classes,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'day_of_week' => 'sometimes|integer|min:1|max:7',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        $schedule->update($request->only(['class_id', 'subject_id', 'day_of_week', 'start_time', 'end_time']));
        $schedule->load(['classRoom', 'subject']);

        return response()->json([
            'id' => $schedule->id,
            'day_of_week' => $schedule->day_of_week,
            'day_name' => $schedule->day_name,
            'start_time' => $schedule->start_time->format('H:i'),
            'end_time' => $schedule->end_time?->format('H:i'),
            'class_id' => $schedule->class_id,
            'class_name' => $schedule->classRoom->name,
            'subject_id' => $schedule->subject_id,
            'subject_name' => $schedule->subject->name,
        ]);
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
     * Returns the schedule entry that matches current day and time window
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

        $currentSchedule = null;
        
        // Find schedule where current time falls within start-end range
        foreach ($todaySchedules as $schedule) {
            $startTime = $schedule->start_time->format('H:i');
            $endTime = $schedule->end_time?->format('H:i');
            
            // If has end_time, check if current time is within range
            if ($endTime) {
                if ($currentTime >= $startTime && $currentTime <= $endTime) {
                    $currentSchedule = $schedule;
                    break;
                }
            } else {
                // No end_time: check if within 2 hours of start
                $scheduleCarbon = $now->copy()->setTimeFromTimeString($startTime);
                $diffMinutes = $now->diffInMinutes($scheduleCarbon, false);
                
                if ($diffMinutes >= -120 && $diffMinutes <= 30) {
                    $currentSchedule = $schedule;
                    break;
                }
            }
        }

        // Fallback: get the nearest upcoming class
        if (!$currentSchedule) {
            foreach ($todaySchedules as $schedule) {
                $startTime = $schedule->start_time->format('H:i');
                if ($startTime >= $currentTime) {
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
            'end_time' => $currentSchedule->end_time?->format('H:i'),
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
