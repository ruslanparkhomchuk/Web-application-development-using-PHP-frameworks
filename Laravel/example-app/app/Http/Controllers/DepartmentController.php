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
    public function index(): JsonResponse
    {
        $departments = Department::with('head')->get();
        
        return response()->json([
            'data' => $departments
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