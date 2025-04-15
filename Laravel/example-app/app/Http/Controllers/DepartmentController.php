<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::with('head');
        
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
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }
        
        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->input('description') . '%');
        }
        
        if ($request->has('head_id')) {
            $query->where('head_id', $request->input('head_id'));
        }
        
        // Get pagination parameters
        $perPage = $request->input('itemsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Get paginated results
        $departments = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $departments->items(),
            'pagination' => [
                'currentPage' => $departments->currentPage(),
                'itemsPerPage' => (int) $departments->perPage(),
                'totalItems' => $departments->total(),
                'totalPages' => $departments->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified department.
     */
    public function show(string $id): JsonResponse
    {
        $department = Department::with('head')->find($id);
        
        if (!$department) {
            return response()->json([
                'message' => 'Department not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $department
        ]);
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:departments,code',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:teachers,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->has('head_id') && $request->head_id) {
            $teacher = Teacher::find($request->head_id);
            if (!$teacher) {
                return response()->json([
                    'message' => 'Teacher not found'
                ], 404);
            }
        }
        
        $department = Department::create($request->all());
        
        return response()->json([
            'data' => $department->load('head')
        ], 201);
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $department = Department::find($id);
        
        if (!$department) {
            return response()->json([
                'message' => 'Department not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|unique:departments,code,' . $id,
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:teachers,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->has('head_id') && $request->head_id) {
            $teacher = Teacher::find($request->head_id);
            if (!$teacher) {
                return response()->json([
                    'message' => 'Teacher not found'
                ], 404);
            }
        }
        
        $department->update($request->all());
        
        return response()->json([
            'data' => $department->fresh()->load('head')
        ]);
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $department = Department::find($id);
        
        if (!$department) {
            return response()->json([
                'message' => 'Department not found'
            ], 404);
        }
        
        $department->delete();
        
        return response()->json(null, 204);
    }
}