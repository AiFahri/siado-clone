<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    public function userAssignmentSubmission(Course $course, Assignment $assignment)
    {
        $userID = Auth::id();

        if ($assignment->course_id !== $course->id) {
            return response()->json(['error' => 'assignment does not belong to this course'], 400);
        }

        $submission = $assignment->submissions()
            ->where('user_id', $userID)
            ->first();

        if (! $submission) {
            return response()->json(['error' => 'no submission found'], 404);
        }

        $submission->course_id = $assignment->course_id;

        return response()->json($submission);
    }

    public function storeSubmission(Request $request, Course $course, Assignment $assignment, SupabaseService $supabaseService)
    {
        $userID = Auth::id();

        if ($assignment->course_id !== $course->id) {
            return response()->json(['error' => 'assignment does not belong to this course'], 400);
        }

        if ($assignment->submissions()->where('user_id', $userID)->exists()) {
            return response()->json(['error' => 'you have already submitted'], 409);
        }

        $validator = Validator::make($request->all(), [
            'answer' => 'nullable|string|required_without:file',
            'file' => 'nullable|file|max:5120|required_without:answer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getPathname();
            $fileName = $file->getClientOriginalName();

            $fileUrl = $supabaseService->uploadFile($filePath, $fileName);
        }

        $submission = $assignment->submissions()->create([
            'answer' => $request->get('answer', null),
            'file_url' => $fileUrl ?? null,
            'user_id' => $userID,
            'assignment_id' => $assignment->id,
        ]);

        $submission->course_id = $assignment->course_id;

        return response()->json($submission, 201);
    }

    public function updateSubmission(Request $request, Course $course, Assignment $assignment, SupabaseService $supabaseService)
    {
        $userID = Auth::id();

        if ($assignment->course_id !== $course->id) {
            return response()->json(['error' => 'assignment does not belong to this course'], 400);
        }

        $submission = $assignment->submissions()->where('user_id', $userID)->first();

        if (! $submission) {
            return response()->json(['error' => 'submission not found'], 404);
        }

        if ($submission->grade !== null || $submission->feedback !== null) {
            return response()->json(['error' => 'submission has already been reviewed and cannot be modified'], 403);
        }

        $validator = Validator::make($request->all(), [
            'answer' => 'nullable|string',
            'file' => 'nullable|file|max:5120',
            'is_file_deleted' => 'nullable|in:true,false,1,0',
        ]);


        $validator->after(function ($validator) use ($request) {
            $delete = filter_var($request->get('is_file_deleted'), FILTER_VALIDATE_BOOLEAN);
            $file = $request->file('file');
            $answer = $request->get('answer');

            if (! $file && ! $answer && ! $delete) {
                $validator->errors()->add('submission', 'Either answer or file must be provided.');
            }

            if ($delete && ! $answer) {
                $validator->errors()->add('answer', 'Answer is required when deleting the file.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->boolean('is_file_deleted')) {

            $submission->file_url = null;
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getPathname();
            $fileName = $file->getClientOriginalName();

            $fileUrl = $supabaseService->uploadFile($filePath, $fileName);
            $submission->file_url = $fileUrl;
        }

        $submission->answer = $request->filled('answer') ? $request->input('answer') : null;

        $submission->save();

        $submission->course_id = $assignment->course_id;

        return response()->json($submission);
    }

    public function deleteSubmission(Course $course, Assignment $assignment)
    {
        $userID = Auth::id();

        if ($assignment->course_id !== $course->id) {
            return response()->json(['error' => 'assignment does not belong to this course'], 400);
        }

        $submission = $assignment->submissions()->where('user_id', $userID)->first();

        if (! $submission) {
            return response()->json(['error' => 'submission not found'], 404);
        }

        $submission->delete();

        return response()->json(['message' => 'submission deleted']);
    }

    public function selfSubmissions(Request $request)
    {
        $userID = Auth::id();
        $c = $request->query('count', 10);

        $submissions = Submission::with('assignment')->where('user_id', $userID)->paginate($c);

        $submissions->getCollection()->transform(function ($submission) {
            $submission->course_id = $submission->assignment->course_id ?? null;
            unset($submission->assignment);
            return $submission;
        });

        return response()->json($submissions);
    }
}
