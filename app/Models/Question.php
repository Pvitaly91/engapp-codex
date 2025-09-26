<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use App\Models\QuestionVariant;
use App\Models\QuestionHint;

class Question extends Model
{
    protected $casts = [
        'flag' => 'integer',
    ];


    public function renderQuestionText(): string
    {
        $questionText = $this->question;

        foreach ($this->answers as $answer) {
            $replacement = $answer->option->option ?? $answer->answer;
            $questionText = str_replace('{' . $answer->marker . '}', $replacement, $questionText);
        }

        return $questionText;
    }

    protected $fillable = ['uuid', 'question', 'difficulty', 'level', 'category_id', 'source_id', 'flag'];

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
