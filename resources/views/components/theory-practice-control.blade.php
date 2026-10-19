@props(['accent' => 'blue', 'marker' => null, 'layout' => 'row', 'technical' => false, 'wrap' => false, 'align' => 'center'])
@php
    $rowAlignment = $align === 'start' ? 'sm:items-start' : 'sm:items-center';
    $classes = match ($layout) {
        'inline' => 'relative flex flex-wrap items-center gap-2 text-sm text-foreground/80 bg-white/60 rounded-lg p-3 border border-white',
        'rephrase' => 'space-y-1.5 bg-white/60 rounded-lg p-3 border border-white',
        default => 'flex flex-col sm:flex-row '.$rowAlignment.' gap-2 bg-white/60 rounded-lg p-3 border border-white',
    };
@endphp
<div {{ $attributes->class([$classes]) }}>
    {{ $before ?? '' }}
    @if($marker !== null)<x-theory-practice-marker :accent="$accent" :technical="$technical">{{ $marker }}</x-theory-practice-marker>@endif
    @if($layout === 'row')<div class="flex-1{{ $wrap ? ' min-w-0' : '' }}">{{ $slot }}</div>@else{{ $slot }}@endif
</div>
