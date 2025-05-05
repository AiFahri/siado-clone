<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function courseAssignment(Course $course, Request $request)
    {
        $c = $request->query('count', 10);
        $filterSubmit = $request->query('has_submit');
        $userID = Auth::id();

        $assignments = $course->assignments()->paginate($c);

        $assignments->getCollection()->transform(function ($assignment) use ($userID) {
            $assignment->has_submit = $assignment->submissions()->where('user_id', $userID)->exists();
            return $assignment;
        });

        if (!is_null($filterSubmit)) {
            $assignments->setCollection(
                $assignments->getCollection()->filter(function ($item) use ($filterSubmit) {
                    return $item->has_submit == filter_var($filterSubmit, FILTER_VALIDATE_BOOLEAN);
                })->values()
            );
        }

        return response()->json($assignments);
    }


    public function selfAssignments(Request $request)
    {
        $userID = Auth::id();
        $c = $request->query('count', 10);
        $submitted = $request->query('has_submit');

        $enrolledCourseIDs = DB::table('course_enrollments')
            ->where('user_id', $userID)
            ->pluck('course_id');

        $query = Assignment::whereIn('course_id', $enrolledCourseIDs);

        $assignments = $query->paginate($c);

        $assignments->getCollection()->transform(function ($assignment) use ($userID) {
            $assignment->has_submit = $assignment->submissions()
                ->where('user_id', $userID)
                ->exists();

            return $assignment;
        });

        if (!is_null($submitted)) {
            $assignments->setCollection(
                $assignments->getCollection()->filter(function ($item) use ($submitted) {
                    return $item->has_submit == filter_var($submitted, FILTER_VALIDATE_BOOLEAN);
                })->values()
            );
        }

        return response()->json($assignments);
    }
}
