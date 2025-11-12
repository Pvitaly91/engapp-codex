@include('engram.pages.partials.grammar-card-styles')

<section class="grammar-card" lang="{{ $locale }}">
    <header>
        <h2 class="gw-title">{{ $page->title }}</h2>
        @if(! empty($subtitleBlock?->body))
            <p class="gw-sub">{!! $subtitleBlock->body !!}</p>
        @endif
    </header>

    <div class="flex flex-col lg:flex-row gap-6">
        @php($imagePath = $page->getImagePath())
        
        @if($imagePath)
            <div class="lg:w-1/4 flex-shrink-0">
                <img src="{{ $imagePath }}" alt="{{ $page->title }}" class="w-full h-auto rounded-xl shadow-soft">
            </div>
        @endif
        
        <div class="gw-grid {{ $imagePath ? 'lg:w-3/4' : 'w-full' }}">
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
    </div>
</section>
