@props(['accent' => 'blue'])
@php($surface = match ($accent) {
    'amber' => 'border-amber-100 bg-amber-50/50',
    'emerald' => 'border-emerald-100 bg-emerald-50/50',
    'purple' => 'border-purple-100 bg-purple-50/50',
    default => 'border-blue-100 bg-blue-50/50',
})
<div {{ $attributes->class(['border-b '.$surface.' px-4 py-3']) }}>{{ $slot }}</div>
