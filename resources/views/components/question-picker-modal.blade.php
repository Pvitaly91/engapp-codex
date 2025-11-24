@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $seederGroups = collect($seederSourceGroups ?? [])
        ->filter(fn ($group) => filled($group['seeder'] ?? null))
        ->values();

    $seederGroupsByDate = collect($seederGroupsByDate ?? [])
        ->sortKeysDesc();

    $recentSeederClasses = collect($recentSeederClasses ?? [])
        ->filter()
        ->values();

    $recentSeederOrdinals = collect($recentSeederOrdinals ?? []);

    $seederExecutionTimes = collect($seederExecutionTimes ?? [])
        ->filter(fn ($time) => filled($time))
        ->mapWithKeys(fn ($time, $class) => [$class => Carbon::parse($time)]);

    $makeSeederLabel = function (string $class) use ($recentSeederClasses, $recentSeederOrdinals, $seederExecutionTimes) {
        $displayName = Str::after($class, 'Database\\Seeders\\');
        $isNew = $recentSeederClasses->contains($class);
        $ordinal = $recentSeederOrdinals->get($class);
        $timestamp = $seederExecutionTimes->get($class);

        $metaParts = collect();
        if ($isNew) {
            $metaParts->push('Новий' . (! is_null($ordinal) ? ' #' . $ordinal : ''));
        }
        if ($timestamp) {
            $metaParts->push($timestamp->format('Y-m-d H:i'));
        }

        $meta = $metaParts->isNotEmpty()
            ? ' (' . $metaParts->implode(' · ') . ')'
            : '';

        return $displayName . $meta;
    };
@endphp

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

<div x-show="open" x-transition.opacity class="fixed inset-0 z-40 flex items-start justify-center px-4 py-6 overflow-y-auto" style="display: none;">
    <div class="absolute inset-0 bg-black/50" @click="close()"></div>
    <div class="relative z-10 w-full max-w-5xl bg-white rounded-2xl shadow-xl p-4 sm:p-6 space-y-4 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-bold text-gray-800">Додати питання до тесту</h3>
            <button type="button" class="text-gray-500 hover:text-gray-700" @click="close()">&times;</button>
        </div>
        <div class="flex-1 min-h-0 flex flex-col gap-4 overflow-hidden">
            <div x-show="view === 'filter'" class="flex-1 min-h-0 flex flex-col gap-4 overflow-hidden">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Спочатку застосуйте фільтри або перейдіть до пошуку без них.</span>
                    <span x-text="selectionLabel()"></span>
                </div>

                <div class="flex-1 min-h-0 space-y-3 border border-gray-200 rounded-2xl p-3 overflow-y-auto"
                     x-data="{ openSections: { seeders: true, sources: true, levels: true, tags: false, aggregatedTags: false } }">
                    <div class="flex flex-wrap gap-2 text-xs text-gray-600 items-center justify-between sticky top-0 bg-white pb-2">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="font-semibold text-gray-800">Фільтри:</span>
                            <label class="inline-flex items-center gap-1">
                                <input type="checkbox" class="rounded text-blue-600" x-model="onlyAiV2" @change="markFiltersDirty()">
                                <span>Тільки AI (flag = 2)</span>
                            </label>
                            <span x-show="filtersDirty" class="text-red-500">Застосуйте фільтри перед пошуком</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" class="text-[11px] text-blue-600 font-semibold" @click="Object.keys(openSections).forEach(key => openSections[key] = false)">
                                Згорнути все
                            </button>
                            <button type="button" class="text-[11px] text-blue-600 font-semibold" @click="applyFilters()"
                                    :disabled="loading"
                                    :class="{ 'opacity-60 cursor-not-allowed': loading }">
                                Застосувати фільтри
                            </button>
                            <button type="button" class="text-[11px] text-gray-500 font-semibold" @click="resetFilters()"
                                    :disabled="loading"
                                    :class="{ 'opacity-60 cursor-not-allowed': loading }">
                                Скинути
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @if($seederGroups->isNotEmpty())
                            <div class="border border-gray-100 rounded-xl">
                                <button type="button" class="w-full flex items-center justify-between px-3 py-2 text-left" @click="openSections.seeders = !openSections.seeders">
                                    <span class="text-xs font-semibold text-gray-700">Клас сидера питання</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': openSections.seeders }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="openSections.seeders" x-collapse class="px-3 pb-3 space-y-3">
                                    @if($seederGroupsByDate->isNotEmpty())
                                        <div class="space-y-2">
                                            @foreach($seederGroupsByDate as $date => $classNames)
                                                @php
                                                    $groupsForDate = $seederGroups->whereIn('seeder', $classNames->all());
                                                @endphp
                                                @if($groupsForDate->isNotEmpty())
                                                    <div class="border border-gray-100 rounded-lg" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                                                        <button type="button" class="w-full flex items-center justify-between text-xs font-semibold text-gray-700 px-2 py-1" @click="open = !open">
                                                            <span>{{ $date }}</span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-500 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                        <div x-show="open" x-collapse class="px-2 pb-2 space-y-2">
                                                            @foreach($groupsForDate as $group)
                                                                <label class="flex items-center gap-2 px-2 py-1 rounded-xl border border-gray-200 text-[11px]">
                                                                    <input type="checkbox" class="rounded text-blue-600" value="{{ $group['seeder'] }}" x-model="filters.seederClasses" @change="markFiltersDirty()">
                                                                    <span class="text-gray-800">{{ $makeSeederLabel($group['seeder']) }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($seederGroups as $group)
                                                <label class="inline-flex items-center gap-2 px-2 py-1 rounded-xl border border-gray-200 text-xs">
                                                    <input type="checkbox" class="rounded text-blue-600" value="{{ $group['seeder'] }}" x-model="filters.seederClasses" @change="markFiltersDirty()">
                                                    <span class="text-gray-800">{{ $makeSeederLabel($group['seeder']) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if(!empty($sourcesByCategory))
                            <div class="border border-gray-100 rounded-xl">
                                <button type="button" class="w-full flex items-center justify-between px-3 py-2 text-left" @click="openSections.sources = !openSections.sources">
                                    <span class="text-xs font-semibold text-gray-700">Джерела по категоріях</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': openSections.sources }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="openSections.sources" x-collapse class="px-3 pb-3 space-y-2 max-h-48 overflow-auto pr-1">
                                    @foreach($sourcesByCategory as $group)
                                        <div class="space-y-1">
                                            <p class="text-[11px] text-gray-500 font-semibold">{{ $group['category']->name }}</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($group['sources'] as $source)
                                                    <label class="inline-flex items-center gap-2 px-2 py-1 rounded-xl border border-gray-200 text-xs">
                                                        <input type="checkbox" class="rounded text-blue-600" value="{{ $source->id }}" x-model="filters.sources" @change="markFiltersDirty()">
                                                        <span class="text-gray-800">{{ $source->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($levels))
                            <div class="border border-gray-100 rounded-xl">
                                <button type="button" class="w-full flex items-center justify-between px-3 py-2 text-left" @click="openSections.levels = !openSections.levels">
                                    <span class="text-xs font-semibold text-gray-700">Level</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': openSections.levels }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="openSections.levels" x-collapse class="px-3 pb-3">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($levels as $level)
                                            <label class="inline-flex items-center gap-2 px-2 py-1 rounded-xl border border-gray-200 text-xs">
                                                <input type="checkbox" class="rounded text-blue-600" value="{{ $level }}" x-model="filters.levels" @change="markFiltersDirty()">
                                                <span class="text-gray-800">{{ $level }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($tagsByCategory))
                            <div class="border border-gray-100 rounded-xl">
                                <button type="button" class="w-full flex items-center justify-between px-3 py-2 text-left" @click="openSections.tags = !openSections.tags">
                                    <span class="text-xs font-semibold text-gray-700">Tags</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': openSections.tags }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="openSections.tags" x-collapse class="px-3 pb-3 space-y-2 max-h-40 overflow-auto pr-1">
                                    @foreach($tagsByCategory as $category => $tags)
                                        <div class="space-y-1">
                                            <p class="text-[11px] text-gray-500 font-semibold">{{ $category }}</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($tags as $tag)
                                                    <label class="inline-flex items-center gap-2 px-2 py-1 rounded-xl border border-gray-200 text-xs">
                                                        <input type="checkbox" class="rounded text-blue-600" value="{{ $tag->name }}" x-model="filters.tags" @change="markFiltersDirty()">
                                                        <span class="text-gray-800">{{ $tag->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($aggregatedTagsByCategory))
                            <div class="border border-gray-100 rounded-xl">
                                <button type="button" class="w-full flex items-center justify-between px-3 py-2 text-left" @click="openSections.aggregatedTags = !openSections.aggregatedTags">
                                    <span class="text-xs font-semibold text-gray-700">Агреговані теги</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': openSections.aggregatedTags }" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 011.04 1.08l-4.25 3.845a.75.75 0 01-1.04 0l-4.25-3.845a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="openSections.aggregatedTags" x-collapse class="px-3 pb-3 space-y-2 max-h-40 overflow-auto pr-1">
                                    @foreach($aggregatedTagsByCategory as $category => $tags)
                                        <div class="space-y-1">
                                            <p class="text-[11px] text-gray-500 font-semibold">{{ $category }}</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($tags as $tag)
                                                    <label class="inline-flex items-center gap-2 px-2 py-1 rounded-xl border border-gray-200 text-xs">
                                                        <input type="checkbox" class="rounded text-blue-600" value="{{ $tag }}" x-model="filters.aggregatedTags" @change="markFiltersDirty()">
                                                        <span class="text-gray-800">{{ $tag }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-gray-100">
                    <div class="text-xs text-gray-500">Застосуйте фільтри, щоб відкрити пошук.</div>
                    <div class="flex flex-wrap gap-2 justify-end">
                        <button type="button" class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-2xl text-sm font-semibold" @click="searchWithoutFilters()" :disabled="loading">
                            Пошук без фільтрів
                        </button>
                        <button type="button" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl text-sm font-semibold disabled:opacity-60 disabled:cursor-not-allowed" @click="applyFilters()" :disabled="loading">
                            Застосувати і шукати
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="view === 'results'" x-cloak class="flex-1 min-h-0 flex flex-col gap-4 overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3">
                        <span>Пошук у відібраних питаннях.</span>
                        <span x-show="filtersDirty" class="text-red-500">Фільтри змінено — застосуйте, щоб оновити.</span>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 text-blue-600 font-semibold" @click="backToFilters()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.78 4.22a.75.75 0 010 1.06L8.06 10l4.72 4.72a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 011.06 0z" clip-rule="evenodd" />
                        </svg>
                        Повернутися до фільтрів
                    </button>
                </div>

                <div class="relative">
                    <input type="search" x-model.debounce.300ms="query" placeholder="Пошук за текстом, тегами, сидером, джерелом, ID або UUID"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                           autocomplete="off">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.9 14.32a6 6 0 111.414-1.414l3.387 3.387a1 1 0 01-1.414 1.414l-3.387-3.387zM14 9a5 5 0 11-10 0 5 5 0 0110 0z" clip-rule="evenodd" />
                    </svg>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Результати для застосованих фільтрів.</span>
                    <span x-text="selectionLabel()"></span>
                </div>

                <div class="border border-gray-200 rounded-2xl divide-y flex-1 min-h-0 overflow-auto" x-ref="results">
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
        </div>
        <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-2">
            <button type="button" class="inline-flex justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-2xl font-semibold" @click="close()">Скасувати</button>
            <button type="button" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-2xl font-semibold disabled:opacity-60 disabled:cursor-not-allowed" :disabled="selected.length === 0 || loading" @click="apply()">Додати вибрані</button>
        </div>
    </div>
</div>
