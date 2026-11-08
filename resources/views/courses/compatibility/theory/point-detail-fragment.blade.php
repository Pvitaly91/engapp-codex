{{-- Only the finite, hash-bound M26 presentation package can supply fragments. --}}
<section id="{{ $fragment['id'] }}" class="theory-point-fragment">
    @php($value = $fragment['value'])
    @if($fragment['type'] === 'm44-author-html')
        <div class="m44-native-detail text-sm leading-relaxed space-y-3">{!! \App\Support\M44NativeHtml::decorateStoredExamples($value) !!}</div>
    @elseif($fragment['type'] === 'm43-author-html')
        <div class="m43-native-detail text-sm leading-relaxed space-y-3">{!! \App\Support\M43NativeHtml::decorateStoredExamples($value) !!}</div>
    @else
    @if($fragment['type'] === 'm41-author-html' && isset($fragment['m41_existing_design_detail']))
        @include('engram.theory.blocks-v3.m41-native-detail', ['detail' => $fragment['m41_existing_design_detail']])
    @elseif(in_array($fragment['type'], ['m27-author-html', 'm28-author-html', 'm29-author-html', 'm30-author-html', 'm31-author-html', 'm32-author-html', 'm33-author-html', 'm34-author-html', 'm35-author-html', 'm36-author-html', 'm37-author-html', 'm38-author-html', 'm39-author-html', 'm41-author-html'], true))
        <div class="text-sm text-muted-foreground leading-relaxed m42-rich-fragment">{!! \App\Support\M42NativeDesignPackage::richFragment($value, $m42Design ?? null, '/details/'.$fragment['id']) !!}</div>
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
    @endif
</section>
