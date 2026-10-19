@php($accent = \App\Support\TheoryComponents::accent($node['accent'] ?? 'rose'))
<article data-theory-component="mistake" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-item rounded-xl border {{ $accent['border'] }} bg-white"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    <div class="p-4">
        <div class="flex items-start gap-3 mb-3">
            @if(!empty($node['label']))<span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full {{ $accent['badge'] }} text-white text-[10px] font-bold mt-0.5">{{ $node['number'] ?? '' }}</span>@endif
            <div class="min-w-0 flex-1">
                @if(!empty($node['label']))<span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground block mb-1">{{ $node['label'] }}</span>@endif
                @if(\App\Support\TheoryComponents::present($node['title'] ?? null))<h3 class="text-sm font-semibold text-foreground">{{ \App\Support\TheoryComponents::body($node['title']) }}</h3>@endif
            </div>
        </div>
        @if(\App\Support\TheoryComponents::present($node['body_html'] ?? null))<div class="text-sm text-foreground/80 leading-relaxed mb-4 theory-point-fragment">{{ \App\Support\TheoryComponents::body($node['body_html']) }}</div>@endif
        <div class="space-y-2 pl-9">
            @if(\App\Support\TheoryComponents::present($node['wrong_en'] ?? null))@include('theory.components.correction', ['node' => ['kind' => 'correction', 'variant' => 'wrong', 'text' => $node['wrong_en'], 'lang' => 'en']])@endif
            @if(\App\Support\TheoryComponents::present($node['wrong_uk'] ?? null))<p lang="uk" class="theory-translation text-xs text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['wrong_uk']) }}</p>@endif
            @if(\App\Support\TheoryComponents::present($node['right_en'] ?? null))@include('theory.components.correction', ['node' => ['kind' => 'correction', 'variant' => 'right', 'text' => $node['right_en'], 'lang' => 'en']])@endif
            @if(\App\Support\TheoryComponents::present($node['right_uk'] ?? null))<p lang="uk" class="theory-translation text-xs text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['right_uk']) }}</p>@endif
            @if(\App\Support\TheoryComponents::present($node['hint_html'] ?? null))@include('theory.components.note', ['node' => ['kind' => 'note', 'variant' => 'hint', 'html' => $node['hint_html']]])@endif
        </div>
        @include('theory.components.children', ['children' => $node['examples'] ?? []])
        @include('theory.components.children', ['children' => $node['tail'] ?? []])
        @if(isset($node['detail']))@include('theory.components.node', ['node' => $node['detail']])@endif
    </div>
</article>
