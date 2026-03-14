{{-- New-Design – Mobile sidebar accordion for theory pages --}}
@php
    $categories      = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $categoryPages   = $categoryPages ?? collect();
    $currentPage     = $currentPage ?? null;
    $routePrefix     = $routePrefix ?? 'new-design.theory';
    $hasCategoryPages = $selectedCategory && $categoryPages->isNotEmpty();
@endphp

<div
    class="lg:hidden space-y-4 mb-6"
    x-data="{
        showCategories: false,
        showPages: {{ $hasCategoryPages ? 'true' : 'false' }}
    }"
>
    {{-- Categories accordion --}}
    <div class="nd-card">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-[var(--nd-night)]"
            @click="showCategories = !showCategories"
            :aria-expanded="showCategories"
        >
            <span>Категорії</span>
            <span class="flex items-center gap-2 text-xs font-medium text-[var(--nd-muted)]">
                <span>{{ $selectedCategory->title ?? 'Оберіть категорію' }}</span>
                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': showCategories }"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>
        <div x-show="showCategories" x-transition style="display:none;" class="border-t border-line">
            <nav class="space-y-1 px-4 py-3">
                @forelse($categories as $category)
                    @php($isActive = $selectedCategory && $selectedCategory->is($category))
                    <a
                        href="{{ localized_route($routePrefix . '.category', $category->slug) }}"
                        class="block rounded-xl px-3 py-2 text-sm font-medium transition whitespace-normal break-words
                            {{ $isActive
                                ? 'bg-ocean/10 text-ocean font-semibold'
                                : 'text-[var(--nd-muted)] hover:bg-ocean/6 hover:text-[var(--nd-ocean)]' }}"
                        @if($isActive) aria-current="page" @endif
                    >
                        {{ $category->title }}
                    </a>
                @empty
                    <p class="px-3 py-2 text-sm text-[var(--nd-muted)]">Немає категорій.</p>
                @endforelse
            </nav>
        </div>
    </div>

    {{-- Pages accordion --}}
    @if($hasCategoryPages)
        <div class="nd-card">
            <button
                type="button"
                class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-[var(--nd-night)]"
                @click="showPages = !showPages"
                :aria-expanded="showPages"
            >
                <span>Сторінки розділу</span>
                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': showPages }"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="showPages" x-transition style="display:none;" class="border-t border-line">
                <nav class="space-y-1 px-4 py-3">
                    @foreach($categoryPages as $pageItem)
                        @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                        <a
                            href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                            class="block rounded-xl px-3 py-2 text-sm transition whitespace-normal break-words
                                {{ $isCurrentPage
                                    ? 'bg-ocean/10 text-ocean font-semibold'
                                    : 'text-[var(--nd-muted)] hover:bg-ocean/6 hover:text-[var(--nd-ocean)]' }}"
                            @if($isCurrentPage) aria-current="page" @endif
                        >
                            {{ $pageItem->title }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif
</div>
