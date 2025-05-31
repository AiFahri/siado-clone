<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MaterialController;

// Route untuk autentikasi (signup & signin)
Route::prefix('auth')->group(function () {
    Route::post('signup', [JWTAuthController::class, 'signup']);
    Route::post('signin', [JWTAuthController::class, 'signin']);
});

// Semua route berikut butuh autentikasi JWT
Route::middleware('jwt.auth')->group(function () {

    // Semua user (student, lecturer, admin) bisa akses data diri dan data yang terkait dengan user sendiri
    Route::prefix('/users/_self')->group(function () {
        Route::get('/', [JWTAuthController::class, 'me']);
        Route::get('/courses', [CourseController::class, 'selfCourses']);
        Route::get('/assignments', [AssignmentController::class, 'selfAssignments']);
        Route::get('/submissions', [SubmissionController::class, 'selfSubmissions']);
    });

    Route::prefix('/courses')->group(function () {
        Route::get('/', [CourseController::class, 'all']);

        Route::get('/{course}', [CourseController::class, 'get'])->where('course', '[0-9]+');
        Route::post('/{course}', [CourseController::class, 'enroll'])->where('course', '[0-9]+');
        Route::delete('/{course}', [CourseController::class, 'unenroll'])->where('course', '[0-9]+');
    });

    // Route /courses hanya untuk admin dan dosen (lecturer)
    Route::middleware('role:admin,lecturer')->prefix('/courses')->group(function () {
        // Admin bisa assign dosen ke course
        Route::post('/{course}/lecturers/{lecturer}', [CourseController::class, 'assignLecturer'])->where(['course' => '[0-9]+', 'lecturer' => '[0-9]+']);
        Route::get('/{course}/lecturers', [CourseController::class, 'listLecturers'])->where('course', '[0-9]+');
        Route::delete('/{course}/lecturers/{lecturer}', [CourseController::class, 'removeLecturer'])->where(['course' => '[0-9]+', 'lecturer' => '[0-9]+']);
    });

    // Route khusus untuk dosen CRUD tugas dan mengelola submission mahasiswa
    Route::middleware(['jwt.auth', 'role:lecturer', 'teachesCourse'])->prefix('/lecturer')->group(function () {
        // CRUD assignments (tugas)
        Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store']);
        Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index']);
        Route::get('/courses/{course}/assignments/{assignment}', [AssignmentController::class, 'show']);
        Route::patch('/courses/{course}/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('/courses/{course}/assignments/{assignment}', [AssignmentController::class, 'destroy']);

        // Melihat submission dan memberi nilai
        Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'listSubmissions']);
        Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'gradeSubmission']);
        
        // Routes khusus materi (materials)
        Route::get('/courses/{course}/materials', [MaterialController::class, 'listMaterials']);
        Route::post('/courses/{course}/materials', [MaterialController::class, 'storeMaterial']);
        Route::get('/courses/{course}/materials/{material}', [MaterialController::class, 'showMaterial']);
        Route::patch('/courses/{course}/materials/{material}', [MaterialController::class, 'updateMaterial']);
        Route::delete('/courses/{course}/materials/{material}', [MaterialController::class, 'deleteMaterial']);
        Route::get('/courses/{course}/students', [CourseController::class, 'getCourseStudents']);
    });

    // Route akses assignment, submission, dan materi hanya untuk mahasiswa yang enrolled
    Route::middleware(['role:student', 'enrolled'])->group(function () {
        Route::get('/courses/{course}/assignments', [AssignmentController::class, 'courseAssignment'])
            ->where('course', '[0-9]+');
        Route::get('/courses/{course}/assignments/{assignment}', [AssignmentController::class, 'getAssignment']);

        Route::get('/courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'userAssignmentSubmission'])
            ->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
        Route::post('/courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'storeSubmission'])
            ->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
        Route::patch('/courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'updateSubmission'])
            ->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);
        Route::delete('/courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'deleteSubmission'])
            ->where(['course' => '[0-9]+', 'assignment' => '[0-9]+']);

        Route::get('/courses/{course}/materials', [MaterialController::class, 'studentListMaterials'])->where('course', '[0-9]+');
    });

    Route::middleware(['jwt.auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::patch('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

        Route::post('/courses/{course}/lecturers/{lecturer}', [AdminController::class, 'assignLecturerToCourse']);
        Route::get('/courses/{course}/lecturers', [AdminController::class, 'listLecturersInCourse']);
        Route::delete('/courses/{course}/lecturers/{lecturer}', [AdminController::class, 'removeLecturerFromCourse']);

        Route::post('/courses', [AdminController::class, 'createCourse']);
        Route::patch('/courses/{course}', [AdminController::class, 'updateCourse']);
        Route::delete('/courses/{course}', [AdminController::class, 'deleteCourse']);
        
        Route::get('/courses/{course}/students', [AdminController::class, 'getStudentsInCourse']);
        Route::post('/courses/{course}/students/{student}', [AdminController::class, 'addStudentToCourse']);
        Route::delete('/courses/{course}/students/{student}', [AdminController::class, 'removeStudentFromCourse']);
        Route::get('/stats', [AdminController::class, 'getAdminStats']);
    });

    Route::middleware(['jwt.auth', 'role:lecturer'])->group(function () {
        Route::get('/lecturer/stats', [CourseController::class, 'getLecturerStats']);
    });
});
