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

        $blockPayloads = $this->prepareBlockPayloads(old('blocks', []));

        return view('engram.pages.v2.manage.create', [
            'page' => $page,
            'blockPayloads' => $blockPayloads,
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

        $this->syncBlocks($page, $validated['blocks'] ?? []);

        return redirect()
            ->route('pages-v2.manage.edit', $page)
            ->with('status', 'Сторінку створено.');
    }

    public function edit(Page $page)
    {
        $blocks = $page->textBlocks()->orderBy('sort_order')->get();
        $page->setRelation('textBlocks', $blocks);

        $initialBlocks = old('blocks');

        if (! is_array($initialBlocks)) {
            $initialBlocks = $blocks->map(function (TextBlock $block) {
                return $block->toArray();
            })->all();
        }

        $payloads = $this->prepareBlockPayloads($initialBlocks);

        return view('engram.pages.v2.manage.edit', [
            'page' => $page,
            'blockPayloads' => $payloads,
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

        $this->syncBlocks($page, $validated['blocks'] ?? []);

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
            'blocks' => ['sometimes', 'array'],
            'blocks.*.id' => ['nullable', 'integer', 'exists:text_blocks,id'],
            'blocks.*.locale' => ['nullable', 'string', 'max:8'],
            'blocks.*.type' => ['nullable', 'string', 'max:32'],
            'blocks.*.column' => ['nullable', 'string', Rule::in(['left', 'right', 'header', ''])],
            'blocks.*.heading' => ['nullable', 'string', 'max:255'],
            'blocks.*.css_class' => ['nullable', 'string', 'max:255'],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'blocks.*.body' => ['nullable', 'string'],
        ]);
    }

    protected function syncBlocks(Page $page, array $blocks): void
    {
        $persistedIds = [];

        foreach ($blocks as $block) {
            $attributes = [
                'locale' => $block['locale'] ?? 'uk',
                'type' => ($block['type'] ?? '') ?: 'box',
                'column' => ($block['column'] ?? '') ?: null,
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $block['sort_order'] ?? 0,
                'body' => $block['body'] ?? null,
            ];

            $shouldSkip = empty($block['id'])
                && empty(trim((string) $attributes['heading']))
                && empty(trim((string) $attributes['body']));

            if ($shouldSkip) {
                continue;
            }

            if (! empty($block['id'])) {
                $model = $page->textBlocks()->whereKey($block['id'])->first();
                if ($model) {
                    $model->update($attributes);
                    $persistedIds[] = $model->id;
                }
                continue;
            }

            $model = $page->textBlocks()->create($attributes);
            $persistedIds[] = $model->id;
        }

        if (! empty($persistedIds)) {
            $page->textBlocks()->whereNotIn('id', $persistedIds)->delete();
        } else {
            $page->textBlocks()->delete();
        }
    }

    protected function prepareBlockPayloads($blocks): array
    {
        return collect($blocks ?? [])->values()->map(function ($block, $index) {
            $type = data_get($block, 'type', 'box') ?: 'box';

            $column = data_get($block, 'column');
            if ($column === null) {
                $column = $type === 'subtitle' ? 'header' : 'left';
            }

            return [
                'id' => data_get($block, 'id'),
                'locale' => data_get($block, 'locale', 'uk'),
                'type' => $type,
                'column' => $column,
                'heading' => data_get($block, 'heading', ''),
                'css_class' => data_get($block, 'css_class') ?? '',
                'sort_order' => (int) data_get($block, 'sort_order', ($index + 1) * 10),
                'body' => (string) data_get($block, 'body', ''),
            ];
        })->all();
    }
}
