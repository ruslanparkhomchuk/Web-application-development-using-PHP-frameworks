<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * Display a listing of the exams.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Exam::with('course');
        
        // Apply filters based on query parameters
        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }
        
        if ($request->has('date')) {
            $query->whereDate('date', $request->input('date'));
        }
        
        if ($request->has('duration')) {
            $query->where('duration', 'like', '%' . $request->input('duration') . '%');
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }
        
        // Get pagination parameters
        $perPage = $request->input('itemsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Get paginated results
        $exams = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $exams->items(),
            'pagination' => [
                'currentPage' => $exams->currentPage(),
                'itemsPerPage' => (int) $exams->perPage(),
                'totalItems' => $exams->total(),
                'totalPages' => $exams->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified exam.
     */
    public function show(string $id): JsonResponse
    {
        $exam = Exam::with(['course', 'results.student'])->find($id);
        
        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $exam
        ]);
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'duration' => 'nullable|string',
            'location' => 'nullable|string',
            'type' => 'required|string|in:midterm,final,quiz,assignment'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if course exists
        $course = Course::find($request->course_id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        
        $exam = Exam::create($request->all());
        
        return response()->json([
            'data' => $exam->load('course')
        ], 201);
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $exam = Exam::find($id);
        
        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'course_id' => 'sometimes|required|exists:courses,id',
            'date' => 'sometimes|required|date',
            'duration' => 'nullable|string',
            'location' => 'nullable|string',
            'type' => 'sometimes|required|string|in:midterm,final,quiz,assignment'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // If course_id is provided, check if course exists
        if ($request->has('course_id')) {
            $course = Course::find($request->course_id);
            if (!$course) {
                return response()->json([
                    'message' => 'Course not found'
                ], 404);
            }
        }
        
        $exam->update($request->all());
        
        return response()->json([
            'data' => $exam->fresh()->load('course')
        ]);
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $exam = Exam::find($id);
        
        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found'
            ], 404);
        }
        
        $exam->delete();
        
        return response()->json(null, 204);
    }
}