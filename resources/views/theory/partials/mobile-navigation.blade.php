@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $categoryPages = $categoryPages ?? collect();
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
@endphp

<div class="mb-8 lg:hidden" x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center justify-between rounded-[24px] border px-4 py-4 shadow-card surface-card-strong"
        style="border-color: var(--line);"
    >
        <div class="text-left">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.map') }}</p>
            <p class="mt-1 text-sm font-bold">{{ $currentPage->title ?? $selectedCategory->title ?? __('public.theory.title') }}</p>
        </div>
        <svg class="h-5 w-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition x-cloak class="mt-5 rounded-[24px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.common.categories') }}</p>
        <div class="mt-4 space-y-3">
            @include('theory.partials.tree-nav-mobile', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
                'currentPage' => $currentPage,
                'routePrefix' => $routePrefix,
            ])
        </div>
    </div>
</div>
