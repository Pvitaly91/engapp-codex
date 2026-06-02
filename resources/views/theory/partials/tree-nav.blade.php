@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    $level = $level ?? 0;
@endphp

@foreach($categories as $category)
    @php
        $isActive = $selectedCategory && $selectedCategory->is($category);
        $hasSelectedDescendant = $category->hasChildren() && $category->hasDescendant($selectedCategory);
        $orderedItems = $category->ordered_tree_items ?? collect();
        $isExpanded = $isActive || $hasSelectedDescendant;
        $isBranchActive = $isActive || $hasSelectedDescendant;
        $itemCount = $orderedItems->count() ?: ($category->pages_count ?? 0);
    @endphp

    <div x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }" class="{{ $level === 0 ? 'space-y-2.5' : 'space-y-2' }}">
        <div class="min-w-0 flex-1">
            <div class="relative">
                <a
                    href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                    class="group block rounded-[22px] border px-3.5 py-3.5 pr-12 transition hover:-translate-y-0.5 surface-card-strong"
                    style="{{ $isActive
                        ? 'border-color: var(--accent); background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) 88%, white) 0%, white 100%); box-shadow: inset 4px 0 0 var(--accent); color: var(--text);'
                        : ($hasSelectedDescendant
                            ? 'border-color: color-mix(in srgb, var(--accent) 40%, var(--line)); background: color-mix(in srgb, var(--accent-soft) 48%, white); color: var(--text);'
                            : 'border-color: var(--line); color: var(--text);') }}"
                >
                    <div class="flex items-start gap-2.5">
                        <span
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[16px] border"
                            style="{{ $isBranchActive
                                ? 'border-color: transparent; background: linear-gradient(135deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 76%, white) 100%);'
                                : 'border-color: color-mix(in srgb, var(--accent) 16%, var(--line)); background: color-mix(in srgb, var(--accent-soft) 38%, white);' }}"
                        >
                            @include('theory.partials.category-icon', [
                                'category' => $category,
                                'active' => $isBranchActive,
                            ])
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-3">
                                <span class="block text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: {{ $isBranchActive ? 'var(--accent)' : 'var(--muted)' }};">
                                    {{ __('public.theory.section_badge') }}
                                </span>
                                <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full px-2 py-1 text-[10px] font-extrabold" style="background: {{ $isBranchActive ? 'rgba(47, 103, 177, 0.12)' : 'rgba(91, 108, 128, 0.10)' }}; color: {{ $isBranchActive ? 'var(--accent)' : 'var(--muted)' }};">
                                    {{ $itemCount }}
                                </span>
                            </span>
                            <span class="mt-1 block text-sm font-extrabold leading-6">{{ $category->title }}</span>
                            <span class="mt-1 block text-xs font-semibold leading-5" style="color: var(--muted);">
                                {{ $orderedItems->isNotEmpty() ? __('public.common.go_to') : __('public.common.section_pages') }}
                            </span>
                        </span>
                    </div>
                </a>

                @if($orderedItems->isNotEmpty())
                    <button
                        type="button"
                        @click.stop.prevent="expanded = !expanded"
                        class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-2xl border transition hover:border-ocean surface-card"
                        style="border-color: {{ $isBranchActive ? 'color-mix(in srgb, var(--accent) 35%, var(--line))' : 'var(--line)' }}; color: {{ $isBranchActive ? 'var(--accent)' : 'var(--muted)' }};"
                        :aria-expanded="expanded.toString()"
                    >
                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif
            </div>

            @if($orderedItems->isNotEmpty())
                <div x-show="expanded" x-transition x-cloak class="ml-5 mt-2.5 space-y-2.5 border-l pl-3" style="border-color: color-mix(in srgb, var(--line) 88%, transparent);">
                    @foreach($orderedItems as $item)
                        @if($item['type'] === 'category')
                            @include('theory.partials.tree-nav', [
                                'categories' => collect([$item['model']]),
                                'selectedCategory' => $selectedCategory,
                                'currentPage' => $currentPage,
                                'routePrefix' => $routePrefix,
                                'level' => $level + 1,
                            ])
                        @else
                            @php($pageItem = $item['model'])
                            @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                            <a
                                href="{{ localized_route($routePrefix . '.show', [$category->slug, $pageItem->slug]) }}"
                                class="group relative flex items-start gap-2.5 rounded-[16px] px-2.5 py-2.5 text-sm transition hover:bg-slate-100/80 dark:hover:bg-slate-800/40"
                                style="{{ $isCurrentPage
                                    ? 'background: color-mix(in srgb, var(--accent-soft) 78%, white); color: var(--text);'
                                    : 'color: var(--muted);' }}"
                                @if($isCurrentPage) aria-current="page" @endif
                            >
                                <span class="mt-1.5 inline-flex h-3 w-3 shrink-0 rounded-full border-2" style="border-color: {{ $isCurrentPage ? 'var(--accent)' : 'color-mix(in srgb, var(--line) 88%, var(--muted))' }}; background: {{ $isCurrentPage ? 'var(--accent)' : 'transparent' }};"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold leading-5">{{ $pageItem->title }}</span>
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endforeach
