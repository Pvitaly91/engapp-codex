@props(['used' => null, 'static' => false, 'wrap' => false])
@if($static)
    <span {{ $attributes->class(['rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700']) }} style="text-transform:none;white-space:normal;overflow-wrap:anywhere;max-width:100%">{{ $slot }}</span>
@else
    <button type="button" {{ $attributes->class(['rounded-lg border px-3 py-1.5 text-sm font-semibold transition']) }}
        @if($used !== null) :disabled="{{ $used }}" :class="{{ $used }} ? 'border-emerald-200 bg-white/60 text-muted-foreground/80 cursor-not-allowed' : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50 hover:text-emerald-900'" @endif
        @if($wrap) style="text-transform:none;white-space:normal;overflow-wrap:anywhere;max-width:100%" @endif>{{ $slot }}</button>
@endif
