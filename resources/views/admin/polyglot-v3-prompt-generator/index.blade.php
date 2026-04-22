@extends('layouts.app')

@section('title', 'Polyglot V3 Prompt Generator')

@section('content')
    <div
        class="mx-auto max-w-6xl space-y-8 pb-10"
        x-data="polyglotV3PromptGenerator(@js([
            'form' => $form,
            'availableLevels' => $availableLevels,
            'selectedTheoryPage' => $selectedTheoryPage,
            'searchRoute' => $searchRoute,
        ]))"
        x-init="init()"
    >
        <header class="space-y-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold text-slate-900">Polyglot V3 Prompt Generator</h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        Admin-обгортка над існуючим <code>PolyglotV3PromptGeneratorService</code>. Вона бере реальну theory page,
                        lesson metadata і віддає copy-ready prompt без переписування prompt generation logic.
                    </p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <div class="font-medium">Маршрут</div>
                    <code class="font-mono">/admin/polyglot-v3-prompt-generator</code>
                </div>
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <div class="font-semibold">Форма містить помилки</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('polyglot-v3-prompt-generator.generate') }}" method="POST" class="space-y-8">
            @csrf

            <section class="grid gap-8 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
                <div class="space-y-8">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">1. Theory page</h2>
                            <p class="mt-1 text-sm text-slate-500">Знайдіть існуючу theory page і дозвольте серверу резолвити її у потрібні slug-и для Polyglot service.</p>
                        </div>

                        <div class="space-y-5">
                            <div class="relative" @click.outside="searchOpen = false">
                                <label for="theory-search" class="mb-2 block text-sm font-medium text-slate-700">Пошук theory page</label>
                                <input
                                    id="theory-search"
                                    type="text"
                                    x-model="theorySearch"
                                    @focus="openSearch()"
                                    @input="queueSearch()"
                                    autocomplete="off"
                                    placeholder="Наприклад: verb to be, present perfect, passive voice"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <input type="hidden" name="theory_page_id" :value="selectedTheoryPageId || ''">

                                <div
                                    x-show="searchOpen"
                                    x-cloak
                                    class="absolute z-20 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                                >
                                    <template x-if="searchBusy">
                                        <div class="px-4 py-3 text-sm text-slate-500">Пошук...</div>
                                    </template>
                                    <template x-if="!searchBusy && searchResults.length === 0">
                                        <div class="px-4 py-3 text-sm text-slate-500">Введіть щонайменше 2 символи або змініть запит.</div>
                                    </template>
                                    <div class="max-h-80 overflow-y-auto">
                                        <template x-for="item in searchResults" :key="item.id">
                                            <button
                                                type="button"
                                                @click="selectTheoryPage(item)"
                                                class="block w-full border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-slate-50"
                                            >
                                                <div class="font-medium text-slate-900" x-text="item.title"></div>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    <span class="font-mono" x-text="item.slug"></span>
                                                    <span x-show="item.category_path"> · <span x-text="item.category_path"></span></span>
                                                </div>
                                                <div class="mt-1 text-xs text-slate-500 break-all" x-text="item.url"></div>
                                                <div class="mt-1 text-xs text-slate-400" x-show="item.resolved_seeder_class" x-text="item.resolved_seeder_class"></div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div
                                x-show="selectedTheoryPage"
                                x-cloak
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="space-y-2">
                                        <div class="text-sm font-semibold text-slate-900" x-text="selectedTheoryPage?.title"></div>
                                        <div class="text-xs text-slate-500">
                                            <span class="font-mono" x-text="selectedTheoryPage?.slug"></span>
                                            <span x-show="selectedTheoryPage?.category_path"> · <span x-text="selectedTheoryPage?.category_path"></span></span>
                                        </div>
                                        <div class="text-xs text-slate-500 break-all" x-text="selectedTheoryPage?.url"></div>
                                        <div class="text-xs text-slate-400" x-show="selectedTheoryPage?.resolved_seeder_class" x-text="selectedTheoryPage?.resolved_seeder_class"></div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="clearTheoryPage()"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100"
                                    >
                                        Clear selection
                                    </button>
                                </div>
                                <template x-if="selectedTheoryPage?.excerpt">
                                    <p class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm leading-6 text-slate-600" x-text="selectedTheoryPage.excerpt"></p>
                                </template>
                            </div>
                            @error('theory_page_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="theory_category_slug_preview" class="mb-2 block text-sm font-medium text-slate-700">theory_category_slug</label>
                                    <input
                                        id="theory_category_slug_preview"
                                        type="text"
                                        readonly
                                        :value="resolvedTheoryCategorySlug()"
                                        class="w-full rounded-xl border-slate-300 bg-slate-50 font-mono text-sm shadow-sm"
                                    >
                                    <p class="mt-2 text-xs text-slate-500">Підставляється з selected category slug path і серверно передається у сервіс.</p>
                                </div>
                                <div>
                                    <label for="theory_page_slug_preview" class="mb-2 block text-sm font-medium text-slate-700">theory_page_slug</label>
                                    <input
                                        id="theory_page_slug_preview"
                                        type="text"
                                        readonly
                                        :value="resolvedTheoryPageSlug()"
                                        class="w-full rounded-xl border-slate-300 bg-slate-50 font-mono text-sm shadow-sm"
                                    >
                                    <p class="mt-2 text-xs text-slate-500">Підставляється з selected page slug. Окремий theory-linking contract не створюється.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">2. Lesson metadata</h2>
                            <p class="mt-1 text-sm text-slate-500">Заповніть рівно ті поля, які потрібні існуючому Polyglot prompt generator service.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="lesson_slug" class="mb-2 block text-sm font-medium text-slate-700">Lesson slug</label>
                                <input
                                    id="lesson_slug"
                                    type="text"
                                    name="lesson_slug"
                                    x-model="lessonSlug"
                                    placeholder="polyglot-verb-to-be-present-a1"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('lesson_slug')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="lesson_order" class="mb-2 block text-sm font-medium text-slate-700">Lesson order</label>
                                <input
                                    id="lesson_order"
                                    type="number"
                                    min="1"
                                    name="lesson_order"
                                    x-model.number="lessonOrder"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('lesson_order')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="lesson_title" class="mb-2 block text-sm font-medium text-slate-700">Lesson title</label>
                                <input
                                    id="lesson_title"
                                    type="text"
                                    name="lesson_title"
                                    x-model="lessonTitle"
                                    placeholder="Verb To Be Present"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('lesson_title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="topic" class="mb-2 block text-sm font-medium text-slate-700">Topic</label>
                                <input
                                    id="topic"
                                    type="text"
                                    name="topic"
                                    x-model="topic"
                                    placeholder="verb to be"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('topic')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="level" class="mb-2 block text-sm font-medium text-slate-700">Level</label>
                                <select
                                    id="level"
                                    name="level"
                                    x-model="level"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    @foreach ($availableLevels as $availableLevel)
                                        <option value="{{ $availableLevel }}">{{ $availableLevel }}</option>
                                    @endforeach
                                </select>
                                @error('level')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="course_slug" class="mb-2 block text-sm font-medium text-slate-700">Course slug</label>
                                <input
                                    id="course_slug"
                                    type="text"
                                    name="course_slug"
                                    x-model="courseSlug"
                                    placeholder="polyglot-english-a1"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('course_slug')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="previous_lesson_slug" class="mb-2 block text-sm font-medium text-slate-700">Previous lesson slug</label>
                                <input
                                    id="previous_lesson_slug"
                                    type="text"
                                    name="previous_lesson_slug"
                                    x-model="previousLessonSlug"
                                    placeholder="polyglot-previous-a1"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('previous_lesson_slug')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="next_lesson_slug" class="mb-2 block text-sm font-medium text-slate-700">Next lesson slug</label>
                                <input
                                    id="next_lesson_slug"
                                    type="text"
                                    name="next_lesson_slug"
                                    x-model="nextLessonSlug"
                                    placeholder="polyglot-next-a1"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('next_lesson_slug')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="items_count" class="mb-2 block text-sm font-medium text-slate-700">Items count</label>
                                <input
                                    id="items_count"
                                    type="number"
                                    min="1"
                                    name="items_count"
                                    x-model.number="itemsCount"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('items_count')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="seeder_class_base_name" class="mb-2 block text-sm font-medium text-slate-700">Seeder class base name</label>
                                <input
                                    id="seeder_class_base_name"
                                    type="text"
                                    name="seeder_class_base_name"
                                    x-model="seederClassBaseName"
                                    placeholder="PolyglotVerbToBePresentSeeder"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('seeder_class_base_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="prompt_id" class="mb-2 block text-sm font-medium text-slate-700">CODEX PROMPT ID: <span class="text-slate-400">(optional)</span></label>
                                <input
                                    id="prompt_id"
                                    type="text"
                                    name="prompt_id"
                                    x-model="promptId"
                                    placeholder="GLZ-PROMPT-XXXXXXXX"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('prompt_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Preview</h2>
                        <p class="mt-1 text-sm text-slate-500">Попередній перегляд параметрів, які підуть в існуючий Polyglot V3 generator.</p>

                        <div class="mt-5 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Theory binding</div>
                                <div class="mt-2 space-y-2 text-sm text-slate-700">
                                    <div><span class="font-medium text-slate-900">Category slug:</span> <span class="font-mono" x-text="resolvedTheoryCategorySlug() || 'Select a theory page'"></span></div>
                                    <div><span class="font-medium text-slate-900">Page slug:</span> <span class="font-mono" x-text="resolvedTheoryPageSlug() || 'Select a theory page'"></span></div>
                                    <div><span class="font-medium text-slate-900">Public URL:</span> <span class="break-all text-slate-600" x-text="selectedTheoryPage?.url || 'Will be resolved from selected page'"></span></div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lesson</div>
                                <div class="mt-2 space-y-2 text-sm text-slate-700">
                                    <div><span class="font-medium text-slate-900">Slug:</span> <span class="font-mono" x-text="lessonSlug || 'polyglot-lesson-slug'"></span></div>
                                    <div><span class="font-medium text-slate-900">Title:</span> <span x-text="resolvedLessonTitle() || 'Lesson title preview'"></span></div>
                                    <div><span class="font-medium text-slate-900">Topic:</span> <span x-text="resolvedTopic() || 'Topic preview'"></span></div>
                                    <div><span class="font-medium text-slate-900">Course:</span> <span class="font-mono" x-text="courseSlug || 'polyglot-english-a1'"></span></div>
                                    <div><span class="font-medium text-slate-900">Level / order / items:</span> <span x-text="`${level || 'A1'} / ${lessonOrder || 1} / ${itemsCount || 24}`"></span></div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Target package</div>
                                <div class="mt-2 space-y-2 text-sm text-slate-700">
                                    <div><span class="font-medium text-slate-900">Seeder class:</span> <span class="break-all font-mono" x-text="`Database\\\\Seeders\\\\V3\\\\Polyglot\\\\${resolvedSeederClassBaseName()}`"></span></div>
                                    <div><span class="font-medium text-slate-900">Loader path:</span> <span class="break-all font-mono" x-text="`database/seeders/V3/Polyglot/${resolvedSeederClassBaseName()}.php`"></span></div>
                                    <div><span class="font-medium text-slate-900">Definition path:</span> <span class="break-all font-mono" x-text="`${packageRelativePath()}/definition.json`"></span></div>
                                    <div><span class="font-medium text-slate-900">Localization path:</span> <span class="break-all font-mono" x-text="`${packageRelativePath()}/localizations/uk.json`"></span></div>
                                    <div><span class="font-medium text-slate-900">CODEX PROMPT ID:</span> <span class="font-mono" x-text="promptIdPreview()"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-900 p-6 text-slate-100 shadow-sm">
                        <h2 class="text-lg font-semibold">Що буде на виході</h2>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-300">
                            <li>Готовий prompt від існуючого <code>PolyglotV3PromptGeneratorService</code> без постобробки його тексту.</li>
                            <li>Окремі copy-ready блоки для <code>CODEX PROMPT ID:</code> і <code>Codex Summary</code> над та під головним prompt.</li>
                            <li>Легкий debug/context для theory binding, target paths і meta без перевантаження UI.</li>
                        </ul>
                    </div>
                </aside>
            </section>

            <div class="flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl border border-blue-500 bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                >
                    Generate Prompt
                </button>
                <a
                    href="{{ route('polyglot-v3-prompt-generator.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    Clear / Reset
                </a>
            </div>
        </form>

        @if (! empty($result))
            <section class="space-y-5">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    Polyglot prompt згенеровано для lesson
                    <span class="font-semibold">{{ $result['meta']['lesson_slug'] ?? '' }}</span>
                    і theory page
                    <span class="font-semibold">{{ $result['theory_context']['page_title'] ?? '' }}</span>.
                </div>

                @if (! empty($promptCard))
                    @include('admin.partials.codex-prompt-card', [
                        'prompt' => $promptCard,
                        'idPrefix' => 'polyglot',
                    ])
                @endif

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">Theory context</h2>
                            <p class="mt-1 text-sm text-slate-500">Легкий контекст для швидкої перевірки theory binding без читання всього prompt-а.</p>
                        </div>

                        <dl class="space-y-3 text-sm text-slate-700">
                            <div>
                                <dt class="font-medium text-slate-900">Theory page</dt>
                                <dd>{{ $result['theory_context']['page_title'] ?? '' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-900">Slug / category path</dt>
                                <dd class="font-mono">{{ $result['theory_context']['page_slug'] ?? '' }} · {{ $result['theory_context']['category_slug_path'] ?? '' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-900">Route</dt>
                                <dd class="break-all">{{ $result['theory_context']['route_url'] ?? ($result['theory_context']['route_path'] ?? '') }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-900">Seeder class</dt>
                                <dd class="break-all font-mono">{{ $result['theory_context']['page_seeder_class'] ?? '' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-900">Database page ID</dt>
                                <dd class="font-mono">{{ $result['theory_context']['database_page_id'] ?? 'n/a' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="space-y-4">
                        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" open>
                            <summary class="cursor-pointer text-sm font-semibold text-slate-900">Target paths</summary>
                            <pre class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-6 text-slate-700">{{ json_encode($result['target_paths'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>

                        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-900">Meta</summary>
                            <pre class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-6 text-slate-700">{{ json_encode($result['meta'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>

                        <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-900">Raw theory context</summary>
                            <pre class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-6 text-slate-700">{{ json_encode($result['theory_context'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function polyglotV3PromptGenerator(config) {
            return {
                availableLevels: Array.isArray(config.availableLevels) ? config.availableLevels : ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
                theorySearch: config.selectedTheoryPage?.title || '',
                selectedTheoryPage: config.selectedTheoryPage || null,
                selectedTheoryPageId: config.selectedTheoryPage?.id || config.form.theory_page_id || null,
                lessonSlug: config.form.lesson_slug || '',
                lessonOrder: Number(config.form.lesson_order ?? 1),
                lessonTitle: config.form.lesson_title || '',
                topic: config.form.topic || '',
                level: config.form.level || 'A1',
                courseSlug: config.form.course_slug || 'polyglot-english-a1',
                previousLessonSlug: config.form.previous_lesson_slug || '',
                nextLessonSlug: config.form.next_lesson_slug || '',
                itemsCount: Number(config.form.items_count ?? 24),
                seederClassBaseName: config.form.seeder_class_base_name || '',
                promptId: config.form.prompt_id || '',
                searchRoute: config.searchRoute,
                searchResults: [],
                searchBusy: false,
                searchOpen: false,
                searchTimeout: null,
                copyStates: {},

                init() {
                    if (!this.availableLevels.includes(this.level)) {
                        this.level = this.availableLevels[0] || 'A1';
                    }
                },

                openSearch() {
                    this.searchOpen = true;

                    if (this.theorySearch.trim().length >= 2) {
                        this.queueSearch();
                    }
                },

                queueSearch() {
                    clearTimeout(this.searchTimeout);

                    const query = this.theorySearch.trim();

                    if (query.length < 2) {
                        this.searchResults = [];
                        this.searchOpen = true;
                        return;
                    }

                    this.searchTimeout = setTimeout(() => this.runSearch(query), 250);
                },

                async runSearch(query) {
                    this.searchBusy = true;
                    this.searchOpen = true;

                    try {
                        const url = new URL(this.searchRoute, window.location.origin);
                        url.searchParams.set('q', query);
                        url.searchParams.set('site_domain', 'gramlyze.com');

                        const response = await fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        const payload = await response.json();
                        this.searchResults = Array.isArray(payload.results) ? payload.results : [];
                    } catch (error) {
                        this.searchResults = [];
                    } finally {
                        this.searchBusy = false;
                    }
                },

                selectTheoryPage(item) {
                    this.selectedTheoryPage = item;
                    this.selectedTheoryPageId = item.id;
                    this.theorySearch = item.title;
                    this.searchOpen = false;
                    this.searchResults = [];

                    if (!this.lessonTitle.trim()) {
                        this.lessonTitle = item.title || '';
                    }

                    if (!this.topic.trim()) {
                        this.topic = item.title || '';
                    }
                },

                clearTheoryPage() {
                    this.selectedTheoryPage = null;
                    this.selectedTheoryPageId = null;
                    this.theorySearch = '';
                    this.searchResults = [];
                },

                resolvedTheoryCategorySlug() {
                    return this.selectedTheoryPage?.category_slug_path || '';
                },

                resolvedTheoryPageSlug() {
                    return this.selectedTheoryPage?.slug || '';
                },

                slugify(text) {
                    return (text || '')
                        .toString()
                        .trim()
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                },

                headlineFromSlug(slug) {
                    const normalized = this.slugify(slug);

                    if (!normalized) {
                        return '';
                    }

                    return normalized
                        .split('-')
                        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                        .join(' ');
                },

                studlyFromSlug(slug) {
                    const normalized = this.slugify(slug);

                    if (!normalized) {
                        return '';
                    }

                    return normalized
                        .split('-')
                        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                        .join('');
                },

                resolvedLessonTitle() {
                    return this.lessonTitle.trim() || this.headlineFromSlug(this.lessonSlug) || this.selectedTheoryPage?.title || '';
                },

                resolvedTopic() {
                    return this.topic.trim() || this.resolvedLessonTitle();
                },

                resolvedSeederClassBaseName() {
                    const typed = (this.seederClassBaseName || '').trim().replace(/\.php$/i, '');

                    if (typed) {
                        return typed.endsWith('Seeder') ? typed : `${typed}Seeder`;
                    }

                    const fromLessonSlug = this.studlyFromSlug(this.lessonSlug);

                    return fromLessonSlug ? `${fromLessonSlug}Seeder` : 'PolyglotLessonSeeder';
                },

                packageRelativePath() {
                    return `database/seeders/V3/Polyglot/${this.resolvedSeederClassBaseName()}`;
                },

                promptIdPreview() {
                    return this.promptId.trim() || 'Auto-generated by service';
                },

                async copyById(id) {
                    const element = document.getElementById(id);

                    if (!element) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(element.value);
                    } catch (error) {
                        element.select();
                        document.execCommand('copy');
                    }

                    this.copyStates[id] = true;

                    setTimeout(() => {
                        this.copyStates[id] = false;
                    }, 1500);
                },
            };
        }
    </script>
@endpush
