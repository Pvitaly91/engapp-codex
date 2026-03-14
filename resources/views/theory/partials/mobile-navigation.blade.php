@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $categoryPages = $categoryPages ?? collect();
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
@endphp

<div class="mb-6 lg:hidden" x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between rounded-[24px] border px-4 py-4 shadow-card surface-card-strong"
        style="border-color: var(--line);"
    >
        <div class="text-left">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">Navigation</p>
            <p class="mt-1 text-sm font-bold">{{ $selectedCategory->title ?? __('public.theory.title') }}</p>
        </div>
        <svg class="h-5 w-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition x-cloak class="mt-4 space-y-4 rounded-[24px] border p-4 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div>
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">Categories</p>
            <div class="mt-3 space-y-2">
                @include('theory.partials.tree-nav-mobile', [
                    'categories' => $categories,
                    'selectedCategory' => $selectedCategory,
                    'currentPage' => $currentPage,
                    'routePrefix' => $routePrefix,
                ])
            </div>
        </div>

        @if($selectedCategory && $categoryPages->isNotEmpty())
            <div class="border-t pt-4" style="border-color: var(--line);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.common.section_pages') }}</p>
                <div class="mt-3 space-y-2">
                    @foreach($categoryPages as $pageItem)
                        @php($isCurrentPage = $currentPage && $currentPage->is($pageItem))
                        <a
                            href="{{ localized_route($routePrefix . '.show', [$selectedCategory->slug, $pageItem->slug]) }}"
                            class="flex items-start gap-3 rounded-[18px] border px-3 py-3 text-sm transition"
                            style="{{ $isCurrentPage ? 'border-color: var(--accent); background: var(--accent-soft); color: var(--text);' : 'border-color: var(--line); color: var(--muted);' }}"
                            @if($isCurrentPage) aria-current="page" @endif
                        >
                            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-ocean text-[10px] font-extrabold text-white">
                                {{ $loop->iteration }}
                            </span>
                            <span class="min-w-0 break-words leading-5">{{ $pageItem->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
