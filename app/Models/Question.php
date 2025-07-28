<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;

class Question extends Model
{
    protected $casts = [
        'flag' => 'boolean',
    ];

    protected $fillable = ['uuid', 'question', 'difficulty', 'category_id', 'source_id'];

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
}
