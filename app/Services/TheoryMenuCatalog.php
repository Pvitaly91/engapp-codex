<?php

namespace App\Services;

use App\Http\Controllers\TheoryController;
use Illuminate\Support\Collection;

/** The explicit build shares the existing tree algorithm but not its hour-long caches. */
class TheoryMenuCatalog
{
    public function fresh(): Collection
    {
        return app(TheoryController::class)->freshMenuCategories();
    }
}
