<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserTeachesCourse
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->role !== 'lecturer') {
            return response()->json(['error' => 'Access denied: User is not a lecturer'], 403);
        }

        $course = $request->route('course');

        if (! $course || ! $course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied: You do not teach this course'], 403);
        }

        return $next($request);
    }
}
