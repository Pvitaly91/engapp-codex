<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPolyglotAnswerAttempt extends Model
{
    protected $table = 'user_polyglot_answer_attempts';

    protected $fillable = [
        'user_id',
        'course_slug',
        'lesson_slug',
        'question_uuid',
        'rating',
        'is_correct',
        'answer_payload',
        'client_attempt_uuid',
        'answered_at',
    ];

    protected $casts = [
        'rating' => 'float',
        'is_correct' => 'boolean',
        'answer_payload' => 'array',
        'answered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
