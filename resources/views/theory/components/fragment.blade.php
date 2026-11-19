@php
    $fragmentTag = in_array($node['tag'] ?? null, ['div', 'section'], true) ? $node['tag'] : 'div';
    $titleClass = ($node['title_hidden'] ?? false) ? 'sr-only' : match ($node['title_role'] ?? null) {
        'label' => 'text-xs font-bold text-muted-foreground',
        'subheading' => 'text-sm font-bold',
        default => 'font-bold',
    };
@endphp
<{{ $fragmentTag }} data-theory-component="fragment" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-point-fragment{{ ($node['typography'] ?? null) === 'inherit' ? '' : ' text-sm leading-relaxed' }}"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    @if(($node['content_layout'] ?? null) === 'compact-stack')<div class="space-y-2">@endif
    @if(!empty($node['label']))<p class="text-xs font-bold text-muted-foreground">{{ $node['label'] }}</p>@endif
    @if(\App\Support\TheoryComponents::present($node['title'] ?? null))<h4 class="{{ $titleClass }}">{{ $node['title'] }}</h4>@endif
    @if(\App\Support\TheoryComponents::present($node['body_html'] ?? null))
        @if(($node['body_role'] ?? null) === 'paragraph')
            @include('theory.components.paragraph', ['node' => ['html' => $node['body_html'], 'tone' => $node['body_tone'] ?? null]])
        @else
            {{ \App\Support\TheoryComponents::body($node['body_html']) }}
        @endif
    @endif
    @if(!empty($node['examples']))<div class="space-y-2">@include('theory.components.children', ['children' => $node['examples']])</div>@endif
    @include('theory.components.children', ['children' => $node['items'] ?? []])
    @include('theory.components.children', ['children' => $node['tail'] ?? []])
    @if(isset($node['detail']))@include('theory.components.node', ['node' => $node['detail']])@endif
    @if(($node['content_layout'] ?? null) === 'compact-stack')</div>@endif
</{{ $fragmentTag }}>
