<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    /**
     * Display a listing of the classes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ClassModel::with(['course', 'teacher']);
        
        // Apply filters based on query parameters
        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }
        
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->input('teacher_id'));
        }
        
        if ($request->has('room')) {
            $query->where('room', 'like', '%' . $request->input('room') . '%');
        }
        
        if ($request->has('schedule')) {
            $query->where('schedule', 'like', '%' . $request->input('schedule') . '%');
        }
        
        if ($request->has('max_students')) {
            $query->where('max_students', $request->input('max_students'));
        }
        
        if ($request->has('current_students')) {
            $query->where('current_students', $request->input('current_students'));
        }
        
        // Get pagination parameters
        $perPage = $request->input('itemsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Get paginated results
        $classes = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $classes->items(),
            'pagination' => [
                'currentPage' => $classes->currentPage(),
                'itemsPerPage' => (int) $classes->perPage(),
                'totalItems' => $classes->total(),
                'totalPages' => $classes->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified class.
     */
    public function show(string $id): JsonResponse
    {
        $class = ClassModel::with(['course', 'teacher'])->find($id);
        
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $class
        ]);
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:teachers,id',
            'room' => 'nullable|string|max:50',
            'schedule' => 'nullable|string|max:255',
            'max_students' => 'nullable|integer|min:1',
            'current_students' => 'nullable|integer|min:0'
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
        
        // Check if teacher exists
        $teacher = Teacher::find($request->teacher_id);
        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
            ], 404);
        }
        
        $class = ClassModel::create($request->all());
        
        return response()->json([
            'data' => $class->load(['course', 'teacher'])
        ], 201);
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $class = ClassModel::find($id);
        
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'course_id' => 'sometimes|required|exists:courses,id',
            'teacher_id' => 'sometimes|required|exists:teachers,id',
            'room' => 'nullable|string|max:50',
            'schedule' => 'nullable|string|max:255',
            'max_students' => 'nullable|integer|min:1',
            'current_students' => 'nullable|integer|min:0'
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
        
        // If teacher_id is provided, check if teacher exists
        if ($request->has('teacher_id')) {
            $teacher = Teacher::find($request->teacher_id);
            if (!$teacher) {
                return response()->json([
                    'message' => 'Teacher not found'
                ], 404);
            }
        }
        
        $class->update($request->all());
        
        return response()->json([
            'data' => $class->fresh()->load(['course', 'teacher'])
        ]);
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $class = ClassModel::find($id);
        
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
            ], 404);
        }
        
        $class->delete();
        
        return response()->json(null, 204);
    }
}