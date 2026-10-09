<div data-theory-component="group" @if(!empty($node['id'])) id="{{ $node['id'] }}" @endif class="{{ \App\Support\TheoryComponents::grid($node['layout'] ?? null) }}"{{ \App\Support\TheoryComponents::attrs($node['attrs'] ?? []) }}>
    @include('theory.components.aliases')
    @include('theory.components.children', ['children' => $node['items'] ?? []])
</div>
