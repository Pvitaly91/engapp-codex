@include('engram.pages.static.partials.grammar-card-styles')

@php
    $blocks = $page->blocks ?? collect();
    $locale = optional($blocks->first())->locale ?? app()->getLocale();
    $header = $blocks->firstWhere('type', 'header');
    $headerTitle = data_get($header, 'content.title', $page->title);
    $headerSubtitle = data_get($header, 'content.subtitle');
    $headerChips = $blocks->where('area', 'header')->reject(fn($block) => $block->is($header));
    $leftBlocks = $blocks->where('area', 'left');
    $rightBlocks = $blocks->where('area', 'right');
    $fullBlocks = $blocks->where('area', 'full');
@endphp

<section class="grammar-card" lang="{{ $locale }}">
    <header>
        <h2 class="gw-title">{{ $headerTitle }}</h2>
        @if(! empty($headerSubtitle))
            <p class="gw-sub">{!! $headerSubtitle !!}</p>
        @endif

        @foreach($headerChips as $chipBlock)
            @include('engram.pages.templates.partials.block', ['block' => $chipBlock])
        @endforeach
    </header>

    <div class="gw-grid">
        <div class="gw-col">
            @foreach($leftBlocks as $block)
                @include('engram.pages.templates.partials.block', ['block' => $block])
            @endforeach
        </div>

        <div class="gw-col">
            @foreach($rightBlocks as $block)
                @include('engram.pages.templates.partials.block', ['block' => $block])
            @endforeach
        </div>
    </div>

    @foreach($fullBlocks as $block)
        @include('engram.pages.templates.partials.block', ['block' => $block])
    @endforeach
</section>
