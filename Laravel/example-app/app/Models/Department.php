<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
        'head_id',
    ];

    /**
     * Get the department head (teacher).
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_id');
    }
}