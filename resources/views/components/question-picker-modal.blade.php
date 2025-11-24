<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="text-sm text-gray-500" id="question-count">Кількість питань: {{ $questionCount }}</div>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" @click="open = true"
                class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-2xl shadow-sm text-sm font-semibold transition">
            Додати питання
        </button>
        @if(!empty($showShuffle))
            <button type="button" id="shuffle-questions"
                    class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-2xl shadow-sm text-sm font-semibold transition">
                Перемішати питання
            </button>
        @endif
    </div>
</div>

<div x-show="open" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center px-4" style="display: none;">
    <div class="absolute inset-0 bg-black/50" @click="close()"></div>
    <div class="relative z-10 w-full max-w-5xl bg-white rounded-2xl shadow-xl p-4 sm:p-6 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-bold text-gray-800">Додати питання до тесту</h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" @click="close()">&times;</button>
        </div>
        <div class="flex flex-col gap-3">
            <div class="relative">
                <input type="search" x-model.debounce.300ms="query" placeholder="Пошук за текстом, тегами, сидером, джерелом, ID або UUID"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                       autocomplete="off">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.9 14.32a6 6 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 9a5 5 0 11-10 0 5 5 0 0110 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>Пошук у тексті питання, відповідях, тегах, сидерах, джерелах та варіантах.</span>
                <span x-text="selectionLabel()"></span>
            </div>
            <div class="border border-gray-200 rounded-2xl divide-y max-h-[60vh] overflow-auto" x-ref="results">
                <template x-if="loading">
                    <div class="p-4 text-sm text-gray-500">Завантаження...</div>
                </template>
                <template x-if="!loading && results.length === 0">
                    <div class="p-4 text-sm text-gray-500">Нічого не знайдено.</div>
                </template>
                <template x-for="item in results" :key="item.id + '-' + (item.uuid || '')">
                    <label class="flex items-start gap-3 p-4 hover:bg-gray-50 transition cursor-pointer">
                        <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded" :checked="isSelected(item)" @change="toggle(item)">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                <span class="font-semibold text-gray-700" x-html="highlightText('ID: ' + item.id)"></span>
                                <template x-if="item.uuid"><span class="text-gray-500" x-html="highlightText('UUID: ' + item.uuid)"></span></template>
                                <template x-if="item.seeder"><span class="px-2 py-0.5 rounded-full bg-gray-100" x-html="highlightText(item.seeder)"></span></template>
                                <template x-if="item.source"><span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700" x-html="highlightText(item.source)"></span></template>
                                <template x-if="item.level"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700" x-html="highlightText('Level: ' + item.level)"></span></template>
                                <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700" x-html="highlightText('Складність: ' + item.difficulty)"></span>
                            </div>
                            <p class="text-sm font-semibold text-gray-800" x-html="renderQuestionPreview(item)"></p>
                            <div class="flex flex-col gap-1 text-xs text-gray-600" x-show="item.answers && item.answers.length" x-cloak>
                                <span class="font-semibold text-gray-700">Відповіді:</span>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="answer in item.answers" :key="(answer.marker || '') + (answer.text || '')">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800" x-html="highlightText(answer.text || '')"></span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1 text-[11px] text-gray-600" x-show="item.options && item.options.length" x-cloak>
                                <span class="font-semibold text-gray-700">Варіанти:</span>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="option in item.options" :key="option">
                                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-800" x-html="highlightText(option)"></span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1" x-show="item.tags && item.tags.length" x-cloak>
                                <template x-for="tag in item.tags" :key="tag">
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-purple-50 text-purple-700" x-html="highlightText(tag)"></span>
                                </template>
                            </div>
                        </div>
                    </label>
                </template>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row sm:justify-end gap-2">
            <button type="button" class="inline-flex justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-2xl font-semibold" @click="close()">Скасувати</button>
            <button type="button" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-2xl font-semibold disabled:opacity-60 disabled:cursor-not-allowed" :disabled="selected.length === 0 || loading" @click="apply()">Додати вибрані</button>
        </div>
    </div>
</div>
