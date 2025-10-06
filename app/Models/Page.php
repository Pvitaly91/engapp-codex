<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'text'];

    public function textBlocks()
    {
        return $this->hasMany(TextBlock::class)->orderBy('sort_order');
    }
}
