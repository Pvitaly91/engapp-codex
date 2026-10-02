@if($section->detail !== null)
    <details class="theory-section-details" data-theory-details>
        <summary class="theory-section-toggle" id="{{ $section->detailsId() }}">
            <span class="theory-section-toggle-closed">{{ __('theory_blocks.section.more') }}</span>
            <span class="theory-section-toggle-open">{{ __('theory_blocks.section.less') }}</span>
            <span class="sr-only"> — {{ $section->contextTitle() }}</span>
            <span class="theory-section-toggle-arrow" aria-hidden="true">⌄</span>
        </summary>
        <div class="theory-section-detail-body">{{ $section->detail }}</div>
    </details>
    <noscript><p class="theory-section-nojs">{{ __('theory_blocks.section.no_js') }} <a href="#{{ $section->detailsId() }}">{{ __('theory_blocks.section.more') }} — {{ $section->contextTitle() }}</a></p></noscript>
@endif
