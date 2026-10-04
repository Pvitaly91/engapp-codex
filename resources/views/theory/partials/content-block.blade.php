@php
    $nativeView = \App\Support\TheoryPresentation::nativeView($block->type);
    $decodedBody = json_decode($block->body ?? '', true);
    $blockAnchor = 'block-' . $block->id;
@endphp

@if($nativeView)
    {{-- Native views own the block anchor. Never repeat it on a parent wrapper. --}}
    @if(!is_array($decodedBody))
        {{-- Preserve the previous native renderer's empty-data fallback, tags and
             practice controls. Malformed JSON is not a new teaching paragraph. --}}
        <div data-theory-render-fallback="invalid-native-data">
    @endif
    @php
        $m27 = is_array($decodedBody) ? (\App\Support\M34ArgumentationCohesionPackage::presentation($block, $decodedBody)
            ?? \App\Support\M33AcademicEnglishPackage::presentation($block, $decodedBody)
            ?? \App\Support\M32FormalEnglishPackage::presentation($block, $decodedBody)
            ?? \App\Support\M31ConditionalsPackage::presentation($block, $decodedBody)
            ?? \App\Support\M30ParticipleClausesPackage::presentation($block, $decodedBody)
            ?? \App\Support\M29SentenceStructurePackage::presentation($block, $decodedBody)
            ?? \App\Support\M28EmphasisPackage::presentation($block, $decodedBody)
            ?? \App\Support\M27LinkingWordsPackage::presentation($block, $decodedBody)) : null;
        $renderData = $m27['data'] ?? $decodedBody;
        $basicHtml = view($nativeView, [
            'block' => $block,
            'data' => is_array($renderData) ? $renderData : [],
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ])->render();
        $points = $m27['points'] ?? (is_array($decodedBody) ? \App\Support\M26PointDetails::fragmentsFor($block, $decodedBody) : null);
        $pointSections = [];
        foreach ($points ?? [] as $index => $point) {
            $detailHtml = '';
            foreach ($point['fragments'] as $fragment) {
                $detailHtml .= view('theory.partials.point-detail-fragment', ['fragment' => $fragment])->render();
            }
            $section = \App\Support\TheorySection::resolve($point['key'], $point['title'],
                new \Illuminate\Support\HtmlString($basicHtml.$detailHtml), null, [
                    'source_key' => $point['key'], 'source_revision' => hash('sha256', $basicHtml.$detailHtml),
                    'main' => new \Illuminate\Support\HtmlString($basicHtml),
                    'detail' => new \Illuminate\Support\HtmlString($detailHtml),
                    'detail_references' => array_column($point['fragments'], 'id'),
                ], nativeHtml5: true);
            if ($section->detail === null) {
                $pointSections = [];
                // An invalid detail must never hide its complete stored author text.
                if ($m27 !== null) { $basicHtml = view($nativeView, ['block' => $block, 'data' => $decodedBody,
                    'practiceQuestions' => $practiceQuestions ?? collect(), 'lessonLinks' => $lessonLinks ?? []])->render(); }
                break;
            }
            $pointSections[$index] = $section;
        }
    @endphp
    @if($m27 !== null)
        @php($legacyBlockId = $block->page?->textBlocks?->first(fn ($b) => (int) $b->sort_order === 2 && $b->locale === 'uk')?->id)
        @if($legacyBlockId)<span id="lesson-block-{{ $legacyBlockId }}-section-{{ $m27['legacy_section'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>@endif
        @if($m27['legacy_practice_id'])<span id="{{ $m27['legacy_practice_id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>@endif
    @endif
    @if($pointSections !== [])
        {{-- Every disclosure belongs to its own existing basic point. --}}
        @include($nativeView, [
            'block' => $block,
            'data' => $renderData,
            'pointSections' => $pointSections,
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ])
    @else
        {!! $basicHtml !!}
    @endif
    @if(!is_array($decodedBody))
        </div>
    @endif
@elseif($block->type === 'box' || empty($block->type))
    <div id="{{ $blockAnchor }}" class="theory-content-block">
        <x-theory-rich-box :block="$block" :presentation="$presentation ?? \App\Support\TheoryPresentation::html($block)" />
    </div>
@elseif($block->type === 'subtitle')
    {{-- Intro text is already in the hero; retain the old unique fragment target. --}}
    <div id="{{ $blockAnchor }}" class="theory-subtitle-anchor" aria-hidden="true"></div>
@elseif(!empty($block->body))
    <article id="{{ $blockAnchor }}" class="theory-section-card theory-section-body" data-theory-render-fallback="unknown-format">
        @if(!empty($block->heading))
            <h2 class="theory-section-title">{{ $block->heading }}</h2>
        @endif
        {{-- Unknown formats are text, never a DB-selected executable Blade view. --}}
        <div class="theory-fallback-content">{{ $block->body }}</div>
    </article>
@endif
