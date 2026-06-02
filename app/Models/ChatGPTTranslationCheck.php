<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGPTTranslationCheck extends Model
{
    protected $table = 'chatgpt_translation_checks';

    protected $fillable = [
        'original',
        'reference',
        'user_text',
        'language',
        'is_correct',
        'explanation',
    ];
}

