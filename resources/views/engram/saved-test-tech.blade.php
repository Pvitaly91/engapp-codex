@extends('layouts.app')

@section('title', $test->name . ' — Технічна сторінка')

@section('content')
@php
    $changeCount = $pendingChanges->count();
@endphp

<div id="saved-test-tech-root"
     class="max-w-6xl mx-auto px-4 py-6 space-y-6"
     data-queue-url="{{ route('saved-test.tech.changes.store', $test->slug) }}">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div x-data="{ editing: false, name: @js($test->name), formName: @js($test->name) }" class="flex-1 space-y-3">
            <div x-show="!editing" x-ref="display" class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-stone-900" x-text="name"></h1>
                    <button type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-600 hover:bg-stone-100"
                            @click="
                                window.highlightEditable($refs.display);
                                formName = name;
                                editing = true;
                                $nextTick(() => window.highlightEditable($refs.form));
                            ">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m13.5 6.5-8 8-3 3 .5-3.5 8-8 2.5 2.5Zm0 0 2-2a1.586 1.586 0 0 1 2.243 0 1.586 1.586 0 0 1 0 2.243l-2 2M10 4h-6a2 2 0 0 0-2 2v10c0 1.105.895 2 2 2h10a2 2 0 0 0 2-2v-6" />
                        </svg>
                        <span>Редагувати назву</span>
                    </button>
                </div>
                <p class="text-sm text-stone-600">Технічна інформація про тест · Питань: <span data-question-count>{{ $questions->count() }}</span></p>
            </div>
            <form x-show="editing" x-cloak x-ref="form" method="POST" action="{{ route('saved-tests.update', $test) }}" class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3"
                  x-on:submit.prevent="
                      const form = $event.target;
                      window.SavedTestTech.submitForm(form, {
                          refresh: false,
                          onSuccess: () => {
                              name = formName;
                              editing = false;
                              window.highlightEditable($refs.display);
                          },
                      });
                  ">
                @csrf
                @method('put')
                <input type="hidden" name="from" value="{{ $returnUrl }}">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Назва тесту</label>
                    <input type="text" name="name" x-model="formName" required
                           class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-stone-500">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">Зберегти</button>
                    <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100" @click="editing = false">Скасувати</button>
                </div>
            </form>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('saved-test.show', $test->slug) }}"
               class="px-3 py-1.5 rounded-2xl border border-stone-200 bg-white shadow-sm text-sm font-semibold text-stone-700 hover:bg-stone-50">
                ← Основна сторінка
            </a>
            <a href="{{ route('saved-test.js', $test->slug) }}"
               class="px-3 py-1.5 rounded-2xl border border-stone-200 bg-white shadow-sm text-sm font-semibold text-stone-700 hover:bg-stone-50">
                JS режим
            </a>
        </div>
    </div>

    <div x-data="{ addingQuestion: false }" class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-stone-900">Додати нове питання</h2>
                <p class="text-sm text-stone-600">Створіть нове питання й воно з'явиться на початку списку.</p>
            </div>
            <button type="button"
                    class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50"
                    x-show="!addingQuestion"
                    @click="addingQuestion = true; $nextTick(() => window.highlightEditable($refs.form))">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z" clip-rule="evenodd" />
                </svg>
                <span>Нове питання</span>
            </button>
        </div>
        <form x-show="addingQuestion" x-cloak x-ref="form" method="POST" action="{{ route('saved-test.questions.store', $test->slug) }}" class="mt-4 space-y-3 rounded-2xl border border-emerald-200 bg-white p-4"
              data-queue-change="true"
              data-change-type="question.create"
              data-change-summary="Створити нове питання"
              data-route-name="saved-test.questions.store"
              data-route-params="@js(['slug' => $test->slug])"
              x-on:submit.prevent="
                  const form = $event.target;
                  window.SavedTestTech.submitForm(form, {
                      onSuccess: () => {
                          addingQuestion = false;
                          form.reset();
                          window.SavedTestTech.highlightFirstQuestion();
                      },
                  });
              ">
            @csrf
            <input type="hidden" name="from" value="{{ $returnUrl }}">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Текст питання</label>
                <textarea name="question" rows="3" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-stone-500">Рівень</label>
                    <select name="level" class="mt-1 w-40 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">N/A</option>
                        @foreach(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $levelOption)
                            <option value="{{ $levelOption }}">{{ $levelOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">Створити</button>
                    <button type="button" class="inline-flex items-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100" @click="addingQuestion = false">Скасувати</button>
                </div>
            </div>
        </form>
    </div>

    <div x-data="{ tab: 'questions' }" class="space-y-6">
        <div class="inline-flex overflow-hidden rounded-full border border-stone-200 bg-white p-1 text-sm font-semibold text-stone-600 shadow-sm">
            <button type="button"
                    class="rounded-full px-4 py-1.5 transition"
                    :class="tab === 'questions' ? 'bg-stone-900 text-white shadow' : 'hover:bg-stone-100'"
                    @click="tab = 'questions'">
                Питання
            </button>
            <button type="button"
                    class="flex items-center gap-2 rounded-full px-4 py-1.5 transition"
                    :class="tab === 'changes' ? 'bg-stone-900 text-white shadow' : 'hover:bg-stone-100'"
                    @click="tab = 'changes'">
                Черга змін
                <span data-change-count
                      class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-amber-500 px-2 text-xs font-bold text-white"
                      @class(['hidden' => $changeCount === 0])
                      x-text="window.SavedTestTech?.changeCount ?? {{ $changeCount }}"></span>
            </button>
        </div>

        <div x-show="tab === 'questions'" class="space-y-6">
            <div id="saved-test-question-list"
                 class="space-y-6"
                 data-refresh-url="{{ route('saved-test.tech.questions', $test->slug) }}"
                 data-test-slug="{{ $test->slug }}">
                @include('engram.partials.saved-test-tech-question-list', [
                    'questions' => $questions,
                    'test' => $test,
                    'explanationsByQuestionId' => $explanationsByQuestionId,
                    'returnUrl' => $returnUrl,
                ])
            </div>
        </div>

        <div x-show="tab === 'changes'" x-cloak class="space-y-6">
            <div id="saved-test-change-list"
                 class="space-y-4"
                 data-refresh-url="{{ route('saved-test.tech.changes.index', $test->slug) }}">
                @include('engram.partials.saved-test-tech-change-list', [
                    'test' => $test,
                    'changes' => $pendingChanges,
                    'returnUrl' => $returnUrl,
                ])
            </div>
        </div>
    </div>

</div>

<style>
    .editable-highlight {
        outline: 3px solid rgba(250, 204, 21, 0.85);
        outline-offset: 4px;
        transition: outline-color 0.2s ease, outline-width 0.2s ease;
    }
</style>
<script>
    window.SavedTestTech = {
        root: null,
        questionList: null,
        changeList: null,
        questionCountEl: null,
        changeCountEl: null,
        queueUrl: null,
        questionRefreshUrl: null,
        changeRefreshUrl: null,
        isRefreshingQuestions: false,
        isRefreshingChanges: false,
        highlightAfterRefresh: false,
        changeCount: 0,

        init() {
            this.root = document.getElementById('saved-test-tech-root');
            this.questionList = document.getElementById('saved-test-question-list');
            this.changeList = document.getElementById('saved-test-change-list');
            this.questionCountEl = document.querySelector('[data-question-count]');
            this.changeCountEl = document.querySelector('[data-change-count]');
            this.queueUrl = this.root ? this.root.dataset.queueUrl || null : null;
            this.questionRefreshUrl = this.questionList ? this.questionList.dataset.refreshUrl || null : null;
            this.changeRefreshUrl = this.changeList ? this.changeList.dataset.refreshUrl || null : null;

            if (this.changeCountEl) {
                const initial = Number(this.changeCountEl.textContent?.trim() || '0');
                this.changeCount = Number.isNaN(initial) ? 0 : initial;
                if (this.changeCount === 0) {
                    this.changeCountEl.classList.add('hidden');
                }
            }

            if (this.questionList) {
                this.questionList.addEventListener('submit', (event) => {
                    const form = event.target;
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    event.preventDefault();
                    this.submitForm(form);
                });
            }

            if (this.changeList) {
                this.changeList.addEventListener('submit', (event) => {
                    const form = event.target;
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    event.preventDefault();
                    this.submitForm(form);
                });
            }
        },

        async submitForm(form, options = {}) {
            if ('queueChange' in form.dataset) {
                await this.submitQueuedChange(form, options);

                return;
            }

            const confirmMessage = options.confirm ?? form.dataset.confirm;
            if (confirmMessage && ! window.confirm(confirmMessage)) {
                return;
            }

            const submitButtons = Array.from(form.querySelectorAll('[type="submit"]'));
            submitButtons.forEach((button) => {
                button.disabled = true;
            });

            const refreshQuestions = options.refresh !== false && form.dataset.refreshQuestions !== 'false';
            const refreshChanges = options.refreshChanges === true || form.dataset.refreshChanges === 'true';

            try {
                const methodAttr = (form.getAttribute('method') || 'POST').toUpperCase();
                const fetchMethod = methodAttr === 'GET' ? 'GET' : 'POST';
                let url = form.action;
                let body = null;

                if (fetchMethod === 'GET') {
                    const params = new URLSearchParams(new FormData(form));
                    url += (url.includes('?') ? '&' : '?') + params.toString();
                } else {
                    body = new FormData(form);
                }

                const response = await fetch(url, {
                    method: fetchMethod,
                    body,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const clone = response.clone();

                if (!response.ok) {
                    await this.handleError(clone, options);
                    return;
                }

                let payload = null;
                if (response.status !== 204) {
                    const text = await response.text();
                    if (text) {
                        try {
                            payload = JSON.parse(text);
                        } catch (error) {
                            payload = text;
                        }
                    }
                }

                if (typeof options.onSuccess === 'function') {
                    await options.onSuccess(payload);
                }

                if (refreshQuestions) {
                    await this.refreshQuestions();
                }

                if (refreshChanges) {
                    await this.refreshChanges();
                }
            } catch (error) {
                const message = error && error.message ? error.message : 'Сталася неочікувана помилка.';
                this.showError(message);
            } finally {
                submitButtons.forEach((button) => {
                    button.disabled = false;
                });
            }
        },

        resolveMethod(form, formData) {
            const baseMethod = (form.getAttribute('method') || 'POST').toUpperCase();
            if (baseMethod === 'GET') {
                return 'GET';
            }

            const override = formData.get('_method');
            if (override) {
                return String(override).toUpperCase();
            }

            return baseMethod;
        },

        normalizeFormData(formData) {
            const payload = {};

            formData.forEach((value, key) => {
                if (['_token', '_method', 'from'].includes(key)) {
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(payload, key)) {
                    const existing = payload[key];
                    if (Array.isArray(existing)) {
                        existing.push(value);
                    } else {
                        payload[key] = [existing, value];
                    }
                } else {
                    payload[key] = value;
                }
            });

            return payload;
        },

        async submitQueuedChange(form, options = {}) {
            const confirmMessage = options.confirm ?? form.dataset.confirm;
            if (confirmMessage && ! window.confirm(confirmMessage)) {
                return;
            }

            if (!this.queueUrl) {
                this.showError('Черга змін тимчасово недоступна.');

                return;
            }

            const submitButtons = Array.from(form.querySelectorAll('[type="submit"]'));
            submitButtons.forEach((button) => {
                button.disabled = true;
            });

            try {
                const formData = new FormData(form);
                const method = this.resolveMethod(form, formData);
                const payload = this.normalizeFormData(formData);
                const routeName = form.dataset.routeName;

                if (!routeName) {
                    throw new Error('Не вказано маршрут для цієї зміни.');
                }

                let routeParams = {};
                if (form.dataset.routeParams) {
                    try {
                        routeParams = JSON.parse(form.dataset.routeParams);
                    } catch (error) {
                        // ignore JSON parse errors
                    }
                }

                const rawQuestionId = form.dataset.questionId || formData.get('question_id');
                const hasQuestionId = rawQuestionId !== null && rawQuestionId !== undefined && String(rawQuestionId).trim() !== '';
                const numericQuestionId = hasQuestionId ? Number(rawQuestionId) : null;
                const normalizedQuestionId = Number.isFinite(numericQuestionId) ? numericQuestionId : null;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

                const response = await fetch(this.queueUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                    body: JSON.stringify({
                        route: routeName,
                        route_params: routeParams,
                        method,
                        payload,
                        change_type: form.dataset.changeType || null,
                        summary: form.dataset.changeSummary || null,
                        question_id: normalizedQuestionId,
                    }),
                });

                const clone = response.clone();

                if (!response.ok) {
                    await this.handleError(clone, options);
                    return;
                }

                let data = null;
                try {
                    data = await response.json();
                } catch {
                    data = null;
                }

                if (typeof options.onSuccess === 'function') {
                    await options.onSuccess(data);
                }

                this.handleQueuedFormSuccess(form, {
                    response: data,
                    questionId: normalizedQuestionId,
                    changeType: form.dataset.changeType || null,
                });

                if (data && typeof data.change_count === 'number') {
                    this.updateChangeCount(data.change_count);
                }

                await this.refreshChanges();

                if (this.changeList) {
                    window.highlightEditable(this.changeList);
                }
            } catch (error) {
                const message = error && error.message ? error.message : 'Не вдалося додати зміну до черги.';
                this.showError(message);
            } finally {
                submitButtons.forEach((button) => {
                    button.disabled = false;
                });
            }
        },

        handleQueuedFormSuccess(form, context = {}) {
            this.closeFormWithAlpine(form);

            const isObject = context && typeof context === 'object';
            const detail = {
                form,
                questionId: isObject && Number.isFinite(context.questionId)
                    ? context.questionId
                    : null,
                changeType: isObject && Object.prototype.hasOwnProperty.call(context, 'changeType')
                    ? context.changeType
                    : null,
                response: isObject && Object.prototype.hasOwnProperty.call(context, 'response')
                    ? context.response
                    : null,
            };

            form.dispatchEvent(new CustomEvent('saved-test-tech:change-queued', {
                bubbles: true,
                detail,
            }));

            window.dispatchEvent(new CustomEvent('saved-test-tech:change-queued', {
                detail: Object.assign({}, detail),
            }));
        },

        closeFormWithAlpine(form) {
            if (!form) {
                return false;
            }

            const expression = form.getAttribute('x-show');
            if (!expression) {
                return false;
            }

            const trimmed = expression.trim();
            if (!trimmed || /[^a-zA-Z0-9_.$]/.test(trimmed)) {
                return false;
            }

            let current = form;

            while (current) {
                const component = current.__x;
                if (component) {
                    const target = component.$data ?? component.getUnobservedData?.();

                    if (target) {
                        try {
                            const setter = new Function('value', `with (this) { ${trimmed} = value; }`);
                            setter.call(target, false);

                            return true;
                        } catch (error) {
                            // If the expression isn't valid in this scope, continue traversing upwards.
                        }
                    }
                }

                current = current.parentElement;
            }

            return false;
        },

        async handleError(response, options = {}) {
            let message = 'Не вдалося зберегти зміни.';

            try {
                const text = await response.text();

                if (text) {
                    try {
                        const data = JSON.parse(text);

                        if (data && typeof data === 'object') {
                            if (data.errors) {
                                const firstError = Object.values(data.errors).flat()[0];
                                if (firstError) {
                                    message = firstError;
                                }
                            } else if (data.message) {
                                message = data.message;
                            }
                        } else {
                            message = text;
                        }
                    } catch {
                        message = text;
                    }
                }
            } catch {
                // ignore parsing errors
            }

            if (typeof options.onError === 'function') {
                options.onError(message);
            }

            this.showError(message);
        },

        async refreshQuestions() {
            if (!this.questionList || !this.questionRefreshUrl || this.isRefreshingQuestions) {
                return;
            }

            this.isRefreshingQuestions = true;

            try {
                const response = await fetch(this.questionRefreshUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Не вдалося оновити список питань.');
                }

                const data = await response.json();
                this.questionList.innerHTML = data.html;

                if (window.Alpine) {
                    window.Alpine.initTree(this.questionList);
                }

                if (this.questionCountEl) {
                    this.questionCountEl.textContent = data.question_count;
                }

                if (this.highlightAfterRefresh) {
                    const firstArticle = this.questionList.querySelector('article');
                    if (firstArticle) {
                        window.highlightEditable(firstArticle);
                    }
                    this.highlightAfterRefresh = false;
                }
            } catch (error) {
                const message = error && error.message ? error.message : 'Не вдалося оновити список питань.';
                this.showError(message);
            } finally {
                this.isRefreshingQuestions = false;
            }
        },

        async refreshChanges() {
            if (!this.changeList || !this.changeRefreshUrl || this.isRefreshingChanges) {
                return;
            }

            this.isRefreshingChanges = true;

            try {
                const response = await fetch(this.changeRefreshUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Не вдалося оновити чергу змін.');
                }

                const data = await response.json();
                this.changeList.innerHTML = data.html;

                if (window.Alpine) {
                    window.Alpine.initTree(this.changeList);
                }

                if (typeof data.change_count === 'number') {
                    this.updateChangeCount(data.change_count);
                }
            } catch (error) {
                const message = error && error.message ? error.message : 'Не вдалося оновити чергу змін.';
                this.showError(message);
            } finally {
                this.isRefreshingChanges = false;
            }
        },

        updateChangeCount(count) {
            this.changeCount = count;

            if (this.changeCountEl) {
                this.changeCountEl.textContent = count;
                if (count > 0) {
                    this.changeCountEl.classList.remove('hidden');
                } else {
                    this.changeCountEl.classList.add('hidden');
                }
            }
        },

        highlightFirstQuestion() {
            this.highlightAfterRefresh = true;
        },

        showError(message) {
            window.alert(message);
        },
    };

    window.highlightEditable = function (element) {
        if (! element) {
            return;
        }

        const cleanup = () => {
            element.classList.remove('editable-highlight');
            delete element.dataset.highlightTimeoutId;
        };

        if (element.dataset.highlightTimeoutId) {
            window.clearTimeout(Number(element.dataset.highlightTimeoutId));
            cleanup();
        }

        element.classList.add('editable-highlight');

        if (typeof element.scrollIntoView === 'function') {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        const timeoutId = window.setTimeout(cleanup, 1800);
        element.dataset.highlightTimeoutId = String(timeoutId);
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.SavedTestTech.init();
    });
</script>

@endsection
