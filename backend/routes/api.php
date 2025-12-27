<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\StudentController;
use App\Http\Controllers\Teacher\EvaluationController;
use App\Http\Controllers\Teacher\ProgressController;
use App\Http\Controllers\Teacher\ReportController;
use App\Http\Controllers\Teacher\StudentProgressController;
use App\Http\Controllers\Teacher\ScheduleController;
use App\Http\Controllers\Teacher\ExcelReportController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

/*
|--------------------------------------------------------------------------
| API Routes - iRPM
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'iRPM API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Excel export - outside auth middleware (handles token manually for browser download)
Route::get('/teacher/classes/{class}/export-excel', [ExcelReportController::class, 'download']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // ============ TEACHER ROUTES ============
    Route::prefix('teacher')->group(function () {
        
        // Classes
        Route::get('/classes', [ClassController::class, 'index']);
        Route::post('/classes', [ClassController::class, 'store']);
        Route::get('/classes/{class}', [ClassController::class, 'show']);
        Route::put('/classes/{class}', [ClassController::class, 'update']);
        Route::delete('/classes/{class}', [ClassController::class, 'destroy']);
        Route::get('/today-classes', [ClassController::class, 'today']);
        
        // Schedules
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);
        Route::get('/schedules/current', [ScheduleController::class, 'current']);
        Route::get('/schedules/my-subjects', [ScheduleController::class, 'mySubjects']);
        
        // Students (nested under class)
        Route::get('/classes/{class}/students', [StudentController::class, 'index']);
        Route::post('/classes/{class}/students', [StudentController::class, 'store']);
        Route::delete('/classes/{class}/students/{student}', [StudentController::class, 'destroy']);
        
        // Student lookup by IC (for auto-fill)
        Route::get('/students/lookup', [StudentController::class, 'lookupByIc']);
        
        // Evaluations (TP Quick Input - HEART OF IRPM)
        Route::post('/evaluations', [EvaluationController::class, 'store']);
        Route::get('/evaluations', [EvaluationController::class, 'index']);
        Route::get('/evaluations/latest', [EvaluationController::class, 'latestBySubtopic']);
        
        // Progress
        Route::get('/classes/{class}/progress', [ProgressController::class, 'show']);
        Route::get('/classes/{class}/summary', [ProgressController::class, 'summary']);
        
        // Reports
        Route::get('/classes/{class}/report', [ReportController::class, 'classReport']);
        Route::get('/classes/{class}/export-csv', [ReportController::class, 'exportCsv']);
        // Note: export-excel is outside auth middleware (handles token manually for download)
        
        // Student Progress (individual student detail)
        Route::get('/students/{student}/progress', [StudentProgressController::class, 'show']);
    });

    // ============ SHARED ROUTES (for dropdowns) ============
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
    Route::get('/subjects/{subject}/topics', [SubjectController::class, 'topics']);

    // ============ ADMIN ROUTES ============
    Route::prefix('admin')->middleware('can:admin')->group(function () {
        // User management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/stats', [AdminUserController::class, 'stats']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}/subscription', [AdminUserController::class, 'updateSubscription']);
        Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);
        
        // DSKP management
        Route::get('/dskp/levels', [\App\Http\Controllers\Admin\DskpController::class, 'levels']);
        Route::get('/dskp/levels/{level}/subjects', [\App\Http\Controllers\Admin\DskpController::class, 'subjects']);
        Route::get('/dskp/subjects/{subject}', [\App\Http\Controllers\Admin\DskpController::class, 'subjectDetail']);
        Route::post('/dskp/subjects', [\App\Http\Controllers\Admin\DskpController::class, 'storeSubject']);
        Route::delete('/dskp/subjects/{subject}', [\App\Http\Controllers\Admin\DskpController::class, 'deleteSubject']);
        Route::post('/dskp/topics', [\App\Http\Controllers\Admin\DskpController::class, 'storeTopic']);
        Route::put('/dskp/topics/{topic}', [\App\Http\Controllers\Admin\DskpController::class, 'updateTopic']);
        Route::delete('/dskp/topics/{topic}', [\App\Http\Controllers\Admin\DskpController::class, 'deleteTopic']);
        Route::post('/dskp/subtopics', [\App\Http\Controllers\Admin\DskpController::class, 'storeSubtopic']);
        Route::put('/dskp/subtopics/{subtopic}', [\App\Http\Controllers\Admin\DskpController::class, 'updateSubtopic']);
        Route::delete('/dskp/subtopics/{subtopic}', [\App\Http\Controllers\Admin\DskpController::class, 'deleteSubtopic']);
        Route::post('/dskp/preview', [\App\Http\Controllers\Admin\DskpController::class, 'previewCsv']);
        Route::post('/dskp/import', [\App\Http\Controllers\Admin\DskpController::class, 'importCsv']);
        Route::get('/dskp/template', [\App\Http\Controllers\Admin\DskpController::class, 'downloadTemplate']);
    });
});
