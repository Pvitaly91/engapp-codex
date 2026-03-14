@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
@endphp

@foreach($categories as $category)
    @php
        $isActive = $selectedCategory && $selectedCategory->is($category);
        $hasSelectedDescendant = $category->hasChildren() && $category->hasDescendant($selectedCategory);
        $orderedItems = $category->ordered_tree_items ?? collect();
        $isExpanded = $isActive || $hasSelectedDescendant;
    @endphp

    <div x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }" class="space-y-1">
        <div class="flex items-start gap-2">
            @if($orderedItems->isNotEmpty())
                <button
                    type="button"
                    @click="expanded = !expanded"
                    class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-2xl border transition hover:border-ocean surface-card"
                    style="border-color: var(--line); color: var(--muted);"
                >
                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-2xl border text-xs font-extrabold surface-card" style="border-color: var(--line); color: var(--muted);">
                    {{ isset($category->pages_count) ? $category->pages_count : 0 }}
                </span>
            @endif

            <a
                href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                class="flex-1 rounded-[22px] border px-4 py-3 transition"
                style="{{ $isActive ? 'border-color: var(--accent); background: var(--accent-soft); color: var(--text);' : 'border-color: var(--line); color: var(--text);' }}"
            >
                <span class="block text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: {{ $isActive ? 'var(--accent)' : 'var(--muted)' }};">
                    {{ __('public.theory.section_badge') }}
                </span>
                <span class="mt-1 block text-sm font-bold leading-6">{{ $category->title }}</span>
            </a>
        </div>

        @if($orderedItems->isNotEmpty())
            <div x-show="expanded" x-transition x-cloak class="ml-4 space-y-2 border-l pl-4" style="border-color: var(--line);">
                @foreach($orderedItems as $item)
                    @if($item['type'] === 'category')
                        @include('theory.partials.tree-nav', [
                            'categories' => collect([$item['model']]),
                            'selectedCategory' => $selectedCategory,
                            'currentPage' => $currentPage,
                            'routePrefix' => $routePrefix,
                        ])
                    @else
                        @php($pageItem = $item['model'])
                        @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                        <a
                            href="{{ localized_route($routePrefix . '.show', [$category->slug, $pageItem->slug]) }}"
                            class="flex items-start gap-3 rounded-[20px] border px-3 py-3 text-sm transition"
                            style="{{ $isCurrentPage ? 'border-color: var(--accent); background: var(--accent-soft); color: var(--text);' : 'border-color: var(--line); color: var(--muted);' }}"
                            @if($isCurrentPage) aria-current="page" @endif
                        >
                            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-ocean text-[10px] font-extrabold text-white">
                                P
                            </span>
                            <span class="min-w-0 break-words leading-5">{{ $pageItem->title }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endforeach
