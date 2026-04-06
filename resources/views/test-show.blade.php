@extends('layouts.catalog-public')

@section('title', $test->name)

@section('head')
<style>
    .nd-page {
        overflow: visible !important;
    }

    #new-design-test-shell #quiz-app {
        min-height: auto !important;
    }

    #new-design-test-shell .max-w-5xl {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #new-design-test-shell .sticky-inner {
        border: 1px solid var(--line) !important;
        background: var(--surface-strong) !important;
        border-radius: 28px !important;
        box-shadow: 0 18px 40px rgba(17, 38, 63, 0.08) !important;
        backdrop-filter: none !important;
        contain: paint;
    }

    #new-design-test-shell .sticky-inner > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.3125rem !important;
    }

    #site-header.has-attached-test-controls {
        border-bottom-color: transparent !important;
        contain: none !important;
        overflow: visible !important;
        padding-top: 0.5rem !important;
        padding-bottom: 0.45rem !important;
    }

    #catalog-shell.has-attached-test-controls {
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }

    #catalog-shell.has-attached-test-controls #site-header,
    #catalog-shell.has-attached-test-controls #site-header::before {
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }

    #site-header-test-controls {
        display: none;
        width: 100%;
        position: relative;
        z-index: 2;
    }

    #site-header.has-attached-test-controls #site-header-test-controls {
        display: block;
        padding-top: 0.28rem;
        border-top: 0 !important;
    }

    #site-header.has-attached-test-controls > div {
        gap: 0.55rem !important;
    }

    #site-header.has-attached-test-controls > div > div:first-child {
        gap: 0.6rem !important;
    }

    #site-header.has-attached-test-controls a[aria-label="Gramlyze"] {
        gap: 0.55rem !important;
    }

    #site-header.has-attached-test-controls a[aria-label="Gramlyze"] .h-12.w-12 {
        width: 2.45rem !important;
        height: 2.45rem !important;
        border-radius: 0.9rem !important;
    }

    #site-header.has-attached-test-controls a[aria-label="Gramlyze"] p:first-child {
        font-size: 1rem !important;
        line-height: 1 !important;
    }

    #site-header.has-attached-test-controls a[aria-label="Gramlyze"] p:last-child {
        margin-top: 0.05rem !important;
        font-size: 0.58rem !important;
        line-height: 1 !important;
    }

    #site-header.has-attached-test-controls > div > nav {
        gap: 0.95rem !important;
        line-height: 1 !important;
    }

    #site-header.has-attached-test-controls > div > div:last-child {
        gap: 0.45rem !important;
    }

    #site-header.has-attached-test-controls > div > div:last-child input[type="search"] {
        padding-top: 0.56rem !important;
        padding-bottom: 0.56rem !important;
        border-radius: 0.95rem !important;
        border-width: 1px !important;
        border-color: var(--line) !important;
        box-shadow: none !important;
    }

    #site-header.has-attached-test-controls > div > div:last-child > button,
    #site-header.has-attached-test-controls > div > div:last-child > div > button {
        min-height: 2.35rem !important;
        padding-top: 0.42rem !important;
        padding-bottom: 0.42rem !important;
        border-radius: 0.95rem !important;
        box-shadow: none !important;
    }

    #site-header.has-attached-test-controls > div > div:last-child > div > button {
        border-width: 1px !important;
        border-color: var(--line) !important;
    }

    #site-header.has-attached-test-controls > div > div:last-child > button {
        width: 2.35rem !important;
        height: 2.35rem !important;
        padding-left: 0.42rem !important;
        padding-right: 0.42rem !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck {
        z-index: 39 !important;
    }

    #new-design-test-shell .sticky-test-header,
    #new-design-test-shell .sticky-inner,
    #new-design-test-shell .word-search-section,
    #new-design-test-shell .progress-section,
    #new-design-test-shell .progress-icon,
    #new-design-test-shell .progress-icon svg,
    #new-design-test-shell .progress-bar-container,
    #new-design-test-shell .sticky-search-btn,
    #new-design-test-shell #restart-test {
        transform: translateZ(0);
        backface-visibility: hidden;
    }

    #new-design-test-shell .sticky-inner,
    #new-design-test-shell .word-search-section,
    #new-design-test-shell .progress-section,
    #new-design-test-shell .progress-bar-container,
    #new-design-test-shell .sticky-search-btn,
    #new-design-test-shell #restart-test {
        will-change: transform, opacity;
        transition-duration: 180ms !important;
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1) !important;
        transition-property: transform, opacity, box-shadow, background-color, border-color, color !important;
    }

    #new-design-test-shell .progress-icon,
    #new-design-test-shell .progress-icon svg,
    #new-design-test-shell .progress-label-text,
    #new-design-test-shell .progress-value,
    #new-design-test-shell #score-label {
        transition-duration: 180ms !important;
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1) !important;
        transition-property: transform, opacity, color !important;
        transform-origin: left center;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .sticky-inner {
        border-top: 0 !important;
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
        border-bottom-left-radius: 24px !important;
        border-bottom-right-radius: 24px !important;
        padding: 0.45rem 0.6rem !important;
        box-shadow: 0 12px 24px rgba(17, 38, 63, 0.06) !important;
        background: color-mix(in srgb, var(--surface-strong) 94%, var(--surface)) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    #new-design-test-shell .progress-section {
        border: 1px solid var(--line) !important;
        background: linear-gradient(180deg, color-mix(in srgb, var(--surface) 92%, white) 0%, var(--surface-strong) 100%) !important;
        border-radius: 24px !important;
        box-shadow: none !important;
    }

    #new-design-test-shell .progress-layout {
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
    }

    #new-design-test-shell .progress-main {
        min-width: 0;
    }

    #new-design-test-shell .progress-actions {
        align-self: stretch;
        display: flex;
        justify-content: flex-end;
    }

    #new-design-test-shell .progress-actions #restart-test {
        white-space: nowrap;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .progress-section {
        border: 0 !important;
        padding: 0.35rem 0.45rem !important;
        border-top-left-radius: 18px !important;
        border-top-right-radius: 18px !important;
        border-bottom-left-radius: 20px !important;
        border-bottom-right-radius: 20px !important;
        background: linear-gradient(180deg, color-mix(in srgb, var(--surface) 94%, white) 0%, color-mix(in srgb, var(--surface-strong) 98%, white) 100%) !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .progress-icon {
        transform: scale(0.78) !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .progress-icon svg {
        transform: scale(0.82) !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .progress-label-text {
        transform: scale(0.88) !important;
        opacity: 0.92 !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .progress-value {
        transform: scale(0.9) !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck .progress-bar-container {
        border: 0 !important;
        box-shadow: none !important;
        background: rgba(47, 103, 177, 0.14) !important;
        transform: scaleY(0.72) !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck #progress-bar {
        top: 0 !important;
        height: 100% !important;
        border-radius: 999px !important;
    }

    #new-design-test-shell .progress-icon {
        background: linear-gradient(135deg, #2f67b1 0%, #4f88d6 100%) !important;
    }

    #new-design-test-shell .progress-label-text {
        color: var(--muted) !important;
    }

    #new-design-test-shell .progress-value,
    #new-design-test-shell #progress-label {
        color: var(--text) !important;
        background: none !important;
        -webkit-text-fill-color: initial !important;
    }

    #new-design-test-shell #score-label {
        color: var(--accent) !important;
        background: none !important;
        -webkit-text-fill-color: initial !important;
    }

    #new-design-test-shell .progress-bar-container {
        border: 1px solid var(--line) !important;
        box-shadow: none !important;
        background: color-mix(in srgb, var(--surface) 85%, white) !important;
    }

    #new-design-test-shell #progress-bar {
        background: linear-gradient(90deg, #2f67b1 0%, #74a9f0 55%, #f59b2f 100%) !important;
    }

    .sticky-inner.header-attached {
        border: 0 !important;
        background: color-mix(in srgb, var(--surface-strong) 97%, var(--surface)) !important;
        border-radius: 22px !important;
        padding: 0.24rem 0.34rem !important;
        box-shadow: 0 10px 22px rgba(17, 38, 63, 0.06) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        contain: none;
        overflow: visible !important;
        transform: translateZ(0);
        backface-visibility: hidden;
        will-change: transform, opacity;
        transition-duration: 180ms !important;
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1) !important;
        transition-property: transform, opacity, box-shadow, background-color, border-color, color !important;
    }

    .sticky-inner.header-attached > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.16rem !important;
    }

    .sticky-inner.header-attached .word-search-section,
    .sticky-inner.header-attached .progress-section,
    .sticky-inner.header-attached .progress-bar-container,
    .sticky-inner.header-attached .sticky-search-btn,
    .sticky-inner.header-attached #restart-test {
        transform: translateZ(0);
        backface-visibility: hidden;
        will-change: transform, opacity;
        transition-duration: 180ms !important;
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1) !important;
        transition-property: transform, opacity, box-shadow, background-color, border-color, color !important;
    }

    .sticky-inner.header-attached .progress-icon,
    .sticky-inner.header-attached .progress-icon svg,
    .sticky-inner.header-attached .progress-label-text,
    .sticky-inner.header-attached .progress-value,
    .sticky-inner.header-attached #score-label {
        transition-duration: 180ms !important;
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1) !important;
        transition-property: transform, opacity, color !important;
        transform-origin: left center;
    }

    .sticky-inner.header-attached .word-search-section > div {
        gap: 0 !important;
    }

    .sticky-inner.header-attached .word-search-section > div > div:first-child {
        display: none !important;
    }

    .sticky-inner.header-attached #word-search {
        min-height: 2.15rem;
        padding: 0.46rem 0.76rem !important;
        border-radius: 0.9rem !important;
        border-width: 1px !important;
        border-color: var(--line) !important;
        box-shadow: none !important;
        font-size: 0.88rem !important;
        line-height: 1.2 !important;
    }

    .sticky-inner.header-attached .progress-section {
        border: 0 !important;
        background: color-mix(in srgb, var(--surface-strong) 97%, var(--surface)) !important;
        border-radius: 18px !important;
        padding: 0.16rem 0.28rem !important;
        box-shadow: none !important;
    }

    .sticky-inner.header-attached .progress-layout {
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.25rem !important;
    }

    .sticky-inner.header-attached .progress-main {
        display: flex;
        flex-direction: column;
        min-width: 0;
        gap: 0.14rem !important;
    }

    .sticky-inner.header-attached .progress-main > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0 !important;
    }

    .sticky-inner.header-attached .progress-meta {
        display: flex;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
    }

    .sticky-inner.header-attached .progress-meta > .flex {
        min-width: 0;
        gap: 0.38rem !important;
    }

    .sticky-inner.header-attached .progress-meta > .flex > div,
    .sticky-inner.header-attached .progress-meta > .text-right {
        display: flex;
        align-items: baseline;
        gap: 0.26rem;
        white-space: nowrap;
    }

    .sticky-inner.header-attached .progress-meta > .text-right {
        text-align: left !important;
    }

    .sticky-inner.header-attached .progress-meta > .text-right > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0 !important;
    }

    .sticky-inner.header-attached .progress-actions {
        align-self: center;
        display: flex;
        justify-content: flex-end;
    }

    .sticky-inner.header-attached .progress-actions #restart-test {
        white-space: nowrap;
        padding: 0.42rem 0.68rem !important;
        font-size: 0.67rem !important;
    }

    .sticky-inner.header-attached .progress-icon {
        transform: scale(0.66) !important;
        background: linear-gradient(135deg, #2f67b1 0%, #4f88d6 100%) !important;
    }

    .sticky-inner.header-attached .progress-icon svg {
        transform: scale(0.72) !important;
    }

    .sticky-inner.header-attached .progress-label-text {
        transform: none !important;
        opacity: 0.84 !important;
        color: var(--muted) !important;
        font-size: 0.62rem !important;
        line-height: 1 !important;
    }

    .sticky-inner.header-attached .progress-value,
    .sticky-inner.header-attached #progress-label {
        transform: none !important;
        color: var(--text) !important;
        background: none !important;
        -webkit-text-fill-color: initial !important;
        font-size: 0.86rem !important;
        line-height: 1 !important;
    }

    .sticky-inner.header-attached #score-label {
        color: var(--accent) !important;
        background: none !important;
        -webkit-text-fill-color: initial !important;
        font-size: 0.86rem !important;
        line-height: 1 !important;
    }

    .sticky-inner.header-attached .progress-bar-container {
        border: 0 !important;
        box-shadow: none !important;
        background: rgba(47, 103, 177, 0.14) !important;
        height: 0.3rem !important;
        transform: none !important;
    }

    .sticky-inner.header-attached #progress-bar {
        top: 0 !important;
        height: 100% !important;
        border-radius: 999px !important;
        background: linear-gradient(90deg, #2f67b1 0%, #74a9f0 55%, #f59b2f 100%) !important;
    }

    .sticky-inner.header-attached #restart-test {
        transform: none !important;
        transform-origin: right center;
        border-width: 1px !important;
        border-color: var(--line) !important;
        background: var(--surface-strong) !important;
        color: var(--text) !important;
        border-radius: 0.85rem !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    #new-design-test-shell main {
        margin-top: 1.75rem !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    #new-design-test-shell article[data-idx] {
        position: relative;
        overflow: hidden;
        background: var(--surface-strong) !important;
        border: 1px solid var(--line) !important;
        border-radius: 30px !important;
        box-shadow: 0 14px 32px rgba(17, 38, 63, 0.08) !important;
        transform: none !important;
    }

    #new-design-test-shell article[data-idx]::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 5px;
        background: linear-gradient(90deg, #2f67b1 0%, #74a9f0 55%, #f59b2f 100%);
        opacity: 0.95;
    }

    #new-design-test-shell article[data-idx]:hover,
    #new-design-test-shell article[data-idx]:focus-within {
        border-color: color-mix(in srgb, var(--accent) 35%, var(--line)) !important;
        box-shadow: 0 18px 40px rgba(17, 38, 63, 0.12) !important;
    }

    #new-design-test-shell article[data-idx] > .flex:first-child {
        position: relative;
        gap: 1rem !important;
        margin-bottom: 1.35rem !important;
        padding-bottom: 1.1rem;
        border-bottom: 1px solid color-mix(in srgb, var(--line) 78%, white);
    }

    #new-design-test-shell article[data-idx] > .flex:first-child > .flex-1 {
        min-width: 0;
    }

    #new-design-test-shell article[data-idx] > .flex:first-child .inline-flex.items-center.px-3.py-1.rounded-full {
        padding: 0.48rem 0.8rem !important;
        border: 1px solid color-mix(in srgb, var(--line) 74%, white);
        border-radius: 999px !important;
        background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) 86%, white) 0%, color-mix(in srgb, var(--surface) 94%, white) 100%) !important;
        color: var(--accent) !important;
        letter-spacing: 0.02em;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    #new-design-test-shell article[data-idx] > .flex:first-child .text-xs.text-gray-500,
    #new-design-test-shell article[data-idx] > .flex:first-child .text-sm.text-gray-500 {
        color: var(--muted) !important;
        font-weight: 700 !important;
    }

    #new-design-test-shell article[data-idx] > .flex:first-child > .flex-1 > .leading-relaxed {
        display: block;
        margin: 0.35rem 0 0 !important;
        font-size: clamp(1.2rem, 1.3vw + 0.9rem, 2rem) !important;
        line-height: 1.45 !important;
        font-weight: 700 !important;
        color: var(--text) !important;
        letter-spacing: -0.025em;
    }

    #new-design-test-shell article[data-idx] > .flex:first-child > .flex-1 > .leading-relaxed .gap-btn {
        min-width: 4.8rem;
        padding-inline: 0.9rem !important;
        padding-block: 0.35rem !important;
        font-size: 1rem !important;
        font-weight: 800 !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.55);
    }

    #new-design-test-shell article[data-idx] > .flex:first-child > .flex-1 > .leading-relaxed .verb-hint {
        font-size: inherit !important;
        line-height: inherit !important;
        font-weight: 700 !important;
    }

    #new-design-test-shell article[data-idx] .help-btn,
    #new-design-test-shell article[data-idx] .theory-btn {
        margin-top: 0.25rem;
        color: var(--accent) !important;
        font-weight: 700 !important;
    }

    #new-design-test-shell article[data-idx] .help-btn:hover,
    #new-design-test-shell article[data-idx] .theory-btn:hover {
        color: color-mix(in srgb, var(--accent) 80%, black) !important;
    }

    #new-design-test-shell article[data-idx] [id^="hints-"] > div,
    #new-design-test-shell article[data-idx] [id^="theory-panel-"] > div {
        border: 1px solid color-mix(in srgb, var(--accent) 18%, var(--line)) !important;
        border-radius: 22px !important;
        background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) 52%, white) 0%, color-mix(in srgb, var(--surface) 96%, white) 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    #new-design-test-shell article[data-idx] [id^="hints-"] .text-blue-900,
    #new-design-test-shell article[data-idx] [id^="hints-"] .text-blue-800,
    #new-design-test-shell article[data-idx] [id^="theory-panel-"] .text-emerald-900,
    #new-design-test-shell article[data-idx] [id^="theory-panel-"] .text-emerald-800 {
        color: var(--text) !important;
    }

    #new-design-test-shell article[data-idx] > .flex.items-center.justify-between {
        margin-bottom: 1rem !important;
        padding: 0.75rem 0.9rem;
        border: 1px solid color-mix(in srgb, var(--line) 76%, white);
        border-radius: 20px;
        background: color-mix(in srgb, var(--surface) 88%, white);
    }

    #new-design-test-shell article[data-idx] > .flex.items-center.justify-between [id^="slot-indicator-"] {
        padding: 0.55rem 0.9rem !important;
        border: 1px solid color-mix(in srgb, var(--accent) 24%, var(--line)) !important;
        border-radius: 999px !important;
        background: var(--accent-soft) !important;
        color: var(--accent) !important;
        font-weight: 800 !important;
        letter-spacing: 0.01em;
    }

    #new-design-test-shell article[data-idx] > .flex.items-center.justify-between [id^="slot-label-"] {
        color: var(--muted) !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
    }

    #new-design-test-shell article[data-idx] > .flex:first-child > .flex.flex-col.items-center.justify-center {
        width: 4.5rem !important;
        height: 4.5rem !important;
        border: 1px solid color-mix(in srgb, var(--accent) 18%, var(--line)) !important;
        border-radius: 22px !important;
        background: linear-gradient(180deg, color-mix(in srgb, var(--accent-soft) 72%, white) 0%, color-mix(in srgb, var(--surface) 96%, white) 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    #new-design-test-shell article[data-idx] > .flex:first-child > .flex.flex-col.items-center.justify-center .text-lg {
        color: var(--accent) !important;
        font-size: 1.55rem !important;
    }

    #new-design-test-shell article[data-idx] .text-gray-900,
    #new-design-test-shell article[data-idx] .text-gray-700,
    #new-design-test-shell article[data-idx] .text-gray-600,
    #new-design-test-shell article[data-idx] .text-gray-500 {
        color: var(--text) !important;
    }

    #new-design-test-shell article[data-idx] .bg-gradient-to-r.from-blue-100.to-indigo-100,
    #new-design-test-shell article[data-idx] .bg-gradient-to-r.from-indigo-100.to-purple-100 {
        background: var(--accent-soft) !important;
        color: var(--accent) !important;
    }

    #new-design-test-shell article[data-idx] .gap-btn {
        border: 1px solid color-mix(in srgb, var(--accent) 45%, var(--line)) !important;
        background: color-mix(in srgb, var(--accent-soft) 70%, white) !important;
        color: var(--text) !important;
        border-radius: 14px !important;
    }

    #new-design-test-shell article[data-idx] button[data-opt] {
        border-color: var(--line) !important;
        background: var(--surface) !important;
        color: var(--text) !important;
        border-radius: 22px !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75) !important;
        transform: none !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
    }

    #new-design-test-shell article[data-idx] button[data-opt]:hover {
        border-color: color-mix(in srgb, var(--accent) 45%, var(--line)) !important;
        background: color-mix(in srgb, var(--accent-soft) 65%, white) !important;
        box-shadow: 0 10px 22px rgba(17, 38, 63, 0.08) !important;
    }

    #new-design-test-shell article[data-idx] button[data-opt] .border-2 {
        border-color: color-mix(in srgb, var(--line) 80%, white) !important;
        color: var(--muted) !important;
        border-radius: 14px !important;
    }

    #new-design-test-shell article[data-idx] button[data-opt].border-red-300 {
        border-color: #ef4444 !important;
        background: #fff1f2 !important;
        color: #b91c1c !important;
    }

    #new-design-test-shell article[data-idx] button[data-opt].border-red-300 .border-2 {
        border-color: #fca5a5 !important;
        color: #dc2626 !important;
        background: rgba(255, 255, 255, 0.75) !important;
    }

    #new-design-test-shell article[data-idx] [id^="feedback-"] > div {
        border-radius: 22px !important;
        box-shadow: none !important;
    }

    #new-design-test-shell article[data-idx] [id^="feedback-"] .from-emerald-50.to-teal-50,
    #new-design-test-shell article[data-idx] [id^="feedback-"] .bg-emerald-50 {
        border-color: #b8e3c7 !important;
        background: linear-gradient(180deg, #f0fbf4 0%, #e7f8ee 100%) !important;
    }

    #new-design-test-shell article[data-idx] [id^="feedback-"] .from-red-50.to-rose-50,
    #new-design-test-shell article[data-idx] [id^="feedback-"] .bg-red-50 {
        border-color: #fecaca !important;
        background: linear-gradient(180deg, #fff5f5 0%, #ffefef 100%) !important;
    }

    #new-design-test-shell article[data-idx] [id^="feedback-"] .text-emerald-800 {
        color: #17603a !important;
    }

    #new-design-test-shell article[data-idx] [id^="feedback-"] .text-red-800 {
        color: #b42318 !important;
    }

    #new-design-test-shell article[data-idx] [id^="hints-"] p,
    #new-design-test-shell article[data-idx] [id^="theory-panel-"] p,
    #new-design-test-shell article[data-idx] [id^="theory-panel-"] li,
    #new-design-test-shell article[data-idx] [id^="feedback-"] .flex-1 > div,
    #new-design-test-shell article[data-idx] [id^="feedback-"] .whitespace-pre-line {
        font-size: 1rem !important;
        line-height: 1.6 !important;
    }

    #new-design-test-shell #restart-test {
        border-color: var(--line) !important;
        background: var(--surface-strong) !important;
        color: var(--text) !important;
        border-radius: 18px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    #new-design-test-shell #restart-test:hover {
        border-color: var(--accent) !important;
        background: var(--accent-soft) !important;
    }

    #new-design-test-shell .sticky-test-header.is-stuck #restart-test {
        transform: scale(0.92) !important;
        transform-origin: right center;
    }

    #new-design-test-shell #summary > div {
        border: 1px solid var(--line) !important;
        background: linear-gradient(180deg, color-mix(in srgb, var(--surface) 92%, white) 0%, var(--surface-strong) 100%) !important;
        border-radius: 30px !important;
        box-shadow: 0 18px 40px rgba(17, 38, 63, 0.08) !important;
    }

    #new-design-test-shell #summary h2,
    #new-design-test-shell #summary p,
    #new-design-test-shell #summary .text-gray-900,
    #new-design-test-shell #summary .text-gray-700 {
        color: var(--text) !important;
        background: none !important;
        -webkit-text-fill-color: initial !important;
    }

    #new-design-test-shell #summary #retry {
        background: linear-gradient(135deg, #2f67b1 0%, #4f88d6 100%) !important;
        border-radius: 20px !important;
        box-shadow: none !important;
        transform: none !important;
    }

    #new-design-test-shell #summary #show-wrong {
        border-color: var(--line) !important;
        background: var(--surface-strong) !important;
        color: var(--text) !important;
        border-radius: 20px !important;
    }

    #new-design-test-shell .bg-gradient-to-r.from-emerald-50.to-teal-50,
    #new-design-test-shell .bg-gradient-to-r.from-red-50.to-rose-50,
    #new-design-test-shell .bg-emerald-50,
    #new-design-test-shell .bg-red-50,
    #new-design-test-shell .bg-gray-50 {
        border-radius: 20px !important;
    }

    @media (max-width: 640px) {
        #new-design-test-shell .progress-layout {
            grid-template-columns: 1fr;
        }

        #new-design-test-shell .progress-actions {
            justify-content: stretch;
        }

        #new-design-test-shell .progress-actions #restart-test {
            width: 100%;
        }

        #new-design-test-shell .sticky-inner > :not([hidden]) ~ :not([hidden]) {
            margin-top: 0.3125rem !important;
        }

        #new-design-test-shell .sticky-inner {
            padding: 0.9rem !important;
        }

        #new-design-test-shell article[data-idx] {
            border-radius: 24px !important;
        }

        #new-design-test-shell article[data-idx] > .flex:first-child {
            padding-bottom: 0.9rem;
        }

        #new-design-test-shell article[data-idx] > .flex:first-child > .flex-1 > .leading-relaxed {
            font-size: 1.15rem !important;
        }

        #new-design-test-shell article[data-idx] > .flex.items-center.justify-between {
            align-items: flex-start !important;
            gap: 0.6rem !important;
            flex-direction: column;
        }
    }

    @media (min-width: 641px) {
        #new-design-test-shell .sticky-inner > :not([hidden]) ~ :not([hidden]) {
            margin-top: 0.5rem !important;
        }
    }
</style>
@endsection

@section('content')
@php
    $templateView = $templateView ?? 'test-modes.card-easy';
    $heroBadge = $heroBadge ?? __('frontend.tests.hero.interactive');
    $heroDescription = $heroDescription ?? __('frontend.tests.hero.card_description');
    $levels = collect($questionData)->pluck('level')->filter()->unique()->values();
    $questionCount = count($questionData ?? []);
    $templatePath = resource_path('views/' . str_replace('.', '/', $templateView) . '.blade.php');
    $template = file_get_contents($templatePath);
    $template = str_replace("@extends('layouts.engram')", '', $template);
    $template = str_replace("@section('title', \$test->name)", '', $template);
    $template = str_replace("@section('content')", '', $template);
    $template = preg_replace('/@endsection\s*$/', '', $template);
    $template = str_replace('<div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-50 via-white to-purple-50"></div>', '', $template);
    $template = preg_replace('/\s*<!-- Header Section with Modern Design -->.*?<\/header>/s', '', $template, 1);
    $template = preg_replace('/\s*<header class="mb-6[^"]*">.*?<\/header>/s', '', $template, 1);
    $template = preg_replace('/\s*<header class="drag-quiz__header">.*?<\/header>/s', '', $template, 1);
    $template = str_replace("@include('components.test-mode-nav-v2')", '', $template);
    $template = str_replace("@include('components.test-mode-nav')", '', $template);
@endphp

<div class="nd-page">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--muted);" aria-label="{{ __('public.common.breadcrumb') }}">
        <a href="{{ localized_route('home') }}" class="transition hover:text-ocean">{{ __('public.common.home') }}</a>
        <span>/</span>
        <a href="{{ localized_route('catalog.tests-cards') }}" class="transition hover:text-ocean">{{ __('public.nav.catalog') }}</a>
        <span>/</span>
        <span style="color: var(--text);">{{ $test->name }}</span>
    </nav>

    <section class="relative overflow-hidden rounded-[30px] border p-7 shadow-card surface-card-strong" style="border-color: var(--line);">
        <div class="absolute -right-10 top-0 hidden h-36 w-36 rounded-full border-[18px] border-ocean/30 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-44 w-14 rounded-tl-[2rem] bg-ocean lg:block"></div>
        <div class="relative grid gap-6 lg:grid-cols-[1.04fr_0.96fr] lg:items-end">
            <div class="max-w-3xl">
                <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-extrabold uppercase tracking-[0.22em] soft-accent" style="border-color: var(--line); color: var(--accent);">
                    {{ $heroBadge }}
                </span>
                <h1 class="mt-4 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">{{ $test->name }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 sm:text-base" style="color: var(--muted);">
                    {{ $heroDescription }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.hero.questions') }}</p>
                    <p class="mt-2 font-display text-[2.25rem] font-extrabold leading-none">{{ $questionCount }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('frontend.tests.hero.questions_count') }}</p>
                </article>
                <article class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('frontend.tests.hero.levels') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($levels as $level)
                            <span class="rounded-full px-3 py-1.5 text-xs font-bold" style="background: var(--accent-soft); color: var(--text);">{{ $level }}</span>
                        @empty
                            <span class="text-sm" style="color: var(--muted);">{{ __('frontend.tests.hero.na') }}</span>
                        @endforelse
                    </div>
                </article>
            </div>
        </div>
    </section>

    @include('components.test-mode-nav-new-design')

    <div id="new-design-test-shell" class="mt-8">
        {!! \Illuminate\Support\Facades\Blade::render($template, [
            'test' => $test,
            'questionData' => $questionData,
            'jsStateMode' => $jsStateMode,
            'savedState' => $savedState,
            'usesUuidLinks' => $usesUuidLinks,
            'isAdmin' => $isAdmin ?? false,
            'showTechnicalInfo' => $showTechnicalInfo ?? false,
        ]) !!}
    </div>
</div>
@endsection
