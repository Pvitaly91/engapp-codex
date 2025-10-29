<?php

namespace App\Modules\DatabaseStructure\Http\Controllers;

use App\Modules\DatabaseStructure\Services\DatabaseStructureFetcher;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

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
            $search = $request->query('search');

            $preview = $this->fetcher->getPreview($table, $page, $perPage, $sort, $direction, $filters, $search);

            return response()->json($preview);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }
    }

    public function destroy(Request $request, string $table): JsonResponse
    {
        try {
            $identifiers = $this->extractIdentifiers($request);

            if (empty($identifiers)) {
                throw new RuntimeException('Не вдалося визначити ідентифікатори запису для видалення.');
            }

            $deleted = $this->fetcher->deleteRecord($table, $identifiers);

            return response()->json([
                'deleted' => $deleted,
                'message' => 'Запис успішно видалено.',
            ]);
        } catch (RuntimeException $exception) {
            $status = str_contains($exception->getMessage(), 'Table') ? 404 : 422;

            return response()->json([
                'message' => $exception->getMessage(),
            ], $status);
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

    /**
     * @return array<int, array{column: string, value: mixed}>
     */
    private function extractIdentifiers(Request $request): array
    {
        $identifiers = $request->input('identifiers', []);

        if (!is_array($identifiers)) {
            return [];
        }

        return collect($identifiers)
            ->filter(fn ($identifier) => is_array($identifier))
            ->map(function (array $identifier): ?array {
                $column = isset($identifier['column']) && is_string($identifier['column'])
                    ? trim($identifier['column'])
                    : '';

                if ($column === '') {
                    return null;
                }

                if (!array_key_exists('value', $identifier)) {
                    return null;
                }

                $value = $identifier['value'];

                if ($value !== null && !is_scalar($value)) {
                    return null;
                }

                return [
                    'column' => $column,
                    'value' => $value,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
