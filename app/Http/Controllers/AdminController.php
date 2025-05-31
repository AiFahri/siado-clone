<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Tampilkan semua user (dosen & mahasiswa).
     * Bisa tambahkan filter role jika ingin.
     */
    public function listUsers(Request $request)
    {
        $role = $request->query('role'); // optional filter by role: student, lecturer

        $query = User::query();

        if ($role && in_array($role, ['student', 'lecturer'])) {
            $query->where('role', $role);
        }

        $users = $query->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json($users);
    }

    /**
     * Buat user baru (dosen atau mahasiswa).
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['student', 'lecturer'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Update data user.
     */
    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|nullable|string|min:6',
            'role' => ['sometimes', 'required', Rule::in(['student', 'lecturer'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('password') && $request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->only(['name', 'email', 'role']));
        $user->save();

        return response()->json($user);
    }

    /**
     * Hapus user.
     */
    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    /**
     * Assign dosen ke mata kuliah.
     */
    public function assignLecturerToCourse($courseId, $lecturerId)
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

        return response()->json(['message' => 'Lecturer assigned to course successfully']);
    }

    /**
     * Hapus dosen dari mata kuliah.
     */
    public function removeLecturerFromCourse($courseId, $lecturerId)
    {
        $course = Course::findOrFail($courseId);

        if (! $course->lecturers()->where('user_id', $lecturerId)->exists()) {
            return response()->json(['error' => 'Lecturer not assigned to this course'], 404);
        }

        $course->lecturers()->detach($lecturerId);

        return response()->json(['message' => 'Lecturer removed from course']);
    }

    /**
     * Daftar dosen yang mengajar di mata kuliah tertentu.
     */
    public function listLecturersInCourse($courseId)
    {
        $course = Course::findOrFail($courseId);

        $lecturers = $course->lecturers()->get(['users.id', 'users.name', 'users.email']);

        return response()->json($lecturers);
    }

    public function createCourse(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|max:255|unique:courses,code',
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
        ]);

        $course = Course::create($validatedData);

        return response()->json($course, 201);
    }

    public function updateCourse(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
        ]);

        $course->update($validated);

        return response()->json($course);
    }

    public function deleteCourse(Course $course)
    {

        $course->delete();

        return response()->json(['message' => 'Course deleted']);
    }

    /**
     * Daftar mahasiswa yang terdaftar di mata kuliah tertentu.
     */
    public function getStudentsInCourse($courseId)
    {
        $course = Course::findOrFail($courseId);

        $students = $course->students()->get(['users.id', 'users.name', 'users.email']);

        return response()->json($students);
    }

    /**
     * Tambahkan mahasiswa ke mata kuliah.
     */
    public function addStudentToCourse($courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);
        $student = User::where('id', $studentId)->where('role', 'student')->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        if ($course->students()->where('user_id', $studentId)->exists()) {
            return response()->json(['error' => 'Student already enrolled in this course'], 409);
        }

        $course->students()->attach($studentId, [
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Student enrolled to course successfully']);
    }

    /**
     * Hapus mahasiswa dari mata kuliah.
     */
    public function removeStudentFromCourse($courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);

        if (!$course->students()->where('user_id', $studentId)->exists()) {
            return response()->json(['error' => 'Student not enrolled in this course'], 404);
        }

        // Hapus submission mahasiswa untuk assignment di course ini
        $assignmentIDs = $course->assignments()->pluck('id');
        \App\Models\Submission::whereIn('assignment_id', $assignmentIDs)
            ->where('user_id', $studentId)
            ->delete();

        $course->students()->detach($studentId);

        return response()->json(['message' => 'Student removed from course and submissions deleted']);
    }

    /**
     * Mendapatkan statistik untuk admin
     */
    public function getAdminStats()
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $totalStudents = User::where('role', 'student')->count();
        $totalLecturers = User::where('role', 'lecturer')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalCourses = Course::count();
        $totalAssignments = \App\Models\Assignment::count();
        $totalSubmissions = \App\Models\Submission::count();
        $avgStudentsPerCourse = \DB::table('course_enrollments')
            ->selectRaw('AVG(student_count) as average')
            ->from(function($query) {
                $query->select('course_id', \DB::raw('COUNT(user_id) as student_count'))
                      ->from('course_enrollments')
                      ->groupBy('course_id');
            }, 'counts')
            ->value('average');
        
        return response()->json([
            'total_students' => $totalStudents,
            'total_lecturers' => $totalLecturers,
            'total_admins' => $totalAdmins,
            'total_courses' => $totalCourses,
            'total_assignments' => $totalAssignments,
            'total_submissions' => $totalSubmissions,
            'avg_students_per_course' => round($avgStudentsPerCourse, 1)
        ]);
    }
}


