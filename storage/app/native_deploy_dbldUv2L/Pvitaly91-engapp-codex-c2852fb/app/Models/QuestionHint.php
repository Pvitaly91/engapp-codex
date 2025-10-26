<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionHint extends Model
{
    protected $fillable = ['question_id','provider','locale','hint'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
