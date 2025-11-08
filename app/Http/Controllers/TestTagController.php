<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Tag;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use App\Services\TagAggregationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestTagController extends Controller
{
    private const UNCATEGORIZED_KEY = '__uncategorized__';

    private function renderQuestionWithHighlightedAnswers(Question $question): string
    {
        $questionText = e($question->question ?? '');

        foreach ($question->answers as $answer) {
            $answerText = optional($answer->option)->option ?? $answer->answer;

            if (! filled($answerText)) {
                continue;
            }

            $replacement = sprintf(
                '<span class="inline-flex items-center px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold">%s</span>',
                e($answerText)
            );

            $questionText = str_replace('{'.$answer->marker.'}', $replacement, $questionText);
        }

        return nl2br($questionText);
    }

    private function formatQuestionMeta(Question $question): ?string
    {
        $parts = [];

        if (filled($question->difficulty)) {
            $parts[] = 'Складність: '.$question->difficulty;
        }

        if (filled($question->level)) {
            $parts[] = 'Рівень: '.$question->level;
        }

        if (empty($parts)) {
            return 'Додаткова інформація недоступна';
        }

        return implode(' · ', $parts);
    }

    private function renderQuestionAnswersBlock(Question $question): string
    {
        $answers = $question->answers->map(function ($answer) {
            $label = optional($answer->option)->option ?? $answer->answer;

            return [
                'marker' => $answer->marker,
                'label' => $label,
                'option_id' => $answer->option_id,
            ];
        });

        $correctOptionIds = $answers
            ->pluck('option_id')
            ->filter()
            ->unique()
            ->all();

        $options = $question->options
            ->map(function ($option) use ($correctOptionIds) {
                return [
                    'id' => $option->id,
                    'label' => $option->option,
                    'is_correct' => in_array($option->id, $correctOptionIds, true),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $textAnswers = $answers
            ->filter(function ($answer) {
                return empty($answer['option_id']) && filled($answer['label']);
            })
            ->map(function ($answer) {
                return [
                    'marker' => $answer['marker'],
                    'label' => $answer['label'],
                ];
            })
            ->values();

        return view('seed-runs.partials.question-answers', [
            'options' => $options,
            'textAnswers' => $textAnswers,
        ])->render();
    }

    private function resolveCategory(array $data): ?string
    {
        $newCategory = isset($data['new_category']) ? trim((string) $data['new_category']) : '';

        if ($newCategory !== '') {
            return $newCategory;
        }

        $existingCategory = $data['category'] ?? null;

        if ($existingCategory === null || trim((string) $existingCategory) === '') {
            return null;
        }

        return trim((string) $existingCategory);
    }

    private function loadTagData(): array
    {
        $tagsByCategory = Tag::query()
            ->withCount('questions')
            ->orderByRaw('CASE WHEN category IS NULL OR category = "" THEN 1 ELSE 0 END')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Tag $tag) {
                return $tag->category ?: null;
            });

        $tagsByCategory = $tagsByCategory->sortKeysUsing(function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            if ($a === null) {
                return -1;
            }

            if ($b === null) {
                return 1;
            }

            return strnatcasecmp($a, $b);
        });

        $categories = $tagsByCategory
            ->keys()
            ->filter(fn ($category) => $category !== null)
            ->unique(function ($value) {
                return Str::lower($value);
            })
            ->sort(function ($a, $b) {
                return strnatcasecmp($a, $b);
            })
            ->values();

        return [$tagsByCategory, $categories];
    }

    public function index(): View
    {
        [$tagsByCategory, $categories] = $this->loadTagData();

        $tagGroups = $tagsByCategory->map(function ($tags, $category) {
            $isEmpty = $tags->isEmpty() || $tags->every(function (Tag $tag) {
                return (int) $tag->questions_count === 0;
            });

            return [
                'name' => $category,
                'label' => $category ?: 'Без категорії',
                'key' => $this->encodeCategoryParam($category),
                'tags' => $tags,
                'is_empty' => $isEmpty,
            ];
        })->values();

        return view('test-tags.index', [
            'tagGroups' => $tagGroups,
            'totalTags' => $tagsByCategory->sum(fn ($tags) => $tags->count()),
        ]);
    }

    public function create(): View
    {
        [, $categories] = $this->loadTagData();

        return view('test-tags.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tags,name'],
            'category' => ['nullable', 'string', 'max:255'],
            'new_category' => ['nullable', 'string', 'max:255'],
        ]);

        $category = $this->resolveCategory($validated);

        Tag::create([
            'name' => $validated['name'],
            'category' => $category,
        ]);

        return redirect()->route('test-tags.index')->with('status', 'Тег успішно створено.');
    }

    public function edit(Tag $tag): View
    {
        [, $categories] = $this->loadTagData();

        return view('test-tags.edit', [
            'tag' => $tag,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($tag->id)],
            'category' => ['nullable', 'string', 'max:255'],
            'new_category' => ['nullable', 'string', 'max:255'],
        ]);

        $category = $this->resolveCategory($validated);

        $tag->update([
            'name' => $validated['name'],
            'category' => $category,
        ]);

        return redirect()->route('test-tags.index')->with('status', 'Тег оновлено.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->questions()->detach();
        $tag->words()->detach();
        $tag->delete();

        return redirect()->route('test-tags.index')->with('status', 'Тег видалено.');
    }

    public function destroyEmptyTags(): RedirectResponse
    {
        $emptyTags = Tag::query()
            ->whereDoesntHave('questions')
            ->get();

        $count = $emptyTags->count();

        foreach ($emptyTags as $tag) {
            $tag->words()->detach();
            $tag->delete();
        }

        $message = $count > 0
            ? "Видалено тегів без питань: {$count}."
            : 'Не знайдено тегів без питань.';

        return redirect()->route('test-tags.index')->with('status', $message);
    }

    public function questions(Tag $tag): JsonResponse
    {
        $questions = $tag->questions()
            ->with(['answers.option'])
            ->orderBy('id')
            ->get()
            ->map(function (Question $question) use ($tag) {
                $answersUrl = route('test-tags.questions.answers', [$tag, $question->id]);
                $tagsUrl = route('test-tags.questions.tags', [$tag, $question->id]);

                return [
                    'id' => $question->id,
                    'content_html' => $this->renderQuestionWithHighlightedAnswers($question),
                    'meta' => $this->formatQuestionMeta($question),
                    'toggle' => [
                        'url' => $answersUrl,
                        'data' => [
                            'question-id' => $question->id,
                            'tag-id' => $tag->id,
                        ],
                    ],
                    'details' => [
                        'answers' => [
                            'url' => $answersUrl,
                        ],
                        'tags' => [
                            'url' => $tagsUrl,
                        ],
                    ],
                    'container_data' => [
                        'question-container' => true,
                        'question-id' => $question->id,
                        'tag-id' => $tag->id,
                    ],
                ];
            });

        $html = view('admin.questions.list', [
            'questions' => $questions,
            'emptyMessage' => 'Для цього тегу ще не додано питань.',
        ])->render();

        return response()->json([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
            ],
            'html' => $html,
        ]);
    }

    public function questionAnswers(Tag $tag, Question $question): JsonResponse
    {
        $belongsToTag = $question->tags()
            ->where('tags.id', $tag->id)
            ->exists();

        if (! $belongsToTag) {
            return response()->json([
                'html' => '',
                'message' => 'Питання не прив’язане до вибраного тегу.',
            ], 404);
        }

        $question->loadMissing(['answers.option', 'options']);

        return response()->json([
            'html' => $this->renderQuestionAnswersBlock($question),
        ]);
    }

    public function questionTags(Tag $tag, Question $question): JsonResponse
    {
        $belongsToTag = $question->tags()
            ->where('tags.id', $tag->id)
            ->exists();

        if (! $belongsToTag) {
            return response()->json([
                'html' => '',
                'message' => 'Питання не прив’язане до вибраного тегу.',
            ], 404);
        }

        $question->loadMissing('tags');

        $tags = $question->tags
            ->map(function (Tag $relatedTag) {
                return [
                    'id' => $relatedTag->id,
                    'name' => $relatedTag->name,
                    'category' => $relatedTag->category,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json([
            'html' => view('seed-runs.partials.question-tags', [
                'tags' => $tags,
            ])->render(),
        ]);
    }

    public function editCategory(string $category): View
    {
        [, $categories] = $this->loadTagData();

        $resolved = $this->normaliseCategoryParam($category);

        if ($resolved !== null && ! $categories->contains($resolved)) {
            abort(404);
        }

        if ($resolved === null) {
            $hasUncategorised = Tag::query()
                ->whereNull('category')
                ->orWhere('category', '')
                ->exists();

            if (! $hasUncategorised) {
                abort(404);
            }
        }

        return view('test-tags.categories.edit', [
            'category' => $resolved,
            'categoryKey' => $this->encodeCategoryParam($resolved),
        ]);
    }

    public function updateCategory(Request $request, string $category): RedirectResponse
    {
        $resolved = $this->normaliseCategoryParam($category);

        $validated = $request->validate([
            'new_name' => ['required', 'string', 'max:255'],
        ]);

        $newName = trim($validated['new_name']);

        $lowerNewName = Str::lower($newName);
        $currentLower = $resolved !== null ? Str::lower($resolved) : null;

        $duplicateExists = Tag::query()
            ->whereRaw('LOWER(category) = ?', [$lowerNewName])
            ->when($currentLower !== null, function ($query) use ($currentLower) {
                $query->whereRaw('LOWER(category) != ?', [$currentLower]);
            })
            ->exists();

        if ($duplicateExists) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['new_name' => 'Категорія з такою назвою вже існує.']);
        }

        $affected = Tag::query()
            ->when($resolved === null, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('category')->orWhere('category', '');
                });
            }, function ($query) use ($resolved) {
                $query->where('category', $resolved);
            })
            ->update(['category' => $newName]);

        if ($affected === 0) {
            return redirect()->route('test-tags.index')->with('error', 'Категорію не знайдено.');
        }

        return redirect()->route('test-tags.index')->with('status', 'Категорію перейменовано.');
    }

    public function destroyCategory(string $category): RedirectResponse
    {
        $resolved = $this->normaliseCategoryParam($category);

        $tags = Tag::query()
            ->when($resolved === null, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('category')->orWhere('category', '');
                });
            }, function ($query) use ($resolved) {
                $query->where('category', $resolved);
            })
            ->get();

        if ($tags->isEmpty()) {
            return redirect()->route('test-tags.index')->with('error', 'Категорію не знайдено.');
        }

        foreach ($tags as $tag) {
            $tag->questions()->detach();
            $tag->words()->detach();
            $tag->delete();
        }

        return redirect()->route('test-tags.index')->with('status', 'Категорію та всі її теги видалено.');
    }

    private function normaliseCategoryParam(?string $category): ?string
    {
        if ($category === null || $category === '') {
            return null;
        }

        if ($category === self::UNCATEGORIZED_KEY) {
            return null;
        }

        $prefix = 'encoded:';

        if (str_starts_with($category, $prefix)) {
            $decoded = base64_decode(substr($category, strlen($prefix)), true);

            if ($decoded === false) {
                abort(404);
            }

            return $decoded;
        }

        return $category;
    }

    private function encodeCategoryParam(?string $category): string
    {
        if ($category === null || $category === '') {
            return self::UNCATEGORIZED_KEY;
        }

        return 'encoded:'.base64_encode($category);
    }

    public function aggregations(TagAggregationService $service): View
    {
        $aggregations = $service->getAggregations();
        $allTags = Tag::orderBy('name')->get();
        
        // Group tags by category
        $tagsByCategory = $allTags->groupBy(function ($tag) {
            return $tag->category ?? 'Other';
        })->sortKeys();
        
        // Move "Other" to the end if it exists
        if ($tagsByCategory->has('Other')) {
            $other = $tagsByCategory->pull('Other');
            $tagsByCategory->put('Other', $other);
        }
        
        $categories = Tag::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        // Group aggregations by category
        $aggregationsByCategory = collect($aggregations)->groupBy(function ($aggregation) {
            return $aggregation['category'] ?? 'Без категорії';
        })->sortKeys();
        
        // Move "Без категорії" to the end if it exists
        if ($aggregationsByCategory->has('Без категорії')) {
            $uncategorized = $aggregationsByCategory->pull('Без категорії');
            $aggregationsByCategory->put('Без категорії', $uncategorized);
        }

        return view('test-tags.aggregations.index', [
            'aggregations' => $aggregations,
            'aggregationsByCategory' => $aggregationsByCategory,
            'allTags' => $allTags,
            'tagsByCategory' => $tagsByCategory,
            'categories' => $categories,
            'isAutoPage' => false,
        ]);
    }

    public function autoAggregationsPage(TagAggregationService $service): View
    {
        $aggregations = $service->getAggregations();
        $allTags = Tag::orderBy('name')->get();
        
        // Group tags by category
        $tagsByCategory = $allTags->groupBy(function ($tag) {
            return $tag->category ?? 'Other';
        })->sortKeys();
        
        // Move "Other" to the end if it exists
        if ($tagsByCategory->has('Other')) {
            $other = $tagsByCategory->pull('Other');
            $tagsByCategory->put('Other', $other);
        }
        
        $categories = Tag::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        // Group aggregations by category
        $aggregationsByCategory = collect($aggregations)->groupBy(function ($aggregation) {
            return $aggregation['category'] ?? 'Без категорії';
        })->sortKeys();
        
        // Move "Без категорії" to the end if it exists
        if ($aggregationsByCategory->has('Без категорії')) {
            $uncategorized = $aggregationsByCategory->pull('Без категорії');
            $aggregationsByCategory->put('Без категорії', $uncategorized);
        }

        return view('test-tags.aggregations.index', [
            'aggregations' => $aggregations,
            'aggregationsByCategory' => $aggregationsByCategory,
            'allTags' => $allTags,
            'tagsByCategory' => $tagsByCategory,
            'categories' => $categories,
            'isAutoPage' => true,
        ]);
    }

    public function storeAggregation(Request $request, TagAggregationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'main_tag' => ['required', 'string', 'max:255'],
            'similar_tags' => ['required', 'array', 'min:1'],
            'similar_tags.*' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $service->addAggregation(
            $validated['main_tag'],
            $validated['similar_tags'],
            $validated['category'] ?? null
        );

        return redirect()->route('test-tags.aggregations.index')
            ->with('status', 'Агрегацію тегів успішно створено.');
    }

    public function updateAggregation(Request $request, string $mainTag, TagAggregationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'similar_tags' => ['required', 'array', 'min:1'],
            'similar_tags.*' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $updated = $service->updateAggregation(
            $mainTag,
            $validated['similar_tags'],
            $validated['category'] ?? null
        );

        if (! $updated) {
            return redirect()->route('test-tags.aggregations.index')
                ->with('error', 'Агрегацію не знайдено.');
        }

        return redirect()->route('test-tags.aggregations.index')
            ->with('status', 'Агрегацію тегів успішно оновлено.');
    }

    public function destroyAggregation(string $mainTag, TagAggregationService $service): RedirectResponse
    {
        $service->removeAggregation($mainTag);

        return redirect()->route('test-tags.aggregations.index')
            ->with('status', 'Агрегацію тегів видалено.');
    }

    public function autoAggregations(GeminiService $gemini, TagAggregationService $service): RedirectResponse
    {
        $tags = Tag::orderBy('name')->get();

        if ($tags->isEmpty()) {
            return redirect()->route('test-tags.aggregations.index')
                ->with('error', 'Немає тегів для агрегації.');
        }

        $tagNames = $tags->pluck('name')->toArray();
        $tagCategories = $tags->pluck('category', 'name')->toArray();

        try {
            $suggestions = $gemini->suggestTagAggregations($tagNames);

            $count = 0;
            foreach ($suggestions as $suggestion) {
                // Get category from the main tag
                $category = $tagCategories[$suggestion['main_tag']] ?? null;
                
                $service->addAggregation(
                    $suggestion['main_tag'],
                    $suggestion['similar_tags'],
                    $category
                );
                $count++;
            }

            return redirect()->route('test-tags.aggregations.index')
                ->with('status', "Автоматично створено агрегацій: {$count}.");
        } catch (\Exception $e) {
            Log::error('Gemini auto aggregation failed: '.$e->getMessage(), [
                'exception' => $e,
                'tags_count' => count($tagNames),
            ]);

            return redirect()->route('test-tags.aggregations.index')
                ->with('error', 'Помилка Gemini: '.$e->getMessage());
        }
    }

    public function autoAggregationsChatGPT(ChatGPTService $chatGPT, TagAggregationService $service): RedirectResponse
    {
        $tags = Tag::orderBy('name')->get();

        if ($tags->isEmpty()) {
            return redirect()->route('test-tags.aggregations.index')
                ->with('error', 'Немає тегів для агрегації.');
        }

        $tagNames = $tags->pluck('name')->toArray();
        $tagCategories = $tags->pluck('category', 'name')->toArray();

        try {
            $suggestions = $chatGPT->suggestTagAggregations($tagNames);

            $count = 0;
            foreach ($suggestions as $suggestion) {
                // Get category from the main tag
                $category = $tagCategories[$suggestion['main_tag']] ?? null;
                
                $service->addAggregation(
                    $suggestion['main_tag'],
                    $suggestion['similar_tags'],
                    $category
                );
                $count++;
            }

            return redirect()->route('test-tags.aggregations.index')
                ->with('status', "Автоматично створено агрегацій: {$count}.");
        } catch (\Exception $e) {
            Log::error('ChatGPT auto aggregation failed: '.$e->getMessage(), [
                'exception' => $e,
                'tags_count' => count($tagNames),
            ]);

            return redirect()->route('test-tags.aggregations.index')
                ->with('error', 'Помилка ChatGPT: '.$e->getMessage());
        }
    }

    public function generateAggregationPrompt(): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();

        if ($tags->isEmpty()) {
            return response()->json([
                'error' => 'Немає тегів для агрегації.',
            ], 400);
        }

        $tagNames = $tags->pluck('name')->toArray();
        $tagsList = implode(', ', $tagNames);

        $prompt = "You are a grammar tag analyzer. Analyze the following list of English grammar tags and suggest aggregations.\n\n";
        $prompt .= "Tags: {$tagsList}\n\n";
        $prompt .= "Group similar or related tags together. For each group, identify:\n";
        $prompt .= "1. A main_tag (the most general or commonly used tag in the group)\n";
        $prompt .= "2. similar_tags (array of related tags that should be aggregated under the main tag)\n\n";
        $prompt .= "Rules:\n";
        $prompt .= "- Only group tags that are clearly related or synonyms\n";
        $prompt .= "- Each tag should appear only once in the result\n";
        $prompt .= "- Don't create aggregations for tags that are clearly distinct\n";
        $prompt .= "- similar_tags should not include the main_tag itself\n";
        $prompt .= "- DO NOT include category field, it will be added automatically\n\n";
        $prompt .= "Respond strictly in JSON format as an array of objects:\n";
        $prompt .= '[{"main_tag": "Present Simple", "similar_tags": ["Simple Present", "Present Tense"]}, ...]';

        return response()->json([
            'prompt' => $prompt,
            'tags_count' => count($tagNames),
        ]);
    }

    public function importAggregations(Request $request, TagAggregationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'json_data' => ['required', 'string'],
        ]);

        try {
            $data = json_decode($validated['json_data'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Невалідний JSON формат: ' . json_last_error_msg());
            }

            // Support both formats:
            // 1. API format: [{main_tag: "...", similar_tags: [...]}, ...]
            // 2. Wrapped format: {aggregations: [{...}]}
            $aggregations = null;
            if (is_array($data)) {
                if (isset($data['aggregations']) && is_array($data['aggregations'])) {
                    // Wrapped format
                    $aggregations = $data['aggregations'];
                } elseif (isset($data[0]) && isset($data[0]['main_tag'])) {
                    // Direct array format (API response)
                    $aggregations = $data;
                }
            }

            if ($aggregations === null) {
                throw new \InvalidArgumentException('JSON має бути масивом агрегацій [{main_tag: "...", similar_tags: [...]}] або об\'єктом з полем "aggregations"');
            }

            // Get tag categories for auto-assignment
            $tagCategories = Tag::pluck('category', 'name')->toArray();

            // Validate and enrich each aggregation structure
            foreach ($aggregations as $index => &$aggregation) {
                if (!isset($aggregation['main_tag']) || !is_string($aggregation['main_tag'])) {
                    throw new \InvalidArgumentException("Агрегація #{$index} не містить валідного поля 'main_tag'");
                }

                if (!isset($aggregation['similar_tags']) || !is_array($aggregation['similar_tags'])) {
                    throw new \InvalidArgumentException("Агрегація #{$index} не містить валідного поля 'similar_tags'");
                }

                // Auto-assign category from main_tag if not provided
                if (!isset($aggregation['category']) || $aggregation['category'] === null) {
                    $aggregation['category'] = $tagCategories[$aggregation['main_tag']] ?? null;
                }

                // Validate category if provided
                if (isset($aggregation['category']) && !is_string($aggregation['category']) && $aggregation['category'] !== null) {
                    throw new \InvalidArgumentException("Агрегація #{$index} містить невалідне поле 'category'");
                }
            }

            // Save the aggregations
            $service->saveAggregations($aggregations);

            return redirect()->route('test-tags.aggregations.index')
                ->with('status', 'JSON успішно імпортовано. Збережено агрегацій: ' . count($aggregations) . '.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('test-tags.aggregations.index')
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Import aggregations failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('test-tags.aggregations.index')
                ->with('error', 'Помилка імпорту: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateAggregationCategory(Request $request, string $category, TagAggregationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'new_name' => ['required', 'string', 'max:255'],
        ]);

        $newName = trim($validated['new_name']);
        $aggregations = $service->getAggregations();
        $updated = false;

        foreach ($aggregations as &$aggregation) {
            if (($aggregation['category'] ?? '') === $category) {
                $aggregation['category'] = $newName;
                $updated = true;
            }
        }

        if ($updated) {
            $service->saveAggregations($aggregations);
            return redirect()->route('test-tags.aggregations.index')
                ->with('status', 'Категорію успішно оновлено.');
        }

        return redirect()->route('test-tags.aggregations.index')
            ->with('error', 'Категорію не знайдено.');
    }

    public function destroyAggregationCategory(string $category, TagAggregationService $service): RedirectResponse
    {
        $aggregations = $service->getAggregations();
        
        // Filter out all aggregations from this category
        $aggregations = array_filter($aggregations, function ($aggregation) use ($category) {
            return ($aggregation['category'] ?? '') !== $category;
        });

        $service->saveAggregations(array_values($aggregations));

        return redirect()->route('test-tags.aggregations.index')
            ->with('status', 'Категорію та всі її агрегації видалено.');
    }
}
