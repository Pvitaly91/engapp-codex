@php
    $design = $data['m44_native_design'] ?? null;
@endphp
@if($design === null)
    @include('engram.theory.blocks-v3.m44-static-fallback')
@else
@php
    $section = $data['author_section'];
@endphp
<section id="block-{{ $block->id }}" class="theory-native-block m44-native-design scroll-mt-24" data-m44-author-section="{{ $section['id'] }}" data-m44-native-layout="{{ $section['native_kind'] }}">
    <span id="{{ $section['id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card">
        <x-theory-native-header :title="$section['title']" :level="$block->level ?? null" />
        <div class="theory-section-body p-5 space-y-4">
            @if(isset($section['intro_uk']))<p lang="uk" class="text-sm leading-relaxed">{{ $section['intro_uk'] }}</p>@endif
            <div class="{{ $section['native_kind'] === 'forms-grid' ? 'grid gap-3 sm:grid-cols-2' : 'space-y-4' }}">
            @foreach($section['points'] as $pointIndex => $point)
                @php
                    $config = $design['points'][$pointIndex];
                    $pieceBlock = (object) ['id' => $block->id.'-point-'.$point['id'], 'level' => $block->level ?? null, 'uuid' => $block->uuid];
                    $pieceDetails = isset($pointSections[$pointIndex]) ? [0 => $pointSections[$pointIndex]] : [];
                    $description = \App\Support\M44NativeHtml::paragraphs($point['paragraphs_uk'], $section['native_kind'] === 'forms-grid');
                    if ($section['native_kind'] !== 'forms-grid' && isset($point['formula'])) {
                        $description = '<div class="font-semibold mb-2" data-m44-formula>'.\App\Support\M44NativeHtml::escape($point['formula']).'</div>'.$description;
                    }
                    $examples = \App\Support\M44NativeHtml::examples($point['examples']);
                @endphp
                <div id="{{ $point['id'] }}" class="{{ $section['native_kind'] === 'forms-grid' ? 'm44-form-point' : '' }}" data-m44-basic-point="{{ $point['id'] }}" data-m44-point-color="{{ $config['color'] }}">
                    @if($section['native_kind'] === 'forms-grid')
                        @include('engram.theory.blocks-v3.forms-grid', ['block' => $pieceBlock, 'data' => ['items' => [[
                            'label' => $point['title'], 'title' => $point['formula'] ?? '', 'native_description' => $description.$examples,
                        ]]], 'embeddedDetail' => true, 'm44NativePiece' => true, 'm44ReferencePiece' => true, 'lessonLinks' => [], 'pointSections' => $pieceDetails])
                    @elseif($section['native_kind'] === 'mistakes-grid')
                        @include('engram.theory.blocks-v3.m44-native-mistake', ['block' => $pieceBlock, 'point' => $point, 'pointSections' => $pieceDetails])
                    @elseif($section['native_kind'] === 'summary-list')
                        <h3 class="text-sm font-bold mb-3">{{ $point['title'] }}</h3>
                        @include('engram.theory.blocks-v3.summary-list', ['block' => $pieceBlock, 'data' => ['items' => array_map(fn ($p) => '<p lang="uk">'.e($p).'</p>', $point['paragraphs_uk'])], 'embeddedDetail' => true, 'm44NativePiece' => true, 'pointSections' => []])
                        {!! $examples !!}
                        @include('theory.partials.point-disclosure', ['index' => 0, 'pointSections' => $pieceDetails])
                    @else
                        @include('engram.theory.blocks-v3.usage-panels', ['block' => $pieceBlock, 'data' => ['sections' => [[
                            'label' => $point['title'], 'description' => $description, 'examples' => [], 'color' => $config['color'],
                        ]]], 'embeddedDetail' => true, 'm44NativePiece' => true, 'm44ReferencePiece' => true, 'm44ExamplesHtml' => $examples, 'm44PointNumber' => $pointIndex + 1, 'pointSections' => $pieceDetails])
                    @endif
                </div>
            @endforeach
            </div>
            @if(isset($section['table']))
                <section id="block-{{ $block->id }}-table" class="theory-native-block scroll-mt-24">
                    <div>
                        <div class="theory-section-body p-5">
                            @include('engram.theory.blocks-v3.m44-native-table', ['data' => ['title' => $section['title']], 'm44StructuredTable' => $section['table']])
                        </div>
                    </div>
                </section>
            @endif
            @foreach($section['notes_uk'] ?? [] as $note)<p lang="uk" class="theory-note text-sm rounded-lg p-3">{{ $note }}</p>@endforeach
            {!! \App\Support\M44NativeHtml::examples($section['note_examples'] ?? []) !!}
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
@endif
