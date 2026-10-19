@if(($theoryCanonical ?? false) !== true)
    @include('courses.compatibility.theory.authored-practice-ui')
@else
@include('components.english-answer-variants')
@once
    <script src="{{ asset('js/authored-practice-ui.js') }}?v={{ filemtime(public_path('js/authored-practice-ui.js')) }}"></script>
@endonce
@once($practiceScope.'-authored-practice-wrapper')
    <script src="{{ asset($practiceScript) }}?v={{ filemtime(public_path($practiceScript)) }}"></script>
@endonce
@php
    $author = \App\Support\TheoryPracticePresentation::author($data['author_self_check'], $data['cases']);
    $linked = $data['linked_practice'];
@endphp
<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24" style="text-transform:none" data-{{ $practiceScope }}-practice-ui>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card" x-data="{{ $practiceFactory }}(@js(['cases' => $data['cases']]))">
        <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" fallback="⚡" />
        <noscript><p class="px-5 pt-5 text-sm text-muted-foreground">Автоматична перевірка потребує JavaScript. Завдання та авторські ключі доступні без нього.</p></noscript>
        <div class="theory-section-body p-5 space-y-6">
            @if(trim(strip_tags($author['intro'] ?? '')) !== '')
                <div class="text-base text-muted-foreground leading-relaxed" data-practice-instruction data-{{ $practiceScope }}-self-check-intro>{!! $author['intro'] !!}</div>
            @endif
            @foreach($data['cases'] as $i => $task)
                @php
                    $presentation = $author['presentation'][$i];
                @endphp
                <x-theory-practice-exercise tag="article" :accent="$presentation['accent']" :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-ui-case' => $task['source_index'], 'data-'.$practiceScope.'-ui-interaction' => $task['interaction']])">
                    <x-theory-practice-header :accent="$presentation['accent']" class="text-base leading-relaxed" data-practice-instruction :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-author-prompt' => $task['source_index']])">{!! $presentation['prompt_html'] !!}</x-theory-practice-header>
                    <div class="p-4 space-y-3">
                    @foreach($task['controls'] as $p => $control)
                        @php
                            $fieldId = $practiceScope.'-'.$block->id.'-'.$i.'-'.$p;
                            $controlPresentation = $presentation['controls'][$p];
                        @endphp
                        <x-theory-practice-control :accent="$controlPresentation['accent']" :marker="$controlPresentation['marker']" :technical="$controlPresentation['marker_technical'] ?? true" :wrap="true" :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-control-panel' => ''])">
                        <fieldset class="space-y-2" data-{{ $practiceScope }}-control="{{ $control['id'] }}" data-{{ $practiceScope }}-control-kind="{{ $control['kind'] }}" style="min-width:0;text-transform:none">
                            <legend class="text-sm font-semibold mb-2">{{ $controlPresentation['label'] }}</legend>
                            @if(isset($control['stimulus_en']))
                                <p lang="en" class="text-sm leading-relaxed">{{ $control['stimulus_en'] }}</p>
                            @endif
                            @if(isset($control['stimulus_uk']))
                                <p lang="uk" class="text-sm leading-relaxed text-muted-foreground">{{ $control['stimulus_uk'] }}</p>
                            @endif
                            @if(in_array($control['kind'], ['select','choice','multi'], true))
                                <div class="flex flex-wrap gap-2" @if($control['kind'] !== 'multi') role="radiogroup" @endif>
                                    @foreach($control['options'] as $option)
                                        @php
                                            $selected = 'selected('.$i.','.$p.','.\Illuminate\Support\Js::from($option['value']).')';
                                            $optionBindings = [
                                                'data-'.$practiceScope.'-answer' => $option['value'],
                                                'role' => $control['kind'] === 'multi' ? 'checkbox' : 'radio',
                                                ':aria-checked' => $selected,
                                                '@click' => 'setAnswer('.$i.','.$p.','.\Illuminate\Support\Js::from($option['value']).')',
                                            ];
                                            if ($control['kind'] !== 'multi') {
                                                $optionBindings[':tabindex'] = $selected.' || (!answers['.$i.']['.$p.'] && '.($loop->first ? 'true' : 'false').') ? 0 : -1';
                                                foreach (['right' => 1, 'down' => 1, 'left' => -1, 'up' => -1] as $direction => $step) {
                                                    $optionBindings['@keydown.arrow-'.$direction.'.prevent'] = 'cycleAnswer('.$i.','.$p.','.$step.',$event)';
                                                }
                                            }
                                        @endphp
                                        <x-theory-practice-option :accent="$controlPresentation['accent']" :uppercase="false" :wrap="true"
                                            :selected="$selected" :checked="'checked['.$i.']'" :correct="'partCorrect('.$i.','.$p.')'"
                                            :attributes="new \Illuminate\View\ComponentAttributeBag($optionBindings)">
                                            {{ $option['label'] }}
                                        </x-theory-practice-option>
                                    @endforeach
                                </div>
                            @else
                                @if(!empty($control['tokens']))
                                    @include('theory.partials.practice-nojs-tokens')
                                <x-theory-practice-token-bank caption="Банк токенів" :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-token-bank' => '', 'x-cloak' => ''])">
                                    <template x-for="token in banks[{{ $i }}][{{ $p }}]" :key="token.index">
                                        <x-theory-practice-token :used="'tokenUsed('.$i.','.$p.',token.index)'" :wrap="true"
                                            :attributes="new \Illuminate\View\ComponentAttributeBag(['@click' => 'appendToken('.$i.','.$p.',token.index)', 'data-'.$practiceScope.'-token' => '', 'x-text' => 'token.value'])" />
                                    </template>
                                </x-theory-practice-token-bank>
                                @endif
                                <label class="sr-only" for="{{ $fieldId }}">{{ $control['label'] }}</label>
                                <div class="relative min-w-[220px] flex-1 sm:max-w-md">
                                    <x-theory-practice-input :field="$controlPresentation['field'] ?? 'textarea'" :rows="$controlPresentation['rows'] ?? 1" :accent="$controlPresentation['accent']"
                                        :attributes="new \Illuminate\View\ComponentAttributeBag(['id' => $fieldId, 'autocomplete' => 'off', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'spellcheck' => 'false',
                                            'x-model' => 'answers['.$i.']['.$p.']', '@input' => 'edited('.$i.')', '@keydown.ctrl.enter.prevent' => 'check('.$i.')', 'data-'.$practiceScope.'-answer-input' => ''])" />
                                </div>
                            @endif
                            <x-theory-practice-feedback :correct="'partCorrect('.$i.','.$p.')'" role="status"
                                :attributes="new \Illuminate\View\ComponentAttributeBag(['x-cloak' => '', 'x-show' => 'checked['.$i.']', 'data-'.$practiceScope.'-part-feedback' => '',
                                    'x-text' => 'partCorrect('.$i.','.$p.') ? \'Правильно\' : \'Перевір цю частину відповіді\''])" />
                        </fieldset>
                        </x-theory-practice-control>
                    @endforeach
                    <x-theory-practice-actions :wrap="true" x-cloak>
                        <x-slot:status>
                            <x-theory-practice-feedback :correct="'isCorrect('.$i.')'" role="status"
                                :attributes="new \Illuminate\View\ComponentAttributeBag(['x-show' => 'checked['.$i.']', 'data-'.$practiceScope.'-case-feedback' => '',
                                    'x-text' => 'isCorrect('.$i.') ? \'Правильно\' : \'Не всі частини відповіді правильні\''])" />
                        </x-slot:status>
                        <x-theory-practice-action :accent="$presentation['accent']" :secondary="true"
                            :attributes="new \Illuminate\View\ComponentAttributeBag(['@click' => 'reset('.$i.')', 'data-'.$practiceScope.'-reset' => '',
                                'x-show' => 'checked['.$i.'] || answers['.$i.'].some(value => Array.isArray(value) ? value.length > 0 : String(value ?? \'\').trim() !== \'\')'])">Почати заново</x-theory-practice-action>
                        <x-theory-practice-action :accent="$presentation['accent']"
                            :attributes="new \Illuminate\View\ComponentAttributeBag(['@click' => 'check('.$i.')', 'data-'.$practiceScope.'-check' => ''])">Перевірити</x-theory-practice-action>
                    </x-theory-practice-actions>
                    <x-theory-practice-explanation :disclosure="true" :title="$author['title']"
                        :attributes="new \Illuminate\View\ComponentAttributeBag([':open' => 'checked['.$i.']', 'x-show' => 'checked['.$i.']', 'data-'.$practiceScope.'-ui-explanation' => '', 'style' => 'text-transform:none'])"
                        :summary-attributes="['x-show' => 'checked['.$i.']']"
                        :body-attributes="['data-'.$practiceScope.'-self-check-answers' => '', 'style' => 'text-transform:none']">
                        <ol class="list-none"><li data-{{ $practiceScope }}-self-check-answer="{{ $task['source_index'] }}">{!! $author['answers'][$task['source_index'] - 1] !!}</li></ol>
                    </x-theory-practice-explanation>
                    </div>
                </x-theory-practice-exercise>
            @endforeach
            <x-theory-practice-feedback tag="p" x-cloak x-show="checked.some(Boolean)" role="status" aria-live="polite"
                :attributes="new \Illuminate\View\ComponentAttributeBag(['data-'.$practiceScope.'-ui-score' => ''])">Результат: <span x-text="score">0</span> / {{ count($data['cases']) }}</x-theory-practice-feedback>
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid"
                :title="$linked['title'] ?? null" :intro="$linked['intro'] ?? null" :footer="$linked['footer'] ?? null" />
        </div>
    </div>
</section>
@endif
