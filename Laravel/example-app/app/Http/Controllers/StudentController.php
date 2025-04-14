<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index(): JsonResponse
    {
        $students = Student::all();
        
        return response()->json([
            'data' => $students
        ]);
    }

    /**
     * Display the specified student.
     */
    public function show(string $id): JsonResponse
    {
        $student = Student::find($id);
        
        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $student
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'birth_date' => 'nullable|date',
            'enrollment_date' => 'required|date',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $student = Student::create($request->all());
        
        return response()->json([
            'data' => $student
        ], 201);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $student = Student::find($id);
        
        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:students,email,' . $id,
            'birth_date' => 'nullable|date',
            'enrollment_date' => 'sometimes|required|date',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $student->update($request->all());
        
        return response()->json([
            'data' => $student
        ]);
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $student = Student::find($id);
        
        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }
        
        $student->delete();
        
        return response()->json(null, 204);
    }
}