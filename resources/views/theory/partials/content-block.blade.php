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
        $basicHtml = view($nativeView, [
            'block' => $block,
            'data' => is_array($decodedBody) ? $decodedBody : [],
            'practiceQuestions' => $practiceQuestions ?? collect(),
            'lessonLinks' => $lessonLinks ?? [],
        ])->render();
        $extension = is_array($decodedBody) ? \App\Support\M26DetailPackage::detailFor($block, $decodedBody) : null;
        $nativeSection = null;
        if ($extension !== null) {
            // A view-only projection avoids Eloquent's integer primary-key cast.
            // It never mutates the persisted block ID/UUID or duplicates relations.
            $detailBlock = (object) ['id' => $extension['key'], 'uuid' => $block->uuid,
                'level' => $block->level ?? null];
            $detailHtml = view($nativeView, ['block' => $detailBlock, 'data' => $extension['detail_native_data'],
                'embeddedDetail' => true, 'practiceQuestions' => collect(), 'lessonLinks' => []])->render();
            $detailId = 'block-'.$extension['key'];
            $nativeSection = \App\Support\TheorySection::resolve($extension['key'], (string) ($decodedBody['title'] ?? ''),
                new \Illuminate\Support\HtmlString($basicHtml.$detailHtml), null, [
                    'source_key' => $extension['key'], 'source_revision' => hash('sha256', $basicHtml.$detailHtml),
                    'main' => new \Illuminate\Support\HtmlString($basicHtml),
                    'detail' => new \Illuminate\Support\HtmlString($detailHtml), 'detail_references' => [$detailId],
                ], nativeHtml5: true);
        }
    @endphp
    @if($nativeSection?->detail !== null)
        {{-- Render the same finite native view; the disclosure belongs to its
             content card rather than to a detached sibling below it. --}}
        @include($nativeView, [
            'block' => $block,
            'data' => $decodedBody,
            'nativeSection' => $nativeSection,
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
