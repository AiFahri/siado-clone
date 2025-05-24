<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Course;

class EnsureUserEnrolledInCourse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = $request->route('course');
        $assignment = $request->route('assignment');
        $submission = $request->route('submission');

        if ($submission && !$assignment && !$course) {
            // Jika ada submission tapi course dan assignment tidak ada, ambil course dari submission
            // Pastikan relasi submission->assignment->course sudah terdefinisi dengan benar di model
            $submissionModel = $submission;
            if (is_numeric($submission)) {
                $submissionModel = \App\Models\Submission::find($submission);
                if (!$submissionModel) {
                    return response()->json(['error' => 'Submission not found'], 404);
                }
            }
            $assignment = $submissionModel->assignment;
            if (!$assignment) {
                return response()->json(['error' => 'Assignment not found for this submission'], 404);
            }
            $course = $assignment->course;
            if (!$course) {
                return response()->json(['error' => 'Course not found for this assignment'], 404);
            }
        }

        if ($assignment && !$course) {
            // Jika assignment ada tapi course tidak ada, ambil course dari assignment
            if (is_numeric($assignment)) {
                $assignment = \App\Models\Assignment::find($assignment);
                if (!$assignment) {
                    return response()->json(['error' => 'Assignment not found'], 404);
                }
            }
            $course = $assignment->course;
            if (!$course) {
                return response()->json(['error' => 'Course not found for this assignment'], 404);
            }
        }

        if (is_numeric($course)) {
            $course = Course::find($course);
            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }
        }

        if (!$course) {
            return response()->json(['error' => 'Course not provided'], 400);
        }

        $isStudentEnrolled = $course->students()->where('user_id', $user->id)->exists();
        $isLecturerAssigned = $course->lecturers()->where('user_id', $user->id)->exists();

        if (!$isStudentEnrolled && !$isLecturerAssigned) {
            return response()->json(['error' => 'Access denied: You are neither enrolled nor teaching this course'], 403);
        }

        return $next($request);
    }
}
