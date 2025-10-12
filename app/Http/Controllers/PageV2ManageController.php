<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageV2ManageController extends Controller
{
    public function index()
    {
        $pages = Page::query()->orderBy('title')->get();

        return view('engram.pages.v2.manage.index', [
            'pages' => $pages,
        ]);
    }
 
    public function create()
    {
        $page = new Page();

        return view('engram.pages.v2.manage.create', [
            'page' => $page,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $page = Page::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'text' => $validated['text'] ?? '',
        ]);

        return redirect()
            ->route('pages-v2.manage.edit', $page)
            ->with('status', 'Сторінку створено. Додайте або оновіть її блоки нижче.');
    }

    public function edit(Page $page)
    {
        $blocks = $page->textBlocks()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('engram.pages.v2.manage.edit', [
            'page' => $page,
            'blocks' => $blocks,
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validatedData($request, $page);

        $page->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'text' => $validated['text'] ?? '',
        ])->save();

        return back()->with('status', 'Зміни збережено.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('pages-v2.manage.index')
            ->with('status', 'Сторінку видалено.');
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
        ]);
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

        return view('engram.pages.v2.manage.blocks.create', [
            'page' => $page,
            'block' => $block,
        ]);
    }

    public function storeBlock(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validatedBlockData($request, $page);

        $page->textBlocks()->create($data);

        return redirect()
            ->route('pages-v2.manage.edit', $page)
            ->with('status', 'Блок додано.');
    }

    public function editBlock(Page $page, TextBlock $block)
    {
        if ($block->page_id !== $page->id) {
            abort(404);
        }

        return view('engram.pages.v2.manage.blocks.edit', [
            'page' => $page,
            'block' => $block,
        ]);
    }

    public function updateBlock(Request $request, Page $page, TextBlock $block): RedirectResponse
    {
        if ($block->page_id !== $page->id) {
            abort(404);
        }

        $data = $this->validatedBlockData($request, $page, $block);

        $block->update($data);

        return redirect()
            ->route('pages-v2.manage.edit', $page)
            ->with('status', 'Блок оновлено.');
    }

    public function destroyBlock(Page $page, TextBlock $block): RedirectResponse
    {
        if ($block->page_id !== $page->id) {
            abort(404);
        }

        $block->delete();

        return redirect()
            ->route('pages-v2.manage.edit', $page)
            ->with('status', 'Блок видалено.');
    }

    protected function validatedBlockData(Request $request, Page $page, ?TextBlock $block = null): array
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
            $normalised['sort_order'] = $this->nextSortOrder($page);
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
}
