<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamResultController extends Controller
{
    /**
     * Display a listing of the exam results.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();
        
        // Apply filters based on query parameters
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
     * Display the specified exam result.
     */
    public function show(string $id): JsonResponse
    {
        $examResult = ExamResult::with(['exam.course', 'student'])->find($id);
        
        if (!$examResult) {
            return response()->json([
                'message' => 'Exam result not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $examResult
        ]);
    }

    /**
     * Store a newly created exam result in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'score' => 'nullable|numeric|min:0',
            'grade' => 'nullable|string|max:5',
            'feedback' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if exam exists
        $exam = Exam::find($request->exam_id);
        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found'
            ], 404);
        }
        
        // Check if student exists
        $student = Student::find($request->student_id);
        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }
        
        // Check if result already exists for this exam and student
        $existingResult = ExamResult::where('exam_id', $request->exam_id)
            ->where('student_id', $request->student_id)
            ->first();
            
        if ($existingResult) {
            return response()->json([
                'message' => 'Exam result already exists for this student and exam'
            ], 422);
        }
        
        $examResult = ExamResult::create($request->all());
        
        return response()->json([
            'data' => $examResult->load(['exam.course', 'student'])
        ], 201);
    }

    /**
     * Update the specified exam result in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $examResult = ExamResult::find($id);
        
        if (!$examResult) {
            return response()->json([
                'message' => 'Exam result not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'exam_id' => 'sometimes|required|exists:exams,id',
            'student_id' => 'sometimes|required|exists:students,id',
            'score' => 'nullable|numeric|min:0',
            'grade' => 'nullable|string|max:5',
            'feedback' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // If exam_id or student_id is provided, check for potential duplicates
        if ($request->has('exam_id') || $request->has('student_id')) {
            $exam_id = $request->exam_id ?? $examResult->exam_id;
            $student_id = $request->student_id ?? $examResult->student_id;
            
            $existingResult = ExamResult::where('exam_id', $exam_id)
                ->where('student_id', $student_id)
                ->where('id', '!=', $id)
                ->first();
                
            if ($existingResult) {
                return response()->json([
                    'message' => 'Exam result already exists for this student and exam'
                ], 422);
            }
        }
        
        $examResult->update($request->all());
        
        return response()->json([
            'data' => $examResult->fresh()->load(['exam.course', 'student'])
        ]);
    }

    /**
     * Remove the specified exam result from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $examResult = ExamResult::find($id);
        
        if (!$examResult) {
            return response()->json([
                'message' => 'Exam result not found'
            ], 404);
        }
        
        $examResult->delete();
        
        return response()->json(null, 204);
    }
}