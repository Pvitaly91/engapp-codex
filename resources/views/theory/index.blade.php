@extends('layouts.catalog-public')

@section('title', $sectionTitle ?? __('public.theory.title'))
@section('body_class', 'scroll-optimized')

@section('content')
@php
    $routePrefix = $routePrefix ?? 'theory';
    $totalPages = $categories->sum(fn ($category) => $category->recursive_pages_count ?? $category->pages_count ?? 0);
    $gradients = ['bg-ocean', 'bg-amber', 'bg-emerald-500', 'bg-slate-800 dark:bg-slate-200', 'bg-rose-500', 'bg-sky-500'];
@endphp

<div class="nd-page">
    <section class="nd-section-tight relative border-b" style="border-color: var(--line);">
        <div class="absolute left-[12%] top-12 hidden h-24 w-24 rounded-full border-[16px] border-ocean/40 lg:block"></div>
        <div class="absolute right-[10%] top-10 hidden h-20 w-20 rounded-full bg-amber/80 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-56 w-16 rounded-tl-[2.5rem] bg-ocean lg:block"></div>

        <div class="relative grid gap-6 lg:grid-cols-[1.02fr_0.98fr] lg:items-center">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.28em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ __('public.theory.section_badge') }}
                </span>
                <h1 class="mt-6 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">
                    {{ __('public.theory.title') }}
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 sm:text-xl" style="color: var(--muted);">
                    {{ __('public.theory.description') }}
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    @if($categories->first())
                        <a href="{{ localized_route($routePrefix . '.category', $categories->first()->slug) }}" class="rounded-2xl bg-ocean px-6 py-4 text-center text-base font-extrabold text-white shadow-card transition hover:bg-[#245592]">
                            {{ __('public.theory.start_learning') }}
                        </a>
                    @endif
                    <a href="#theory-categories" class="rounded-2xl bg-amber px-6 py-4 text-center text-base font-extrabold text-white shadow-card transition hover:bg-[#df8a24]">
                        {{ __('public.theory.all_categories') }}
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.categories_count') }}</p>
                    <p class="mt-3 font-display text-[2.25rem] font-extrabold leading-none">{{ $categories->count() }}</p>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ __('public.theory.theory_categories') }}</p>
                </article>
                <article class="rounded-[28px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.lessons_count') }}</p>
                    <p class="mt-3 font-display text-[2.25rem] font-extrabold leading-none">{{ $totalPages }}</p>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ __('public.theory.lesson_pages') }}</p>
                </article>
                <article class="rounded-[28px] border p-6 shadow-card surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.difficulty_levels') }}</p>
                    <p class="mt-3 font-display text-2xl font-extrabold leading-none">A1-B2</p>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ __('public.theory.topics_hint') }}</p>
                </article>
                <article class="rounded-[28px] border p-6 shadow-card surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.in_ukrainian') }}</p>
                    <p class="mt-3 font-display text-2xl font-extrabold leading-none">UA</p>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ __('public.theory.how_to_learn_desc') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section id="theory-categories" class="theory-lazy-section nd-section border-b" style="border-color: var(--line);">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.topics_to_learn') }}</p>
                <h2 class="mt-2 font-display text-2xl font-extrabold leading-none">{{ __('public.theory.topics_hint') }}</h2>
            </div>
            <p class="max-w-2xl text-sm leading-6 sm:text-right" style="color: var(--muted);">
                {{ __('public.theory.how_to_learn_desc') }}
            </p>
        </div>

        <div class="mt-8 rounded-[28px] border p-4 shadow-card surface-card-strong sm:p-5" style="border-color: var(--line);" data-theory-category-search>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <label for="theory-category-search" class="min-w-0 flex-1">
                    <span class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.category_search_label') }}</span>
                    <span class="mt-3 flex items-center gap-3 rounded-[22px] border px-4 py-3 surface-card" style="border-color: var(--line);">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-ocean/10 text-sm font-extrabold" style="color: var(--accent);">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m21 21-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <input
                            id="theory-category-search"
                            type="search"
                            autocomplete="off"
                            data-theory-category-search-input
                            class="min-w-0 flex-1 bg-transparent text-base font-bold outline-none placeholder:font-semibold"
                            style="color: var(--text);"
                            placeholder="{{ __('public.theory.category_search_placeholder') }}"
                        >
                    </span>
                </label>
                <div class="flex shrink-0 items-center justify-between gap-3 lg:justify-end">
                    <span class="rounded-full px-4 py-2 text-sm font-extrabold soft-accent" data-theory-category-search-count>
                        {{ $categories->count() }} / {{ $categories->count() }}
                    </span>
                    <button type="button" class="hidden rounded-2xl border px-4 py-3 text-sm font-extrabold transition hover:bg-slate-50" style="border-color: var(--line); color: var(--muted);" data-theory-category-search-clear>
                        {{ __('public.theory.category_search_clear') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-theory-category-grid>
            @forelse($categories as $index => $category)
                @php
                    $accent = $gradients[$index % count($gradients)];
                    $hasChildren = $category->relationLoaded('children') && $category->children->isNotEmpty();
                    $hasPages = $category->relationLoaded('pages') && $category->pages->isNotEmpty();
                    $searchText = collect([$category->title])
                        ->merge($hasChildren ? $category->children->pluck('title') : collect())
                        ->implode(' ');
                @endphp
                <article class="flex h-full flex-col overflow-hidden rounded-[26px] border shadow-card surface-card-strong" style="border-color: var(--line);" data-theory-category-card data-theory-search-text="{{ $searchText }}">
                    <a href="{{ localized_route($routePrefix . '.category', $category->slug) }}" class="block shrink-0 border-b p-6" style="border-color: var(--line);">
                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-[20px] {{ $accent }} text-sm font-extrabold text-white dark:text-slate-950">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">
                                {{ $category->recursive_pages_count ?? $category->pages_count ?? 0 }} {{ __('public.theory.lessons_count') }}
                            </span>
                        </div>
                        <h3 class="mt-5 font-display text-xl font-extrabold leading-tight"><span data-theory-highlight>{{ $category->title }}</span></h3>
                    </a>

                    <div class="flex flex-1 flex-col gap-4 p-5">
                        @if($hasChildren)
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.categories') }}</p>
                                <div class="mt-3 space-y-2">
                                    @foreach($category->children as $child)
                                        <a href="{{ localized_route($routePrefix . '.category', $child->slug) }}" class="flex items-center justify-between rounded-[18px] border px-3 py-3 text-sm transition hover:-translate-y-0.5 surface-card" style="border-color: var(--line); color: var(--text);">
                                            <span class="min-w-0 break-words" data-theory-highlight>{{ $child->title }}</span>
                                            <span class="text-xs font-bold" style="color: var(--muted);">{{ $child->recursive_pages_count ?? $child->pages_count ?? 0 }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($hasPages)
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.section_pages') }}</p>
                                <div class="mt-3 space-y-2">
                                    @foreach($category->pages->take(4) as $page)
                                        <a href="{{ localized_route($routePrefix . '.show', [$category->slug, $page->slug]) }}" class="flex items-start gap-3 rounded-[18px] border px-3 py-3 text-sm transition hover:-translate-y-0.5 surface-card" style="border-color: var(--line); color: var(--muted);">
                                            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-ocean text-[10px] font-extrabold text-white">
                                                P
                                            </span>
                                            <span class="min-w-0 break-words leading-5">{{ $page->title }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <a href="{{ localized_route($routePrefix . '.category', $category->slug) }}" class="mt-auto self-start inline-flex items-center gap-2 rounded-[18px] bg-ocean px-4 py-3 text-sm font-extrabold uppercase tracking-[0.18em] text-white transition hover:bg-[#245592]">
                            {{ __('public.common.go_to') }}
                            <span>+</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-[28px] border border-dashed p-10 text-center shadow-card surface-card-strong" style="border-color: var(--line);">
                    <h3 class="font-display text-xl font-extrabold">{{ __('public.theory.no_categories_title') }}</h3>
                    <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ __('public.theory.no_categories_hint') }}</p>
                </div>
            @endforelse
        </div>

        @if($categories->isNotEmpty())
            <div class="mt-8 hidden rounded-[28px] border border-dashed p-10 text-center shadow-card surface-card-strong" style="border-color: var(--line);" data-theory-category-search-empty>
                <h3 class="font-display text-xl font-extrabold">{{ __('public.theory.category_search_empty_title') }}</h3>
                <p class="mt-3 text-sm leading-6" style="color: var(--muted);">{{ __('public.theory.category_search_empty_hint') }}</p>
            </div>
        @endif
    </section>

    <section class="theory-lazy-section nd-section">
        <div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
            <article class="rounded-[30px] border p-6 shadow-card surface-card-strong" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.recommended_path') }}</p>
                <h2 class="mt-3 font-display text-2xl font-extrabold leading-tight">{{ __('public.theory.how_to_learn') }}</h2>
                <p class="mt-4 text-sm leading-7 sm:text-base" style="color: var(--muted);">{{ __('public.theory.how_to_learn_desc') }}</p>
                <div class="mt-8 space-y-4">
                    @foreach([
                        [__('public.theory.step1'), __('public.theory.step1_desc')],
                        [__('public.theory.step2'), __('public.theory.step2_desc')],
                        [__('public.theory.step3'), __('public.theory.step3_desc')],
                        [__('public.theory.step4'), __('public.theory.step4_desc')],
                    ] as $step)
                        <div class="flex gap-4 rounded-[22px] border p-4 surface-card" style="border-color: var(--line);">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[18px] bg-ocean text-sm font-extrabold text-white">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <h3 class="font-display text-lg font-extrabold leading-tight">{{ $step[0] }}</h3>
                                <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ $step[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[30px] border p-6 shadow-card surface-card" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.common.quick_links') }}</p>
                <h2 class="mt-3 font-display text-2xl font-extrabold leading-tight">{{ __('public.home.pick_path') }}</h2>
                <div class="mt-6 grid gap-4">
                    <a href="{{ localized_route('theory.index') }}" class="rounded-[22px] border p-5 surface-card-strong" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.theory.section_badge') }}</p>
                        <h3 class="mt-2 font-display text-xl font-extrabold">{{ __('public.theory.all_categories') }}</h3>
                    </a>
                    @devMode
                        <a href="{{ localized_route('catalog.tests-cards') }}" class="rounded-[22px] border p-5 surface-card-strong" style="border-color: var(--line);">
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.nav.catalog') }}</p>
                            <h3 class="mt-2 font-display text-xl font-extrabold">{{ __('public.home.to_catalog') }}</h3>
                        </a>
                    @enddevMode
                    <a href="{{ localized_route('home') }}" class="rounded-[22px] border p-5 surface-card-strong" style="border-color: var(--line);">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('public.home.new_design') }}</p>
                        <h3 class="mt-2 font-display text-xl font-extrabold">{{ __('public.home.title') }}</h3>
                    </a>
                </div>
            </article>
        </div>
    </section>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-theory-category-search]');
            const grid = document.querySelector('[data-theory-category-grid]');

            if (!root || !grid) {
                return;
            }

            const input = root.querySelector('[data-theory-category-search-input]');
            const clearButton = root.querySelector('[data-theory-category-search-clear]');
            const count = root.querySelector('[data-theory-category-search-count]');
            const cards = Array.from(grid.querySelectorAll('[data-theory-category-card]'));
            const empty = document.querySelector('[data-theory-category-search-empty]');
            const total = cards.length;

            const normalize = (value) => value.toLocaleLowerCase().trim();

            const clearHighlight = (element) => {
                element.textContent = element.dataset.theoryOriginalText || element.textContent;
            };

            const highlightText = (element, query) => {
                const original = element.dataset.theoryOriginalText || element.textContent;
                element.dataset.theoryOriginalText = original;
                element.textContent = '';

                if (!query) {
                    element.textContent = original;
                    return;
                }

                const lowerOriginal = original.toLocaleLowerCase();
                const lowerQuery = query.toLocaleLowerCase();
                let cursor = 0;
                let matchIndex = lowerOriginal.indexOf(lowerQuery);

                if (matchIndex === -1) {
                    element.textContent = original;
                    return;
                }

                while (matchIndex !== -1) {
                    if (matchIndex > cursor) {
                        element.append(document.createTextNode(original.slice(cursor, matchIndex)));
                    }

                    const mark = document.createElement('mark');
                    mark.className = 'rounded-md bg-amber/30 px-1 py-0.5 text-inherit';
                    mark.textContent = original.slice(matchIndex, matchIndex + query.length);
                    element.append(mark);

                    cursor = matchIndex + query.length;
                    matchIndex = lowerOriginal.indexOf(lowerQuery, cursor);
                }

                if (cursor < original.length) {
                    element.append(document.createTextNode(original.slice(cursor)));
                }
            };

            const applySearch = () => {
                const query = normalize(input.value);
                let visible = 0;

                cards.forEach((card) => {
                    const haystack = normalize(card.dataset.theorySearchText || '');
                    const matched = !query || haystack.includes(query);

                    card.classList.toggle('hidden', !matched);

                    if (matched) {
                        visible += 1;
                    }

                    card.querySelectorAll('[data-theory-highlight]').forEach((element) => {
                        if (matched && query) {
                            highlightText(element, query);
                        } else {
                            clearHighlight(element);
                        }
                    });
                });

                if (count) {
                    count.textContent = `${visible} / ${total}`;
                }

                if (clearButton) {
                    clearButton.classList.toggle('hidden', !query);
                }

                if (empty) {
                    empty.classList.toggle('hidden', visible !== 0);
                }
            };

            input.addEventListener('input', applySearch);

            clearButton?.addEventListener('click', () => {
                input.value = '';
                input.focus();
                applySearch();
            });

            applySearch();
        });
    </script>
@endsection
