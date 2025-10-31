@extends('layouts.app')

@section('title', __('Попередній перегляд сидера'))

@section('content')
    @php
        $previewType = $preview['type'] ?? 'questions';
        $previewTypeLabel = $previewType === 'page' ? __('Сторінка') : __('Питання');
        $questionPreviews = $preview['questions'] ?? collect();

        if (! $questionPreviews instanceof \Illuminate\Support\Collection) {
            $questionPreviews = collect($questionPreviews);
        }

        $questionGroups = $questionPreviews
            ->groupBy(function ($question) {
                $source = $question['source'] ?? null;

                return filled($source) ? $source : __('Без джерела');
            })
            ->sortKeys();

        $existingQuestionCount = $preview['existingQuestionCount'] ?? null;
        $pagePreview = $previewType === 'page' ? ($preview['page'] ?? null) : null;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">{{ __('Попередній перегляд сидера') }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ $previewType === 'page'
                            ? __('Переконайтеся, що сторінка виглядає коректно, перш ніж запускати сидер.')
                            : __('Переконайтеся, що питання та повʼязані дані виглядають коректно, перш ніж запускати сидер.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('seed-runs.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        {{ __('Повернутися до списку') }}
                    </a>
                    <form method="POST" action="{{ route('seed-runs.run') }}" data-preloader>
                        @csrf
                        <input type="hidden" name="class_name" value="{{ $className }}">
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-500 transition">
                            <i class="fa-solid fa-play"></i>
                            {{ __('Виконати сидер') }}
                        </button>
                    </form>
                </div>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Клас сидера') }}</dt>
                    <dd class="font-mono break-all">{{ $className }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Читабельна назва') }}</dt>
                    <dd>{{ $displayClassName }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Тип попереднього перегляду') }}</dt>
                    <dd>{{ $previewTypeLabel }}</dd>
                </div>

                @if($previewType === 'questions')
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Кількість питань у попередньому перегляді') }}</dt>
                        <dd>{{ $questionPreviews->count() }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Існуючих питань для цього сидера') }}</dt>
                        <dd>{{ $existingQuestionCount === null ? '—' : $existingQuestionCount }}</dd>
                    </div>
                @elseif($previewType === 'page' && $pagePreview)
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Слаг сторінки') }}</dt>
                        <dd class="font-mono break-all">{{ $pagePreview['slug'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Категорія') }}</dt>
                        <dd>{{ $pagePreview['category_title'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Локаль контенту') }}</dt>
                        <dd>{{ strtoupper($pagePreview['locale'] ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Кількість текстових блоків') }}</dt>
                        <dd>{{ $pagePreview['text_block_count'] ?? '0' }}</dd>
                    </div>
                @endif
            </dl>

            @if($previewType === 'questions' && !is_null($existingQuestionCount) && $existingQuestionCount > 0)
                <div class="mt-4 rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    {{ __('Деякі питання вже існують у базі даних для цього сидера. Попередній перегляд показує лише нові записи, які будуть створені.') }}
                </div>
            @endif
        </div>

        @if($previewType === 'questions')
            @if($questionPreviews->isEmpty())
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-sm text-gray-500">
                        {{ __('Немає питань для попереднього перегляду. Сидер, можливо, вже виконувався або не повертає даних.') }}
                    </p>
                </div>
            @else
                <div class="space-y-8">
                    @foreach($questionGroups as $sourceName => $questions)
                        <div class="border border-slate-200 rounded-lg bg-white" data-source-group>
                            <button
                                type="button"
                                class="w-full flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between px-4 py-3 text-left hover:bg-slate-50 transition"
                                data-source-group-toggle
                                aria-expanded="true"
                            >
                                <span class="text-lg font-semibold text-gray-800">
                                    {{ __('Джерело: :source', ['source' => $sourceName]) }}
                                </span>
                                <span class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-medium">
                                        {{ __('Кількість: :count', ['count' => $questions->count()]) }}
                                    </span>
                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 rotate-180" viewBox="0 0 20 20" fill="currentColor" data-source-group-icon>
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>

                            <div class="border-t border-slate-200 px-4 py-4 space-y-4" data-source-group-content>
                                @foreach($questions as $question)
                                    <div class="bg-white shadow rounded-lg p-6 space-y-4" data-question-preview>
                                    <div class="space-y-1">
                                        <h2 class="text-lg font-semibold text-gray-800">{!! $question['highlighted_text'] !!}</h2>
                                        <p class="text-xs text-gray-500 font-mono break-all">UUID: {{ $question['uuid'] }}</p>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Правильні відповіді') }}</h3>
                                        @php
                                            $filledAnswers = $question['answers']->filter(fn ($answer) => filled($answer['label']));
                                        @endphp
                                        @if($filledAnswers->isEmpty())
                                            <p class="mt-2 text-sm text-gray-500">{{ __('Коректні відповіді відсутні.') }}</p>
                                        @else
                                            <ul class="mt-2 space-y-1">
                                                @foreach($filledAnswers as $answer)
                                                    <li class="flex items-center gap-2">
                                                        <span class="font-mono text-xs text-gray-500">{{ $answer['marker'] }}</span>
                                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs font-medium">{{ $answer['label'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    <div class="space-y-3">
                                        <div class="border border-slate-200 rounded-lg" data-preview-section>
                                            <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                <span>{{ __('Деталі питання') }}</span>
                                                <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0Л5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div class="hidden border-t border-slate-200 px-3 py-3 text-sm text-slate-700 space-y-3" data-preview-section-content>
                                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div>
                                                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Категорія') }}</dt>
                                                        <dd>{{ $question['category'] ?? __('Без категорії') }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Джерело') }}</dt>
                                                        <dd>{{ $question['source'] ?? __('Без джерела') }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Рівень') }}</dt>
                                                        <dd>{{ $question['level'] ?? __('Невідомо') }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Складність') }}</dt>
                                                        <dd>{{ $question['difficulty'] }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Прапор') }}</dt>
                                                        <dd>{{ $question['flag'] }}</dd>
                                                    </div>
                                                </dl>

                                                @if($question['tags']->isNotEmpty())
                                                    <div>
                                                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Теги') }}</h4>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            @foreach($question['tags'] as $tag)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs">
                                                                    {{ $tag['name'] }}
                                                                    @if(!empty($tag['category']))
                                                                        <span class="ml-2 text-[10px] uppercase tracking-wide text-indigo-500">{{ $tag['category'] }}</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <div>
                                                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Текст питання без підсвічування') }}</h4>
                                                    <p class="mt-2 text-slate-700 whitespace-pre-line">{{ $question['raw_text'] }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        @if($question['options']->isNotEmpty())
                                            <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                    <span>{{ __('Варіанти відповідей') }}</span>
                                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0Л5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($question['options'] as $option)
                                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium">{{ $option }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($question['verb_hints']->isNotEmpty())
                                            <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                    <span>{{ __('Підказки дієслів') }}</span>
                                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0Л5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                                    <ul class="space-y-1 text-sm text-gray-600">
                                                        @foreach($question['verb_hints'] as $hint)
                                                            <li><span class="font-mono text-xs text-gray-500">{{ $hint['marker'] }}</span> — {{ $hint['label'] }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif

                                        @if($question['variants']->isNotEmpty())
                                            <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                    <span>{{ __('Варіанти формулювань') }}</span>
                                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0Л5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                                    @foreach($question['variants'] as $variant)
                                                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $variant }}</p>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($question['hints']->isNotEmpty())
                                            <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                    <span>{{ __('Підказки') }}</span>
                                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0Л5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                                    @foreach($question['hints'] as $hint)
                                                        <div class="border border-slate-200 rounded px-3 py-2 bg-slate-50">
                                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Провайдер') }}: <span class="font-normal text-slate-700">{{ $hint['provider'] }}</span></p>
                                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Локаль') }}: <span class="font-normal text-slate-700">{{ strtoupper($hint['locale']) }}</span></p>
                                                            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $hint['text'] }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($question['explanations']->isNotEmpty())
                                            <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                    <span>{{ __('Пояснення ChatGPT') }}</span>
                                                    <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0Л5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                                    @foreach($question['explanations'] as $explanation)
                                                        <div class="rounded border border-slate-200 px-3 py-2 bg-slate-50">
                                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Неправильна відповідь') }}: <span class="font-normal text-slate-700">{{ $explanation['wrong_answer'] ?? __('Невідомо') }}</span></p>
                                                            <p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $explanation['text'] }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @elseif($previewType === 'page')
            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Попередній перегляд сторінки') }}</h2>
                <p class="text-sm text-gray-500">
                    {{ __('Нижче показано рендер сторінки за допомогою публічного шаблону Engram. Перевірте, що всі блоки виглядають як очікується.') }}
                </p>

                @if($pagePreview && ! empty($pagePreview['html']))
                    <div
                        class="border border-gray-200 rounded-lg overflow-hidden"
                        data-page-preview
                        data-page-preview-html="{{ base64_encode($pagePreview['html']) }}"
                    >
                        <iframe
                            class="w-full"
                            style="min-height: 900px;"
                            data-page-preview-frame
                            loading="lazy"
                            title="{{ __('Попередній перегляд сторінки') }}"
                        ></iframe>
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{ __('Не вдалося згенерувати попередній перегляд сторінки.') }}</p>
                @endif
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-500">{{ __('Для цього типу сидера попередній перегляд поки недоступний.') }}</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('click', function (event) {
            const sourceToggle = event.target.closest('[data-source-group-toggle]');

            if (sourceToggle) {
                const group = sourceToggle.closest('[data-source-group]');

                if (!group) {
                    return;
                }

                const content = group.querySelector('[data-source-group-content]');

                if (!content) {
                    return;
                }

                const isExpanded = sourceToggle.getAttribute('aria-expanded') === 'true';
                const icon = sourceToggle.querySelector('[data-source-group-icon]');

                if (isExpanded) {
                    sourceToggle.setAttribute('aria-expanded', 'false');
                    content.classList.add('hidden');

                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }
                } else {
                    sourceToggle.setAttribute('aria-expanded', 'true');
                    content.classList.remove('hidden');

                    if (icon) {
                        icon.classList.add('rotate-180');
                    }
                }

                return;
            }

            const toggle = event.target.closest('[data-preview-section-toggle]');

            if (!toggle) {
                return;
            }

            const section = toggle.closest('[data-preview-section]');

            if (!section) {
                return;
            }

            const content = section.querySelector('[data-preview-section-content]');

            if (!content) {
                return;
            }

            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            const icon = toggle.querySelector('[data-preview-section-icon]');

            if (isExpanded) {
                toggle.setAttribute('aria-expanded', 'false');
                content.classList.add('hidden');

                if (icon) {
                    icon.classList.remove('rotate-180');
                }
            } else {
                toggle.setAttribute('aria-expanded', 'true');
                content.classList.remove('hidden');

                if (icon) {
                    icon.classList.add('rotate-180');
                }
            }
        });

        document.querySelectorAll('[data-page-preview-frame]').forEach((frame) => {
            const container = frame.closest('[data-page-preview]');
            const encodedHtml = container?.getAttribute('data-page-preview-html');

            if (encodedHtml) {
                try {
                    const binary = atob(encodedHtml);
                    const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
                    const decoder = new TextDecoder('utf-8');

                    frame.srcdoc = decoder.decode(bytes);
                } catch (error) {
                    try {
                        frame.srcdoc = decodeURIComponent(escape(atob(encodedHtml)));
                    } catch (fallbackError) {
                        // ignore decoding errors
                    }
                }
            }

            frame.addEventListener('load', () => {
                try {
                    const doc = frame.contentDocument || frame.contentWindow.document;
                    const height = doc?.body?.scrollHeight;

                    if (height) {
                        frame.style.height = `${height + 40}px`;
                    }
                } catch (error) {
                    // ignore sizing errors
                }
            });
        });
    </script>
@endsection
