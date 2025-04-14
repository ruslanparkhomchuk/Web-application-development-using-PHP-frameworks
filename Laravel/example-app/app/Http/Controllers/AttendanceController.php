<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendances.
     */
    public function index(): JsonResponse
    {
        $attendances = Attendance::with(['student', 'class.course', 'class.teacher'])->get();
        
        return response()->json([
            'data' => $attendances
        ]);
    }

    /**
     * Display the specified attendance.
     */
    public function show(string $id): JsonResponse
    {
        $attendance = Attendance::with(['student', 'class.course', 'class.teacher'])->find($id);
        
        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance record not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $attendance
        ]);
    }

    /**
     * Store a newly created attendance in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'status' => 'required|string|in:present,absent,late,excused',
            'remark' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if student exists
        $student = Student::find($request->student_id);
        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }
        
        // Check if class exists
        $class = ClassModel::find($request->class_id);
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
            ], 404);
        }
        
        // Check if attendance record already exists for this student, class, and date
        $existingAttendance = Attendance::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->first();
            
        if ($existingAttendance) {
            return response()->json([
                'message' => 'Attendance record already exists for this student, class, and date'
            ], 422);
        }
        
        $attendance = Attendance::create($request->all());
        
        return response()->json([
            'data' => $attendance->load(['student', 'class.course', 'class.teacher'])
        ], 201);
    }

    /**
     * Update the specified attendance in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance record not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'student_id' => 'sometimes|required|exists:students,id',
            'class_id' => 'sometimes|required|exists:classes,id',
            'date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|in:present,absent,late,excused',
            'remark' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // If any of the keys are changing, check for potential duplicates
        if ($request->has('student_id') || $request->has('class_id') || $request->has('date')) {
            $student_id = $request->student_id ?? $attendance->student_id;
            $class_id = $request->class_id ?? $attendance->class_id;
            $date = $request->date ?? $attendance->date;
            
            $existingAttendance = Attendance::where('student_id', $student_id)
                ->where('class_id', $class_id)
                ->where('date', $date)
                ->where('id', '!=', $id)
                ->first();
                
            if ($existingAttendance) {
                return response()->json([
                    'message' => 'Attendance record already exists for this student, class, and date'
                ], 422);
            }
        }
        
        $attendance->update($request->all());
        
        return response()->json([
            'data' => $attendance->fresh()->load(['student', 'class.course', 'class.teacher'])
        ]);
    }

    /**
     * Remove the specified attendance from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return response()->json([
                'message' => 'Attendance record not found'
            ], 404);
        }
        
        $attendance->delete();
        
        return response()->json(null, 204);
    }
}