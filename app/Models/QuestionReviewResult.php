<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Question;

class QuestionReviewResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answers',
        'tags',
        'comment',
    ];

    protected $casts = [
        'answers' => 'array',
        'tags' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
