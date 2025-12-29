@php($categorySlug = $category->slug ?? null)
@php($routePrefix = $routePrefix ?? 'pages')

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($pages as $page)
        <a href="{{ $categorySlug ? localized_route(($routePrefix ?? 'pages') . '.show', [$categorySlug, $page->slug]) : '#' }}" class="group block p-5 bg-[var(--card)] text-[var(--fg)] rounded-2xl border border-[var(--border)] shadow-sm transition hover:border-brand-500 hover:shadow-md hover:-translate-y-0.5">
            <div class="font-bold text-lg group-hover:text-brand-600 transition">{{ $page->title }}</div>
            @if(! empty($page->text))
                <p class="mt-2 text-sm text-[var(--muted)] line-clamp-2">{{ $page->text }}</p>
            @endif
            <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-600">
                {{ __('public.common.read_more', ['default' => 'Читати']) }}
                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </span>
        </a>
    @endforeach
</div>
