@if($section->detail !== null)
    <details class="theory-section-details" data-theory-details{{ \App\Support\TheoryComponents::attrs($disclosureAttrs ?? []) }}>
        <summary class="theory-section-toggle" id="{{ $toggleId ?? $section->detailsId() }}">
            <span class="theory-section-toggle-closed">{{ __('theory_blocks.section.more') }}</span>
            <span class="theory-section-toggle-open">{{ __('theory_blocks.section.less') }}</span>
            <span class="sr-only"> — {{ $section->contextTitle() }}</span>
        </summary>
        <div class="theory-section-detail-body">{{ $section->detail }}</div>
    </details>
    <noscript><p class="theory-section-nojs">{{ __('theory_blocks.section.no_js') }} <a href="#{{ $toggleId ?? $section->detailsId() }}">{{ __('theory_blocks.section.more') }} — {{ $section->contextTitle() }}</a></p></noscript>
@endif
