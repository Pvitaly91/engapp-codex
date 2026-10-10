{{-- One content entry, without an additional visual wrapper. The caller owns
     the theory boundary and validates source data before preparing this model. --}}
@switch($content['kind'] ?? 'text-fallback')
    @case('node')
        @include('theory.components.node', ['node' => $content['node']])
        @break

    @case('legacy-box')
        <div id="{{ $content['id'] ?? 'block-'.$block->id }}" class="theory-content-block">
            <x-theory-rich-box :block="$block" :presentation="$content['presentation'] ?? $presentation ?? \App\Support\TheoryPresentation::html($block, canonical: true)" />
        </div>
        @break

    @case('anchor')
        <div id="{{ $content['id'] ?? 'block-'.$block->id }}" class="theory-subtitle-anchor" aria-hidden="true"></div>
        @break

    @default
        <article id="{{ $content['id'] ?? 'block-'.$block->id }}" class="theory-section-card theory-section-body" data-theory-render-fallback="unknown-format">
            @if(!empty($content['heading'] ?? $block->heading))
                <h2 class="theory-section-title">{{ $content['heading'] ?? $block->heading }}</h2>
            @endif
            {{-- Unknown formats remain escaped text, never executable views. --}}
            <div class="theory-fallback-content">{{ $content['body'] ?? $block->body }}</div>
        </article>
@endswitch
