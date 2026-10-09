@php
    $theoryCanonical = ($theoryCanonical ?? false) === true;
    $nativeView = \App\Support\TheoryPresentation::nativeView($block->type);
    $decodedBody = json_decode($block->body ?? '', true);
    $m45StoredAuthor = is_array($decodedBody) && \App\Support\M45FutureComparisonsPackage::hasStoredAuthor($decodedBody);
    if (!$nativeView && $m45StoredAuthor) { $nativeView = 'engram.theory.blocks-v3.m45-static-fallback'; }
    $m44StoredAuthor = is_array($decodedBody) && \App\Support\M44AuthoredFutureFormsPackage::hasStoredAuthor($decodedBody);
    if (!$nativeView && $m44StoredAuthor) { $nativeView = 'engram.theory.blocks-v3.m44-static-fallback'; }
    $m43StoredAuthor = is_array($decodedBody) && \App\Support\M43AuthoredTenseUsagePackage::hasStoredAuthor($decodedBody);
    if (!$nativeView && $m43StoredAuthor) { $nativeView = 'engram.theory.blocks-v3.m43-static-fallback'; }
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
        $m27 = is_array($decodedBody) ? (($m45StyleContext ?? false ? \App\Support\M45FutureComparisonsPackage::presentation($block, $decodedBody) : null)
            ?? ($m44StyleContext ?? false ? \App\Support\M44AuthoredFutureFormsPackage::presentation($block, $decodedBody) : null)
            ?? ($m43StyleContext ?? false ? \App\Support\M43AuthoredTenseUsagePackage::presentation($block, $decodedBody) : null)
            ?? \App\Support\M41AuthoredTenseComparisonsPackage::presentation($block, $decodedBody)
            ?? \App\Support\M40TensesB1Package::presentation($block, $decodedBody)
            ?? \App\Support\M39PracticeUiPackage::presentation($block, $decodedBody)
            ?? \App\Support\M39AuthoredRevisionPackage::presentation($block, $decodedBody)
            ?? \App\Support\M38ArticlesCollocationsPackage::presentation($block, $decodedBody)
            ?? \App\Support\M37GrammarStructuresPackage::presentation($block, $decodedBody)
            ?? \App\Support\M36ModalsSubjunctivePackage::presentation($block, $decodedBody)
            ?? \App\Support\M35PassiveReportingPackage::presentation($block, $decodedBody)
            ?? \App\Support\M34ArgumentationCohesionPackage::presentation($block, $decodedBody)
            ?? \App\Support\M33AcademicEnglishPackage::presentation($block, $decodedBody)
            ?? \App\Support\M32FormalEnglishPackage::presentation($block, $decodedBody)
            ?? \App\Support\M31ConditionalsPackage::presentation($block, $decodedBody)
            ?? \App\Support\M30ParticipleClausesPackage::presentation($block, $decodedBody)
            ?? \App\Support\M29SentenceStructurePackage::presentation($block, $decodedBody)
            ?? \App\Support\M28EmphasisPackage::presentation($block, $decodedBody)
            ?? \App\Support\M27LinkingWordsPackage::presentation($block, $decodedBody)) : null;
        // A caller-owned theory-only opt-in, never a URL/category or DB view-name guess.
        // Decoration leaves every existing package's data, fragments and keys intact.
        $m27 = is_array($decodedBody)
            ? (\App\Support\M42NativeDesignPackage::decorate($block, $decodedBody, $m27, $m42StyleContext ?? false) ?? $m27)
            : $m27;
        $m42Design = $m27['m42_native_design'] ?? null;
        $renderData = $m27['data'] ?? $decodedBody;
        // The finite package returns a code-owned constant after complete identity checks.
        if (($m27['native_view'] ?? null) !== null) { $nativeView = $m27['native_view']; }
        if ($m27 === null && $m45StoredAuthor) { $nativeView = 'engram.theory.blocks-v3.m45-static-fallback'; }
        if ($m27 === null && $m44StoredAuthor) {
            $nativeView = 'engram.theory.blocks-v3.m44-static-fallback';
        }
        if ($m27 === null && $m43StoredAuthor) {
            $nativeView = 'engram.theory.blocks-v3.m43-static-fallback';
        }
        $basicHtml = view($nativeView, [
            'theoryCanonical' => $theoryCanonical,
            'block' => $block,
            'data' => is_array($renderData) ? $renderData : [],
            'm42Design' => $m42Design,
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ])->render();
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
                if ($m27 !== null) { $basicHtml = view($m44StoredAuthor ? 'engram.theory.blocks-v3.m44-static-fallback' : ($m43StoredAuthor ? 'engram.theory.blocks-v3.m43-static-fallback' : $nativeView), ['block' => $block, 'data' => $decodedBody,
                    'practiceQuestions' => $practiceQuestions ?? collect(), 'lessonLinks' => $lessonLinks ?? [], 'theoryCanonical' => $theoryCanonical])->render(); }
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
        @include($nativeView, [
            'theoryCanonical' => $theoryCanonical,
            'block' => $block,
            'data' => $renderData,
            'm42Design' => $m42Design,
            'pointSections' => $pointSections,
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ])
    @else
        {!! $basicHtml !!}
    @endif
    @if($m42Design !== null && !$theoryCanonical)</div>@endif
    @if(!is_array($decodedBody))
        </div>
    @endif
@elseif($block->type === 'box' || empty($block->type))
    <div id="{{ $blockAnchor }}" class="theory-content-block">
        <x-theory-rich-box :block="$block" :presentation="$presentation ?? \App\Support\TheoryPresentation::html($block, canonical: $theoryCanonical)" />
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
