<?php

namespace App\Models;

use App\Support\AiOutputSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class QuestionHint extends Model
{
    protected $fillable = ['question_id', 'provider', 'locale', 'hint'];

    protected function hint(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => AiOutputSanitizer::sanitize($value),
        );
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
