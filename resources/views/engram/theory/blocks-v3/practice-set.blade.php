@if(isset($data['m41_v1']) && \App\Support\M41AuthoredTenseComparisonsPackage::presentation($block, $data) !== null)
    @include('engram.theory.blocks-v3.m41-practice-ui')
@elseif(isset($data['m41_v1']))
    <section id="block-{{ $block->id }}" class="theory-section-card theory-section-body" data-theory-render-fallback="m41-practice-identity">
        <x-theory-native-header :title="$data['title'] ?? ''" :level="$block->level ?? null" />
        @foreach($data['author_self_check']['prompts'] ?? [] as $i => $prompt)
            <article class="theory-item rounded-xl p-4 bg-muted/50">
                <div data-m41-fallback-prompt="{{ $i + 1 }}">{!! $prompt !!}</div>
                @foreach($data['cases'][$i]['controls'] ?? [] as $control)
                    <div class="space-y-2 my-4" data-m41-fallback-control="{{ $control['id'] }}">
                        <p class="text-sm font-semibold" data-m41-fallback-label>{{ $control['label'] }}</p>
                        @if(isset($control['stimulus_en']))
                            <p lang="en" class="text-sm leading-relaxed" data-m41-fallback-stimulus>{{ $control['stimulus_en'] }}</p>
                        @endif
                        @if(!empty($control['options']))
                            <ul class="space-y-1 text-sm">
                                @foreach($control['options'] as $option)
                                    <li data-m41-fallback-option>{{ $option['label'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if(!empty($control['tokens']))
                            <div class="flex flex-wrap gap-2" data-m41-fallback-token-bank>
                                @foreach($control['tokens'] as $token)
                                    <span class="rounded-lg border border-border px-3 py-2 text-sm" style="text-transform:none" data-m41-fallback-token>{{ $token }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
                <details><summary>{{ $data['author_self_check']['title'] ?? 'Авторський ключ' }}</summary>
                    <div data-m41-fallback-key="{{ $i + 1 }}">{!! $data['author_self_check']['answers'][$i] ?? '' !!}</div>
                </details>
            </article>
        @endforeach
    </section>
@elseif(isset($data['m40_v1']) && \App\Support\M40TensesB1Package::presentation($block, $data) !== null)
    @include('engram.theory.blocks-v3.m40-practice-ui')
@elseif(isset($data['m40_v1']))
    <section id="block-{{ $block->id }}" class="theory-section-card theory-section-body" data-theory-render-fallback="m40-practice-identity">
        <x-theory-native-header :title="$data['title'] ?? ''" :level="$block->level ?? null" />
        <div>{!! $data['author_self_check']['intro'] ?? '' !!}</div>
        @foreach($data['author_self_check']['prompts'] ?? [] as $i => $prompt)
            <article class="theory-item rounded-xl p-4 bg-muted/50">
                {!! $prompt !!}
                <details><summary>{{ $data['author_self_check']['title'] ?? 'Авторський ключ' }}</summary>
                    {!! $data['author_self_check']['answers'][$i] ?? '' !!}
                </details>
            </article>
        @endforeach
    </section>
@elseif(isset($data['m39_practice_ui_v1']) && \App\Support\M39PracticeUiPackage::presentation($block, $data) !== null)
    @include('engram.theory.blocks-v3.m39-practice-ui')
@elseif(isset($data['m39_practice_ui_v1']))
    <section id="block-{{ $block->id }}" class="theory-section-card theory-section-body" data-theory-render-fallback="m39-practice-ui-identity">
        <x-theory-native-header :title="$data['title'] ?? ''" :level="$block->level ?? null" />
        @foreach($data['author_self_check']['prompts'] ?? [] as $i => $prompt)
            <article class="theory-item rounded-xl p-4 bg-muted/50">
                {!! $prompt !!}
                <details><summary>{{ $data['author_self_check']['title'] ?? 'Авторський ключ' }}</summary>
                    {!! $data['author_self_check']['answers'][$i] ?? '' !!}
                </details>
            </article>
        @endforeach
    </section>
@else
@include('components.english-answer-variants')
@php
    $data = $data ?? json_decode($block->body ?? '[]', true) ?? [];
    $selects = $data['selects'] ?? [];
    $choices = $data['choices'] ?? [];
    $inputs = $data['inputs'] ?? [];
    $rephrase = $data['rephrase'] ?? [];
    $options = $data['options'] ?? [];
    $choiceOptions = $data['choice_options'] ?? ['a', 'b'];
    $linkedPractice = is_array($data['linked_practice'] ?? null) ? $data['linked_practice'] : [];
    $m30AuthorSelfCheck = (isset($data['m30_v1']) || isset($data['m31_v1']) || (isset($data['m32_v1']) || (isset($data['m33_v1']) || (isset($data['m34_v1']) || (isset($data['m35_v1']) || (isset($data['m36_v1']) || (isset($data['m37_v1']) || (isset($data['m38_v1']) || isset($data['m39_v1']))))))))) && is_array($data['author_self_check'] ?? null)
        ? $data['author_self_check'] : null;
    $authorSelfCheckStage = isset($data['m39_v1']) ? 'm39' : (isset($data['m38_v1']) ? 'm38' : (isset($data['m37_v1']) ? 'm37' : (isset($data['m36_v1']) ? 'm36' : (isset($data['m35_v1']) ? 'm35' : (isset($data['m34_v1']) ? 'm34' : (isset($data['m33_v1']) ? 'm33' : (isset($data['m32_v1']) ? 'm32' : (isset($data['m31_v1']) ? 'm31' : 'm30'))))))));
    $practiceSetId = 'practice-set-' . ($block->uuid ?? $block->id);
    $hasCheckableSelects = collect($selects)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
    $hasCheckableChoices = collect($choices)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
    $hasCheckableInputs = collect($inputs)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
    $hasCheckableRephrase = collect($rephrase)->contains(fn ($item) => !empty($item['answer']) || !empty($item['accepted']));
    $choiceExerciseNumber = !empty($selects) ? 2 : 1;
    $inputExerciseNumber = 1 + (!empty($selects) ? 1 : 0) + (!empty($choices) ? 1 : 0);
    $rephraseExerciseNumber = $inputExerciseNumber + (!empty($inputs) ? 1 : 0);
@endphp

<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24">
    <div
        x-data="theoryPracticeSet(@js([
            'selects' => $selects,
            'choices' => $choices,
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
            'wordSearchEndpoint' => route('api.words.search', ['lang' => app()->getLocale() === 'ua' ? 'uk' : app()->getLocale()]),
        ]))"
        class="theory-section-card rounded-2xl border border-border/60 bg-card overflow-visible"
    >
        @if(!empty($data['title']))
            <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" fallback="⚡" />
        @endif

        <div class="theory-section-body p-5 space-y-6">
            @if($m30AuthorSelfCheck !== null)
                <div class="text-base text-muted-foreground leading-relaxed" data-practice-instruction data-{{ $authorSelfCheckStage }}-self-check-intro>{!! $m30AuthorSelfCheck['intro'] !!}</div>
                <noscript>
                    <div class="theory-item rounded-xl p-4 bg-muted/50" data-{{ $authorSelfCheckStage }}-self-check-no-js>
                        <p class="text-sm text-muted-foreground mb-3">Інтерактивна перевірка потребує JavaScript. Завдання й авторські пояснення доступні нижче.</p>
                        <ol class="list-decimal pl-5 space-y-3 text-sm leading-relaxed">
                            @foreach($m30AuthorSelfCheck['prompts'] as $prompt)<li>{!! $prompt !!}</li>@endforeach
                        </ol>
                    </div>
                </noscript>
            @endif
            {{-- Select Exercise --}}
            @if(!empty($selects))
                <x-theory-practice-exercise accent="blue">
                    <div class="border-b border-blue-100 bg-blue-50/50 px-4 py-3">
                        <x-theory-practice-heading :title="$data['select_title'] ?? __('theory_blocks.practice.select_title')" :number="1" accent="blue"
                            :instruction="!empty($data['select_intro']) ? new \Illuminate\Support\HtmlString($data['select_intro']) : null" />
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($selects as $index => $item)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <div class="flex-1">
                                    @if($m30AuthorSelfCheck !== null && !empty($item['context']))
                                        <div class="text-base text-foreground/80 leading-relaxed mb-2" data-practice-instruction data-{{ $authorSelfCheckStage }}-author-prompt="{{ $item['source_index'] }}">{!! $item['context'] !!}</div>
                                    @endif
                                    <label class="block text-sm text-foreground/80 mb-1.5">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item['options'] ?? $options as $option)
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
                </x-theory-practice-exercise>
            @endif

            {{-- Choice Exercise --}}
            @if(!empty($choices))
                <x-theory-practice-exercise accent="amber">
                    <div class="border-b border-amber-100 bg-amber-50/50 px-4 py-3">
                        <x-theory-practice-heading :title="$data['choice_title'] ?? __('theory_blocks.practice.select_title')" :number="$choiceExerciseNumber" accent="amber"
                            :instruction="!empty($data['choice_intro']) ? new \Illuminate\Support\HtmlString($data['choice_intro']) : null" />
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($choices as $index => $item)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-white/60 rounded-lg p-3 border border-white">
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                <div class="flex-1">
                                    @if($m30AuthorSelfCheck !== null && !empty($item['context']))
                                        <div class="text-base text-foreground/80 leading-relaxed mb-2" data-practice-instruction data-{{ $authorSelfCheckStage }}-author-prompt="{{ $item['source_index'] }}">{!! $item['context'] !!}</div>
                                    @endif
                                    <label class="block text-sm text-foreground/80 mb-1.5">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    @if(!empty($item['prompt']))
                                        <p class="mb-2 text-base text-muted-foreground leading-relaxed" data-practice-instruction>{!! $item['prompt'] !!}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item['options'] ?? $choiceOptions as $option)
                                            <button
                                                type="button"
                                                @click="choiceAnswers[{{ $index }}] = @js($option)"
                                                class="min-w-12 rounded-xl border px-4 py-2 text-sm font-extrabold uppercase transition"
                                                :class="[
                                                    choiceAnswers[{{ $index }}] === @js($option)
                                                        ? 'border-amber-600 bg-amber-600 text-white shadow-sm'
                                                        : 'border-amber-200 bg-white text-amber-700 hover:border-amber-400 hover:bg-amber-50',
                                                    isChecked('choices') && hasAnswer('choices', {{ $index }}) && choiceAnswers[{{ $index }}] === @js($option)
                                                        ? (isCorrect('choices', {{ $index }}) ? 'ring-2 ring-emerald-300' : 'ring-2 ring-rose-300')
                                                        : ''
                                                ].join(' ')"
                                            >
                                                {{ $option }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <div x-show="isChecked('choices') && hasAnswer('choices', {{ $index }})" class="mt-2 text-xs font-semibold" :class="isCorrect('choices', {{ $index }}) ? 'text-emerald-700' : 'text-rose-700'">
                                        <span x-text="feedbackText('choices', {{ $index }})"></span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if($hasCheckableChoices)
                            <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between">
                                <p x-show="isChecked('choices')" class="text-xs font-semibold text-amber-700" x-text="scoreText('choices')"></p>
                                <div class="flex gap-2 sm:ml-auto">
                                    <button type="button" @click="resetGroup('choices')" x-show="isChecked('choices')" class="rounded-lg border border-amber-200 bg-white px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-50">
                                        {{ __('theory_blocks.practice.reset') }}
                                    </button>
                                    <button type="button" @click="check('choices')" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                                        {{ __('theory_blocks.practice.check') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-theory-practice-exercise>
            @endif

            {{-- Input Exercise --}}
            @if(!empty($inputs))
                <x-theory-practice-exercise accent="emerald" :visible-overflow="true">
                    <div class="border-b border-emerald-100 bg-emerald-50/50 px-4 py-3">
                        <x-theory-practice-heading :title="$data['input_title'] ?? __('theory_blocks.practice.input_title')" :number="$inputExerciseNumber" accent="emerald"
                            :instruction="!empty($data['input_intro']) ? new \Illuminate\Support\HtmlString($data['input_intro']) : null" />
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($inputs as $index => $item)
                            @php
                                $hasInputTokenBank = !empty($item['before'])
                                    && is_string($item['before'])
                                    && str_contains($item['before'], '/');
                            @endphp
                            <div class="relative flex flex-wrap items-center gap-2 text-sm text-foreground/80 bg-white/60 rounded-lg p-3 border border-white">
                                @if($m30AuthorSelfCheck !== null && !empty($item['context']))
                                    <div class="w-full text-base text-foreground/80 leading-relaxed" data-practice-instruction data-{{ $authorSelfCheckStage }}-author-prompt="{{ $item['source_index'] }}">{!! $item['context'] !!}</div>
                                @endif
                                <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold">
                                    {{ chr(97 + $index) }}
                                </span>
                                @unless($hasInputTokenBank)
                                    <span>{!! $item['before'] ?? '' !!}</span>
                                @endunless
                                @if(!empty($item['after']))
                                    <span class="font-semibold text-foreground">{!! $item['after'] !!}</span>
                                @endif
                                @if($hasInputTokenBank)
                                    <div class="w-full">
                                        <p class="mb-1.5 text-xs font-semibold text-muted-foreground flex items-center gap-2">
                                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                            {{ __('frontend.tests.compose.token_bank') }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <template x-for="token in inputTokenBank({{ $index }})" :key="token.id">
                                                <button
                                                    type="button"
                                                    @click="appendInputToken('inputs', {{ $index }}, token)"
                                                    :disabled="token.used"
                                                    :class="token.used
                                                        ? 'border-emerald-200 bg-white/60 text-muted-foreground/80 cursor-not-allowed'
                                                        : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50 hover:text-emerald-900'"
                                                    class="rounded-lg border px-3 py-1.5 text-sm font-semibold transition"
                                                >
                                                    <span x-text="token.value"></span>
                                                </button>
                                            </template>
                                            <p x-show="isInputTokenBankEmpty({{ $index }})" class="text-xs text-muted-foreground">
                                                {{ __('frontend.tests.compose.empty_pool') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                <div class="relative min-w-[220px] flex-1 sm:max-w-md" @click.outside="closeWordSuggestions('inputs', {{ $index }})">
                                    <input
                                        type="text"
                                        autocomplete="off"
                                        autocorrect="off"
                                        autocapitalize="none"
                                        spellcheck="false"
                                        x-model="inputAnswers[{{ $index }}]"
                                        :class="fieldClass('inputs', {{ $index }})"
                                        @input="syncInputTokenBank({{ $index }})"
                                        @unless($hasInputTokenBank)
                                            @input.debounce.150ms="searchWordSuggestions('inputs', {{ $index }}, $event)"
                                            @keydown.escape.stop.prevent="closeWordSuggestions('inputs', {{ $index }})"
                                            @keydown.enter="maybeSelectFirstWordSuggestion('inputs', {{ $index }}, $event)"
                                        @endunless
                                        data-word-suggestion-input="inputs-{{ $index }}"
                                        class="w-full rounded-xl border border-emerald-300 bg-white px-3.5 py-2 text-sm font-semibold text-foreground shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition-all"
                                        placeholder="..."
                                    />
                                    @unless($hasInputTokenBank)
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
                                    @endunless
                                </div>
                                @if(isset($data['m38_v1']) && !empty($item['m38_semantic_checks']))
                                    <div class="basis-full space-y-2 pl-7" data-m38-semantic-checks="{{ $index }}">
                                        @foreach($item['m38_semantic_checks'] as $partIndex => $part)
                                            <div class="flex flex-wrap gap-2" data-m38-semantic-part="{{ $partIndex }}">
                                                @foreach($part['options'] as $option)
                                                    <button type="button"
                                                        @click="setM38SemanticAnswer({{ $index }}, {{ $partIndex }}, @js($option))"
                                                        :class="m38SemanticAnswer({{ $index }}, {{ $partIndex }}) === @js($option)
                                                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                                                            : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'"
                                                        class="rounded-xl border px-3 py-2 text-left text-sm font-semibold transition">
                                                        {{ $option }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
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
                </x-theory-practice-exercise>
            @endif

            {{-- Rephrase Exercise --}}
            @if(!empty($rephrase))
                <x-theory-practice-exercise accent="purple" :visible-overflow="true">
                    <div class="border-b border-purple-100 bg-purple-50/50 px-4 py-3">
                        <x-theory-practice-heading :title="$data['rephrase_title'] ?? __('theory_blocks.practice.rephrase_title')" :number="$rephraseExerciseNumber" accent="purple"
                            :instruction="!empty($data['rephrase_intro']) ? new \Illuminate\Support\HtmlString($data['rephrase_intro']) : null" />
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
                                            autocomplete="off"
                                            autocorrect="off"
                                            autocapitalize="none"
                                            spellcheck="false"
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
                </x-theory-practice-exercise>
            @endif

            @if($m30AuthorSelfCheck !== null)
                <div class="theory-item rounded-xl p-4 bg-muted/50" data-{{ $authorSelfCheckStage }}-self-check-answers>
                    <h3 class="font-semibold text-foreground text-sm mb-3">{{ $m30AuthorSelfCheck['title'] }}</h3>
                    <ol class="list-decimal pl-5 space-y-3 text-sm leading-relaxed">
                        @foreach($m30AuthorSelfCheck['answers'] as $answer)<li>{!! $answer !!}</li>@endforeach
                    </ol>
                </div>
            @endif

            {{-- Block Tags --}}
            <x-text-block-tags :block="$block" />

            {{-- Practice Questions --}}
            <x-text-block-practice-questions
                :questions="$practiceQuestions ?? collect()"
                :blockUuid="$block->uuid"
                :title="$linkedPractice['title'] ?? null"
                :intro="$linkedPractice['intro'] ?? null"
                :footer="$linkedPractice['footer'] ?? null"
            />
        </div>
    </div>
</section>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('theoryPracticeSet', (config) => ({
                selects: config.selects || [],
                choices: config.choices || [],
                inputs: config.inputs || [],
                rephrase: config.rephrase || [],
                i18n: config.i18n || {},
                wordSearchEndpoint: config.wordSearchEndpoint || '',
                selectAnswers: {},
                choiceAnswers: {},
                inputAnswers: {},
                m38SemanticAnswers: {},
                rephraseAnswers: {},
                inputTokenBanks: {},
                checkedGroups: {},
                wordSuggestionResults: {},
                wordSuggestionOpen: {},
                wordSuggestionRanges: {},
                wordSuggestionRequestIds: {},

                init() {
                    this.initInputTokenBanks();
                },

                initInputTokenBanks() {
                    const banks = {};
                    const inputItems = Array.isArray(this.inputs) ? this.inputs : [];

                    inputItems.forEach((item, index) => {
                        // M35 author answers can contain a literal slash (on/before).
                        // Their finite explicit groups retain it; legacy delimiter parsing is unchanged.
                        const tokens = Array.isArray(item?.m39_token_groups)
                            ? item.m39_token_groups.map(value => String(value).trim()).filter(Boolean)
                            : Array.isArray(item?.m38_token_groups)
                            ? item.m38_token_groups.map(value => String(value).trim()).filter(Boolean)
                            : Array.isArray(item?.m37_token_groups)
                            ? item.m37_token_groups.map(value => String(value).trim()).filter(Boolean)
                            : Array.isArray(item?.m36_token_groups)
                            ? item.m36_token_groups.map(value => String(value).trim()).filter(Boolean)
                            : Array.isArray(item?.m35_token_groups)
                            ? item.m35_token_groups.map(value => String(value).trim()).filter(Boolean)
                            : this.extractInputTokens(item?.before);

                        if (tokens.length === 0) {
                            return;
                        }

                        banks[index] = tokens.map((value, tokenIndex) => ({
                            id: `input-${index}-${tokenIndex}-${tokens.length}`,
                            value,
                            used: false,
                        }));
                    });

                    this.inputTokenBanks = banks;
                },

                extractInputTokens(raw) {
                    if (!raw || typeof raw !== 'string') return [];

                    const tokens = String(raw)
                        .split('/')
                        .map((token) => String(token || '').trim())
                        .filter((token) => token !== '');

                    return tokens.length > 1 ? tokens : [];
                },

                inputTokenBank(index) {
                    return this.inputTokenBanks[index] || [];
                },

                isInputTokenBankEmpty(index) {
                    const bank = this.inputTokenBank(index);
                    return bank.length > 0 && bank.every((token) => token.used);
                },

                syncInputTokenBank(index) {
                    const bank = this.inputTokenBank(index);
                    if (!Array.isArray(bank) || bank.length === 0) return;

                    const words = this.inputTokenWords(this.inputAnswers[index]);
                    const usedIds = new Set();

                    for (let wordIndex = 0; wordIndex < words.length;) {
                        const matchingToken = bank.find((token) => {
                            if (usedIds.has(token.id)) return false;

                            const tokenWords = this.inputTokenWords(token.value);

                            return tokenWords.length > 0
                                && tokenWords.every((word, offset) => words[wordIndex + offset] === word);
                        });

                        if (!matchingToken) {
                            wordIndex += 1;
                            continue;
                        }

                        usedIds.add(matchingToken.id);
                        wordIndex += this.inputTokenWords(matchingToken.value).length;
                    }

                    this.inputTokenBanks = {
                        ...this.inputTokenBanks,
                        [index]: bank.map((token) => ({ ...token, used: usedIds.has(token.id) })),
                    };
                },

                inputTokenWords(value) {
                    return String(value || '')
                        .toLocaleLowerCase()
                        .replace(/[.,!?;:()]+/g, ' ')
                        .trim()
                        .split(/\s+/)
                        .filter(Boolean);
                },

                resetInputTokenBanks() {
                    const next = {};

                    Object.keys(this.inputTokenBanks).forEach((key) => {
                        next[key] = (this.inputTokenBanks[key] || []).map((token) => ({
                            ...token,
                            used: false,
                        }));
                    });

                    this.inputTokenBanks = next;
                },

                appendInputToken(group, index, token) {
                    if (group !== 'inputs') return;

                    this.closeWordSuggestions(group, index);

                    const bank = this.inputTokenBank(index);
                    if (!Array.isArray(bank) || bank.length === 0) return;

                    const tokenIndex = bank.findIndex((item) => item.id === token.id);

                    if (tokenIndex < 0 || bank[tokenIndex]?.used) return;

                    const nextBank = bank.map((item) => item.id === token.id ? { ...item, used: true } : item);
                    const value = String(token?.value || '').trim();
                    const current = String(this.inputAnswers[index] || '').trim();

                    this.inputTokenBanks = { ...this.inputTokenBanks, [index]: nextBank };
                    this.inputAnswers[index] = current ? `${current} ${value}` : value;

                    this.$nextTick(() => {
                        const input = document.querySelector(`[data-word-suggestion-input=\"inputs-${index}\"]`);

                        if (!input) return;

                        input.focus();
                    });
                },

                check(group) {
                    this.checkedGroups[group] = true;
                },

                isChecked(group) {
                    return this.checkedGroups[group] === true;
                },

                resetGroup(group) {
                    this.checkedGroups[group] = false;

                    if (group === 'selects') this.selectAnswers = {};
                    if (group === 'choices') this.choiceAnswers = {};
                    if (group === 'inputs') this.inputAnswers = {};
                    if (group === 'inputs') this.m38SemanticAnswers = {};
                    if (group === 'inputs') this.resetInputTokenBanks();
                    if (group === 'rephrase') this.rephraseAnswers = {};
                    this.closeAllWordSuggestions();
                },

                item(group, index) {
                    return (this[group] || [])[index] || {};
                },

                answerSource(group) {
                    return {
                        selects: this.selectAnswers,
                        choices: this.choiceAnswers,
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
                    if (group === 'inputs' && Array.isArray(this.item(group, index).m38_semantic_checks)
                        && !this.m38SemanticComplete(index)) return false;

                    // Opt-in editing task: ignore terminal punctuation, not a comma splice.
                    if (this.item(group, index).punctuation_sensitive === true) {
                        const punctuation = (value) => String(value || '').toLowerCase()
                            .replace(/[\u2018\u2019]/g, "'").trim().replace(/[.!?]+$/, '')
                            .replace(/\s+/g, ' ').replace(/\s*([,;])\s*/g, '$1 ');
                        return this.acceptedAnswers(group, index)
                            .some((accepted) => punctuation(accepted) === punctuation(this.userAnswer(group, index)));
                    }

                    return this.acceptedAnswers(group, index)
                        .some((accepted) => window.EnglishAnswerVariants.variants(accepted)
                            .some((variant) => this.normalize(variant) === answer));
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
                    if (group === 'inputs' && Array.isArray(this.item(group, index).m38_semantic_checks)
                        && !this.m38SemanticComplete(index)) {
                        return `${this.i18n.incorrect} ${this.i18n.answer}: ${String(this.item(group, index).author_explanation || '')
                            .replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()}`;
                    }
                    const itemFeedback = this.item(group, index).feedback;
                    const contextualFeedback = itemFeedback?.[String(this.userAnswer(group, index)).trim().toLowerCase()];
                    if (typeof contextualFeedback === 'string' && contextualFeedback.trim()) return contextualFeedback;
                    if (this.isCorrect(group, index)) return this.i18n.correct;

                    return `${this.i18n.incorrect} ${this.i18n.answer}: ${this.acceptedAnswers(group, index)[0]}`;
                },

                scoreText(group) {
                    const total = (this[group] || []).filter((_, index) => this.hasAnswer(group, index)).length;
                    const correct = (this[group] || []).filter((_, index) => this.hasAnswer(group, index) && this.isCorrect(group, index)).length;

                    return this.i18n.score.replace(':correct', correct).replace(':total', total);
                },

                // Finite M38 multi-part author cases. Other practice data has
                // no opt-in property and therefore retains its prior scoring.
                m38SemanticAnswer(index, part) {
                    return this.m38SemanticAnswers?.[`${index}-${part}`] || '';
                },

                setM38SemanticAnswer(index, part, value) {
                    this.m38SemanticAnswers = {...this.m38SemanticAnswers, [`${index}-${part}`]: String(value || '')};
                },

                m38SemanticComplete(index) {
                    const parts = this.item('inputs', index).m38_semantic_checks;
                    if (!Array.isArray(parts) || parts.length === 0) return true;
                    return parts.every((part, offset) => String(part.answer || '') !== ''
                        && this.m38SemanticAnswer(index, offset) === String(part.answer));
                },

                suggestionKey(group, index) {
                    return `${group}-${index}`;
                },

                wordSuggestions(group, index) {
                    return this.wordSuggestionResults[this.suggestionKey(group, index)] || [];
                },

                isWordSuggestionOpen(group, index) {
                    if (group === 'inputs' && this.inputTokenBank(index).length > 0) return false;

                    const key = this.suggestionKey(group, index);

                    return this.wordSuggestionOpen[key] === true && this.wordSuggestions(group, index).length > 0;
                },

                closeWordSuggestions(group, index) {
                    const key = this.suggestionKey(group, index);
                    const requestId = (this.wordSuggestionRequestIds[key] || 0) + 1;

                    this.wordSuggestionRequestIds = { ...this.wordSuggestionRequestIds, [key]: requestId };
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
                    if (group === 'inputs' && this.inputTokenBank(index).length > 0) {
                        this.closeWordSuggestions(group, index);
                        return;
                    }

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
@endif
