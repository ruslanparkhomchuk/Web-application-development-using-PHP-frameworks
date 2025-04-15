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
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();
        
        // Apply filters based on query parameters
        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        
        if ($request->has('first_name')) {
            $query->where('first_name', 'like', '%' . $request->input('first_name') . '%');
        }
        
        if ($request->has('last_name')) {
            $query->where('last_name', 'like', '%' . $request->input('last_name') . '%');
        }
        
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        
        if ($request->has('birth_date')) {
            $query->whereDate('birth_date', $request->input('birth_date'));
        }
        
        if ($request->has('enrollment_date')) {
            $query->whereDate('enrollment_date', $request->input('enrollment_date'));
        }
        
        if ($request->has('address')) {
            $query->where('address', 'like', '%' . $request->input('address') . '%');
        }
        
        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }
        
        // Get pagination parameters
        $perPage = $request->input('itemsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Get paginated results
        $students = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $students->items(),
            'pagination' => [
                'currentPage' => $students->currentPage(),
                'itemsPerPage' => (int) $students->perPage(),
                'totalItems' => $students->total(),
                'totalPages' => $students->lastPage()
            ]
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