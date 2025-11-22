<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Scope to find tests that have questions with matching tags.
     *
     * @param  Builder  $query
     * @param  array  $tagIds
     * @return Builder
     */
    public function scopeWithMatchingTags(Builder $query, array $tagIds): Builder
    {
        if (empty($tagIds)) {
            return $query->whereRaw('1 = 0'); // Return no results if no tags provided
        }

        return $query->whereHas('questionLinks', function ($q) use ($tagIds) {
            $q->whereExists(function ($subQuery) use ($tagIds) {
                $subQuery->select(\DB::raw(1))
                    ->from('questions')
                    ->join('question_tag', 'questions.id', '=', 'question_tag.question_id')
                    ->whereColumn('questions.uuid', 'saved_grammar_test_questions.question_uuid')
                    ->whereIn('question_tag.tag_id', $tagIds);
            });
        });
    }
}
