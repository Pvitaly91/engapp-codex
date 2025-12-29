@php($categoryPages = $categoryPages ?? collect())
@php($showCategoryPagesNav = $showCategoryPagesNav ?? false)
@php($routePrefix = $routePrefix ?? 'pages')
@php($class = trim('space-y-8 ' . ($class ?? '')))
<aside class="{{ $class }}">
    <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-brand-600">Категорії</h2>
        <nav class="mt-3 space-y-1">
            @forelse($categories as $category)
                @php($isActive = $selectedCategory && $selectedCategory->is($category))
                <a
                    href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                    class="block rounded-xl px-3 py-2 text-sm font-medium transition hover:-translate-y-0.5 hover:shadow-sm {{ $isActive ? 'bg-brand-600 text-white' : 'text-[var(--muted)] hover:bg-brand-50 hover:text-brand-700' }}"
                >
                    <span>{{ $category->title }}</span>
                </a>
            @empty
                <p class="text-sm text-[var(--muted)]">Немає категорій.</p>
            @endforelse
        </nav>
    </div>

    @if ($showCategoryPagesNav && $selectedCategory && $categoryPages->isNotEmpty())
        <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)] p-5 shadow-sm">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-brand-600">Сторінки розділу</h3>
            <nav class="mt-3 space-y-1">
                @foreach($categoryPages as $pageItem)
                    @php($isCurrentPage = isset($currentPage) && $currentPage && $currentPage->is($pageItem))
                    <a
                        href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                        class="block rounded-xl px-3 py-2 text-sm transition hover:-translate-y-0.5 hover:shadow-sm {{ $isCurrentPage ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-[var(--muted)] hover:bg-brand-50/50 hover:text-brand-600' }}"
                    >
                        {{ $pageItem->title }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif
</aside>
