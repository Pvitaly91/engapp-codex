<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TheoryVariantSelection extends Model
{
    protected $fillable = [
        'variantable_type',
        'variantable_id',
        'locale',
        'theory_variant_id',
    ];

    public function variantable(): MorphTo
    {
        return $this->morphTo();
    }

    public function theoryVariant(): BelongsTo
    {
        return $this->belongsTo(TheoryVariant::class);
    }
}
