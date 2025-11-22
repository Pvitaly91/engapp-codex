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
                            href="{{ route('test-tags.aggregations.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring"
                        >
                            <i class="fa-solid fa-layer-group mr-2"></i>Агрегація тегів
                        </a>
                        <form
                            action="{{ route('test-tags.destroy-empty') }}"
                            method="POST"
                            data-confirm="Видалити всі теги без питань?"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-700 focus:outline-none focus:ring"
                            >
                                Видалити пусті теги
                            </button>
                        </form>
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
                                                $usageCount = (int) $tag->questions_count + (int) $tag->pages_count + (int) $tag->page_categories_count;
                                                $isEmptyTag = $usageCount === 0;
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
                                                            Використань: {{ $usageCount }}
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
                                                <div class="flex flex-wrap gap-2 text-[11px] text-slate-600">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-medium">
                                                        <i class="fa-regular fa-circle-question text-slate-500"></i>
                                                        Питань: {{ $tag->questions_count }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">
                                                        <i class="fa-regular fa-file-lines"></i>
                                                        Сторінок: {{ $tag->pages_count }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-800">
                                                        <i class="fa-regular fa-folder-closed"></i>
                                                        Категорій: {{ $tag->page_categories_count }}
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
        const submitFormViaAjax = async (form) => {
            const url = form.action;
            const method = form.querySelector('input[name="_method"]')?.value || 'POST';
            const csrfToken = form.querySelector('input[name="_token"]')?.value;

            if (!url || !csrfToken) {
                showStatusMessage('Помилка: не вдалося відправити запит.', 'error');
                return;
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Виникла помилка під час видалення.');
                }

                // Show success message
                showStatusMessage(data.message, 'success');

                // Remove the deleted element from DOM
                removeDeletedElement(form);

                // Update counts if needed
                updateTagCounts();
            } catch (error) {
                console.error('Delete error:', error);
                showStatusMessage(error.message || 'Виникла помилка під час видалення.', 'error');
            }
        };

        const showStatusMessage = (message, type = 'success') => {
            // Remove any existing messages
            const existingMessages = document.querySelectorAll('.status-message-ajax');
            existingMessages.forEach(msg => msg.remove());

            // Create new message
            const messageDiv = document.createElement('div');
            messageDiv.className = `status-message-ajax rounded-lg border px-4 py-3 text-sm mb-4 ${
                type === 'success' 
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700' 
                    : 'border-red-200 bg-red-50 text-red-700'
            }`;
            messageDiv.textContent = message;

            // Insert at the top of the content area
            const contentArea = document.querySelector('.mx-auto.flex.max-w-5xl.flex-col.gap-8');
            if (contentArea) {
                const header = contentArea.querySelector('header');
                if (header) {
                    header.insertAdjacentElement('afterend', messageDiv);
                } else {
                    contentArea.insertBefore(messageDiv, contentArea.firstChild);
                }
            }

            // Auto-remove after 5 seconds
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        };

        const removeDeletedElement = (form) => {
            // Find the closest tag or category container
            const tagItem = form.closest('li');
            const categoryBlock = form.closest('.space-y-3.rounded-xl.border');

            if (tagItem) {
                // Removing a tag
                tagItem.remove();
            } else if (categoryBlock) {
                // Removing a category
                categoryBlock.remove();
            }
        };

        const updateTagCounts = () => {
            // Update total tags count
            const totalTagsElement = document.querySelector('p.text-sm.text-slate-400');
            if (totalTagsElement) {
                const tagItems = document.querySelectorAll('li [data-tag-load]');
                totalTagsElement.textContent = `Всього тегів: ${tagItems.length}`;
            }

            // Update category counts
            document.querySelectorAll('.space-y-3.rounded-xl.border').forEach(categoryBlock => {
                const tagsInCategory = categoryBlock.querySelectorAll('li [data-tag-load]').length;
                const countElement = categoryBlock.querySelector('.block.text-sm');
                if (countElement) {
                    const countText = tagsInCategory === 0 
                        ? 'Немає тегів'
                        : tagsInCategory === 1 
                        ? '1 тег'
                        : tagsInCategory >= 2 && tagsInCategory <= 4
                        ? `${tagsInCategory} теги`
                        : `${tagsInCategory} тегів`;
                    countElement.textContent = countText;
                }
            });
        };

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

            acceptButton.addEventListener('click', async () => {
                if (!pendingForm) {
                    closeModal();
                    return;
                }

                const formToSubmit = pendingForm;
                closeModal(false);
                
                // Submit via AJAX
                await submitFormViaAjax(formToSubmit);
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

        const updateToggleLabels = (button, expanded) => {
            const collapsedLabel = button.querySelector('[data-toggle-label-collapsed]');
            const expandedLabel = button.querySelector('[data-toggle-label-expanded]');

            if (collapsedLabel) {
                collapsedLabel.classList.toggle('hidden', expanded);
            }

            if (expandedLabel) {
                expandedLabel.classList.toggle('hidden', !expanded);
            }
        };

        const loadQuestionTags = async (tagsContainer) => {
            if (!tagsContainer || tagsContainer.dataset.loaded === 'loading' || tagsContainer.dataset.loaded === 'true') {
                return;
            }

            const url = tagsContainer.dataset.loadUrl;

            if (!url) {
                tagsContainer.innerHTML = '<p class="text-xs text-red-600">Посилання для завантаження тегів не вказане.</p>';
                tagsContainer.dataset.loaded = 'error';

                return;
            }

            tagsContainer.dataset.loaded = 'loading';
            tagsContainer.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const payload = await response.json().catch(() => null);

                if (!response.ok) {
                    const message = payload && typeof payload.message === 'string' && payload.message
                        ? payload.message
                        : 'Не вдалося завантажити теги.';
                    throw new Error(message);
                }

                const html = payload && typeof payload.html === 'string' ? payload.html : '';

                tagsContainer.innerHTML = html.trim()
                    ? html
                    : '<p class="text-xs text-gray-500">Теги не знайдені.</p>';
                tagsContainer.dataset.loaded = 'true';
            } catch (error) {
                const message = error && typeof error.message === 'string' && error.message
                    ? error.message
                    : 'Не вдалося завантажити теги.';

                tagsContainer.innerHTML = `<p class="text-xs text-red-600">${message}</p>`;
                tagsContainer.dataset.loaded = 'error';
            }
        };

        const attachQuestionToggleHandlers = (root) => {
            if (!root) {
                return;
            }

            const toggles = root.querySelectorAll('[data-question-toggle]');

            toggles.forEach((button) => {
                if (button.dataset.questionToggleBound === 'true') {
                    return;
                }

                button.dataset.questionToggleBound = 'true';

                button.addEventListener('click', async () => {
                    const container = button.closest('[data-question-container]');

                    if (!container) {
                        return;
                    }

                    const detailsContainer = container.querySelector('[data-question-details]');

                    if (!detailsContainer) {
                        return;
                    }

                    const answersContainer = detailsContainer.querySelector('[data-question-answers]');
                    const tagsContainer = detailsContainer.querySelector('[data-question-tags]');

                    if (!answersContainer) {
                        return;
                    }

                    const icon = button.querySelector('[data-question-toggle-icon]');
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';

                    if (isExpanded) {
                        button.setAttribute('aria-expanded', 'false');
                        updateToggleLabels(button, false);

                        if (icon) {
                            icon.classList.remove('rotate-180');
                        }

                        detailsContainer.classList.add('hidden');

                        return;
                    }

                    button.setAttribute('aria-expanded', 'true');
                    updateToggleLabels(button, true);

                    if (icon) {
                        icon.classList.add('rotate-180');
                    }

                    detailsContainer.classList.remove('hidden');

                    if (button.dataset.loaded === 'true') {
                        if (tagsContainer) {
                            loadQuestionTags(tagsContainer);
                        }

                        return;
                    }

                    const url = button.dataset.loadUrl;

                    if (!url) {
                        answersContainer.innerHTML = '<p class="text-xs text-red-600">Посилання для завантаження не вказане.</p>';
                        button.dataset.loaded = 'error';

                        return;
                    }

                    button.dataset.loaded = 'loading';
                    answersContainer.innerHTML = '<p class="text-xs text-gray-500">Завантаження…</p>';

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        const payload = await response.json().catch(() => null);

                        if (!response.ok) {
                            const message = payload && typeof payload.message === 'string' && payload.message
                                ? payload.message
                                : 'Не вдалося завантажити варіанти.';
                            throw new Error(message);
                        }

                        const html = payload && typeof payload.html === 'string' ? payload.html : '';

                        answersContainer.innerHTML = html.trim()
                            ? html
                            : '<p class="text-xs text-gray-500">Варіанти відповіді не знайдені.</p>';
                        button.dataset.loaded = 'true';

                        if (tagsContainer) {
                            loadQuestionTags(tagsContainer);
                        }
                    } catch (error) {
                        const message = error && typeof error.message === 'string' && error.message
                            ? error.message
                            : 'Не вдалося завантажити варіанти.';

                        answersContainer.innerHTML = `<p class="text-xs text-red-600">${message}</p>`;
                        button.dataset.loaded = 'error';
                    }
                });
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
                }

                if (activeButton) {
                    activeButton.classList.remove('text-blue-600', 'font-semibold');
                }

                activeButton = null;
                activeContainer = null;
            };

            tagButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const url = button.dataset.tagUrl;
                    const container = button.closest('li')?.querySelector('[data-tag-questions]');

                    if (!url || !container) {
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

                    if (activeButton && activeButton !== button) {
                        activeButton.classList.remove('text-blue-600', 'font-semibold');
                    }

                    if (activeContainer && activeContainer !== container) {
                        activeContainer.classList.add('hidden');
                    }

                    button.classList.add('text-blue-600', 'font-semibold');
                    activeButton = button;
                    activeContainer = container;

                    container.classList.remove('hidden');

                    if (button.dataset.loaded === 'true') {
                        return;
                    }

                    if (abortController) {
                        abortController.abort();
                    }

                    abortController = new AbortController();
                    container.innerHTML = '<p class="text-sm text-slate-500">Зачекайте, дані завантажуються…</p>';

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        signal: abortController.signal,
                    })
                        .then(async (response) => {
                            const payload = await response.json().catch(() => null);

                            if (!response.ok) {
                                const message = payload && typeof payload.message === 'string' && payload.message
                                    ? payload.message
                                    : 'Не вдалося завантажити питання.';
                                throw new Error(message);
                            }

                            const html = payload && typeof payload.html === 'string' ? payload.html : '';

                            container.innerHTML = html.trim()
                                ? html
                                : '<p class="text-sm text-slate-500">Для цього тегу ще не додано питань.</p>';

                            attachQuestionToggleHandlers(container);
                            button.dataset.loaded = 'true';
                        })
                        .catch((error) => {
                            if (error.name === 'AbortError') {
                                return;
                            }

                            container.innerHTML = `<p class="text-sm text-red-600">${error.message || 'Сталася помилка під час завантаження.'}</p>`;
                            button.dataset.loaded = 'error';
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
