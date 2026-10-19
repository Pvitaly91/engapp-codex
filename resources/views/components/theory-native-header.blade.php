@props(['title', 'level' => null, 'fallback' => '#'])

@php
    // Only an authored, leading section number is structural. B1/C2 and
    // numbers elsewhere in the title must never become a second number.
    $heading = is_string($title) ? $title : '';
    $number = null;
    if (preg_match('/^(\d+[.)])\s+/u', $heading, $match)) {
        $number = $match[1];
        $heading = substr($heading, strlen($match[0]));
    }
@endphp

<div class="theory-section-header">
    <h2 class="theory-section-title">
        <span class="theory-section-number" @if($number === null) aria-hidden="true" data-theory-ui @endif>{{ $number ?? $fallback }}</span>
        <span>{{ $heading }}</span>
    </h2>
    <x-text-block-level-badge :level="$level" />
</div>
