@extends('layouts.app')

@section('title', 'Page_V3 Prompt Generator')

@php
    $pageV3Warnings = $pageV3Result['warnings'] ?? [];
    $variantPrompt = $variantResult['prompts'][0]['text'] ?? null;
    $variantSource = $variantResult['source'] ?? [];
    $variantSourceContentExcerpt = $variantResult['source_content_excerpt'] ?? null;
@endphp

@section('content')
    <div
        class="mx-auto max-w-6xl space-y-8 pb-10"
        x-data="pageV3PromptGenerator(@js([
            'activeGeneratorMode' => $activeGeneratorMode,
            'form' => $form,
            'variantForm' => $variantForm,
            'selectedCategory' => $selectedCategory,
            'categoryOptions' => $categoryOptions,
            'pageOptions' => $pageOptions,
            'localeOptions' => $localeOptions,
            'defaultLocale' => $defaultLocale,
        ]))"
        x-init="init()"
    >
        <header class="space-y-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold text-slate-900">Page_V3 Prompt Generator</h1>
                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        Existing admin generator for `Page_V3` prompts plus an additive theory-variant mode that
                        builds copy-ready external-LLM prompts for one theory page or theory category variant seeder.
                    </p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <div class="font-medium">Маршрут</div>
                    <code class="font-mono">/admin/page-v3-prompt-generator</code>
                </div>
            </div>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="grid gap-3 md:grid-cols-2">
                <button
                    type="button"
                    @click="activeGeneratorMode = 'page_v3'"
                    class="rounded-2xl border px-5 py-4 text-left transition"
                    :class="activeGeneratorMode === 'page_v3' ? 'border-blue-500 bg-blue-50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
                >
                    <div class="text-sm font-semibold text-slate-900">Page_V3 Prompt Generator</div>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Existing prompt flow for new `Page_V3` theory seeders, category seeders, and related JSON files.
                    </p>
                </button>

                <button
                    type="button"
                    @click="activeGeneratorMode = 'theory_variant'"
                    class="rounded-2xl border px-5 py-4 text-left transition"
                    :class="activeGeneratorMode === 'theory_variant' ? 'border-blue-500 bg-blue-50 shadow-sm' : 'border-slate-200 bg-slate-50 hover:border-slate-300'"
                >
                    <div class="text-sm font-semibold text-slate-900">Theory Variant Prompt</div>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Builds a universal prompt for Copilot, Claude, Gemini, ChatGPT, or any other external LLM to
                        generate exactly one PHP theory variant seeder for an existing `/theory` page or category.
                    </p>
                </button>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <div class="font-semibold">
                    {{ old('generator_mode', $activeGeneratorMode) === 'theory_variant' ? 'Форма theory variant prompt містить помилки' : 'Форма Page_V3 prompt generator містить помилки' }}
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($pageV3Warnings))
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                <div class="font-semibold">Є попередження</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($pageV3Warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div x-show="activeGeneratorMode === 'page_v3'" x-cloak class="space-y-8">
            <form action="{{ route('page-v3-prompt-generator.generate') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" name="generator_mode" value="page_v3">

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
                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    Prompt A options: <span class="font-medium text-slate-700">Mode A1 / repository-connected</span>
                                    and <span class="font-medium text-slate-700">Mode A2 / no-repository fallback</span>.
                                </p>
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

                            <div class="mt-5" x-show="generationMode === 'split'" x-cloak>
                                <div class="mb-2 block text-sm font-medium text-slate-700">Prompt A mode for split mode</div>
                                <p class="mb-3 text-xs leading-5 text-slate-500">
                                    `Mode A1` очікує підключений репозиторій і живі reference-файли. `Mode A2` вбудовує Page_V3 compatibility reference прямо в Prompt A для роботи без repo access.
                                </p>
                                <div class="grid gap-3 md:grid-cols-2">
                                    @foreach ($promptAModes as $modeValue => $modeLabel)
                                        <label class="rounded-xl border px-4 py-3 transition" :class="promptAMode === '{{ $modeValue }}' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                            <input type="radio" name="prompt_a_mode" value="{{ $modeValue }}" x-model="promptAMode" class="sr-only">
                                            <div class="font-medium text-slate-900">{{ $modeLabel }}</div>
                                            <div class="mt-1 text-sm text-slate-500">
                                                {{ $modeValue === 'repository_connected' ? 'Prompt A uses live Page_V3 references from the repo.' : 'Prompt A uses embedded Page_V3 references and can work offline.' }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                @error('prompt_a_mode')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
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
                                <li>У split mode Prompt A можна перемкнути між `repository-connected` і `no-repository fallback`.</li>
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

            @if (! empty($pageV3Result))
                <section class="space-y-5">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                        Prompt-и згенеровано. Режим:
                        <span class="font-semibold">{{ $pageV3Result['generation_mode'] === 'single' ? 'Mode 1 / one prompt' : 'Mode 2 / two prompts' }}</span>
                        @if (($pageV3Result['generation_mode'] ?? null) === 'split')
                            , Prompt A:
                            <span class="font-semibold">{{ $pageV3Result['prompt_a_mode_label'] ?? '' }}</span>
                        @endif
                        .
                    </div>

                    @foreach ($pageV3Result['prompts'] as $prompt)
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

        <div x-show="activeGeneratorMode === 'theory_variant'" x-cloak class="space-y-8">
            <form action="{{ route('page-v3-prompt-generator.generate') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" name="generator_mode" value="theory_variant">

                <section class="grid gap-8 xl:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)]">
                    <div class="space-y-8">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">1. Theory source</h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Select an existing theory page or category. You can paste a real `/theory/...`
                                        URL, choose explicit slugs, or both. URL parsing runs server-side on submit.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="fillVariantDefaults(true)"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Fill defaults from source
                                </button>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label for="source_lookup_url" class="mb-2 block text-sm font-medium text-slate-700">Source URL input</label>
                                    <input
                                        id="source_lookup_url"
                                        type="text"
                                        name="source_lookup_url"
                                        x-model="sourceLookupUrl"
                                        placeholder="/theory/tenses/present-simple or /theory/tenses"
                                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p class="mt-2 text-xs leading-5 text-slate-500">
                                        If present, the generator will parse this URL and use it to resolve `target_type`,
                                        category slug, and page slug before validation.
                                    </p>
                                    @error('source_lookup_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-slate-700">Target type</label>
                                        <div class="grid gap-3 md:grid-cols-2">
                                            <label class="rounded-xl border px-4 py-3 transition" :class="variantTargetType === 'page' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                                <input type="radio" name="target_type" value="page" class="sr-only" x-model="variantTargetType" @change="syncVariantTargetType()">
                                                <div class="font-medium text-slate-900">Page</div>
                                                <div class="mt-1 text-sm text-slate-500">Generate prompt for one existing theory page variant.</div>
                                            </label>
                                            <label class="rounded-xl border px-4 py-3 transition" :class="variantTargetType === 'category' ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                                                <input type="radio" name="target_type" value="category" class="sr-only" x-model="variantTargetType" @change="syncVariantTargetType()">
                                                <div class="font-medium text-slate-900">Category</div>
                                                <div class="mt-1 text-sm text-slate-500">Generate prompt for one existing theory category variant.</div>
                                            </label>
                                        </div>
                                        @error('target_type')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="locale" class="mb-2 block text-sm font-medium text-slate-700">Requested locale</label>
                                        <select
                                            id="locale"
                                            name="locale"
                                            x-model="variantLocale"
                                            @change="fillVariantDefaults(false)"
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            @foreach ($localeOptions as $localeValue => $localeLabel)
                                                <option value="{{ $localeValue }}">{{ $localeLabel }}</option>
                                            @endforeach
                                        </select>
                                        @error('locale')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="target_category_slug" class="mb-2 block text-sm font-medium text-slate-700">Theory category</label>
                                        <select
                                            id="target_category_slug"
                                            name="target_category_slug"
                                            x-model="variantTargetCategorySlug"
                                            @change="syncVariantCategorySelection()"
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="">Select category</option>
                                            @foreach ($categoryOptions as $category)
                                                <option value="{{ $category['slug'] }}">
                                                    {{ $category['path'] ?: $category['title'] }} [{{ $category['slug'] }}]
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('target_category_slug')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div x-show="variantTargetType === 'page'" x-cloak>
                                        <label for="target_page_slug" class="mb-2 block text-sm font-medium text-slate-700">Theory page</label>
                                        <select
                                            id="target_page_slug"
                                            name="target_page_slug"
                                            x-model="variantTargetPageSlug"
                                            @change="syncVariantPageSelection()"
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="">Select page</option>
                                            <template x-for="item in availableVariantPageOptions()" :key="`${item.category_slug}-${item.slug}`">
                                                <option :value="item.slug" x-text="`${item.title} [${item.slug}]`"></option>
                                            </template>
                                        </select>
                                        @error('target_page_slug')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <h2 class="text-lg font-semibold text-slate-900">2. Variant metadata</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    These values are embedded directly into the generated universal prompt. Defaults are
                                    suggested from the selected source, but every field remains editable.
                                </p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="namespace" class="mb-2 block text-sm font-medium text-slate-700">Namespace</label>
                                    <input id="namespace" type="text" name="namespace" x-model="variantNamespace" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('namespace')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="class_name" class="mb-2 block text-sm font-medium text-slate-700">Class name</label>
                                    <input id="class_name" type="text" name="class_name" x-model="variantClassName" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('class_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="variant_key" class="mb-2 block text-sm font-medium text-slate-700">Variant key</label>
                                    <input id="variant_key" type="text" name="variant_key" x-model="variantKey" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('variant_key')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="label" class="mb-2 block text-sm font-medium text-slate-700">Label</label>
                                    <input id="label" type="text" name="label" x-model="variantLabel" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('label')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="provider" class="mb-2 block text-sm font-medium text-slate-700">Provider</label>
                                    <input id="provider" type="text" name="provider" x-model="variantProvider" placeholder="Optional" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('provider')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="model" class="mb-2 block text-sm font-medium text-slate-700">Model</label>
                                    <input id="model" type="text" name="model" x-model="variantModel" placeholder="Optional" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('model')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="prompt_version" class="mb-2 block text-sm font-medium text-slate-700">Prompt version</label>
                                    <input id="prompt_version" type="text" name="prompt_version" x-model="variantPromptVersion" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('prompt_version')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="output_mode_preference" class="mb-2 block text-sm font-medium text-slate-700">Output mode preference</label>
                                    <select id="output_mode_preference" name="output_mode_preference" x-model="variantOutputModePreference" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="downloadable_php_file">downloadable_php_file</option>
                                        <option value="fenced_php_code_block">fenced_php_code_block</option>
                                    </select>
                                    @error('output_mode_preference')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="source_url" class="mb-2 block text-sm font-medium text-slate-700">Source URL</label>
                                    <input id="source_url" type="text" name="source_url" x-model="variantSourceUrl" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('source_url')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="source_page_title" class="mb-2 block text-sm font-medium text-slate-700">Source page title</label>
                                    <input id="source_page_title" type="text" name="source_page_title" x-model="variantSourcePageTitle" :disabled="variantTargetType === 'category'" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-slate-100">
                                    @error('source_page_title')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="source_category_title" class="mb-2 block text-sm font-medium text-slate-700">Source category title</label>
                                    <input id="source_category_title" type="text" name="source_category_title" x-model="variantSourceCategoryTitle" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('source_category_title')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="source_page_seeder_class" class="mb-2 block text-sm font-medium text-slate-700">Source page seeder class</label>
                                    <input id="source_page_seeder_class" type="text" name="source_page_seeder_class" x-model="variantSourcePageSeederClass" placeholder="Optional/manual if not detectable" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('source_page_seeder_class')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <h2 class="text-lg font-semibold text-slate-900">3. Rewrite brief</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    These instructions shape the universal prompt. They do not trigger any external AI
                                    call from this admin screen.
                                </p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="target_learner_levels" class="mb-2 block text-sm font-medium text-slate-700">Target learner levels</label>
                                    <input id="target_learner_levels" type="text" name="target_learner_levels" x-model="variantTargetLearnerLevels" placeholder="A2-B1, B2, mixed adult learners" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('target_learner_levels')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="tone" class="mb-2 block text-sm font-medium text-slate-700">Tone</label>
                                    <input id="tone" type="text" name="tone" x-model="variantTone" placeholder="Teacher-friendly, concise, exam-focused" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('tone')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="rewrite_goal" class="mb-2 block text-sm font-medium text-slate-700">Rewrite goal</label>
                                    <input id="rewrite_goal" type="text" name="rewrite_goal" x-model="variantRewriteGoal" placeholder="Fresh alternative explanation with clearer scaffolding" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('rewrite_goal')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="content_strategy" class="mb-2 block text-sm font-medium text-slate-700">Content strategy</label>
                                    <input id="content_strategy" type="text" name="content_strategy" x-model="variantContentStrategy" placeholder="Keep structure, simplify explanations, expand examples" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('content_strategy')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="must_cover_list" class="mb-2 block text-sm font-medium text-slate-700">Must cover list</label>
                                    <textarea id="must_cover_list" name="must_cover_list" x-model="variantMustCoverList" rows="5" placeholder="- core definition&#10;- affirmative / negative / question use&#10;- timeline logic" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    @error('must_cover_list')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="avoid_list" class="mb-2 block text-sm font-medium text-slate-700">Avoid list</label>
                                    <textarea id="avoid_list" name="avoid_list" x-model="variantAvoidList" rows="5" placeholder="- avoid copying original wording&#10;- avoid unsupported HTML&#10;- avoid changing page identity" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    @error('avoid_list')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="editor_notes" class="mb-2 block text-sm font-medium text-slate-700">Editor notes</label>
                                    <textarea id="editor_notes" name="editor_notes" x-model="variantEditorNotes" rows="6" placeholder="Optional notes for the external LLM about examples, pacing, or pedagogical framing." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    @error('editor_notes')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-6">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900">Selection preview</h2>
                            <p class="mt-1 text-sm text-slate-500">Pre-submit defaults inferred from the currently selected theory source.</p>

                            <div class="mt-5 space-y-4">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Source target</div>
                                    <div class="mt-2 text-sm font-medium text-slate-900" x-text="variantTargetType === 'page' ? 'Theory page' : 'Theory category'"></div>
                                    <div class="mt-1 break-all font-mono text-xs text-slate-500" x-text="variantSuggestedDefaults().sourceUrl || 'Select a category or page'"></div>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved category</div>
                                    <div class="mt-2 text-sm font-medium text-slate-900" x-text="variantCurrentCategory()?.title || 'No category selected'"></div>
                                    <div class="mt-1 break-all font-mono text-xs text-slate-500" x-text="variantCurrentCategory()?.slug || ''"></div>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4" x-show="variantTargetType === 'page'" x-cloak>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resolved page</div>
                                    <div class="mt-2 text-sm font-medium text-slate-900" x-text="variantCurrentPage()?.title || 'No page selected'"></div>
                                    <div class="mt-1 break-all font-mono text-xs text-slate-500" x-text="variantCurrentPage()?.slug || ''"></div>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Suggested namespace</div>
                                    <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="variantSuggestedDefaults().namespace"></div>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Suggested class</div>
                                    <div class="mt-2 break-all font-mono text-sm text-slate-900" x-text="variantSuggestedDefaults().className"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-900 p-6 text-slate-100 shadow-sm">
                            <h2 class="text-lg font-semibold">What this mode does</h2>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-300">
                                <li>Loads real theory source material from the DB for the selected page or category.</li>
                                <li>Normalizes source title, subtitle, locale, and theory blocks into the prompt payload.</li>
                                <li>Embeds anti-confusion rules and the real base variant seeder contract into the prompt text.</li>
                                <li>Does not call OpenAI, Gemini, Copilot, Claude, or any other external API.</li>
                                <li>Outputs only copy-ready prompt text for another LLM to turn into one PHP seeder class.</li>
                            </ul>
                        </div>
                    </aside>
                </section>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-blue-500 bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                        Generate Theory Variant Prompt
                    </button>
                    <a href="{{ route('page-v3-prompt-generator.index', ['generator_mode' => 'theory_variant']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Clear / Reset
                    </a>
                </div>
            </form>

            @if (! empty($variantResult))
                <section class="space-y-5">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                        Theory variant prompt generated.
                        Requested locale:
                        <span class="font-semibold">{{ $variantSource['requested_locale'] ?? $variantForm['locale'] }}</span>.
                        Source locale actually used:
                        <span class="font-semibold">{{ $variantSource['source_locale'] ?? $variantForm['locale'] }}</span>.
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Theory Variant Prompt</h2>
                                <p class="mt-1 text-sm text-slate-500">Copy-ready universal prompt for an external LLM.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500" x-show="copyStates['prompt-theory-variant']" x-cloak>Copied</span>
                                <button type="button" @click="copyById('prompt-theory-variant')" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Copy prompt
                                </button>
                                <button type="button" @click="downloadTextById('prompt-theory-variant', 'theory-variant-prompt.txt')" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Download .txt
                                </button>
                            </div>
                        </div>

                        <textarea
                            id="prompt-theory-variant"
                            readonly
                            class="min-h-[420px] w-full rounded-2xl border border-slate-200 bg-slate-950 p-4 font-mono text-sm leading-6 text-slate-100 shadow-inner focus:border-slate-400 focus:ring-slate-400"
                        >{{ $variantPrompt }}</textarea>
                    </div>

                    <div class="grid gap-5 xl:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900">Loaded source excerpt</h2>
                            <p class="mt-1 text-sm text-slate-500">Escaped HTML in the browser, raw text preserved inside the generated prompt itself.</p>

                            <textarea
                                id="variant-source-content-excerpt"
                                readonly
                                class="mt-4 min-h-[320px] w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 font-mono text-sm leading-6 text-slate-900 shadow-inner focus:border-slate-400 focus:ring-slate-400"
                            >{{ $variantSourceContentExcerpt }}</textarea>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-lg font-semibold text-slate-900">Normalized payload excerpt</h2>
                            <p class="mt-1 text-sm text-slate-500">Stable structured source data embedded into the prompt for offline external LLM use.</p>

                            <textarea
                                id="variant-normalized-payload-excerpt"
                                readonly
                                class="mt-4 min-h-[320px] w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 font-mono text-sm leading-6 text-slate-900 shadow-inner focus:border-slate-400 focus:ring-slate-400"
                            >{{ $variantSource['normalized_payload_json'] ?? '' }}</textarea>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function pageV3PromptGenerator(config) {
            return {
                activeGeneratorMode: config.activeGeneratorMode || 'page_v3',

                sourceType: config.form.source_type || 'manual_topic',
                manualTopic: config.form.manual_topic || '',
                externalUrl: config.form.external_url || '',
                categoryMode: config.form.category_mode || 'existing',
                generationMode: config.form.generation_mode || 'single',
                promptAMode: config.form.prompt_a_mode || 'repository_connected',

                categoryOptions: Array.isArray(config.categoryOptions) ? config.categoryOptions : [],
                variantPageOptions: Array.isArray(config.pageOptions) ? config.pageOptions : [],
                localeOptions: config.localeOptions || {},
                defaultLocale: config.defaultLocale || 'uk',

                filteredCategoryOptions: [],
                categorySearch: config.selectedCategory?.title || '',
                categorySearchOpen: false,
                selectedCategory: config.selectedCategory || null,
                selectedCategoryId: config.selectedCategory?.id || config.form.existing_category_id || null,
                newCategoryTitle: config.form.new_category_title || '',

                sourceLookupUrl: config.variantForm.source_lookup_url || '',
                variantTargetType: config.variantForm.target_type || 'page',
                variantTargetCategorySlug: config.variantForm.target_category_slug || '',
                variantTargetPageSlug: config.variantForm.target_page_slug || '',
                variantLocale: config.variantForm.locale || config.defaultLocale || 'uk',
                variantNamespace: config.variantForm.namespace || '',
                variantClassName: config.variantForm.class_name || '',
                variantKey: config.variantForm.variant_key || '',
                variantLabel: config.variantForm.label || '',
                variantProvider: config.variantForm.provider || '',
                variantModel: config.variantForm.model || '',
                variantPromptVersion: config.variantForm.prompt_version || 'v1',
                variantSourceUrl: config.variantForm.source_url || '',
                variantSourcePageTitle: config.variantForm.source_page_title || '',
                variantSourceCategoryTitle: config.variantForm.source_category_title || '',
                variantSourcePageSeederClass: config.variantForm.source_page_seeder_class || '',
                variantTargetLearnerLevels: config.variantForm.target_learner_levels || '',
                variantTone: config.variantForm.tone || '',
                variantRewriteGoal: config.variantForm.rewrite_goal || '',
                variantContentStrategy: config.variantForm.content_strategy || '',
                variantMustCoverList: config.variantForm.must_cover_list || '',
                variantAvoidList: config.variantForm.avoid_list || '',
                variantEditorNotes: config.variantForm.editor_notes || '',
                variantOutputModePreference: config.variantForm.output_mode_preference || 'downloadable_php_file',

                copyStates: {},

                init() {
                    this.filterCategoryOptions();
                    this.syncVariantTargetType();
                    this.syncVariantCategorySelection();
                    this.fillVariantDefaults(false);
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

                humanizeSlug(text) {
                    const normalized = (text || '').toString().trim();

                    if (!normalized) {
                        return '';
                    }

                    return normalized
                        .replace(/[-_]+/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .replace(/\b\w/g, (letter) => letter.toUpperCase());
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

                variantCurrentCategory() {
                    return this.categoryOptions.find((item) => item.slug === this.variantTargetCategorySlug) || null;
                },

                availableVariantPageOptions() {
                    const categorySlug = (this.variantTargetCategorySlug || '').trim();

                    if (!categorySlug) {
                        return [];
                    }

                    return this.variantPageOptions.filter((item) => item.category_slug === categorySlug);
                },

                variantCurrentPage() {
                    return this.availableVariantPageOptions().find((item) => item.slug === this.variantTargetPageSlug) || null;
                },

                syncVariantTargetType() {
                    if (this.variantTargetType !== 'page') {
                        this.variantTargetPageSlug = '';
                        this.variantSourcePageTitle = '';
                        this.variantSourcePageSeederClass = '';
                    }

                    this.fillVariantDefaults(false);
                },

                syncVariantCategorySelection() {
                    const validPage = this.availableVariantPageOptions().some((item) => item.slug === this.variantTargetPageSlug);

                    if (!validPage) {
                        this.variantTargetPageSlug = '';
                    }

                    this.fillVariantDefaults(false);
                },

                syncVariantPageSelection() {
                    this.fillVariantDefaults(false);
                },

                variantSuggestedDefaults() {
                    const locale = (this.variantLocale || this.defaultLocale || 'uk').toLowerCase();
                    const category = this.variantCurrentCategory();
                    const page = this.variantCurrentPage();
                    const categoryTitle = category?.title || this.humanizeSlug(this.variantTargetCategorySlug) || 'Theory Category';
                    const targetTitle = this.variantTargetType === 'page'
                        ? (page?.title || this.humanizeSlug(this.variantTargetPageSlug) || 'Theory Page')
                        : categoryTitle;

                    return {
                        namespace: `Database\\Seeders\\Page_v2\\Variants\\${this.studly(categoryTitle)}`,
                        className: `${this.studly(targetTitle)}${this.studly(locale)}V1Seeder`,
                        variantKey: `generated-${locale}-v1`,
                        label: `Generated ${locale.toUpperCase()} v1`,
                        promptVersion: 'v1',
                        sourceUrl: this.computeVariantSourceUrl(),
                        sourcePageTitle: this.variantTargetType === 'page' ? (page?.title || '') : '',
                        sourceCategoryTitle: category?.title || '',
                        sourcePageSeederClass: page?.page_seeder_class || '',
                    };
                },

                computeVariantSourceUrl() {
                    const page = this.variantCurrentPage();

                    if (this.variantTargetType === 'page' && page?.source_url) {
                        return page.source_url;
                    }

                    const categorySlug = (this.variantTargetCategorySlug || '').trim();

                    if (!categorySlug) {
                        return '';
                    }

                    if (this.variantTargetType === 'page' && this.variantTargetPageSlug) {
                        return `/theory/${categorySlug}/${this.variantTargetPageSlug}`;
                    }

                    return `/theory/${categorySlug}`;
                },

                fillVariantDefaults(force = false) {
                    const defaults = this.variantSuggestedDefaults();

                    if (force || !(this.variantNamespace || '').trim()) {
                        this.variantNamespace = defaults.namespace;
                    }

                    if (force || !(this.variantClassName || '').trim()) {
                        this.variantClassName = defaults.className;
                    }

                    if (force || !(this.variantKey || '').trim()) {
                        this.variantKey = defaults.variantKey;
                    }

                    if (force || !(this.variantLabel || '').trim()) {
                        this.variantLabel = defaults.label;
                    }

                    if (force || !(this.variantPromptVersion || '').trim()) {
                        this.variantPromptVersion = defaults.promptVersion;
                    }

                    if (force || !(this.variantSourceUrl || '').trim()) {
                        this.variantSourceUrl = defaults.sourceUrl;
                    }

                    if (this.variantTargetType === 'page') {
                        if (force || !(this.variantSourcePageTitle || '').trim()) {
                            this.variantSourcePageTitle = defaults.sourcePageTitle;
                        }

                        if (force || !(this.variantSourcePageSeederClass || '').trim()) {
                            this.variantSourcePageSeederClass = defaults.sourcePageSeederClass;
                        }
                    } else if (force) {
                        this.variantSourcePageTitle = '';
                        this.variantSourcePageSeederClass = '';
                    }

                    if (force || !(this.variantSourceCategoryTitle || '').trim()) {
                        this.variantSourceCategoryTitle = defaults.sourceCategoryTitle;
                    }
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

                downloadTextById(id, filename) {
                    const element = document.getElementById(id);

                    if (!element) {
                        return;
                    }

                    const blob = new Blob([element.value || ''], { type: 'text/plain;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');

                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }
            };
        }
    </script>
@endpush
