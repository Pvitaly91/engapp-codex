<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGPTExplanation extends Model
{
    protected $table = "chatgpt_explanations";   
    protected $fillable = [
        'question',
        'wrong_answer',
        'correct_answer',
        'language',
        'explanation',
    ];
}
