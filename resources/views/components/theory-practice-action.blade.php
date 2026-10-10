@props(['accent' => 'blue', 'secondary' => false])
@php
    $classes = match ($accent) {
        'amber' => $secondary ? 'rounded-lg border border-amber-200 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50' : 'rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700',
        'emerald' => $secondary ? 'rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50' : 'rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700',
        'purple' => $secondary ? 'rounded-lg border border-purple-200 bg-white px-4 py-2 text-sm font-semibold text-purple-700 transition hover:bg-purple-50' : 'rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700',
        default => $secondary ? 'rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50' : 'rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700',
    };
@endphp
<button type="button" {{ $attributes->class([$classes]) }}>{{ $slot }}</button>
