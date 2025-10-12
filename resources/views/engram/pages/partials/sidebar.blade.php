@php($categoryPages = $categoryPages ?? collect())
@php($showCategoryPagesNav = $showCategoryPagesNav ?? false)
<aside class="space-y-8">
    <div>
        <h2 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Категорії</h2>
        <nav class="mt-3 space-y-1">
            @forelse($categories as $category)
                @php($isActive = $selectedCategory && $selectedCategory->is($category))
                <a
                    href="{{ route('pages.category', $category->slug) }}"
                    class="block rounded-xl px-3 py-2 text-sm font-medium transition hover:bg-muted/80 {{ $isActive ? 'bg-primary/10 text-primary' : 'text-muted-foreground' }}"
                >
                    <span>{{ $category->title }}</span>
                </a>
            @empty
                <p class="text-sm text-muted-foreground">Немає категорій.</p>
            @endforelse
        </nav>
    </div>

    @if ($showCategoryPagesNav && $selectedCategory && $categoryPages->isNotEmpty())
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Сторінки розділу</h3>
            <nav class="mt-3 space-y-1">
                @foreach($categoryPages as $pageItem)
                    @php($isCurrentPage = isset($currentPage) && $currentPage && $currentPage->is($pageItem))
                    <a
                        href="{{ route('pages.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                        class="block rounded-xl px-3 py-2 text-sm transition hover:bg-muted/80 {{ $isCurrentPage ? 'bg-secondary/20 text-secondary-foreground font-semibold' : 'text-muted-foreground' }}"
                    >
                        {{ $pageItem->title }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif
</aside>
