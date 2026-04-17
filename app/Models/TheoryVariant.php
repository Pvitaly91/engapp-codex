<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TheoryVariant extends Model
{
    protected $fillable = [
        'variantable_type',
        'variantable_id',
        'locale',
        'variant_key',
        'label',
        'provider',
        'model',
        'status',
        'payload',
        'source_hash',
        'prompt_version',
        'seeder_class',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function variantable(): MorphTo
    {
        return $this->morphTo();
    }

    public function selections(): HasMany
    {
        return $this->hasMany(TheoryVariantSelection::class);
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }
}
