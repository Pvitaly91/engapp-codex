<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
    ];

 
    public function translates()
    {
        return $this->hasMany(Translate::class);
    }

  
    public function translate($lang = 'uk')
    {
        return $this->hasOne(Translate::class)->where('lang', $lang);
    }
}
