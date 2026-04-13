<?php

namespace App\Models;

use App\Support\AiOutputSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ChatGPTExplanation extends Model
{
    protected $table = 'chatgpt_explanations';

    protected $fillable = [
        'question',
        'wrong_answer',
        'correct_answer',
        'language',
        'explanation',
    ];

    protected function explanation(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => AiOutputSanitizer::sanitize($value),
        );
    }
}
