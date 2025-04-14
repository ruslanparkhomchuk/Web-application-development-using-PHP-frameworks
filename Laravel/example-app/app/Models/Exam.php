<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'date',
        'duration',
        'location',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the course that owns the exam.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the exam results for the exam.
     */
    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}