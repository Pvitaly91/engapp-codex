<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'text', 'seeder', 'page_category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }

    public function textBlocks(): HasMany
    {
        return $this->hasMany(TextBlock::class)->orderBy('sort_order');
    }
}
