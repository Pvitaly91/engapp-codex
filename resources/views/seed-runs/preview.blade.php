@extends('layouts.app')

@section('title', __('Попередній перегляд сидера'))

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

        <div id="seeder-file-section" class="bg-white shadow rounded-lg p-6 space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">{{ __('Файл сидера') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('Перегляньте та за потреби відредагуйте PHP-файл сидера безпосередньо зі сторінки попереднього перегляду.') }}</p>
                </div>
                <div class="text-xs text-gray-500 space-y-1 md:text-right">
                    <div class="font-semibold uppercase tracking-wide">{{ __('Шлях до файлу') }}</div>
                    <div class="font-mono break-all text-gray-600">{{ $filePath ?? '—' }}</div>
                    @if($fileLastModified)
                        <div>{{ __('Остання зміна: :date', ['date' => $fileLastModified->format('d.m.Y H:i:s')]) }}</div>
                    @endif
                </div>
            </div>

            @if($fileError)
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $fileError }}
                </div>
            @elseif(is_null($fileContents))
                <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    {{ __('Не вдалося отримати вміст файлу сидера.') }}
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('Попередній перегляд файлу') }}</h3>
                        <pre class="text-xs leading-relaxed font-mono bg-slate-900 text-slate-100 rounded-lg p-4 overflow-auto max-h-[32rem] border border-slate-800 shadow-inner">{{ $fileContents }}</pre>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ __('Редагування файлу') }}</h3>
                        @if(! $fileIsWritable)
                            <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 mb-3">
                                {{ __('Файл доступний лише для читання. Змініть дозволи файлу, щоб редагувати його зі сторінки.') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('seed-runs.update-file') }}" class="space-y-3" data-preloader>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="class_name" value="{{ $className }}">
                            <input type="hidden" name="original_hash" value="{{ $fileHash }}">
                            <textarea name="contents"
                                      rows="18"
                                      class="w-full font-mono text-xs leading-relaxed border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('contents') border-red-300 ring-red-200 @enderror"
                                      @if(! $fileIsWritable) readonly @endif>{{ old('contents', $fileContents) }}</textarea>
                            @error('contents')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-gray-500">
                                <span>{{ __('Внесені зміни буде збережено безпосередньо у файлі сидера.') }}</span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('seed-runs.preview', ['class_name' => $className]) }}#seeder-file-section" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition">
                                        <i class="fa-solid fa-rotate"></i>
                                        {{ __('Скинути зміни') }}
                                    </a>
                                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 text-white rounded-md shadow @if($fileIsWritable) bg-blue-600 hover:bg-blue-500 @else bg-blue-300 cursor-not-allowed @endif" @if(! $fileIsWritable) disabled @endif>
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        {{ __('Зберегти файл') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
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
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
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
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
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
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3" data-preview-section-content>
                                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                                            @foreach($question['variants'] as $variant)
                                                <li>{{ $variant }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            @if($question['hints']->isNotEmpty())
                                <div class="border border-slate-200 rounded-lg" data-preview-section>
                                    <button type="button" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition" data-preview-section-toggle aria-expanded="false">
                                        <span>{{ __('Підказки') }}</span>
                                        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" data-preview-section-icon>
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                        @foreach($question['hints'] as $hint)
                                            <div class="rounded bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-gray-600 space-y-1">
                                                <div class="flex items-center justify-between text-xs text-slate-500">
                                                    <span>{{ $hint['provider'] ?? __('Невідомий провайдер') }}</span>
                                                    <span>{{ strtoupper($hint['locale'] ?? '–') }}</span>
                                                </div>
                                                <p class="whitespace-pre-line">{{ $hint['text'] }}</p>
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
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.7a.75.75 0 0 1 1.08 1.04l-4.25 4.25a.75.75 0 0 1-1.08 0L5.25 8.27a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="hidden border-t border-slate-200 px-3 py-3 space-y-2" data-preview-section-content>
                                        @foreach($question['explanations'] as $explanation)
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
    </script>
@endsection
