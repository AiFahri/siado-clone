<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;

class EnsureUserTeachesCourse
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Cek user login dan role
        if (!$user || $user->role !== 'lecturer') {
            return response()->json(['error' => 'Access denied: User is not a lecturer'], 403);
        }

        // Tangkap parameter dari URL
        $courseParam = $request->route('course');
        $assignmentParam = $request->route('assignment');
        $submissionParam = $request->route('submission');

        $course = null;

        // Cek jika submission tersedia → ambil course dari relasi
        if ($submissionParam) {
            $submission = is_numeric($submissionParam)
                ? Submission::with('assignment.course')->find($submissionParam)
                : $submissionParam;
        
            // Tambahkan ini:
            logger([
                'submission_id' => $submissionParam,
                'submission' => $submission,
                'assignment' => optional($submission)->assignment,
                'course' => optional(optional($submission)->assignment)->course,
            ]);
        
            if (!$submission || !$submission->assignment || !$submission->assignment->course) {
                return response()->json(['error' => 'Course or assignment ID not found'], 400);
            }
            $course = $submission->assignment->course;
        }
        

        // Jika assignment tersedia → ambil course dari relasi
        elseif ($assignmentParam) {
            $assignment = is_numeric($assignmentParam)
                ? Assignment::with('course')->find($assignmentParam)
                : $assignmentParam;

            if (!$assignment || !$assignment->course) {
                return response()->json(['error' => 'Course not found for assignment'], 400);
            }

            $course = $assignment->course;
        }

        // Jika course tersedia → langsung pakai
        elseif ($courseParam) {
            $course = is_numeric($courseParam)
                ? Course::find($courseParam)
                : $courseParam;

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }
        }

        // Jika tidak satupun tersedia
        else {
            return response()->json(['error' => 'Course or assignment ID not found'], 400);
        }

        // Validasi bahwa dosen mengajar course tersebut
        if (!$course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied: You do not teach this course'], 403);
        }

        return $next($request);
    }
}