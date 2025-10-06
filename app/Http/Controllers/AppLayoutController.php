<?php

namespace App\Http\Controllers;

use App\Models\PageBlock;
use App\Support\EngramPages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppLayoutController extends Controller
{
    public function index(): View
    {
        $pages = EngramPages::all()->map(function ($page) {
            $page->blocks_count = PageBlock::query()
                ->where('page_slug', $page->slug)
                ->count();

            $page->locales = PageBlock::query()
                ->where('page_slug', $page->slug)
                ->select('locale')
                ->distinct()
                ->pluck('locale');

            return $page;
        });

        return view('app-layout.index', compact('pages'));
    }

    public function edit(Request $request, string $slug): View
    {
        $page = EngramPages::find($slug);
        abort_unless($page, 404);

        $availableLocales = PageBlock::query()
            ->where('page_slug', $slug)
            ->select('locale')
            ->distinct()
            ->pluck('locale')
            ->filter()
            ->values();

        $locale = $request->input('locale', $availableLocales->first() ?? app()->getLocale());

        $blocks = PageBlock::forPage($slug, $locale)->get();

        return view('app-layout.edit', [
            'page' => $page,
            'blocks' => $blocks,
            'locale' => $locale,
            'availableLocales' => $availableLocales,
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $page = EngramPages::find($slug);
        abort_unless($page, 404);

        $validated = $this->validatedData($request);

        PageBlock::create($validated + ['page_slug' => $slug]);

        return redirect()
            ->route('app-layout.pages.edit', ['slug' => $slug, 'locale' => $validated['locale']])
            ->with('status', 'Блок успішно додано.');
    }

    public function update(Request $request, string $slug, PageBlock $block): RedirectResponse
    {
        abort_unless($block->page_slug === $slug, 404);

        $validated = $this->validatedData($request);

        $block->update($validated);

        return redirect()
            ->route('app-layout.pages.edit', ['slug' => $slug, 'locale' => $validated['locale']])
            ->with('status', 'Блок успішно оновлено.');
    }

    public function destroy(string $slug, PageBlock $block): RedirectResponse
    {
        abort_unless($block->page_slug === $slug, 404);

        $locale = $block->locale;
        $block->delete();

        return redirect()
            ->route('app-layout.pages.edit', ['slug' => $slug, 'locale' => $locale])
            ->with('status', 'Блок видалено.');
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'max:5'],
            'area' => ['required', 'string'],
            'type' => ['required', 'string'],
            'label' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'content' => ['nullable', 'string'],
        ]);

        $content = $data['content'] ?? null;
        if ($content !== null && $content !== '') {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'content' => __('Зміст блока має бути валідним JSON.'),
                ]);
            }
            $data['content'] = $decoded;
        } else {
            $data['content'] = null;
        }

        $data['position'] = $data['position'] ?? 0;

        return $data;
    }
}
