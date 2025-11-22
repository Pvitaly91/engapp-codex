<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'category'];

    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class);
    }

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class);
    }

    public function pageCategories(): BelongsToMany
    {
        return $this->belongsToMany(PageCategory::class);
    }
}
