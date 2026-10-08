@php
    $pointSections = $pointSections ?? [];
@endphp
<section id="block-{{ $block->id }}" class="theory-native-block m44-native-design scroll-mt-24" data-m44-author-section="{{ $section['id'] }}" data-m44-native-layout="{{ $section['native_kind'] }}" data-m44-simplified>
    <span id="{{ $section['id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card">
        <x-theory-native-header :title="$section['title']" :level="$block->level ?? null" />
        <div class="theory-section-body p-5 space-y-4">
            @if(isset($section['intro_uk']))<p lang="uk" class="text-sm leading-relaxed">{{ $section['intro_uk'] }}</p>@endif
            <div class="{{ $section['native_kind'] === 'forms-grid' ? 'grid gap-3 sm:grid-cols-2' : 'space-y-4' }}">
                @foreach($compact as $groupIndex => $group)
                    @php
                        $pointIndex = $group['source_index'];
                        $config = $design['points'][$pointIndex];
                        $point = $group['sources'][0];
                        $pieceBlock = (object) ['id' => $block->id.'-point-'.$group['id'], 'level' => $block->level ?? null, 'uuid' => $block->uuid];
                        $pieceDetails = !$group['disclosure'] && isset($pointSections[$pointIndex]) ? [0 => $pointSections[$pointIndex]] : [];
                        $title = $group['disclosure'] ? $group['title'] : $point['title'];
                        $formula = $group['disclosure'] ? $group['formula'] : ($point['formula'] ?? null);
                        $paragraphs = $group['disclosure'] ? $group['basic_uk'] : $point['paragraphs_uk'];
                        $description = \App\Support\M44NativeHtml::paragraphs($paragraphs, $section['native_kind'] === 'forms-grid');
                        if ($section['native_kind'] !== 'forms-grid' && $formula !== null) {
                            $description = '<div class="font-semibold mb-2" data-m44-formula>'.e($formula).'</div>'.$description;
                        }
                        $examples = \App\Support\M44NativeHtml::examples($group['disclosure'] ? $group['examples'] : $point['examples']);
                        if ($section['native_kind'] === 'forms-grid') {
                            $examples = view('engram.theory.blocks-v3.m44-compact-form-examples', ['formExamples' => $group['disclosure'] ? $group['examples'] : $point['examples']])->render();
                        }
                        $detailHtml = $group['disclosure'] ? view('engram.theory.blocks-v3.m44-compact-detail', compact('group', 'section', 'pointSections', 'block'))->render() : '';
                    @endphp
                    <div id="{{ $group['id'] }}" class="{{ $section['native_kind'] === 'forms-grid' ? 'm44-form-point' : '' }}" data-m44-compact-group="{{ $group['id'] }}" @unless($group['disclosure']) data-m44-basic-point="{{ $point['id'] }}" @endunless data-m44-point-color="{{ $config['color'] }}">
                        @if($section['native_kind'] === 'forms-grid')
                            @include('engram.theory.blocks-v3.forms-grid', ['block' => $pieceBlock, 'data' => ['items' => [[
                                'label' => $title, 'title' => $formula ?? '', 'native_description' => $description.$examples.$detailHtml,
                            ]]], 'embeddedDetail' => true, 'm44NativePiece' => true, 'm44ReferencePiece' => true, 'lessonLinks' => [], 'pointSections' => $pieceDetails])
                        @elseif($section['native_kind'] === 'mistakes-grid' && !$group['disclosure'])
                            @include('engram.theory.blocks-v3.m44-native-mistake', ['block' => $pieceBlock, 'point' => $point, 'pointSections' => $pieceDetails])
                        @elseif($section['native_kind'] === 'summary-list')
                            <h3 class="text-sm font-bold mb-3">{{ $title }}</h3>
                            @include('engram.theory.blocks-v3.summary-list', ['block' => $pieceBlock, 'data' => ['items' => array_map(fn ($p) => '<p lang="uk">'.e($p).'</p>', $paragraphs)], 'embeddedDetail' => true, 'm44NativePiece' => true, 'pointSections' => []])
                            {!! $examples.$detailHtml !!}
                            @include('theory.partials.point-disclosure', ['index' => 0, 'pointSections' => $pieceDetails])
                        @else
                            @include('engram.theory.blocks-v3.usage-panels', ['block' => $pieceBlock, 'data' => ['sections' => [[
                                'label' => $title, 'description' => $description, 'examples' => [], 'color' => $config['color'],
                            ]]], 'embeddedDetail' => true, 'm44NativePiece' => true, 'm44ReferencePiece' => true, 'm44ExamplesHtml' => $examples.$detailHtml, 'm44PointNumber' => $groupIndex + 1, 'pointSections' => $pieceDetails])
                        @endif
                    </div>
                @endforeach
            </div>
            @if(isset($section['table']))
                <span id="block-{{ $block->id }}-table" aria-hidden="true"></span>
                @include('engram.theory.blocks-v3.m44-native-table', ['data' => ['title' => $section['title']], 'm44StructuredTable' => $section['table'], 'm44CompactTable' => true])
            @endif
            @foreach($section['notes_uk'] ?? [] as $note)<p lang="uk" class="theory-note text-sm rounded-lg p-3">{{ $note }}</p>@endforeach
            {!! \App\Support\M44NativeHtml::examples($section['note_examples'] ?? []) !!}
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
