@extends('layouts.app')

@section('title', $test->name . ' — Технічна сторінка')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">{{ $test->name }}</h1>
            <p class="text-sm text-stone-600 mt-1">Технічна інформація про тест · Питань: {{ $questions->count() }}</p>
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

    @foreach($questions as $question)
        @php
            $initialPayload = $questionPayloads->get($question->id);
            $explanations = collect($explanationsByQuestionId[$question->id] ?? []);
        @endphp
        <article
            x-data="techQuestionEditor(@json($initialPayload), {
                slug: '{{ $test->slug }}',
                questionId: {{ $question->id }},
                dumpUrl: '{{ route('saved-test.tech.dump', ['slug' => $test->slug, 'question' => $question->id]) }}',
                saveUrl: '{{ route('saved-test.tech.draft', ['slug' => $test->slug, 'question' => $question->id]) }}',
                applyUrl: '{{ route('saved-test.tech.apply', ['slug' => $test->slug, 'question' => $question->id]) }}',
            })"
            x-init="init()"
            class="bg-white shadow rounded-2xl p-6 space-y-6 border border-stone-100"
        >
            <header class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-baseline gap-3 text-sm text-stone-500">
                        <span class="font-semibold uppercase tracking-wide">Питання {{ $loop->iteration }}</span>
                        <span>ID: {{ $question->id }}</span>
                        <span class="font-mono text-[11px] text-stone-400" x-text="'UUID: ' + (state.question.uuid || '—')"></span>
                    </div>
                    <p class="text-lg leading-relaxed text-stone-900" x-html="renderQuestionPreview()"></p>
                    <div class="text-xs text-stone-500">
                        Файл змін: <code class="font-mono text-stone-700" x-text="state.meta?.file_path || 'storage/question-dumps/{{ $question->uuid }}.json'"></code>
                    </div>
                </div>
                <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:gap-4 lg:flex-col lg:items-end">
                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wide text-stone-500">Level</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-stone-900 text-white text-sm font-semibold" x-text="state.question.level || 'N/A'"></span>
                    </div>
                    <template x-if="state.meta?.has_draft">
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                            Незастосовані зміни
                        </span>
                    </template>
                    <span class="text-[11px] text-stone-400" x-text="formattedUpdatedAt()"></span>
                </div>
            </header>

            <div class="space-y-3" x-show="message">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700" x-text="message"></div>
            </div>
            <div class="space-y-3" x-show="error">
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="error"></div>
            </div>

            <section class="space-y-6">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-stone-700" for="question-text-{{ $question->id }}">Текст питання</label>
                    <textarea
                        id="question-text-{{ $question->id }}"
                        class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                        rows="4"
                        x-model="state.question.question"
                        @input="queueSave()"
                    ></textarea>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Level</label>
                        <input
                            type="text"
                            maxlength="10"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                            x-model="state.question.level"
                            @input="queueSave()"
                            @blur="state.question.level = (state.question.level || '').trim().toUpperCase(); queueSave()"
                        >
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Difficulty</label>
                        <input
                            type="number"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                            x-model.number="state.question.difficulty"
                            @input="queueSave()"
                        >
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Flag</label>
                        <input
                            type="number"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                            x-model.number="state.question.flag"
                            @input="queueSave()"
                        >
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Category ID</label>
                        <input
                            type="number"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                            x-model.number="state.question.category_id"
                            @input="queueSave()"
                        >
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Source ID</label>
                        <input
                            type="number"
                            class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                            x-model.number="state.question.source_id"
                            @input="queueSave()"
                        >
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Варіанти запитання</h2>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100"
                            @click="addVariant()"
                        >
                            + Додати
                        </button>
                    </div>
                    <template x-if="!state.variants.length">
                        <p class="text-sm text-stone-500">Варіанти відсутні.</p>
                    </template>
                    <template x-for="(variant, index) in state.variants" :key="variant.__key">
                        <div class="space-y-2 rounded-lg border border-stone-200 bg-stone-50 p-3">
                            <div class="flex items-center justify-between text-xs font-semibold text-stone-500">
                                <span>Варіант <span x-text="index + 1"></span></span>
                                <button type="button" class="text-red-500 hover:text-red-600" @click="removeVariant(index)">Видалити</button>
                            </div>
                            <textarea
                                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                rows="3"
                                x-model="variant.text"
                                @input="queueSave()"
                                placeholder="Текст варіанту"
                            ></textarea>
                            <div class="text-xs text-stone-500">
                                Попередній перегляд: <span x-html="renderVariantPreview(variant.text)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Правильні відповіді</h2>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                            @click="addAnswer()"
                        >
                            + Додати відповідь
                        </button>
                    </div>
                    <template x-if="!state.answers.length">
                        <p class="text-sm text-stone-500">Додайте принаймні одну правильну відповідь.</p>
                    </template>
                    <template x-for="(answer, index) in state.answers" :key="answer.__key">
                        <div class="space-y-2 rounded-lg border border-emerald-100 bg-emerald-50/60 p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Маркер</label>
                                    <input
                                        type="text"
                                        maxlength="4"
                                        class="w-20 rounded-lg border border-emerald-200 bg-white px-2 py-1 text-sm font-semibold text-emerald-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                        x-model="answer.marker"
                                        @input="queueSave()"
                                        @blur="answer.marker = (answer.marker || '').toUpperCase(); queueSave()"
                                    >
                                </div>
                                <button type="button" class="text-xs font-semibold text-red-500 hover:text-red-600" @click="removeAnswer(index)">Видалити</button>
                            </div>
                            <input
                                type="text"
                                class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                x-model="answer.value"
                                @input="queueSave()"
                                placeholder="Значення відповіді"
                            >
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Варіанти відповіді</h2>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100"
                            @click="addOption()"
                        >
                            + Додати варіант
                        </button>
                    </div>
                    <template x-if="!state.options.length">
                        <p class="text-sm text-stone-500">Список варіантів порожній.</p>
                    </template>
                    <template x-for="(option, index) in state.options" :key="option.__key">
                        <div class="flex flex-wrap items-center gap-3 rounded-lg border border-stone-200 bg-white px-3 py-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold"
                                :class="isOptionCorrect(option.value) ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-stone-200 bg-stone-50 text-stone-600'"
                            >
                                <template x-if="isOptionCorrect(option.value)">
                                    <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.25a1 1 0 0 1-1.425-.01L3.29 9.967a1 1 0 1 1 1.42-1.407l3.162 3.19 6.49-6.538a1 1 0 0 1 1.342-.088Z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <span x-text="isOptionCorrect(option.value) ? 'Коректна' : 'Опція'"></span>
                            </span>
                            <input
                                type="text"
                                class="flex-1 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                x-model="option.value"
                                @input="queueSave()"
                                placeholder="Варіант відповіді"
                            >
                            <button type="button" class="text-xs font-semibold text-red-500 hover:text-red-600" @click="removeOption(index)">Видалити</button>
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Verb hints</h2>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100"
                            @click="addVerbHint()"
                        >
                            + Додати hint
                        </button>
                    </div>
                    <template x-if="!state.verb_hints.length">
                        <p class="text-sm text-stone-500">Verb hints відсутні.</p>
                    </template>
                    <template x-for="(hint, index) in state.verb_hints" :key="hint.__key">
                        <div class="grid gap-3 rounded-lg border border-blue-100 bg-blue-50/60 p-3 sm:grid-cols-3">
                            <div class="space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-blue-600">Маркер</label>
                                <input
                                    type="text"
                                    maxlength="4"
                                    class="w-full rounded-lg border border-blue-200 bg-white px-2 py-1 text-sm font-semibold text-blue-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    x-model="hint.marker"
                                    @input="queueSave()"
                                    @blur="hint.marker = (hint.marker || '').toUpperCase(); queueSave()"
                                >
                            </div>
                            <div class="sm:col-span-2 space-y-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-blue-600">Значення</label>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    x-model="hint.value"
                                    @input="queueSave()"
                                    placeholder="Підказка"
                                >
                            </div>
                            <div class="sm:col-span-3 flex justify-end">
                                <button type="button" class="text-xs font-semibold text-red-500 hover:text-red-600" @click="removeVerbHint(index)">Видалити</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Question hints</h2>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-100"
                            @click="addHint()"
                        >
                            + Додати підказку
                        </button>
                    </div>
                    <template x-if="!state.hints.length">
                        <p class="text-sm text-stone-500">Підказки відсутні.</p>
                    </template>
                    <template x-for="(hint, index) in state.hints" :key="hint.__key">
                        <div class="space-y-2 rounded-lg border border-indigo-100 bg-indigo-50/60 p-3">
                            <div class="grid gap-3 sm:grid-cols-[repeat(3,minmax(0,1fr))]">
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Провайдер</label>
                                    <input
                                        type="text"
                                        class="w-full rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                        x-model="hint.provider"
                                        @input="queueSave()"
                                    >
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Мова</label>
                                    <input
                                        type="text"
                                        maxlength="5"
                                        class="w-full rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                        x-model="hint.locale"
                                        @input="queueSave()"
                                        @blur="hint.locale = (hint.locale || '').toLowerCase(); queueSave()"
                                    >
                                </div>
                                <div class="flex items-end justify-end">
                                    <button type="button" class="text-xs font-semibold text-red-500 hover:text-red-600" @click="removeHint(index)">Видалити</button>
                                </div>
                            </div>
                            <textarea
                                class="w-full rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                rows="3"
                                x-model="hint.hint"
                                @input="queueSave()"
                                placeholder="Текст підказки"
                            ></textarea>
                        </div>
                    </template>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-stone-300 px-3 py-1.5 text-sm font-semibold text-stone-700 hover:bg-stone-100"
                        @click="saveDraft()"
                    >
                        Зберегти зараз
                    </button>
                    <span class="text-xs text-stone-400" x-show="saving">Збереження…</span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-600 hover:bg-blue-100"
                        @click="toggleDump()"
                    >
                        <span x-text="showDump ? 'Сховати дамп' : 'Показати дамп'"></span>
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 disabled:opacity-50"
                        :class="applyLoading || !state.meta?.has_draft ? 'cursor-not-allowed' : 'cursor-pointer'"
                        :disabled="applyLoading || !state.meta?.has_draft"
                        @click="applyChanges()"
                    >
                        <span x-show="!applyLoading">Застосувати зміни</span>
                        <span x-show="applyLoading">Застосування…</span>
                    </button>
                </div>
            </div>

            <template x-if="showDump">
                <div class="space-y-3 rounded-lg border border-stone-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-sm font-semibold text-stone-700">Перегляд дампу</div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-full border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100"
                                @click="refreshDump()"
                            >
                                Оновити
                            </button>
                            <button
                                type="button"
                                class="rounded-full border border-stone-200 px-3 py-1 text-xs font-semibold text-stone-600 hover:bg-stone-100 disabled:opacity-50"
                                :disabled="!dumpContent"
                                @click="copyDump()"
                            >
                                Скопіювати
                            </button>
                        </div>
                    </div>
                    <pre class="max-h-72 overflow-auto rounded-lg border border-stone-200 bg-stone-50 p-3 text-xs text-stone-700" x-text="dumpContent || (dumpLoading ? 'Завантаження…' : 'Чернетка відсутня. Зміни зʼявляться після редагування.')"></pre>
                </div>
            </template>

            @if($explanations->isNotEmpty())
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <span>ChatGPT explanations</span>
                        <span class="text-[10px] font-normal text-stone-400 group-open:hidden">Показати ▼</span>
                        <span class="hidden text-[10px] font-normal text-stone-400 group-open:inline">Сховати ▲</span>
                    </summary>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-stone-800">
                            <thead class="text-xs uppercase tracking-wide text-stone-500">
                                <tr>
                                    <th class="py-2 pr-4">Мова</th>
                                    <th class="py-2 pr-4">Неправильна відповідь</th>
                                    <th class="py-2 pr-4">Правильна відповідь</th>
                                    <th class="py-2">Пояснення</th>
                                </tr>
                            </thead>
                            <tbody class="align-top">
                                @foreach($explanations as $explanation)
                                    <tr class="border-t border-stone-200">
                                        <td class="py-2 pr-4 font-semibold text-stone-600">{{ strtoupper($explanation->language) }}</td>
                                        <td class="py-2 pr-4">{{ $explanation->wrong_answer ?: '—' }}</td>
                                        <td class="py-2 pr-4 font-semibold"
                                            :class="isCorrectAnswer(@js($explanation->correct_answer)) ? 'text-emerald-700' : 'text-stone-800'"
                                        >
                                            {{ $explanation->correct_answer }}
                                        </td>
                                        <td class="py-2">{{ $explanation->explanation }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </article>
    @endforeach
</div>

<script>
    function techQuestionEditor(initialState, config) {
        const originalState = JSON.parse(JSON.stringify(initialState || {}));

        return {
            config,
            state: {},
            message: '',
            error: '',
            saving: false,
            saveTimeout: null,
            savePromise: null,
            pendingSave: false,
            applyLoading: false,
            dumpContent: '',
            dumpLoading: false,
            showDump: false,
            keyCounter: 0,
            init() {
                this.state = this.decorateState(originalState);
            },
            decorateState(payload) {
                const clone = JSON.parse(JSON.stringify(payload || {}));
                clone.question = clone.question || {};
                clone.meta = clone.meta || {};
                clone.options = this.decorateItems(clone.options || [], 'option');
                clone.answers = this.decorateItems(clone.answers || [], 'answer');
                clone.verb_hints = this.decorateItems(clone.verb_hints || [], 'verbHint');
                clone.variants = this.decorateItems(clone.variants || [], 'variant');
                clone.hints = this.decorateItems(clone.hints || [], 'hint');
                return clone;
            },
            decorateItems(items, prefix) {
                return items.map((item) => ({
                    ...item,
                    __key: item.id ? `${prefix}-${item.id}` : `${prefix}-${this.makeKey()}`,
                }));
            },
            makeKey() {
                this.keyCounter += 1;
                return `${Date.now()}-${this.keyCounter}`;
            },
            queueSave() {
                if (this.saveTimeout) {
                    clearTimeout(this.saveTimeout);
                }

                this.saveTimeout = setTimeout(() => {
                    this.saveTimeout = null;
                    this.saveDraft();
                }, 500);
            },
            cleanStateForRequest() {
                const payload = JSON.parse(JSON.stringify(this.state));
                delete payload.meta;
                const collections = ['options', 'answers', 'verb_hints', 'variants', 'hints'];

                collections.forEach((key) => {
                    if (!Array.isArray(payload[key])) {
                        payload[key] = [];
                    }

                    payload[key].forEach((item) => {
                        delete item.__key;
                    });
                });

                return payload;
            },
            jsonHeaders() {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                return {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                };
            },
            saveDraft() {
                if (this.saving) {
                    this.pendingSave = true;
                    return this.savePromise;
                }

                this.saving = true;
                this.pendingSave = false;
                this.message = '';
                this.error = '';

                const payload = this.cleanStateForRequest();

                this.savePromise = fetch(this.config.saveUrl, {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(payload),
                })
                    .then(async (response) => {
                        if (!response.ok) {
                            const data = await response.json().catch(() => ({}));
                            const message = data.message || 'Не вдалося зберегти чернетку.';
                            this.error = message;
                            throw new Error(message);
                        }

                        return response.json();
                    })
                    .then((data) => {
                        if (data?.payload) {
                            this.state = this.decorateState(data.payload);
                            this.dumpContent = '';
                            this.message = 'Чернетку збережено.';
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                    })
                    .finally(() => {
                        this.saving = false;
                        if (this.pendingSave) {
                            this.pendingSave = false;
                            this.saveDraft();
                        }
                    });

                return this.savePromise;
            },
            async applyChanges() {
                this.error = '';
                this.message = '';

                if (this.saveTimeout) {
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = null;
                    await this.saveDraft();
                } else if (this.saving && this.savePromise) {
                    await this.savePromise;
                }

                this.applyLoading = true;

                try {
                    const response = await fetch(this.config.applyUrl, {
                        method: 'POST',
                        headers: this.jsonHeaders(),
                    });

                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        if (response.status === 422 && data?.errors) {
                            this.error = Object.values(data.errors).flat().join(' ');
                        } else if (data?.message) {
                            this.error = data.message;
                        } else {
                            this.error = 'Не вдалося застосувати зміни.';
                        }
                        return;
                    }

                    const data = await response.json();
                    if (data?.payload) {
                        this.state = this.decorateState(data.payload);
                        this.dumpContent = '';
                        this.message = 'Зміни застосовано до бази даних.';
                    }
                } finally {
                    this.applyLoading = false;
                }
            },
            async loadDump() {
                this.dumpLoading = true;
                try {
                    const response = await fetch(this.config.dumpUrl, {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        this.dumpContent = 'Не вдалося завантажити дамп.';
                        return;
                    }

                    const data = await response.json();
                    this.dumpContent = JSON.stringify(data?.payload ?? {}, null, 2);
                } finally {
                    this.dumpLoading = false;
                }
            },
            toggleDump() {
                this.showDump = !this.showDump;
                if (this.showDump) {
                    this.loadDump();
                }
            },
            async refreshDump() {
                await this.loadDump();
            },
            async copyDump() {
                if (!this.dumpContent) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(this.dumpContent);
                    this.message = 'Дамп скопійовано у буфер обміну.';
                } catch (error) {
                    this.error = 'Не вдалося скопіювати дамп.';
                }
            },
            addVariant() {
                this.state.variants.push({ id: null, text: '', __key: `variant-${this.makeKey()}` });
                this.queueSave();
            },
            removeVariant(index) {
                this.state.variants.splice(index, 1);
                this.queueSave();
            },
            addAnswer() {
                this.state.answers.push({ id: null, marker: '', value: '', option_id: null, __key: `answer-${this.makeKey()}` });
                this.queueSave();
            },
            removeAnswer(index) {
                this.state.answers.splice(index, 1);
                this.queueSave();
            },
            addOption() {
                this.state.options.push({ id: null, value: '', __key: `option-${this.makeKey()}` });
                this.queueSave();
            },
            removeOption(index) {
                this.state.options.splice(index, 1);
                this.queueSave();
            },
            addVerbHint() {
                this.state.verb_hints.push({ id: null, marker: '', value: '', option_id: null, __key: `verbHint-${this.makeKey()}` });
                this.queueSave();
            },
            removeVerbHint(index) {
                this.state.verb_hints.splice(index, 1);
                this.queueSave();
            },
            addHint() {
                this.state.hints.push({ id: null, provider: '', locale: '', hint: '', __key: `hint-${this.makeKey()}` });
                this.queueSave();
            },
            removeHint(index) {
                this.state.hints.splice(index, 1);
                this.queueSave();
            },
            answersMap() {
                const map = {};
                (this.state.answers || []).forEach((answer) => {
                    const marker = (answer.marker || '').toLowerCase();
                    if (!marker) {
                        return;
                    }

                    map[marker] = answer.value || '';
                });

                return map;
            },
            highlightText(text) {
                const answers = this.answersMap();
                return (text || '').replace(/\{(a\d+)\}/gi, (match, marker) => {
                    const value = answers[marker.toLowerCase()];
                    if (!value) {
                        return this.escapeHtml(match);
                    }

                    return `<mark class="rounded bg-emerald-100 px-1 py-0.5 font-semibold text-emerald-800">${this.escapeHtml(value)}</mark>`;
                });
            },
            renderQuestionPreview() {
                return this.highlightText(this.state.question.question || '');
            },
            renderVariantPreview(text) {
                return this.highlightText(text || '');
            },
            escapeHtml(value) {
                return (value ?? '').toString()
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            },
            isCorrectAnswer(value) {
                const answers = Object.values(this.answersMap());
                return answers.some((answer) => (answer || '').trim() === (value || '').trim());
            },
            isOptionCorrect(value) {
                return this.isCorrectAnswer(value);
            },
            formattedUpdatedAt() {
                const timestamp = this.state.meta?.updated_at;
                if (!timestamp) {
                    return '';
                }

                const date = new Date(timestamp);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }

                return 'Оновлено ' + date.toLocaleString();
            },
        };
    }
</script>
@endsection
