<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestTagController extends Controller
{
    private const UNCATEGORIZED_KEY = '__uncategorized__';

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

        $categories = Tag::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

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

    public function questions(Tag $tag): JsonResponse
    {
        $questions = $tag->questions()
            ->with(['answers.option'])
            ->orderBy('id')
            ->get()
            ->map(function (Question $question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'rendered_question' => $question->renderQuestionText(),
                    'difficulty' => $question->difficulty,
                    'level' => $question->level,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'marker' => $answer->marker,
                            'answer' => $answer->answer,
                            'option' => optional($answer->option)->option,
                            'rendered_answer' => optional($answer->option)->option ?? $answer->answer,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
            ],
            'questions' => $questions,
        ]);
    }

    public function editCategory(string $category): View
    {
        [, $categories] = $this->loadTagData();

        $resolved = $this->normaliseCategoryParam($category);

        if ($resolved !== null && !$categories->contains($resolved)) {
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

        return 'encoded:' . base64_encode($category);
    }
}
