<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPolyglotLessonProgress extends Model
{
    protected $table = 'user_polyglot_lesson_progress';

    protected $fillable = [
        'user_id',
        'course_slug',
        'lesson_slug',
        'lesson_index',
        'answered_count',
        'last_100_count',
        'last_100_average',
        'is_completed',
        'completed_at',
        'unlocked_at',
        'metadata',
    ];

    protected $casts = [
        'lesson_index' => 'integer',
        'answered_count' => 'integer',
        'last_100_count' => 'integer',
        'last_100_average' => 'float',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
