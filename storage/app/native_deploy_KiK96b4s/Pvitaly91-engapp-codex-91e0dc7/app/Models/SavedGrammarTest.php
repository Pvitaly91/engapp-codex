<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavedGrammarTest extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'filters',
        'description',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function questionLinks(): HasMany
    {
        return $this->hasMany(SavedGrammarTestQuestion::class)->orderBy('position');
    }

    public function getQuestionUuidsAttribute(): array
    {
        return $this->relationLoaded('questionLinks')
            ? $this->questionLinks->pluck('question_uuid')->filter()->values()->all()
            : $this->questionLinks()->pluck('question_uuid')->filter()->values()->all();
    }
}
