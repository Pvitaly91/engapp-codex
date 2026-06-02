<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneratePolyglotV3PromptRequest;
use App\Services\PolyglotV3PromptGeneratorService;
use App\Services\V3PromptGenerator\TheoryPageSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PolyglotV3PromptGeneratorController extends Controller
{
    public function __construct(
        private PolyglotV3PromptGeneratorService $polyglotV3PromptGeneratorService,
        private TheoryPageSearchService $theoryPageSearchService,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.polyglot-v3-prompt-generator.index', $this->viewData($request));
    }

    public function generate(GeneratePolyglotV3PromptRequest $request): View
    {
        $validated = $request->validated();
        $selectedTheoryPage = $this->resolveSelectedTheoryPage((int) $validated['theory_page_id']);
        $input = array_merge(
            Arr::except($validated, ['theory_page_id']),
            [
                'theory_category_slug' => (string) ($selectedTheoryPage['category_slug_path'] ?? ''),
                'theory_page_slug' => (string) ($selectedTheoryPage['slug'] ?? ''),
            ],
        );

        try {
            $result = $this->polyglotV3PromptGeneratorService->generate($input);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'theory_page_id' => $exception->getMessage(),
            ]);
        }

        return view('admin.polyglot-v3-prompt-generator.index', $this->viewData($request, $result, $selectedTheoryPage));
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
     * @param  array<string, mixed>|null  $selectedTheoryPage
     * @return array<string, mixed>
     */
    private function viewData(Request $request, ?array $result = null, ?array $selectedTheoryPage = null): array
    {
        $form = [
            'theory_page_id' => old('theory_page_id', $request->input('theory_page_id')),
            'lesson_slug' => old('lesson_slug', (string) $request->input('lesson_slug', '')),
            'lesson_order' => old('lesson_order', $request->input('lesson_order', 1)),
            'lesson_title' => old('lesson_title', (string) $request->input('lesson_title', '')),
            'topic' => old('topic', (string) $request->input('topic', '')),
            'level' => old('level', (string) $request->input('level', 'A1')),
            'course_slug' => old('course_slug', (string) $request->input('course_slug', 'polyglot-english-a1')),
            'previous_lesson_slug' => old('previous_lesson_slug', (string) $request->input('previous_lesson_slug', '')),
            'next_lesson_slug' => old('next_lesson_slug', (string) $request->input('next_lesson_slug', '')),
            'items_count' => old('items_count', $request->input('items_count', 24)),
            'seeder_class_base_name' => old('seeder_class_base_name', (string) $request->input('seeder_class_base_name', '')),
            'prompt_id' => old('prompt_id', (string) $request->input('prompt_id', '')),
        ];

        $promptId = is_array($result) ? trim((string) ($result['prompt_id'] ?? '')) : '';
        $summary = is_array($result) ? (array) ($result['summary'] ?? []) : [];
        $promptIdText = $promptId !== ''
            ? $this->polyglotV3PromptGeneratorService->formatPromptIdLine($promptId)
            : null;
        $summaryTopText = $promptId !== ''
            ? $this->polyglotV3PromptGeneratorService->formatSummaryBlock('Top', $promptId, $summary)
            : null;
        $summaryBottomText = $promptId !== ''
            ? $this->polyglotV3PromptGeneratorService->formatSummaryBlock('Bottom', $promptId, $summary)
            : null;

        return [
            'form' => $form,
            'availableLevels' => GeneratePolyglotV3PromptRequest::CEFR_LEVELS,
            'selectedTheoryPage' => $selectedTheoryPage ?? $this->selectedTheoryPageForView($form, $result),
            'result' => $result,
            'promptIdText' => $promptIdText,
            'summaryTopText' => $summaryTopText,
            'summaryBottomText' => $summaryBottomText,
            'promptCard' => $promptId !== ''
                ? [
                    'key' => 'polyglot',
                    'title' => 'Generated prompt',
                    'prompt_id' => $promptId,
                    'prompt_id_text' => $promptIdText,
                    'summary' => $summary,
                    'summary_top_text' => $summaryTopText,
                    'summary_bottom_text' => $summaryBottomText,
                    'text' => (string) ($result['prompt_text'] ?? ''),
                ]
                : null,
            'searchRoute' => route('polyglot-v3-prompt-generator.theory-pages.search'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSelectedTheoryPage(int $pageId): array
    {
        $selectedTheoryPage = $this->theoryPageSearchService->findSummaryById($pageId);

        if ($selectedTheoryPage === null) {
            throw ValidationException::withMessages([
                'theory_page_id' => 'Обрана theory page не знайдена або недоступна.',
            ]);
        }

        return $selectedTheoryPage;
    }

    /**
     * @param  array<string, mixed>  $form
     * @param  array<string, mixed>|null  $result
     * @return array<string, mixed>|null
     */
    private function selectedTheoryPageForView(array $form, ?array $result): ?array
    {
        if (is_array($result)) {
            $fromResult = $this->selectedTheoryPageFromResult($result);

            if ($fromResult !== null) {
                $databasePageId = (int) ($fromResult['id'] ?? 0);

                if ($databasePageId > 0) {
                    return $this->theoryPageSearchService->findSummaryById($databasePageId) ?? $fromResult;
                }

                return $fromResult;
            }
        }

        $theoryPageId = (int) ($form['theory_page_id'] ?? 0);

        return $theoryPageId > 0
            ? $this->theoryPageSearchService->findSummaryById($theoryPageId)
            : null;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>|null
     */
    private function selectedTheoryPageFromResult(array $result): ?array
    {
        $context = $result['theory_context'] ?? null;

        if (! is_array($context) || empty($context['page_slug'])) {
            return null;
        }

        return [
            'id' => $context['database_page_id'] ?? null,
            'title' => $context['page_title'] ?? null,
            'slug' => $context['page_slug'] ?? null,
            'category_path' => $context['category_title_path'] ?? null,
            'category_slug_path' => $context['category_slug_path'] ?? null,
            'url' => $context['route_url'] ?? null,
            'page_seeder_class' => $context['page_seeder_class'] ?? null,
            'resolved_seeder_class' => $context['page_seeder_class'] ?? null,
            'excerpt' => null,
        ];
    }
}
