<?php

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseRepository
{
    /**
     * Get filtered and paginated courses
     */
    public function getFilteredCourses(Request $request): LengthAwarePaginator
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
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find course by ID
     */
    public function findById(string $id)
    {
        return Course::with('department')->find($id);
    }

    /**
     * Create a new course
     */
    public function create(array $data)
    {
        return Course::create($data);
    }

    /**
     * Update an existing course
     */
    public function update(Course $course, array $data)
    {
        $course->update($data);
        return $course->fresh()->load('department');
    }

    /**
     * Delete a course
     */
    public function delete(Course $course): bool
    {
        return $course->delete();
    }
}