<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TextBlock extends Model
{
    protected $fillable = [
        'page_id',
        'locale',
        'type',
        'column',
        'heading',
        'css_class',
        'sort_order',
        'body',
        'seeder',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
