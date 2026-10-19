@php
    $theoryCanonical = ($theoryCanonical ?? false) === true;
    $source = \App\Support\TheoryContentSource::resolve($block, [
        'm42StyleContext' => $m42StyleContext ?? false,
        'm43StyleContext' => $m43StyleContext ?? false,
        'm44StyleContext' => $m44StyleContext ?? false,
        'm45StyleContext' => $m45StyleContext ?? false,
    ]);
    $nativeView = $source['view'];
    $decodedBody = $source['stored'];
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
        $m27 = $source['presentation'];
        $m42Design = $source['design'];
        $renderData = $source['data'];
        $basicHtml = \App\Support\TheoryContentRenderer::render($nativeView, [
            'theoryCanonical' => $theoryCanonical,
            'block' => $block,
            'data' => is_array($renderData) ? $renderData : [],
            'm42Design' => $m42Design,
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ]);
        $points = $m27['points'] ?? (is_array($decodedBody) ? \App\Support\M26PointDetails::fragmentsFor($block, $decodedBody) : null);
        $pointSections = [];
        foreach ($points ?? [] as $index => $point) {
            $detailHtml = '';
            foreach ($point['fragments'] as $fragment) {
                $detailHtml .= view('theory.partials.point-detail-fragment', ['fragment' => $fragment, 'm42Design' => $m42Design, 'theoryCanonical' => $theoryCanonical])->render();
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
                if ($m27 !== null) { $basicHtml = \App\Support\TheoryContentRenderer::render($source['detail_fallback_view'], ['block' => $block, 'data' => $decodedBody,
                    'practiceQuestions' => $practiceQuestions ?? collect(), 'lessonLinks' => $lessonLinks ?? [], 'theoryCanonical' => $theoryCanonical]); }
                $m42Design = null;
                break;
            }
            $pointSections[$index] = $section;
        }
    @endphp
    @unless($theoryCanonical)
        @include('courses.compatibility.theory.package-styles')
    @endunless
    @if($m42Design !== null && !$theoryCanonical)
        {{-- Emit once outside discarded validation renders. Scope stays out of courses. --}}
        <div class="m42-native-design" data-m42-native-design="v1"
             data-m42-native-kind="{{ $m42Design['component'] }}"
             data-m42-color="{{ $m42Design['color'] ?? 'slate' }}"
             data-m42-source-uuid="{{ $m42Design['uuid'] }}">
    @endif
    @if($m27 !== null && ($m27['legacy_section'] ?? null) !== null)
        @php($legacyBlockId = isset($m27['legacy_block_uuid'])
            ? $block->page?->textBlocks?->first(fn ($b) => $b->uuid === $m27['legacy_block_uuid'] && $b->locale === 'uk')?->id
            : $block->page?->textBlocks?->first(fn ($b) => (int) $b->sort_order === 2 && $b->locale === 'uk')?->id)
        @if($legacyBlockId)<span id="lesson-block-{{ $legacyBlockId }}-section-{{ $m27['legacy_section'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>@endif
        @if($m27['legacy_practice_id'])<span id="{{ $m27['legacy_practice_id'] }}" class="theory-subtitle-anchor" aria-hidden="true"></span>@endif
    @endif
    @if($pointSections !== [])
        {{-- Every disclosure belongs to its own existing basic point. --}}
        {!! \App\Support\TheoryContentRenderer::render($nativeView, [
            'theoryCanonical' => $theoryCanonical,
            'block' => $block,
            'data' => $renderData,
            'm42Design' => $m42Design,
            'pointSections' => $pointSections,
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ]) !!}
    @else
        {!! $basicHtml !!}
    @endif
    @if($m42Design !== null && !$theoryCanonical)</div>@endif
    @if(!is_array($decodedBody))
        </div>
    @endif
@elseif($block->type === 'box' || empty($block->type))
    @include('theory.content', ['content' => [
        'kind' => 'legacy-box',
        'presentation' => $presentation ?? \App\Support\TheoryPresentation::html($block, canonical: $theoryCanonical),
    ]])
@elseif($block->type === 'subtitle')
    {{-- Intro text is already in the hero; retain the old unique fragment target. --}}
    @include('theory.content', ['content' => ['kind' => 'anchor', 'id' => $blockAnchor]])
@elseif(!empty($block->body))
    @include('theory.content', ['content' => ['kind' => 'text-fallback']])
@endif
