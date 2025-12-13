<?php

namespace App\Services\SeedRuns;

use App\Http\Controllers\SeedRunController;
use App\Services\QuestionDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeedRunsService
{
    public function __construct(private QuestionDeletionService $questionDeletionService)
    {
    }

    public function overview(?SeedRunController $controller = null): array
    {
        $controller ??= $this->makeController();

        if (method_exists($controller, 'getSeedRunOverview')) {
            return $controller->getSeedRunOverview();
        }

        return [];
    }

    public function runMissing(?SeedRunController $controller = null): array
    {
        $controller ??= $this->makeController();

        $response = $controller->runMissing($this->jsonRequest('POST'));

        return $this->normalizeResponse($response);
    }

    public function runSeeder(string $className, ?SeedRunController $controller = null): array
    {
        $controller ??= $this->makeController();

        $response = $controller->run($this->jsonRequest('POST', ['class_name' => $className]));

        return $this->normalizeResponse($response);
    }

    public function markSeederExecuted(string $className, ?SeedRunController $controller = null): array
    {
        $controller ??= $this->makeController();

        $response = $controller->markAsExecuted($this->jsonRequest('POST', ['class_name' => $className]));

        return $this->normalizeResponse($response);
    }

    protected function jsonRequest(string $method = 'POST', array $data = []): Request
    {
        $request = Request::create('', $method, $data);
        $request->headers->set('Accept', 'application/json');

        return $request;
    }

    protected function normalizeResponse(JsonResponse|RedirectResponse $response): array
    {
        if ($response instanceof JsonResponse) {
            return [
                'status' => $response->getStatusCode(),
                'ok' => $response->isSuccessful(),
                'data' => $response->getData(true),
            ];
        }

        return [
            'status' => $response->getStatusCode(),
            'ok' => $response->isRedirection(),
            'redirect' => $response->getTargetUrl(),
        ];
    }

    protected function makeController(): SeedRunController
    {
        return new SeedRunController($this->questionDeletionService);
    }
}
