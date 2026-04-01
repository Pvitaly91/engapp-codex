@extends('layouts.app')

@section('title', 'V3 Prompt Generator')

@section('content')
    <div
        class="mx-auto max-w-6xl space-y-8 pb-10"
        x-data="v3PromptGenerator(@js([
            'form' => $form,
            'selectedTheoryPage' => $selectedTheoryPage,
            'searchRoute' => $searchRoute,
            'namespaceSuggestions' => $namespaceSuggestions,
        ]))"
        x-init="init()"
    >
        <header class="space-y-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold text-slate-900">V3 Prompt Generator</h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        Інструмент готує copy-ready prompt-и для генерації V3 seeders під поточний Laravel-проєкт.
                        Він працює від теми, CEFR-рівнів, target namespace і реальних V3 reference файлів із цього репозиторію.
                    </p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <div class="font-medium">Маршрут</div>
                    <code class="font-mono">/admin/v3-prompt-generator</code>
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

        <form action="{{ route('v3-prompt-generator.generate') }}" method="POST" class="space-y-8">
            @csrf

            <section class="grid gap-8 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
                <div class="space-y-8">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-slate-900">1. Джерело теми</h2>
                            <p class="mt-1 text-sm text-slate-500">Оберіть один із трьох способів: локальна theory page, ручна тема або external URL.</p>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <label class="rounded-xl border px-4 py-3 transition" :class="sourceType === 'theory_page' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="source_type" value="theory_page" class="sr-only" x-model="sourceType">
                                <div class="font-medium text-slate-900">Existing theory page</div>
                                <div class="mt-1 text-sm text-slate-500">Пошук по title/slug із поточних theory pages.</div>
                            </label>
                            <label class="rounded-xl border px-4 py-3 transition" :class="sourceType === 'manual_topic' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="source_type" value="manual_topic" class="sr-only" x-model="sourceType">
                                <div class="font-medium text-slate-900">Manual topic</div>
                                <div class="mt-1 text-sm text-slate-500">Власна тема текстом, без прив’язки до сторінки сайту.</div>
                            </label>
                            <label class="rounded-xl border px-4 py-3 transition" :class="sourceType === 'external_url' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                <input type="radio" name="source_type" value="external_url" class="sr-only" x-model="sourceType">
                                <div class="font-medium text-slate-900">External theory URL</div>
                                <div class="mt-1 text-sm text-slate-500">Зовнішня сторінка з базовим safe fetch та text snippet.</div>
                            </label>
                        </div>

                        <div class="mt-6 space-y-5">
                            <div x-show="sourceType === 'theory_page'" x-cloak class="space-y-4">
                                <div class="relative" @click.outside="searchOpen = false">
                                    <label for="theory-search" class="mb-2 block text-sm font-medium text-slate-700">Пошук theory page</label>
                                    <input
                                        id="theory-search"
                                        type="text"
                                        x-model="theorySearch"
                                        @focus="openSearch()"
                                        @input="queueSearch()"
                                        autocomplete="off"
                                        placeholder="Наприклад: passive voice, plural nouns, present perfect"
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
                            </div>

                            <div x-show="sourceType === 'manual_topic'" x-cloak>
                                <label for="manual_topic" class="mb-2 block text-sm font-medium text-slate-700">Manual topic</label>
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
                            <h2 class="text-lg font-semibold text-slate-900">2. Namespace і параметри</h2>
                            <p class="mt-1 text-sm text-slate-500">Prompt-и одразу готуються під конкретну V3 папку, клас і обрані CEFR-рівні.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="namespace_base" class="mb-2 block text-sm font-medium text-slate-700">Target namespace inside <code>database/seeders/V3</code></label>
                                <div class="space-y-4">
                                    <div>
                                        <label for="namespace_base" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Base namespace</label>
                                        <select
                                            id="namespace_base"
                                            x-model="selectedNamespaceBase"
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <template x-for="option in namespaceBaseOptions()" :key="option">
                                                <option :value="option" x-text="option"></option>
                                            </template>
                                        </select>
                                        <p class="mt-2 text-xs text-slate-500">За замовчуванням доступні AI-папки: <code>AI\ChatGpt</code>, <code>AI\ChatGptPro</code>, <code>AI\Gemini</code>, <code>AI\Claude</code>. Також у списку є поточні namespace з репозиторію.</p>
                                    </div>

                                    <div>
                                        <label for="namespace_suffix" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Additional folders inside selected namespace</label>
                                        <input
                                            id="namespace_suffix"
                                            type="text"
                                            x-model="namespaceSuffix"
                                            placeholder="Наприклад: Grammar\\PluralNouns"
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                        <p class="mt-2 text-xs text-slate-500">Опційно. Наприклад, suffix <code>Grammar\PluralNouns</code> перетворить <code>AI\ChatGptPro</code> на <code>AI\ChatGptPro\Grammar\PluralNouns</code>.</p>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved target namespace</div>
                                        <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="normalizedNamespace()"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="target_namespace" name="target_namespace" :value="normalizedNamespace()">
                                @error('target_namespace')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="site_domain" class="mb-2 block text-sm font-medium text-slate-700">Public domain for generated theory URLs</label>
                                <input
                                    id="site_domain"
                                    name="site_domain"
                                    x-model="siteDomain"
                                    placeholder="gramlyze.com"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <p class="mt-2 text-xs text-slate-500">Використовується для `Full URL` у prompt-ах. За замовчуванням: <code>gramlyze.com</code>.</p>
                                @error('site_domain')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="questions_per_level" class="mb-2 block text-sm font-medium text-slate-700">Questions per level</label>
                                <input
                                    id="questions_per_level"
                                    type="number"
                                    min="1"
                                    max="50"
                                    name="questions_per_level"
                                    x-model.number="questionsPerLevel"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                @error('questions_per_level')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="mb-2 block text-sm font-medium text-slate-700">Generation mode</div>
                                <div class="space-y-2">
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

                            <div class="md:col-span-2" x-show="generationMode === 'split'" x-cloak>
                                <div class="mb-2 block text-sm font-medium text-slate-700">Prompt A mode for split mode</div>
                                <p class="mb-3 text-xs leading-5 text-slate-500">
                                    `Mode A1` працює з live reference-файлами підключеного репозиторію. `Mode A2` вбудовує compatibility contract прямо в Prompt A для роботи без repo access.
                                </p>
                                <div class="grid gap-3 md:grid-cols-2">
                                    @foreach ($promptAModes as $modeValue => $modeLabel)
                                        <label class="rounded-xl border px-4 py-3 transition" :class="promptAMode === '{{ $modeValue }}' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                            <input type="radio" name="prompt_a_mode" value="{{ $modeValue }}" x-model="promptAMode" class="sr-only">
                                            <div class="font-medium text-slate-900">{{ $modeLabel }}</div>
                                            <div class="mt-1 text-sm text-slate-500">
                                                {{ $modeValue === 'repository_connected' ? 'Prompt A requires repository-connected references.' : 'Prompt A carries embedded references and can work offline.' }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('prompt_a_mode')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="mb-2 block text-sm font-medium text-slate-700">CEFR levels</div>
                            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                                @foreach ($availableLevels as $level)
                                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 hover:border-slate-300">
                                        <input
                                            type="checkbox"
                                            name="levels[]"
                                            value="{{ $level }}"
                                            x-model="selectedLevels"
                                            class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                        >
                                        <span>{{ $level }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('levels')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('levels.*')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Preview</h2>
                        <p class="mt-1 text-sm text-slate-500">Очікуваний клас і шляхи будуються від обраної теми та namespace.</p>

                        <div class="mt-5 space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved topic</div>
                                <div class="mt-2 text-sm font-medium text-slate-900" x-text="resolvedTopic() || 'Topic will be resolved from the current source'"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class name preview</div>
                                <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="preview.className"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Seeder path preview</div>
                                <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="preview.seederPath"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">JSON path preview</div>
                                <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="preview.definitionPath"></div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total questions</div>
                                <div class="mt-2 text-2xl font-semibold text-slate-900" x-text="totalQuestions()"></div>
                                <div class="mt-1 text-sm text-slate-500" x-text="distributionLabel()"></div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-900 p-6 text-slate-100 shadow-sm">
                        <h2 class="text-lg font-semibold">Що буде в prompt-ах</h2>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-300">
                            <li>Реальний reference на існуючі V3 seeders і `JsonTestSeeder` контракт.</li>
                            <li>Вибрані CEFR рівні, counts per level і total questions.</li>
                            <li>Target namespace, class preview, seeder path і JSON path.</li>
                            <li>Для split mode можна окремо перемкнути Prompt A між `repository-connected` і `no-repository fallback`.</li>
                            <li>Контекст з local theory page або external URL snippet, якщо він доступний.</li>
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
                    href="{{ route('v3-prompt-generator.index') }}"
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
                    <span class="font-semibold">{{ $result['generation_mode'] === 'single' ? 'Mode 1 / one prompt' : 'Mode 2 / two prompts' }}</span>,
                    @if (($result['generation_mode'] ?? null) === 'split')
                        Prompt A:
                        <span class="font-semibold">{{ $result['prompt_a_mode_label'] ?? '' }}</span>,
                    @endif
                    всього питань:
                    <span class="font-semibold">{{ $result['total_questions'] }}</span>.
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
        function v3PromptGenerator(config) {
            return {
                sourceType: config.form.source_type || 'theory_page',
                generationMode: config.form.generation_mode || 'single',
                promptAMode: config.form.prompt_a_mode || 'repository_connected',
                targetNamespace: config.form.target_namespace || 'AI\\ChatGptPro',
                namespaceSuggestions: Array.isArray(config.namespaceSuggestions) ? config.namespaceSuggestions : [],
                namespacePresets: ['AI\\ChatGpt', 'AI\\ChatGptPro', 'AI\\Gemini', 'AI\\Claude'],
                selectedNamespaceBase: 'AI\\ChatGptPro',
                namespaceSuffix: '',
                siteDomain: config.form.site_domain || 'gramlyze.com',
                manualTopic: config.form.manual_topic || '',
                externalUrl: config.form.external_url || '',
                selectedLevels: Array.isArray(config.form.levels) ? config.form.levels : ['A1'],
                questionsPerLevel: Number(config.form.questions_per_level || 5),
                theorySearch: config.selectedTheoryPage?.title || '',
                selectedTheoryPage: config.selectedTheoryPage || null,
                selectedTheoryPageId: config.selectedTheoryPage?.id || config.form.theory_page_id || null,
                searchRoute: config.searchRoute,
                searchResults: [],
                searchBusy: false,
                searchOpen: false,
                searchTimeout: null,
                copyStates: {},

                init() {
                    this.syncNamespaceSelection();
                    this.reconcileLevels();
                },

                namespaceBaseCandidates() {
                    const options = [];
                    const pushUnique = (value) => {
                        const normalized = this.normalizeNamespaceValue(value);

                        if (!normalized || options.includes(normalized)) {
                            return;
                        }

                        options.push(normalized);
                    };

                    this.namespacePresets.forEach(pushUnique);
                    this.namespaceSuggestions.forEach(pushUnique);

                    return options;
                },

                namespaceBaseOptions() {
                    const options = this.namespaceBaseCandidates();
                    const currentBase = this.normalizeNamespaceValue(this.selectedNamespaceBase || this.targetNamespace);

                    if (currentBase && !options.includes(currentBase)) {
                        options.push(currentBase);
                    }

                    return options;
                },

                syncNamespaceSelection() {
                    const normalized = this.normalizeNamespaceValue(this.targetNamespace || 'AI\\ChatGptPro');
                    const match = [...this.namespaceBaseCandidates()]
                        .sort((left, right) => right.length - left.length)
                        .find((option) => normalized === option || normalized.startsWith(`${option}\\`));

                    if (match) {
                        this.selectedNamespaceBase = match;
                        this.namespaceSuffix = normalized === match
                            ? ''
                            : normalized.slice(match.length + 1);

                        return;
                    }

                    this.selectedNamespaceBase = normalized || 'AI\\ChatGptPro';
                    this.namespaceSuffix = '';
                },

                normalizeNamespaceValue(namespace) {
                    return (namespace || '')
                        .replace(/\//g, '\\')
                        .replace(/\\+/g, '\\')
                        .replace(/^\\+|\\+$/g, '')
                        .trim();
                },

                openSearch() {
                    if (this.sourceType !== 'theory_page') {
                        return;
                    }

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
                        url.searchParams.set('site_domain', this.siteDomain || 'gramlyze.com');

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
                },

                clearTheoryPage() {
                    this.selectedTheoryPage = null;
                    this.selectedTheoryPageId = null;
                    this.theorySearch = '';
                    this.searchResults = [];
                },

                reconcileLevels() {
                    if (!Array.isArray(this.selectedLevels)) {
                        this.selectedLevels = [];
                    }

                    this.selectedLevels = [...new Set(this.selectedLevels)].sort((left, right) => {
                        const order = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
                        return order.indexOf(left) - order.indexOf(right);
                    });
                },

                resolvedTopic() {
                    if (this.sourceType === 'theory_page') {
                        return this.selectedTheoryPage?.title || this.theorySearch.trim();
                    }

                    if (this.sourceType === 'external_url') {
                        return this.externalTopicFromUrl();
                    }

                    return this.manualTopic.trim();
                },

                externalTopicFromUrl() {
                    try {
                        const parsed = new URL(this.externalUrl.trim());
                        const parts = parsed.pathname.split('/').filter(Boolean);
                        const candidate = (parts.pop() || parsed.hostname || '').replace(/[-_]+/g, ' ').trim();

                        return candidate
                            ? candidate.replace(/\b\w/g, (letter) => letter.toUpperCase())
                            : '';
                    } catch (error) {
                        return '';
                    }
                },

                normalizedNamespace() {
                    const base = this.normalizeNamespaceValue(this.selectedNamespaceBase || 'AI\\ChatGptPro');
                    const suffix = this.normalizeNamespaceValue(this.namespaceSuffix);

                    if (!suffix) {
                        return base;
                    }

                    return `${base}\\${suffix}`;
                },

                usesQuestionsOnlySuffix() {
                    const namespace = this.normalizedNamespace().toLowerCase();

                    return [
                        'chatgpt',
                        'gpt',
                        'gemini',
                        'claude',
                        'anthropic',
                        'openai',
                        'llm',
                    ].some((token) => namespace.includes(token)) || namespace.startsWith('ia');
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
                        return 'new_v3_topic';
                    }

                    return normalized.split(/\s+/).join('_');
                },

                studly(text) {
                    return this.slugify(text)
                        .split(/[_-]+/)
                        .filter(Boolean)
                        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                        .join('');
                },

                get preview() {
                    const topic = this.resolvedTopic();
                    const namespace = this.normalizedNamespace();
                    const namespacePath = namespace.replace(/\\/g, '/');
                    const topicSlug = this.slugify(topic);
                    const stem = this.studly(topic);
                    const suffix = this.usesQuestionsOnlySuffix() ? 'V3QuestionsOnlySeeder' : 'V3Seeder';
                    const definitionSuffix = this.usesQuestionsOnlySuffix() ? '_v3_questions_only.json' : '_v3.json';
                    const className = `${stem || 'NewV3Topic'}${suffix}`;

                    return {
                        className,
                        seederPath: `database/seeders/V3/${namespacePath}/${className}.php`,
                        definitionPath: `database/seeders/V3/definitions/${namespacePath}/${topicSlug}${definitionSuffix}`,
                    };
                },

                totalQuestions() {
                    this.reconcileLevels();
                    return this.selectedLevels.length * (Number(this.questionsPerLevel) || 0);
                },

                distributionLabel() {
                    this.reconcileLevels();

                    if (this.selectedLevels.length === 0) {
                        return 'Оберіть хоча б один рівень.';
                    }

                    return `${this.selectedLevels.join(', ')} × ${Number(this.questionsPerLevel) || 0} questions`;
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
