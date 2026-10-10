@props(['accent' => 'blue', 'selected' => null, 'checked' => 'false', 'correct' => 'false', 'uppercase' => true, 'compact' => false, 'wrap' => false])
@php
    [$active, $idle] = match ($accent) {
        'amber' => ['border-amber-600 bg-amber-600 text-white shadow-sm', 'border-amber-200 bg-white text-amber-700 hover:border-amber-400 hover:bg-amber-50'],
        'emerald' => ['border-emerald-600 bg-emerald-600 text-white shadow-sm', 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'],
        'purple' => ['border-purple-600 bg-purple-600 text-white shadow-sm', 'border-purple-200 bg-white text-purple-700 hover:border-purple-400 hover:bg-purple-50'],
        default => ['border-blue-600 bg-blue-600 text-white shadow-sm', 'border-blue-200 bg-white text-blue-700 hover:border-blue-400 hover:bg-blue-50'],
    };
    $base = $compact ? 'rounded-xl border px-3 py-2 text-left text-sm font-semibold transition'
        : 'min-w-12 rounded-xl border px-4 py-2 text-sm font-extrabold'.($uppercase ? ' uppercase' : ' text-left').' transition';
    $state = $selected === null ? null : "[($selected) ? '$active' : '$idle', ($checked) && ($selected) ? (($correct) ? 'ring-2 ring-emerald-300' : 'ring-2 ring-rose-300') : ''].join(' ')";
@endphp
<button type="button" {{ $attributes->class([$base]) }} @if($state !== null) :class="{{ $state }}" @endif @if($wrap) style="text-transform:none;white-space:normal;overflow-wrap:anywhere;max-width:100%" @endif>{{ $slot }}</button>
