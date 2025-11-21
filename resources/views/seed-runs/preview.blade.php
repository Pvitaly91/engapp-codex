@extends('layouts.app')

@section('title', __('Попередній перегляд сидера'))

@section('content')
    @php
        use Illuminate\Support\Str;

        $previewType = $preview['type'] ?? 'questions';
        $previewTypeLabel = match ($previewType) {
            'page' => __('Сторінка'),
            'category' => __('Категорія'),
            default => __('Питання'),
        };
        $questionPreviews = $preview['questions'] ?? collect();

        if (! $questionPreviews instanceof \Illuminate\Support\Collection) {
            $questionPreviews = collect($questionPreviews);
        }

        $existingQuestionCount = $preview['existingQuestionCount'] ?? null;
        $pagePreview = $previewType === 'page' ? ($preview['page'] ?? null) : null;
        $categoryPreview = $previewType === 'category' ? ($preview['category'] ?? null) : null;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">{{ __('Попередній перегляд сидера') }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ match ($previewType) {
                            'page' => __('Переконайтеся, що сторінка виглядає коректно, перш ніж запускати сидер.'),
                            'category' => __('Переконайтеся, що опис категорії виглядає коректно, перш ніж запускати сидер.'),
                            default => __('Переконайтеся, що питання та пов’язані дані виглядають коректно, перш ніж запускати сидер.'),
                        } }}
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
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Кількість питань у сидері') }}</dt>
                        <dd>{{ $questionPreviews->count() }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Існуючих питань із такими UUID') }}</dt>
                        <dd>{{ $existingQuestionCount === null ? __('—') : $existingQuestionCount }}</dd>
                    </div>
                @elseif($previewType === 'page' && $pagePreview)
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Заголовок сторінки') }}</dt>
                        <dd>{{ $pagePreview['title'] ?? __('Без назви') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('URL сторінки') }}</dt>
                        <dd>{{ $pagePreview['url'] ?? __('Немає посилання') }}</dd>
                    </div>
                @elseif($previewType === 'category' && $categoryPreview)
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Категорія') }}</dt>
                        <dd>{{ $categoryPreview['title'] ?? __('Без назви') }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('URL категорії') }}</dt>
                        <dd>{{ $categoryPreview['url'] ?? __('Немає посилання') }}</dd>
                    </div>
                @endif
            </dl>

            @if($previewType === 'questions' && ! is_null($existingQuestionCount) && $existingQuestionCount > 0)
                <div class="mt-4 rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    {{ __('Деякі з цих питань вже існують у базі. Переконайтеся, що дублікати — це очікувана поведінка або змініть UUID.') }}
                </div>
            @endif
        </div>

        @if($previewType === 'questions')
            {{-- Tags Summary Block - Moved to top and made collapsible --}}
            @php
                $tagsSummary = collect($preview['tagsSummary'] ?? []);
            @endphp

            @if($tagsSummary->isNotEmpty())
                <div class="bg-white shadow rounded-lg overflow-hidden" data-tags-summary-section>
                    <button type="button"
                            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left transition hover:bg-slate-50"
                            data-tags-summary-toggle
                            aria-expanded="true">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">{{ __('Усі теги в сидері') }}</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ __('Перелік усіх унікальних тегів, які присутні в цьому сидері. Нові теги будуть додані до бази даних під час виконання сидера.') }}
                            </p>
                            <p class="text-sm text-purple-600 font-medium mt-1 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                </svg>
                                {{ __('Клікніть на тег, щоб побачити питання з цим тегом') }}
                            </p>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200 rotate-180"
                             viewBox="0 0 20 20"
                             fill="currentColor"
                             data-tags-summary-icon>
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="border-t border-slate-200 px-6 py-4" data-tags-summary-content>
                        @php
                            $newTags = $tagsSummary->where('is_new', true);
                            $existingTags = $tagsSummary->where('is_new', false);
                        @endphp

                        <div class="space-y-4">
                            @if($newTags->isNotEmpty())
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs font-medium">
                                            {{ __('Нові') }}
                                        </span>
                                        <span>{{ trans_choice('{1} :count тег|[2,4] :count теги|[5,*] :count тегів', $newTags->count(), ['count' => $newTags->count()]) }}</span>
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($newTags as $tag)
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium hover:bg-green-100 hover:border-green-300 transition cursor-pointer"
                                                    data-tag-filter="{{ $tag['name'] }}"
                                                    title="{{ __('Клікніть, щоб побачити питання з цим тегом') }}">
                                                <svg class="w-4 h-4 mr-1.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                {{ $tag['name'] }}
                                                @if(filled($tag['category']))
                                                    <span class="ml-1.5 text-xs text-green-600">({{ $tag['category'] }})</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($existingTags->isNotEmpty())
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-medium">
                                            {{ __('Існуючі') }}
                                        </span>
                                        <span>{{ trans_choice('{1} :count тег|[2,4] :count теги|[5,*] :count тегів', $existingTags->count(), ['count' => $existingTags->count()]) }}</span>
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($existingTags as $tag)
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm font-medium hover:bg-blue-100 hover:border-blue-300 transition cursor-pointer"
                                                    data-tag-filter="{{ $tag['name'] }}"
                                                    title="{{ __('Клікніть, щоб побачити питання з цим тегом') }}">
                                                <svg class="w-4 h-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ $tag['name'] }}
                                                @if(filled($tag['category']))
                                                    <span class="ml-1.5 text-xs text-blue-600">({{ $tag['category'] }})</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($questionPreviews->isEmpty())
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-sm text-gray-500">
                        {{ __('У цьому сидері немає питань для перегляду. Можливо, він створює лише пов’язані дані.') }}
                    </p>
                </div>
            @else
                @php
                    $groupedBySource = $questionPreviews->groupBy(function ($question) {
                        $source = data_get($question, 'source');

                        return filled($source) ? $source : __('Без джерела');
                    });
                @endphp

                <div class="space-y-6">
                    @foreach($groupedBySource as $sourceLabel => $questions)
                        @php
                            $panelId = 'source-panel-' . Str::slug($sourceLabel . '-' . $loop->index);
                            $isExpanded = $loop->first;
                            $count = $questions->count();
                            $questionsLabel = trans_choice('{1} :count питання|[2,4] :count питання|[5,*] :count питань', $count, ['count' => $count]);
                        @endphp

                        <div class="rounded-xl border border-slate-200 bg-white shadow-sm" data-source-group>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50"
                                data-source-toggle
                                aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
                                aria-controls="{{ $panelId }}"
                            >
                                <div>
                                    <span class="block text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">{{ __('Джерело') }}</span>
                                    <span class="mt-1 block text-lg font-bold text-slate-800 sm:text-xl">{{ $sourceLabel }}</span>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-slate-100 px-3 py-0.5 text-xs font-medium text-slate-600">
                                        {{ $questionsLabel }}
                                    </span>
                                </div>
                                <svg
                                    class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }}"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    data-source-toggle-icon
                                >
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                id="{{ $panelId }}"
                                class="space-y-4 border-t border-slate-200 px-5 py-5 {{ $isExpanded ? '' : 'hidden' }}"
                                data-source-content
                            >
                                @foreach($questions as $question)
                                    @php
                                        $answers = collect(data_get($question, 'answers', []));
                                        $filledAnswers = $answers->filter(fn ($answer) => filled(data_get($answer, 'label')));

                                        $questionTags = collect(data_get($question, 'tags', []));
                                        $questionTopics = collect(data_get($question, 'topics', []));
                                        $questionOptions = collect(data_get($question, 'options', []));
                                        $questionVerbHints = collect(data_get($question, 'verb_hints', []));
                                        $questionVariants = collect(data_get($question, 'variants', []));
                                        $questionHints = collect(data_get($question, 'hints', []));
                                        $questionExplanations = collect(data_get($question, 'explanations', []));
                                    @endphp

                                    <div class="rounded-xl border border-slate-200 bg-white/60 p-6 shadow-sm transition-all" 
                                         data-question-preview
                                         data-question-tags="{{ $questionTags->pluck('name')->implode(',') }}">
                                        <div class="space-y-1">
                                            <h2 class="text-lg font-semibold text-gray-800">{!! $question['highlighted_text'] !!}</h2>
                                            <p class="text-xs text-gray-500 font-mono break-all">UUID: {{ $question['uuid'] }}</p>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Варіанти відповіді') }}</h3>
                                            @if($filledAnswers->isEmpty())
                                                <p class="mt-2 text-sm text-gray-500">{{ __('Варіанти відповідей відсутні.') }}</p>
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
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
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
                                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Теги') }}</dt>
                                                            <dd>
                                                                @if($questionTags->isEmpty())
                                                                    {{ __('Без тегів') }}
                                                                @else
                                                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                                                        @foreach($questionTags as $tag)
                                                                            <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-medium">
                                                                                {{ $tag['name'] }}
                                                                                @if(filled($tag['category']))
                                                                                    <span class="text-indigo-500">({{ $tag['category'] }})</span>
                                                                                @endif
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </dd>
                                                        </div>
                                                        <div>
                                                            <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Теми') }}</dt>
                                                            <dd>{{ $questionTopics->isEmpty() ? __('Без тем') : $questionTopics->implode(', ') }}</dd>
                                                        </div>
                                                    </dl>
                                                </div>
                                            </div>

                                            @if($questionOptions->isNotEmpty())
                                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                        <span>{{ __('Додаткові опції') }}</span>
                                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($questionOptions as $option)
                                                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium">{{ $option }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($questionVerbHints->isNotEmpty())
                                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                        <span>{{ __('Підказки дієслів') }}</span>
                                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                                        <ul class="space-y-1 text-sm text-gray-600">
                                                            @foreach($questionVerbHints as $hint)
                                                                <li><span class="font-mono text-xs text-gray-500">{{ $hint['marker'] }}</span> — {{ $hint['label'] }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($questionVariants->isNotEmpty())
                                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-сlate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                        <span>{{ __('Альтернативні формулювання') }}</span>
                                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                                                            @foreach($questionVariants as $variant)
                                                                <li>{{ $variant }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($questionHints->isNotEmpty())
                                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-сlate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                        <span>{{ __('Підказки') }}</span>
                                                        <svg class="h-4 w-4 text-slate-500 transition-transform durée-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                                        @foreach($questionHints as $hint)
                                                            <div class="rounded bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-gray-600 space-y-1">
                                                                <div class="flex items-center justify-between text-xs text-slate-500">
                                                                    <span>{{ $hint['provider'] ?? __('Невідомий провайдер') }}</span>
                                                                    <span>{{ strtoupper($hint['locale'] ?? 'UA') }}</span>
                                                                </div>
                                                                <p class="whitespace-pre-line">{{ $hint['text'] }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($questionExplanations->isNotEmpty())
                                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-сlate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                                        <span>{{ __('Пояснення ChatGPT') }}</span>
                                                        <svg class="h-4 w-4 text-slate-500 transition-transform durée-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94л3.71-3.7a.75.75 0 0 1 1.08 1.04л-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06З" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                                        @foreach($questionExplanations as $explanation)
                                                            <div class="rounded bg-purple-50 border border-purple-200 px-3 py-2 text-sm text-purple-800 space-y-1">
                                                                <div class="text-xs text-purple-600 font-semibold">{{ __('Неправильна відповідь:') }} {{ $explanation['wrong_answer'] }}</div>
                                                                <p class="whitespace-pre-line">{{ $explanation['text'] }}</p>
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
                    {{ __('Нижче відображено HTML, який буде створений сидером. Перевірте вміст перед запуском.') }}
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
                        ></iframe>
                    </div>
                @else
                    <div class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-sm text-gray-500">
                        {{ __('Сидер не надав HTML для попереднього перегляду.') }}
                    </div>
                @endif
            </div>
        @elseif($previewType === 'category')
            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Попередній перегляд категорії') }}</h2>
                <p class="text-sm text-gray-500">
                    {{ __('Нижче відображено HTML опису категорії. Переконайтеся, що блоки та сторінки відображаються коректно.') }}
                </p>

                @if($categoryPreview && ! empty($categoryPreview['html']))
                    <div
                        class="border border-gray-200 rounded-lg overflow-hidden"
                        data-page-preview
                        data-page-preview-html="{{ base64_encode($categoryPreview['html']) }}"
                    >
                        <iframe
                            class="w-full"
                            style="min-height: 900px;"
                            data-page-preview-frame
                            loading="lazy"
                        ></iframe>
                    </div>
                @else
                    <div class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-sm text-gray-500">
                        {{ __('Сидер не надав HTML для попереднього перегляду категорії.') }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-500">{{ __('Цей тип попереднього перегляду ще не підтримується.') }}</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('click', function (event) {
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

        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('[data-source-toggle]');

            if (!toggle) {
                return;
            }

            const container = toggle.closest('[data-source-group]');

            if (!container) {
                return;
            }

            const content = container.querySelector('[data-source-content]');
            const icon = toggle.querySelector('[data-source-toggle-icon]');
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                toggle.setAttribute('aria-expanded', 'false');
                content?.classList.add('hidden');
                icon?.classList.remove('rotate-180');
            } else {
                toggle.setAttribute('aria-expanded', 'true');
                content?.classList.remove('hidden');
                icon?.classList.add('rotate-180');
            }
        });

        // Tags summary toggle
        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('[data-tags-summary-toggle]');

            if (!toggle) {
                return;
            }

            const section = toggle.closest('[data-tags-summary-section]');

            if (!section) {
                return;
            }

            const content = section.querySelector('[data-tags-summary-content]');
            const icon = toggle.querySelector('[data-tags-summary-icon]');
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                toggle.setAttribute('aria-expanded', 'false');
                content?.classList.add('hidden');
                icon?.classList.remove('rotate-180');
            } else {
                toggle.setAttribute('aria-expanded', 'true');
                content?.classList.remove('hidden');
                icon?.classList.add('rotate-180');
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

        // Tag filter functionality
        let activeTagFilter = null;

        document.addEventListener('click', function (event) {
            const tagButton = event.target.closest('[data-tag-filter]');
            
            if (!tagButton) {
                return;
            }

            const tagName = tagButton.getAttribute('data-tag-filter');
            
            // Toggle filter if clicking the same tag
            if (activeTagFilter === tagName) {
                activeTagFilter = null;
                resetTagFilter();
                return;
            }

            activeTagFilter = tagName;
            applyTagFilter(tagName);
        });

        function applyTagFilter(tagName) {
            const allQuestions = document.querySelectorAll('[data-question-preview]');
            const allTagButtons = document.querySelectorAll('[data-tag-filter]');
            let matchCount = 0;

            // Reset all tag buttons to normal state
            allTagButtons.forEach(btn => {
                btn.classList.remove('ring-2', 'ring-purple-500', 'ring-offset-2');
            });

            // Highlight the active tag button
            const activeButton = document.querySelector(`[data-tag-filter="${CSS.escape(tagName)}"]`);
            if (activeButton) {
                activeButton.classList.add('ring-2', 'ring-purple-500', 'ring-offset-2');
            }

            // Filter questions
            allQuestions.forEach(question => {
                const questionTags = question.getAttribute('data-question-tags') || '';
                const tagsArray = questionTags.split(',').map(t => t.trim()).filter(t => t);
                const hasTag = tagsArray.includes(tagName);

                if (hasTag) {
                    question.classList.remove('opacity-30', 'scale-95');
                    question.classList.add('ring-2', 'ring-purple-400', 'shadow-lg');
                    matchCount++;

                    // Auto-expand the source section if collapsed
                    const sourceContent = question.closest('[data-source-content]');
                    if (sourceContent && sourceContent.classList.contains('hidden')) {
                        const sourceGroup = sourceContent.closest('[data-source-group]');
                        const sourceToggle = sourceGroup?.querySelector('[data-source-toggle]');
                        if (sourceToggle) {
                            sourceToggle.setAttribute('aria-expanded', 'true');
                            sourceContent.classList.remove('hidden');
                            const icon = sourceToggle.querySelector('[data-source-toggle-icon]');
                            if (icon) {
                                icon.classList.add('rotate-180');
                            }
                        }
                    }
                } else {
                    question.classList.add('opacity-30', 'scale-95');
                    question.classList.remove('ring-2', 'ring-purple-400', 'shadow-lg');
                }
            });

            // Show notification
            showFilterNotification(tagName, matchCount);

            // Scroll to first matching question
            const firstMatch = document.querySelector('[data-question-preview].ring-2');
            if (firstMatch) {
                setTimeout(() => {
                    firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }

        function resetTagFilter() {
            const allQuestions = document.querySelectorAll('[data-question-preview]');
            const allTagButtons = document.querySelectorAll('[data-tag-filter]');

            // Reset all tag buttons
            allTagButtons.forEach(btn => {
                btn.classList.remove('ring-2', 'ring-purple-500', 'ring-offset-2');
            });

            // Reset all questions
            allQuestions.forEach(question => {
                question.classList.remove('opacity-30', 'scale-95', 'ring-2', 'ring-purple-400', 'shadow-lg');
            });

            // Remove notification
            const existingNotification = document.getElementById('tag-filter-notification');
            if (existingNotification) {
                existingNotification.remove();
            }
        }

        function showFilterNotification(tagName, count) {
            // Remove existing notification
            const existing = document.getElementById('tag-filter-notification');
            if (existing) {
                existing.remove();
            }

            // Create new notification
            const notification = document.createElement('div');
            notification.id = 'tag-filter-notification';
            notification.className = 'fixed top-4 right-4 z-50 bg-purple-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-fade-in';
            notification.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span class="font-medium">Фільтр: <strong>${escapeHtml(tagName)}</strong> (${count} ${getPluralForm(count)})</span>
                <button type="button" onclick="activeTagFilter = null; resetTagFilter();" class="ml-2 hover:bg-purple-700 rounded p-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;

            document.body.appendChild(notification);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function getPluralForm(count) {
            if (count === 1) return 'питання';
            if (count >= 2 && count <= 4) return 'питання';
            return 'питань';
        }
    </script>
@endsection
