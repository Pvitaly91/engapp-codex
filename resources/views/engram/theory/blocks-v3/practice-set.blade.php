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
    // The dispatcher supplies this metadata only after the exact UK owner/body
    // guard. Keep reference lessons, courses and unverified fallback unchanged.
    $nativePracticePresentation = ($theoryCanonical ?? false) === true
        && is_array($m42Design ?? null) && ($m42Design['component'] ?? null) === 'practice-set';
    // Sentence/explanation options are prose, unlike A/B or lexical form chips.
    // This is display-only: the original option still owns every JS binding.
    $optionPresentation = static fn ($option) => $nativePracticePresentation
        && is_string($option) && preg_match('/\s/u', $option) === 1
        && preg_match('/[.!?;:,—–\r\n]/u', $option) === 1 ? 'prose' : 'chip';
    $instructionPresentation = $nativePracticePresentation ? ' [&_em]:not-italic' : '';
    // A separate, hash-bound display projection never replaces answer values.
    $nativeDisplay = \App\Support\TheoryPracticePresentation::nativeDisplay(
        $block, $data, $m42Design ?? null, ($theoryCanonical ?? false) === true
    );
    $answerKeyConditions = [];
    foreach ($nativeDisplay['answer_groups'] ?? [] as $sourceIndex => $location) {
        $answerKeyConditions[$sourceIndex] = "isChecked('".$location['group']."') && !isEmpty('".$location['group']."', ".$location['index'].")";
    }
    $answerKeysVisibility = implode(' || ', array_map(static fn ($condition) => '('.$condition.')', $answerKeyConditions));
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
                <div class="text-base text-muted-foreground leading-relaxed{{ $instructionPresentation }}" data-practice-instruction data-{{ $authorSelfCheckStage }}-self-check-intro>{!! $m30AuthorSelfCheck['intro'] !!}</div>
                <noscript>
                    <div class="theory-item rounded-xl p-4 bg-muted/50" data-{{ $authorSelfCheckStage }}-self-check-no-js>
                        <p class="text-sm text-muted-foreground mb-3">Інтерактивна перевірка потребує JavaScript. Завдання й авторські пояснення доступні нижче.</p>
                        <ol class="list-decimal pl-5 space-y-3 text-sm leading-relaxed{{ $instructionPresentation }}">
                            @foreach($m30AuthorSelfCheck['prompts'] as $prompt)<li>{!! $prompt !!}</li>@endforeach
                        </ol>
                    </div>
                </noscript>
            @endif
            {{-- Select Exercise --}}
            @if(!empty($selects))
                <x-theory-practice-exercise accent="blue">
                    <x-theory-practice-header accent="blue" :class="trim($instructionPresentation)">
                        <x-theory-practice-heading :title="$data['select_title'] ?? __('theory_blocks.practice.select_title')" :number="1" accent="blue"
                            :instruction="!empty($data['select_intro']) ? new \Illuminate\Support\HtmlString($data['select_intro']) : null" />
                    </x-theory-practice-header>
                    <div class="p-4 space-y-3">
                        @foreach($selects as $index => $item)
                            @php
                                $displayOptions = $nativeDisplay['selects'][$index]['options'] ?? [];
                                $fullOptionsVisibility = "isChecked('selects') && !isEmpty('selects', ".$index.")";
                            @endphp
                            <x-theory-practice-control accent="blue" :marker="chr(97 + $index)" :align="$nativePracticePresentation && !empty($item['context']) ? 'start' : 'center'" :wrap="$nativePracticePresentation">
                                    @if($m30AuthorSelfCheck !== null && !empty($item['context']))
                                        <div class="text-base text-foreground/80 leading-relaxed mb-2{{ $instructionPresentation }}" data-practice-instruction data-{{ $authorSelfCheckStage }}-author-prompt="{{ $item['source_index'] }}">{!! $item['context'] !!}</div>
                                    @endif
                                    <label class="block text-sm text-foreground/80 mb-1.5{{ $instructionPresentation }}">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item['options'] ?? $options as $optionIndex => $option)
                                            @php
                                                $displayOption = $displayOptions[$optionIndex] ?? $option;
                                            @endphp
                                            <x-theory-practice-option accent="blue" :presentation="$optionPresentation($displayOption)"
                                                :selected="'selectAnswers['.$index.'] === '.\Illuminate\Support\Js::from($option)"
                                                :checked="'isChecked(\'selects\') && hasAnswer(\'selects\', '.$index.')'"
                                                :correct="'isCorrect(\'selects\', '.$index.')'"
                                                :attributes="new \Illuminate\View\ComponentAttributeBag(['@click' => 'selectAnswers['.$index.'] = '.\Illuminate\Support\Js::from($option)])">
                                                {{ $displayOption }}
                                            </x-theory-practice-option>
                                        @endforeach
                                    </div>
                                    <x-theory-practice-feedback position="below" :correct="'isCorrect(\'selects\', '.$index.')'" x-show="isChecked('selects') && hasAnswer('selects', {{ $index }})">
                                        <span x-text="feedbackText('selects', {{ $index }})"></span>
                                    </x-theory-practice-feedback>
                                    @if($displayOptions !== [])
                                        {{-- Closed without JS; after Check, every original alternative remains available. --}}
                                        <x-theory-practice-explanation :disclosure="true" title="Повні варіанти й пояснення"
                                            :attributes="new \Illuminate\View\ComponentAttributeBag(['x-show' => $fullOptionsVisibility, 'class' => 'mt-3'.$instructionPresentation])">
                                            <ol class="list-decimal pl-5 space-y-3">
                                                @foreach($item['options'] ?? $options as $originalOption)
                                                    <li>{{ $originalOption }}</li>
                                                @endforeach
                                            </ol>
                                        </x-theory-practice-explanation>
                                    @endif
                            </x-theory-practice-control>
                        @endforeach
                        @if($hasCheckableSelects)
                            <x-theory-practice-actions>
                                <x-slot:status>
                                    <x-theory-practice-feedback tag="p" accent="blue" x-show="isChecked('selects')" x-text="scoreText('selects')" />
                                </x-slot:status>
                                <x-theory-practice-action accent="blue" :secondary="true" @click="resetGroup('selects')" x-show="isChecked('selects')">
                                    {{ __('theory_blocks.practice.reset') }}
                                </x-theory-practice-action>
                                <x-theory-practice-action accent="blue" @click="check('selects')">
                                    {{ __('theory_blocks.practice.check') }}
                                </x-theory-practice-action>
                            </x-theory-practice-actions>
                        @endif
                    </div>
                </x-theory-practice-exercise>
            @endif

            {{-- Choice Exercise --}}
            @if(!empty($choices))
                <x-theory-practice-exercise accent="amber">
                    <x-theory-practice-header accent="amber" :class="trim($instructionPresentation)">
                        <x-theory-practice-heading :title="$data['choice_title'] ?? __('theory_blocks.practice.select_title')" :number="$choiceExerciseNumber" accent="amber"
                            :instruction="!empty($data['choice_intro']) ? new \Illuminate\Support\HtmlString($data['choice_intro']) : null" />
                    </x-theory-practice-header>
                    <div class="p-4 space-y-3">
                        @foreach($choices as $index => $item)
                            @php
                                $displayPrompt = $nativeDisplay['choices'][$index]['prompt_html'] ?? null;
                                $fullPromptVisibility = "isChecked('choices') && !isEmpty('choices', ".$index.")";
                            @endphp
                            <x-theory-practice-control accent="amber" :marker="chr(97 + $index)" :align="$nativePracticePresentation && !empty($item['context']) ? 'start' : 'center'" :wrap="$nativePracticePresentation">
                                    @if($m30AuthorSelfCheck !== null && !empty($item['context']))
                                        <div class="text-base text-foreground/80 leading-relaxed mb-2{{ $instructionPresentation }}" data-practice-instruction data-{{ $authorSelfCheckStage }}-author-prompt="{{ $item['source_index'] }}">{!! $item['context'] !!}</div>
                                    @endif
                                    <label class="block text-sm text-foreground/80 mb-1.5{{ $instructionPresentation }}">
                                        {!! $item['label'] ?? '' !!}
                                    </label>
                                    @if(!empty($item['prompt']))
                                        <p class="mb-2 text-base text-muted-foreground leading-relaxed{{ $instructionPresentation }}" data-practice-instruction>{!! $displayPrompt ?? $item['prompt'] !!}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($item['options'] ?? $choiceOptions as $option)
                                            <x-theory-practice-option accent="amber" :presentation="$optionPresentation($option)"
                                                :selected="'choiceAnswers['.$index.'] === '.\Illuminate\Support\Js::from($option)"
                                                :checked="'isChecked(\'choices\') && hasAnswer(\'choices\', '.$index.')'"
                                                :correct="'isCorrect(\'choices\', '.$index.')'"
                                                :attributes="new \Illuminate\View\ComponentAttributeBag(['@click' => 'choiceAnswers['.$index.'] = '.\Illuminate\Support\Js::from($option)])">
                                                {{ $option }}
                                            </x-theory-practice-option>
                                        @endforeach
                                    </div>
                                    <x-theory-practice-feedback position="below" :correct="'isCorrect(\'choices\', '.$index.')'" x-show="isChecked('choices') && hasAnswer('choices', {{ $index }})">
                                        <span x-text="feedbackText('choices', {{ $index }})"></span>
                                    </x-theory-practice-feedback>
                                    @if($displayPrompt !== null)
                                        <x-theory-practice-explanation :disclosure="true" title="Повні варіанти й пояснення"
                                            :attributes="new \Illuminate\View\ComponentAttributeBag(['x-show' => $fullPromptVisibility, 'class' => 'mt-3'.$instructionPresentation])">
                                            <div>{!! $item['prompt'] !!}</div>
                                        </x-theory-practice-explanation>
                                    @endif
                            </x-theory-practice-control>
                        @endforeach
                        @if($hasCheckableChoices)
                            <x-theory-practice-actions>
                                <x-slot:status>
                                    <x-theory-practice-feedback tag="p" accent="amber" x-show="isChecked('choices')" x-text="scoreText('choices')" />
                                </x-slot:status>
                                <x-theory-practice-action accent="amber" :secondary="true" @click="resetGroup('choices')" x-show="isChecked('choices')">
                                    {{ __('theory_blocks.practice.reset') }}
                                </x-theory-practice-action>
                                <x-theory-practice-action accent="amber" @click="check('choices')">
                                    {{ __('theory_blocks.practice.check') }}
                                </x-theory-practice-action>
                            </x-theory-practice-actions>
                        @endif
                    </div>
                </x-theory-practice-exercise>
            @endif

            {{-- Input Exercise --}}
            @if(!empty($inputs))
                <x-theory-practice-exercise accent="emerald" :visible-overflow="true">
                    <x-theory-practice-header accent="emerald" :class="trim($instructionPresentation)">
                        <x-theory-practice-heading :title="$data['input_title'] ?? __('theory_blocks.practice.input_title')" :number="$inputExerciseNumber" accent="emerald"
                            :instruction="!empty($data['input_intro']) ? new \Illuminate\Support\HtmlString($data['input_intro']) : null" />
                    </x-theory-practice-header>
                    <div class="p-4 space-y-3">
                        @foreach($inputs as $index => $item)
                            @php
                                $hasInputTokenBank = !empty($item['before'])
                                    && is_string($item['before'])
                                    && str_contains($item['before'], '/');
                            @endphp
                            <x-theory-practice-control layout="inline" accent="emerald" :marker="chr(97 + $index)">
                                <x-slot:before>
                                @if($m30AuthorSelfCheck !== null && !empty($item['context']))
                                    <div class="w-full text-base text-foreground/80 leading-relaxed{{ $instructionPresentation }}" data-practice-instruction data-{{ $authorSelfCheckStage }}-author-prompt="{{ $item['source_index'] }}">{!! $item['context'] !!}</div>
                                @endif
                                </x-slot:before>
                                @unless($hasInputTokenBank)
                                    <span @if($nativePracticePresentation) class="[&_em]:not-italic" @endif>{!! $item['before'] ?? '' !!}</span>
                                @endunless
                                @if(!empty($item['after']))
                                    <span class="font-semibold text-foreground{{ $instructionPresentation }}">{!! $item['after'] !!}</span>
                                @endif
                                @if($hasInputTokenBank)
                                    <x-theory-practice-token-bank>
                                        <template x-for="token in inputTokenBank({{ $index }})" :key="token.id">
                                            <x-theory-practice-token used="token.used" @click="appendInputToken('inputs', {{ $index }}, token)">
                                                <span x-text="token.value"></span>
                                            </x-theory-practice-token>
                                        </template>
                                        <p x-show="isInputTokenBankEmpty({{ $index }})" class="text-xs text-muted-foreground">
                                            {{ __('frontend.tests.compose.empty_pool') }}
                                        </p>
                                    </x-theory-practice-token-bank>
                                @endif
                                <div class="relative min-w-[220px] flex-1 sm:max-w-md" @click.outside="closeWordSuggestions('inputs', {{ $index }})">
                                    @php
                                        $inputBindings = [
                                            'autocomplete' => 'off', 'autocorrect' => 'off', 'autocapitalize' => 'none', 'spellcheck' => 'false',
                                            'x-model' => 'inputAnswers['.$index.']',
                                            ':class' => "fieldClass('inputs', ".$index.")",
                                            '@input' => 'syncInputTokenBank('.$index.')',
                                            'data-word-suggestion-input' => 'inputs-'.$index, 'placeholder' => '...',
                                        ];
                                        if (!$hasInputTokenBank) {
                                            $inputBindings['@input.debounce.150ms'] = "searchWordSuggestions('inputs', ".$index.', $event)';
                                            $inputBindings['@keydown.escape.stop.prevent'] = "closeWordSuggestions('inputs', ".$index.")";
                                            $inputBindings['@keydown.enter'] = "maybeSelectFirstWordSuggestion('inputs', ".$index.', $event)';
                                        }
                                    @endphp
                                    <x-theory-practice-input :attributes="new \Illuminate\View\ComponentAttributeBag($inputBindings)" />
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
                                                    <x-theory-practice-option accent="emerald" :compact="true" :uppercase="false"
                                                        :selected="'m38SemanticAnswer('.$index.', '.$partIndex.') === '.\Illuminate\Support\Js::from($option)"
                                                        :attributes="new \Illuminate\View\ComponentAttributeBag(['@click' => 'setM38SemanticAnswer('.$index.', '.$partIndex.', '.\Illuminate\Support\Js::from($option).')'])">
                                                        {{ $option }}
                                                    </x-theory-practice-option>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <x-theory-practice-feedback tag="span" position="inline" :correct="'isCorrect(\'inputs\', '.$index.')'"
                                    x-show="isChecked('inputs') && hasAnswer('inputs', {{ $index }})" x-text="feedbackText('inputs', {{ $index }})" />
                            </x-theory-practice-control>
                        @endforeach
                        @if($hasCheckableInputs)
                            <x-theory-practice-actions>
                                <x-slot:status>
                                    <x-theory-practice-feedback tag="p" accent="emerald" x-show="isChecked('inputs')" x-text="scoreText('inputs')" />
                                </x-slot:status>
                                <x-theory-practice-action accent="emerald" :secondary="true" @click="resetGroup('inputs')" x-show="isChecked('inputs')">
                                    {{ __('theory_blocks.practice.reset') }}
                                </x-theory-practice-action>
                                <x-theory-practice-action accent="emerald" @click="check('inputs')">
                                    {{ __('theory_blocks.practice.check') }}
                                </x-theory-practice-action>
                            </x-theory-practice-actions>
                        @endif
                    </div>
                </x-theory-practice-exercise>
            @endif

            {{-- Rephrase Exercise --}}
            @if(!empty($rephrase))
                <x-theory-practice-exercise accent="purple" :visible-overflow="true">
                    <x-theory-practice-header accent="purple">
                        <x-theory-practice-heading :title="$data['rephrase_title'] ?? __('theory_blocks.practice.rephrase_title')" :number="$rephraseExerciseNumber" accent="purple"
                            :instruction="!empty($data['rephrase_intro']) ? new \Illuminate\Support\HtmlString($data['rephrase_intro']) : null" />
                    </x-theory-practice-header>
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
                                <x-theory-practice-control accent="purple" layout="rephrase">
                                    <p class="text-sm text-foreground/80 font-mono">
                                        {{ $item['original'] ?? '' }}
                                    </p>
                                    <div class="relative" @click.outside="closeWordSuggestions('rephrase', {{ $index }})">
                                        @php
                                            $rephraseBindings = [
                                                'autocomplete' => 'off', 'autocorrect' => 'off', 'autocapitalize' => 'none', 'spellcheck' => 'false',
                                                'x-model' => 'rephraseAnswers['.$index.']',
                                                ':class' => "fieldClass('rephrase', ".$index.")",
                                                '@input.debounce.150ms' => "searchWordSuggestions('rephrase', ".$index.', $event)',
                                                '@focus' => "searchWordSuggestions('rephrase', ".$index.', $event)',
                                                '@keydown.escape.stop.prevent' => "closeWordSuggestions('rephrase', ".$index.")",
                                                '@keydown.enter' => "maybeSelectFirstWordSuggestion('rephrase', ".$index.', $event)',
                                                'data-word-suggestion-input' => 'rephrase-'.$index,
                                                'placeholder' => $item['placeholder'] ?? '',
                                            ];
                                        @endphp
                                        <x-theory-practice-input role="rephrase" :attributes="new \Illuminate\View\ComponentAttributeBag($rephraseBindings)" />
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
                                    <x-theory-practice-feedback x-show="isChecked('rephrase') && hasAnswer('rephrase', {{ $index }})" :correct="'isCorrect(\'rephrase\', '.$index.')'" x-text="feedbackText('rephrase', {{ $index }})" />
                                </x-theory-practice-control>
                            @endif
                        @endforeach
                        @if($hasCheckableRephrase)
                            <x-theory-practice-actions>
                                <x-slot:status>
                                    <x-theory-practice-feedback tag="p" accent="purple" x-show="isChecked('rephrase')" x-text="scoreText('rephrase')" />
                                </x-slot:status>
                                <x-theory-practice-action accent="purple" :secondary="true" @click="resetGroup('rephrase')" x-show="isChecked('rephrase')">
                                    {{ __('theory_blocks.practice.reset') }}
                                </x-theory-practice-action>
                                <x-theory-practice-action accent="purple" @click="check('rephrase')">
                                    {{ __('theory_blocks.practice.check') }}
                                </x-theory-practice-action>
                            </x-theory-practice-actions>
                        @endif
                    </div>
                </x-theory-practice-exercise>
            @endif

            @if($m30AuthorSelfCheck !== null)
                @if($nativeDisplay !== null && $answerKeyConditions !== [])
                    <x-theory-practice-explanation :title="$m30AuthorSelfCheck['title']"
                        :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$authorSelfCheckStage.'-self-check-answers' => '', 'x-cloak' => '', 'x-show' => $answerKeysVisibility])">
                        <ol class="list-decimal pl-5 space-y-3 text-sm leading-relaxed{{ $instructionPresentation }}">
                            @foreach($m30AuthorSelfCheck['answers'] as $answerIndex => $answer)
                                <li value="{{ $answerIndex + 1 }}" x-show="{{ $answerKeyConditions[$answerIndex + 1] }}">{!! $answer !!}</li>
                            @endforeach
                        </ol>
                    </x-theory-practice-explanation>
                    <noscript>
                        <x-theory-practice-explanation :disclosure="true" :title="$m30AuthorSelfCheck['title']">
                            <ol class="list-decimal pl-5 space-y-3 text-sm leading-relaxed{{ $instructionPresentation }}">
                                @foreach($m30AuthorSelfCheck['answers'] as $answer)<li>{!! $answer !!}</li>@endforeach
                            </ol>
                        </x-theory-practice-explanation>
                    </noscript>
                @else
                    <x-theory-practice-explanation :title="$m30AuthorSelfCheck['title']" :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$authorSelfCheckStage.'-self-check-answers' => ''])">
                        <ol class="list-decimal pl-5 space-y-3 text-sm leading-relaxed">
                            @foreach($m30AuthorSelfCheck['answers'] as $answer)<li>{!! $answer !!}</li>@endforeach
                        </ol>
                    </x-theory-practice-explanation>
                @endif
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
