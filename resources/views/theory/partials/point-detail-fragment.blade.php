{{-- Only the finite, hash-bound M26 presentation package can supply fragments. --}}
<section id="{{ $fragment['id'] }}" class="theory-point-fragment">
    @php($value = $fragment['value'])
    @if(in_array($fragment['type'], ['m27-author-html', 'm28-author-html'], true))
        <div class="text-sm text-muted-foreground leading-relaxed">{!! $value !!}</div>
    @elseif($fragment['type'] === 'intro')
        <p class="text-sm text-muted-foreground leading-relaxed">{!! $value !!}</p>
    @elseif($fragment['type'] === 'warning')
        <p class="theory-note text-sm rounded-lg p-3">{!! $value !!}</p>
    @elseif($fragment['type'] === 'summary-list')
        <p class="text-sm leading-relaxed">{!! $value !!}</p>
    @elseif($fragment['type'] === 'forms-grid')
        <div class="space-y-2">
            <p class="text-xs font-bold text-muted-foreground">{{ $value['label'] }}</p>
            <h4 class="text-sm font-bold">{{ $value['title'] }}</h4>
            <p class="text-sm leading-relaxed">{{ \App\Support\TheoryInlineHtml::render($value['subtitle']) }}</p>
        </div>
    @elseif($fragment['type'] === 'comparison-table')
        <div class="theory-example">
            <p>{{ \App\Support\TheoryInlineHtml::render($value['en']) }}</p>
            <p class="theory-translation">{{ \App\Support\TheoryInlineHtml::render($value['ua']) }}</p>
        </div>
        <p class="text-sm leading-relaxed">{!! $value['note'] !!}</p>
    @elseif(in_array($fragment['type'], ['usage-panels', 'supplement'], true))
        @if(isset($value['label']))
            <h4 class="text-xs font-bold text-muted-foreground">{{ $value['label'] }}</h4>
        @endif
        <p class="text-sm leading-relaxed">@if($fragment['type'] === 'supplement'){{ $value['description'] }}@else{!! $value['description'] !!}@endif</p>
        @foreach($value['examples'] as $example)
            <div class="theory-example">
                <p>{{ \App\Support\TheoryInlineHtml::render($example['en']) }}</p>
                <p class="theory-translation">{{ \App\Support\TheoryInlineHtml::render($example['ua']) }}</p>
            </div>
        @endforeach
        @if(!empty($value['note']))<p class="theory-note text-sm rounded-lg p-3">{!! $value['note'] !!}</p>@endif
    @endif
</section>
