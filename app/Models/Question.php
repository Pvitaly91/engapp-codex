<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $casts = [
        'flag' => 'boolean',
    ];

    protected $fillable = ['question', 'difficulty', 'category_id', 'source_id'];

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
        return $this->hasMany(QuestionOption::class);
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    public function verbHints()
    {
        return $this->hasMany(VerbHint::class);
    }
}

