@if(!empty($detail))
    @if($guarded ?? true)
        <div data-theory-native-extension>
            <details class="theory-section-details" data-theory-details data-m45-detail="{{ $detail['id'] }}">
                <summary id="{{ $detail['id'] }}-toggle" class="theory-section-toggle">
                    <span class="theory-section-toggle-closed">{{ __('theory_blocks.section.more') }}</span>
                    <span class="theory-section-toggle-open">{{ __('theory_blocks.section.less') }}</span>
                    <span class="sr-only"> — {{ $detail['title'] }}</span>
                </summary>
                <div id="{{ $detail['id'] }}" class="theory-section-detail-body text-sm leading-relaxed space-y-3">
                    <h4 class="font-bold">{{ $detail['title'] }}</h4>
                    @foreach($detail['paragraphs_uk'] as $paragraph)<p lang="uk">{{ $paragraph }}</p>@endforeach
                    {!! \App\Support\M43NativeHtml::examples($detail['examples'] ?? []) !!}
                </div>
            </details>
        </div>
    @else
        <div id="{{ $detail['id'] }}" class="text-sm leading-relaxed space-y-3 mt-4">
            <h4 class="font-bold">{{ $detail['title'] }}</h4>
            @foreach($detail['paragraphs_uk'] as $paragraph)<p lang="uk">{{ $paragraph }}</p>@endforeach
            {!! \App\Support\M43NativeHtml::examples($detail['examples'] ?? []) !!}
        </div>
    @endif
@endif
