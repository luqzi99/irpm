<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
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
     * Get topics for a subject/year
     */
    public function topics(Request $request, Subject $subject): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer',
        ]);

        $topics = Topic::where('subject_id', $subject->id)
            ->where('year', $request->year)
            ->with('subtopics')
            ->orderBy('sequence')
            ->get();

        return response()->json($topics);
    }
}
