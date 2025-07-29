<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'tag_category_id'];

    public function words()
    {
        return $this->belongsToMany(Word::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }

    public function category()
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }
}
