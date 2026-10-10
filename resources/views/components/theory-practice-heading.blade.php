@props(['title', 'number' => null, 'accent' => 'blue', 'level' => 'h3', 'instruction' => null, 'technical' => false])
@php
    $tag = in_array($level, ['h3', 'h4'], true) ? $level : 'h3';
    $color = \App\Support\TheoryComponents::accent($accent);
@endphp
<{{ $tag }} class="font-semibold text-foreground text-sm flex items-center gap-2">
    @if($number !== null)<span @if($technical) aria-hidden="true" data-theory-ui @endif class="flex h-5 w-5 items-center justify-center rounded {{ $color['badge'] }} text-white text-[10px]{{ $technical ? ' shrink-0' : '' }}">{{ $number }}</span>@endif
    {{ \App\Support\TheoryComponents::body($title) }}
</{{ $tag }}>
@if(\App\Support\TheoryComponents::present($instruction))<p class="text-base text-muted-foreground mt-1 leading-relaxed" data-practice-instruction>{{ \App\Support\TheoryComponents::body($instruction) }}</p>@endif
