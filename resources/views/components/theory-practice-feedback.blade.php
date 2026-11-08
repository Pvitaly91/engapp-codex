@props(['tag' => 'div', 'position' => null, 'accent' => null, 'correct' => null])
@php
    $tag = in_array($tag, ['div', 'p', 'span'], true) ? $tag : 'div';
    $spacing = match ($position) { 'below' => 'mt-2 ', 'inline' => 'basis-full pl-7 ', default => '' };
    $color = match ($accent) { 'blue' => ' text-blue-700', 'amber' => ' text-amber-700', 'emerald' => ' text-emerald-700', 'purple' => ' text-purple-700', default => '' };
@endphp
<{{ $tag }} {{ $attributes->class([$spacing.'text-xs font-semibold'.$color]) }} @if($correct !== null) :class="{{ $correct }} ? 'text-emerald-700' : 'text-rose-700'" @endif>{{ $slot }}</{{ $tag }}>
