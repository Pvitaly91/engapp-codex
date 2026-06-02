<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedGrammarTestQuestion extends Model
{
    protected $fillable = [
        'question_uuid',
        'position',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(SavedGrammarTest::class, 'saved_grammar_test_id');
    }
}
