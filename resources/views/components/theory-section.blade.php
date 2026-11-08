@props([
    'sectionKey',
    'title' => '',
    'number' => null,
    'headingLevel' => 2,
    'fullContent' => null,
    'pair' => null,
])
@php
    $section = \App\Support\TheorySection::resolve(
        (string) $sectionKey,
        (string) $title,
        $fullContent ?? $slot,
        $number === null ? null : (string) $number,
        $pair,
    );
    $headingTag = 'h'.(in_array((int) $headingLevel, [2, 3, 4], true) ? (int) $headingLevel : 2);
    $sectionNumber = $section->visibleNumber();
@endphp
<section {{ $attributes->class(['theory-section']) }} data-theory-section="{{ $section->key }}" @if($section->diagnosticCode) data-theory-section-fallback="{{ $section->diagnosticCode }}" @endif>
    @if($section->title !== '')
        <header class="theory-section-header">
            @if($sectionNumber !== null)
                <span class="theory-section-number" data-theory-ui aria-hidden="true">{{ $sectionNumber }}</span>
            @endif
            <{{ $headingTag }} class="theory-section-title">{{ $section->title }}</{{ $headingTag }}>
        </header>
    @endif
    <div class="theory-section-main">{{ $section->main }}</div>
    @include('theory.partials.section-disclosure', ['section' => $section])
</section>
