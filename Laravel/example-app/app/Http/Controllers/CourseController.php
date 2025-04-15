<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::with('department');
        
        // Apply filters based on query parameters
        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        
        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->input('code') . '%');
        }
        
        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }
        
        if ($request->has('credits')) {
            $query->where('credits', $request->input('credits'));
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('start_date', $request->input('start_date'));
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('end_date', $request->input('end_date'));
        }
        
        if ($request->has('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }
        
        // Get pagination parameters
        $perPage = $request->input('itemsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Get paginated results
        $courses = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $courses->items(),
            'pagination' => [
                'currentPage' => $courses->currentPage(),
                'itemsPerPage' => (int) $courses->perPage(),
                'totalItems' => $courses->total(),
                'totalPages' => $courses->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified course.
     */
    public function show(string $id): JsonResponse
    {
        $course = Course::with('department')->find($id);
        
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $course
        ]);
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses,code',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->has('department_id') && $request->department_id) {
            $department = Department::find($request->department_id);
            if (!$department) {
                return response()->json([
                    'message' => 'Department not found'
                ], 404);
            }
        }
        
        $course = Course::create($request->all());
        
        return response()->json([
            'data' => $course->load('department')
        ], 201);
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $course = Course::find($id);
        
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|unique:courses,code,' . $id,
            'description' => 'nullable|string',
            'credits' => 'sometimes|required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->has('department_id') && $request->department_id) {
            $department = Department::find($request->department_id);
            if (!$department) {
                return response()->json([
                    'message' => 'Department not found'
                ], 404);
            }
        }
        
        $course->update($request->all());
        
        return response()->json([
            'data' => $course->fresh()->load('department')
        ]);
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $course = Course::find($id);
        
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        
        $course->delete();
        
        return response()->json(null, 204);
    }
}