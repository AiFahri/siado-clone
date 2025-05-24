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

    // List semua assignment dalam course (dosen)
    public function index(Request $request, Course $course)
    {
        $user = Auth::user();

        if (! $course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $count = $request->query('count', 10);

        $assignments = $course->assignments()->paginate($count);

        return response()->json($assignments);
    }


    // Buat assignment baru (dosen)
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        if ($user->role !== 'lecturer' || ! $course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
        ]);

        $assignment = $course->assignments()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        return response()->json($assignment, 201);
    }

    // Detail assignment, bisa untuk mahasiswa dan dosen
    public function show(Course $course, Assignment $assignment)
    {
        $user = Auth::user();

        // Pastikan assignment memang milik course dari route
        if ($assignment->course_id !== $course->id) {
            return response()->json(['error' => 'Assignment does not belong to this course'], 400);
        }

        if ($user->role === 'lecturer' && $course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json($assignment);
        }

        if ($user->role === 'student' && $course->students()->where('user_id', $user->id)->exists()) {
            return response()->json($assignment);
        }

        return response()->json(['error' => 'Access denied'], 403);
    }

    // Update assignment (dosen)
    public function update(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $course = $assignment->course;

        if ($user->role !== 'lecturer' || ! $course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'due_date' => 'sometimes|required|date',
        ]);

        $assignment->update($request->only(['title', 'description', 'due_date']));

        return response()->json($assignment);
    }

    // Hapus assignment (dosen)
    public function destroy(Assignment $assignment)
    {
        $user = Auth::user();
        $course = $assignment->course;

        if ($user->role !== 'lecturer' || ! $course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted']);
    }

    public function getAssignment(Course $course, Assignment $assignment)
    {
        $user = Auth::user();

        // Pastikan assignment memang milik course yang diminta
        if ($assignment->course_id !== $course->id) {
            return response()->json(['error' => 'Assignment does not belong to the given course'], 400);
        }

        // Cek apakah user mahasiswa dan ter-enroll di course
        if ($user->role !== 'student' || ! $course->students()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Cek apakah user sudah submit assignment ini
        $assignment->has_submit = $assignment->submissions()
            ->where('user_id', $user->id)
            ->exists();

        return response()->json($assignment);
    }
}
