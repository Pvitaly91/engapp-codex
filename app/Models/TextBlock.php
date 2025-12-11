<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TextBlock extends Model
{
    protected $fillable = [
        'uuid',
        'page_id',
        'page_category_id',
        'locale',
        'type',
        'column',
        'heading',
        'css_class',
        'sort_order',
        'body',
        'level',
        'seeder',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function category()
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }
}
