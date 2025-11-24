@props(['test'])

@php
    // Get the filter levels for display
    $filters = $test->filters ?? [];
    $levels = $filters['levels'] ?? [];
    $levelLabel = !empty($levels) ? implode('-', $levels) : 'N/A';
    $tags = $filters['tags'] ?? [];
    
    // Get total available questions (count questions matching the filters)
    $totalAvailable = \App\Models\Question::query()
        ->where('flag', 2)
        ->whereIn('level', $levels)
        ->when(!empty($tags), fn($q) => $q->whereHas('tags', fn($tq) => $tq->whereIn('name', $tags)))
        ->count();
    
    $questionsCount = $filters['num_questions'] ?? 15;
@endphp

<div class="bg-background border border-border/60 rounded-xl p-4 flex flex-col hover:border-primary/40 hover:shadow-md transition">
    <a href="{{ route('saved-test.js', $test->slug) }}" class="font-medium text-foreground hover:text-primary mb-2">
        {{ $test->name }}
    </a>
    
    @if(!empty($levels))
        <div class="text-xs text-muted-foreground mb-2">
            <span class="font-semibold">Рівні:</span> {{ implode(', ', $levels) }}
        </div>
    @endif

    <div class="text-xs text-muted-foreground mb-3">
        <span class="font-semibold">Питань:</span> {{ $questionsCount }} / {{ $totalAvailable }} доступно
    </div>

    @if(!empty($tags))
        <div class="mb-3 flex flex-wrap gap-1">
            @foreach(array_slice($tags, 0, 3) as $tag)
                <span class="inline-block bg-secondary text-secondary-foreground font-medium text-xs px-2 py-0.5 rounded">{{ $tag }}</span>
            @endforeach
            @if(count($tags) > 3)
                <span class="inline-block bg-secondary text-secondary-foreground font-medium text-xs px-2 py-0.5 rounded">+{{ count($tags) - 3 }}</span>
            @endif
        </div>
    @endif

    <a href="{{ route('saved-test.js', $test->slug) }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-xl text-sm font-semibold transition">
        Пройти тест
    </a>
</div>
