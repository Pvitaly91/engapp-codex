@php
    $categories = $categories ?? collect();
    $selectedCategory = $selectedCategory ?? null;
    $categoryPages = $categoryPages ?? collect();
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    $mobileNavigationUrl = localized_route($routePrefix . '.navigation') . '?' . http_build_query(array_filter([
        'category' => $selectedCategory?->getKey(),
        'page' => $currentPage?->getKey(),
    ]));
@endphp

<div
    class="mb-8 lg:hidden"
    x-data="{
        open: false,
        loaded: false,
        loading: false,
        error: false,
        async toggle() {
            this.open = !this.open;

            if (!this.open || this.loaded || this.loading) {
                return;
            }

            this.loading = true;
            this.error = false;

            try {
                const response = await fetch(@js($mobileNavigationUrl), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                this.$refs.content.innerHTML = await response.text();
                window.Alpine?.initTree(this.$refs.content);
                this.loaded = true;
                this.$nextTick(() => {
                    window.initTheorySidebarSearch?.();
                    window.initTheorySidebarAutoscroll?.();
                });
            } catch (error) {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },
    }"
>
    <button
        type="button"
        @click="toggle()"
        class="flex w-full items-center justify-between rounded-[24px] border px-4 py-4 shadow-card surface-card-strong"
        style="border-color: var(--line);"
        data-theory-mobile-nav-toggle
    >
        <div class="text-left">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.map') }}</p>
            <p class="mt-1 text-sm font-bold">{{ $currentPage->title ?? $selectedCategory->title ?? __('public.theory.title') }}</p>
        </div>
        <svg class="h-5 w-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition x-cloak class="mt-5 flex max-h-[calc(100vh-8rem)] flex-col rounded-[24px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);" data-theory-sidebar data-theory-mobile-nav-panel>
        <div x-show="loading" class="px-3 py-8 text-center text-sm" style="color: var(--muted);">
            {{ __('public.common.loading') }}
        </div>
        <div x-show="error" class="px-3 py-8 text-center text-sm" style="color: var(--muted);">
            {{ __('public.common.error') }}
        </div>
        <div x-ref="content" class="contents"></div>
    </div>
</div>
