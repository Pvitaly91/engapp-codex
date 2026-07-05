@if($variant === 'mobile')
<p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.common.categories') }}</p>
@include('theory.partials.tree-nav-search', [
    'categories' => $categories,
    'searchId' => 'theory-sidebar-search-mobile',
])
<div class="mt-4 min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain pr-1" data-theory-sidebar-scroll style="scrollbar-color: color-mix(in srgb, var(--accent) 34%, transparent) transparent;">
    @include('theory.partials.tree-nav-mobile', [
        'categories' => $categories,
        'selectedCategory' => $selectedCategory,
        'currentPage' => $currentPage,
        'routePrefix' => $routePrefix,
    ])
</div>
@else
@include('theory.partials.tree-nav-search', [
    'categories' => $categories,
    'searchId' => 'theory-sidebar-search-desktop',
])
<div class="mt-4 min-h-0 flex-1 overflow-y-auto overscroll-contain pr-1 space-y-3" data-theory-sidebar-scroll style="scrollbar-color: color-mix(in srgb, var(--accent) 34%, transparent) transparent;">
    @include('theory.partials.tree-nav', [
        'categories' => $categories,
        'selectedCategory' => $selectedCategory,
        'currentPage' => $currentPage,
        'routePrefix' => $routePrefix,
    ])
</div>
@endif
