<?php

namespace App\Modules\DatabaseStructure\Http\Controllers;

use App\Modules\DatabaseStructure\Services\DatabaseStructureFetcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;

class DatabaseStructureController
{
    public function __construct(private DatabaseStructureFetcher $fetcher)
    {
    }

    public function __invoke(): View|ViewFactory
    {
        $structure = $this->fetcher->getStructure();
        $meta = $this->fetcher->getMeta();

        return view('database-structure::index', [
            'structure' => $structure,
            'meta' => $meta,
        ]);
    }
}
