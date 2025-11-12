<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'text', 'img', 'seeder', 'page_category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }

    public function textBlocks(): HasMany
    {
        return $this->hasMany(TextBlock::class)->orderBy('sort_order');
    }

    /**
     * Get the image path for this page, checking multiple locations.
     */
    public function getImagePath(): ?string
    {
        if (empty($this->img)) {
            return null;
        }

        // Check in /public/uploads first
        if (file_exists(public_path($this->img))) {
            return $this->img;
        }

        // Then check in /frontend/web/uploads
        $basename = basename($this->img);
        $frontendPath = base_path('frontend/web/uploads/'.$basename);
        if (file_exists($frontendPath)) {
            return '/frontend/web/uploads/'.$basename;
        }

        return null;
    }

    /**
     * Get the icon path for this page (for catalog listings).
     * Falls back to default icon if no custom icon is set.
     */
    public function getIconPath(): ?string
    {
        $customPath = $this->getImagePath();

        if ($customPath) {
            return $customPath;
        }

        // Use default icon if no custom icon found
        $defaultIcon = '/img/catalog/ico45.png';
        if (file_exists(public_path($defaultIcon))) {
            return $defaultIcon;
        }

        return null;
    }
}
