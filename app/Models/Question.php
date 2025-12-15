<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public const TYPE_MATCH = '1';

    public const TYPE_DIALOGUE = '2';

    public const TYPE_DRAG_DROP = '3';

    protected $casts = [
        'flag' => 'integer',
    ];

    public function renderQuestionText(): string
    {
        $questionText = $this->question;

        foreach ($this->answers as $answer) {
            $replacement = $answer->option->option ?? $answer->answer;
            $questionText = str_replace('{'.$answer->marker.'}', $replacement, $questionText);
        }

        return $questionText;
    }

    protected $fillable = ['uuid', 'question', 'difficulty', 'level', 'theory_text_block_uuid', 'category_id', 'source_id', 'flag', 'seeder', 'type'];

    /**
     * Get the theory text block linked to this question.
     */
    public function theoryTextBlock()
    {
        return $this->belongsTo(TextBlock::class, 'theory_text_block_uuid', 'uuid');
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_MATCH => 'Match (відповідність пар)',
            self::TYPE_DIALOGUE => 'Dialogue (діалог)',
            self::TYPE_DRAG_DROP => 'Drag & Drop (перетягування)',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function options()
    {
        return $this->belongsToMany(
            QuestionOption::class,
            'question_option_question',
            'question_id',
            'option_id'
        )->where(function ($query) {
            $query->whereNull('question_option_question.flag')
                ->orWhere('question_option_question.flag', '=', 0);
        })->withPivot('flag');
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    public function verbHints()
    {
        return $this->hasMany(VerbHint::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get tags for specific markers (a1, a2, etc.) via the question_marker_tag pivot table.
     */
    public function markerTags()
    {
        return $this->belongsToMany(Tag::class, 'question_marker_tag')
            ->withPivot('marker')
            ->withTimestamps();
    }

    /**
     * Get tags for a specific marker.
     *
     * @param  string  $marker  The marker name (e.g., 'a1', 'a2')
     * @return \Illuminate\Database\Eloquent\Collection<Tag>
     */
    public function getTagsForMarker(string $marker)
    {
        return $this->markerTags()->wherePivot('marker', $marker)->get();
    }

    public function hints()
    {
        return $this->hasMany(QuestionHint::class);
    }

    public function variants()
    {
        return $this->hasMany(QuestionVariant::class);
    }

    public function chatgptExplanations()
    {
        return $this->hasMany(ChatGPTExplanation::class, 'question', 'question');
    }
}
