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
use App\Http\Controllers\Admin\SubjectController;

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
});

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
        
        // Student Progress (individual student detail)
        Route::get('/students/{student}/progress', [StudentProgressController::class, 'show']);
    });

    // ============ SHARED ROUTES (for dropdowns) ============
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
    Route::get('/subjects/{subject}/topics', [SubjectController::class, 'topics']);

    // ============ ADMIN ROUTES ============
    Route::prefix('admin')->middleware('can:admin')->group(function () {
        // More admin routes to be added
    });
});
