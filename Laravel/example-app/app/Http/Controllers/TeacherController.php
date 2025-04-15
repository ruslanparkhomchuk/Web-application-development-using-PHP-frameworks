<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    /**
     * Display a listing of the teachers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Teacher::query();
        
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
        
        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }
        
        if ($request->has('hire_date')) {
            $query->whereDate('hire_date', $request->input('hire_date'));
        }
        
        // Get pagination parameters
        $perPage = $request->input('itemsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Get paginated results
        $teachers = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $teachers->items(),
            'pagination' => [
                'currentPage' => $teachers->currentPage(),
                'itemsPerPage' => (int) $teachers->perPage(),
                'totalItems' => $teachers->total(),
                'totalPages' => $teachers->lastPage()
            ]
        ]);
    }

    /**
     * Display the specified teacher.
     */
    public function show(string $id): JsonResponse
    {
        $teacher = Teacher::find($id);
        
        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $teacher
        ]);
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $teacher = Teacher::create($request->all());
        
        return response()->json([
            'data' => $teacher
        ], 201);
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $teacher = Teacher::find($id);
        
        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:teachers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $teacher->update($request->all());
        
        return response()->json([
            'data' => $teacher
        ]);
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $teacher = Teacher::find($id);
        
        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
            ], 404);
        }
        
        $teacher->delete();
        
        return response()->json(null, 204);
    }
}