@props(['block', 'richContent' => null, 'presentation' => null])

@if($presentation !== null)
    <article class="theory-html-article{{ $presentation['structured'] ? ' theory-html-article--structured' : ' theory-section-card theory-section-body' }}">
        @if(!empty($block->heading))
            <h2 class="theory-section-title theory-box-heading">{{ $block->heading }}</h2>
        @endif
        <div class="theory-html-content{{ $presentation['structured'] ? ' theory-rich-content' : '' }}">
            {!! $presentation['html'] !!}
        </div>
    </article>
@elseif($richContent !== null)
    <article class="theory-rich-article">
        @if(!empty($block->heading))
            <h3 class="font-display text-lg font-extrabold leading-tight">{{ $block->heading }}</h3>
        @endif
        <div class="theory-rich-content prose prose-sm max-w-none">
            {!! $richContent !!}
        </div>
    </article>
@else
    <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
        @if(!empty($block->heading))
            <h3 class="font-display text-lg font-extrabold leading-tight">{{ $block->heading }}</h3>
        @endif
        <div class="prose prose-sm mt-4 max-w-none leading-7" style="color: var(--muted);">
            {!! $block->body !!}
        </div>
    </article>
@endif
