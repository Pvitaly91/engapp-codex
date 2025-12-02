@extends('layouts.app')

@section('title', 'Промт генератор')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <header class="space-y-3">
        <p class="text-sm uppercase tracking-wide text-blue-600 font-semibold">Новий модуль</p>
        <h1 class="text-3xl font-bold text-slate-900">Генератор промптів для AI сидерів</h1>
        <p class="text-slate-600 leading-relaxed">
            Заповніть параметри для створення промпту під генерацію нового сидера тестових запитань.
            Можна обирати дані з наявних ресурсів сайту або вказувати значення вручну.
        </p>
    </header>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <div class="font-semibold mb-2">Виправте помилки у формі:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('prompt-generator.generate') }}" class="space-y-6">
        @csrf
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-800">Тема та джерела</h2>
                        <p class="text-sm text-slate-500">Оберіть тему тесту та, за потреби, посилання на теорію.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">Крок 1</span>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2" data-search-select>
                        <label for="topic_name" class="block text-sm font-medium text-slate-700">TOPIC_NAME</label>
                        <input
                            type="text"
                            name="topic_name"
                            id="topic_name"
                            value="{{ old('topic_name', $state['topic_name']) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="Напр.: Mixed Conditionals"
                            required
                        >
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Пошук у списку тем"
                                    data-search-input
                                >
                            </div>
                            <select
                                size="6"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                data-searchable-select
                                data-target-input="topic_name"
                            >
                                @foreach ($topicOptions as $topic)
                                    <option value="{{ $topic }}">{{ $topic }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500">Оберіть зі списку або залиште власне значення у полі вище.</p>
                        </div>
                    </div>

                    <div class="space-y-2" data-search-select>
                        <label for="optional_theory_url" class="block text-sm font-medium text-slate-700">OPTIONAL_THEORY_URL</label>
                        <input
                            type="text"
                            name="optional_theory_url"
                            id="optional_theory_url"
                            value="{{ old('optional_theory_url', $state['optional_theory_url']) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="https://... або none"
                        >
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Пошук серед сторінок теорії"
                                    data-search-input
                                >
                            </div>
                            <select
                                size="6"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                data-searchable-select
                                data-target-input="optional_theory_url"
                            >
                                @foreach ($theoryOptions as $theory)
                                    <option value="{{ $theory['url'] }}">{{ $theory['title'] }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500">Обирайте існуючу теорію або залишайте власне значення/none у полі вище.</p>
                        </div>
                    </div>

                    <div class="space-y-2" data-search-select>
                        <label for="base_seeder_class" class="block text-sm font-medium text-slate-700">BASE_SEEDER_CLASS</label>
                        <input
                            type="text"
                            name="base_seeder_class"
                            id="base_seeder_class"
                            value="{{ old('base_seeder_class', $state['base_seeder_class']) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="V2\\ConditionalsMixedPracticeV2Seeder"
                            required
                        >
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Пошук серед сидерів"
                                    data-search-input
                                >
                            </div>
                            <select
                                size="6"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                data-searchable-select
                                data-target-input="base_seeder_class"
                            >
                                @foreach ($seederClasses as $class)
                                    <option value="{{ $class }}">{{ $class }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500">Підтягуються всі наявні сидери з каталогу <code>database/seeders</code>, виберіть або залиште власний шлях.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-800">Новий сидер</h2>
                        <p class="text-sm text-slate-500">Шлях, назва класу та мова для пояснень/підказок.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">Крок 2</span>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2" data-search-select>
                        <label for="new_seeder_namespace_path" class="block text-sm font-medium text-slate-700">NEW_SEEDER_NAMESPACE_PATH</label>
                        <input
                            type="text"
                            name="new_seeder_namespace_path"
                            id="new_seeder_namespace_path"
                            value="{{ old('new_seeder_namespace_path', $state['new_seeder_namespace_path']) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="database/seeders/AI/Claude"
                            required
                        >
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Пошук каталогу сидерів"
                                    data-search-input
                                >
                            </div>
                            <select
                                size="6"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                data-searchable-select
                                data-target-input="new_seeder_namespace_path"
                            >
                                @foreach ($seederNamespaces as $namespace)
                                    <option value="{{ $namespace }}">{{ $namespace }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500">Пропонуються існуючі каталоги сидерів, можна також вказати свій шлях вручну.</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="new_seeder_class_name" class="block text-sm font-medium text-slate-700">NEW_SEEDER_CLASS_NAME</label>
                        <input
                            type="text"
                            name="new_seeder_class_name"
                            id="new_seeder_class_name"
                            value="{{ old('new_seeder_class_name', $state['new_seeder_class_name']) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="MixedConditionalsAIGeneratedSeeder"
                            required
                        >
                        <p class="text-xs text-slate-500">Формуйте назву у стилі наявних сидерів, наприклад <code>TopicAIGeneratedSeeder</code>.</p>
                    </div>

                    <div class="space-y-2" data-search-select>
                        <label for="hints_language" class="block text-sm font-medium text-slate-700">HINTS_LANGUAGE</label>
                        <input
                            type="text"
                            name="hints_language"
                            id="hints_language"
                            value="{{ old('hints_language', $state['hints_language']) }}"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="Ukrainian"
                            required
                        >
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Пошук мови"
                                    data-search-input
                                >
                            </div>
                            <select
                                size="6"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                data-searchable-select
                                data-target-input="hints_language"
                            >
                                @foreach ($languageOptions as $language)
                                    <option value="{{ $language }}">{{ $language }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500">Обирайте мову підказок або введіть власну у поле вище (за замовчуванням — українська).</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Рівні та кількість</h2>
                    <p class="text-sm text-slate-500">Вкажіть CEFR-рівні та кількість запитань для кожного.</p>
                </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">Крок 3</span>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-3">
                    <p class="text-sm font-medium text-slate-700">LEVELS_TO_USE</p>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($levelOptions as $level)
                            <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm">
                                <input type="checkbox" name="levels[]" value="{{ $level }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ in_array($level, old('levels', $state['levels'] ?? []), true) ? 'checked' : '' }}>
                                <span>{{ $level }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="space-y-1">
                        <label for="custom_levels" class="block text-sm font-medium text-slate-700">Додати власні рівні</label>
                        <textarea
                            name="custom_levels"
                            id="custom_levels"
                            rows="2"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="Напр.: Primary, Advanced"
                        >{{ old('custom_levels', $state['custom_levels']) }}</textarea>
                        <p class="text-xs text-slate-500">Через кому або новий рядок. Якщо нічого не вибрано, підставляються рівні A1–C2.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label for="questions_per_level" class="block text-sm font-medium text-slate-700">QUESTIONS_PER_LEVEL</label>
                    <input
                        type="number"
                        min="1"
                        max="200"
                        name="questions_per_level"
                        id="questions_per_level"
                        value="{{ old('questions_per_level', $state['questions_per_level']) }}"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        required
                    >
                    <p class="text-xs text-slate-500">Скільки унікальних запитань генеруємо на кожен вибраний рівень.</p>
                </div>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('prompt-generator.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Скинути</a>
            <button type="submit" class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-100">
                <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Згенерувати промт
            </button>
        </div>
    </form>

    @if ($generatedPrompt)
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800">Згенерований промт</h2>
                    <p class="text-sm text-slate-500">Скопіюйте текст та використайте його в ChatGPT, Gemini чи іншій моделі.</p>
                </div>
                <button
                    type="button"
                    onclick="copyPrompt(event)"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                >
                    <i class="fa-solid fa-copy mr-2"></i>Копіювати
                </button>
            </div>
            <pre id="generated-prompt" class="whitespace-pre-wrap text-sm leading-relaxed rounded-2xl border border-slate-200 bg-slate-900 text-slate-100 p-4 overflow-auto">{{ $generatedPrompt }}</pre>
            <div class="grid gap-4 md:grid-cols-2 text-sm text-slate-600">
                <div class="space-y-1">
                    <p class="font-semibold text-slate-800">Підставлені значення</p>
                    <ul class="space-y-1">
                        <li><span class="text-slate-500">TOPIC_NAME:</span> {{ $state['topic_name'] }}</li>
                        <li><span class="text-slate-500">OPTIONAL_THEORY_URL:</span> {{ $state['optional_theory_url'] }}</li>
                        <li><span class="text-slate-500">BASE_SEEDER_CLASS:</span> {{ $state['base_seeder_class'] }}</li>
                        <li><span class="text-slate-500">NEW_SEEDER_NAMESPACE_PATH:</span> {{ $state['new_seeder_namespace_path'] }}</li>
                        <li><span class="text-slate-500">NEW_SEEDER_CLASS_NAME:</span> {{ $state['new_seeder_class_name'] }}</li>
                    </ul>
                </div>
                <div class="space-y-1">
                    <p class="font-semibold text-slate-800">Кількість та мова</p>
                    <ul class="space-y-1">
                        <li><span class="text-slate-500">LEVELS_TO_USE:</span> {{ implode(', ', $state['levels']) }}</li>
                        <li><span class="text-slate-500">QUESTIONS_PER_LEVEL:</span> {{ $state['questions_per_level'] }}</li>
                        <li><span class="text-slate-500">HINTS_LANGUAGE:</span> {{ $state['hints_language'] }}</li>
                    </ul>
                </div>
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function copyPrompt(event) {
        const text = document.getElementById('generated-prompt').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const button = event.currentTarget;
            const original = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Скопійовано';
            setTimeout(() => (button.innerHTML = original), 1500);
        });
    }

    function initSearchableSelects() {
        document.querySelectorAll('[data-search-select]').forEach((container) => {
            const searchInput = container.querySelector('[data-search-input]');
            const select = container.querySelector('[data-searchable-select]');
            const targetInputId = select?.dataset.targetInput;
            const targetInput = targetInputId ? document.getElementById(targetInputId) : null;

            if (!select || !targetInput) {
                return;
            }

            const filterOptions = () => {
                const term = (searchInput?.value || '').toLowerCase();

                Array.from(select.options).forEach((option) => {
                    const haystack = `${option.text} ${option.value}`.toLowerCase();
                    option.hidden = term ? !haystack.includes(term) : false;
                });
            };

            searchInput?.addEventListener('input', filterOptions);

            select.addEventListener('change', () => {
                const value = select.value;

                if (value !== undefined) {
                    targetInput.value = value;
                }
            });

            filterOptions();
        });
    }

    document.addEventListener('DOMContentLoaded', initSearchableSelects);
</script>
@endpush
