@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $categoryPages = $categoryPages ?? collect();
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    $hasCategoryPages = $selectedCategory && $categoryPages->isNotEmpty();
@endphp

<div
    class="lg:hidden space-y-4 mb-6"
    x-data="{
        showCategories: false,
        showPages: {{ $hasCategoryPages ? 'true' : 'false' }}
    }"
>
    <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
            @click="showCategories = !showCategories"
            :aria-expanded="showCategories"
        >
            <span>Категорії</span>
            <span class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                <span>{{ $selectedCategory->title ?? 'Оберіть категорію' }}</span>
                <svg
                    class="h-4 w-4 transition-transform"
                    :class="{ 'rotate-180': showCategories }"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>
        <div x-show="showCategories" x-transition style="display: none;" class="border-t border-border/80">
            <nav class="space-y-1 px-4 py-3">
                @forelse($categories as $category)
                    @php($isActive = $selectedCategory && $selectedCategory->is($category))
                    <a
                        href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                        class="block rounded-xl px-3 py-2 text-left text-sm font-medium transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 whitespace-normal break-words {{ $isActive ? 'bg-primary px-[5px] text-primary-foreground' : 'text-muted-foreground' }}"
                        @if($isActive) aria-current="page" @endif
                    >
                        <span class="block">{{ $category->title }}</span>
                    </a>
                @empty
                    <p class="px-3 py-2 text-sm text-muted-foreground">Немає категорій.</p>
                @endforelse
            </nav>
        </div>
    </div>

    @if($hasCategoryPages)
        <div class="rounded-2xl border border-border/80 bg-card shadow-soft">
            <button
                type="button"
                class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold"
                @click="showPages = !showPages"
                :aria-expanded="showPages"
            >
                <span>Сторінки розділу</span>
                <svg
                    class="h-4 w-4 transition-transform"
                    :class="{ 'rotate-180': showPages }"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="showPages" x-transition style="display: none;" class="border-t border-border/80">
                <nav class="space-y-1 px-4 py-3">
                    @foreach($categoryPages as $pageItem)
                        @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                        <a
                            href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                            class="block rounded-xl px-3 py-2 text-left text-sm transition hover:bg-muted/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary/40 whitespace-normal break-words {{ $isCurrentPage ? 'bg-secondary text-secondary-foreground font-semibold' : 'text-muted-foreground' }}"
                            @if($isCurrentPage) aria-current="page" @endif
                        >
                            <span class="block">{{ $pageItem->title }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif
</div>
