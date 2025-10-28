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
                                            data-confirm="Видалити категорію та всі її теги?"
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
                                            <li class="space-y-3 rounded-lg border border-slate-100 px-3 py-2">
                                                <div class="flex flex-wrap items-center justify-between gap-3">
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
                                                            data-confirm="Видалити тег «{{ $tag->name }}»?"
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
                                                </div>
                                                <div
                                                    class="hidden rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600"
                                                    data-tag-questions
                                                ></div>
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

        </div>
    </div>

    <div
        id="test-tag-confirmation-modal"
        class="fixed inset-0 z-40 hidden items-center justify-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="test-tag-confirmation-title"
    >
        <div class="absolute inset-0 bg-slate-900/50" data-confirm-overlay></div>
        <div class="relative w-full max-w-sm space-y-5 rounded-xl bg-white px-6 py-5 shadow-xl">
            <div class="space-y-2">
                <h2 id="test-tag-confirmation-title" class="text-lg font-semibold text-slate-800">Підтвердження</h2>
                <p class="text-sm text-slate-600" data-confirm-message></p>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200"
                    data-confirm-cancel
                >
                    Скасувати
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-500"
                    data-confirm-accept
                >
                    Підтвердити
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const initTestTagDeletionConfirmation = () => {
            const modal = document.getElementById('test-tag-confirmation-modal');
            const messageTarget = modal ? modal.querySelector('[data-confirm-message]') : null;
            const acceptButton = modal ? modal.querySelector('[data-confirm-accept]') : null;
            const cancelButton = modal ? modal.querySelector('[data-confirm-cancel]') : null;
            const overlay = modal ? modal.querySelector('[data-confirm-overlay]') : null;
            const forms = document.querySelectorAll('form[data-confirm]');

            if (!modal || !messageTarget || !acceptButton || !cancelButton) {
                forms.forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (form.dataset.confirmed === 'true') {
                            form.dataset.confirmed = '';
                            return;
                        }

                        const message = form.dataset.confirm || '';
                        if (message && !window.confirm(message)) {
                            event.preventDefault();
                        }
                    });
                });

                return;
            }

            let pendingForm = null;

            const closeModal = (restoreFocus = true) => {
                modal.classList.add('hidden');
                modal.classList.remove('flex', 'items-center', 'justify-center');

                if (restoreFocus && pendingForm) {
                    const focusTarget = pendingForm.querySelector('button, [type="submit"], [tabindex]:not([tabindex="-1"])');
                    if (focusTarget && typeof focusTarget.focus === 'function') {
                        focusTarget.focus();
                    }
                }

                pendingForm = null;
            };

            const openModal = (form, message) => {
                messageTarget.textContent = message || 'Підтвердьте дію.';
                pendingForm = form;
                modal.classList.remove('hidden');
                modal.classList.add('flex', 'items-center', 'justify-center');
                acceptButton.focus();
            };

            const handleFormSubmit = (event) => {
                const form = event.target;

                if (form.dataset.confirmed === 'true') {
                    form.dataset.confirmed = '';
                    return;
                }

                event.preventDefault();
                const message = form.dataset.confirm || '';
                openModal(form, message);
            };

            forms.forEach((form) => {
                form.addEventListener('submit', handleFormSubmit);
            });

            acceptButton.addEventListener('click', () => {
                if (!pendingForm) {
                    closeModal();
                    return;
                }

                const formToSubmit = pendingForm;
                formToSubmit.dataset.confirmed = 'true';
                closeModal(false);
                formToSubmit.requestSubmit();
            });

            const cancelHandler = () => {
                closeModal();
            };

            cancelButton.addEventListener('click', cancelHandler);

            if (overlay) {
                overlay.addEventListener('click', cancelHandler);
            }

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    cancelHandler();
                }
            });
        };

        const initTestTagQuestionsPanel = () => {
            const tagButtons = document.querySelectorAll('[data-tag-load]');

            if (!tagButtons.length) {
                return;
            }

            let activeButton = null;
            let activeContainer = null;
            let abortController = null;

            const closeActiveContainer = () => {
                if (activeContainer) {
                    activeContainer.classList.add('hidden');
                    activeContainer.innerHTML = '';
                }

                if (activeButton) {
                    activeButton.classList.remove('text-blue-600', 'font-semibold');
                }

                activeButton = null;
                activeContainer = null;
            };

            const formatMeta = (question) => {
                const difficulty = (question.difficulty ?? '') !== '' ? `Складність: ${question.difficulty}` : null;
                const level = (question.level ?? '') !== '' ? `Рівень: ${question.level}` : null;
                const parts = [difficulty, level].filter(Boolean);

                return parts.length ? parts.join(' · ') : 'Додаткова інформація недоступна';
            };

            const renderQuestions = (container, questions) => {
                const normalisedQuestions = Array.isArray(questions)
                    ? questions
                    : (questions && typeof questions === 'object')
                        ? Object.values(questions)
                        : [];

                if (!normalisedQuestions.length) {
                    container.innerHTML = '<p class="text-sm text-slate-500">Для цього тегу ще не додано питань.</p>';
                    return;
                }

                const questionsHtml = normalisedQuestions
                    .map((question) => {
                        const answers = Array.isArray(question.answers) ? question.answers : [];
                        const answersHtml = answers.length
                            ? `
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Відповіді</p>
                                <ul class="space-y-1 text-sm text-slate-700">
                                    ${answers
                                        .map((answer) => `
                                            <li class="flex items-start gap-2">
                                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">${answer.marker ?? '•'}</span>
                                                <span class="flex-1">${answer.rendered_answer || answer.answer || ''}</span>
                                            </li>
                                        `)
                                        .join('')}
                                </ul>
                            `
                            : '';

                        return `
                            <li class="space-y-2 rounded-lg border border-slate-200 bg-white p-4">
                                <p class="font-medium text-slate-800">${question.rendered_question || question.question || ''}</p>
                                ${answersHtml}
                                <p class="text-xs text-slate-500">${formatMeta(question)}</p>
                            </li>
                        `;
                    })
                    .join('');

                container.innerHTML = `
                    <ol class="space-y-3 text-sm text-slate-700">
                        ${questionsHtml}
                    </ol>
                `;
            };

            const showError = (container, message) => {
                container.innerHTML = `<p class="text-sm text-red-600">${message}</p>`;
            };

            const showLoading = (container) => {
                container.innerHTML = '<p class="text-sm text-slate-500">Зачекайте, дані завантажуються…</p>';
            };

            tagButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const url = button.dataset.tagUrl;
                    const container = button.closest('li')?.querySelector('[data-tag-questions]');

                    if (!url) {
                        return;
                    }

                    if (!container) {
                        return;
                    }

                    if (activeButton === button && activeContainer && !activeContainer.classList.contains('hidden')) {
                        if (abortController) {
                            abortController.abort();
                            abortController = null;
                        }

                        closeActiveContainer();
                        return;
                    }

                    if (activeButton) {
                        activeButton.classList.remove('text-blue-600', 'font-semibold');
                    }

                    if (activeContainer && activeContainer !== container) {
                        activeContainer.classList.add('hidden');
                        activeContainer.innerHTML = '';
                    }

                    button.classList.add('text-blue-600', 'font-semibold');
                    activeButton = button;
                    activeContainer = container;

                    container.classList.remove('hidden');
                    container.innerHTML = '';

                    if (abortController) {
                        abortController.abort();
                    }

                    abortController = new AbortController();

                    showLoading(container);

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
                            if (!data || !data.tag) {
                                throw new Error('Невірна відповідь від сервера.');
                            }

                            renderQuestions(container, data.questions);
                        })
                        .catch((error) => {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            showError(container, error.message || 'Сталася помилка під час завантаження.');
                        })
                        .finally(() => {
                            abortController = null;
                        });
                });
            });
        };

        const initTestTagPage = () => {
            initTestTagDeletionConfirmation();
            initTestTagQuestionsPanel();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTestTagPage);
        } else {
            initTestTagPage();
        }
    </script>
@endpush
