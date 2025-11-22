<?php

namespace App\Modules\PageManager\Http\Controllers;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class PageManagerController extends Controller
{
    public function index(Request $request)
    {
        $pages = Page::query()
            ->with(['category', 'textBlocks'])
            ->orderBy('title')
            ->get();

        $categories = PageCategory::query()
            ->withCount('pages')
            ->with(['textBlocks', 'tags'])
            ->orderBy('title')
            ->get();

        $tagsByCategory = $this->tagOptions();

        $activeTab = $request->query('tab', 'pages');

        $editingCategory = null;
        if ($request->filled('edit')) {
            $activeTab = 'categories';

            $categoryId = (int) $request->query('edit');
            $editingCategory = $categories->firstWhere('id', $categoryId);
        }

        $exportFileExists = file_exists(config_path('pages/exported_pages.json'));

        return view('page-manager::index', [
            'pages' => $pages,
            'categories' => $categories,
            'activeTab' => $activeTab,
            'editingCategory' => $editingCategory,
            'tagsByCategory' => $tagsByCategory,
            'exportFileExists' => $exportFileExists,
        ]);
    }

    public function create()
    {
        $page = new Page();
        $categories = $this->categories();
        $tagsByCategory = $this->tagOptions();

        return view('page-manager::create', [
            'page' => $page,
            'categories' => $categories,
            'tagsByCategory' => $tagsByCategory,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $tagIds = $validated['tags'] ?? [];
        unset($validated['tags']);

        $page = Page::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'text' => $validated['text'] ?? '',
            'page_category_id' => $validated['page_category_id'],
        ]);

        $this->syncTags($page, $tagIds);

        return redirect()
            ->route('pages.manage.edit', $page)
            ->with('status', 'Сторінку створено. Додайте або оновіть її блоки нижче.');
    }

    public function edit(Page $page)
    {
        $blocks = $page->textBlocks()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $categories = $this->categories();
        $tagsByCategory = $this->tagOptions();

        $page->loadMissing('tags');

        return view('page-manager::edit', [
            'page' => $page,
            'blocks' => $blocks,
            'categories' => $categories,
            'tagsByCategory' => $tagsByCategory,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validatedData($request, $page);

        $tagIds = $validated['tags'] ?? [];
        unset($validated['tags']);

        $page->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'text' => $validated['text'] ?? '',
            'page_category_id' => $validated['page_category_id'],
        ])->save();

        $this->syncTags($page, $tagIds);

        return back()->with('status', 'Зміни збережено.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('pages.manage.index')
            ->with('status', 'Сторінку видалено.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validatedCategoryData($request);

        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        $category = PageCategory::create($data);

        $this->syncTags($category, $tagIds);

        return redirect()
            ->route('pages.manage.index', ['tab' => 'categories'])
            ->with('status', 'Категорію створено.');
    }

    public function updateCategory(Request $request, PageCategory $category): RedirectResponse
    {
        $data = $this->validatedCategoryData($request, $category);

        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        $category->fill($data)->save();

        $this->syncTags($category, $tagIds);

        return redirect()
            ->route('pages.manage.index', ['tab' => 'categories'])
            ->with('status', 'Категорію оновлено.');
    }

    public function destroyCategory(PageCategory $category): RedirectResponse
    {
        $category->delete();

        return redirect()
            ->route('pages.manage.index', ['tab' => 'categories'])
            ->with('status', 'Категорію видалено.');
    }

    public function destroyEmptyCategories(): RedirectResponse
    {
        $deletedCount = PageCategory::query()
            ->whereDoesntHave('pages')
            ->delete();

        if ($deletedCount === 0) {
            $message = 'Порожніх категорій не знайдено.';
        } elseif ($deletedCount === 1) {
            $message = 'Видалено 1 порожню категорію.';
        } else {
            $message = 'Видалено ' . $deletedCount . ' порожніх категорій.';
        }

        return redirect()
            ->route('pages.manage.index', ['tab' => 'categories'])
            ->with('status', $message);
    }

    protected function validatedData(Request $request, ?Page $page = null): array
    {
        $pageId = $page?->getKey();

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($pageId),
            ],
            'text' => ['nullable', 'string'],
            'page_category_id' => ['required', 'integer', Rule::exists('page_categories', 'id')],
        ] + $this->tagRules());
    }

    protected function validatedCategoryData(Request $request, ?PageCategory $category = null): array
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
            'language' => ['required', 'string', 'max:8'],
        ] + $this->tagRules());
    }

    public function createBlock(Page $page)
    {
        $defaults = [
            'locale' => app()->getLocale(),
            'type' => 'box',
            'column' => 'left',
            'sort_order' => $this->nextSortOrder($page),
        ];

        $block = new TextBlock($defaults);

        return view('page-manager::blocks.create', [
            'page' => $page,
            'block' => $block,
        ]);
    }

    public function storeBlock(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validatedBlockData($request, $page);

        $page->textBlocks()->create($data);

        return redirect()
            ->route('pages.manage.edit', $page)
            ->with('status', 'Блок додано.');
    }

    public function editBlock(Page $page, TextBlock $block)
    {
        $this->ensureBlockBelongsToPage($page, $block);

        return view('page-manager::blocks.edit', [
            'page' => $page,
            'block' => $block,
        ]);
    }

    public function updateBlock(Request $request, Page $page, TextBlock $block): RedirectResponse
    {
        $this->ensureBlockBelongsToPage($page, $block);

        $data = $this->validatedBlockData($request, $page, $block);

        $block->update($data);

        return redirect()
            ->route('pages.manage.edit', $page)
            ->with('status', 'Блок оновлено.');
    }

    public function destroyBlock(Page $page, TextBlock $block): RedirectResponse
    {
        $this->ensureBlockBelongsToPage($page, $block);

        $block->delete();

        return redirect()
            ->route('pages.manage.edit', $page)
            ->with('status', 'Блок видалено.');
    }

    public function categoryBlocks(PageCategory $category)
    {
        $blocks = $category->textBlocks()->get();

        return view('page-manager::categories.blocks.index', [
            'category' => $category,
            'blocks' => $blocks,
        ]);
    }

    public function createCategoryBlock(PageCategory $category)
    {
        $defaults = [
            'locale' => app()->getLocale(),
            'type' => 'box',
            'column' => 'left',
            'sort_order' => $this->nextCategorySortOrder($category),
        ];

        $block = new TextBlock($defaults);

        return view('page-manager::categories.blocks.create', [
            'category' => $category,
            'block' => $block,
        ]);
    }

    public function storeCategoryBlock(Request $request, PageCategory $category): RedirectResponse
    {
        $data = $this->validatedCategoryBlockData($request, $category);

        $category->textBlocks()->create($data);

        return redirect()
            ->route('pages.manage.categories.blocks.index', $category)
            ->with('status', 'Блок опису додано.');
    }

    public function editCategoryBlock(PageCategory $category, TextBlock $block)
    {
        $this->ensureBlockBelongsToCategory($category, $block);

        return view('page-manager::categories.blocks.edit', [
            'category' => $category,
            'block' => $block,
        ]);
    }

    public function updateCategoryBlock(Request $request, PageCategory $category, TextBlock $block): RedirectResponse
    {
        $this->ensureBlockBelongsToCategory($category, $block);

        $data = $this->validatedCategoryBlockData($request, $category, $block);

        $block->update($data);

        return redirect()
            ->route('pages.manage.categories.blocks.index', $category)
            ->with('status', 'Блок опису оновлено.');
    }

    public function destroyCategoryBlock(PageCategory $category, TextBlock $block): RedirectResponse
    {
        $this->ensureBlockBelongsToCategory($category, $block);

        $block->delete();

        return redirect()
            ->route('pages.manage.categories.blocks.index', $category)
            ->with('status', 'Блок опису видалено.');
    }

    protected function validatedBlockData(Request $request, Page $page, ?TextBlock $block = null): array
    {
        return $this->prepareBlockPayload($request, $block, fn () => $this->nextSortOrder($page));
    }

    protected function validatedCategoryBlockData(Request $request, PageCategory $category, ?TextBlock $block = null): array
    {
        return $this->prepareBlockPayload($request, $block, fn () => $this->nextCategorySortOrder($category));
    }

    protected function prepareBlockPayload(Request $request, ?TextBlock $block, callable $sortOrderResolver): array
    {
        $data = $request->validate([
            'locale' => ['nullable', 'string', 'max:8'],
            'type' => ['nullable', 'string', 'max:32'],
            'column' => ['nullable', 'string', Rule::in(['left', 'right', 'header', ''])],
            'heading' => ['nullable', 'string', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'body' => ['nullable', 'string'],
        ]);

        $locale = trim((string) ($data['locale'] ?? ''));
        $type = trim((string) ($data['type'] ?? ''));
        $column = $data['column'] ?? null;

        $normalised = [
            'locale' => $locale !== '' ? $locale : ($block?->locale ?? app()->getLocale() ?? 'uk'),
            'type' => $type !== '' ? $type : ($block?->type ?? 'box'),
            'column' => $column === '' ? null : ($column ?? ($type === 'subtitle' ? 'header' : ($block?->column ?? null))),
            'heading' => $this->normaliseNullableString($data['heading'] ?? ($block?->heading ?? null)),
            'css_class' => $this->normaliseNullableString($data['css_class'] ?? ($block?->css_class ?? null)),
            'body' => $this->normaliseNullableString($data['body'] ?? ($block?->body ?? null), preserveEmpty: true),
        ];

        if (array_key_exists('sort_order', $data) && $data['sort_order'] !== null) {
            $normalised['sort_order'] = (int) $data['sort_order'];
        } elseif ($block) {
            $normalised['sort_order'] = (int) $block->sort_order;
        } else {
            $normalised['sort_order'] = (int) call_user_func($sortOrderResolver);
        }

        if ($normalised['column'] === null && $normalised['type'] === 'subtitle') {
            $normalised['column'] = 'header';
        }

        return $normalised;
    }

    protected function normaliseNullableString($value, bool $preserveEmpty = false): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = is_string($value) ? trim($value) : (string) $value;

        if ($string === '') {
            return $preserveEmpty ? '' : null;
        }

        return $preserveEmpty ? (string) $value : $string;
    }

    protected function nextSortOrder(Page $page): int
    {
        $currentMax = (int) $page->textBlocks()->max('sort_order');

        return $currentMax + 10 ?: 10;
    }

    protected function nextCategorySortOrder(PageCategory $category): int
    {
        $currentMax = (int) $category->textBlocks()->max('sort_order');

        return $currentMax + 10 ?: 10;
    }

    protected function tagRules(): array
    {
        return [
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
        ];
    }

    protected function syncTags($model, array $tagIds): void
    {
        $uniqueIds = collect($tagIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $model->tags()->sync($uniqueIds);
    }

    protected function tagOptions()
    {
        $tags = Tag::query()
            ->orderByRaw('CASE WHEN category IS NULL OR category = "" THEN 1 ELSE 0 END')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Tag $tag) {
                return $tag->category ?: 'Без категорії';
            });

        return $tags->sortKeysUsing(function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            if ($a === 'Без категорії') {
                return 1;
            }

            if ($b === 'Без категорії') {
                return -1;
            }

            return strnatcasecmp($a, $b);
        });
    }

    protected function categories()
    {
        return PageCategory::query()->orderBy('title')->get();
    }

    protected function ensureBlockBelongsToPage(Page $page, TextBlock $block): void
    {
        if ((int) $block->page_id !== (int) $page->getKey()) {
            abort(404);
        }
    }

    protected function ensureBlockBelongsToCategory(PageCategory $category, TextBlock $block): void
    {
        if ((int) $block->page_category_id !== (int) $category->getKey()) {
            abort(404);
        }
    }

    public function exportToJson(): RedirectResponse
    {
        $categories = PageCategory::query()
            ->with(['pages' => function ($query) {
                $query->orderBy('title');
            }])
            ->orderBy('title')
            ->get();

        $exportData = $categories->map(function (PageCategory $category) {
            return [
                'category_id' => $category->id,
                'category_title' => $category->title,
                'category_slug' => $category->slug,
                'category_language' => $category->language,
                'pages' => $category->pages->map(function (Page $page) {
                    return [
                        'page_id' => $page->id,
                        'page_title' => $page->title,
                        'page_slug' => $page->slug,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $jsonData = [
            'exported_at' => now()->toIso8601String(),
            'total_categories' => count($exportData),
            'total_pages' => $categories->sum(fn ($category) => $category->pages->count()),
            'categories' => $exportData,
        ];

        $filePath = config_path('pages/exported_pages.json');
        $directory = dirname($filePath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return redirect()->route('pages.manage.index')->with('error', 'Не вдалося створити директорію для експорту.');
        }

        $result = file_put_contents($filePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($result === false) {
            return redirect()->route('pages.manage.index')->with('error', 'Не вдалося записати файл експорту.');
        }

        $relativePath = 'config/pages/exported_pages.json';

        return redirect()->route('pages.manage.index')->with('status', "Сторінки успішно експортовано до файлу {$relativePath}");
    }

    public function viewExportedJson()
    {
        $filePath = config_path('pages/exported_pages.json');

        if (!file_exists($filePath)) {
            return redirect()->route('pages.manage.index')->with('error', 'Файл експорту не знайдено. Спочатку виконайте експорт.');
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            return redirect()->route('pages.manage.index')->with('error', 'Не вдалося прочитати файл експорту.');
        }

        $jsonData = json_decode($jsonContent, true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->route('pages.manage.index')->with('error', 'Файл експорту містить некоректний JSON.');
        }

        $relativePath = 'config/pages/exported_pages.json';

        return view('page-manager::view-export', [
            'jsonContent' => $jsonContent,
            'jsonData' => $jsonData,
            'filePath' => $relativePath,
            'fileSize' => filesize($filePath),
            'lastModified' => filemtime($filePath),
        ]);
    }

    public function downloadExportedJson()
    {
        $filePath = config_path('pages/exported_pages.json');

        if (!file_exists($filePath)) {
            return redirect()->route('pages.manage.index')->with('error', 'Файл експорту не знайдено. Спочатку виконайте експорт.');
        }

        return response()->download($filePath, 'exported_pages.json');
    }
}
