@extends('layouts.new-design')

@section('title', $test->name)

@section('content')
{{-- Background gradient adapted to new-design ocean palette --}}
<div class="fixed inset-0 -z-10" style="background: radial-gradient(ellipse 80% 50% at 10% 0%, rgba(47,103,177,0.07), transparent), radial-gradient(ellipse 60% 50% at 90% 20%, rgba(20,35,59,0.05), transparent), #f5fbff;"></div>

<div class="min-h-screen" id="quiz-app">
    <div class="max-w-5xl -mx-3 sm:mx-auto px-0 sm:px-5 md:px-6 lg:px-8 py-6 sm:py-10">

        {{-- ─── Header ─── --}}
        <header class="mb-6 sm:mb-12">
            <div class="space-y-3 text-center sm:space-y-4">
                <a
                    href="{{ localized_route('new-design.catalog.tests-cards') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-line bg-shell px-3 py-1.5 text-xs font-semibold text-steel shadow-sm transition hover:border-ocean hover:text-ocean"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Каталог тестів
                </a>

                <div class="inline-flex items-center gap-2 rounded-full bg-ocean/10 px-4 py-2 text-sm font-semibold text-ocean">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Interactive Test
                </div>

                <h1 class="font-display text-[28px] font-extrabold text-night sm:text-4xl lg:text-5xl">
                    {{ $test->name }}
                </h1>
                <p class="mx-auto max-w-2xl text-[15px] text-steel sm:text-lg">
                    Відповідай відразу, клавіші 1–4 активні для поточного питання
                </p>
            </div>
        </header>

        {{-- ─── Mode navigation ─── --}}
        @include('components.test-mode-nav-v2')

        {{-- ─── Sticky controls: search + progress ─── --}}
        <div class="sticky-test-header sticky top-0 z-30 max-h-[40vh] md:max-h-none" id="sticky-header">
            <div class="sticky-inner space-y-2.5 rounded-2xl border border-line bg-shell/95 p-2.5 shadow backdrop-blur-md transition-all duration-300 sm:space-y-4 sm:p-5 lg:p-6">
                <div class="word-search-section transition-all duration-300">
                    @include('components.word-search')
                </div>
                <div class="progress-section rounded-2xl border border-line bg-mist p-2.5 shadow-inner transition-all duration-300 sm:p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-3">
                        <div class="flex items-center space-x-2.5 sm:space-x-3">
                            {{-- Search toggle – visible only when sticky --}}
                            <button
                                type="button"
                                id="sticky-search-toggle"
                                class="sticky-search-btn hidden h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-ocean transition-all duration-300 hover:scale-110 hover:shadow-md"
                                title="Пошук слова"
                            >
                                <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>

                            <div class="progress-icon flex h-9 w-9 items-center justify-center rounded-full bg-ocean transition-all duration-300 sm:h-10 sm:w-10">
                                <svg class="h-4 w-4 text-white transition-all duration-300 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>

                            <div>
                                <div class="progress-label-text text-[11px] font-semibold uppercase tracking-wide text-steel transition-all duration-300 sm:text-xs">Прогрес</div>
                                <div id="progress-label" class="progress-value text-base font-bold text-night transition-all duration-300 sm:text-xl">1 / 0</div>
                            </div>
                        </div>

                        <div class="space-y-0.5 text-right">
                            <div class="progress-label-text text-[11px] font-semibold uppercase tracking-wide text-steel transition-all duration-300 sm:text-xs">Точність</div>
                            <div id="score-label" class="progress-value text-base font-bold text-ocean transition-all duration-300 sm:text-xl">0%</div>
                        </div>
                    </div>

                    <div class="progress-bar-container relative mt-3 h-2.5 w-full overflow-hidden rounded-full border border-line bg-shell shadow-inner transition-all duration-300 sm:h-3">
                        <div
                            id="progress-bar"
                            class="absolute left-0 top-0 h-full rounded-full transition-all duration-500 ease-out"
                            style="width:0%; background: linear-gradient(90deg, #2f67b1 0%, #14233b 100%)"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Main question area ─── --}}
        <main class="mt-6 space-y-5 rounded-[1.75rem] border border-line bg-shell p-4 shadow-card sm:mt-8 sm:space-y-6 sm:p-6 lg:p-8">
            @include('components.saved-test-js-restart-button')

            <div id="questions" class="space-y-5 sm:space-y-6"></div>

            {{-- ─── Summary ─── --}}
            <div id="summary" class="hidden">
                <div class="rounded-[1.75rem] border-2 border-ocean/30 bg-[radial-gradient(circle_at_top_left,rgba(47,103,177,0.08),transparent_50%),#f5fbff] p-6 text-center shadow-card sm:p-8">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-ocean shadow-card">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="font-display mb-3 text-3xl font-extrabold text-night">Тест завершено! 🎉</h2>
                    <p id="summary-text" class="mb-8 text-xl text-steel"></p>
                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <button
                            id="retry"
                            class="group rounded-2xl bg-ocean px-6 py-3.5 font-semibold text-white shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-panel sm:px-8 sm:py-4"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="mr-2 h-5 w-5 transition-transform duration-500 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Спробувати ще раз
                            </span>
                        </button>
                        <button
                            id="show-wrong"
                            class="rounded-2xl border-2 border-line bg-shell px-6 py-3.5 font-semibold text-steel transition-all duration-200 hover:border-ocean hover:bg-mist hover:text-ocean sm:px-8 sm:py-4"
                        >
                            <span class="flex items-center justify-center">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Переглянути помилки
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Loading overlay --}}
<div id="ajax-loader" class="fixed inset-0 z-50 hidden items-center justify-center bg-night/20 backdrop-blur-sm">
    <div class="flex flex-col items-center rounded-[1.75rem] bg-shell p-8 shadow-panel">
        <svg class="mb-4 h-12 w-12 animate-spin text-ocean" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <p class="font-medium text-steel">Завантаження...</p>
    </div>
</div>

<script>
window.__INITIAL_JS_TEST_QUESTIONS__ = @json($questionData);
let QUESTIONS = Array.isArray(window.__INITIAL_JS_TEST_QUESTIONS__)
    ? window.__INITIAL_JS_TEST_QUESTIONS__
    : [];
const CSRF_TOKEN = '{{ csrf_token() }}';
const JS_IS_ADMIN = Boolean(@json($isAdmin ?? false));
window.__IS_ADMIN__ = JS_IS_ADMIN;
const EXPLAIN_URL = '{{ localized_route('question.explain') }}';
const HINT_URL = '{{ localized_route('question.hint') }}';
const MARKER_THEORY_URL = '{{ localized_route('question.marker-theory') }}';
const TEST_SLUG = @json($test->slug);
</script>
@include('components.saved-test-js-persistence', ['mode' => $jsStateMode, 'savedState' => $savedState])
@include('components.saved-test-js-helpers')
@include('components.marker-theory-js')
@include('components.saved-test-js-v2-core')
@include('components.sticky-header-scroll')
@endsection
