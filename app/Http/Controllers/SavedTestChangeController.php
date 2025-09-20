<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Test;
use App\Services\PendingTestChangeRepository;
use App\Services\QuestionSnapshotRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SavedTestChangeController extends Controller
{
    public function __construct(
        private PendingTestChangeRepository $repository,
        private QuestionSnapshotRepository $snapshotRepository
    ) {
    }

    public function index(Request $request, string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $grouped = collect($this->repository->groupedByQuestion($slug))
            ->map(fn (array $changes) => collect($changes));
        $globalChanges = collect($this->repository->allForQuestion($slug, null));
        $changeCount = $this->repository->count($slug);

        $questionSnapshots = collect();

        if ($grouped->isNotEmpty()) {
            $questionSnapshots = $grouped->keys()
                ->filter(fn ($key) => $key !== null)
                ->mapWithKeys(function ($questionId) {
                    $snapshot = $this->snapshotRepository->getOrCreate((int) $questionId);

                    return $snapshot ? [(int) $questionId => $snapshot] : [];
                });
        }

        $html = view('engram.partials.saved-test-tech-change-list', [
            'test' => $test,
            'groupedChanges' => $grouped,
            'globalChanges' => $globalChanges,
            'questionSnapshots' => $questionSnapshots,
        ])->render();

        return response()->json([
            'html' => $html,
            'change_count' => $changeCount,
        ]);
    }

    public function showForQuestion(Request $request, string $slug, int $questionId)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $changes = collect($this->repository->allForQuestion($slug, $questionId));
        $snapshot = $this->snapshotRepository->getOrCreate($questionId);

        $html = view('engram.partials.saved-test-tech-question-change-list', [
            'test' => $test,
            'questionId' => $questionId,
            'changes' => $changes,
            'questionSnapshot' => $snapshot,
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

        $routeName = $data['route'];
        $routeParams = $this->normalizeRouteParams($data['route_params'] ?? []);

        if ($routeName && Str::startsWith($routeName, 'question-answers.')) {
            $payloadAnswerId = Arr::get($payload, 'question_answer_id');

            if ($payloadAnswerId !== null && $payloadAnswerId !== '' && ! array_key_exists('questionAnswer', $routeParams)) {
                $routeParams['questionAnswer'] = is_numeric($payloadAnswerId)
                    ? (int) $payloadAnswerId
                    : $payloadAnswerId;
            }
        }

        $change = [
            'route' => $routeName,
            'route_params' => $routeParams,
            'method' => strtoupper($data['method']),
            'payload' => $payload,
            'change_type' => $data['change_type'] ?? 'generic',
            'summary' => $data['summary'] ?? null,
            'question_id' => $data['question_id'] ?? null,
        ];

        if ($change['question_id']) {
            $snapshot = $this->snapshotRepository->getOrCreate((int) $change['question_id']);

            if ($snapshot) {
                $questionText = trim(strip_tags((string) Arr::get($snapshot, 'question', '')));

                if ($questionText !== '') {
                    $change['question_preview'] = Str::limit($questionText, 200);
                }
            }
        }

        $stored = $this->repository->add($slug, $change);

        if ($request->expectsJson()) {
            return response()->json([
                'change' => $stored,
                'change_count' => $this->repository->count($slug),
                'question_id' => $stored['question_id'],
                'question_change_count' => $stored['question_id'] !== null
                    ? $this->repository->countForQuestion($slug, $stored['question_id'])
                    : null,
            ], 201);
        }

        return redirect()->route('saved-test.tech', [$test->slug]);
    }

    public function apply(Request $request, string $slug, string $changeId)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $found = $this->repository->find($slug, $changeId);

        if (! $found) {
            abort(404, 'Change not found.');
        }

        $change = $found['change'];
        $questionId = $found['question_id'];

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

        if ($questionId !== null) {
            $question = Question::find($questionId);

            if ($question) {
                $this->snapshotRepository->sync($question);
            } else {
                $this->snapshotRepository->delete($questionId);
            }
        }

        $this->repository->remove($slug, $changeId, $questionId);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'applied',
                'change_id' => $changeId,
                'change_count' => $this->repository->count($slug),
                'question_id' => $questionId,
                'question_change_count' => $questionId !== null
                    ? $this->repository->countForQuestion($slug, $questionId)
                    : null,
            ]);
        }

        return redirect()->route('saved-test.tech', [$test->slug]);
    }

    public function destroy(Request $request, string $slug, string $changeId)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $found = $this->repository->find($slug, $changeId);

        if (! $found) {
            abort(404, 'Change not found.');
        }

        $questionId = $found['question_id'];

        $this->repository->remove($slug, $changeId, $questionId);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'deleted',
                'change_id' => $changeId,
                'change_count' => $this->repository->count($slug),
                'question_id' => $questionId,
                'question_change_count' => $questionId !== null
                    ? $this->repository->countForQuestion($slug, $questionId)
                    : null,
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

        if (! is_array($routeParams)) {
            $routeParams = [];
        }

        if (! is_array($payload)) {
            $payload = [];
        }

        if ($routeName && Str::startsWith($routeName, 'question-answers.')
            && ! array_key_exists('questionAnswer', $routeParams)
        ) {
            $payloadAnswerId = Arr::get($payload, 'question_answer_id');

            if ($payloadAnswerId !== null && $payloadAnswerId !== '') {
                $routeParams['questionAnswer'] = is_numeric($payloadAnswerId)
                    ? (int) $payloadAnswerId
                    : $payloadAnswerId;
            }
        }

        $questionId = Arr::get($change, 'question_id');

        if (! array_key_exists('question', $routeParams) && $questionId !== null) {
            $routeParams['question'] = $questionId;
        }

        $url = route($routeName, $routeParams);
        $fakeRequest = Request::create($url, $method, $payload);
        $fakeRequest->headers->set('Accept', 'application/json');
        $fakeRequest->headers->set('X-Requested-With', 'XMLHttpRequest');

        $router = app('router');
        $route = $router->getRoutes()->match($fakeRequest);
        $route->setContainer(app());
        $route->bind($fakeRequest);
        $router->substituteBindings($route);
        $router->substituteImplicitBindings($route);

        $action = $route->getAction();
        $callable = $action['uses'] ?? ($action['controller'] ?? null);

        if ($callable === null) {
            throw new \RuntimeException('Route action is missing a callable handler.');
        }

        $currentRequest = request();
        app()->instance('request', $fakeRequest);

        try {
            return app()->call($callable, array_merge($route->parameters(), [
                'request' => $fakeRequest,
            ]));
        } finally {
            app()->instance('request', $currentRequest);
        }
    }
}
