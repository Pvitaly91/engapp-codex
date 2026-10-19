@props(['field' => 'input', 'rows' => 1, 'accent' => 'emerald', 'role' => 'sentence', 'wrap' => false])
@php
    $field = $field === 'textarea' ? 'textarea' : 'input';
    $rows = in_array((int) $rows, [1, 2, 3], true) ? (int) $rows : 1;
    [$border, $focus] = match ($accent) {
        'blue' => ['border-blue-300', 'focus:border-blue-500 focus:ring-2 focus:ring-blue-100'],
        'amber' => ['border-amber-300', 'focus:border-amber-500 focus:ring-2 focus:ring-amber-100'],
        'purple' => ['border-purple-300', 'focus:border-purple-500 focus:ring-2 focus:ring-purple-100'],
        default => ['border-emerald-300', 'focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'],
    };
    $classes = $role === 'rephrase' ? 'w-full rounded-lg border-border bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-100 transition-all'
        : 'w-full rounded-xl border '.$border.' bg-white px-3.5 py-2 text-sm font-semibold text-foreground shadow-sm placeholder:text-slate-400 '.$focus.' transition-all';
@endphp
@if($field === 'textarea')
    <textarea rows="{{ $rows }}" {{ $attributes->class([$classes]) }} style="text-transform:none;resize:vertical;min-width:0">{{ $slot }}</textarea>
@else
    <input type="text" {{ $attributes->class([$classes]) }} @if($wrap) style="text-transform:none;min-width:0" @endif />
@endif
