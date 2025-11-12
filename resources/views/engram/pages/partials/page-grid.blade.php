@php($categorySlug = $category->slug ?? null)

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($pages as $page)
        <a href="{{ $categorySlug ? route('pages.show', [$categorySlug, $page->slug]) : '#' }}" class="block p-4 bg-card text-card-foreground rounded-xl shadow-soft hover:bg-muted">
            @php($iconPath = $page->getIconPath())
            
            @if($iconPath)
                <div class="mb-3">
                    <img src="{{ $iconPath }}" alt="{{ $page->title }}" class="w-16 h-16 object-contain">
                </div>
            @endif
            
            <div class="font-semibold">{{ $page->title }}</div>
            @if(! empty($page->text))
                <p class="mt-2 text-sm text-muted-foreground">{{ $page->text }}</p>
            @endif
        </a>
    @endforeach
</div>
