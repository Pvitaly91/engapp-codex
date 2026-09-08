@php
    $desktopNavigationUrl = localized_route($routePrefix . '.navigation') . '?' . http_build_query(array_filter([
        'variant' => 'desktop',
        'category' => $selectedCategory?->getKey(),
        'page' => $currentPage?->getKey(),
    ]));
@endphp

{{-- Keep the sidebar behavior/styles in the page without rendering the category tree. --}}
@include('theory.partials.tree-nav', [
    'categories' => collect(),
    'selectedCategory' => null,
    'currentPage' => null,
    'routePrefix' => $routePrefix,
])

<div
    class="flex min-h-0 flex-1 flex-col overflow-hidden"
    x-data="{
        loading: true,
        error: false,
        async load() {
            try {
                const response = await fetch(@js($desktopNavigationUrl), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                this.$refs.content.innerHTML = await response.text();
                window.Alpine?.initTree(this.$refs.content);
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
    x-init="load()"
    :aria-busy="loading.toString()"
    data-theory-desktop-navigation-loader
>
    <div x-show="loading" class="px-3 py-8 text-center text-sm" style="color: var(--muted);">
        {{ __('public.common.loading') }}
    </div>
    <div x-show="error" x-cloak class="px-3 py-8 text-center text-sm" style="color: var(--muted);" role="alert">
        {{ __('public.common.error') }}
    </div>
    <div x-ref="content" x-show="!loading && !error" x-cloak class="flex min-h-0 flex-1 flex-col overflow-hidden"></div>
</div>
