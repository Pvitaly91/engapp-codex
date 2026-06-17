@php
    $data = $data ?? json_decode($block->body ?? '[]', true) ?? [];
    $selects = $data['selects'] ?? [];
    $inputs = $data['inputs'] ?? [];
    $rephrase = $data['rephrase'] ?? [];
    $options = $data['options'] ?? [];
    $practiceSetId = 'practice-set-' . ($block->uuid ?? $block->id);
    $hasCheckableSelects = collect($selects)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
    $hasCheckableInputs = collect($inputs)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
    $hasCheckableRephrase = collect($rephrase)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
@endphp

<section id="block-{{ $block->id }}" class="scroll-mt-24">
    <div
        x-data="theoryPracticeSet(@js([
            'selects' => $selects,
            'inputs' => $inputs,
            'rephrase' => $rephrase,
            'i18n' => [
                'check' => __('theory_blocks.practice.check'),
                'reset' => __('theory_blocks.practice.reset'),
                'correct' => __('theory_blocks.practice.correct'),
                'incorrect' => __('theory_blocks.practice.incorrect'),
                'empty' => __('theory_blocks.practice.empty'),
                'score' => __('theory_blocks.practice.score'),
                'answer' => __('theory_blocks.practice.answer'),
            ],
            'wordSearchEndpoint' => localized_route('words.search'),
        ]))"
        class="rounded-2xl border border-border/60 bg-card overflow-visible"
    >
        @if(!empty($data['title']))
            <div class="border-b border-border/40 bg-gradient-to-r from-brand-50 to-indigo-50 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-3 text-lg font-bold text-foreground">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-600 text-white text-xs font-bold shadow-sm">
                            ⚡
                        </span>
                        {{ preg_replace('/^\d+\.\s*/', '', $data['title']) }}
                    </h2>
                    <x-text-block-level-badge :level="$block->level ?? null" />
                </div>
            </div>
        @endif

        <div class="p-5 space-y-6">
            {{-- Select Exercise --}}
            @if(!empty($selects))
                <div class="rounded-xl border border-blue-100 bg-blue-50/30 overflow-hidden">
                    <div class="border-b border-blue-100 bg-blue-50/50 px-4 py-3">
                        <h3 class="font-semibold text-foreground text-sm flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-blue-500 text-white text-[10px]">1</span>
                            {{ $data['select_title'] ?? __('theory_blocks.practice.select_title') }}
                        </h3>
                        @if(!empty($data['select_intro']))
                            <p class="text-xs text-muted-foreground mt-1">{!! $data['select_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($selects as $index => $item)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <div class="flex-1">
                                    <label class="block text-sm text-foreground/80 mb-1.5">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($options as $option)
                                            <button
                                                type="button"
                                                @click="selectAnswers[{{ $index }}] = @js($option)"
                                                class="min-w-12 rounded-xl border px-4 py-2 text-sm font-extrabold uppercase transition"
                                                :class="[
                                                    selectAnswers[{{ $index }}] === @js($option)
                                                        ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                                        : 'border-blue-200 bg-white text-blue-700 hover:border-blue-400 hover:bg-blue-50',
                                                    isChecked('selects') && hasAnswer('selects', {{ $index }}) && selectAnswers[{{ $index }}] === @js($option)
                                                        ? (isCorrect('selects', {{ $index }}) ? 'ring-2 ring-emerald-300' : 'ring-2 ring-rose-300')
                                                        : ''
                                                ].join(' ')"
                                            >
                                                {{ $option }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <div x-show="isChecked('selects') && hasAnswer('selects', {{ $index }})" class="mt-2 text-xs font-semibold" :class="isCorrect('selects', {{ $index }}) ? 'text-emerald-700' : 'text-rose-700'">
                                        <span x-text="feedbackText('selects', {{ $index }})"></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($hasCheckableSelects)
                            <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between">
                                <p x-show="isChecked('selects')" class="text-xs font-semibold text-blue-700" x-text="scoreText('selects')"></p>
                                <div class="flex gap-2 sm:ml-auto">
                                    <button type="button" @click="resetGroup('selects')" x-show="isChecked('selects')" class="rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                                        {{ __('theory_blocks.practice.reset') }}
                                    </button>
                                    <button type="button" @click="check('selects')" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                        {{ __('theory_blocks.practice.check') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Input Exercise --}}
            @if(!empty($inputs))
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/30 overflow-visible">
                    <div class="border-b border-emerald-100 bg-emerald-50/50 px-4 py-3">
                        <h3 class="font-semibold text-foreground text-sm flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-emerald-500 text-white text-[10px]">2</span>
                            {{ $data['input_title'] ?? __('theory_blocks.practice.input_title') }}
                        </h3>
                        @if(!empty($data['input_intro']))
                            <p class="text-xs text-muted-foreground mt-1">{!! $data['input_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($inputs as $index => $item)
                            <div class="relative flex flex-wrap items-center gap-2 text-sm text-foreground/80 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <span>{!! $item['before'] ?? '' !!}</span>
                                @if(!empty($item['after']))
                                    <span class="font-semibold text-foreground">{!! $item['after'] !!}</span>
                                @endif
                                <div class="relative min-w-[220px] flex-1 sm:max-w-md" @click.outside="closeWordSuggestions('inputs', {{ $index }})">
                                    <input
                                        type="text"
                                        x-model="inputAnswers[{{ $index }}]"
                                        :class="fieldClass('inputs', {{ $index }})"
                                        @input.debounce.150ms="searchWordSuggestions('inputs', {{ $index }}, $event)"
                                        @focus="searchWordSuggestions('inputs', {{ $index }}, $event)"
                                        @keydown.escape.stop.prevent="closeWordSuggestions('inputs', {{ $index }})"
                                        @keydown.enter="maybeSelectFirstWordSuggestion('inputs', {{ $index }}, $event)"
                                        data-word-suggestion-input="inputs-{{ $index }}"
                                        class="w-full rounded-xl border border-emerald-300 bg-white px-3.5 py-2 text-sm font-semibold text-foreground shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition-all"
                                        placeholder="..."
                                    />
                                    <div
                                        x-cloak
                                        x-show="isWordSuggestionOpen('inputs', {{ $index }})"
                                        x-transition.opacity.duration.100ms
                                        class="absolute left-0 top-full z-[9999] mt-1 w-72 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-border/70 bg-white shadow-2xl shadow-slate-900/15"
                                    >
                                        <template x-for="item in wordSuggestions('inputs', {{ $index }})" :key="item.word + '-' + (item.translation || '')">
                                            <button
                                                type="button"
                                                @mousedown.prevent="applyWordSuggestion('inputs', {{ $index }}, item)"
                                                class="flex w-full items-start justify-between gap-3 px-3 py-2 text-left text-sm transition hover:bg-brand-50 focus:bg-brand-50 focus:outline-none"
                                            >
                                                <span class="min-w-0">
                                                    <span class="block truncate font-semibold text-foreground" x-text="item.word"></span>
                                                    <span class="block truncate text-xs text-muted-foreground" x-text="item.translation || ''"></span>
                                                </span>
                                                <span class="shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700" x-text="item.translation_lang || ''"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <span x-show="isChecked('inputs') && hasAnswer('inputs', {{ $index }})" class="basis-full pl-7 text-xs font-semibold" :class="isCorrect('inputs', {{ $index }}) ? 'text-emerald-700' : 'text-rose-700'" x-text="feedbackText('inputs', {{ $index }})"></span>
                            </div>
                        @endforeach
                        @if($hasCheckableInputs)
                            <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between">
                                <p x-show="isChecked('inputs')" class="text-xs font-semibold text-emerald-700" x-text="scoreText('inputs')"></p>
                                <div class="flex gap-2 sm:ml-auto">
                                    <button type="button" @click="resetGroup('inputs')" x-show="isChecked('inputs')" class="rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                        {{ __('theory_blocks.practice.reset') }}
                                    </button>
                                    <button type="button" @click="check('inputs')" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        {{ __('theory_blocks.practice.check') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Rephrase Exercise --}}
            @if(!empty($rephrase))
                <div class="rounded-xl border border-purple-100 bg-purple-50/30 overflow-visible">
                    <div class="border-b border-purple-100 bg-purple-50/50 px-4 py-3">
                        <h3 class="font-semibold text-foreground text-sm flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-purple-500 text-white text-[10px]">3</span>
                            {{ $data['rephrase_title'] ?? __('theory_blocks.practice.rephrase_title') }}
                        </h3>
                        @if(!empty($data['rephrase_intro']))
                            <p class="text-xs text-muted-foreground mt-1">{!! $data['rephrase_intro'] !!}</p>
                        @endif
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($rephrase as $index => $item)
                            @if($index === 0 && !empty($item['example_original']))
                                {{-- Example --}}
                                <div class="rounded-lg bg-purple-100/50 border border-purple-200/50 p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 mb-1.5 block">
                                        {{ $item['example_label'] ?? __('theory_blocks.practice.example_label') }}
                                    </span>
                                    <div class="space-y-1 font-mono text-xs">
                                        <p class="text-foreground/60">{{ $item['example_original'] }}</p>
                                        <p class="text-emerald-600 flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                            {{ $item['example_target'] ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                {{-- Task --}}
                                <div class="space-y-1.5 bg-white/60 rounded-lg p-3 border border-white">
                                    <p class="text-sm text-foreground/80 font-mono">
                                        {{ $item['original'] ?? '' }}
                                    </p>
                                    <div class="relative" @click.outside="closeWordSuggestions('rephrase', {{ $index }})">
                                        <input
                                            type="text"
                                            x-model="rephraseAnswers[{{ $index }}]"
                                            :class="fieldClass('rephrase', {{ $index }})"
                                            @input.debounce.150ms="searchWordSuggestions('rephrase', {{ $index }}, $event)"
                                            @focus="searchWordSuggestions('rephrase', {{ $index }}, $event)"
                                            @keydown.escape.stop.prevent="closeWordSuggestions('rephrase', {{ $index }})"
                                            @keydown.enter="maybeSelectFirstWordSuggestion('rephrase', {{ $index }}, $event)"
                                            data-word-suggestion-input="rephrase-{{ $index }}"
                                            class="w-full rounded-lg border-border bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-100 transition-all"
                                            placeholder="{{ $item['placeholder'] ?? '' }}"
                                        />
                                        <div
                                            x-cloak
                                            x-show="isWordSuggestionOpen('rephrase', {{ $index }})"
                                            x-transition.opacity.duration.100ms
                                            class="absolute left-0 right-0 top-full z-[9999] mt-1 max-h-72 overflow-y-auto rounded-xl border border-border/70 bg-white shadow-2xl shadow-slate-900/15"
                                        >
                                            <template x-for="item in wordSuggestions('rephrase', {{ $index }})" :key="item.word + '-' + (item.translation || '')">
                                                <button
                                                    type="button"
                                                    @mousedown.prevent="applyWordSuggestion('rephrase', {{ $index }}, item)"
                                                    class="flex w-full items-start justify-between gap-3 px-3 py-2 text-left text-sm transition hover:bg-brand-50 focus:bg-brand-50 focus:outline-none"
                                                >
                                                    <span class="min-w-0">
                                                        <span class="block truncate font-semibold text-foreground" x-text="item.word"></span>
                                                        <span class="block truncate text-xs text-muted-foreground" x-text="item.translation || ''"></span>
                                                    </span>
                                                    <span class="shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700" x-text="item.translation_lang || ''"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <div x-show="isChecked('rephrase') && hasAnswer('rephrase', {{ $index }})" class="text-xs font-semibold" :class="isCorrect('rephrase', {{ $index }}) ? 'text-emerald-700' : 'text-rose-700'" x-text="feedbackText('rephrase', {{ $index }})"></div>
                                </div>
                            @endif
                        @endforeach
                        @if($hasCheckableRephrase)
                            <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between">
                                <p x-show="isChecked('rephrase')" class="text-xs font-semibold text-purple-700" x-text="scoreText('rephrase')"></p>
                                <div class="flex gap-2 sm:ml-auto">
                                    <button type="button" @click="resetGroup('rephrase')" x-show="isChecked('rephrase')" class="rounded-lg border border-purple-200 bg-white px-4 py-2 text-sm font-semibold text-purple-700 transition hover:bg-purple-50">
                                        {{ __('theory_blocks.practice.reset') }}
                                    </button>
                                    <button type="button" @click="check('rephrase')" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">
                                        {{ __('theory_blocks.practice.check') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Block Tags --}}
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
        </div>
    </div>
</section>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('theoryPracticeSet', (config) => ({
                selects: config.selects || [],
                inputs: config.inputs || [],
                rephrase: config.rephrase || [],
                i18n: config.i18n || {},
                wordSearchEndpoint: config.wordSearchEndpoint || '',
                selectAnswers: {},
                inputAnswers: {},
                rephraseAnswers: {},
                checkedGroups: {},
                wordSuggestionResults: {},
                wordSuggestionOpen: {},
                wordSuggestionRanges: {},
                wordSuggestionRequestIds: {},

                check(group) {
                    this.checkedGroups[group] = true;
                },

                isChecked(group) {
                    return this.checkedGroups[group] === true;
                },

                resetGroup(group) {
                    this.checkedGroups[group] = false;

                    if (group === 'selects') this.selectAnswers = {};
                    if (group === 'inputs') this.inputAnswers = {};
                    if (group === 'rephrase') this.rephraseAnswers = {};
                    this.closeAllWordSuggestions();
                },

                item(group, index) {
                    return (this[group] || [])[index] || {};
                },

                answerSource(group) {
                    return {
                        selects: this.selectAnswers,
                        inputs: this.inputAnswers,
                        rephrase: this.rephraseAnswers,
                    }[group] || {};
                },

                userAnswer(group, index) {
                    return this.answerSource(group)?.[index] || '';
                },

                acceptedAnswers(group, index) {
                    const item = this.item(group, index);
                    const accepted = item.accepted || item.answers || item.answer || [];
                    const values = Array.isArray(accepted) ? accepted : [accepted];

                    return values
                        .map((value) => String(value || '').trim())
                        .filter(Boolean);
                },

                hasAnswer(group, index) {
                    return this.acceptedAnswers(group, index).length > 0;
                },

                normalize(value) {
                    return String(value || '')
                        .normalize('NFKD')
                        .toLowerCase()
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[\u2018\u2019\u201b\u2032`´ʼ']/g, '')
                        .replace(/[^\p{L}\p{N}]+/gu, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                isEmpty(group, index) {
                    return this.normalize(this.userAnswer(group, index)) === '';
                },

                isCorrect(group, index) {
                    const answer = this.normalize(this.userAnswer(group, index));

                    if (!answer || !this.hasAnswer(group, index)) return false;

                    return this.acceptedAnswers(group, index)
                        .some((accepted) => this.normalize(accepted) === answer);
                },

                fieldClass(group, index) {
                    if (!this.isChecked(group) || !this.hasAnswer(group, index)) return '';

                    if (this.isCorrect(group, index)) {
                        return 'border-emerald-400 bg-emerald-50 text-emerald-900';
                    }

                    return 'border-rose-400 bg-rose-50 text-rose-900';
                },

                feedbackText(group, index) {
                    if (this.isEmpty(group, index)) return this.i18n.empty;
                    if (this.isCorrect(group, index)) return this.i18n.correct;

                    return `${this.i18n.incorrect} ${this.i18n.answer}: ${this.acceptedAnswers(group, index)[0]}`;
                },

                scoreText(group) {
                    const total = (this[group] || []).filter((_, index) => this.hasAnswer(group, index)).length;
                    const correct = (this[group] || []).filter((_, index) => this.hasAnswer(group, index) && this.isCorrect(group, index)).length;

                    return this.i18n.score.replace(':correct', correct).replace(':total', total);
                },

                suggestionKey(group, index) {
                    return `${group}-${index}`;
                },

                wordSuggestions(group, index) {
                    return this.wordSuggestionResults[this.suggestionKey(group, index)] || [];
                },

                isWordSuggestionOpen(group, index) {
                    const key = this.suggestionKey(group, index);

                    return this.wordSuggestionOpen[key] === true && this.wordSuggestions(group, index).length > 0;
                },

                closeWordSuggestions(group, index) {
                    const key = this.suggestionKey(group, index);
                    this.wordSuggestionOpen = { ...this.wordSuggestionOpen, [key]: false };
                },

                closeAllWordSuggestions() {
                    this.wordSuggestionOpen = {};
                },

                isWordCharacter(character) {
                    return /[\p{L}\p{N}'\u2018\u2019\u201b\u2032`´ʼ-]/u.test(character || '');
                },

                currentWordRange(input) {
                    const value = String(input?.value || '');
                    const cursor = typeof input?.selectionStart === 'number' ? input.selectionStart : value.length;
                    let start = cursor;
                    let end = cursor;

                    while (start > 0 && this.isWordCharacter(value[start - 1])) {
                        start--;
                    }

                    while (end < value.length && this.isWordCharacter(value[end])) {
                        end++;
                    }

                    return {
                        value,
                        start,
                        end,
                        query: value.slice(start, end),
                    };
                },

                cleanWordQuery(query) {
                    return String(query || '')
                        .trim()
                        .replace(/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/gu, '');
                },

                async searchWordSuggestions(group, index, event) {
                    if (!this.wordSearchEndpoint || !['inputs', 'rephrase'].includes(group)) return;

                    const key = this.suggestionKey(group, index);
                    const range = this.currentWordRange(event?.target);
                    const query = this.cleanWordQuery(range.query);

                    this.wordSuggestionRanges = { ...this.wordSuggestionRanges, [key]: range };

                    if (query.length < 2) {
                        this.wordSuggestionResults = { ...this.wordSuggestionResults, [key]: [] };
                        this.closeWordSuggestions(group, index);
                        return;
                    }

                    const requestId = (this.wordSuggestionRequestIds[key] || 0) + 1;
                    this.wordSuggestionRequestIds = { ...this.wordSuggestionRequestIds, [key]: requestId };

                    try {
                        const url = new URL(this.wordSearchEndpoint, window.location.origin);
                        url.searchParams.set('q', query);

                        const response = await fetch(url, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        if (!response.ok || this.wordSuggestionRequestIds[key] !== requestId) return;

                        const results = await response.json();

                        if (this.wordSuggestionRequestIds[key] !== requestId) return;

                        this.wordSuggestionResults = { ...this.wordSuggestionResults, [key]: Array.isArray(results) ? results.slice(0, 8) : [] };
                        this.wordSuggestionOpen = { ...this.wordSuggestionOpen, [key]: this.wordSuggestionResults[key].length > 0 };
                    } catch (error) {
                        console.error(error);
                        this.wordSuggestionResults = { ...this.wordSuggestionResults, [key]: [] };
                        this.closeWordSuggestions(group, index);
                    }
                },

                applyWordSuggestion(group, index, item) {
                    const key = this.suggestionKey(group, index);
                    const source = this.answerSource(group);
                    const word = String(item?.word || '').trim();
                    const fallbackValue = String(source[index] || '');
                    const range = this.wordSuggestionRanges[key] || {
                        value: fallbackValue,
                        start: 0,
                        end: fallbackValue.length,
                    };

                    if (!word) return;

                    const current = String(source[index] || range.value || '');
                    const start = Math.max(0, Math.min(range.start, current.length));
                    const end = Math.max(start, Math.min(range.end, current.length));
                    const next = `${current.slice(0, start)}${word}${current.slice(end)}`;
                    const caret = start + word.length;

                    source[index] = next;
                    this.closeWordSuggestions(group, index);

                    this.$nextTick(() => {
                        const input = document.querySelector(`[data-word-suggestion-input="${key}"]`);

                        if (!input) return;

                        input.focus();

                        if (typeof input.setSelectionRange === 'function') {
                            input.setSelectionRange(caret, caret);
                        }
                    });
                },

                maybeSelectFirstWordSuggestion(group, index, event) {
                    if (!this.isWordSuggestionOpen(group, index)) return;

                    const first = this.wordSuggestions(group, index)[0];

                    if (!first) return;

                    event.preventDefault();
                    this.applyWordSuggestion(group, index, first);
                },
            }));
        });
    </script>
@endonce
