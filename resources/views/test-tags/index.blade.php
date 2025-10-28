@extends('layouts.app')

@section('title', 'Керування тегами тестів')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-5xl flex-col gap-8">
            <header class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-semibold text-slate-800">Теги тестів</h1>
                        <p class="text-slate-500">Переглядайте категорії та теги й переходьте до редагування за потреби.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('test-tags.create') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                        >
                            Додати тег
                        </a>
                    </div>
                </div>
                <p class="text-sm text-slate-400">Всього тегів: {{ $totalTags }}</p>
            </header>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-800">Категорії та теги</h2>
                @if ($tagGroups->isEmpty())
                    <p class="text-sm text-slate-500">Теги ще не додано.</p>
                @else
                    <div class="space-y-6">
                        @foreach ($tagGroups as $group)
                            @php
                                $categoryIsEmpty = (bool) ($group['is_empty'] ?? false);
                            @endphp
                            <div
                                x-data="{ open: false }"
                                class="space-y-3 rounded-xl border p-5 shadow-sm {{ $categoryIsEmpty ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-white' }}"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <button
                                        type="button"
                                        class="flex flex-1 items-start gap-3 text-left"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                        aria-controls="tag-group-{{ $loop->index }}"
                                    >
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border {{ $categoryIsEmpty ? 'border-red-200 bg-red-100 text-red-500' : 'border-slate-200 bg-slate-50 text-slate-500' }}">
                                            <i class="fa-solid fa-chevron-down text-sm transform transition-transform" :class="{ '-rotate-90': !open }"></i>
                                        </span>
                                        <span class="space-y-1">
                                            <span class="block text-lg font-semibold {{ $categoryIsEmpty ? 'text-red-700' : 'text-slate-800' }}">{{ $group['label'] }}</span>
                                            <span class="block text-sm {{ $categoryIsEmpty ? 'text-red-600' : 'text-slate-500' }}">{{ trans_choice('{0}Немає тегів|{1}1 тег|[2,4]:count теги|[5,*]:count тегів', $group['tags']->count(), ['count' => $group['tags']->count()]) }}</span>
                                        </span>
                                    </button>
                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ route('test-tags.categories.edit', ['category' => $group['key']]) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-blue-200 hover:text-blue-600"
                                        >
                                            Редагувати
                                        </a>
                                        <form
                                            action="{{ route('test-tags.categories.destroy', ['category' => $group['key']]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Видалити категорію та всі її теги?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                            >
                                                Видалити
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div
                                    id="tag-group-{{ $loop->index }}"
                                    x-show="open"
                                    x-transition
                                    x-cloak
                                >
                                    <ul class="space-y-2">
                                        @forelse ($group['tags'] as $tag)
                                            @php
                                                $isEmptyTag = (int) $tag->questions_count === 0;
                                            @endphp
                                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-100 px-3 py-2">
                                                <button
                                                    type="button"
                                                    class="flex flex-1 items-center justify-between gap-3 text-left font-medium transition {{ $isEmptyTag ? 'text-red-600 hover:text-red-600' : 'text-slate-700 hover:text-blue-600' }}"
                                                    data-tag-load
                                                    data-tag-id="{{ $tag->id }}"
                                                    data-tag-name="{{ $tag->name }}"
                                                    data-tag-url="{{ route('test-tags.questions', $tag) }}"
                                                >
                                                    <span>{{ $tag->name }}</span>
                                                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isEmptyTag ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-600' }}">
                                                        {{ $tag->questions_count }}
                                                    </span>
                                                </button>
                                                <span class="flex items-center gap-2 text-xs">
                                                    <a
                                                        href="{{ route('test-tags.edit', $tag) }}"
                                                        class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium text-slate-600 hover:border-blue-200 hover:text-blue-600"
                                                    >
                                                        Редагувати
                                                    </a>
                                                    <form
                                                        action="{{ route('test-tags.destroy', $tag) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Видалити тег «{{ $tag->name }}»?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="rounded-lg border border-red-200 px-3 py-1.5 font-medium text-red-600 hover:bg-red-50"
                                                        >
                                                            Видалити
                                                        </button>
                                                    </form>
                                                </span>
                                            </li>
                                        @empty
                                            <li class="rounded-lg border border-dashed px-3 py-4 text-center text-sm {{ $categoryIsEmpty ? 'border-red-200 bg-red-100 text-red-600' : 'border-slate-200 bg-slate-50 text-slate-500' }}">
                                                У цій категорії ще немає тегів.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-800">Питання за тегом</h2>
                    <p class="text-sm text-slate-400" id="tag-questions-selected"></p>
                </div>
                <div
                    id="tag-questions-panel"
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm text-slate-500">Натисніть на тег, щоб переглянути питання, пов'язані з ним.</p>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const initTestTagQuestionsPanel = () => {
            const panel = document.getElementById('tag-questions-panel');
            const selectedLabel = document.getElementById('tag-questions-selected');
            const defaultPanelContent = panel.innerHTML;
            let activeButton = null;
            let abortController = null;

            const setPanelContent = (html) => {
                panel.innerHTML = html;
            };

            const renderQuestions = (tagName, questions) => {
                const questionCount = questions.length;
                selectedLabel.textContent = `Обраний тег: ${tagName} • Питань: ${questionCount}`;

                if (!questions.length) {
                    setPanelContent('<p class="text-sm text-slate-500">Для цього тегу ще не додано питань.</p>');
                    return;
                }

                const list = document.createElement('ol');
                list.className = 'space-y-4 list-decimal list-inside text-sm text-slate-700';

                questions.forEach((question) => {
                    const item = document.createElement('li');
                    const questionWrapper = document.createElement('div');
                    questionWrapper.className = 'space-y-2';

                    const questionText = document.createElement('p');
                    questionText.className = 'font-medium text-slate-800';
                    questionText.innerHTML = question.rendered_question || question.question;

                    const meta = document.createElement('p');
                    meta.className = 'text-xs text-slate-500';
                    const difficulty = (question.difficulty ?? '') !== '' ? `Складність: ${question.difficulty}` : null;
                    const level = (question.level ?? '') !== '' ? `Рівень: ${question.level}` : null;
                    const metaParts = [difficulty, level].filter(Boolean);
                    meta.textContent = metaParts.length ? metaParts.join(' · ') : 'Додаткова інформація недоступна';

                    questionWrapper.appendChild(questionText);
                    if (Array.isArray(question.answers) && question.answers.length) {
                        const answersTitle = document.createElement('p');
                        answersTitle.className = 'text-xs font-semibold uppercase tracking-wide text-slate-500';
                        answersTitle.textContent = 'Відповіді';

                        const answersList = document.createElement('ul');
                        answersList.className = 'space-y-1 text-sm text-slate-700';

                        question.answers.forEach((answer) => {
                            const answerItem = document.createElement('li');
                            answerItem.className = 'flex items-start gap-2';

                            const marker = document.createElement('span');
                            marker.className = 'mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600';
                            marker.textContent = answer.marker ?? '•';

                            const value = document.createElement('span');
                            value.className = 'flex-1';
                            value.textContent = answer.rendered_answer || answer.answer || '';

                            answerItem.appendChild(marker);
                            answerItem.appendChild(value);
                            answersList.appendChild(answerItem);
                        });

                        questionWrapper.appendChild(answersTitle);
                        questionWrapper.appendChild(answersList);
                    }

                    questionWrapper.appendChild(meta);
                    item.appendChild(questionWrapper);
                    list.appendChild(item);
                });

                panel.innerHTML = '';
                panel.appendChild(list);
            };

            const showError = (message) => {
                selectedLabel.textContent = '';
                setPanelContent(`<p class="text-sm text-red-600">${message}</p>`);
            };

            const showLoading = (tagName) => {
                selectedLabel.textContent = `Завантаження: ${tagName}...`;
                setPanelContent('<p class="text-sm text-slate-500">Зачекайте, дані завантажуються…</p>');
            };

            document.querySelectorAll('[data-tag-load]').forEach((button) => {
                button.addEventListener('click', () => {
                    const url = button.dataset.tagUrl;
                    const tagName = button.dataset.tagName;

                    if (!url) {
                        return;
                    }

                    if (activeButton) {
                        activeButton.classList.remove('text-blue-600', 'font-semibold');
                    }

                    button.classList.add('text-blue-600', 'font-semibold');
                    activeButton = button;

                    if (abortController) {
                        abortController.abort();
                    }

                    abortController = new AbortController();

                    showLoading(tagName);

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: abortController.signal,
                    })
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error('Не вдалося завантажити питання.');
                            }

                            return response.json();
                        })
                        .then((data) => {
                            if (!data || !data.tag || !Array.isArray(data.questions)) {
                                throw new Error('Невірна відповідь від сервера.');
                            }

                            renderQuestions(data.tag.name, data.questions);
                        })
                        .catch((error) => {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            showError(error.message || 'Сталася помилка під час завантаження.');
                        });
                });
            });

            // Reset helpers on initial load
            const resetPanel = () => {
                selectedLabel.textContent = '';
                panel.innerHTML = defaultPanelContent;
            };
            resetPanel();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTestTagQuestionsPanel);
        } else {
            initTestTagQuestionsPanel();
        }
    </script>
@endpush
