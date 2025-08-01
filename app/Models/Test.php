<?php

// app/Models/Test.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = ['name', 'slug', 'filters', 'questions', 'description'];
    protected $casts = [
        'filters' => 'array',
        'questions' => 'array',
    ];
}
