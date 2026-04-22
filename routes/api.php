<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\ProgramController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/student', [AuthController::class, 'registerStudent']);
Route::post('/register/professor', [AuthController::class, 'registerProfessor']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/programs', [ProgramController::class, 'index']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/professors', [ProfessorController::class, 'index']);
    Route::get('/professors/{professor}', [ProfessorController::class, 'show']);

    Route::middleware('role:student')->group(function (): void {
        Route::post('/applications', [ApplicationController::class, 'store']);
        Route::post('/enrollments', [EnrollmentController::class, 'store']);
    });

    Route::post('/enroll', [EnrollmentController::class, 'enroll'])
        ->middleware('role:student,admin');

    Route::middleware('role:admin')->group(function (): void {
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);

        Route::post('/programs', [ProgramController::class, 'store']);
        Route::put('/programs/{program}', [ProgramController::class, 'update']);
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy']);

        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{course}', [CourseController::class, 'update']);
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        Route::post('/courses/{course}/assign-professor', [CourseController::class, 'assignProfessor']);

        Route::post('/professors', [ProfessorController::class, 'store']);
        Route::put('/professors/{professor}', [ProfessorController::class, 'update']);
        Route::delete('/professors/{professor}', [ProfessorController::class, 'destroy']);

        Route::put('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
        Route::get('/login', function () {
            return ApiResponse::error('Unauthorized', 401);
        })->name('login');
    });
});
