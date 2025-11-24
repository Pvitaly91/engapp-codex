@props(['testData', 'tags'])

@php
    $tagNames = $tags->pluck('name')->toArray();
    $testUrl = route('auto-test.show', [
        'level_from' => $testData['level_from'],
        'level_to' => $testData['level_to'],
        'tags' => $tagNames,
    ]);
@endphp

<div class="bg-background border border-border/60 rounded-xl p-4 flex flex-col hover:border-primary/40 hover:shadow-md transition">
    <a href="{{ $testUrl }}" class="font-medium text-foreground hover:text-primary mb-2">
        Тест {{ $testData['level_label'] }}
    </a>
    
    <div class="text-xs text-muted-foreground mb-2">
        <span class="font-semibold">Рівні:</span> {{ $testData['level_from'] }}, {{ $testData['level_to'] }}
    </div>

    <div class="text-xs text-muted-foreground mb-3">
        <span class="font-semibold">Питань:</span> {{ $testData['questions_count'] }} / {{ $testData['total_available'] }} доступно
    </div>

    @if($tags->isNotEmpty())
        <div class="mb-3 flex flex-wrap gap-1">
            @foreach($tags->take(3) as $tag)
                <span class="inline-block bg-secondary text-secondary-foreground font-medium text-xs px-2 py-0.5 rounded">{{ $tag->name }}</span>
            @endforeach
            @if($tags->count() > 3)
                <span class="inline-block bg-secondary text-secondary-foreground font-medium text-xs px-2 py-0.5 rounded">+{{ $tags->count() - 3 }}</span>
            @endif
        </div>
    @endif

    <a href="{{ $testUrl }}" class="mt-auto inline-block text-center bg-primary hover:bg-primary/80 text-primary-foreground px-4 py-2 rounded-xl text-sm font-semibold transition">
        Пройти тест
    </a>
</div>
