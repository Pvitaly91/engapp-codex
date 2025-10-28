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
            $filters = $this->extractFilters($request);

            $preview = $this->fetcher->getPreview($table, $page, $perPage, $sort, $direction, $filters);

            return response()->json($preview);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    /**
     * @return array<int, array{column: string, operator: string, value?: mixed}>
     */
    private function extractFilters(Request $request): array
    {
        $filters = $request->query('filters', []);

        if (!is_array($filters)) {
            return [];
        }

        return collect($filters)
            ->filter(fn ($filter) => is_array($filter))
            ->map(function (array $filter): array {
                $column = isset($filter['column']) && is_string($filter['column'])
                    ? trim($filter['column'])
                    : '';
                $operator = isset($filter['operator']) && is_string($filter['operator'])
                    ? trim($filter['operator'])
                    : '';
                $value = $filter['value'] ?? null;

                $payload = [
                    'column' => $column,
                    'operator' => $operator,
                ];

                if (array_key_exists('value', $filter)) {
                    $payload['value'] = $value;
                }

                return $payload;
            })
            ->values()
            ->all();
    }
}
