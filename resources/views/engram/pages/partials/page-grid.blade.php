@php($categorySlug = $category->slug ?? null)
@php($routePrefix = $routePrefix ?? 'pages')

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($pages as $page)
        <a href="{{ $categorySlug ? localized_route(($routePrefix ?? 'pages') . '.show', [$categorySlug, $page->slug]) : '#' }}" class="block p-4 bg-card text-card-foreground rounded-xl shadow-soft hover:bg-muted">
            <div class="font-semibold">{{ $page->title }}</div>
            @if(! empty($page->text))
                <p class="mt-2 text-sm text-muted-foreground">{{ $page->text }}</p>
            @endif
        </a>
    @endforeach
</div>
