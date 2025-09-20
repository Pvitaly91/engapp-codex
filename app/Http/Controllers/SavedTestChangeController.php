<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Test;
use App\Services\PendingTestChangeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SavedTestChangeController extends Controller
{
    public function __construct(private PendingTestChangeRepository $repository)
    {
    }

    public function index(Request $request, string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $changes = collect($this->repository->all($slug));

        $html = view('engram.partials.saved-test-tech-change-list', [
            'test' => $test,
            'changes' => $changes,
            'returnUrl' => route('saved-test.tech', $test->slug),
        ])->render();

        return response()->json([
            'html' => $html,
            'change_count' => $changes->count(),
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'route' => ['required', 'string'],
            'route_params' => ['nullable', 'array'],
            'method' => ['required', 'string', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])],
            'payload' => ['nullable', 'array'],
            'change_type' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:255'],
            'question_id' => ['nullable', 'integer'],
        ]);

        $payload = collect($data['payload'] ?? [])
            ->reject(fn ($value, $key) => in_array($key, ['_token', '_method', 'from'], true))
            ->all();

        $change = [
            'route' => $data['route'],
            'route_params' => $this->normalizeRouteParams($data['route_params'] ?? []),
            'method' => strtoupper($data['method']),
            'payload' => $payload,
            'change_type' => $data['change_type'] ?? 'generic',
            'summary' => $data['summary'] ?? null,
            'question_id' => $data['question_id'] ?? null,
        ];

        if ($change['question_id']) {
            $question = Question::find($change['question_id']);
            if ($question) {
                $change['question_preview'] = Str::limit(trim(strip_tags($question->question ?? '')), 200);
            }
        }

        $stored = $this->repository->add($slug, $change);

        if ($request->expectsJson()) {
            return response()->json([
                'change' => $stored,
                'change_count' => collect($this->repository->all($slug))->count(),
            ], 201);
        }

        return redirect()->route('saved-test.tech', [$test->slug]);
    }

    public function apply(Request $request, string $slug, string $changeId)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $change = $this->repository->find($slug, $changeId);

        if (! $change) {
            abort(404, 'Change not found.');
        }

        try {
            $this->executeChange($change);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                $message = $exception->getMessage() ?: 'Не вдалося застосувати зміну.';

                return response()->json([
                    'message' => $message,
                ], 500);
            }

            throw $exception;
        }

        $this->repository->remove($slug, $changeId);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'applied',
                'change_id' => $changeId,
            ]);
        }

        return redirect()->route('saved-test.tech', [$test->slug]);
    }

    public function destroy(Request $request, string $slug, string $changeId)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $change = $this->repository->find($slug, $changeId);

        if (! $change) {
            abort(404, 'Change not found.');
        }

        $this->repository->remove($slug, $changeId);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'deleted',
                'change_id' => $changeId,
            ]);
        }

        return redirect()->route('saved-test.tech', [$test->slug]);
    }

    private function normalizeRouteParams(array $params): array
    {
        return collect($params)
            ->map(fn ($value) => is_numeric($value) ? (int) $value : $value)
            ->all();
    }

    private function executeChange(array $change)
    {
        $routeName = Arr::get($change, 'route');
        $routeParams = Arr::get($change, 'route_params', []);
        $method = Arr::get($change, 'method', 'POST');
        $payload = Arr::get($change, 'payload', []);

        if (! $routeName) {
            throw new \RuntimeException('Route name is missing for queued change.');
        }

        $url = route($routeName, $routeParams);
        $fakeRequest = Request::create($url, $method, $payload);
        $fakeRequest->headers->set('Accept', 'application/json');
        $fakeRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

        $router = app('router');
        $route = $router->getRoutes()->match($fakeRequest);
        $route->setContainer(app());
        $route->bind($fakeRequest);

        $currentRequest = request();
        app()->instance('request', $fakeRequest);

        try {
            return app()->call($route->getAction(), array_merge($route->parameters(), [
                'request' => $fakeRequest,
            ]));
        } finally {
            app()->instance('request', $currentRequest);
        }
    }
}
