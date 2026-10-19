<section id="block-{{ $block->id }}" class="theory-native-block m41-author-section scroll-mt-24" data-m41-author-section="{{ $data['author_section']['id'] }}">
    <span id="{{ $data['author_section']['id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card">
        <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" />
        <div class="theory-section-body p-5 space-y-4">
            @if(!empty($data['intro']))
                <div class="text-sm leading-relaxed" data-m41-form-table>{!! $data['intro'] !!}</div>
            @endif
            @foreach($data['sections'] as $index => $section)
                @php($authorPoint = $data['author_section']['points'][$index])
                <article id="{{ $authorPoint['id'] }}" class="theory-item rounded-xl p-4 space-y-3 {{ $authorPoint['kind'] === 'warning' ? 'bg-amber-50/30' : 'bg-muted/50' }}" data-m41-basic-point="{{ $authorPoint['id'] }}">
                    <h3 class="m41-point-title">{{ $section['label'] }}</h3>
                    <div class="m41-basic text-sm leading-relaxed">{!! $section['description'] !!}</div>
                    @foreach($section['examples'] as $example)
                        <div class="theory-example">
                            <p lang="en">{{ $example['en'] }}</p>
                            <p lang="uk" class="theory-translation">{{ $example['ua'] }}</p>
                        </div>
                    @endforeach
                    @if(!empty($section['note']))
                        {{-- Complete stored author detail remains readable if the finite projection fails. --}}
                        <div class="theory-note text-sm leading-relaxed">{!! $section['note'] !!}</div>
                    @endif
                    @include('theory.partials.point-disclosure', ['index' => $index])
                </article>
            @endforeach
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
