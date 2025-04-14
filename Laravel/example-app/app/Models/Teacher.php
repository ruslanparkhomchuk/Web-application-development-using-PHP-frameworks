<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    /**
     * Get the classes taught by the teacher.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Get the department headed by the teacher.
     */
    public function headedDepartment()
    {
        return $this->hasOne(Department::class, 'head_id');
    }
}