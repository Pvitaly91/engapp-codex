<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestTagController extends Controller
{
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

    public function index(): View
    {
        $tags = Tag::query()
            ->orderByRaw('CASE WHEN category IS NULL OR category = "" THEN 1 ELSE 0 END')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Tag $tag) {
                return $tag->category ?: null;
            });

        return view('test-tags.index', [
            'tagsByCategory' => $tags,
            'categories' => Tag::query()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
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

    public function updateCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'original_category' => ['required', 'string'],
            'new_name' => ['required', 'string', 'max:255'],
        ]);

        $original = trim($validated['original_category']);
        $newName = trim($validated['new_name']);

        $affected = Tag::where('category', $original)
            ->update(['category' => $newName]);

        if ($affected === 0) {
            return redirect()->route('test-tags.index')->with('error', 'Категорію не знайдено.');
        }

        return redirect()->route('test-tags.index')->with('status', 'Категорію перейменовано.');
    }

    public function destroyCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
        ]);

        $category = trim($validated['category']);

        $tags = Tag::where('category', $category)->get();

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
}
