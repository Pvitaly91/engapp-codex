<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneratePageV3PromptRequest;
use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Services\PageV3PromptGenerator\PageV3PromptGeneratorService;
use App\Services\PageV3PromptGenerator\TheoryCategorySearchService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PageV3PromptGeneratorController extends Controller
{
    public function __construct(
        private PageV3PromptGeneratorService $pageV3PromptGeneratorService,
        private TheoryCategorySearchService $theoryCategorySearchService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.page-v3-prompt-generator.index', $this->viewData($request));
    }

    public function generate(GeneratePageV3PromptRequest $request): View
    {
        try {
            $result = $this->pageV3PromptGeneratorService->generate(
                PagePromptGenerationInput::fromArray($request->validated())
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                $this->errorKey($request) => $exception->getMessage(),
            ]);
        }

        return view('admin.page-v3-prompt-generator.index', $this->viewData($request, $result));
    }

    /**
     * @param  array<string, mixed>|null  $result
     * @return array<string, mixed>
     */
    private function viewData(Request $request, ?array $result = null): array
    {
        $form = [
            'source_type' => old('source_type', (string) $request->input('source_type', 'manual_topic')),
            'manual_topic' => old('manual_topic', (string) $request->input('manual_topic', '')),
            'external_url' => old('external_url', (string) $request->input('external_url', '')),
            'category_mode' => old('category_mode', (string) $request->input('category_mode', 'existing')),
            'existing_category_id' => old('existing_category_id', $request->input('existing_category_id')),
            'new_category_title' => old('new_category_title', (string) $request->input('new_category_title', '')),
            'generation_mode' => old('generation_mode', (string) $request->input('generation_mode', 'single')),
            'prompt_a_mode' => old('prompt_a_mode', (string) $request->input('prompt_a_mode', 'repository_connected')),
        ];

        $selectedCategory = null;

        if (($result['category']['mode'] ?? null) === 'existing') {
            $selectedCategory = $result['category']['selected_category'] ?? null;
        } elseif (! empty($form['existing_category_id']) && $form['category_mode'] === 'existing') {
            $selectedCategory = $this->theoryCategorySearchService->findSummaryById((int) $form['existing_category_id']);
        }

        $previewTopic = $this->resolvePreviewTopic($form, $result);

        return [
            'form' => $form,
            'categoryModes' => $this->pageV3PromptGeneratorService->categoryModes(),
            'generationModes' => $this->pageV3PromptGeneratorService->generationModes(),
            'promptAModes' => $this->pageV3PromptGeneratorService->promptAModes(),
            'categoryOptions' => $this->pageV3PromptGeneratorService->categoryOptions(),
            'selectedCategory' => $selectedCategory,
            'preview' => $this->pageV3PromptGeneratorService->buildPreview(
                $previewTopic,
                (string) $form['category_mode'],
                $selectedCategory,
                (string) $form['new_category_title'],
            ),
            'result' => $result,
            'promptCards' => is_array($result) ? array_values((array) ($result['prompts'] ?? [])) : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @param  array<string, mixed>|null  $result
     */
    private function resolvePreviewTopic(array $form, ?array $result): ?string
    {
        if (! empty($result['source']['topic'])) {
            return (string) $result['source']['topic'];
        }

        if (($form['source_type'] ?? 'manual_topic') === 'external_url' && filled($form['external_url'])) {
            return $this->pageV3PromptGeneratorService->topicFromExternalUrl((string) $form['external_url']);
        }

        return filled($form['manual_topic']) ? (string) $form['manual_topic'] : null;
    }

    private function errorKey(Request $request): string
    {
        return match ((string) $request->input('category_mode', 'existing')) {
            'existing' => (string) $request->input('source_type', 'manual_topic') === 'external_url'
                ? 'external_url'
                : 'existing_category_id',
            'new' => (string) $request->input('source_type', 'manual_topic') === 'external_url'
                ? 'external_url'
                : 'new_category_title',
            default => (string) $request->input('source_type', 'manual_topic') === 'external_url'
                ? 'external_url'
                : 'manual_topic',
        };
    }
}
