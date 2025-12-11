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

    protected static function booted(): void
    {
        static::creating(function (TextBlock $block) {
            if (blank($block->uuid)) {
                $block->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function category()
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }
}
