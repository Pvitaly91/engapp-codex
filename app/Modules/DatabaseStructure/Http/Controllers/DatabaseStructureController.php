<?php

namespace App\Modules\DatabaseStructure\Http\Controllers;

use App\Modules\DatabaseStructure\Services\DatabaseStructureFetcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class DatabaseStructureController
{
    public function __construct(private DatabaseStructureFetcher $fetcher)
    {
    }

    public function index(): View|ViewFactory
    {
        $structure = $this->fetcher->getStructure();
        $meta = $this->fetcher->getMeta();

        return view('database-structure::index', [
            'structure' => $structure,
            'meta' => $meta,
        ]);
    }

    public function records(string $table): JsonResponse
    {
        try {
            $preview = $this->fetcher->getPreview($table);

            return response()->json($preview);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }
}
