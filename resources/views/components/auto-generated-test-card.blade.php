@props(['test'])

@php
    // Get the filter levels for display
    $filters = $test->filters ?? [];
    $levels = $filters['levels'] ?? [];
    $tags = $filters['tags'] ?? [];
    $questionsCount = $filters['num_questions'] ?? 15;
    
    // Get total available from computed attribute (set in service to avoid N+1 queries)
    $totalAvailable = $test->getAttribute('total_questions_available') ?? $questionsCount;
    $publicName = \App\Support\SentenceBuilderBranding::publicText((string) ($test->name ?? ''));
    $publicSlug = data_get($test, 'public_slug', \App\Support\SentenceBuilderBranding::canonicalLessonSlug((string) ($test->slug ?? '')));
    
    // Check if this is a virtual test (not persisted in the database)
    // Use isVirtual() method if available, otherwise check exists property
    $isVirtual = method_exists($test, 'isVirtual') ? $test->isVirtual() : (($test->exists ?? true) === false);
    $launchPerClickHandler = "const url = new URL(this.href, window.location.origin); const token = (window.crypto && typeof window.crypto.randomUUID === 'function') ? window.crypto.randomUUID() : ('launch-' + Date.now() + '-' + Math.random().toString(16).slice(2)); url.searchParams.set('launch', token); window.location.href = url.toString(); return false;";
    
    // Build URL with server-side registered filters for virtual tests.
    if ($isVirtual) {
        $registerMethod = data_get($filters, '__meta.theory_page_static_slug') === true
            ? 'registerStatic'
            : 'register';
        $publicSlug = \App\Support\VirtualTestRegistry::{$registerMethod}(
            $publicSlug,
            $publicName,
            is_array($filters) ? $filters : [],
            (string) ($test->description ?? ''),
            (int) $totalAvailable
        );
        $testUrl = localized_route('test.show', $publicSlug);
    } else {
        \App\Support\VirtualTestRegistry::rememberSlug($publicSlug);
        $testUrl = localized_route('test.show', $publicSlug);
    }
@endphp

<div class="bg-background border border-border/60 rounded-xl p-4 flex flex-col hover:border-primary/40 hover:shadow-md transition">
    <a href="{{ $testUrl }}" class="font-medium text-foreground hover:text-primary mb-2" @if($isVirtual) onclick="{{ $launchPerClickHandler }}" @endif>
        {{ $publicName }}
    </a>
    
    @if(!empty($levels))
        <div class="text-xs text-muted-foreground mb-2">
            <span class="font-semibold">{{ __('frontend.tests.related.levels') }}:</span> {{ implode(', ', $levels) }}
        </div>
    @endif

    <div class="text-xs text-muted-foreground mb-3">
        <span class="font-semibold">{{ __('frontend.catalog.questions') }}:</span> {{ $questionsCount }} / {{ $totalAvailable }}
    </div>

    @if(!empty($tags))
        <div class="mb-3 flex flex-wrap gap-1">
            @foreach(array_slice($tags, 0, 3) as $tag)
                <span class="inline-block bg-secondary text-secondary-foreground font-medium text-xs px-2 py-0.5 rounded">{{ \App\Support\SentenceBuilderBranding::publicText(\App\Support\TheoryTagLabel::display($tag, app()->getLocale())) }}</span>
            @endforeach
            @if(count($tags) > 3)
                <span class="inline-block bg-secondary text-secondary-foreground font-medium text-xs px-2 py-0.5 rounded">+{{ count($tags) - 3 }}</span>
            @endif
        </div>
    @endif

    <a href="{{ $testUrl }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-xl text-sm font-semibold transition" @if($isVirtual) onclick="{{ $launchPerClickHandler }}" @endif>
        {{ __('frontend.tests.related.take_test') }}
    </a>
</div>
