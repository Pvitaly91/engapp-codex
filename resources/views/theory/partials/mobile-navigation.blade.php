@php
    $selectedCategory = $selectedCategory ?? null;
    $currentPage = $currentPage ?? null;
    $routePrefix = $routePrefix ?? 'theory';
    if (!array_key_exists('theoryMenu', get_defined_vars())) {
        $theoryMenu = app(\App\Services\TheoryMenuStore::class)->active(
            app(\App\Support\SiteMode::class)->current(), app()->getLocale(), $routePrefix
        );
    }
    $revision = $theoryMenu['revision'] ?? null;
    $mobileNavigationUrl = $revision === null ? null
        : localized_route($routePrefix . '.navigation', [], false) . '?' . http_build_query(['revision' => $revision]);
@endphp
@include('theory.partials.menu-cache-assets')

<div
    class="mb-8 lg:hidden"
    data-theory-menu-context="mobile"
    data-theory-menu-current-page="{{ $currentPage?->getKey() }}"
    data-theory-menu-selected-category="{{ $selectedCategory?->getKey() }}"
    data-theory-menu-revision="{{ $revision }}"
    data-theory-menu-url="{{ $mobileNavigationUrl }}"
    x-data="{
        open: false,
        loaded: {{ $mobileNavigationUrl === null ? 'true' : 'false' }},
        loading: false,
        error: false,
        async toggle() {
            this.open = !this.open;

            if (!this.open || this.loading) {
                return;
            }
            if (this.loaded) {
                this.$nextTick(() => window.showTheoryMenu?.(this.$root));
                return;
            }

            this.loading = true;
            this.error = false;

            try {
                await window.loadTheoryMenu(this.$root, this.$refs.content);
                this.loaded = true;
                this.$nextTick(() => window.showTheoryMenu?.(this.$root));
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
        aria-expanded="false"
        :aria-expanded="open.toString()"
        aria-controls="theory-mobile-navigation"
    >
        <div class="text-left">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.copilot_theory.map') }}</p>
            <p class="mt-1 text-sm font-bold">{{ $currentPage->title ?? $selectedCategory->title ?? __('public.theory.title') }}</p>
        </div>
        <svg class="h-5 w-5 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition x-cloak class="mt-5 flex max-h-[calc(100vh-8rem)] flex-col rounded-[24px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);" data-theory-sidebar data-theory-mobile-nav-panel id="theory-mobile-navigation" :aria-busy="loading.toString()">
        <div x-show="loading" class="px-3 py-8 text-center text-sm" style="color: var(--muted);">
            {{ __('public.common.loading') }}
        </div>
        <div x-show="error" x-cloak class="px-3 py-8 text-center text-sm" style="color: var(--muted);" role="alert">
            {{ __('public.common.error') }}
            @include('theory.partials.menu-cache-fallback')
        </div>
        <div x-ref="content" x-show="loaded && !loading && !error" x-cloak class="flex min-h-0 flex-1 flex-col overflow-hidden">
            @if($mobileNavigationUrl === null)
                @include('theory.partials.menu-cache-fallback')
            @endif
        </div>
    </div>
    <noscript>@include('theory.partials.menu-cache-fallback')</noscript>
</div>
