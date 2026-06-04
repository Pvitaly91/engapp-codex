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
        ]))"
        class="rounded-2xl border border-border/60 bg-card overflow-hidden"
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
                                    <select
                                        x-model="selectAnswers[{{ $index }}]"
                                        :class="fieldClass('selects', {{ $index }})"
                                        class="w-full sm:w-48 rounded-lg border-border bg-white px-3 py-1.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-100 transition-all"
                                    >
                                        <option value="">{{ __('theory_blocks.practice.select_placeholder') }}</option>
                                        @foreach($options as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
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
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/30 overflow-hidden">
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
                            <div class="flex flex-wrap items-center gap-2 text-sm text-foreground/80 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <span>{!! $item['before'] ?? '' !!}</span>
                                <input 
                                    type="text" 
                                    x-model="inputAnswers[{{ $index }}]"
                                    :class="fieldClass('inputs', {{ $index }})"
                                    class="w-28 rounded-lg border-border bg-white px-2.5 py-1 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-100 transition-all" 
                                    placeholder="..."
                                />
                                <span>{!! $item['after'] ?? '' !!}</span>
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
                <div class="rounded-xl border border-purple-100 bg-purple-50/30 overflow-hidden">
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
                                    <input 
                                        type="text" 
                                        x-model="rephraseAnswers[{{ $index }}]"
                                        :class="fieldClass('rephrase', {{ $index }})"
                                        class="w-full rounded-lg border-border bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-100 transition-all" 
                                        placeholder="{{ $item['placeholder'] ?? '' }}"
                                    />
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
                selectAnswers: {},
                inputAnswers: {},
                rephraseAnswers: {},
                checkedGroups: {},

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
                },

                item(group, index) {
                    return (this[group] || [])[index] || {};
                },

                userAnswer(group, index) {
                    const sources = {
                        selects: this.selectAnswers,
                        inputs: this.inputAnswers,
                        rephrase: this.rephraseAnswers,
                    };

                    return sources[group]?.[index] || '';
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
                        .trim()
                        .toLowerCase()
                        .replace(/[’‘]/g, "'")
                        .replace(/[“”]/g, '"')
                        .replace(/\s+/g, ' ')
                        .replace(/\s+([?.!,;:])/g, '$1');
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
            }));
        });
    </script>
@endonce
