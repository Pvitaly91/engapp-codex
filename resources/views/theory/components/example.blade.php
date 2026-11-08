@if(($node['variant'] ?? 'box') === 'table-cell')
    <p data-theory-component="example" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif lang="en"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>{{ \App\Support\TheoryComponents::body($node['en'] ?? '') }}</p>
    @if(\App\Support\TheoryComponents::present($node['uk'] ?? null))<p lang="uk" class="theory-translation">{{ \App\Support\TheoryComponents::body($node['uk']) }}</p>@endif
    @if(\App\Support\TheoryComponents::present($node['note_uk'] ?? null))<p lang="uk" class="text-xs text-muted-foreground mt-2">{{ \App\Support\TheoryComponents::body($node['note_uk']) }}</p>@endif
@elseif(($node['variant'] ?? 'box') === 'short-answer')
    <p data-theory-component="example" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif><span lang="en" class="font-semibold">{{ \App\Support\TheoryComponents::body($node['en'] ?? '') }}</span> — <span lang="uk" class="text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['uk'] ?? '') }}</span></p>
@elseif(($node['variant'] ?? 'box') === 'code')
    <code data-theory-component="example" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif lang="en" class="theory-example font-mono text-sm font-semibold text-foreground">{{ \App\Support\TheoryComponents::body($node['en'] ?? '') }}</code>
@elseif(($node['variant'] ?? 'box') === 'plain')
    <div data-theory-component="example" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-example"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
        <p lang="en">{{ \App\Support\TheoryComponents::body($node['en'] ?? '') }}</p>
        @if(\App\Support\TheoryComponents::present($node['uk'] ?? null))<p lang="uk" class="theory-translation">{{ \App\Support\TheoryComponents::body($node['uk']) }}</p>@endif
        @if(\App\Support\TheoryComponents::present($node['note_uk'] ?? null))<p lang="uk" class="text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['note_uk']) }}</p>@endif
    </div>
@elseif(($node['variant'] ?? 'box') === 'inline')
    <div data-theory-component="example" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="text-sm"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
        <p lang="en" class="text-sm text-foreground">{{ \App\Support\TheoryComponents::body($node['en'] ?? '') }}</p>
        @if(\App\Support\TheoryComponents::present($node['uk'] ?? null))<p lang="uk" class="text-sm text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['uk']) }}</p>@endif
        @if(\App\Support\TheoryComponents::present($node['note_uk'] ?? null))<p lang="uk" class="text-sm text-muted-foreground">{{ \App\Support\TheoryComponents::body($node['note_uk']) }}</p>@endif
    </div>
@else
    <div data-theory-component="example" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-example flex items-start gap-3 rounded-lg bg-white/60 border border-white/80 p-3"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
        <span class="flex-shrink-0 text-lg">💬</span>
        <div class="min-w-0 flex-1">
            <p lang="en" class="font-mono text-xs font-medium text-foreground">{{ \App\Support\TheoryComponents::body($node['en'] ?? '') }}</p>
            @if(\App\Support\TheoryComponents::present($node['uk'] ?? null))<p lang="uk" class="theory-translation text-xs text-muted-foreground mt-0.5 italic">{{ \App\Support\TheoryComponents::body($node['uk']) }}</p>@endif
            @if(\App\Support\TheoryComponents::present($node['note_uk'] ?? null))<p lang="uk" class="text-xs text-muted-foreground mt-0.5">{{ \App\Support\TheoryComponents::body($node['note_uk']) }}</p>@endif
        </div>
    </div>
@endif
