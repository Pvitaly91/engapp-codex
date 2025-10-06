@php
    $content = $block->content ?? [];
    $title = $block->label ?? ($content['title'] ?? null);
    $boxClasses = collect(['gw-box']);
    if (! empty($content['scroll'])) {
        $boxClasses->push('gw-box--scroll');
    }
@endphp

@if($block->area === 'header' && $block->type === 'chips')
    <div class="gw-chips">
        @foreach($content['chips'] ?? [] as $chip)
            <span class="gw-chip">{{ $chip }}</span>
        @endforeach
    </div>
@elseif($block->type === 'html-snippet')
    {!! $content['html'] ?? '' !!}
@else
    <div class="{{ $boxClasses->implode(' ') }}">
        @if($title)
            <h3>{{ $title }}</h3>
        @endif

        @switch($block->type)
            @case('list')
                @if(! empty($content['items']))
                    <ul class="gw-list">
                        @foreach($content['items'] as $item)
                            <li>{!! $item !!}</li>
                        @endforeach
                    </ul>
                @endif

                @if(! empty($content['examples']))
                    @foreach($content['examples'] as $example)
                        <div class="gw-ex">
                            @if(! empty($example['en']))
                                <div class="gw-en">{!! $example['en'] !!}</div>
                            @endif
                            @if(! empty($example['ua']))
                                <div class="gw-ua">{!! $example['ua'] !!}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
                @break

            @case('formula')
                @foreach($content['variants'] ?? [] as $variant)
                    @if(! empty($variant['label']))
                        <div class="gw-code-badge">{{ $variant['label'] }}</div>
                    @endif
                    @if(! empty($variant['text']))
                        <pre class="gw-formula">{!! $variant['text'] !!}</pre>
                    @endif
                @endforeach
                @break

            @case('chips')
                <div class="gw-chips">
                    @foreach($content['chips'] ?? [] as $chip)
                        <span class="gw-chip">{{ $chip }}</span>
                    @endforeach
                </div>

                @if(! empty($content['examples']))
                    @foreach($content['examples'] as $example)
                        <div class="gw-ex">
                            @if(! empty($example['en']))
                                <div class="gw-en">{!! $example['en'] !!}</div>
                            @endif
                            @if(! empty($example['ua']))
                                <div class="gw-ua">{!! $example['ua'] !!}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
                @break

            @case('table')
                <table class="gw-table" aria-label="{{ $title }}">
                    @if(! empty($content['headings']))
                        <thead>
                            <tr>
                                @foreach($content['headings'] as $heading)
                                    <th>{!! $heading !!}</th>
                                @endforeach
                            </tr>
                        </thead>
                    @endif
                    <tbody>
                        @foreach($content['rows'] ?? [] as $row)
                            <tr>
                                @foreach($row as $cell)
                                    <td>{!! $cell !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('hint')
                <div class="gw-hint">
                    @if(! empty($content['emoji']))
                        <div class="gw-emoji">{{ $content['emoji'] }}</div>
                    @endif
                    <div>
                        @foreach($content['body'] ?? [] as $paragraph)
                            <p>{!! $paragraph !!}</p>
                        @endforeach

                        @if(! empty($content['examples']))
                            @foreach($content['examples'] as $example)
                                <div class="gw-ex" style="margin-top:6px">
                                    @if(! empty($example['en']))
                                        <div class="gw-en">{!! $example['en'] !!}</div>
                                    @endif
                                    @if(! empty($example['ua']))
                                        <div class="gw-ua">{!! $example['ua'] !!}</div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                @break

            @case('examples')
                @foreach($content['examples'] ?? [] as $example)
                    <div class="gw-ex">
                        @if(! empty($example['en']))
                            <div class="gw-en">{!! $example['en'] !!}</div>
                        @endif
                        @if(! empty($example['ua']))
                            <div class="gw-ua">{!! $example['ua'] !!}</div>
                        @endif
                    </div>
                @endforeach
                @break

            @default
                @foreach($content['body'] ?? [] as $paragraph)
                    <p>{!! $paragraph !!}</p>
                @endforeach

                @if(! empty($content['examples']))
                    @foreach($content['examples'] as $example)
                        <div class="gw-ex">
                            @if(! empty($example['en']))
                                <div class="gw-en">{!! $example['en'] !!}</div>
                            @endif
                            @if(! empty($example['ua']))
                                <div class="gw-ua">{!! $example['ua'] !!}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
        @endswitch
    </div>
@endif
