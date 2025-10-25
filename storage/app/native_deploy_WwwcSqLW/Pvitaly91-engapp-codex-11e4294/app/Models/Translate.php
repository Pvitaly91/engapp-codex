<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translate extends Model
{
    use HasFactory;

    protected $fillable = [
        'word_id',
        'lang',
        'translation',
    ];

    // Зв'язок: переклад належить до слова
    public function word()
    {
        return $this->belongsTo(Word::class);
    }
}
