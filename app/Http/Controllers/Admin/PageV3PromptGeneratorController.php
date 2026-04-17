<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneratePageV3PromptRequest;
use App\Services\PageV3PromptGenerator\Data\PagePromptGenerationInput;
use App\Services\PageV3PromptGenerator\Data\TheoryVariantPromptGenerationInput;
use App\Services\PageV3PromptGenerator\PageV3PromptGeneratorService;
use App\Services\PageV3PromptGenerator\TheoryVariantSourceResolver;
use App\Services\PageV3PromptGenerator\TheoryCategorySearchService;
use App\Services\PageV3PromptGenerator\VariantSeederPromptBuilder;
use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PageV3PromptGeneratorController extends Controller
{
    public function __construct(
        private PageV3PromptGeneratorService $pageV3PromptGeneratorService,
        private TheoryCategorySearchService $theoryCategorySearchService,
        private TheoryVariantSourceResolver $theoryVariantSourceResolver,
        private VariantSeederPromptBuilder $variantSeederPromptBuilder,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.page-v3-prompt-generator.index', $this->viewData($request));
    }

    public function generate(GeneratePageV3PromptRequest $request): View
    {
        $generatorMode = (string) $request->validated('generator_mode', 'page_v3');

        try {
            if ($generatorMode === 'theory_variant') {
                $variantResult = $this->variantSeederPromptBuilder->generate(
                    TheoryVariantPromptGenerationInput::fromArray($request->validated())
                );

                return view('admin.page-v3-prompt-generator.index', $this->viewData($request, null, $variantResult));
            }

            $pageV3Result = $this->pageV3PromptGeneratorService->generate(PagePromptGenerationInput::fromArray($request->validated()));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                $this->errorKey($request, $generatorMode) => $exception->getMessage(),
            ]);
        }

        return view('admin.page-v3-prompt-generator.index', $this->viewData($request, $pageV3Result));
    }

    /**
     * @param  array<string, mixed>|null  $pageV3Result
     * @param  array<string, mixed>|null  $variantResult
     * @return array<string, mixed>
     */
    private function viewData(Request $request, ?array $pageV3Result = null, ?array $variantResult = null): array
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

        if (($pageV3Result['category']['mode'] ?? null) === 'existing') {
            $selectedCategory = $pageV3Result['category']['selected_category'] ?? null;
        } elseif (! empty($form['existing_category_id']) && $form['category_mode'] === 'existing') {
            $selectedCategory = $this->theoryCategorySearchService->findSummaryById((int) $form['existing_category_id']);
        }

        $variantFormResult = is_array($variantResult['form'] ?? null) ? $variantResult['form'] : [];
        $defaultLocale = old('locale', (string) $request->input('locale', LocaleService::getDefaultLocaleCode()));
        $variantForm = [
            'source_lookup_url' => old('source_lookup_url', (string) $request->input('source_lookup_url', $variantFormResult['source_lookup_url'] ?? '')),
            'target_type' => old('target_type', (string) $request->input('target_type', $variantFormResult['target_type'] ?? 'page')),
            'target_category_slug' => old('target_category_slug', (string) $request->input('target_category_slug', $variantFormResult['target_category_slug'] ?? '')),
            'target_page_slug' => old('target_page_slug', (string) $request->input('target_page_slug', $variantFormResult['target_page_slug'] ?? '')),
            'locale' => $defaultLocale,
            'namespace' => old('namespace', (string) $request->input('namespace', $variantFormResult['namespace'] ?? '')),
            'class_name' => old('class_name', (string) $request->input('class_name', $variantFormResult['class_name'] ?? '')),
            'variant_key' => old('variant_key', (string) $request->input('variant_key', $variantFormResult['variant_key'] ?? '')),
            'label' => old('label', (string) $request->input('label', $variantFormResult['label'] ?? '')),
            'provider' => old('provider', (string) $request->input('provider', $variantFormResult['provider'] ?? '')),
            'model' => old('model', (string) $request->input('model', $variantFormResult['model'] ?? '')),
            'prompt_version' => old('prompt_version', (string) $request->input('prompt_version', $variantFormResult['prompt_version'] ?? 'v1')),
            'source_url' => old('source_url', (string) $request->input('source_url', $variantFormResult['source_url'] ?? '')),
            'source_page_title' => old('source_page_title', (string) $request->input('source_page_title', $variantFormResult['source_page_title'] ?? '')),
            'source_category_title' => old('source_category_title', (string) $request->input('source_category_title', $variantFormResult['source_category_title'] ?? '')),
            'source_page_seeder_class' => old('source_page_seeder_class', (string) $request->input('source_page_seeder_class', $variantFormResult['source_page_seeder_class'] ?? '')),
            'target_learner_levels' => old('target_learner_levels', (string) $request->input('target_learner_levels', $variantFormResult['target_learner_levels'] ?? '')),
            'tone' => old('tone', (string) $request->input('tone', $variantFormResult['tone'] ?? '')),
            'rewrite_goal' => old('rewrite_goal', (string) $request->input('rewrite_goal', $variantFormResult['rewrite_goal'] ?? '')),
            'content_strategy' => old('content_strategy', (string) $request->input('content_strategy', $variantFormResult['content_strategy'] ?? '')),
            'must_cover_list' => old('must_cover_list', (string) $request->input('must_cover_list', $variantFormResult['must_cover_list'] ?? '')),
            'avoid_list' => old('avoid_list', (string) $request->input('avoid_list', $variantFormResult['avoid_list'] ?? '')),
            'editor_notes' => old('editor_notes', (string) $request->input('editor_notes', $variantFormResult['editor_notes'] ?? '')),
            'output_mode_preference' => old('output_mode_preference', (string) $request->input('output_mode_preference', $variantFormResult['output_mode_preference'] ?? 'downloadable_php_file')),
        ];

        $previewTopic = $this->resolvePreviewTopic($form, $pageV3Result);
        $activeGeneratorMode = old(
            'generator_mode',
            (string) $request->input('generator_mode', $variantResult !== null ? 'theory_variant' : 'page_v3')
        );

        return [
            'activeGeneratorMode' => $activeGeneratorMode,
            'form' => $form,
            'variantForm' => $variantForm,
            'categoryModes' => $this->pageV3PromptGeneratorService->categoryModes(),
            'generationModes' => $this->pageV3PromptGeneratorService->generationModes(),
            'promptAModes' => $this->pageV3PromptGeneratorService->promptAModes(),
            'categoryOptions' => $this->pageV3PromptGeneratorService->categoryOptions(),
            'pageOptions' => $this->theoryVariantSourceResolver->pageOptions(),
            'localeOptions' => $this->theoryVariantSourceResolver->localeOptions(),
            'defaultLocale' => LocaleService::getDefaultLocaleCode(),
            'selectedCategory' => $selectedCategory,
            'preview' => $this->pageV3PromptGeneratorService->buildPreview(
                $previewTopic,
                (string) $form['category_mode'],
                $selectedCategory,
                (string) $form['new_category_title'],
            ),
            'pageV3Result' => $pageV3Result,
            'variantResult' => $variantResult,
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

    private function errorKey(Request $request, string $generatorMode): string
    {
        if ($generatorMode === 'theory_variant') {
            return $this->variantErrorKey($request);
        }

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

    private function variantErrorKey(Request $request): string
    {
        if (filled($request->input('source_lookup_url'))) {
            return 'source_lookup_url';
        }

        if (! filled($request->input('target_category_slug'))) {
            return 'target_category_slug';
        }

        return (string) $request->input('target_type', 'page') === 'page'
            ? 'target_page_slug'
            : 'target_category_slug';
    }
}
