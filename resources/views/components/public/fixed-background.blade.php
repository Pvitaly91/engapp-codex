@php
    /**
     * Build the icon roster for the fixed background. Positions and
     * sizes are recomputed on every request (via mt_rand) so the
     * pattern looks different on each page reload — no cached SVG.
     *
     * Strategy: divide the visible canvas into a coarse grid (8×11)
     * and drop ONE icon into each cell, jittered around the cell
     * centre. That keeps even spacing without producing a robotic
     * grid look. Big "feature" icons (book / notebook / headphones)
     * are picked roughly 1 in 3 cells; the rest get small accents.
     */
    $featureIcons = ['letter-aa', 'letter-abc', 'question-bubble', 'hi-bubble', 'en-globe', 'translate-arrows', 'book-open', 'notebook', 'headphones', 'microphone', 'lightbulb', 'flashcards', 'graduation-cap'];
    $accentIcons  = ['plus-letter', 'star', 'checkmark', 'quote-marks', 'spiral', 'dot', 'pencil'];
    $tones        = ['accent', 'warm', 'muted'];

    $cols = 8;
    $rows = 11;
    $cellW = 100 / $cols;
    $cellH = 100 / $rows;

    $icons = [];
    for ($r = 0; $r < $rows; $r++) {
        for ($c = 0; $c < $cols; $c++) {
            // ~12% empty cells so the pattern breathes.
            if (mt_rand(0, 99) < 12) continue;

            $isFeature = mt_rand(0, 2) === 0; // ~1 in 3 is a big icon
            $pool = $isFeature ? $featureIcons : $accentIcons;
            $name = $pool[array_rand($pool)];

            // Jitter within the cell, so icons aren't centred on the grid.
            $left = $c * $cellW + mt_rand(10, 90) / 100 * $cellW;
            $top  = $r * $cellH + mt_rand(10, 90) / 100 * $cellH;

            $size = $isFeature
                ? mt_rand(70, 130)
                : mt_rand(28, 56);

            $rotate  = mt_rand(-22, 22);
            $opacity = $isFeature
                ? mt_rand(78, 92) / 100
                : mt_rand(60, 80) / 100;

            $icons[] = [
                'name'    => $name,
                'size'    => $size,
                'left'    => round($left, 2) . '%',
                'top'     => round($top, 2) . '%',
                'rotate'  => $rotate . 'deg',
                'opacity' => $opacity,
                'tone'    => $tones[array_rand($tones)],
            ];
        }
    }
@endphp

@once
    {{-- Self-contained CSS so the component can be dropped into any
         layout without depending on layout-specific stylesheets. --}}
    <style>
        .app-fixed-background__canvas {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }
        .app-bg-icon {
            position: absolute;
            left: var(--left, 50%);
            top: var(--top, 50%);
            width: var(--size, 64px);
            height: var(--size, 64px);
            transform: translate(-50%, -50%) rotate(var(--rotate, 0deg));
            opacity: var(--opacity, 0.7);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .app-bg-icon svg {
            width: 100%;
            height: 100%;
        }
        /* Tone variants — pick a flavour from the public palette. */
        .app-bg-icon--accent { color: #2f67b1; }
        .app-bg-icon--warm   { color: #f59b2f; }
        .app-bg-icon--muted  { color: #94a3b8; }
        .dark .app-bg-icon--accent { color: #7ab3ff; }
        .dark .app-bg-icon--warm   { color: #ffbb6b; }
        .dark .app-bg-icon--muted  { color: #cbd5e1; }
    </style>
@endonce

<div class="app-fixed-background" aria-hidden="true">
    <div class="app-fixed-background__canvas">
        @foreach ($icons as $icon)
            <span
                class="app-bg-icon app-bg-icon--{{ $icon['tone'] }}"
                style="--size: {{ $icon['size'] }}px; --left: {{ $icon['left'] }}; --top: {{ $icon['top'] }}; --rotate: {{ $icon['rotate'] }}; --opacity: {{ $icon['opacity'] }};"
            >
                <x-public.background-icon-svg :name="$icon['name']" />
            </span>
        @endforeach
    </div>
</div>
