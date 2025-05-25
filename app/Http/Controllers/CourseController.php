<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    // Menampilkan semua course (untuk admin atau dosen)
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

    // Menampilkan detail course
    public function get(Course $course)
    {
        $userID = Auth::id();
        $course->has_enroll = $course->students()->where('user_id', $userID)->exists();

        return response()->json($course);
    }

    // Menampilkan course yang diikuti oleh mahasiswa
    public function selfCourses(Request $request)
    {
        $user = Auth::user();
        $c = $request->query('count', 10);

        if ($user->role === 'student') {
            $courses = $user->enrolledCourses()->paginate($c);
        } elseif ($user->role === 'lecturer') {
            $courses = $user->teachingCourses()->paginate($c);
        } else {
            return response()->json(['error' => 'Role not supported'], 403);
        }

        $courses->getCollection()->transform(function ($course) {
            return $course->makeHidden('pivot');
        });

        return response()->json($courses);
    }


    // Enroll mahasiswa ke dalam course
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

    // Unenroll mahasiswa dari course
    public function unenroll($courseID)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseID);

        if (!$course->students()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'you are not enrolled in this course'], 403);
        }

        $assignmentIDs = $course->assignments()->pluck('id');

        \App\Models\Submission::whereIn('assignment_id', $assignmentIDs)
            ->where('user_id', $user->id)
            ->delete();

        $course->students()->detach($user->id);

        return response()->json(['message' => 'unenrolled and submissions deleted']);
    }

    // Assign dosen ke mata kuliah (admin)
    public function assignLecturer($courseId, $lecturerId)
    {
        $course = Course::findOrFail($courseId);
        $lecturer = User::where('id', $lecturerId)->where('role', 'lecturer')->first();

        if (!$lecturer) {
            return response()->json(['error' => 'Lecturer not found'], 404);
        }

        if ($course->lecturers()->where('user_id', $lecturerId)->exists()) {
            return response()->json(['error' => 'Lecturer already assigned to this course'], 409);
        }

        $course->lecturers()->attach($lecturerId);

        return response()->json(['message' => 'Lecturer assigned successfully']);
    }

    // List dosen yang mengajar di mata kuliah
    public function listLecturers($courseId)
    {
        $course = Course::findOrFail($courseId);

        $lecturers = $course->lecturers()->get(['users.id', 'users.name', 'users.email']);

        return response()->json($lecturers);
    }

    // Remove dosen dari mata kuliah
    public function removeLecturer($courseId, $lecturerId)
    {
        $course = Course::findOrFail($courseId);

        if (!$course->lecturers()->where('user_id', $lecturerId)->exists()) {
            return response()->json(['error' => 'Lecturer not assigned to this course'], 404);
        }

        $course->lecturers()->detach($lecturerId);

        return response()->json(['message' => 'Lecturer removed from course']);
    }
}
