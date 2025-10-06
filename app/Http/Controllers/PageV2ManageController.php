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
        return view('engram.pages.v2.manage.create', [
            'page' => new Page(),
            'blockPayloads' => old('blocks', []),
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

        $payloads = $blocks->map(function (TextBlock $block) {
            return [
                'id' => $block->id,
                'locale' => $block->locale,
                'type' => $block->type,
                'column' => $block->column,
                'heading' => $block->heading,
                'css_class' => $block->css_class,
                'sort_order' => $block->sort_order,
                'body' => $block->body,
            ];
        })->values()->toArray();

        return view('engram.pages.v2.manage.edit', [
            'page' => $page,
            'blockPayloads' => old('blocks', $payloads),
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
            'blocks.*.column' => ['nullable', 'string', Rule::in(['left', 'right', ''])],
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
}
