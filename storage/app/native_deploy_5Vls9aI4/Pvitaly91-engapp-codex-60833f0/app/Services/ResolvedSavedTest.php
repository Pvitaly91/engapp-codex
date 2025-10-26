<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ResolvedSavedTest
{
    public function __construct(
        public Model $model,
        public Collection $questionIds,
        public Collection $questionUuids,
        public bool $usesUuidLinks,
        public ?Collection $preloadedQuestions = null,
        public bool $isFilterBased = false,
    ) {
    }
}
