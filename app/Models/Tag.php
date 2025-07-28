<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'category'];

    public function words()
    {
        return $this->belongsToMany(Word::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }
}
