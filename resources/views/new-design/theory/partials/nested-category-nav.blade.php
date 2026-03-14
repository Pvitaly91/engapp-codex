{{--
    New-Design – Nested category navigation (desktop sidebar)
    @param Collection $categories
    @param ?PageCategory $selectedCategory
    @param string $routePrefix  e.g. 'new-design.theory'
    @param int $level  nesting depth (default 0)
--}}
@php
    $categories      = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $currentPage     = $currentPage ?? null;
    $routePrefix     = $routePrefix ?? 'new-design.theory';
    $level           = $level ?? 0;
@endphp

@foreach($categories as $category)
    @php
        $isActive              = $selectedCategory && $selectedCategory->is($category);
        $hasChildren           = $category->hasChildren();
        $hasSelectedDescendant = $hasChildren && $category->hasDescendant($selectedCategory);
        $orderedItems          = $category->ordered_tree_items ?? collect();
        $isExpandable          = $orderedItems->isNotEmpty();
        $isExpanded            = $isActive || $hasSelectedDescendant;
    @endphp

    <div class="category-item" x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }">
        <div class="flex items-center gap-1">
            @if($isExpandable)
                <button
                    @click="expanded = !expanded"
                    class="flex h-6 w-6 items-center justify-center rounded text-[var(--nd-muted)] hover:text-[var(--nd-ocean)] hover:bg-ocean/8 transition-colors"
                    type="button"
                >
                    <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-90': expanded }"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <span class="w-6"></span>
            @endif

            <a
                href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                class="flex-1 min-w-0 flex items-start justify-between gap-2 rounded-xl py-1.5 text-sm transition-all
                    {{ $isActive
                        ? 'bg-ocean/10 px-2 text-ocean font-semibold'
                        : 'text-[var(--nd-muted)] hover:text-[var(--nd-ocean)] hover:bg-ocean/6' }}"
            >
                <span class="min-w-0 whitespace-normal break-words">{{ $category->title }}</span>
                @if(isset($category->pages_count) && $category->pages_count > 0)
                    <span class="text-xs opacity-60 shrink-0">{{ $category->pages_count }}</span>
                @endif
            </a>
        </div>

        @if($orderedItems->isNotEmpty())
            <div x-show="expanded" x-collapse class="ml-4 mt-1 space-y-1 border-l border-line pl-2">
                @foreach($orderedItems as $item)
                    @if($item['type'] === 'category')
                        @include('new-design.theory.partials.nested-category-nav', [
                            'categories'       => collect([$item['model']]),
                            'selectedCategory' => $selectedCategory,
                            'currentPage'      => $currentPage,
                            'routePrefix'      => $routePrefix,
                            'level'            => $level + 1,
                        ])
                    @else
                        @php($pageItem = $item['model'])
                        @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                        <a
                            href="{{ localized_route($routePrefix . '.show', [$category->slug, $pageItem->slug]) }}"
                            class="flex items-start gap-2 rounded-xl px-2 py-1.5 text-xs transition-all
                                {{ $isCurrentPage
                                    ? 'bg-ocean/10 text-ocean font-semibold'
                                    : 'text-[var(--nd-muted)] hover:text-[var(--nd-ocean)] hover:bg-ocean/6' }}"
                            @if($isCurrentPage) aria-current="page" @endif
                        >
                            <svg class="h-3 w-3 flex-shrink-0 text-[var(--nd-muted)] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="min-w-0 whitespace-normal break-words">{{ $pageItem->title }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endforeach
