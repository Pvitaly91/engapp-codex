<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = ['option'];
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_option_question', 'option_id', 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class, 'option_id');
    }

    public function verbHints()
    {
        return $this->hasMany(VerbHint::class, 'option_id');
    }
}
