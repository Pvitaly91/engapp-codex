<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionDumpState extends Model
{
    protected $fillable = ['question_uuid', 'file_hash', 'applied_at'];

    protected $casts = [
        'applied_at' => 'datetime',
    ];
}
