@php($wrong = ($node['variant'] ?? 'right') === 'wrong')
@if($node['inline'] ?? false)
    @if($wrong)<span class="theory-correction theory-correction--wrong line-through" @if(isset($node['lang'])) lang="{{ $node['lang'] }}" @endif>{{ \App\Support\TheoryComponents::body($node['text'] ?? '') }}</span>@else<span class="theory-correction theory-correction--right font-semibold" @if(isset($node['lang'])) lang="{{ $node['lang'] }}" @endif>{{ \App\Support\TheoryComponents::body($node['text'] ?? '') }}</span>@endif
@else
    <div class="theory-example theory-example--{{ $wrong ? 'wrong' : 'right' }} flex items-center gap-2.5 rounded-lg {{ $wrong ? 'bg-rose-50 border border-rose-100' : 'bg-emerald-50 border border-emerald-100' }} px-3 py-2">
        <svg class="h-4 w-4 flex-shrink-0 {{ $wrong ? 'text-rose-500' : 'text-emerald-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $wrong ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"/></svg>
        @if($wrong)<code @if(isset($node['lang'])) lang="{{ $node['lang'] }}" @endif class="font-mono text-xs text-rose-700 line-through">{{ \App\Support\TheoryComponents::body($node['text'] ?? '') }}</code>@else<span @if(isset($node['lang'])) lang="{{ $node['lang'] }}" @endif class="text-xs text-emerald-700">{{ \App\Support\TheoryComponents::body($node['text'] ?? '') }}</span>@endif
    </div>
@endif
