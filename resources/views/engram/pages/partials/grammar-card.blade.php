@include('engram.pages.static.partials.grammar-card-styles')

<section class="grammar-card" lang="{{ $locale }}">
    <header>
        <h2 class="gw-title">{{ $page->title }}</h2>
        @if(! empty($subtitleBlock?->body))
            <p class="gw-sub">{!! $subtitleBlock->body !!}</p>
        @endif
    </header>

    <div class="gw-grid">
        @foreach(['left', 'right'] as $columnKey)
            <div class="gw-col">
                @foreach(($columns[$columnKey] ?? collect()) as $block)
                    <div class="gw-box{{ $block->css_class ? ' ' . $block->css_class : '' }}">
                        @if(! empty($block->heading))
                            <h3>{{ $block->heading }}</h3>
                        @endif
                        {!! $block->body !!}
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</section>
