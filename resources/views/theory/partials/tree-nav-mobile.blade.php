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
        $indent = $level * 16;
    @endphp

    <div x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }" class="space-y-2">
        <div class="flex items-center gap-2" style="padding-left: {{ $indent }}px;">
            @if($orderedItems->isNotEmpty())
                <button
                    type="button"
                    @click="expanded = !expanded"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border surface-card"
                    style="border-color: var(--line); color: var(--muted);"
                >
                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-2xl border text-[10px] font-extrabold surface-card" style="border-color: var(--line); color: var(--muted);">
                    {{ isset($category->pages_count) ? $category->pages_count : 0 }}
                </span>
            @endif

            <a
                href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                class="flex-1 rounded-[18px] border px-3 py-2.5 text-sm font-semibold transition"
                style="{{ $isActive ? 'border-color: var(--accent); background: var(--accent-soft); color: var(--text);' : 'border-color: var(--line); color: var(--text);' }}"
            >
                {{ $category->title }}
            </a>
        </div>

        @if($orderedItems->isNotEmpty())
            <div x-show="expanded" x-transition x-cloak class="space-y-2">
                @foreach($orderedItems as $item)
                    @if($item['type'] === 'category')
                        @include('theory.partials.tree-nav-mobile', [
                            'categories' => collect([$item['model']]),
                            'selectedCategory' => $selectedCategory,
                            'currentPage' => $currentPage,
                            'routePrefix' => $routePrefix,
                            'level' => $level + 1,
                        ])
                    @else
                        @php($pageItem = $item['model'])
                        @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                        <div style="padding-left: {{ $indent + 40 }}px;">
                            <a
                                href="{{ localized_route($routePrefix . '.show', [$category->slug, $pageItem->slug]) }}"
                                class="flex items-start gap-3 rounded-[18px] border px-3 py-2.5 text-xs transition"
                                style="{{ $isCurrentPage ? 'border-color: var(--accent); background: var(--accent-soft); color: var(--text);' : 'border-color: var(--line); color: var(--muted);' }}"
                                @if($isCurrentPage) aria-current="page" @endif
                            >
                                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-xl bg-ocean text-[9px] font-extrabold text-white">P</span>
                                <span class="min-w-0 break-words leading-5">{{ $pageItem->title }}</span>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endforeach
