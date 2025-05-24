<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'title',
        'content',
        'course_id',
        'created_at',
        'updated_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}