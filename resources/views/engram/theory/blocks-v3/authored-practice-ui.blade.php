@include('components.english-answer-variants')
@once
    <script src="{{ asset('js/authored-practice-ui.js') }}?v={{ filemtime(public_path('js/authored-practice-ui.js')) }}"></script>
@endonce
@once($practiceScope.'-authored-practice-wrapper')
    <script src="{{ asset($practiceScript) }}?v={{ filemtime(public_path($practiceScript)) }}"></script>
@endonce
@php($author = $data['author_self_check'])
@php($linked = $data['linked_practice'])
<section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24" style="text-transform:none" data-{{ $practiceScope }}-practice-ui>
    <div class="theory-section-card rounded-2xl border border-border/60 bg-card" x-data="{{ $practiceFactory }}(@js(['cases' => $data['cases']]))">
        <x-theory-native-header :title="$data['title']" :level="$block->level ?? null" fallback="⚡" />
        <div class="theory-section-body p-5 space-y-6">
            <div class="text-sm text-muted-foreground leading-relaxed" data-{{ $practiceScope }}-self-check-intro>{!! $author['intro'] !!}</div>
            <noscript><p class="text-sm text-muted-foreground">Автоматична перевірка потребує JavaScript. Завдання та авторські ключі доступні без нього.</p></noscript>
            <p class="text-sm font-semibold" aria-live="polite" data-{{ $practiceScope }}-ui-score>Результат: <span x-text="score">0</span> / {{ count($data['cases']) }}</p>
            @foreach($data['cases'] as $i => $task)
                <article class="theory-exercise rounded-xl border border-border p-4 space-y-4" data-{{ $practiceScope }}-ui-case="{{ $task['source_index'] }}" data-{{ $practiceScope }}-ui-interaction="{{ $task['interaction'] }}">
                    <div class="text-sm leading-relaxed" data-{{ $practiceScope }}-author-prompt="{{ $task['source_index'] }}">{!! $author['prompts'][$task['source_index'] - 1] !!}</div>
                    @foreach($task['controls'] as $p => $control)
                        @php($fieldId = $practiceScope.'-'.$block->id.'-'.$i.'-'.$p)
                        <fieldset class="space-y-2" data-{{ $practiceScope }}-control="{{ $control['id'] }}" data-{{ $practiceScope }}-control-kind="{{ $control['kind'] }}" style="min-width:0;text-transform:none">
                            <legend class="text-sm font-semibold mb-2">{{ $control['label'] }}</legend>
                            @if(in_array($control['kind'], ['select','choice','multi'], true))
                                <div class="flex flex-wrap gap-2" @if($control['kind'] !== 'multi') role="radiogroup" @endif>
                                    @foreach($control['options'] as $option)
                                        <button type="button" data-{{ $practiceScope }}-answer="{{ $option['value'] }}" role="{{ $control['kind'] === 'multi' ? 'checkbox' : 'radio' }}"
                                            :aria-checked="selected({{ $i }},{{ $p }},@js($option['value']))"
                                            @click="setAnswer({{ $i }},{{ $p }},@js($option['value']))"
                                            @if($control['kind'] !== 'multi')
                                            :tabindex="selected({{ $i }},{{ $p }},@js($option['value'])) || (!answers[{{ $i }}][{{ $p }}] && {{ $loop->first ? 'true' : 'false' }}) ? 0 : -1"
                                            @keydown.arrow-right.prevent="cycleAnswer({{ $i }},{{ $p }},1,$event)"
                                            @keydown.arrow-down.prevent="cycleAnswer({{ $i }},{{ $p }},1,$event)"
                                            @keydown.arrow-left.prevent="cycleAnswer({{ $i }},{{ $p }},-1,$event)"
                                            @keydown.arrow-up.prevent="cycleAnswer({{ $i }},{{ $p }},-1,$event)"
                                            @endif
                                            class="rounded-lg border px-3 py-2 text-sm text-left leading-relaxed transition"
                                            style="text-transform:none;white-space:normal;overflow-wrap:anywhere;max-width:100%"
                                            :class="selected({{ $i }},{{ $p }},@js($option['value'])) ? (checked[{{ $i }}] ? (partCorrect({{ $i }},{{ $p }}) ? 'border-emerald-400 bg-emerald-50 text-emerald-900' : 'border-rose-400 bg-rose-50 text-rose-900') : 'border-blue-600 bg-blue-600 text-white') : 'border-border bg-card text-foreground'">
                                            {{ $option['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-wrap gap-2" data-{{ $practiceScope }}-token-bank>
                                    <template x-for="token in banks[{{ $i }}][{{ $p }}]" :key="token.index">
                                        <button type="button" @click="appendToken({{ $i }},{{ $p }},token.index)"
                                            :disabled="tokenUsed({{ $i }},{{ $p }},token.index)"
                                            class="rounded-lg border border-border px-3 py-2 text-sm text-left leading-relaxed"
                                            style="text-transform:none;white-space:normal;overflow-wrap:anywhere;max-width:100%"
                                            :style="tokenUsed({{ $i }},{{ $p }},token.index) ? 'opacity:0.4;text-transform:none' : 'opacity:1;text-transform:none'"
                                            data-{{ $practiceScope }}-token x-text="token.value"></button>
                                    </template>
                                </div>
                                <label class="sr-only" for="{{ $fieldId }}">{{ $control['label'] }}</label>
                                <textarea id="{{ $fieldId }}" rows="3" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                    x-model="answers[{{ $i }}][{{ $p }}]" @input="edited({{ $i }})" @keydown.ctrl.enter.prevent="check({{ $i }})"
                                    class="w-full rounded-lg border border-border bg-card p-3 text-sm leading-relaxed text-foreground"
                                    style="text-transform:none;resize:vertical" data-{{ $practiceScope }}-answer-input></textarea>
                            @endif
                            <p x-show="checked[{{ $i }}]" class="text-sm" role="status" data-{{ $practiceScope }}-part-feedback
                                :class="partCorrect({{ $i }},{{ $p }}) ? 'text-emerald-700' : 'text-rose-700'"
                                x-text="partCorrect({{ $i }},{{ $p }}) ? 'Правильно' : 'Перевір цю частину відповіді'"></p>
                        </fieldset>
                    @endforeach
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="check({{ $i }})" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white" data-{{ $practiceScope }}-check>Перевірити</button>
                        <button type="button" @click="reset({{ $i }})" class="rounded-lg border border-border px-4 py-2 text-sm" data-{{ $practiceScope }}-reset>Почати заново</button>
                    </div>
                    <p x-show="checked[{{ $i }}]" class="text-sm font-semibold" role="status" data-{{ $practiceScope }}-case-feedback
                        :class="isCorrect({{ $i }}) ? 'text-emerald-700' : 'text-rose-700'"
                        x-text="isCorrect({{ $i }}) ? 'Правильно' : 'Не всі частини відповіді правильні'"></p>
                    <details :open="checked[{{ $i }}]" data-{{ $practiceScope }}-ui-explanation style="text-transform:none">
                        <summary x-show="checked[{{ $i }}]" class="text-sm font-semibold cursor-pointer">{{ $author['title'] }}</summary>
                        <div class="theory-item rounded-xl p-4 bg-muted/50 mt-2 text-sm leading-relaxed" data-{{ $practiceScope }}-self-check-answers style="text-transform:none">
                            <ol class="list-none"><li data-{{ $practiceScope }}-self-check-answer="{{ $task['source_index'] }}">{!! $author['answers'][$task['source_index'] - 1] !!}</li></ol>
                        </div>
                    </details>
                </article>
            @endforeach
            <x-text-block-tags :block="$block" />
            <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid"
                :title="$linked['title'] ?? null" :intro="$linked['intro'] ?? null" :footer="$linked['footer'] ?? null" />
        </div>
    </div>
</section>
