<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Repositories\CourseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    protected $courseRepository;

    /**
     * CourseController constructor.
     */
    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Display a listing of the courses.
     */
    public function index(Request $request): JsonResponse
    {
        $courses = $this->courseRepository->getFilteredCourses($request);
        
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
        $course = $this->courseRepository->findById($id);
        
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
        
        $course = $this->courseRepository->create($request->all());
        
        return response()->json([
            'data' => $course->load('department')
        ], 201);
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $course = $this->courseRepository->findById($id);
        
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
        
        $updatedCourse = $this->courseRepository->update($course, $request->all());
        
        return response()->json([
            'data' => $updatedCourse
        ]);
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $course = $this->courseRepository->findById($id);
        
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        
        $this->courseRepository->delete($course);
        
        return response()->json(null, 204);
    }
}