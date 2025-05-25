<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'credits',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function lecturers()
    {
        return $this->belongsToMany(User::class, 'course_lecturers');
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($course) {
            $course->assignments()->delete();
            $course->materials()->delete();
            $course->students()->detach();
            $course->lecturers()->detach();
        });
    }
}
