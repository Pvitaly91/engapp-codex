<div data-theory-native-extension>
    <details class="theory-section-details" data-theory-details>
        <summary class="theory-section-toggle" id="{{ $group['id'] }}-more">
            <span class="theory-section-toggle-closed">{{ __('theory_blocks.section.more') }}</span>
            <span class="theory-section-toggle-open">{{ __('theory_blocks.section.less') }}</span>
            <span class="sr-only"> — {{ $group['title'] }}</span>
        </summary>
        <div class="theory-section-detail-body text-sm leading-relaxed space-y-4">
            @foreach($group['sources'] as $sourcePoint)
                @php($sourceIndex = array_search($sourcePoint['id'], array_column($section['points'], 'id'), true))
                <article @if($sourcePoint['id'] !== $group['id']) id="{{ $sourcePoint['id'] }}" @endif data-m44-basic-point="{{ $sourcePoint['id'] }}" class="space-y-3">
                    @if($sourcePoint['id'] !== $group['id'])<span id="block-{{ $block->id }}-point-{{ $sourcePoint['id'] }}" aria-hidden="true"></span>@endif
                    <h4 class="font-bold">{{ $sourcePoint['title'] }}</h4>
                    @if(isset($sourcePoint['formula']) && $group['formula_from'] !== $sourcePoint['id'])<p class="font-semibold">{{ $sourcePoint['formula'] }}</p>@endif
                    {!! \App\Support\M44NativeHtml::paragraphs($sourcePoint['paragraphs_uk']) !!}
                    @if(isset($sourcePoint['wrong_en']))
                        <p lang="en" class="line-through">{{ $sourcePoint['wrong_en'] }}</p>
                        @if(isset($sourcePoint['wrong_uk']))<p lang="uk">{{ $sourcePoint['wrong_uk'] }}</p>@endif
                        <p lang="en">{{ $sourcePoint['right_en'] }}</p><p lang="uk">{{ $sourcePoint['right_uk'] }}</p>
                    @endif
                    @foreach($sourcePoint['examples'] as $exampleIndex => $example)
                        @unless(isset($group['selected'][$sourcePoint['id'].':'.$exampleIndex]))
                            {!! \App\Support\M44NativeHtml::examples([$example]) !!}
                        @endunless
                    @endforeach
                    @if(isset($sourcePoint['detail']))
                        @if(isset($pointSections[$sourceIndex]))<span id="{{ $pointSections[$sourceIndex]->detailsId() }}" aria-hidden="true"></span>@endif
                        <div id="block-{{ $sourcePoint['detail']['id'] }}" class="space-y-3">
                            <h4 class="font-bold">{{ $sourcePoint['detail']['title'] }}</h4>
                            {!! \App\Support\M44NativeHtml::paragraphs($sourcePoint['detail']['paragraphs_uk']) !!}
                            {!! \App\Support\M44NativeHtml::examples($sourcePoint['detail']['examples']) !!}
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </details>
</div>
