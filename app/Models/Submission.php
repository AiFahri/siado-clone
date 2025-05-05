<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'answer',
        'file_url',
        'user_id',
        'assignment_id',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
