@props(['accent' => 'blue', 'visibleOverflow' => false, 'tag' => 'div', 'compatibility' => null])
@php
    $tag = in_array($tag, ['article', 'div'], true) ? $tag : 'div';
    $surface = match ($accent) {
        'amber' => 'border-amber-100 bg-amber-50/30',
        'emerald' => 'border-emerald-100 bg-emerald-50/30',
        'purple' => 'border-purple-100 bg-purple-50/30',
        default => 'border-blue-100 bg-blue-50/30',
    };
    $classes = match ($compatibility) {
        'plain' => 'theory-exercise rounded-xl border border-border p-4 space-y-4',
        'reference' => 'theory-exercise rounded-xl border border-border overflow-hidden',
        default => 'theory-exercise rounded-xl border '.$surface.($visibleOverflow ? ' overflow-visible' : ' overflow-hidden'),
    };
@endphp
<{{ $tag }} {{ $attributes->class([$classes]) }}>
    {{ $slot }}
</{{ $tag }}>
