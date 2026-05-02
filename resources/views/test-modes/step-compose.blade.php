@extends('layouts.engram')

@section('title', $test->name)

@section('content')
@php
    $courseContext = is_array($courseContext ?? null) ? $courseContext : [];
    $rawFilters = $test->filters ?? [];
    if (is_string($rawFilters)) {
        $decodedFilters = json_decode($rawFilters, true);
        $rawFilters = is_array($decodedFilters) ? $decodedFilters : [];
    }
    $rawFilters = is_array($rawFilters) ? $rawFilters : [];
    $rawCompletionWindow = (int) data_get($rawFilters, 'completion.rolling_window', 100);
    $lessonQuestionTotal = is_array($questionData ?? null) ? count($questionData) : 0;
    $completionWindow = $lessonQuestionTotal > 0
        ? max(1, min($rawCompletionWindow, $lessonQuestionTotal))
        : max(1, $rawCompletionWindow);
    $completionRating = (float) data_get($rawFilters, 'completion.min_rating', 4.5);
    $courseSlug = data_get($courseContext, 'course_slug', data_get($rawFilters, 'course_slug'));
    $courseUrl = data_get($courseContext, 'course_url', filled($courseSlug) ? localized_route('courses.show', $courseSlug) : null);
    $previousLessonSlug = data_get($courseContext, 'previous_lesson_slug', data_get($rawFilters, 'previous_lesson_slug'));
    $nextLessonSlug = data_get($courseContext, 'next_lesson_slug', data_get($rawFilters, 'next_lesson_slug'));
    $firstLessonSlug = data_get($courseContext, 'first_lesson_slug');
    $firstLessonUrl = data_get($courseContext, 'first_lesson_url');
    $isFinalLesson = ! filled($nextLessonSlug);
    $startsLockedPending = filled($courseSlug) && filled($previousLessonSlug);
    $continueCourseMap = [
        'polyglot-english-a1' => [
            'slug' => 'polyglot-english-a2',
            'name' => 'Polyglot English A2',
        ],
        'polyglot-english-a2' => [
            'slug' => 'polyglot-english-b1',
            'name' => 'Polyglot English B1',
        ],
        'polyglot-english-b1' => [
            'slug' => 'polyglot-english-b2',
            'name' => 'Polyglot English B2',
        ],
        'polyglot-english-b2' => [
            'slug' => 'polyglot-english-c1',
            'name' => 'Polyglot English C1',
        ],
        'polyglot-english-c1' => [
            'slug' => 'polyglot-english-c2',
            'name' => 'Polyglot English C2',
        ],
    ];
    $continueCourse = $isFinalLesson ? ($continueCourseMap[$courseSlug ?? ''] ?? null) : null;
    $continueCourseUrl = is_array($continueCourse)
        ? localized_route('courses.show', $continueCourse['slug'])
        : null;
    $continueCourseLabel = is_array($continueCourse)
        ? __('frontend.tests.course.continue_with_next_course', ['course' => $continueCourse['name']])
        : null;
    $allPolyglotLevelsCompleteLabel = $isFinalLesson && ! is_array($continueCourse)
        ? __('frontend.tests.course.all_polyglot_levels_complete')
        : null;
@endphp

<style>
    /* Polyglot compose — modern layout. CSS variables, IDs and data-* attrs
       expected by the JS controller are preserved 1:1. */
    #new-design-test-shell #polyglot-compose-root,
    #polyglot-compose-root {
        --polyglot-chip-min-height: 1.45rem;
        --polyglot-chip-padding: 0.16rem 0.9rem;
        --polyglot-chip-radius: 12px;
        --polyglot-chip-font-size: 1.38rem;

        --poly-radius-lg: 28px;
        --poly-radius-md: 20px;
        --poly-radius-sm: 14px;
        --poly-shadow: 0 28px 60px -36px rgba(15, 38, 67, 0.35);
        --poly-shadow-soft: 0 14px 32px -22px rgba(15, 38, 67, 0.28);
        --poly-line: color-mix(in srgb, var(--line) 82%, white);
        --poly-line-strong: color-mix(in srgb, var(--accent) 22%, var(--line));
        --poly-accent-tint: color-mix(in srgb, var(--accent) 10%, var(--surface-strong));
        --poly-prompt-bg: linear-gradient(140deg, color-mix(in srgb, var(--accent-soft) 78%, white) 0%, color-mix(in srgb, var(--surface-strong) 100%, white) 75%);
        --poly-emerald-bg: color-mix(in srgb, #d1fae5 55%, white);
        --poly-emerald-border: color-mix(in srgb, #10b981 38%, var(--line));
    }

    @media (max-width: 639px) {
        #new-design-test-shell #polyglot-compose-root,
        #polyglot-compose-root {
            margin-left: -0.75rem !important;
            margin-right: -0.75rem !important;
            width: calc(100% + 1.5rem) !important;
            max-width: calc(100% + 1.5rem) !important;
            --polyglot-chip-min-height: 2rem;
            --polyglot-chip-padding: 0.32rem 0.7rem;
            --polyglot-chip-radius: 0.7rem;
            --polyglot-chip-font-size: 1rem;
        }
    }

    #polyglot-compose-root .poly-stack > * + * { margin-top: 1rem; }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-stack > * + * { margin-top: 1.4rem; }
    }

    /* ── Course breadcrumb ────────────────────────────────────────────── */
    #polyglot-compose-root .poly-crumbs {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem 0.85rem;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
        border-radius: var(--poly-radius-md);
        border: 1px solid var(--poly-line);
        background: color-mix(in srgb, var(--surface) 86%, white);
        box-shadow: var(--poly-shadow-soft);
    }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-crumbs {
            padding: 0.95rem 1.25rem;
            margin-bottom: 1.25rem;
        }
    }
    #polyglot-compose-root .poly-crumbs__back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        padding: 0.45rem 0.95rem;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: var(--accent);
        background: color-mix(in srgb, var(--accent-soft) 65%, white);
        border: 1px solid color-mix(in srgb, var(--accent) 22%, var(--line));
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    #polyglot-compose-root .poly-crumbs__back:hover {
        transform: translateX(-2px);
        box-shadow: 0 8px 20px -12px color-mix(in srgb, var(--accent) 60%, transparent);
    }
    #polyglot-compose-root .poly-crumbs__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--muted);
    }
    #polyglot-compose-root .poly-crumbs__sep { opacity: 0.4; }
    #polyglot-compose-root .poly-crumbs__topic { color: var(--text); }
    #polyglot-compose-root .poly-crumbs__prev {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.5rem 1rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text);
        border: 1px solid var(--line);
        background: var(--surface-strong);
        transition: border-color 0.18s ease, background 0.18s ease;
    }
    #polyglot-compose-root .poly-crumbs__prev:hover {
        border-color: color-mix(in srgb, var(--accent) 35%, var(--line));
        background: color-mix(in srgb, var(--accent-soft) 55%, white);
    }

    /* ── Hero card with stats ────────────────────────────────────────── */
    #polyglot-compose-root .poly-hero {
        position: relative;
        border-radius: var(--poly-radius-lg);
        border: 1px solid var(--poly-line);
        background: var(--surface-strong);
        padding: 1.4rem 1.25rem;
        box-shadow: var(--poly-shadow-soft);
    }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-hero { padding: 2rem 2.2rem; }
    }
    #polyglot-compose-root .poly-hero__inner {
        display: grid; gap: 1.4rem;
    }
    @media (min-width: 1024px) {
        #polyglot-compose-root .poly-hero__inner {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.95fr);
            align-items: center; gap: 2.4rem;
        }
    }
    #polyglot-compose-root .poly-hero__lead {
        display: flex; flex-direction: column; gap: 0.85rem; min-width: 0;
    }
    #polyglot-compose-root .poly-hero__eyebrow {
        display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.75rem;
    }
    #polyglot-compose-root .poly-pill {
        display: inline-flex; align-items: center; gap: 0.35rem;
        border-radius: 999px; padding: 0.4rem 0.95rem;
        font-size: 0.65rem; font-weight: 800;
        letter-spacing: 0.18em; text-transform: uppercase;
        color: var(--accent);
        background: color-mix(in srgb, var(--accent-soft) 78%, white);
        border: 1px solid color-mix(in srgb, var(--accent) 25%, var(--line));
    }
    #polyglot-compose-root .poly-hero__progress {
        font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.14em; text-transform: uppercase;
        color: var(--muted);
    }
    #polyglot-compose-root .poly-hero__title {
        margin: 0;
        font-family: 'DM Serif Display', 'Georgia', serif;
        font-size: clamp(1.55rem, 1.6vw + 1rem, 2.55rem);
        font-weight: 800; line-height: 1.1; letter-spacing: -0.018em;
        color: var(--text);
    }
    #polyglot-compose-root .poly-stats {
        display: grid; gap: 0.7rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        min-width: 0;
    }
    #polyglot-compose-root .poly-stat {
        position: relative;
        border-radius: var(--poly-radius-md);
        border: 1px solid var(--poly-line);
        background: color-mix(in srgb, var(--surface-strong) 96%, white);
        padding: 1rem 1.1rem;
        box-shadow: var(--poly-shadow-soft);
        overflow: hidden;
    }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-stat { padding: 1.15rem 1.35rem; }
    }
    #polyglot-compose-root .poly-stat--primary {
        background: color-mix(in srgb, var(--surface-strong) 96%, white);
        border-color: color-mix(in srgb, var(--accent) 30%, var(--line));
        border-left-width: 3px;
        border-left-color: var(--accent);
    }
    #polyglot-compose-root .poly-stat__label {
        font-size: 0.6rem; font-weight: 800;
        letter-spacing: 0.2em; text-transform: uppercase;
        color: var(--accent); margin: 0;
    }
    #polyglot-compose-root .poly-stat__value {
        margin: 0.45rem 0 0;
        font-family: 'DM Serif Display', 'Georgia', serif;
        font-size: clamp(1.45rem, 1.2vw + 1rem, 2rem);
        font-weight: 800; line-height: 1; letter-spacing: -0.018em;
        color: var(--text);
    }
    #polyglot-compose-root .poly-stat__value--small {
        font-size: clamp(1.05rem, 0.6vw + 0.9rem, 1.45rem);
        line-height: 1.15;
    }
    #polyglot-compose-root .poly-stat__meta {
        margin: 0.5rem 0 0; font-size: 0.74rem; line-height: 1.4;
        color: var(--muted);
    }

    /* ── Workspace card ──────────────────────────────────────────────── */
    #polyglot-compose-root .poly-card {
        border-radius: var(--poly-radius-lg);
        border: 1px solid var(--poly-line);
        background: var(--surface-strong);
        padding: 1.25rem 1.15rem;
        box-shadow: var(--poly-shadow);
    }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-card { padding: 2rem 2.2rem; }
    }
    #polyglot-compose-root .poly-empty {
        border-radius: var(--poly-radius-md);
        border: 1.5px dashed var(--poly-line);
        padding: 1.75rem 1.25rem;
        text-align: center;
        background: color-mix(in srgb, var(--surface) 90%, white);
        color: var(--muted);
    }
    #polyglot-compose-root .poly-empty p {
        margin: 0; font-size: 1rem; font-weight: 600; color: var(--text);
    }

    /* ── Source-sentence prompt ──────────────────────────────────────── */
    #polyglot-compose-root .poly-prompt {
        position: relative; overflow: hidden;
        border-radius: var(--poly-radius-md);
        border: 1px solid color-mix(in srgb, var(--accent) 18%, var(--poly-line));
        background: var(--poly-prompt-bg);
        padding: 1.25rem 1.15rem 1.4rem;
        margin-bottom: 1.2rem;
    }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-prompt {
            padding: 1.65rem 1.85rem 1.85rem;
            margin-bottom: 1.6rem;
        }
    }
    #polyglot-compose-root .poly-prompt::before {
        content: ""; position: absolute; inset: 0 0 auto; height: 4px;
        background: linear-gradient(90deg,
            var(--accent) 0%,
            color-mix(in srgb, var(--accent) 35%, white) 100%);
    }
    #polyglot-compose-root .poly-prompt__eyebrow {
        display: flex; align-items: center; justify-content: space-between;
        gap: 0.75rem; flex-wrap: wrap;
    }
    #polyglot-compose-root .poly-prompt__label {
        font-size: 0.62rem; font-weight: 800;
        letter-spacing: 0.22em; text-transform: uppercase;
        color: var(--accent);
    }
    #polyglot-compose-root .poly-prompt__punct {
        display: inline-flex; align-items: center; gap: 0.3rem;
        border-radius: 10px; padding: 0.32rem 0.7rem;
        font-size: 0.78rem; font-weight: 700; line-height: 1;
        color: var(--muted);
        background: color-mix(in srgb, var(--surface) 92%, white);
        border: 1px solid var(--poly-line);
    }
    #polyglot-compose-root #compose-source-text {
        margin-top: 0.95rem;
        font-family: 'DM Serif Display', 'Georgia', serif;
        font-size: clamp(1.5rem, 1.5vw + 1rem, 2.4rem);
        font-weight: 800; line-height: 1.18; letter-spacing: -0.012em;
        color: var(--text); word-break: break-word;
    }
    @media (min-width: 768px) {
        #polyglot-compose-root #compose-source-text { margin-top: 1.1rem; }
    }
    #polyglot-compose-root .poly-prompt__actions {
        margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 0.5rem;
    }
    #polyglot-compose-root #compose-theory-btn {
        display: inline-flex; align-items: center; gap: 0.4rem;
        border-radius: 999px; padding: 0.46rem 0.95rem;
        font-size: 0.78rem; font-weight: 700; cursor: pointer;
        background: var(--poly-emerald-bg); color: #047857;
        border: 1px solid var(--poly-emerald-border);
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    #polyglot-compose-root #compose-theory-btn:hover {
        background: #d1fae5; color: #064e3b;
        transform: translateY(-1px);
        box-shadow: 0 10px 22px -14px rgba(16, 185, 129, 0.5);
    }
    #polyglot-compose-root #compose-theory-btn svg { width: 1rem; height: 1rem; }
    #polyglot-compose-root #compose-theory-panel { margin-top: 1.1rem; }
    #polyglot-compose-root #compose-source-seeder {
        margin-top: 0.85rem;
        padding: 0.55rem 0.85rem;
        font-family: ui-monospace, SFMono-Regular, monospace;
        font-size: 0.72rem; line-height: 1.45;
        color: var(--muted);
        background: color-mix(in srgb, var(--surface) 88%, white);
        border: 1px solid var(--poly-line);
        border-radius: 12px; word-break: break-all;
    }

    /* ── Compose surface (answer + bank) ─────────────────────────────── */
    #polyglot-compose-root .poly-surface {
        border-radius: var(--poly-radius-md);
        border: 1px solid var(--poly-line);
        background: color-mix(in srgb, var(--surface) 92%, white);
        padding: 1.1rem 1rem 1.25rem;
    }
    @media (min-width: 768px) {
        #polyglot-compose-root .poly-surface { padding: 1.4rem 1.55rem 1.55rem; }
    }
    #polyglot-compose-root .poly-surface__header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 0.65rem; flex-wrap: wrap; margin-bottom: 0.85rem;
    }
    #polyglot-compose-root .poly-surface__heading {
        margin: 0; font-size: 0.65rem; font-weight: 800;
        letter-spacing: 0.22em; text-transform: uppercase;
        color: var(--accent);
    }
    #polyglot-compose-root .poly-surface__hint {
        font-size: 0.7rem; font-weight: 600; color: var(--muted);
    }
    #polyglot-compose-root #compose-answer-zone {
        border-radius: var(--poly-radius-md);
        border: 2px dashed color-mix(in srgb, var(--accent) 32%, var(--line));
        background: color-mix(in srgb, var(--surface-strong) 96%, white);
        padding: 0.95rem; min-height: 4.5rem;
        display: flex; flex-wrap: wrap; gap: 0.55rem;
        align-content: flex-start;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    #polyglot-compose-root #compose-answer-zone:hover {
        border-color: color-mix(in srgb, var(--accent) 50%, var(--line));
    }
    @media (min-width: 768px) {
        #polyglot-compose-root #compose-answer-zone {
            padding: 1.25rem 1.4rem; min-height: 7rem; gap: 0.7rem;
        }
    }
    #polyglot-compose-root .poly-divider {
        display: flex; align-items: center; gap: 0.7rem;
        margin: 1.05rem 0 0.75rem;
    }
    #polyglot-compose-root .poly-divider::before,
    #polyglot-compose-root .poly-divider::after {
        content: ""; flex: 1; height: 1px;
        background: linear-gradient(90deg,
            transparent 0%,
            color-mix(in srgb, var(--line) 90%, white) 50%,
            transparent 100%);
    }
    #polyglot-compose-root .poly-divider span {
        font-size: 0.62rem; font-weight: 800;
        letter-spacing: 0.22em; text-transform: uppercase;
        color: var(--muted);
    }
    #polyglot-compose-root #compose-bank {
        display: flex; flex-wrap: wrap; gap: 0.5rem;
        min-height: 3.5rem; align-content: flex-start;
    }
    @media (min-width: 768px) {
        #polyglot-compose-root #compose-bank { gap: 0.65rem; min-height: 4.5rem; }
    }

    /* ── Action bar ──────────────────────────────────────────────────── */
    #polyglot-compose-root #compose-controls {
        margin-top: 1.4rem;
        display: flex; flex-direction: column; gap: 0.85rem;
    }
    @media (min-width: 768px) {
        #polyglot-compose-root #compose-controls { margin-top: 1.7rem; }
    }
    #polyglot-compose-root .poly-actions {
        display: flex; flex-direction: column; gap: 0.55rem;
    }
    @media (min-width: 640px) {
        #polyglot-compose-root .poly-actions {
            flex-direction: row; align-items: center;
            justify-content: space-between; gap: 0.85rem;
        }
    }
    #polyglot-compose-root .poly-actions__primary,
    #polyglot-compose-root .poly-actions__secondary {
        display: flex; flex-wrap: wrap; gap: 0.55rem;
    }
    #polyglot-compose-root .poly-btn {
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px; padding: 0.65rem 1.15rem;
        font-size: 0.82rem; font-weight: 700; line-height: 1;
        cursor: pointer; border: 1px solid var(--line);
        background: var(--surface-strong); color: var(--text);
        transition: transform 0.18s ease, box-shadow 0.18s ease,
                    border-color 0.18s ease, background 0.18s ease;
    }
    #polyglot-compose-root .poly-btn:hover:not(:disabled) {
        border-color: color-mix(in srgb, var(--accent) 38%, var(--line));
        background: color-mix(in srgb, var(--accent-soft) 50%, white);
    }
    #polyglot-compose-root .poly-btn:disabled { opacity: 0.55; cursor: not-allowed; }
    #polyglot-compose-root .poly-btn--primary {
        background: linear-gradient(135deg,
            var(--ocean, #2f67b1) 0%,
            color-mix(in srgb, var(--ocean, #2f67b1) 70%, var(--accent)) 100%);
        color: #fff; border-color: transparent;
        padding: 0.85rem 1.65rem; font-size: 0.92rem;
        font-weight: 800; letter-spacing: 0.02em;
        box-shadow: 0 14px 30px -16px rgba(47, 103, 177, 0.65);
    }
    #polyglot-compose-root .poly-btn--primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 18px 36px -16px rgba(47, 103, 177, 0.78);
        border-color: transparent;
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--ocean, #2f67b1) 92%, white) 0%,
            color-mix(in srgb, var(--ocean, #2f67b1) 60%, var(--accent)) 100%);
    }
    #polyglot-compose-root .poly-btn--ghost {
        background: transparent; border-color: transparent; color: var(--muted);
    }
    #polyglot-compose-root .poly-btn--ghost:hover:not(:disabled) {
        color: var(--text);
        background: color-mix(in srgb, var(--surface) 88%, white);
        border-color: var(--line);
    }
    #polyglot-compose-root .poly-keyboard-hint {
        margin: 0; font-size: 0.74rem; line-height: 1.45; color: var(--muted);
    }

    /* mobile: full-width primary, secondary buttons in a row underneath */
    @media (max-width: 639px) {
        #polyglot-compose-root .poly-actions__primary { flex-direction: column; }
        #polyglot-compose-root .poly-actions__primary .poly-btn--primary { width: 100%; }
        #polyglot-compose-root .poly-actions__secondary { justify-content: space-between; }
        #polyglot-compose-root .poly-actions__secondary .poly-btn {
            flex: 1 1 calc(50% - 0.3rem); min-width: 0;
            padding: 0.6rem 0.85rem; font-size: 0.78rem;
        }
    }
</style>

<div class="-mx-3 w-auto max-w-5xl sm:mx-auto sm:w-full"
     id="polyglot-compose-root"
     data-polyglot-lesson-root
     data-polyglot-lesson-slug="{{ $test->slug }}"
     data-polyglot-course-slug="{{ $courseSlug }}"
     data-polyglot-previous-lesson-slug="{{ $previousLessonSlug }}"
     data-polyglot-next-lesson-slug="{{ $nextLessonSlug }}"
     data-polyglot-first-lesson-url="{{ $firstLessonUrl }}"
     data-polyglot-is-final-lesson="{{ $isFinalLesson ? '1' : '0' }}"
     data-polyglot-lock-state="{{ $startsLockedPending ? 'pending' : 'ready' }}">
    @if(filled($courseSlug))
        <nav class="poly-crumbs" aria-label="{{ __('frontend.tests.course.course') }}">
            <a href="{{ $courseUrl }}" class="poly-crumbs__back">
                <span aria-hidden="true">&larr;</span>
                <span>{{ __('frontend.tests.course.back_to_course') }}</span>
            </a>
            <div class="poly-crumbs__meta">
                <span>{{ data_get($courseContext, 'course_name') }}</span>
                @if(filled(data_get($courseContext, 'lesson_order')))
                    <span class="poly-crumbs__sep" aria-hidden="true">/</span>
                    <span>{{ __('frontend.tests.course.lesson_number', ['number' => data_get($courseContext, 'lesson_order')]) }}@if(filled(data_get($courseContext, 'total_lessons'))) / {{ data_get($courseContext, 'total_lessons') }}@endif</span>
                @endif
                @if(filled(data_get($courseContext, 'topic')))
                    <span class="poly-crumbs__sep" aria-hidden="true">·</span>
                    <span class="poly-crumbs__topic">{{ data_get($courseContext, 'topic') }}</span>
                @endif
            </div>
            @if(filled(data_get($courseContext, 'previous_lesson_url')))
                <a href="{{ data_get($courseContext, 'previous_lesson_url') }}" class="poly-crumbs__prev">
                    <span aria-hidden="true">&larr;</span>
                    <span>{{ __('frontend.tests.course.previous_lesson') }}</span>
                </a>
            @endif
        </nav>
    @endif

    <div id="polyglot-compose-app" class="poly-stack">
        <header class="poly-hero">
            <div class="poly-hero__inner">
                <div class="poly-hero__lead">
                    <div class="poly-hero__eyebrow">
                        <span class="poly-pill">{{ __('frontend.tests.templates.compose.badge') }}</span>
                        <span class="poly-hero__progress" id="compose-question-index">
                            {{ __('frontend.tests.compose.current_sentence', ['current' => 1, 'total' => max(count($questionData ?? []), 1)]) }}
                        </span>
                    </div>
                    <h1 class="poly-hero__title" id="compose-test-name">{{ $test->name }}</h1>
                </div>

                <div class="poly-stats">
                    <article class="poly-stat poly-stat--primary">
                        <p class="poly-stat__label" id="compose-rating-title">{{ __('frontend.tests.compose.last_100_answers') }}</p>
                        <p class="poly-stat__value" id="compose-rating">0.0 / 5</p>
                        <p class="poly-stat__meta" id="compose-rating-meta">0 / {{ $completionWindow }}</p>
                    </article>
                    <article class="poly-stat">
                        <p class="poly-stat__label">{{ __('frontend.tests.compose.lesson_status') }}</p>
                        <p class="poly-stat__value poly-stat__value--small" id="compose-status">{{ __('frontend.tests.compose.in_progress') }}</p>
                        <p class="poly-stat__meta" id="compose-status-note">
                            {{ __('frontend.tests.compose.goal_note', ['rating' => number_format($completionRating, 1), 'count' => $completionWindow]) }}
                        </p>
                    </article>
                </div>
            </div>
        </header>

        <article class="poly-card">
            <div id="compose-empty-state" class="{{ count($questionData ?? []) > 0 ? 'hidden' : '' }}">
                <div class="poly-empty">
                    <p>{{ __('frontend.tests.compose.empty') }}</p>
                </div>
            </div>

            <div id="compose-course-pending" class="{{ count($questionData ?? []) > 0 && $startsLockedPending ? '' : 'hidden' }}">
                <div class="poly-empty">
                    <p>{{ __('frontend.tests.course.checking_access') }}</p>
                </div>
            </div>

            <div id="compose-course-lock" class="hidden"></div>

            <div id="compose-workspace" class="{{ count($questionData ?? []) > 0 && ! $startsLockedPending ? '' : 'hidden' }}">
                <div id="compose-feedback" class="hidden mb-3 sm:mb-5" aria-live="polite"></div>

                <div class="poly-prompt">
                    <div class="poly-prompt__eyebrow">
                        <span class="poly-prompt__label">{{ __('frontend.tests.compose.source_sentence') }}</span>
                        <span class="poly-prompt__punct" id="compose-punctuation"></span>
                    </div>
                    <div id="compose-source-text"></div>
                    <div class="poly-prompt__actions">
                        <button type="button" id="compose-theory-btn" class="hidden" aria-expanded="false" aria-controls="compose-theory-panel">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span id="compose-theory-btn-label">{{ __('frontend.tests.question.show_theory') }}</span>
                        </button>
                    </div>
                    <div id="compose-theory-panel" class="hidden"></div>
                    @if($isAdmin ?? false)
                        <div id="compose-source-seeder" class="hidden"></div>
                    @endif
                </div>

                <section class="poly-surface" aria-label="{{ __('frontend.tests.compose.build_translation') }}">
                    <div class="poly-surface__header">
                        <h2 class="poly-surface__heading">{{ __('frontend.tests.compose.build_translation') }}</h2>
                        <span class="poly-surface__hint">{{ __('frontend.tests.compose.keyboard_hint') }}</span>
                    </div>
                    <div id="compose-answer-zone"></div>
                    <div class="poly-divider"><span>{{ __('frontend.tests.compose.token_bank') }}</span></div>
                    <div id="compose-bank" aria-label="{{ __('frontend.tests.compose.token_bank') }}"></div>

                    <div id="compose-controls">
                        <div class="poly-actions">
                            <div class="poly-actions__primary">
                                <button type="button" data-action="check" class="poly-btn poly-btn--primary">
                                    {{ __('frontend.tests.compose.check') }}
                                </button>
                            </div>
                            <div class="poly-actions__secondary">
                                <button type="button" data-action="undo" class="poly-btn">
                                    {{ __('frontend.tests.compose.remove_last') }}
                                </button>
                                <button type="button" data-action="clear" class="poly-btn">
                                    {{ __('frontend.tests.compose.clear') }}
                                </button>
                                <button type="button" data-action="reset-progress" class="poly-btn poly-btn--ghost">
                                    {{ __('frontend.tests.compose.reset_progress') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div id="compose-course-completion"
                 data-polyglot-course-completion-kind="{{ $isFinalLesson ? 'course' : 'lesson' }}"
                 data-polyglot-continue-course-url="{{ $continueCourseUrl }}"
                 data-polyglot-continue-course-label="{{ $continueCourseLabel }}"
                 data-polyglot-all-levels-complete-label="{{ $allPolyglotLevelsCompleteLabel }}"
                 class="mt-5 hidden"></div>
        </article>
    </div>
</div>

@includeWhen(($isAdmin ?? false) && is_array($polyglotAdminDebugPayload ?? null), 'test-modes.partials.polyglot-admin-debug', [
    'debugPayload' => $polyglotAdminDebugPayload ?? null,
])

@php
    $polyglotProgressAssetVersion = is_file(public_path('js/polyglot-course-progress.js'))
        ? filemtime(public_path('js/polyglot-course-progress.js'))
        : null;
@endphp
<script type="module" src="{{ asset('js/polyglot-course-progress.js') }}@if($polyglotProgressAssetVersion)?v={{ $polyglotProgressAssetVersion }}@endif"></script>
<script>
@php
    $composeConfig = [
        'slug' => $test->slug,
        'rollingWindow' => $completionWindow,
        'minRating' => $completionRating,
        'courseSlug' => $courseSlug,
        'courseUrl' => $courseUrl,
        'courseName' => data_get($courseContext, 'course_name'),
        'lessonOrder' => data_get($courseContext, 'lesson_order'),
        'totalLessons' => data_get($courseContext, 'total_lessons'),
        'topic' => data_get($courseContext, 'topic'),
        'firstLessonSlug' => $firstLessonSlug,
        'firstLessonUrl' => $firstLessonUrl,
        'previousLessonSlug' => $previousLessonSlug,
        'previousLessonUrl' => data_get($courseContext, 'previous_lesson_url'),
        'nextLessonSlug' => $nextLessonSlug,
        'nextLessonUrl' => data_get($courseContext, 'next_lesson_url', filled($nextLessonSlug) ? localized_route('test.step-compose', $nextLessonSlug) : null),
        'isFinalLesson' => $isFinalLesson,
        'continueCourseUrl' => $continueCourseUrl,
        'continueCourseLabel' => $continueCourseLabel,
        'allPolyglotLevelsCompleteLabel' => $allPolyglotLevelsCompleteLabel,
        'interfaceLocale' => data_get($rawFilters, 'interface_locale', app()->getLocale()),
        'courseLessons' => data_get($courseContext, 'lessons', []),
        'shuffleQuestionOrder' => (bool) config('tests.compose_shuffle_enabled', true) && filled($courseSlug),
    ];
    $progressSyncPayload = [
        'progressUrl' => filled($courseSlug) ? localized_route('courses.progress.show', $courseSlug, false) : null,
        'attemptUrl' => filled($courseSlug) ? localized_route('courses.progress.attempt', $courseSlug, false) : null,
        'importUrl' => filled($courseSlug) ? localized_route('courses.progress.import', $courseSlug, false) : null,
        'csrfToken' => csrf_token(),
    ];
    if (($isAdmin ?? false) && filled($courseSlug)) {
        $progressSyncPayload['debugUrl'] = localized_route('courses.progress.debug', $courseSlug, false);
    }
@endphp
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
window.__POLYGLOT_COMPOSE_CONFIG__ = @json($composeConfig);
window.__POLYGLOT_PROGRESS_SYNC__ = @json($progressSyncPayload);
</script>
@include('components.saved-test-js-helpers')
<script>
(function () {
    let booted = false;

    function boot() {
        if (booted) {
            return;
        }

        if (!window.PolyglotCourseProgress) {
            return;
        }

        booted = true;

    const questions = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
        ? window.__INITIAL_JS_TEST_QUESTIONS__
        : [];
    const config = window.__POLYGLOT_COMPOSE_CONFIG__ || {};
    const rollingWindow = Number.isFinite(Number(config.rollingWindow)) ? Number(config.rollingWindow) : 100;
    const minRating = Number.isFinite(Number(config.minRating)) ? Number(config.minRating) : 4.5;
    const progressSync = window.__POLYGLOT_PROGRESS_SYNC__ || {};
    const adminDebugQuestionStatsKey = `polyglot_debug_question_stats:${config.courseSlug || 'course'}:${config.slug || 'lesson'}`;
    let serverCourseState = null;
    let serverAuthenticated = false;

    const pageRoot = document.getElementById('polyglot-compose-root');
    const root = document.getElementById('polyglot-compose-app');
    const workspace = document.getElementById('compose-workspace');
    const emptyState = document.getElementById('compose-empty-state');
    const pendingState = document.getElementById('compose-course-pending');
    const lockState = document.getElementById('compose-course-lock');
    const completionState = document.getElementById('compose-course-completion');

    if (!root) {
        return;
    }

    function cookieValue(name) {
        const prefix = `${name}=`;
        const item = String(document.cookie || '')
            .split(';')
            .map((cookie) => cookie.trim())
            .find((cookie) => cookie.startsWith(prefix));

        if (!item) {
            return '';
        }

        try {
            return decodeURIComponent(item.slice(prefix.length));
        } catch (error) {
            return item.slice(prefix.length);
        }
    }

    function csrfHeaders() {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
        };
        const xsrfToken = cookieValue('XSRF-TOKEN');
        const embeddedToken = String(
            progressSync.csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || ''
        ).trim();

        if (xsrfToken !== '') {
            headers['X-XSRF-TOKEN'] = xsrfToken;
        } else if (embeddedToken !== '') {
            headers['X-CSRF-TOKEN'] = embeddedToken;
        }

        return headers;
    }

    function serverHeaders() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...csrfHeaders(),
        };
    }

    function generateClientAttemptUuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return `attempt-${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
    }

    function normalizeText(value) {
        return String(value ?? '')
            .replace(/\s+/g, ' ')
            .replace(/\s+([?.!,;:])/g, '$1')
            .trim();
    }

    function normalizeCompare(value) {
        return normalizeText(value).toLowerCase();
    }

    function seederNamespaceAfterV3(value) {
        const normalized = String(value ?? '')
            .replace(/\//g, '\\')
            .split('\\')
            .map((segment) => segment.trim())
            .filter(Boolean);
        const v3Index = normalized.findIndex((segment) => segment.toLowerCase() === 'v3');

        if (v3Index === -1 || v3Index >= normalized.length - 1) {
            return '';
        }

        return normalized.slice(v3Index + 1).join('\\');
    }

    function sanitizeInteger(value, fallback = 0) {
        const normalized = Number.parseInt(String(value ?? ''), 10);

        return Number.isInteger(normalized) ? normalized : fallback;
    }

    function sanitizeCount(value) {
        return Math.max(0, sanitizeInteger(value, 0));
    }

    function sanitizeRollingResults(values) {
        if (!Array.isArray(values)) {
            return [];
        }

        return values
            .map((value) => Number(value))
            .filter((value) => value === 0 || value === 5)
            .slice(-rollingWindow);
    }

    function readJsonStorage(key) {
        if (!key) {
            return null;
        }

        try {
            const raw = window.localStorage.getItem(key);

            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            return null;
        }
    }

    function normalizeDebugPolicy(policy) {
        if (!policy || typeof policy !== 'object' || policy.enabled === false) {
            return null;
        }

        const rawRequiredAnswered = sanitizeCount(policy.required_answered ?? policy.requiredAnswered);
        const hasAnsweredPercent = policy.required_answered_percent !== undefined || policy.requiredAnsweredPercent !== undefined;
        const requiredAnsweredPercent = Math.min(
            100,
            Math.max(0, sanitizeInteger(
                policy.required_answered_percent ?? policy.requiredAnsweredPercent,
                hasAnsweredPercent ? 0 : Math.round(rawRequiredAnswered / Math.max(1, normalizedQuestions.length) * 100)
            ))
        );
        const requiredAnswered = requiredAnsweredPercent > 0
            ? Math.ceil(normalizedQuestions.length * requiredAnsweredPercent / 100)
            : rawRequiredAnswered;
        const rawRequiredCorrect = sanitizeCount(policy.required_correct ?? policy.requiredCorrect);
        const hasCorrectPercent = policy.required_correct_percent !== undefined || policy.requiredCorrectPercent !== undefined;
        const requiredCorrectPercent = Math.min(
            100,
            Math.max(0, sanitizeInteger(
                policy.required_correct_percent ?? policy.requiredCorrectPercent,
                hasCorrectPercent ? 0 : (requiredAnswered > 0 ? Math.round(rawRequiredCorrect / requiredAnswered * 100) : 0)
            ))
        );
        const requiredCorrect = requiredCorrectPercent > 0
            ? Math.ceil(requiredAnswered * requiredCorrectPercent / 100)
            : rawRequiredCorrect;
        const minimumRatingPercent = Math.min(
            100,
            Math.max(0, sanitizeInteger(policy.minimum_rating_percent ?? policy.minimumRatingPercent, Math.round(minRating * 20)))
        );

        return {
            required_answered: requiredAnswered,
            required_correct: requiredCorrect,
            required_answered_percent: requiredAnsweredPercent,
            required_correct_percent: requiredCorrectPercent,
            minimum_rating_percent: minimumRatingPercent,
            force_unlock_next: Boolean(policy.force_unlock_next ?? policy.forceUnlockNext),
        };
    }

    function activeDebugPolicy() {
        const adminDebugAttribute = ['data', 'polyglot', 'admin', 'debug'].join('-');
        const adminDebugEnabled = Boolean(progressSync.debugUrl || document.querySelector(`[${adminDebugAttribute}="1"]`));
        if (!adminDebugEnabled) {
            return null;
        }

        const courseSlug = String(config.courseSlug || '').trim();
        const lessonSlug = String(config.slug || '').trim();

        if (!courseSlug || !lessonSlug) {
            return null;
        }

        const lessonPolicy = normalizeDebugPolicy(
            readJsonStorage(`polyglot_debug_unlock_policy:${courseSlug}:${lessonSlug}`)
        );

        if (lessonPolicy) {
            return lessonPolicy;
        }

        return normalizeDebugPolicy(
            readJsonStorage(`polyglot_debug_unlock_policy:${courseSlug}:__course__`)
        );
    }

    function statusGoalNote() {
        if (state.progress.lesson_completed) {
            return testUi('compose.completed_banner');
        }

        const policy = activeDebugPolicy();
        if (!policy) {
            return testUi('compose.goal_note', { rating: minRating.toFixed(1), count: rollingWindow });
        }

        return testUi('compose.debug_goal_note', {
            count: policy.required_answered,
            correct: policy.required_correct,
            answered_percent: policy.required_answered_percent,
            correct_percent: policy.required_correct_percent,
            percent: policy.minimum_rating_percent,
            rating: (policy.minimum_rating_percent / 20).toFixed(1),
        });
    }

    function ratingCardSummary(rating) {
        const policy = activeDebugPolicy();

        if (!policy || state.progress.lesson_completed) {
            return {
                title: testUi('compose.last_100_answers'),
                value: `${rating.toFixed(1)} / 5`,
                meta: `${state.progress.rolling_results.length} / ${rollingWindow}`,
            };
        }

        return {
            title: testUi('compose.debug_policy_metric_title'),
            value: `${(policy.minimum_rating_percent / 20).toFixed(1)} / 5`,
            meta: testUi('compose.debug_policy_rating_meta', {
                current: state.progress.rolling_results.length,
                required: policy.required_answered,
                correct: policy.required_correct,
                answered_percent: policy.required_answered_percent,
                correct_percent: policy.required_correct_percent,
            }),
        };
    }

    function sanitizeTokenValue(value) {
        return String(value ?? '').trim();
    }

    function createTokenInstance(id, value, isCorrect = false, correctIndex = null) {
        const sanitizedValue = sanitizeTokenValue(value);
        if (sanitizedValue === '') {
            return null;
        }

        return {
            id: String(id),
            value: sanitizedValue,
            isCorrect: Boolean(isCorrect),
            correctIndex: Number.isInteger(correctIndex) ? correctIndex : null,
        };
    }

    function buildFallbackTokenBank(correctTokenValues, tokensPool) {
        const instances = [];
        const correctLookup = {};

        correctTokenValues.forEach((token, index) => {
            const instance = createTokenInstance(`c${index + 1}`, token, true, index);
            if (!instance) {
                return;
            }

            correctLookup[instance.value] = true;
            instances.push(instance);
        });

        let distractorIndex = 1;
        (Array.isArray(tokensPool) ? tokensPool : []).forEach((token) => {
            const value = sanitizeTokenValue(token);
            if (value === '' || correctLookup[value]) {
                return;
            }

            const instance = createTokenInstance(`d${distractorIndex}`, value, false, null);
            if (!instance) {
                return;
            }

            distractorIndex += 1;
            instances.push(instance);
        });

        return instances;
    }

    function sanitizeTokenBank(rawBank, fallbackCorrectValues, fallbackPool) {
        const tokenBank = Array.isArray(rawBank)
            ? rawBank
                .map((token, index) => {
                    const fallbackId = `t${index + 1}`;
                    const isCorrect = Boolean(token?.isCorrect ?? token?.is_correct);
                    const correctIndex = Number.isInteger(token?.correctIndex)
                        ? token.correctIndex
                        : sanitizeInteger(token?.correct_index, -1);

                    return createTokenInstance(
                        token?.id ?? fallbackId,
                        token?.value,
                        isCorrect,
                        correctIndex >= 0 ? correctIndex : null
                    );
                })
                .filter(Boolean)
            : [];

        return tokenBank.length > 0
            ? tokenBank
            : buildFallbackTokenBank(fallbackCorrectValues, fallbackPool);
    }

    function sanitizeQuestion(item, index = 0) {
        const punctuation = item?.punctuation === '?' ? '?' : '.';
        const fallbackCorrectValues = Array.isArray(item?.correctTokenValues)
            ? item.correctTokenValues.map(sanitizeTokenValue).filter(Boolean)
            : (Array.isArray(item?.correctTokens)
                ? item.correctTokens.map(sanitizeTokenValue).filter(Boolean)
                : []);
        const fallbackPool = Array.isArray(item?.tokensPool)
            ? item.tokensPool.map(sanitizeTokenValue).filter(Boolean)
            : [];
        const tokenBank = sanitizeTokenBank(item?.tokenBank, fallbackCorrectValues, fallbackPool);
        const tokenMap = tokenBank.reduce((accumulator, token) => {
            accumulator[token.id] = token;

            return accumulator;
        }, {});
        const derivedCorrectInstances = tokenBank
            .filter((token) => token.isCorrect)
            .sort((left, right) => {
                const leftIndex = Number.isInteger(left.correctIndex) ? left.correctIndex : Number.MAX_SAFE_INTEGER;
                const rightIndex = Number.isInteger(right.correctIndex) ? right.correctIndex : Number.MAX_SAFE_INTEGER;

                return leftIndex - rightIndex;
            });
        const providedCorrectTokenIds = Array.isArray(item?.correctTokenIds)
            ? item.correctTokenIds
                .map((id) => String(id ?? ''))
                .filter((id) => id !== '' && Object.prototype.hasOwnProperty.call(tokenMap, id))
            : [];
        const correctTokenIds = providedCorrectTokenIds.length > 0
            ? providedCorrectTokenIds
            : derivedCorrectInstances.map((token) => token.id);
        const correctTokenValues = correctTokenIds
            .map((id) => tokenMap[id]?.value ?? '')
            .filter(Boolean);
        const sourceTextUk = String(item?.sourceTextUk ?? item?.question ?? '').trim();
        const seederNamespace = seederNamespaceAfterV3(item?.tech_info?.seeder?.class);

        if (sourceTextUk === '' || correctTokenValues.length === 0 || tokenBank.length === 0) {
            return null;
        }

        const theoryBlocks = Array.isArray(item?.theory_blocks) && item.theory_blocks.length
            ? item.theory_blocks
            : (item?.theory_block ? [item.theory_block] : []);

        return {
            id: String(item?.id ?? ''),
            uuid: String(item?.uuid ?? ''),
            position: index + 1,
            level: item?.level ? String(item.level) : '',
            sourceTextUk,
            correctTokens: correctTokenValues,
            correctTokenValues,
            correctTokenIds,
            tokenBank,
            tokenMap,
            correctText: normalizeText(item?.correctText || `${correctTokenValues.join(' ')}${punctuation}`),
            hintUk: item?.hintUk ? String(item.hintUk).trim() : '',
            explanations: item?.explanations && typeof item.explanations === 'object' ? item.explanations : {},
            punctuation,
            seederNamespace,
            theoryBlocks,
        };
    }

    let normalizedQuestions = questions
        .map(sanitizeQuestion)
        .filter(Boolean);

    if (normalizedQuestions.length === 0) {
        workspace?.classList.add('hidden');
        pendingState?.classList.add('hidden');
        emptyState?.classList.remove('hidden');
        return;
    }

    const courseStore = config.courseSlug && window.PolyglotCourseProgress
        ? window.PolyglotCourseProgress.createStore(
            config.courseSlug,
            Array.isArray(config.courseLessons) ? config.courseLessons : []
        )
        : null;
    const progressStore = window.PolyglotCourseProgress.createLessonStore({
        lessonSlug: config.slug,
        courseSlug: config.courseSlug,
        questionCount: normalizedQuestions.length,
        rollingWindow,
        minRating,
        courseStore,
        sourceId: courseStore?.sourceId,
    });

    function reshuffleQuestionOrder() {
        if (!config.shuffleQuestionOrder || normalizedQuestions.length < 2) {
            return;
        }

        shuffle(normalizedQuestions);
        normalizedQuestions = normalizedQuestions.map((question, index) => ({
            ...question,
            position: index + 1,
        }));
    }

    const state = {
        progress: progressStore.read(),
        selectedTokenIds: [],
        bankOrder: [],
        feedback: null,
        autoAdvanceTimer: null,
        lastTrackedQuestionKey: null,
        nextLessonPromptShown: false,
    };

    function currentCourseState() {
        return serverCourseState || (courseStore ? courseStore.read() : null);
    }

    function currentCourseLesson(courseState) {
        if (!courseStore || !courseState || !courseState.lessons) {
            return null;
        }

        return courseState.lessons[config.slug] || null;
    }

    function lessonIsLocked(courseState) {
        const lessonState = currentCourseLesson(courseState);

        return Boolean(courseStore && (!lessonState || !lessonState.unlocked));
    }

    function lessonNameBySlug(slug) {
        if (!courseStore || !slug) {
            return '';
        }

        return courseStore.findLesson(slug)?.name || '';
    }

    function localLessonAnswered(progress) {
        if (!progress || typeof progress !== 'object') {
            return 0;
        }

        const totalAttempts = Number(progress.total_attempts || 0);
        const rollingCount = Array.isArray(progress.rolling_results) ? progress.rolling_results.length : 0;

        return Math.max(Number.isFinite(totalAttempts) ? totalAttempts : 0, rollingCount);
    }

    function serverLessonAnswered(progress) {
        const lessonEntry = progress?.lessons?.[config.slug] || null;
        const lessonProgress = progress?.lesson_progress?.[config.slug] || null;
        const answeredCount = Number(lessonEntry?.answered_count || 0);
        const progressTotal = Number(lessonProgress?.total_attempts || 0);
        const rollingCount = Array.isArray(lessonProgress?.rolling_results) ? lessonProgress.rolling_results.length : 0;

        return Math.max(
            Number.isFinite(answeredCount) ? answeredCount : 0,
            Number.isFinite(progressTotal) ? progressTotal : 0,
            rollingCount
        );
    }

    function localProgressPayload() {
        const lessonProgress = progressStore.read();

        if (localLessonAnswered(lessonProgress) <= 0 && !lessonProgress.lesson_completed) {
            return null;
        }

        return {
            course: courseStore ? courseStore.read() : null,
            lesson_progress: {
                [config.slug]: lessonProgress,
            },
        };
    }

    async function maybeImportLocalProgress(progress) {
        if (!progressSync.importUrl) {
            return null;
        }

        const localProgress = localProgressPayload();
        const lessonProgress = localProgress?.lesson_progress?.[config.slug] || null;

        if (!lessonProgress || localLessonAnswered(lessonProgress) <= serverLessonAnswered(progress)) {
            return null;
        }

        try {
            const response = await fetch(progressSync.importUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: serverHeaders(),
                body: JSON.stringify({
                    local_progress: localProgress,
                }),
            });

            if (!response.ok) {
                return null;
            }

            const payload = await response.json();

            return payload?.authenticated && payload.progress ? payload.progress : null;
        } catch (error) {
            return null;
        }
    }

    function applyServerProgress(progress) {
        if (!progress || typeof progress !== 'object') {
            return;
        }

        serverCourseState = progress;
        serverAuthenticated = true;

        const lessonProgress = progress.lesson_progress?.[config.slug];
        if (lessonProgress) {
            mergeServerQuestionStats(lessonProgress.question_stats);
            state.progress = progressStore.normalize(lessonProgress);
        }
    }

    async function hydrateServerProgress() {
        if (!progressSync.progressUrl) {
            return;
        }

        try {
            const response = await fetch(progressSync.progressUrl, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            if (!payload?.authenticated || !payload.progress) {
                serverAuthenticated = false;
                serverCourseState = null;
                return;
            }

            applyServerProgress(await maybeImportLocalProgress(payload.progress) || payload.progress);
        } catch (error) {
            serverAuthenticated = false;
            serverCourseState = null;
        }
    }

    async function postServerAttempt(result, question, submitted, progressSnapshot) {
        if (!serverAuthenticated || !progressSync.attemptUrl) {
            return;
        }

        try {
            const response = await fetch(progressSync.attemptUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: serverHeaders(),
                body: JSON.stringify({
                    lesson_slug: config.slug,
                    question_uuid: question?.uuid || null,
                    rating: result,
                    is_correct: result >= minRating,
                    client_attempt_uuid: generateClientAttemptUuid(),
                    answer_payload: {
                        source: 'compose_tokens',
                        current_queue_index: progressSnapshot.current_queue_index,
                        selected_token_ids: state.selectedTokenIds,
                        submitted_answer: submitted,
                        correct_answer: question?.correctText || null,
                    },
                }),
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            if (!payload?.authenticated) {
                serverAuthenticated = false;
                serverCourseState = null;
                return;
            }

            const previousCourseState = serverCourseState;
            if (payload.course_progress) {
                serverCourseState = payload.course_progress;
            }

            if (payload.lesson_progress) {
                const previousProgress = { ...state.progress };
                const localQueueIndex = state.progress.current_queue_index;
                const serverLessonProgress = progressStore.normalize(payload.lesson_progress);

                mergeServerQuestionStats(payload.lesson_progress.question_stats);
                state.progress = {
                    ...serverLessonProgress,
                    current_queue_index: state.autoAdvanceTimer !== null
                        ? localQueueIndex
                        : serverLessonProgress.current_queue_index,
                };

                render();
                offerNextLessonIfJustUnlocked(previousProgress, state.progress);
            }

            offerNextLessonIfCourseUnlocked(previousCourseState, serverCourseState);
        } catch (error) {
            serverAuthenticated = false;
            serverCourseState = null;
        }
    }

    function actionLinkMarkup(url, label, variant = 'solid') {
        if (!url || !label) {
            return '';
        }

        const style = variant === 'solid'
            ? 'background: var(--accent); color: white; border-color: var(--accent);'
            : 'border-color: var(--line); color: var(--text);';

        return `
            <a href="${html(url)}"
               class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold transition hover:opacity-95"
               style="${style}">
                ${html(label)}
            </a>
        `;
    }

    function actionButtonMarkup(action, label, variant = 'soft') {
        if (!action || !label) {
            return '';
        }

        const style = variant === 'solid'
            ? 'background: var(--accent); color: white; border-color: var(--accent);'
            : 'border-color: var(--line); color: var(--text);';

        return `
            <button type="button"
               data-action="${html(action)}"
               class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-extrabold transition hover:opacity-95"
               style="${style}">
                ${html(label)}
            </button>
        `;
    }

    function lockMarkup() {
        const previousLessonName = lessonNameBySlug(config.previousLessonSlug);
        const previousLabel = previousLessonName || testUi('course.previous_lesson');
        const previousLink = config.previousLessonUrl
            ? actionLinkMarkup(config.previousLessonUrl, previousLabel, 'soft')
            : '';

        return `
            <div class="rounded-[24px] border px-6 py-8 text-center surface-card" style="border-color: var(--line);">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    ${html(testUi('course.lesson_locked'))}
                </span>
                <h3 class="mt-4 font-display text-2xl font-extrabold">${html(testUi('course.complete_previous_lesson_first'))}</h3>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-7" style="color: var(--muted);">
                    ${html(testUi('course.locked_lesson_note'))}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                    ${previousLink}
                </div>
            </div>
        `;
    }

    function completionMarkup() {
        if (!state.progress.lesson_completed) {
            return '';
        }

        const allLevelsCompleteLabel = String(config.allPolyglotLevelsCompleteLabel || completionState?.dataset?.polyglotAllLevelsCompleteLabel || '').trim();

        if (config.nextLessonUrl && config.nextLessonSlug) {
            return `
                <div class="rounded-[24px] border px-5 py-5" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: #17603a;">${html(testUi('course.lesson_unlocked'))}</p>
                    <h3 class="mt-2 font-display text-2xl font-extrabold" style="color: #17603a;">${html(testUi('course.next_lesson_available'))}</h3>
                    <p class="mt-2 text-sm leading-6" style="color: #17603a;">${html(testUi('compose.completed_banner'))}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        ${actionLinkMarkup(config.nextLessonUrl, testUi('course.next_lesson'), 'solid')}
                        ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                    </div>
                </div>
            `;
        }

        return `
            <div class="rounded-[24px] border px-5 py-5" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: #17603a;">${html(testUi('course.course_completed'))}</p>
                <h3 class="mt-2 font-display text-2xl font-extrabold" style="color: #17603a;">${html(testUi('course.course_completed_title'))}</h3>
                <p class="mt-2 text-sm leading-6" style="color: #17603a;">${html(testUi('course.course_completed_note'))}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    ${actionLinkMarkup(config.firstLessonUrl || config.courseUrl, testUi('course.repeat_course'), 'solid')}
                    ${actionLinkMarkup(config.courseUrl, testUi('course.back_to_course'), 'soft')}
                    ${config.continueCourseUrl ? actionLinkMarkup(config.continueCourseUrl, config.continueCourseLabel || '', 'soft') : ''}
                    ${!config.continueCourseUrl && allLevelsCompleteLabel ? `<span class="inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-bold" style="border-color: #17603a; color: #17603a;">${html(allLevelsCompleteLabel)}</span>` : ''}
                    ${actionButtonMarkup('restart-course', testUi('course.restart_course'), 'soft')}
                </div>
            </div>
        `;
    }

    function showNextLessonToast(targetUrl) {
        if (typeof document === 'undefined') {
            return;
        }

        const existing = document.getElementById('polyglot-next-lesson-toast');
        if (existing) {
            existing.remove();
        }

        const toast = document.createElement('div');
        toast.id = 'polyglot-next-lesson-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.style.cssText = [
            'position:fixed',
            'top:50%',
            'left:50%',
            'transform:translate(-50%, -50%) scale(0.96)',
            'z-index:9999',
            'width:min(420px, calc(100vw - 32px))',
            'padding:24px 28px',
            'border-radius:24px',
            'border:1px solid #b8e3c7',
            'background:linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%)',
            'box-shadow:0 20px 60px rgb(15 23 42 / 28%)',
            'color:#17603a',
            'font-size:14px',
            'line-height:1.4',
            'opacity:0',
            'transition:opacity 220ms ease, transform 220ms ease',
        ].join(';');

        toast.innerHTML = `
            <div style="display:flex;align-items:flex-start;gap:10px;">
                <div style="flex:1;">
                    <div style="font-size:11px;font-weight:800;letter-spacing:0.22em;text-transform:uppercase;">${html(testUi('course.lesson_unlocked'))}</div>
                    <div style="margin-top:4px;font-weight:700;">${html(testUi('course.next_lesson_available'))}</div>
                    <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
                        <a href="${html(targetUrl)}" data-polyglot-next-lesson-go style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:8px 16px;font-size:13px;font-weight:800;background:#17603a;color:#fff;text-decoration:none;">${html(testUi('course.next_lesson'))}</a>
                        <button type="button" data-polyglot-next-lesson-dismiss style="border:1px solid #b8e3c7;background:transparent;border-radius:999px;padding:8px 14px;font-size:13px;font-weight:700;color:#17603a;cursor:pointer;">${html(testUi('course.toast_dismiss'))}</button>
                    </div>
                </div>
                <button type="button" aria-label="${html(testUi('course.toast_dismiss'))}" data-polyglot-next-lesson-dismiss style="border:0;background:transparent;color:#17603a;font-size:18px;font-weight:800;cursor:pointer;line-height:1;padding:0 4px;">×</button>
            </div>
        `;

        document.body.appendChild(toast);

        window.requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translate(-50%, -50%) scale(1)';
        });

        const dismiss = () => {
            if (!toast.isConnected) {
                return;
            }
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -50%) scale(0.96)';
            window.setTimeout(() => {
                toast.remove();
            }, 240);
        };

        toast.querySelectorAll('[data-polyglot-next-lesson-dismiss]').forEach((btn) => {
            btn.addEventListener('click', dismiss);
        });

        const goLink = toast.querySelector('[data-polyglot-next-lesson-go]');
        if (goLink) {
            goLink.addEventListener('click', () => {
                dismiss();
            });
        }

        window.setTimeout(dismiss, 15000);
    }

    function offerNextLessonIfJustUnlocked(previousProgress, nextProgress) {
        if (state.nextLessonPromptShown || !config.nextLessonUrl || !config.nextLessonSlug) {
            return;
        }

        if (previousProgress?.lesson_completed || !nextProgress?.lesson_completed) {
            return;
        }

        state.nextLessonPromptShown = true;

        window.setTimeout(() => {
            showNextLessonToast(config.nextLessonUrl);
        }, 120);
    }

    function isNextLessonUnlockedInCourseState(courseState) {
        if (!courseState || !config.nextLessonSlug) {
            return false;
        }

        const lessons = courseState.lessons && typeof courseState.lessons === 'object'
            ? courseState.lessons
            : null;
        if (lessons && lessons[config.nextLessonSlug]) {
            return Boolean(lessons[config.nextLessonSlug].unlocked);
        }

        const unlockedList = Array.isArray(courseState.unlocked_lessons) ? courseState.unlocked_lessons : null;
        if (unlockedList) {
            return unlockedList.includes(config.nextLessonSlug);
        }

        return false;
    }

    function offerNextLessonIfCourseUnlocked(previousCourseState, nextCourseState) {
        if (state.nextLessonPromptShown || !config.nextLessonUrl || !config.nextLessonSlug) {
            return;
        }

        if (!previousCourseState) {
            return;
        }

        const wasUnlocked = isNextLessonUnlockedInCourseState(previousCourseState);
        const isUnlocked = isNextLessonUnlockedInCourseState(nextCourseState);

        if (wasUnlocked || !isUnlocked) {
            return;
        }

        state.nextLessonPromptShown = true;

        window.setTimeout(() => {
            showNextLessonToast(config.nextLessonUrl);
        }, 120);
    }

    function refreshCourseUi() {
        const courseState = currentCourseState();

        if (lessonIsLocked(courseState)) {
            if (pageRoot) {
                pageRoot.dataset.polyglotLockState = 'locked';
            }

            workspace?.classList.add('hidden');
            pendingState?.classList.add('hidden');
            completionState?.classList.add('hidden');
            lockState.innerHTML = lockMarkup();
            lockState.classList.remove('hidden');

            return {
                courseState,
                locked: true,
            };
        }

        lockState.innerHTML = '';
        lockState.classList.add('hidden');
        if (pageRoot) {
            pageRoot.dataset.polyglotLockState = 'ready';
        }
        pendingState?.classList.add('hidden');
        workspace?.classList.remove('hidden');

        if (state.progress.lesson_completed) {
            completionState.innerHTML = completionMarkup();
            completionState.classList.remove('hidden');
        } else {
            completionState.innerHTML = '';
            completionState.classList.add('hidden');
        }

        return {
            courseState,
            locked: false,
        };
    }

    function clampQueueIndex() {
        state.progress = progressStore.normalize(state.progress);
    }

    function currentQuestion() {
        clampQueueIndex();

        return normalizedQuestions[state.progress.current_queue_index] || normalizedQuestions[0];
    }

    function adminDebugEnabled() {
        const adminDebugAttribute = ['data', 'polyglot', 'admin', 'debug'].join('-');

        return Boolean(progressSync.debugUrl || document.querySelector(`[${adminDebugAttribute}="1"]`));
    }

    function readQuestionStats() {
        if (!adminDebugEnabled()) {
            return {};
        }

        return readJsonStorage(adminDebugQuestionStatsKey) || {};
    }

    function writeQuestionStats(stats) {
        if (!adminDebugEnabled()) {
            return;
        }

        try {
            window.localStorage.setItem(adminDebugQuestionStatsKey, JSON.stringify(stats));
            window.dispatchEvent(new CustomEvent('polyglot:admin-debug-question-stats-updated', {
                detail: {
                    key: adminDebugQuestionStatsKey,
                    stats,
                },
            }));
        } catch (error) {
            // localStorage can be unavailable in private contexts.
        }
    }

    function questionStatsEntry(stats, question) {
        const position = sanitizeCount(question?.position);
        const uuid = String(question?.uuid || '').trim();
        const id = String(question?.id || '').trim();
        const key = position > 0
            ? `position:${position}`
            : (uuid !== '' ? uuid : (id !== '' ? `id:${id}` : ''));

        if (key === '') {
            return null;
        }

        const aliasKeys = [
            key,
            position > 0 ? `position:${position}` : '',
            uuid,
            id !== '' ? `id:${id}` : '',
        ].filter((value, index, values) => value !== '' && values.indexOf(value) === index);
        const existingKey = aliasKeys.find((alias) => stats[alias]);
        const existing = existingKey ? stats[existingKey] : {};

        const normalizedEntry = {
            uuid: uuid || existing.uuid || '',
            id: id || existing.id || '',
            position: position > 0 ? position : (sanitizeCount(existing.position) || null),
            shown: sanitizeCount(existing.shown),
            correct: sanitizeCount(existing.correct),
            incorrect: sanitizeCount(existing.incorrect),
            last_seen_at: existing.last_seen_at || null,
            last_answered_at: existing.last_answered_at || null,
        };

        stats[key] = normalizedEntry;
        aliasKeys
            .filter((alias) => alias !== key)
            .forEach((alias) => {
                stats[alias] = normalizedEntry;
            });

        return stats[key];
    }

    function questionStatsKey(question) {
        const position = sanitizeCount(question?.position);
        const uuid = String(question?.uuid || '').trim();
        const id = String(question?.id || '').trim();

        if (position > 0) {
            return `position:${position}`;
        }

        if (uuid !== '') {
            return uuid;
        }

        return id !== '' ? `id:${id}` : '';
    }

    function trackQuestionShown(question) {
        const key = questionStatsKey(question);

        if (!adminDebugEnabled() || key === '' || state.lastTrackedQuestionKey === key) {
            return;
        }

        const stats = readQuestionStats();
        const entry = questionStatsEntry(stats, question);
        if (!entry) {
            return;
        }

        entry.shown = sanitizeCount(entry.shown) + 1;
        entry.last_seen_at = new Date().toISOString();
        state.lastTrackedQuestionKey = key;
        writeQuestionStats(stats);
    }

    function trackQuestionAttempt(question, wasCorrect) {
        if (!adminDebugEnabled()) {
            return;
        }

        const stats = readQuestionStats();
        const entry = questionStatsEntry(stats, question);
        if (!entry) {
            return;
        }

        if (sanitizeCount(entry.shown) <= 0) {
            entry.shown = 1;
            entry.last_seen_at = new Date().toISOString();
        }

        if (wasCorrect) {
            entry.correct = sanitizeCount(entry.correct) + 1;
        } else {
            entry.incorrect = sanitizeCount(entry.incorrect) + 1;
        }

        entry.last_answered_at = new Date().toISOString();
        writeQuestionStats(stats);
    }

    function mergeServerQuestionStats(serverStats) {
        if (!adminDebugEnabled() || !serverStats || typeof serverStats !== 'object') {
            return;
        }

        const stats = readQuestionStats();
        Object.entries(serverStats).forEach(([serverKey, serverEntry]) => {
            const uuid = String(serverEntry?.uuid || serverKey || '').trim();
            const question = normalizedQuestions.find((item) => item.uuid === uuid) || { uuid };
            const entry = questionStatsEntry(stats, question);

            if (!entry) {
                return;
            }

            entry.shown = Math.max(sanitizeCount(entry.shown), sanitizeCount(serverEntry?.shown));
            entry.correct = Math.max(sanitizeCount(entry.correct), sanitizeCount(serverEntry?.correct));
            entry.incorrect = Math.max(sanitizeCount(entry.incorrect), sanitizeCount(serverEntry?.incorrect));
            entry.last_seen_at = serverEntry?.last_seen_at || entry.last_seen_at || null;
            entry.last_answered_at = serverEntry?.last_answered_at || entry.last_answered_at || null;

            if (uuid !== '') {
                stats[uuid] = entry;
            }
        });

        writeQuestionStats(stats);
    }

    function persistProgress() {
        state.progress = progressStore.write(state.progress);
    }

    function sentenceFromTokenIds(question, tokenIds) {
        const values = tokenIds
            .map((tokenId) => question.tokenMap[tokenId]?.value ?? '')
            .filter(Boolean);

        return normalizeText(`${values.join(' ')}${question.punctuation || '.'}`);
    }

    // Theory hint helpers — render content blocks attached to the current
    // question through the question_theory_text_blocks pivot.
    // body fields originate in trusted server-side theory seeders and may
    // include intentional <strong>/<em> formatting; render raw.
    function getComposeTheoryBlocks(question) {
        if (!question || !Array.isArray(question.theoryBlocks)) {
            return [];
        }
        return question.theoryBlocks;
    }

    function renderComposeTheoryBlockContent(block) {
        let content = '';
        try {
            const body = typeof block.body === 'string' ? JSON.parse(block.body) : block.body;

            if (body && body.title) {
                content += `<h4 class="font-semibold text-emerald-900 mb-2">${body.title}</h4>`;
            }
            if (body && body.intro) {
                content += `<p class="text-sm text-emerald-800 mb-3">${body.intro}</p>`;
            }
            if (body && Array.isArray(body.sections)) {
                body.sections.forEach((section) => {
                    content += `<div class="mb-3">`;
                    if (section.label) {
                        content += `<p class="text-sm font-semibold text-emerald-700">${section.label}</p>`;
                    }
                    if (section.description) {
                        content += `<p class="text-sm text-emerald-800">${section.description}</p>`;
                    }
                    if (Array.isArray(section.examples)) {
                        content += `<ul class="mt-1 space-y-1">`;
                        section.examples.forEach((ex) => {
                            content += `<li class="text-sm"><span class="text-emerald-900 font-medium">${ex.en || ''}</span>`;
                            if (ex.ua) content += ` — <span class="text-emerald-700">${ex.ua}</span>`;
                            content += `</li>`;
                        });
                        content += `</ul>`;
                    }
                    if (section.note) {
                        content += `<p class="text-xs text-emerald-600 mt-1">${section.note}</p>`;
                    }
                    content += `</div>`;
                });
            }
            if (body && Array.isArray(body.items)) {
                content += `<ul class="list-disc list-inside space-y-1">`;
                body.items.forEach((item) => {
                    if (typeof item === 'string') {
                        content += `<li class="text-sm text-emerald-800">${item}</li>`;
                    } else if (item && item.title) {
                        content += `<li class="text-sm"><span class="font-medium text-emerald-900">${item.title}</span>`;
                        if (item.subtitle) content += ` — <span class="text-emerald-700">${item.subtitle}</span>`;
                        content += `</li>`;
                    }
                });
                content += `</ul>`;
            }
            if (body && Array.isArray(body.rows)) {
                content += `<ul class="space-y-1">`;
                body.rows.forEach((row) => {
                    if (!row) return;
                    content += `<li class="text-sm">`;
                    if (row.en) content += `<span class="text-emerald-900 font-medium">${row.en}</span>`;
                    if (row.ua) content += `${row.en ? ' — ' : ''}<span class="text-emerald-700">${row.ua}</span>`;
                    if (row.note) content += `<div class="text-xs text-emerald-600">${row.note}</div>`;
                    content += `</li>`;
                });
                content += `</ul>`;
                if (body.warning) {
                    content += `<p class="text-xs text-emerald-700 mt-2">${body.warning}</p>`;
                }
            }
            if (body && Array.isArray(body.rules)) {
                content += `<ul class="space-y-1">`;
                body.rules.forEach((rule) => {
                    if (!rule) return;
                    content += `<li class="text-sm">`;
                    if (rule.label) content += `<span class="font-semibold text-emerald-700">${rule.label}</span> — `;
                    if (rule.text) content += `<span class="text-emerald-800">${rule.text}</span>`;
                    if (rule.example) content += `<div class="text-xs text-emerald-600 mt-0.5">${rule.example}</div>`;
                    content += `</li>`;
                });
                content += `</ul>`;
            }
        } catch (e) {
            content = `<div class="text-sm text-emerald-800">${block.body || ''}</div>`;
        }
        return content;
    }

    function renderComposeTheoryPanel(question) {
        const panel = document.getElementById('compose-theory-panel');
        if (!panel) return;

        const blocks = getComposeTheoryBlocks(question);
        if (!blocks.length) {
            panel.innerHTML = '';
            return;
        }

        const blocksHtml = blocks.map((block, blockIdx) => {
            const content = renderComposeTheoryBlockContent(block);
            const separator = blockIdx > 0
                ? '<div class="my-3 border-t border-emerald-200/70"></div>'
                : '';
            const levelBadge = block.level
                ? `<span class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-200 text-emerald-800">${html(block.level)}</span>`
                : '';
            const heading = blockIdx === 0
                ? `<div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="text-sm font-semibold text-emerald-900">${html(testUi('question.theory'))}${blocks.length > 1 ? ` (${blocks.length})` : ''}</span>
                        ${levelBadge}
                    </div>`
                : (block.level
                    ? `<div class="flex items-center gap-2 mb-1"><span class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-200 text-emerald-800">${html(block.level)}</span></div>`
                    : '');
            return `${separator}${heading}${content}`;
        }).join('');

        panel.innerHTML = `
            <div class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200">
                ${blocksHtml}
            </div>
        `;
    }

    function renderComposeTheoryControls(question) {
        const btn = document.getElementById('compose-theory-btn');
        const panel = document.getElementById('compose-theory-panel');
        const blocks = getComposeTheoryBlocks(question);
        const hasBlocks = blocks.length > 0;

        if (btn) {
            btn.classList.toggle('hidden', !hasBlocks);
            btn.setAttribute('aria-expanded', 'false');
        }
        if (panel) {
            panel.classList.add('hidden');
            panel.innerHTML = '';
        }
    }

    function toggleComposeTheoryPanel() {
        const panel = document.getElementById('compose-theory-panel');
        const btn = document.getElementById('compose-theory-btn');
        if (!panel) return;

        const question = currentQuestion();
        if (!question) return;

        // Defensive: if there are no theory blocks for this question, do
        // nothing — the button is already hidden by renderComposeTheoryControls
        // but stale clicks should still no-op rather than open an empty panel.
        if (getComposeTheoryBlocks(question).length === 0) {
            return;
        }

        if (panel.classList.contains('hidden')) {
            renderComposeTheoryPanel(question);
            panel.classList.remove('hidden');
            if (btn) btn.setAttribute('aria-expanded', 'true');
        } else {
            panel.classList.add('hidden');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    }

    function ratingValue() {
        if (state.progress.rolling_results.length === 0) {
            return 0;
        }

        const total = state.progress.rolling_results.reduce((sum, value) => sum + Number(value || 0), 0);

        return total / state.progress.rolling_results.length;
    }

    function shuffleBank(question) {
        state.bankOrder = question.tokenBank.map((token) => token.id);
        shuffle(state.bankOrder);
    }

    function availableBankTokenIds(question) {
        const selectedIds = new Set(state.selectedTokenIds);

        return state.bankOrder.filter((tokenId) => !selectedIds.has(tokenId) && question.tokenMap[tokenId]);
    }

    function relevantExplanation(question) {
        if (!question.explanations || typeof question.explanations !== 'object') {
            return '';
        }

        const selectedValues = state.selectedTokenIds
            .map((tokenId) => question.tokenMap[tokenId]?.value ?? '')
            .filter(Boolean);

        const mismatchedToken = selectedValues.find((token, index) => {
            const expected = question.correctTokenValues[index];
            if (expected && normalizeCompare(expected) === normalizeCompare(token)) {
                return false;
            }

            return Object.prototype.hasOwnProperty.call(question.explanations, token);
        });

        if (mismatchedToken) {
            return String(question.explanations[mismatchedToken] || '').trim();
        }

        const explainedToken = selectedValues.find((token) => Object.prototype.hasOwnProperty.call(question.explanations, token));

        return explainedToken
            ? String(question.explanations[explainedToken] || '').trim()
            : '';
    }

    function clearAutoAdvance() {
        if (state.autoAdvanceTimer !== null) {
            window.clearTimeout(state.autoAdvanceTimer);
            state.autoAdvanceTimer = null;
        }
    }

    function resetCurrentAnswer(reshuffle = false) {
        state.selectedTokenIds = [];
        state.feedback = null;

        if (reshuffle || state.bankOrder.length === 0) {
            shuffleBank(currentQuestion());
        }
    }

    function advanceQuestion() {
        state.progress.current_queue_index = (state.progress.current_queue_index + 1) % normalizedQuestions.length;
        persistProgress();
        resetCurrentAnswer(true);
        render();
    }

    function markAttempt(result, question, submitted) {
        const previousProgress = { ...state.progress };
        trackQuestionAttempt(question, result === 5);
        state.progress = progressStore.markAttempt(state.progress, result === 5);
        offerNextLessonIfJustUnlocked(previousProgress, state.progress);
        postServerAttempt(result, question, submitted, state.progress);
    }

    function resetLessonProgress() {
        if (serverAuthenticated) {
            return;
        }

        clearAutoAdvance();
        reshuffleQuestionOrder();
        state.progress = progressStore.reset();
        state.lastTrackedQuestionKey = null;
        resetCurrentAnswer(true);
        render();
    }

    function restartCourse() {
        if (serverAuthenticated) {
            if (config.courseUrl) {
                window.location.assign(config.courseUrl);
            }

            return;
        }

        clearAutoAdvance();

        if (courseStore && typeof courseStore.reset === 'function') {
            courseStore.reset();
        } else {
            progressStore.reset();
        }

        if (config.courseUrl) {
            window.location.assign(config.courseUrl);

            return;
        }

        if (config.firstLessonUrl) {
            window.location.assign(config.firstLessonUrl);

            return;
        }

        rehydrateFromSharedState();
    }

    function rehydrateFromSharedState(sync = null) {
        const detail = sync?.detail || {};

        if (serverAuthenticated) {
            const serverProgress = detail.serverCourseProgress || detail.course_progress || detail.progress || null;
            if (serverProgress) {
                applyServerProgress(serverProgress);
            } else if (detail.serverLessonProgress && typeof detail.serverLessonProgress === 'object') {
                state.progress = progressStore.normalize(detail.serverLessonProgress);
            }

            render();
            return;
        }

        const previousQueueIndex = state.progress.current_queue_index;

        clearAutoAdvance();
        state.progress = progressStore.read();
        clampQueueIndex();
        state.feedback = null;

        if (previousQueueIndex !== state.progress.current_queue_index) {
            resetCurrentAnswer(true);
        } else if (state.selectedTokenIds.some((tokenId) => !currentQuestion().tokenMap[tokenId])) {
            resetCurrentAnswer(true);
        }

        render();
    }

    function feedbackMarkup(question) {
        if (!state.feedback) {
            if (state.progress.lesson_completed) {
                return `
                    <div class="rounded-[22px] border px-5 py-4" style="border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%);">
                        <p class="font-semibold" style="color: #17603a;">${html(testUi('compose.lesson_completed'))}</p>
                        <p class="mt-2 text-sm leading-6" style="color: #17603a;">${html(testUi('compose.completed_banner'))}</p>
                    </div>
                `;
            }

            return '';
        }

        const baseClass = state.feedback.type === 'correct'
            ? 'border-color: #b8e3c7; background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%); color: #17603a;'
            : 'border-color: #fecaca; background: linear-gradient(180deg, #fff5f5 0%, #ffefef 100%); color: #b42318;';
        const statusLabel = state.feedback.type === 'correct'
            ? testUi('compose.correct')
            : testUi('compose.incorrect');
        const hintBlock = state.feedback.hint
            ? `
                <div class="mt-3 rounded-[18px] border px-4 py-3 text-sm leading-6" style="border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.72);">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(testUi('compose.hint'))}</div>
                    <div class="mt-2 whitespace-pre-line">${html(state.feedback.hint)}</div>
                </div>
            `
            : '';
        const explanationBlock = state.feedback.explanation
            ? `
                <div class="mt-3 rounded-[18px] border px-4 py-3 text-sm leading-6" style="border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.72);">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(testUi('compose.explanation'))}</div>
                    <div class="mt-2 whitespace-pre-line">${html(state.feedback.explanation)}</div>
                </div>
            `
            : '';
        const answerBlock = state.feedback.type === 'incorrect'
            ? `
                <div class="mt-3 rounded-[18px] border px-4 py-3 text-sm leading-6" style="border-color: rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.72);">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em]" style="color: var(--muted);">${html(testUi('compose.correct_answer'))}</div>
                    <div class="mt-2 font-semibold">${html(question.correctText)}</div>
                </div>
            `
            : '';

        return `
            <div class="rounded-[22px] border px-5 py-4" style="${baseClass}">
                <p class="font-semibold">${html(statusLabel)}</p>
                ${answerBlock}
                ${hintBlock}
                ${explanationBlock}
            </div>
        `;
    }

    const chipBaseClass = 'inline-flex items-center border font-bold leading-none transition';
    const chipLayoutStyle = 'min-height: var(--polyglot-chip-min-height); padding: var(--polyglot-chip-padding); border-radius: var(--polyglot-chip-radius); font-size: var(--polyglot-chip-font-size);';
    const answerChipStyle = `${chipLayoutStyle} border-color: var(--line); background: color-mix(in srgb, var(--accent-soft) 88%, white); color: var(--text);`;
    const bankChipStyle = `${chipLayoutStyle} border-color: var(--line); background: var(--surface); color: var(--text);`;
    const punctuationChipStyle = `${chipLayoutStyle} border-color: var(--line); background: color-mix(in srgb, var(--surface) 90%, white); color: var(--muted);`;

    function answerZoneMarkup(question) {
        const chips = state.selectedTokenIds.map((tokenId) => {
            const token = question.tokenMap[tokenId];
            if (!token) {
                return '';
            }

            return `
                <button type="button"
                    data-answer-token-id="${html(token.id)}"
                    class="${chipBaseClass} gap-1.5 hover:opacity-90 sm:gap-2.5"
                    style="${answerChipStyle}">
                    <span>${html(token.value)}</span>
                    <span class="text-xs leading-none sm:text-sm" style="color: var(--muted);">&times;</span>
                </button>
            `;
        }).join('');

        const placeholder = chips === ''
            ? `<div class="rounded-[14px] border border-dashed px-3 py-3 text-[13px] leading-5 sm:rounded-[18px] sm:px-4 sm:py-5 sm:text-sm sm:leading-6" style="border-color: var(--line); color: var(--muted);">${html(testUi('compose.answer_placeholder'))}</div>`
            : chips;

        return `
            <div class="flex min-h-[3.25rem] flex-wrap content-start items-start gap-2 sm:min-h-[5rem] sm:gap-3">
                ${placeholder}
                <span class="${chipBaseClass}" style="${punctuationChipStyle}">${html(question.punctuation)}</span>
            </div>
        `;
    }

    function bankMarkup(question) {
        const tokenIds = availableBankTokenIds(question);

        if (tokenIds.length === 0) {
            return `<div class="rounded-[14px] border border-dashed px-3 py-3 text-[13px] leading-5 sm:rounded-[18px] sm:px-4 sm:py-5 sm:text-sm sm:leading-6" style="border-color: var(--line); color: var(--muted);">${html(testUi('compose.empty_pool'))}</div>`;
        }

        return tokenIds.map((tokenId) => {
            const token = question.tokenMap[tokenId];
            if (!token) {
                return '';
            }

            return `
                <button type="button"
                    data-bank-token-id="${html(token.id)}"
                    class="${chipBaseClass} hover:-translate-y-0.5 hover:shadow-sm"
                    style="${bankChipStyle}">
                    ${html(token.value)}
                </button>
            `;
        }).join('');
    }

    function render() {
        const courseUi = refreshCourseUi();
        if (courseUi.locked) {
            return;
        }

        clampQueueIndex();
        const question = currentQuestion();
        trackQuestionShown(question);
        const rating = ratingValue();
        const ratingSummary = ratingCardSummary(rating);
        const isLocked = state.autoAdvanceTimer !== null;
        const statusLabel = state.progress.lesson_completed
            ? testUi('compose.lesson_completed')
            : testUi('compose.in_progress');

        document.getElementById('compose-question-index').textContent = testUi('compose.current_sentence', {
            current: state.progress.current_queue_index + 1,
            total: normalizedQuestions.length,
        });
        document.getElementById('compose-rating-title').textContent = ratingSummary.title;
        document.getElementById('compose-rating').textContent = ratingSummary.value;
        document.getElementById('compose-rating-meta').textContent = ratingSummary.meta;
        document.getElementById('compose-status').textContent = statusLabel;
        document.getElementById('compose-status-note').textContent = statusGoalNote();
        document.getElementById('compose-source-text').textContent = question.sourceTextUk;
        renderComposeTheoryControls(question);
        const sourceSeederElement = document.getElementById('compose-source-seeder');
        if (sourceSeederElement) {
            sourceSeederElement.textContent = question.seederNamespace ? `Seeder: ${question.seederNamespace}` : '';
            sourceSeederElement.classList.toggle('hidden', !question.seederNamespace);
        }
        document.getElementById('compose-punctuation').textContent = testUi('compose.ends_with', { punctuation: question.punctuation });
        document.getElementById('compose-answer-zone').innerHTML = answerZoneMarkup(question);
        document.getElementById('compose-bank').innerHTML = bankMarkup(question);
        const feedbackElement = document.getElementById('compose-feedback');
        const feedbackHtml = feedbackMarkup(question);
        feedbackElement.innerHTML = feedbackHtml;
        feedbackElement.classList.toggle('hidden', feedbackHtml.trim() === '');

        root.querySelector('[data-action="check"]').disabled = isLocked;
        root.querySelector('[data-action="clear"]').disabled = isLocked || state.selectedTokenIds.length === 0;
        root.querySelector('[data-action="undo"]').disabled = isLocked || state.selectedTokenIds.length === 0;
        root.querySelector('[data-action="reset-progress"]').disabled = isLocked || serverAuthenticated;
    }

    function addTokenById(tokenId) {
        const question = currentQuestion();
        if (!question.tokenMap[tokenId] || state.selectedTokenIds.includes(tokenId)) {
            return;
        }

        state.selectedTokenIds.push(String(tokenId));
        state.feedback = null;
        render();
    }

    function removeTokenById(tokenId) {
        const index = state.selectedTokenIds.findIndex((selectedTokenId) => selectedTokenId === tokenId);
        if (index === -1) {
            return;
        }

        state.selectedTokenIds.splice(index, 1);
        state.feedback = null;
        render();
    }

    function removeLastToken() {
        if (state.selectedTokenIds.length === 0) {
            return;
        }

        removeTokenById(state.selectedTokenIds[state.selectedTokenIds.length - 1]);
    }

    function checkAnswer() {
        if (state.autoAdvanceTimer !== null) {
            return;
        }

        const question = currentQuestion();
        const submitted = sentenceFromTokenIds(question, state.selectedTokenIds);
        const isCorrect = normalizeCompare(submitted) === normalizeCompare(question.correctText);

        if (isCorrect) {
            markAttempt(5, question, submitted);
            state.feedback = {
                type: 'correct',
                hint: '',
                explanation: '',
            };
            render();
            state.autoAdvanceTimer = window.setTimeout(() => {
                state.autoAdvanceTimer = null;
                advanceQuestion();
            }, 800);

            return;
        }

        markAttempt(0, question, submitted);
        state.feedback = {
            type: 'incorrect',
            hint: question.hintUk || '',
            explanation: relevantExplanation(question),
        };
        render();
    }

    function hasEditableFocus(target) {
        if (!(target instanceof HTMLElement)) {
            return false;
        }

        if (target.isContentEditable) {
            return true;
        }

        return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
    }

    root.addEventListener('click', (event) => {
        if (state.autoAdvanceTimer !== null) {
            const actionButton = event.target.closest('[data-action="reset-progress"], [data-action="restart-course"]');
            if (!actionButton) {
                return;
            }
        }

        const bankToken = event.target.closest('[data-bank-token-id]');
        if (bankToken) {
            addTokenById(bankToken.getAttribute('data-bank-token-id') || '');
            return;
        }

        const answerToken = event.target.closest('[data-answer-token-id]');
        if (answerToken) {
            removeTokenById(answerToken.getAttribute('data-answer-token-id') || '');
            return;
        }

        if (event.target.closest('#compose-theory-btn')) {
            toggleComposeTheoryPanel();
            return;
        }

        const actionButton = event.target.closest('[data-action]');
        if (!actionButton) {
            return;
        }

        const action = actionButton.getAttribute('data-action');

        if (action === 'check') {
            checkAnswer();
        } else if (action === 'clear') {
            resetCurrentAnswer(false);
            render();
        } else if (action === 'undo') {
            removeLastToken();
        } else if (action === 'reset-progress') {
            resetLessonProgress();
        } else if (action === 'restart-course') {
            const confirmed = window.confirm(testUi('course.reset_course_progress_confirm'));
            if (!confirmed) {
                return;
            }

            restartCourse();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (hasEditableFocus(event.target)) {
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            checkAnswer();
            return;
        }

        if (event.key === 'Backspace' && state.selectedTokenIds.length > 0) {
            event.preventDefault();
            removeLastToken();
        }
    });

    window.addEventListener('storage', (event) => {
        const key = String(event.key || '');
        const courseSlug = String(config.courseSlug || '').trim();

        if (courseSlug && key.startsWith(`polyglot_debug_unlock_policy:${courseSlug}:`)) {
            render();
        }
    });

    window.PolyglotCourseProgress.subscribeToSync({
        courseSlug: config.courseSlug,
        lessonSlug: config.slug,
        ignoreSourceId: progressStore.sourceId,
        onSync: rehydrateFromSharedState,
    });

    hydrateServerProgress().finally(() => {
        const initialCourseUi = refreshCourseUi();
        if (initialCourseUi.locked) {
            return;
        }

        if (courseStore && !serverAuthenticated) {
            courseStore.markLessonOpened(config.slug);
        }

        clampQueueIndex();
        resetCurrentAnswer(true);

        if (!serverAuthenticated) {
            persistProgress();
        }

        render();
    });
    }

    if (window.PolyglotCourseProgress) {
        boot();

        return;
    }

    window.addEventListener('polyglot:progress-ready', boot, { once: true });
})();
</script>
@endsection
