@extends('layouts.app')

@section('title', __('Попередній перегляд сидера'))

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">{{ __('Попередній перегляд сидера') }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ __('Переконайтеся, що питання та повʼязані дані виглядають коректно, перш ніж запускати сидер.') }}
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
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Кількість питань у попередньому перегляді') }}</dt>
                    <dd>{{ $questions->count() }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600 uppercase tracking-wide text-xs">{{ __('Існуючих питань для цього сидера') }}</dt>
                    <dd>{{ $existingQuestionCount === null ? '—' : $existingQuestionCount }}</dd>
                </div>
            </dl>

            @if(!is_null($existingQuestionCount) && $existingQuestionCount > 0)
                <div class="mt-4 rounded-md bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                    {{ __('Деякі питання вже існують у базі даних для цього сидера. Попередній перегляд показує лише нові записи, які будуть створені.') }}
                </div>
            @endif
        </div>

        @if($questions->isEmpty())
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-500">
                    {{ __('Немає питань для попереднього перегляду. Сидер, можливо, вже виконувався або не повертає даних.') }}
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($questions as $question)
                    <div class="bg-white shadow rounded-lg p-6 space-y-4" data-question-preview>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">{!! $question['highlighted_text'] !!}</h2>
                            <p class="text-xs text-gray-500 mt-1 font-mono break-all">UUID: {{ $question['uuid'] }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="space-y-2">
                                <div>
                                    <span class="font-semibold text-gray-600">{{ __('Категорія:') }}</span>
                                    <span>{{ $question['category'] ?? __('Без категорії') }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-600">{{ __('Джерело:') }}</span>
                                    <span>{{ $question['source'] ?? __('Без джерела') }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-600">{{ __('Рівень:') }}</span>
                                    <span>{{ $question['level'] ?? __('Невідомо') }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-600">{{ __('Складність:') }}</span>
                                    <span>{{ $question['difficulty'] }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <span class="font-semibold text-gray-600">{{ __('Прапор:') }}</span>
                                    <span>{{ $question['flag'] }}</span>
                                </div>
                                @if($question['tags']->isNotEmpty())
                                    <div class="space-y-1">
                                        <span class="font-semibold text-gray-600">{{ __('Теги:') }}</span>
                                        <div class="flex flex-wrap gap-2">
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
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Відповіді') }}</h3>
                                <ul class="mt-2 space-y-1">
                                    @foreach($question['answers'] as $answer)
                                        <li class="flex items-center gap-2">
                                            <span class="font-mono text-xs text-gray-500">{{ $answer['marker'] }}</span>
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-xs font-medium">{{ $answer['label'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @if($question['options']->isNotEmpty())
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Варіанти відповідей') }}</h3>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($question['options'] as $option)
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium">{{ $option }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($question['verb_hints']->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Підказки дієслів') }}</h3>
                                <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                    @foreach($question['verb_hints'] as $hint)
                                        <li><span class="font-mono text-xs text-gray-500">{{ $hint['marker'] }}</span> — {{ $hint['label'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($question['variants']->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Варіанти формулювань') }}</h3>
                                <ul class="mt-2 list-disc list-inside text-sm text-gray-600 space-y-1">
                                    @foreach($question['variants'] as $variant)
                                        <li>{{ $variant }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($question['hints']->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Підказки') }}</h3>
                                <ul class="mt-2 space-y-2 text-sm text-gray-600">
                                    @foreach($question['hints'] as $hint)
                                        <li class="rounded bg-slate-50 border border-slate-200 px-3 py-2">
                                            <div class="flex items-center justify-between text-xs text-slate-500">
                                                <span>{{ $hint['provider'] ?? __('Невідомий провайдер') }}</span>
                                                <span>{{ strtoupper($hint['locale'] ?? '–') }}</span>
                                            </div>
                                            <p class="mt-1 whitespace-pre-line">{{ $hint['text'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($question['explanations']->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ __('Пояснення ChatGPT') }}</h3>
                                <ul class="mt-2 space-y-2 text-sm text-gray-600">
                                    @foreach($question['explanations'] as $explanation)
                                        <li class="rounded bg-purple-50 border border-purple-200 px-3 py-2">
                                            <div class="text-xs text-purple-600 font-semibold">{{ __('Неправильна відповідь:') }} {{ $explanation['wrong_answer'] }}</div>
                                            <p class="mt-1 whitespace-pre-line text-purple-800">{{ $explanation['text'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
