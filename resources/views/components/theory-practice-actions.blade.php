@props(['wrap' => false])
<div {{ $attributes->class(['flex flex-col gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between']) }}>
    {{ $status ?? '' }}
    <div class="flex gap-2 sm:ml-auto{{ $wrap ? ' flex-wrap min-w-0' : '' }}">{{ $slot }}</div>
</div>
