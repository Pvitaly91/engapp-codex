@props(['accent' => 'blue', 'technical' => false])
@php($color = match ($accent) {
    'amber' => 'bg-amber-100 text-amber-700',
    'emerald' => 'bg-emerald-100 text-emerald-600',
    'purple' => 'bg-purple-100 text-purple-600',
    default => 'bg-blue-100 text-blue-600',
})
<span {{ $attributes->class(['flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full '.$color.' text-[10px] font-bold']) }} @if($technical) aria-hidden="true" data-theory-ui @endif>{{ $slot }}</span>
