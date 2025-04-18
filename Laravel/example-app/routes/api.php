<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Student routes
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/{id}', [StudentController::class, 'show']);
Route::post('/students', [StudentController::class, 'store']);
Route::put('/students/{id}', [StudentController::class, 'update']);
Route::delete('/students/{id}', [StudentController::class, 'destroy']);

// Teacher routes
Route::get('/teachers', [TeacherController::class, 'index']);
Route::get('/teachers/{id}', [TeacherController::class, 'show']);
Route::post('/teachers', [TeacherController::class, 'store']);
Route::put('/teachers/{id}', [TeacherController::class, 'update']);
Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);

// Department routes
Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/{id}', [DepartmentController::class, 'show']);
Route::post('/departments', [DepartmentController::class, 'store']);
Route::put('/departments/{id}', [DepartmentController::class, 'update']);
Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

// Course routes
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::post('/courses', [CourseController::class, 'store']);
Route::put('/courses/{id}', [CourseController::class, 'update']);
Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

// Class routes
Route::get('/classes', [ClassController::class, 'index']);
Route::get('/classes/{id}', [ClassController::class, 'show']);
Route::post('/classes', [ClassController::class, 'store']);
Route::put('/classes/{id}', [ClassController::class, 'update']);
Route::delete('/classes/{id}', [ClassController::class, 'destroy']);

// Attendance routes
Route::get('/attendances', [AttendanceController::class, 'index']);
Route::get('/attendances/{id}', [AttendanceController::class, 'show']);
Route::post('/attendances', [AttendanceController::class, 'store']);
Route::put('/attendances/{id}', [AttendanceController::class, 'update']);
Route::delete('/attendances/{id}', [AttendanceController::class, 'destroy']);

// Exam routes
Route::get('/exams', [ExamController::class, 'index']);
Route::get('/exams/{id}', [ExamController::class, 'show']);
Route::post('/exams', [ExamController::class, 'store']);
Route::put('/exams/{id}', [ExamController::class, 'update']);
Route::delete('/exams/{id}', [ExamController::class, 'destroy']);

// Exam Result routes
Route::get('/exam-results', [ExamResultController::class, 'index']);
Route::get('/exam-results/{id}', [ExamResultController::class, 'show']);
Route::post('/exam-results', [ExamResultController::class, 'store']);
Route::put('/exam-results/{id}', [ExamResultController::class, 'update']);
Route::delete('/exam-results/{id}', [ExamResultController::class, 'destroy']);

// Authentication Routes
Route::group(['prefix' => 'auth'], function () {
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login', [AuthController::class, 'login']);
  
  Route::group(['middleware' => 'jwt.verify'], function () {
      Route::post('logout', [AuthController::class, 'logout']);
      Route::post('refresh', [AuthController::class, 'refresh']);
      Route::get('me', [AuthController::class, 'me']);
  });
});

// User Routes with Role-Based Access Control
Route::group(['prefix' => 'users', 'middleware' => 'jwt.verify'], function () {
  // Route accessible to all authenticated users (their own profile)
  Route::get('profile', [AuthController::class, 'me']);
  
  // Routes accessible to Manager and Admin only
  Route::group(['middleware' => 'role:manager,admin'], function () {
      Route::get('', [UserController::class, 'index']);
  });
  
  // Routes with specific role requirements
  Route::get('{id}', [UserController::class, 'show']); // Access controlled in controller
  Route::put('{id}', [UserController::class, 'update']); // Access controlled in controller
  
  // Routes accessible to Admin only
  Route::group(['middleware' => 'role:admin'], function () {
      Route::post('', [UserController::class, 'store']);
  });
  
  // Routes accessible to Manager and Admin
  Route::group(['middleware' => 'role:manager,admin'], function () {
      Route::delete('{id}', [UserController::class, 'destroy']); // Further access controlled in controller
  });
});
