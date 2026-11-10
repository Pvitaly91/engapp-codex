<div class="flex min-h-0 flex-1 flex-col overflow-hidden" data-theory-menu-fragment="{{ $variant }}">
    @if($variant === 'mobile')
        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('public.common.categories') }}</p>
    @endif
    @include('theory.partials.tree-nav-search', [
        'categories' => $categories,
        'searchId' => 'theory-sidebar-search-' . $variant,
    ])
    <div class="mt-4 min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain pr-1" data-theory-sidebar-scroll style="scrollbar-color: color-mix(in srgb, var(--accent) 34%, transparent) transparent;">
        @include('theory.partials.menu-cache-tree', ['level' => 0])
    </div>
</div>
