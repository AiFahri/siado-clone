<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;

Route::prefix('auth')->group(function () {
    Route::post('signup', [JWTAuthController::class, 'signup']);
    Route::post('signin', [JWTAuthController::class, 'signin']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::prefix('/users/_self')->group(function () {
        Route::get('', [JWTAuthController::class, 'me']);
        Route::get('/courses', [CourseController::class, 'selfCourses']);
        Route::get('/assignments', [AssignmentController::class, 'selfAssignments']);
        Route::get('/submissions', [SubmissionController::class, 'selfSubmissions']);
    });

    Route::prefix('/courses')->group(function () {
        Route::get('', [CourseController::class, 'all']);

        Route::get('/{course}', [CourseController::class, 'get'])->where('course', '[0-9]+');
        Route::post('/{course}', [CourseController::class, 'enroll'])->where('course', '[0-9]+');
        Route::delete('/{course}', [CourseController::class, 'unenroll'])->where('course', '[0-9]+');

        Route::middleware('enrolled')->get('/{course}/assignments', [AssignmentController::class, 'courseAssignment'])->where('course', '[0-9]+');

        Route::middleware('enrolled')->get('/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'userAssignmentSubmission'])->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
        Route::middleware('enrolled')->post('/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'storeSubmission'])->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
        Route::middleware('enrolled')->patch('/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'updateSubmission'])->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
        Route::middleware('enrolled')->delete('/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'deleteSubmission'])->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
    });
});
