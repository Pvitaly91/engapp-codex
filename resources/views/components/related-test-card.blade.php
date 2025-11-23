@props(['test'])

<div class="bg-background border border-border/60 rounded-xl p-4 flex flex-col hover:border-primary/40 hover:shadow-md transition">
    <a href="{{ route('saved-test.js', $test->slug) }}" class="font-medium text-foreground hover:text-primary mb-2">
        {{ $test->name }}
    </a>
    
    @if($test->level_range->isNotEmpty())
        <div class="text-xs text-muted-foreground mb-2">
            <span class="font-semibold">Рівні:</span> {{ $test->level_range->join(', ') }}
        </div>
    @endif

    @if($test->matching_tags->isNotEmpty())
        <div class="mb-3 flex flex-wrap gap-1">
            @foreach($test->matching_tags as $tag)
                <span class="inline-block bg-primary/10 text-primary/90 font-medium text-xs px-2 py-0.5 rounded">{{ $tag }}</span>
            @endforeach
        </div>
    @endif

    @if($test->description)
        <p class="text-sm text-muted-foreground mb-3 line-clamp-2">{{ $test->description }}</p>
    @endif

    <a href="{{ route('saved-test.js', $test->slug) }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-xl text-sm font-semibold transition">
        Пройти тест
    </a>
</div>
