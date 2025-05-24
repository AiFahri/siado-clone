<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{

    public function studentListMaterials(Course $course)
    {
        $user = Auth::user();

        if (!$course->students()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied: You are not enrolled in this course'], 403);
        }

        $materials = $course->materials()->get();

        return response()->json($materials);
    }

    public function listMaterials(Course $course)
    {
        $user = Auth::user();

        if (!$course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $materials = $course->materials()->get();

        return response()->json($materials);
    }

    public function storeMaterial(Request $request, Course $course)
    {
        $user = Auth::user();

        if (!$course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $material = $course->materials()->create($validated);

        return response()->json($material, 201);
    }

    public function showMaterial($courseId, $materialId)
    {
        $course = Course::findOrFail($courseId);
        $material = $course->materials()->findOrFail($materialId);

        $user = Auth::user();

        if (!$course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json($material);
    }

    public function updateMaterial(Request $request, $courseId, $materialId)
    {
        $course = Course::findOrFail($courseId);
        $material = $course->materials()->findOrFail($materialId);

        $user = Auth::user();

        if (!$course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $material->update($validated);

        return response()->json($material);
    }

    public function deleteMaterial($courseId, $materialId)
    {
        $course = Course::findOrFail($courseId);
        $material = $course->materials()->findOrFail($materialId);

        $user = Auth::user();

        if (!$course->lecturers()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted']);
    }
}
