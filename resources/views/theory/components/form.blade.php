@php($linked = !empty($node['url']))
@if($linked)<a data-theory-component="form" href="{{ $node['url'] }}" @else<div data-theory-component="form" @endif
    @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-item group relative rounded-xl border border-border/50 bg-gradient-to-br from-muted/20 to-transparent p-4 transition-all hover:border-brand-500 hover:shadow-sm{{ $linked ? ' block' : '' }}"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    <div class="pr-7">
        @include('theory.components.form-heading', ['heading' => $node, 'rowHeading' => false])
        @if(\App\Support\TheoryComponents::present($node['subtitle_html'] ?? null))<p class="text-sm text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['subtitle_html']) }}</p>@endif
    </div>
    @if(!empty($node['rows']))
        <div class="mt-4 space-y-4">
            @foreach($node['rows'] as $row)
                <div{{ \App\Support\TheoryComponents::attrs($row['attrs'] ?? []) }}>
                    @include('theory.components.form-heading', ['heading' => ['label' => $row['label'] ?? '', 'title' => $row['formula'] ?? ''], 'rowHeading' => true])
                    @if(isset($row['en']))@include('theory.components.example', ['node' => ['kind' => 'example', 'variant' => 'inline', 'en' => $row['en'], 'uk' => $row['uk'] ?? null, 'note_uk' => $row['note_uk'] ?? null]])@endif
                </div>
            @endforeach
        </div>
    @endif
    @if(!empty($node['rules']))
        <div class="mt-4 space-y-2.5">
            @foreach($node['rules'] as $row)
                <div class="theory-rule rounded-2xl border border-border/50 bg-background/80 px-3 py-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        @if(!empty($row['label']))<span class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-brand-700">{{ $row['label'] }}</span>@endif
                        @if(\App\Support\TheoryComponents::present($row['formula'] ?? null))<span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.12em] text-brand-700">{{ \App\Support\TheoryComponents::body($row['formula']) }}</span>@endif
                    </div>
                    @if(\App\Support\TheoryComponents::present($row['text_html'] ?? null))<div class="mt-2 text-xs leading-5 text-muted-foreground">{{ \App\Support\TheoryComponents::body($row['text_html']) }}</div>@endif
                    @if(\App\Support\TheoryComponents::present($row['example_html'] ?? null))<div class="theory-example mt-2 rounded-xl bg-muted/60 px-3 py-2 text-xs font-semibold leading-5 text-foreground">{{ \App\Support\TheoryComponents::body($row['example_html']) }}</div>@endif
                </div>
            @endforeach
        </div>
    @endif
    @if(\App\Support\TheoryComponents::present($node['body_html'] ?? null))<div class="text-sm text-muted-foreground theory-point-fragment">{{ \App\Support\TheoryComponents::body($node['body_html']) }}</div>@endif
    @if(!empty($node['examples']))<div class="space-y-2">@include('theory.components.children', ['children' => $node['examples']])</div>@endif
    @include('theory.components.children', ['children' => $node['tail'] ?? []])
    @if(!$linked && isset($node['detail']))@include('theory.components.node', ['node' => $node['detail']])@endif
    <div class="absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity">
        <svg class="h-4 w-4 text-brand-600/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </div>
@if($linked)</a>@else</div>@endif
