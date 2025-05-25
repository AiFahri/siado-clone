<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
        $course = $request->route('course');

        if (!$course) {
            return response()->json(['error' => 'course not provided'], 400);
        }

        $isStudentEnrolled = $course->students()->where('user_id', $user->id)->exists();
        $isLecturerAssigned = $course->lecturers()->where('user_id', $user->id)->exists();

        if (!$isStudentEnrolled && !$isLecturerAssigned) {
            return response()->json(['error' => 'Access denied: You are neither enrolled as a student nor assigned as a lecturer for this course'], 403);
        }

        return $next($request);
    }
}