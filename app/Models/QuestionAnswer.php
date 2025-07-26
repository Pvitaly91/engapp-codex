<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    protected $fillable = ['question_id', 'marker', 'answer', 'verb_hint'];
    public function question() { return $this->belongsTo(Question::class); }
}