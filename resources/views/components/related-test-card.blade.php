@props(['test'])

@php
    $publicName = \App\Support\SentenceBuilderBranding::publicText((string) ($test->name ?? ''));
    $publicSlug = data_get($test, 'public_slug', \App\Support\SentenceBuilderBranding::canonicalLessonSlug((string) ($test->slug ?? '')));
    $publicDescription = \App\Support\SentenceBuilderBranding::publicText((string) ($test->description ?? ''));
    \App\Support\VirtualTestRegistry::rememberSlug($publicSlug);
    $testUrl = localized_route('test.show', $publicSlug);
@endphp

<div class="bg-background border border-border/60 rounded-xl p-4 flex flex-col hover:border-primary/40 hover:shadow-md transition">
    <a href="{{ $testUrl }}" class="font-medium text-foreground hover:text-primary mb-2">
        {{ $publicName }}
    </a>
    
    @if($test->level_range->isNotEmpty())
        <div class="text-xs text-muted-foreground mb-2">
            <span class="font-semibold">{{ __('frontend.tests.related.levels') }}:</span> {{ $test->level_range->join(', ') }}
        </div>
    @endif

    @if($test->matching_tags->isNotEmpty())
        <div class="mb-3 flex flex-wrap gap-1">
            @foreach($test->matching_tags as $tag)
                <span class="inline-block bg-primary text-primary-foreground font-medium text-xs px-2 py-0.5 rounded">{{ \App\Support\SentenceBuilderBranding::publicText((string) $tag) }}</span>
            @endforeach
        </div>
    @endif

    @if($publicDescription !== '')
        <p class="text-sm text-muted-foreground mb-3 line-clamp-2">{{ $publicDescription }}</p>
    @endif

    <a href="{{ $testUrl }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-xl text-sm font-semibold transition">
        {{ __('frontend.tests.related.take_test') }}
    </a>
</div>
