@php($accent = \App\Support\TheoryComponents::accent($node['accent'] ?? null))
<article data-theory-component="usage" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-item rounded-xl border {{ $accent['border'] }} {{ $accent['bg'] }}"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    <div class="p-4">
        @if(!empty($node['label']))
            <div class="flex items-center gap-2 mb-3">
                <span class="flex h-5 w-5 items-center justify-center rounded-full {{ $accent['badge'] }} text-white text-[10px] font-bold">{{ $node['number'] ?? '' }}</span>
                <span class="text-xs font-bold uppercase tracking-wider {{ $accent['text'] }}">{{ $node['label'] }}</span>
            </div>
        @endif
        @if(\App\Support\TheoryComponents::present($node['body_html'] ?? null))
            @if($node['body_inline'] ?? false)
                <p class="text-sm text-foreground/80 leading-relaxed mb-4">{{ \App\Support\TheoryComponents::body($node['body_html']) }}</p>
            @else
                <div class="text-sm text-foreground/80 leading-relaxed mb-4 theory-point-fragment">{{ \App\Support\TheoryComponents::body($node['body_html']) }}</div>
            @endif
        @endif
        @include('theory.components.children', ['children' => $node['items'] ?? []])
        @if(!empty($node['examples']))
            <div class="space-y-2">@include('theory.components.children', ['children' => $node['examples']])</div>
        @endif
        @include('theory.components.children', ['children' => $node['tail'] ?? []])
        @if(isset($node['detail']))@include('theory.components.node', ['node' => $node['detail']])@endif
    </div>
</article>
