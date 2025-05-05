<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function all(Request $request)
    {
        $c = $request->query('count', 10);
        $q = $request->query('query');
        $filterEnroll = $request->query('has_enroll');

        $all = Course::query();

        if ($q) {
            $all->where(function ($query) use ($q) {
                $query->where('code', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%');
            });
        }

        $userID = Auth::id();
        $courses = $all->paginate($c);

        if ($courses->isEmpty()) {
            return response()->json(['error' => 'resource not found'], 404);
        }

        $courses->getCollection()->transform(function ($course) use ($userID) {
            $course->has_enroll = $course->students()->where('user_id', $userID)->exists();
            return $course;
        });

        if (!is_null($filterEnroll)) {
            $courses->setCollection(
                $courses->getCollection()->filter(function ($item) use ($filterEnroll) {
                    return $item->has_enroll == filter_var($filterEnroll, FILTER_VALIDATE_BOOLEAN);
                })->values()
            );
        }

        return response()->json($courses);
    }

    public function get(Course $course)
    {
        $userID = Auth::id();
        $course->has_enroll = $course->students()->where('user_id', $userID)->exists();

        return response()->json($course);
    }

    public function selfCourses(Request $request)
    {
        $user = Auth::user();
        $c = $request->query('count', 10);

        $courses = $user->enrolledCourses()->paginate($c);

        $courses->getCollection()->transform(function ($course) {
            return $course->makeHidden('pivot');
        });

        return response()->json($courses);
    }

    public function enroll($courseID)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseID);

        if ($course->students()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'you are already enrolled in this course'], 403);
        }

        $course->students()->attach($user->id, [
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'enrolled successfully']);
    }


    public function unenroll($courseID)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseID);

        if (! $course->students()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'you are not enrolled in this course'], 403);
        }

        $assignmentIDs = $course->assignments()->pluck('id');

        \App\Models\Submission::whereIn('assignment_id', $assignmentIDs)
            ->where('user_id', $user->id)
            ->delete();

        $course->students()->detach($user->id);

        return response()->json(['message' => 'unenrolled and submissions deleted']);
    }
}
