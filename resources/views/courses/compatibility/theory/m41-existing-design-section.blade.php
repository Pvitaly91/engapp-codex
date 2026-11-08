@php
    $design = $data['m41_existing_design'] ?? null;
@endphp
@if($design === null)
    {{-- A rejected detail resolver hands back raw accepted data, not ephemeral mapping. --}}
    @include('engram.theory.blocks-v3.m41-author-section')
@else
<section id="block-{{ $block->id }}" class="theory-native-block m41-existing-design scroll-mt-24" data-m41-author-section="{{ $design['id'] }}" data-m41-native-layout="{{ $design['layout'] }}">
    <span id="{{ $design['id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card">
        <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" />
        <div class="theory-section-body p-5 space-y-4">
            @if($design['forms'] !== null)
                <div class="m41-form-columns text-xs font-bold uppercase tracking-wider text-muted-foreground" data-m41-native-component="forms-grid">
                    @foreach($design['forms']['columns'] as $columnIndex => $column)
                        <span data-m41-form-column="{{ $columnIndex }}">{{ $column }}</span>
                    @endforeach
                </div>
                @foreach($design['forms']['rows'] as $row)
                    @php
                        $formBlock = (object) ['id' => $block->id.'-form-row-'.$row['row_index'], 'level' => $block->level ?? null, 'uuid' => $block->uuid];
                        $formItems = array_map(fn ($cell) => ['title' => $cell['en'], 'subtitle' => $cell['uk'], 'm41_form_cell' => $cell['source_cell']], $row['cells']);
                    @endphp
                    <div class="m41-form-row" data-m41-form-row="{{ $row['row_index'] }}">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-brand-700 mb-3" data-m41-form-row-label="{{ $row['row_index'] }}">{{ $row['label'] }}</h3>
                        @include('engram.theory.blocks-v3.forms-grid', ['block' => $formBlock, 'data' => ['items' => $formItems], 'embeddedDetail' => true, 'm41NativePiece' => true, 'lessonLinks' => [], 'pointSections' => []])
                    </div>
                @endforeach
            @endif
            @foreach($design['points'] as $pointConfig)
                @php
                    $pointIndex = $pointConfig['point_index'];
                    $point = $data['author_section']['points'][$pointIndex];
                    $native = $data['sections'][$pointIndex];
                    $pieceBlock = (object) ['id' => $block->id.'-point-'.$point['id'], 'level' => $block->level ?? null, 'uuid' => $block->uuid];
                    $pieceDetails = isset($pointSections[$pointIndex]) ? [0 => $pointSections[$pointIndex]] : [];
                @endphp
                <div id="{{ $point['id'] }}" @if($pointConfig['component'] === 'forms-note') class="m41-form-note" @endif data-m41-basic-point="{{ $point['id'] }}" data-m41-author-point="{{ $point['id'] }}" data-m41-native-component="{{ $pointConfig['component'] }}" data-m41-point-color="{{ $pointConfig['color'] }}">
                    @if($pointConfig['component'] === 'usage-panel')
                        @php
                            $usagePoint = array_merge($native, ['color' => $pointConfig['color']]);
                        @endphp
                        @include('engram.theory.blocks-v3.usage-panels', ['block' => $pieceBlock, 'data' => ['sections' => [$usagePoint]], 'embeddedDetail' => true, 'm41NativePiece' => true, 'm41PointNumber' => $pointIndex + 1, 'pointSections' => $pieceDetails])
                    @elseif($pointConfig['component'] === 'forms-note')
                        @include('engram.theory.blocks-v3.m41-native-point-heading')
                        @include('engram.theory.blocks-v3.forms-grid', ['block' => $pieceBlock, 'data' => ['items' => [['title' => '', 'm41_description' => $native['description']]]], 'embeddedDetail' => true, 'm41NativePiece' => true, 'lessonLinks' => [], 'pointSections' => $pieceDetails])
                    @elseif($pointConfig['component'] === 'comparison-table')
                        <div class="theory-item m41-comparison-point rounded-xl border p-4 bg-{{ $pointConfig['color'] }}-50">
                            @include('engram.theory.blocks-v3.m41-native-point-heading')
                            @include('engram.theory.blocks-v3.comparison-table', ['block' => $pieceBlock, 'data' => ['title' => $point['title'], 'intro' => $native['description'], 'rows' => $native['examples']], 'embeddedDetail' => true, 'm41NativePiece' => true, 'pointSections' => []])
                            @include('theory.partials.point-disclosure', ['index' => 0, 'pointSections' => $pieceDetails])
                        </div>
                    @elseif($pointConfig['component'] === 'mistakes-grid')
                        @include('engram.theory.blocks-v3.mistakes-grid', ['block' => $pieceBlock, 'data' => [], 'm41NativeMistake' => true, 'm41NativePiece' => true, 'embeddedDetail' => true, 'pointSections' => $pieceDetails])
                    @elseif($pointConfig['component'] === 'summary-list')
                        <div class="m41-summary-point">
                            @include('engram.theory.blocks-v3.m41-native-point-heading')
                            @php
                                $summaryItems = array_map(fn ($paragraph) => '<p>'.e($paragraph).'</p>', $point['paragraphs_uk']);
                            @endphp
                            @include('engram.theory.blocks-v3.summary-list', ['block' => $pieceBlock, 'data' => ['items' => $summaryItems], 'embeddedDetail' => true, 'm41NativePiece' => true, 'pointSections' => []])
                            @include('theory.partials.point-disclosure', ['index' => 0, 'pointSections' => $pieceDetails])
                        </div>
                    @endif
                </div>
            @endforeach
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>
@endif
