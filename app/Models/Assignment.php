<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $hidden = [
        'updated_at',
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
