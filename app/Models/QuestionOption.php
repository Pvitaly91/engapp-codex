<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'option'];
    public function question()
    {
        return $this->belongsTo(Question::class);
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
