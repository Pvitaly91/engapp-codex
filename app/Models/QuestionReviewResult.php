<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionReviewResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answers',
        'tags',
    ];

    protected $casts = [
        'answers' => 'array',
        'tags' => 'array',
    ];
}
