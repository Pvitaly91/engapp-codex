<?php

namespace App\Modules\DatabaseStructure\Http\Controllers;

use App\Modules\DatabaseStructure\Services\DatabaseStructureFetcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function records(Request $request, string $table): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query('page', 1));
            $perPage = max(1, min((int) $request->query('per_page', 20), 100));
            $sort = $request->query('sort');
            $direction = strtolower((string) $request->query('direction', 'asc'));

            $preview = $this->fetcher->getPreview($table, $page, $perPage, $sort, $direction);

            return response()->json($preview);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }
}
