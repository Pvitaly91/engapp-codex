@extends('layouts.catalog-public')

@section('title', __('words_test.title'))

@section('content')
@php
    $difficulty = $difficulty ?? 'easy';
    $tabs = [
        ['label' => 'Easy', 'difficulty' => 'easy', 'href' => localized_route('words.test')],
        ['label' => 'Medium', 'difficulty' => 'medium', 'href' => localized_route('words.test.medium')],
        ['label' => 'Hard', 'difficulty' => 'hard', 'href' => localized_route('words.test.hard')],
    ];

    $heroDescriptionKey = [
        'easy' => 'words_test.description_easy',
        'medium' => 'words_test.description_medium',
        'hard' => 'words_test.description_hard',
    ][$difficulty] ?? 'words_test.description_easy';

    $heroDescription = __($heroDescriptionKey);

    $modeCards = [
        ['label' => 'Mode', 'value' => ucfirst($difficulty), 'tone' => 'bg-ocean'],
        ['label' => __('words_test.active_lang'), 'value' => strtoupper($siteLocale), 'tone' => 'bg-amber'],
        ['label' => __('words_test.study_lang'), 'value' => $singleStudyLangName ?: ($studyLangOptions[$studyLang] ?? __('words_test.select_study_lang')), 'tone' => 'bg-emerald-500'],
        ['label' => __('words_test.errors'), 'value' => '3 max', 'tone' => 'bg-slate-800 dark:bg-slate-200'],
    ];
@endphp

<div class="nd-page">
    <style>
        @keyframes fade-in-soft {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pop-in {
            0% { transform: scale(0.97); opacity: 0; }
            60% { transform: scale(1.015); opacity: 1; }
            100% { transform: scale(1); }
        }

        @keyframes choice-glow {
            0% { transform: scale(1); box-shadow: 0 12px 28px rgba(17, 38, 63, 0.10); }
            50% { transform: scale(1.015); box-shadow: 0 16px 34px rgba(17, 38, 63, 0.18); }
            100% { transform: scale(1); box-shadow: 0 12px 28px rgba(17, 38, 63, 0.10); }
        }

        @keyframes choice-shake {
            0%, 100% { transform: translateX(0); }
            15% { transform: translateX(-6px); }
            30% { transform: translateX(6px); }
            45% { transform: translateX(-5px); }
            60% { transform: translateX(5px); }
            75% { transform: translateX(-3px); }
            90% { transform: translateX(3px); }
        }

        .wt-animate-soft { animation: fade-in-soft 280ms ease; }
        .wt-animate-pop { animation: pop-in 220ms ease; }
        .wt-animate-choice { animation: choice-glow 700ms ease; }
        .wt-animate-shake { animation: choice-shake 520ms ease; }

        .wt-option {
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--text);
            border-radius: 1.35rem;
            padding: 1rem 1.1rem;
            text-align: left;
            font-size: 0.98rem;
            font-weight: 700;
            line-height: 1.45;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
            box-shadow: 0 12px 28px rgba(17, 38, 63, 0.08);
        }

        .wt-option:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--accent) 36%, var(--line));
            background: color-mix(in srgb, var(--surface-strong) 88%, #eef5ff);
        }

        .wt-option:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .wt-option-correct {
            border-color: rgba(22, 163, 74, 0.35);
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.12), rgba(22, 163, 74, 0.04));
            color: #166534;
        }

        .dark .wt-option-correct {
            color: #bbf7d0;
        }

        .wt-option-wrong {
            border-color: rgba(220, 38, 38, 0.35);
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.12), rgba(220, 38, 38, 0.04));
            color: #b91c1c;
        }

        .dark .wt-option-wrong {
            color: #fecaca;
        }

        .wt-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.6rem 0.95rem;
            font-size: 0.73rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .wt-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border-radius: 999px;
            padding: 0.55rem 0.95rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .wt-chip-positive {
            background: rgba(22, 163, 74, 0.12);
            color: #166534;
        }

        .wt-chip-negative {
            background: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
        }

        .dark .wt-chip-positive { color: #bbf7d0; }
        .dark .wt-chip-negative { color: #fecaca; }

        .wt-primary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            border-radius: 1.15rem;
            background: var(--accent);
            color: #fff;
            padding: 0.95rem 1.3rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
            box-shadow: 0 16px 32px rgba(47, 103, 177, 0.22);
        }

        .wt-primary-btn:hover {
            transform: translateY(-1px);
            background: #245592;
        }

        .wt-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border-radius: 1.1rem;
            border: 1px solid var(--line);
            background: var(--surface-strong);
            color: var(--text);
            padding: 0.85rem 1.15rem;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease;
        }

        .wt-secondary-btn:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--accent) 40%, var(--line));
        }

        .wt-modal-backdrop {
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.16), rgba(15, 23, 42, 0.58));
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
    </style>

    <section class="nd-section-tight relative border-b" style="border-color: var(--line);">
        <div class="absolute left-[12%] top-10 hidden h-24 w-24 rounded-full border-[16px] border-ocean/40 lg:block"></div>
        <div class="absolute right-[10%] top-10 hidden h-20 w-20 rounded-full bg-amber/80 lg:block"></div>
        <div class="absolute bottom-0 right-0 hidden h-56 w-16 rounded-tl-[2.5rem] bg-ocean lg:block"></div>

        <div class="relative grid gap-6 lg:grid-cols-[1.02fr_0.98fr] lg:items-center">
            <div class="max-w-3xl">
                <span class="wt-pill soft-accent border" style="border-color: var(--line); color: var(--accent);">
                    {{ __('public.nav.words_test') }}
                </span>
                <h1 class="mt-6 font-display text-3xl font-extrabold leading-[1.04] sm:text-4xl">
                    {{ __('words_test.quick_test') }}
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 sm:text-xl" style="color: var(--muted);">
                    {{ $heroDescription }}
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <button id="reset-btn" class="wt-primary-btn" type="button">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>{{ __('words_test.restart') }}</span>
                    </button>
                    <a href="#words-test-panel" class="wt-secondary-btn">
                        <span>{{ __('public.common.go_to') }}</span>
                        <span>+</span>
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($modeCards as $card)
                    <article class="rounded-[28px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ $card['label'] }}</p>
                                <p class="mt-3 font-display text-2xl font-extrabold leading-tight break-words">{{ $card['value'] }}</p>
                            </div>
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-[18px] {{ $card['tone'] }} text-sm font-extrabold text-white dark:text-slate-950">
                                {{ strtoupper(mb_substr((string) $card['value'], 0, 2)) }}
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="nd-section">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
            <div id="words-test-panel" class="min-w-0">
                <article class="rounded-[30px] border shadow-card surface-card-strong" style="border-color: var(--line);" x-data>
                    <div class="border-b p-6 sm:p-7" style="border-color: var(--line);">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('words_test.subtitle') }}</p>
                                    <h2 class="mt-2 font-display text-2xl font-extrabold leading-tight">{{ __('words_test.title') }}</h2>
                                </div>
                                <div class="flex flex-wrap gap-2" role="tablist" aria-label="{{ __('words_test.difficulty_modes') }}">
                                    @foreach ($tabs as $tab)
                                        <a
                                            href="{{ $tab['href'] }}"
                                            role="tab"
                                            aria-selected="{{ $tab['difficulty'] === $difficulty ? 'true' : 'false' }}"
                                            class="inline-flex items-center rounded-full border px-4 py-2.5 text-sm font-bold transition {{ $tab['difficulty'] === $difficulty ? 'bg-ocean text-white shadow-card border-ocean' : 'surface-card text-[var(--text)] hover:-translate-y-0.5' }}"
                                            style="{{ $tab['difficulty'] === $difficulty ? '' : 'border-color: var(--line);' }}"
                                        >
                                            {{ $tab['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);" id="study-lang-selector">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('words_test.study_lang') }}</p>
                                        <p class="mt-2 text-sm leading-6" style="color: var(--muted);">{{ __('words_test.study_lang_hint') }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        @if ($singleStudyLangName)
                                            <span class="inline-flex rounded-full bg-emerald-500/12 px-4 py-2 text-sm font-bold text-emerald-700 dark:text-emerald-200">{{ $singleStudyLangName }}</span>
                                        @elseif (count($studyLangOptions) > 1)
                                            <select id="study-lang" class="rounded-full border px-4 py-3 text-sm font-bold shadow-sm transition focus:outline-none focus:ring-4" style="border-color: var(--line); background: var(--surface-strong); color: var(--text); box-shadow: 0 10px 24px rgba(17, 38, 63, 0.08);">
                                                @foreach ($studyLangOptions as $langCode => $langName)
                                                    <option value="{{ $langCode }}" {{ $studyLang === $langCode ? 'selected' : '' }}>{{ $langName }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>

                                <div id="study-lang-warning" class="mt-4 rounded-[18px] border border-amber-300/70 bg-amber-50/90 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100 {{ (!$studyLang && $siteLocale === 'en' && count($studyLangOptions) > 1) ? '' : 'hidden' }}">
                                    {{ __('words_test.select_study_lang_warning') }}
                                </div>

                                @if(empty($studyLangOptions))
                                    <div class="mt-4 rounded-[18px] border border-red-300/70 bg-red-50/90 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">
                                        {{ __('words_test.no_langs_available') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-6 sm:p-7">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div id="question-label-wrap" class="wt-chip soft-accent" style="color: var(--accent);">
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-ocean"></span>
                                <span id="question-label">{{ __('words_test.loading') }}</span>
                            </div>
                            <div class="text-sm font-semibold" style="color: var(--muted);" id="queue-counter"></div>
                        </div>

                        <div class="mt-7 space-y-7" id="question-wrapper">
                            <div class="rounded-[24px] border p-5 surface-card" style="border-color: var(--line);">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('words_test.translation') }}</p>
                                <div id="question-prompt" class="mt-3 font-display text-3xl font-extrabold leading-tight text-[var(--text)] sm:text-[2.2rem]">{{ __('words_test.wait') }}</div>
                                <p id="question-tags" class="mt-3 text-sm leading-6" style="color: var(--muted);"></p>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2" id="options"></div>

                            <div id="input-wrapper" class="hidden space-y-4">
                                <form id="answer-form" class="space-y-4">
                                    <div class="space-y-2">
                                        <label for="answer-input" class="text-sm font-bold" style="color: var(--muted);">{{ __('words_test.your_answer') }}</label>
                                        <div class="relative">
                                            <input
                                                id="answer-input"
                                                type="text"
                                                name="answer"
                                                autocomplete="off"
                                                class="w-full rounded-[22px] border px-4 py-4 text-base font-bold shadow-sm transition focus:outline-none focus:ring-4"
                                                style="border-color: var(--line); background: var(--surface); color: var(--text); box-shadow: 0 10px 24px rgba(17, 38, 63, 0.08);"
                                                placeholder="{{ __('words_test.enter_word') }}"
                                            >
                                            <ul id="suggestions" class="absolute left-0 top-full z-10 mt-2 hidden max-h-60 w-full overflow-auto rounded-[20px] border shadow-card surface-card-strong" style="border-color: var(--line);"></ul>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <button id="submit-answer" type="submit" class="wt-primary-btn">
                                            {{ __('words_test.check') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="empty-state" class="hidden rounded-[24px] border border-dashed p-8 text-center surface-card" style="border-color: var(--line); color: var(--muted);">
                            <p class="font-display text-2xl font-extrabold text-[var(--text)]">{{ __('words_test.no_questions') }}</p>
                            <p class="mt-3 text-sm leading-6">{{ __('words_test.no_questions_msg') }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="min-w-0">
                <div class="sticky top-24 space-y-6">
                    <section class="rounded-[28px] border p-5 shadow-card surface-card-strong" style="border-color: var(--line);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--accent);">{{ __('words_test.progress') }}</p>
                                <h2 class="mt-2 font-display text-xl font-extrabold leading-none">{{ __('words_test.title') }}</h2>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-[0.16em] soft-accent" style="color: var(--accent);" id="percentage">0%</span>
                        </div>

                        <div class="mt-5 h-3 rounded-full bg-slate-200/80 dark:bg-slate-700/70">
                            <div id="progress-bar" class="h-3 rounded-full bg-ocean transition-all duration-500" style="width: 0%"></div>
                        </div>

                        <dl class="mt-5 grid gap-3">
                            <div class="rounded-[20px] border px-4 py-4 surface-card" style="border-color: var(--line);">
                                <dt class="text-[11px] font-extrabold uppercase tracking-[0.22em]" style="color: var(--muted);">{{ __('words_test.total') }}</dt>
                                <dd id="stat-total" class="mt-2 font-display text-2xl font-extrabold">0</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-[20px] border border-emerald-200/80 bg-emerald-50/80 px-4 py-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                    <dt class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-emerald-700 dark:text-emerald-200">{{ __('words_test.correct') }}</dt>
                                    <dd id="stat-correct" class="mt-2 font-display text-2xl font-extrabold text-emerald-700 dark:text-emerald-200">0</dd>
                                </div>
                                <div class="rounded-[20px] border border-red-200/80 bg-red-50/80 px-4 py-4 dark:border-red-500/20 dark:bg-red-500/10">
                                    <dt class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-red-700 dark:text-red-200">{{ __('words_test.errors') }}</dt>
                                    <dd id="stat-wrong" class="mt-2 font-display text-2xl font-extrabold text-red-700 dark:text-red-200">0</dd>
                                </div>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-[28px] border p-5 shadow-card surface-card" style="border-color: var(--line);" id="feedback" hidden>
                        <div id="feedback-chip" class="wt-chip"></div>
                        <div class="mt-4 space-y-2">
                            <p id="feedback-title" class="font-display text-2xl font-extrabold leading-tight"></p>
                            <p id="feedback-body" class="text-sm leading-6" style="color: var(--muted);"></p>
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-emerald-200/80 bg-[linear-gradient(135deg,rgba(16,185,129,0.10),rgba(255,255,255,0.92))] p-6 shadow-card dark:border-emerald-500/20 dark:bg-[linear-gradient(135deg,rgba(16,185,129,0.18),rgba(22,37,61,0.95))]" id="completion" hidden>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-emerald-500 text-white shadow-card">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-emerald-700 dark:text-emerald-200">{{ __('words_test.test_completed') }}</p>
                                <p class="mt-1 font-display text-xl font-extrabold text-emerald-900 dark:text-emerald-50">{{ __('words_test.all_words_done') }}</p>
                            </div>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-emerald-800 dark:text-emerald-100">{{ __('words_test.can_restart') }}</p>
                    </section>
                </div>
            </aside>
        </div>
    </section>

    <div id="failure-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 wt-modal-backdrop wt-animate-soft"></div>
        <div id="failure-card" class="relative w-full max-w-xl rounded-[30px] border border-red-300/70 p-6 shadow-[0_26px_80px_rgba(15,23,42,0.28)] surface-card-strong wt-animate-pop dark:border-red-500/30 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[20px] bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 8v4m0 4h.01M4.93 4.93l14.14 14.14" />
                    </svg>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.22em] text-red-600 dark:text-red-200">{{ __('words_test.test_failed') }}</p>
                        <p class="mt-2 font-display text-3xl font-extrabold leading-tight">{{ __('words_test.error_limit') }}</p>
                    </div>
                    <p class="text-sm leading-6" style="color: var(--muted);">{{ __('words_test.error_limit_msg') }}</p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <button id="retry-btn" class="wt-primary-btn !bg-red-600 hover:!bg-red-700" type="button">
                            {{ __('words_test.retry') }}
                        </button>
                        <button id="close-failure" class="wt-secondary-btn" type="button">
                            {{ __('words_test.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.wordsTestI18n = @json(__('words_test'));

    document.addEventListener('DOMContentLoaded', () => {
      const i18n = window.wordsTestI18n;
      const difficulty = "{{ $difficulty }}";
      const isEasy = difficulty === 'easy';
      const isMedium = difficulty === 'medium';

      const stateUrl = "{{ $stateUrl }}";
      const checkUrl = "{{ $checkUrl }}";
      const resetUrl = "{{ $resetUrl }}";
      const setStudyLangUrl = "{{ $setStudyLangUrl }}";
      const csrfToken = '{{ csrf_token() }}';

      const questionLabel = document.getElementById('question-label');
      const queueCounter = document.getElementById('queue-counter');
      const questionPrompt = document.getElementById('question-prompt');
      const questionTags = document.getElementById('question-tags');
      const optionsWrapper = document.getElementById('options');
      const inputWrapper = document.getElementById('input-wrapper');
      const answerInput = document.getElementById('answer-input');
      const answerForm = document.getElementById('answer-form');
      const submitAnswerBtn = document.getElementById('submit-answer');
      const suggestionsList = document.getElementById('suggestions');
      const percentageEl = document.getElementById('percentage');
      const progressBar = document.getElementById('progress-bar');
      const statTotal = document.getElementById('stat-total');
      const statCorrect = document.getElementById('stat-correct');
      const statWrong = document.getElementById('stat-wrong');
      const feedback = document.getElementById('feedback');
      const feedbackChip = document.getElementById('feedback-chip');
      const feedbackTitle = document.getElementById('feedback-title');
      const feedbackBody = document.getElementById('feedback-body');
      const completion = document.getElementById('completion');
      const failureModal = document.getElementById('failure-modal');
      const failureCard = document.getElementById('failure-card');
      const questionWrapper = document.getElementById('question-wrapper');
      const emptyState = document.getElementById('empty-state');
      const resetBtn = document.getElementById('reset-btn');
      const retryBtn = document.getElementById('retry-btn');
      const closeFailure = document.getElementById('close-failure');
      const studyLangSelect = document.getElementById('study-lang');
      const studyLangWarning = document.getElementById('study-lang-warning');

      let currentQuestion = null;
      let loading = false;
      let suggestionTimeout = null;
      let currentStudyLang = "{{ $studyLang ?? '' }}";
      let currentSiteLocale = "{{ $siteLocale }}";
      let availableStudyLangs = @json($availableStudyLangs ?? []);

      function animate(el, className = 'wt-animate-soft') {
        if (!el) return;
        el.classList.remove(className);
        void el.offsetWidth;
        el.classList.add(className);
      }

      function updateStudyLangUI(studyLang, siteLocale, showWarning = false) {
        currentStudyLang = studyLang;
        currentSiteLocale = siteLocale;

        if (studyLangSelect && studyLang) {
          studyLangSelect.value = studyLang;
        }

        if (studyLangWarning) {
          studyLangWarning.classList.toggle('hidden', !showWarning);
        }
      }

      async function setStudyLanguage(lang) {
        loading = true;
        toggleAnswerControls(false);

        try {
          const formData = new FormData();
          formData.append('lang', lang);
          formData.append('difficulty', difficulty);

          const response = await fetch(setStudyLangUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              Accept: 'application/json',
            },
            body: formData,
          });

          if (!response.ok) {
            loading = false;
            toggleAnswerControls(true);
            return;
          }

          const data = await response.json();
          if (data.ok) {
            updateStudyLangUI(data.studyLang, data.siteLocale, false);
            await fetchState();
          }
        } catch (e) {
          console.error('Error setting study language:', e);
        }

        loading = false;
      }

      async function fetchState() {
        loading = true;
        toggleAnswerControls(false);
        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        loading = false;
        updateState(data);
      }

      function updateState(data) {
        availableStudyLangs = Array.isArray(data.availableStudyLangs) ? data.availableStudyLangs : [];
        const shouldShowWarning = data.needsStudyLanguage && data.siteLocale === 'en' && availableStudyLangs.length >= 2;
        updateStudyLangUI(data.studyLang, data.siteLocale, shouldShowWarning);

        if (data.needsStudyLanguage) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          questionLabel.textContent = i18n.select_study_lang;
          queueCounter.textContent = '';
          toggleAnswerControls(false);
          renderStats({ correct: 0, wrong: 0, total: 0 }, 0, 0);
          return;
        }

        renderStats(data.stats, data.percentage, data.totalCount);
        completion.hidden = !data.completed;
        toggleFailureModal(data.failed);

        if (data.failed) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.add('hidden');
          questionLabel.textContent = i18n.test_failed;
          queueCounter.textContent = '';
          toggleAnswerControls(false);
          return;
        }

        if (!data.question) {
          currentQuestion = null;
          questionWrapper.classList.add('hidden');
          emptyState.classList.remove('hidden');
          queueCounter.textContent = '';
          questionLabel.textContent = data.completed ? i18n.all_done : i18n.no_question;
          toggleAnswerControls(false);
          return;
        }

        emptyState.classList.add('hidden');
        questionWrapper.classList.remove('hidden');
        currentQuestion = data.question;
        renderQuestion(data.question, data.totalCount, data.stats.total);
        feedback.hidden = true;
        completion.hidden = true;
      }

      function renderStats(stats, percentage, totalCount) {
        statTotal.textContent = `${stats.total} / ${totalCount}`;
        statCorrect.textContent = stats.correct;
        statWrong.textContent = stats.wrong;
        percentageEl.textContent = `${percentage}%`;
        progressBar.style.width = `${Math.min(percentage, 100)}%`;
      }

      function renderQuestion(question, totalCount, totalAnswered) {
        questionLabel.textContent = isEasy ? i18n.choose_translation : i18n.enter_word;

        const questionOfText = i18n.question_of
          .replace(':current', totalAnswered + 1)
          .replace(':total', totalCount);
        queueCounter.textContent = questionOfText;
        questionPrompt.textContent = question.prompt;
        questionTags.textContent = (question.tags || []).join(', ');

        optionsWrapper.classList.toggle('hidden', !isEasy);
        inputWrapper.classList.toggle('hidden', isEasy);

        if (isEasy) {
          optionsWrapper.innerHTML = '';
          question.options.forEach((option) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'wt-option';
            btn.textContent = option;
            btn.dataset.value = option;
            btn.addEventListener('click', () => submitAnswer(option, btn));
            optionsWrapper.appendChild(btn);
            animate(btn, 'wt-animate-soft');
          });
        } else {
          optionsWrapper.innerHTML = '';
          answerInput.value = '';
          answerInput.placeholder = i18n.enter_word;
          hideSuggestions();
          setTimeout(() => answerInput?.focus(), 50);
        }

        toggleAnswerControls(!loading);
        animate(questionPrompt);
        animate(optionsWrapper);
      }

      function toggleOptions(enabled) {
        optionsWrapper.querySelectorAll('button').forEach((btn) => {
          btn.disabled = !enabled;
          btn.classList.toggle('opacity-60', !enabled);
          btn.classList.toggle('cursor-not-allowed', !enabled);
        });
      }

      function toggleAnswerControls(enabled) {
        if (isEasy) {
          toggleOptions(enabled);
          return;
        }

        if (answerInput) {
          answerInput.disabled = !enabled;
          answerInput.classList.toggle('opacity-60', !enabled);
        }

        if (submitAnswerBtn) {
          submitAnswerBtn.disabled = !enabled;
          submitAnswerBtn.classList.toggle('opacity-60', !enabled);
        }
      }

      async function submitAnswer(answer, selectedButton) {
        if (!currentQuestion || loading) return;

        const value = typeof answer === 'string' ? answer : (answerInput?.value ?? '');
        const preparedAnswer = value.trim();
        if (!preparedAnswer) {
          return;
        }

        loading = true;
        toggleAnswerControls(false);
        hideSuggestions();

        const formData = new FormData();
        formData.append('word_id', currentQuestion.word_id);
        formData.append('answer', preparedAnswer);

        const response = await fetch(checkUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
          },
          body: formData,
        });

        if (!response.ok) {
          loading = false;
          toggleAnswerControls(true);
          return;
        }

        const data = await response.json();
        loading = false;

        highlightSelection(selectedButton, data.result?.isCorrect);

        setTimeout(() => {
          renderStats(data.stats, data.percentage, data.totalCount);
          showFeedback(data.result);

          currentQuestion = data.question;
          completion.hidden = !data.completed;
          toggleFailureModal(data.failed);

          if (data.failed) {
            questionWrapper.classList.add('hidden');
            emptyState.classList.add('hidden');
            questionLabel.textContent = i18n.test_failed;
            queueCounter.textContent = '';
            toggleAnswerControls(false);
            return;
          }

          if (data.completed || !data.question) {
            completion.hidden = false;
            questionWrapper.classList.add('hidden');
            emptyState.classList.remove('hidden');
            questionLabel.textContent = i18n.all_done;
            queueCounter.textContent = '';
            toggleAnswerControls(false);
          } else {
            renderQuestion(data.question, data.totalCount, data.stats.total);
          }
        }, 1000);
      }

      function toggleFailureModal(show) {
        failureModal.classList.toggle('hidden', !show);
        if (show) {
          failureModal.classList.add('flex');
          failureCard.classList.remove('wt-animate-pop');
          void failureCard.offsetWidth;
          failureCard.classList.add('wt-animate-pop');
        } else {
          failureModal.classList.remove('flex');
        }
      }

      function highlightSelection(button, isCorrect) {
        if (!button) return;
        button.classList.remove('wt-option-correct', 'wt-option-wrong', 'wt-animate-choice', 'wt-animate-shake');
        void button.offsetWidth;
        if (isCorrect) {
          button.classList.add('wt-option-correct', 'wt-animate-choice');
        } else {
          button.classList.add('wt-option-wrong', 'wt-animate-choice', 'wt-animate-shake');
        }
        setTimeout(() => {
          button.classList.remove('wt-option-correct', 'wt-option-wrong', 'wt-animate-choice', 'wt-animate-shake');
        }, 1000);
      }

      function showFeedback(result) {
        feedback.hidden = false;
        animate(feedback, 'wt-animate-pop');
        const isCorrect = result.isCorrect;
        feedbackChip.className = `wt-chip ${isCorrect ? 'wt-chip-positive' : 'wt-chip-negative'}`;
        feedbackChip.textContent = isCorrect ? i18n.feedback_correct : i18n.feedback_error;

        if (result.questionType === 'en_to_translation') {
          feedbackTitle.textContent = `${result.word} → ${result.correctAnswer}`;
        } else {
          feedbackTitle.textContent = `${result.translation} → ${result.correctAnswer}`;
        }

        feedbackBody.textContent = isCorrect
          ? i18n.feedback_correct_msg
          : i18n.feedback_error_msg;
      }

      async function resetTest(button) {
        if (button) {
          button.disabled = true;
          button.classList.add('opacity-60');
        }

        const response = await fetch(resetUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
          },
        });

        const data = await response.json();
        hideSuggestions();
        updateState(data);

        if (button) {
          button.disabled = false;
          button.classList.remove('opacity-60');
        }
      }

      function hideSuggestions() {
        if (!suggestionsList) return;
        suggestionsList.innerHTML = '';
        suggestionsList.classList.add('hidden');
      }

      function renderSuggestions(query) {
        if (!isMedium || !suggestionsList) return;
        if (suggestionTimeout) {
          clearTimeout(suggestionTimeout);
        }

        const term = query.trim();
        if (!term) {
          hideSuggestions();
          return;
        }

        suggestionTimeout = setTimeout(async () => {
          try {
            const response = await fetch(`/api/search?lang=en&q=${encodeURIComponent(term)}`);
            if (!response.ok) {
              hideSuggestions();
              return;
            }

            const data = await response.json();
            if (answerInput.value.trim() !== term) {
              return;
            }

            suggestionsList.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
              hideSuggestions();
              return;
            }

            data.forEach((item) => {
              const value = item.en ?? item.word ?? item;
              if (!value) return;
              const li = document.createElement('li');
              li.textContent = value;
              li.className = 'cursor-pointer border-b px-4 py-3 text-sm font-semibold transition last:border-b-0 hover:bg-blue-50/80 dark:hover:bg-slate-800/70';
              li.style.borderColor = 'var(--line)';
              li.addEventListener('click', () => {
                answerInput.value = value;
                hideSuggestions();
                answerInput.focus();
              });
              suggestionsList.appendChild(li);
            });

            if (suggestionsList.childElementCount > 0) {
              suggestionsList.classList.remove('hidden');
            } else {
              hideSuggestions();
            }
          } catch (e) {
            hideSuggestions();
          }
        }, 200);
      }

      if (studyLangSelect) {
        studyLangSelect.addEventListener('change', (event) => {
          const newLang = event.target.value;
          if (newLang && newLang !== currentStudyLang) {
            setStudyLanguage(newLang);
          }
        });
      }

      if (answerForm && !isEasy) {
        answerForm.addEventListener('submit', (event) => {
          event.preventDefault();
          submitAnswer();
        });
      }

      if (answerInput && !isEasy) {
        answerInput.addEventListener('input', (event) => {
          if (isMedium) {
            renderSuggestions(event.target.value);
          }
        });

        answerInput.addEventListener('focus', () => {
          if (isMedium && suggestionsList && suggestionsList.childElementCount > 0) {
            suggestionsList.classList.remove('hidden');
          }
        });
      }

      document.addEventListener('click', (event) => {
        if (suggestionsList && !suggestionsList.contains(event.target) && event.target !== answerInput) {
          hideSuggestions();
        }
      });

      resetBtn.addEventListener('click', () => resetTest(resetBtn));
      retryBtn.addEventListener('click', () => resetTest(retryBtn));
      closeFailure.addEventListener('click', () => toggleFailureModal(false));

      fetchState();
    });
</script>
@endsection
