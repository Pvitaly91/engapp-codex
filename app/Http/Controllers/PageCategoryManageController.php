<?php

namespace App\Http\Controllers;

use App\Models\PageCategory;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageCategoryManageController extends Controller
{
    public function index()
    {
        $categories = PageCategory::query()
            ->withCount(['pages', 'tags'])
            ->orderBy('title')
            ->get();

        return view('engram.page-categories.manage.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $category = new PageCategory();
        $allTags = Tag::orderBy('name')->get();

        return view('engram.page-categories.manage.create', [
            'category' => $category,
            'allTags' => $allTags,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $category = PageCategory::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'language' => $validated['language'] ?? 'uk',
        ]);

        // Sync tags
        if (isset($validated['tags'])) {
            $category->tags()->sync($validated['tags']);
        }

        return redirect()
            ->route('page-categories.manage.index')
            ->with('status', 'Категорію створено.');
    }

    public function edit(PageCategory $category)
    {
        $category->load('tags');
        $allTags = Tag::orderBy('name')->get();

        return view('engram.page-categories.manage.edit', [
            'category' => $category,
            'allTags' => $allTags,
        ]);
    }

    public function update(Request $request, PageCategory $category): RedirectResponse
    {
        $validated = $this->validatedData($request, $category);

        $category->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'language' => $validated['language'] ?? 'uk',
        ])->save();

        // Sync tags
        if (isset($validated['tags'])) {
            $category->tags()->sync($validated['tags']);
        } else {
            $category->tags()->sync([]);
        }

        return back()->with('status', 'Зміни збережено.');
    }

    public function destroy(PageCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('page-categories.manage.index')
            ->with('status', 'Категорію видалено.');
    }

    protected function validatedData(Request $request, ?PageCategory $category = null): array
    {
        $categoryId = $category?->getKey();

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('page_categories', 'slug')->ignore($categoryId),
            ],
            'language' => ['nullable', 'string', 'max:8'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);
    }
}
