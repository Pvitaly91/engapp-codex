<section data-theory-component="section" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="theory-native-block scroll-mt-24"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    <div @unless($node['embedded'] ?? false) class="theory-section-card rounded-2xl border {{ match ($node['variant'] ?? 'plain') {
        'summary' => 'border-emerald-200/60 bg-gradient-to-br from-emerald-50/30 to-card',
        'mistakes' => 'border-rose-200/60 bg-gradient-to-br from-rose-50/50 to-card',
        default => 'border-border/60 bg-card',
    } }}" @endunless>
        @if(!($node['embedded'] ?? false) && \App\Support\TheoryComponents::present($node['title'] ?? null))
            <x-theory-native-header :title="$node['title']" :level="$node['level'] ?? null" :fallback="$node['fallback'] ?? '#'" />
        @endif
        <div class="theory-section-body p-5{{ ($node['layout'] ?? 'stack') === 'stack' ? ' space-y-4' : '' }}">
            @if(\App\Support\TheoryComponents::present($node['intro_html'] ?? null))
                <div class="text-sm text-muted-foreground mb-5 leading-relaxed">{{ \App\Support\TheoryComponents::body($node['intro_html']) }}</div>
            @endif
            @if(in_array($node['layout'] ?? 'stack', ['stack', 'plain'], true))
                @include('theory.components.children', ['children' => $node['items'] ?? []])
            @else
                <div class="{{ \App\Support\TheoryComponents::grid($node['layout']) }}">
                    @include('theory.components.children', ['children' => $node['items'] ?? []])
                </div>
            @endif
            @include('theory.components.children', ['children' => $node['tail'] ?? []])
            @if(($node['footer'] ?? false) && !($node['embedded'] ?? false) && isset($block))
                <x-text-block-tags :block="$block" />
                <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
            @endif
        </div>
    </div>
</section>
