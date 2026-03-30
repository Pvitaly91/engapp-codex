<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateV3PromptRequest;
use App\Services\V3PromptGenerator\Data\PromptGenerationInput;
use App\Services\V3PromptGenerator\TheoryPageSearchService;
use App\Services\V3PromptGenerator\V3PromptGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class V3PromptGeneratorController extends Controller
{
    public function __construct(
        private V3PromptGeneratorService $v3PromptGeneratorService,
        private TheoryPageSearchService $theoryPageSearchService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.v3-prompt-generator.index', $this->viewData($request));
    }

    public function generate(GenerateV3PromptRequest $request): View
    {
        try {
            $result = $this->v3PromptGeneratorService->generate(
                PromptGenerationInput::fromArray($request->validated())
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                $this->sourceErrorKey((string) $request->input('source_type', 'manual_topic')) => $exception->getMessage(),
            ]);
        }

        return view('admin.v3-prompt-generator.index', $this->viewData($request, $result));
    }

    public function searchTheoryPages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'site_domain' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'results' => $this->theoryPageSearchService->search(
                (string) ($validated['q'] ?? ''),
                12,
                (string) ($validated['site_domain'] ?? 'gramlyze.com'),
            ),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $result
     * @return array<string, mixed>
     */
    private function viewData(Request $request, ?array $result = null): array
    {
        $form = [
            'source_type' => old('source_type', (string) $request->input('source_type', 'theory_page')),
            'generation_mode' => old('generation_mode', (string) $request->input('generation_mode', 'single')),
            'theory_page_id' => old('theory_page_id', $request->input('theory_page_id')),
            'manual_topic' => old('manual_topic', (string) $request->input('manual_topic', '')),
            'external_url' => old('external_url', (string) $request->input('external_url', '')),
            'site_domain' => old('site_domain', (string) $request->input('site_domain', 'gramlyze.com')),
            'target_namespace' => old('target_namespace', (string) $request->input('target_namespace', 'IA\\ChatGptPro')),
            'levels' => old('levels', $request->input('levels', ['A1'])),
            'questions_per_level' => old('questions_per_level', $request->input('questions_per_level', 5)),
        ];

        $selectedTheoryPage = null;

        if (($result['source']['source_type'] ?? null) === 'theory_page') {
            $selectedTheoryPage = $result['source'];
        } elseif (! empty($form['theory_page_id'])) {
            $selectedTheoryPage = $this->theoryPageSearchService->findSummaryById(
                (int) $form['theory_page_id'],
                (string) $form['site_domain'],
            );
        }

        $previewTopic = $this->resolvePreviewTopic($form, $selectedTheoryPage, $result);

        return [
            'form' => $form,
            'availableLevels' => $this->v3PromptGeneratorService->levels(),
            'namespaceSuggestions' => $this->v3PromptGeneratorService->namespaceSuggestions(),
            'generationModes' => [
                'single' => 'Mode 1: One prompt for Codex',
                'split' => 'Mode 2: Two prompts (LLM JSON + Codex seeder)',
            ],
            'selectedTheoryPage' => $selectedTheoryPage,
            'preview' => $this->v3PromptGeneratorService->buildPreview((string) $form['target_namespace'], $previewTopic),
            'result' => $result,
            'searchRoute' => route('v3-prompt-generator.theory-pages.search'),
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @param  array<string, mixed>|null  $selectedTheoryPage
     * @param  array<string, mixed>|null  $result
     */
    private function resolvePreviewTopic(array $form, ?array $selectedTheoryPage, ?array $result): ?string
    {
        if (! empty($result['source']['topic'])) {
            return (string) $result['source']['topic'];
        }

        return match ((string) ($form['source_type'] ?? 'manual_topic')) {
            'theory_page' => $selectedTheoryPage['title'] ?? null,
            'external_url' => filled($form['external_url'])
                ? $this->v3PromptGeneratorService->topicFromExternalUrl((string) $form['external_url'])
                : null,
            default => filled($form['manual_topic']) ? (string) $form['manual_topic'] : null,
        };
    }

    private function sourceErrorKey(string $sourceType): string
    {
        return match ($sourceType) {
            'theory_page' => 'theory_page_id',
            'external_url' => 'external_url',
            default => 'manual_topic',
        };
    }
}
