@extends('layouts.app')

@section('title', 'Page_V3 Prompt Generator')

@section('content')
    <div
        class="mx-auto max-w-6xl space-y-8 pb-10"
        x-data="pageV3PromptGenerator(@js([
            'form' => $form,
            'selectedCategory' => $selectedCategory,
            'categoryOptions' => $categoryOptions,
        ]))"
        x-init="init()"
    >
        <header class="space-y-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold text-slate-900">Page_V3 Prompt Generator</h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        Інструмент готує copy-ready prompt-и для генерації `Page_V3` theory seeders, category seeders
                        і пов’язаних JSON definition/localization файлів під поточний Laravel-проєкт.
                    </p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <div class="font-medium">Маршрут</div>
                    <code class="font-mono">/admin/page-v3-prompt-generator</code>
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

        @if (! empty($result['warnings']))
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                <div class="font-semibold">Є попередження</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($result['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('page-v3-prompt-generator.generate') }}" method="POST" class="space-y-8">
            @csrf

            <section class="grid gap-8 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
                <div class="space-y-8">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">1. Джерело теми</h2>
                            <p class="mt-1 text-sm text-slate-500">Тему можна задати вручну або через зовнішній URL зі сторінкою теорії.</p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="rounded-xl border px-4 py-3 transition" :class="sourceType === 'manual_topic' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="source_type" value="manual_topic" class="sr-only" x-model="sourceType">
                                <div class="font-medium text-slate-900">Manual topic</div>
                                <div class="mt-1 text-sm text-slate-500">Введіть назву теми для майбутньої theory page.</div>
                            </label>
                            <label class="rounded-xl border px-4 py-3 transition" :class="sourceType === 'external_url' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="source_type" value="external_url" class="sr-only" x-model="sourceType">
                                <div class="font-medium text-slate-900">External theory URL</div>
                                <div class="mt-1 text-sm text-slate-500">Safe fetch для зовнішньої статті або grammar page.</div>
                            </label>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div x-show="sourceType === 'manual_topic'" x-cloak>
                                <label for="manual_topic" class="mb-2 block text-sm font-medium text-slate-700">Topic title</label>
                                <input
                                    id="manual_topic"
                                    type="text"
                                    name="manual_topic"
                                    x-model="manualTopic"
                                    placeholder="Наприклад: Passive Voice in all tenses"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('manual_topic')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="sourceType === 'external_url'" x-cloak>
                                <label for="external_url" class="mb-2 block text-sm font-medium text-slate-700">External theory URL</label>
                                <input
                                    id="external_url"
                                    type="url"
                                    name="external_url"
                                    x-model="externalUrl"
                                    placeholder="https://example.com/grammar/passive-voice"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    Дозволені тільки <code>http/https</code>. Localhost, private IP, loopback та internal hosts будуть відхилені.
                                </p>
                                @error('external_url')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">2. Категорія теорії</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Можна використати існуючу категорію, попросити створити нову або дати AI право самому вибрати найкращу.
                            </p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach ($categoryModes as $modeValue => $modeLabel)
                                <label class="rounded-xl border px-4 py-3 transition" :class="categoryMode === '{{ $modeValue }}' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                    <input type="radio" name="category_mode" value="{{ $modeValue }}" class="sr-only" x-model="categoryMode">
                                    <div class="font-medium text-slate-900">{{ $modeLabel }}</div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 space-y-5">
                            <div x-show="categoryMode === 'existing'" x-cloak class="space-y-4">
                                <div class="relative" @click.outside="categorySearchOpen = false">
                                    <label for="category-search" class="mb-2 block text-sm font-medium text-slate-700">Search existing theory category</label>
                                    <input
                                        id="category-search"
                                        type="text"
                                        x-model="categorySearch"
                                        @focus="openCategorySearch()"
                                        @input="filterCategoryOptions()"
                                        autocomplete="off"
                                        placeholder="Наприклад: passive voice, questions, word order"
                                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <input type="hidden" name="existing_category_id" :value="selectedCategoryId || ''">

                                    <div
                                        x-show="categorySearchOpen"
                                        x-cloak
                                        class="absolute z-20 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                                    >
                                        <template x-if="filteredCategoryOptions.length === 0">
                                            <div class="px-4 py-3 text-sm text-slate-500">Нічого не знайдено. Спробуйте інший запит або створіть нову категорію.</div>
                                        </template>
                                        <div class="max-h-80 overflow-y-auto">
                                            <template x-for="item in filteredCategoryOptions" :key="item.id">
                                                <button
                                                    type="button"
                                                    @click="selectCategory(item)"
                                                    class="block w-full border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-slate-50"
                                                >
                                                    <div class="font-medium text-slate-900" x-text="item.title"></div>
                                                    <div class="mt-1 text-xs text-slate-500">
                                                        <span class="font-mono" x-text="item.slug"></span>
                                                        <span x-show="item.path"> · <span x-text="item.path"></span></span>
                                                    </div>
                                                    <div class="mt-1 text-xs text-slate-400" x-show="item.namespace" x-text="item.namespace"></div>
                                                    <div class="mt-1 text-xs text-slate-400" x-show="item.seeder_class" x-text="item.seeder_class"></div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    x-show="selectedCategory"
                                    x-cloak
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                >
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="space-y-2">
                                            <div class="text-sm font-semibold text-slate-900" x-text="selectedCategory?.title"></div>
                                            <div class="text-xs text-slate-500">
                                                <span class="font-mono" x-text="selectedCategory?.slug"></span>
                                                <span x-show="selectedCategory?.path"> · <span x-text="selectedCategory?.path"></span></span>
                                            </div>
                                            <div class="text-xs text-slate-400" x-show="selectedCategory?.namespace" x-text="selectedCategory?.namespace"></div>
                                            <div class="text-xs text-slate-400" x-show="selectedCategory?.seeder_class" x-text="selectedCategory?.seeder_class"></div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="clearCategory()"
                                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100"
                                        >
                                            Clear selection
                                        </button>
                                    </div>
                                </div>

                                @error('existing_category_id')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="categoryMode === 'new'" x-cloak>
                                <label for="new_category_title" class="mb-2 block text-sm font-medium text-slate-700">New category title</label>
                                <input
                                    id="new_category_title"
                                    type="text"
                                    name="new_category_title"
                                    x-model="newCategoryTitle"
                                    placeholder="Наприклад: Types of Questions"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p class="mt-2 text-xs text-slate-500">
                                    У prompt буде явно сказано створити category seeder і category JSON definition для цієї нової категорії.
                                </p>
                                @error('new_category_title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="categoryMode === 'ai_select'" x-cloak class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                AI отримає список поточних theory categories з БД, зможе вибрати найкращу категорію для теми або створити нову, якщо підходящої немає.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">3. Режим генерації</h2>
                            <p class="mt-1 text-sm text-slate-500">Один prompt для Codex або два prompt-и: окремо для LLM JSON pack і для Codex інтеграції.</p>
                        </div>

                        <div class="space-y-3">
                            @foreach ($generationModes as $modeValue => $modeLabel)
                                <label class="flex items-start gap-3 rounded-xl border px-4 py-3" :class="generationMode === '{{ $modeValue }}' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50'">
                                    <input type="radio" name="generation_mode" value="{{ $modeValue }}" x-model="generationMode" class="mt-1">
                                    <span class="text-sm text-slate-700">{{ $modeLabel }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error('generation_mode')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Preview</h2>
                        <p class="mt-1 text-sm text-slate-500">Suggested Page_V3 files based on the current form state.</p>

                        <div class="mt-5 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved topic</div>
                                <div class="mt-2 text-sm font-medium text-slate-900" x-text="resolvedTopic() || 'Topic will be resolved from the current source'"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category decision</div>
                                <div class="mt-2 text-sm font-medium text-slate-900" x-text="previewCategoryLabel()"></div>
                                <div class="mt-1 break-all font-mono text-xs text-slate-500" x-text="preview().categoryNamespace"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Page wrapper preview</div>
                                <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="preview().pageSeederPath"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Page JSON preview</div>
                                <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="preview().pageDefinitionPath"></div>
                                <div class="mt-2 break-all font-mono text-xs text-slate-500" x-text="preview().pageLocalizationEnPath"></div>
                                <div class="mt-1 break-all font-mono text-xs text-slate-500" x-text="preview().pageLocalizationPlPath"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category JSON preview</div>
                                <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="preview().categoryDefinitionPath"></div>
                                <div class="mt-2 break-all font-mono text-xs text-slate-500" x-text="preview().categorySeederPath"></div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-900 p-6 text-slate-100 shadow-sm">
                        <h2 class="text-lg font-semibold">Що буде в prompt-ах</h2>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-300">
                            <li>Reference на реальні `Page_V3` seeders, definitions та localization files.</li>
                            <li>Чітка інструкція про reuse existing category або створення нового category seeder.</li>
                            <li>JSON pack format для split mode, сумісний із подальшою інтеграцією в Codex.</li>
                            <li>Safe handling зовнішнього URL без сирих exception у UI.</li>
                        </ul>
                    </div>
                </aside>
            </section>

            <div class="flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl border border-blue-500 bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                >
                    Generate Prompt(s)
                </button>
                <a
                    href="{{ route('page-v3-prompt-generator.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    Clear / Reset
                </a>
            </div>
        </form>

        @if (! empty($result))
            <section class="space-y-5">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    Prompt-и згенеровано. Режим:
                    <span class="font-semibold">{{ $result['generation_mode'] === 'single' ? 'Mode 1 / one prompt' : 'Mode 2 / two prompts' }}</span>.
                </div>

                @foreach ($result['prompts'] as $prompt)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">{{ $prompt['title'] }}</h2>
                                <p class="mt-1 text-sm text-slate-500">Copy-ready текст без додаткової постобробки.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500" x-show="copyStates['prompt-{{ $prompt['key'] }}']" x-cloak>Copied</span>
                                <button
                                    type="button"
                                    @click="copyById('prompt-{{ $prompt['key'] }}')"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Copy
                                </button>
                            </div>
                        </div>
                        <textarea
                            id="prompt-{{ $prompt['key'] }}"
                            readonly
                            class="min-h-[320px] w-full rounded-2xl border border-slate-200 bg-slate-950 p-4 font-mono text-sm leading-6 text-slate-100 shadow-inner focus:border-slate-400 focus:ring-slate-400"
                        >{{ $prompt['text'] }}</textarea>
                    </div>
                @endforeach
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function pageV3PromptGenerator(config) {
            return {
                sourceType: config.form.source_type || 'manual_topic',
                manualTopic: config.form.manual_topic || '',
                externalUrl: config.form.external_url || '',
                categoryMode: config.form.category_mode || 'existing',
                generationMode: config.form.generation_mode || 'single',
                categoryOptions: Array.isArray(config.categoryOptions) ? config.categoryOptions : [],
                filteredCategoryOptions: [],
                categorySearch: config.selectedCategory?.title || '',
                categorySearchOpen: false,
                selectedCategory: config.selectedCategory || null,
                selectedCategoryId: config.selectedCategory?.id || config.form.existing_category_id || null,
                newCategoryTitle: config.form.new_category_title || '',
                copyStates: {},

                init() {
                    this.filterCategoryOptions();
                },

                openCategorySearch() {
                    if (this.categoryMode !== 'existing') {
                        return;
                    }

                    this.categorySearchOpen = true;
                    this.filterCategoryOptions();
                },

                filterCategoryOptions() {
                    const query = (this.categorySearch || '').trim().toLowerCase();

                    this.filteredCategoryOptions = this.categoryOptions
                        .filter((item) => {
                            if (!query) {
                                return true;
                            }

                            return [
                                item.title,
                                item.slug,
                                item.path,
                                item.namespace,
                                item.seeder_class,
                            ].some((value) => (value || '').toLowerCase().includes(query));
                        })
                        .slice(0, 20);
                },

                selectCategory(item) {
                    this.selectedCategory = item;
                    this.selectedCategoryId = item.id;
                    this.categorySearch = item.title;
                    this.categorySearchOpen = false;
                },

                clearCategory() {
                    this.selectedCategory = null;
                    this.selectedCategoryId = null;
                    this.categorySearch = '';
                    this.filterCategoryOptions();
                },

                resolvedTopic() {
                    if (this.sourceType === 'external_url') {
                        return this.externalTopicFromUrl();
                    }

                    return (this.manualTopic || '').trim();
                },

                externalTopicFromUrl() {
                    try {
                        const parsed = new URL((this.externalUrl || '').trim());
                        const parts = parsed.pathname.split('/').filter(Boolean);
                        const candidate = (parts.pop() || parsed.hostname || '').replace(/[-_]+/g, ' ').trim();

                        return candidate
                            ? candidate.replace(/\b\w/g, (letter) => letter.toUpperCase())
                            : '';
                    } catch (error) {
                        return '';
                    }
                },

                slugify(text) {
                    const normalized = (text || '')
                        .toString()
                        .normalize('NFKD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^A-Za-z0-9]+/g, ' ')
                        .trim()
                        .toLowerCase();

                    if (!normalized) {
                        return 'new_page_v3_topic';
                    }

                    return normalized.split(/\s+/).join('_');
                },

                studly(text) {
                    return this.slugify(text)
                        .split(/[_-]+/)
                        .filter(Boolean)
                        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                        .join('') || 'NewPageV3Topic';
                },

                previewCategoryLabel() {
                    if (this.categoryMode === 'existing') {
                        return this.selectedCategory?.title || 'Select an existing theory category';
                    }

                    if (this.categoryMode === 'new') {
                        return this.newCategoryTitle.trim() || 'Create a new theory category';
                    }

                    return 'AI will inspect current categories and choose or create one';
                },

                preview() {
                    const topic = this.resolvedTopic();
                    const topicStem = this.studly(topic);
                    const topicSlug = this.slugify(topic);
                    let categoryNamespace = '';
                    let categoryDefinitionPath = 'database/seeders/Page_V3/definitions/<resolved_category>_category.json';
                    let categorySeederPath = 'database/seeders/Page_V3/<resolved-category-path>/<ResolvedCategory>CategorySeeder.php';

                    if (this.categoryMode === 'existing') {
                        categoryNamespace = this.selectedCategory?.namespace || '';
                        categoryDefinitionPath = `database/seeders/Page_V3/definitions/${this.selectedCategory?.slug || 'existing_category'}_category.json`;
                        categorySeederPath = this.selectedCategory?.seeder_relative_path || `database/seeders/Page_V3/${(categoryNamespace || '<resolved-category-path>').replace(/\\/g, '/')}/${this.studly(this.selectedCategory?.title || 'Existing Category')}CategorySeeder.php`;
                    } else if (this.categoryMode === 'new') {
                        categoryNamespace = this.studly(this.newCategoryTitle || 'New Theory Category');
                        const categorySlug = this.slugify(this.newCategoryTitle || 'New Theory Category');
                        categoryDefinitionPath = `database/seeders/Page_V3/definitions/${categorySlug}_category.json`;
                        categorySeederPath = `database/seeders/Page_V3/${categoryNamespace}/${this.studly(this.newCategoryTitle || 'New Theory Category')}CategorySeeder.php`;
                    } else {
                        categoryNamespace = '<resolved-category-namespace>';
                    }

                    const namespacePath = (categoryNamespace || '<resolved-category-path>').replace(/\\/g, '/');

                    return {
                        categoryNamespace: categoryNamespace || '<resolved-category-namespace>',
                        categoryDefinitionPath,
                        categorySeederPath,
                        pageSeederPath: `database/seeders/Page_V3/${namespacePath}/${topicStem}TheorySeeder.php`,
                        pageDefinitionPath: `database/seeders/Page_V3/definitions/${topicSlug}_theory.json`,
                        pageLocalizationEnPath: `database/seeders/Page_V3/localizations/en/${topicSlug}_theory.json`,
                        pageLocalizationPlPath: `database/seeders/Page_V3/localizations/pl/${topicSlug}_theory.json`,
                    };
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
