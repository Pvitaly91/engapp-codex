@props(['questions', 'blockUuid' => null])

@php
    $questions = $questions ?? collect();
    $uniqueId = $blockUuid ? 'practice-' . \Illuminate\Support\Str::slug($blockUuid) : 'practice-' . uniqid();
    $isAdmin = \App\Support\AdminDebugAccess::allowed();
    $locale = app()->getLocale();

    $normalizeTags = function ($tags, $matchedTagIds = null) use ($locale) {
        return collect($tags)
            ->map(function ($tag) use ($matchedTagIds, $locale) {
                $name = \App\Support\TheoryTagLabel::display($tag->name ?? '', $locale);

                if ($name === '') {
                    return null;
                }

                return [
                    'id' => $tag->id,
                    'name' => $name,
                    'matched' => $matchedTagIds?->contains($tag->id) ?? false,
                ];
            })
            ->filter()
            ->groupBy(fn (array $tag) => mb_strtolower($tag['name']))
            ->map(function ($group) {
                $tag = $group->first();
                $tag['matched'] = $group->contains(fn (array $item) => $item['matched']);

                return $tag;
            })
            ->values();
    };

    $normalizeMarkerTags = function ($tags) use ($locale) {
        return collect($tags)
            ->map(function ($tag) use ($locale) {
                $name = \App\Support\TheoryTagLabel::display($tag['name'] ?? '', $locale);

                if ($name === '') {
                    return null;
                }

                return [
                    'id' => $tag['id'] ?? null,
                    'name' => $name,
                    'category' => $tag['category'] ?? null,
                ];
            })
            ->filter()
            ->unique(fn (array $tag) => mb_strtolower($tag['name']))
            ->values();
    };
    
    // Prepare questions data for JavaScript
    $questionsData = $questions->map(function($q) use ($isAdmin, $normalizeTags, $normalizeMarkerTags) {
        $matchedTagIds = collect($q->getAttribute('matched_tag_ids') ?? []);
        $tags = $normalizeTags($q->tags, $matchedTagIds);

        $markerTags = $isAdmin
            ? collect($q->getAttribute('marker_tags') ?? [])->map(function($tags, $marker) use ($normalizeMarkerTags) {
                return $normalizeMarkerTags($tags);
            })
            : collect();

        // Get verb hints by marker
        $verbHints = $q->verbHints->mapWithKeys(function($vh) {
            return [$vh->marker => $vh->option->option ?? ''];
        })->toArray();
        
        // Get hints/explanations
        $hints = $q->hints->map(function($h) {
            return [
                'provider' => $h->provider,
                'hint' => $h->hint,
            ];
        })->toArray();

        $answers = $q->answers
            ->sortBy(function ($answer) {
                $marker = strtolower(trim((string) $answer->marker));

                if (preg_match('/^([a-z_]+)(\d+)$/', $marker, $matches) === 1) {
                    return sprintf('%s%08d', $matches[1], (int) $matches[2]);
                }

                return $marker;
            })
            ->map(function($a) {
                return [
                    'marker' => $a->marker,
                    'correct' => $a->option->option ?? null,
                ];
            })
            ->values();
        
        return [
            'id' => $q->id,
            'type' => (string) $q->type,
            'question' => $q->question,
            'level' => $q->level,
            'options' => $q->options->pluck('option')->toArray(),
            'answers' => $answers->toArray(),
            'correct_tokens' => $answers->pluck('correct')->filter()->values()->toArray(),
            'verb_hints' => $verbHints,
            'hints' => $hints,
            'tags' => $tags->toArray(),
            'marker_tags' => $markerTags->toArray(),
        ];
    })->values()->toArray();

    $practiceI18n = [
        'title' => __('theory_blocks.practice_questions.title'),
        'question_prefix' => __('theory_blocks.practice_questions.question_prefix'),
        'matched_tags' => __('theory_blocks.practice_questions.matched_tags'),
        'marker_tags' => __('theory_blocks.practice_questions.marker_tags'),
        'hint' => __('theory_blocks.practice_questions.hint'),
        'check_answer' => __('theory_blocks.practice_questions.check_answer'),
        'correct' => __('theory_blocks.practice_questions.correct'),
        'incorrect' => __('theory_blocks.practice_questions.incorrect', ['answer' => ':answer']),
        'explanation' => __('theory_blocks.practice_questions.explanation'),
        'next_question' => __('theory_blocks.practice_questions.next_question'),
        'start_over' => __('theory_blocks.practice_questions.start_over'),
        'footer' => __('theory_blocks.practice_questions.footer'),
    ];
@endphp

@if($questions->isNotEmpty())
    <div class="mt-4 pt-4 border-t border-border/40">
        <div 
            x-data="practiceQuestion_{{ str_replace('-', '_', $uniqueId) }}()"
            x-init="init()"
            class="rounded-xl border border-dashed border-border/60 bg-gradient-to-br from-primary/5 to-transparent p-4"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <h4 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                    <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    {{ __('theory_blocks.practice_questions.title') }}
                </h4>
                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                    <span x-text="practiceI18n.question_prefix + (currentIndex + 1) + '/' + questions.length"></span>
                    <span class="text-emerald-600 font-medium" x-show="correctCount > 0" x-text="'✓ ' + correctCount"></span>
                </div>
            </div>
            
            {{-- Question Display --}}
            <div class="mb-4">
                <template x-if="currentQuestion">
                    <div>
                        {{-- Level badge --}}
                        <div class="mb-2" x-show="currentQuestion.level">
                            <span
                                class="inline-flex items-center rounded border px-1.5 py-0.5 text-[10px] font-bold"
                                :class="getLevelColor(currentQuestion.level)"
                                x-text="currentQuestion.level"
                            ></span>
                        </div>

                        {{-- Question text --}}
                        <p class="text-sm text-foreground font-medium leading-relaxed" x-html="getDisplayText()"></p>

                        {{-- Question tags (matched tags highlighted) --}}
                        @if($isAdmin)
                            <div class="mt-3 space-y-1" x-show="currentQuestion.tags && currentQuestion.tags.length">
                                <div class="text-[11px] font-semibold text-muted-foreground">{{ __('theory_blocks.practice_questions.matched_tags') }}</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="tag in currentQuestion.tags" :key="tag.id || tag.name">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium border"
                                            :class="tag.matched
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                                : 'bg-muted/60 text-muted-foreground border-border/60'"
                                        >
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" x-show="tag.matched">
                                                <path d="M7.172 2.172a4 4 0 0 1 5.656 0l4 4a4 4 0 0 1 0 5.656l-3.586 3.586a2 2 0 0 1-1.414.586H5a2 2 0 0 1-2-2v-6.828a2 2 0 0 1 .586-1.414z" />
                                            </svg>
                                            <span x-text="tag.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        @endif

                        {{-- Marker tags (admin only) --}}
                        @if($isAdmin)
                            <div class="mt-3 space-y-1" x-show="hasMarkerTags(currentQuestion)">
                                <div class="text-[11px] font-semibold text-muted-foreground">{{ __('theory_blocks.practice_questions.marker_tags') }}</div>
                                <template x-for="(tags, marker) in currentQuestion.marker_tags" :key="marker">
                                    <div class="flex items-start gap-2">
                                        <span class="px-1.5 py-0.5 rounded bg-muted/60 text-[10px] font-semibold uppercase text-muted-foreground" x-text="marker"></span>
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="tag in tags" :key="tag.id || tag.name">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-2 py-0.5 text-[10px] font-medium">
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M7.172 2.172a4 4 0 0 1 5.656 0l4 4a4 4 0 0 1 0 5.656l-3.586 3.586a2 2 0 0 1-1.414.586H5a2 2 0 0 1-2-2v-6.828a2 2 0 0 1 .586-1.414z" />
                                                    </svg>
                                                    <span x-text="tag.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endif

                        {{-- Verb hint (shown before answering) --}}
                        <div x-show="!answered && currentVerbHint" class="mt-2">
                            <span class="text-xs text-rose-600 font-semibold">
                                💡 {{ __('theory_blocks.practice_questions.hint') }}: <span x-text="currentVerbHint"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
            
            {{-- Compose Tokens --}}
            <div class="mb-4 space-y-3" x-show="isComposeQuestion()">
                <div class="min-h-[48px] rounded-lg border border-border/70 bg-background px-3 py-2">
                    <template x-if="selectedTokens.length === 0 && !answered">
                        <span class="text-xs text-muted-foreground">Складіть переклад речення з токенів нижче</span>
                    </template>
                    <template x-if="selectedTokens.length === 0 && answered">
                        <span class="text-xs text-muted-foreground">Відповідь не складена</span>
                    </template>
                    <div class="flex flex-wrap gap-2" x-show="selectedTokens.length > 0">
                        <template x-for="token in selectedTokens" :key="token.id">
                            <button
                                type="button"
                                :disabled="answered"
                                @click="removeToken(token)"
                                class="rounded-md border border-primary/30 bg-primary/10 px-2.5 py-1 text-sm font-medium text-primary transition hover:bg-primary/15 disabled:cursor-default disabled:hover:bg-primary/10"
                                x-text="token.value"
                            ></button>
                        </template>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2" x-show="!answered">
                    <template x-for="token in tokenBank" :key="token.id">
                        <button
                            type="button"
                            @click="selectToken(token)"
                            class="rounded-md border border-border/70 bg-background px-3 py-1.5 text-sm text-foreground/85 transition hover:border-primary/40 hover:bg-primary/5"
                            x-text="token.value"
                        ></button>
                    </template>
                </div>
            </div>

            {{-- Answer Options --}}
            <div class="space-y-2 mb-4" x-show="!answered && !isComposeQuestion()">
                <template x-for="(option, index) in currentOptions" :key="index">
                    <button 
                        @click="selectAnswer(option)"
                        class="w-full text-left px-4 py-2.5 rounded-lg border transition-all text-sm"
                        :class="selectedOption === option 
                            ? 'border-primary bg-primary/10 text-primary font-medium' 
                            : 'border-border/60 bg-background hover:border-primary/40 hover:bg-primary/5 text-foreground/80'"
                    >
                        <span class="inline-flex items-center gap-2">
                            <span 
                                class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold border"
                                :class="selectedOption === option 
                                    ? 'border-primary bg-primary text-white' 
                                    : 'border-muted-foreground/40 text-muted-foreground'"
                                x-text="String.fromCharCode(65 + index)"
                            ></span>
                            <span x-text="option"></span>
                        </span>
                    </button>
                </template>
            </div>
            
            {{-- Submit Button --}}
            <div x-show="!answered && (selectedOption || (isComposeQuestion() && selectedTokens.length > 0))" class="mb-4">
                <button 
                    @click="checkAnswer()"
                    class="w-full py-2.5 rounded-lg bg-primary text-primary-foreground font-medium text-sm hover:bg-primary/90 transition-colors"
                >
                    {{ __('theory_blocks.practice_questions.check_answer') }}
                </button>
            </div>
            
            {{-- Feedback --}}
            <div x-show="answered" class="mb-4 space-y-3">
                <div 
                    class="rounded-lg p-3 border"
                    :class="isCorrect 
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-800' 
                        : 'bg-rose-50 border-rose-200 text-rose-800'"
                >
                    <div class="flex items-center gap-2 font-medium text-sm">
                        <template x-if="isCorrect">
                            <span class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('theory_blocks.practice_questions.correct') }}
                            </span>
                        </template>
                        <template x-if="!isCorrect">
                            <span class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span x-text="incorrectFeedback()"></span>
                            </span>
                        </template>
                    </div>
                </div>
                
                {{-- Explanation/Hint (shown after answering) --}}
                <template x-if="isComposeQuestion()">
                    <div class="rounded-lg border border-border/70 bg-background p-3 text-sm">
                        <div class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-muted-foreground">Твоя відповідь</div>
                        <div class="mt-1 font-semibold text-foreground" x-text="composeSelectedText() || '—'"></div>
                    </div>
                </template>

                <template x-if="currentExplanation">
                    <div class="rounded-lg p-3 border border-blue-200 bg-blue-50 text-blue-800">
                        <div class="flex items-start gap-2 text-sm">
                            <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <span class="font-semibold">{{ __('theory_blocks.practice_questions.explanation') }}:</span>
                                <span x-text="currentExplanation" class="ml-1"></span>
                            </div>
                        </div>
                    </div>
                </template>
                
                {{-- Next Question Button --}}
                <button 
                    @click="nextQuestion()"
                    class="w-full py-2.5 rounded-lg border border-border bg-background text-foreground font-medium text-sm hover:bg-muted/50 transition-colors flex items-center justify-center gap-2"
                >
                    <span x-text="hasMoreQuestions() ? practiceI18n.next_question : practiceI18n.start_over"></span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
            
            {{-- Footer --}}
            <p class="text-[10px] text-muted-foreground/70 text-center">
                {{ __('theory_blocks.practice_questions.footer') }}
            </p>
        </div>
    </div>
    
    <script>
        function practiceQuestion_{{ str_replace('-', '_', $uniqueId) }}() {
            // Common filler options for grammar questions
            const FILLER_OPTIONS = [
                'do', 'does', 'did', 'is', 'are', 'was', 'were', 
                'have', 'has', 'had', 'will', 'would', 'can', 'could',
                'should', 'must', 'may', 'might', 'shall',
                'am', "don't", "doesn't", "didn't", "isn't", "aren't",
                "wasn't", "weren't", "haven't", "hasn't", "hadn't",
                "won't", "wouldn't", "can't", "couldn't", "shouldn't"
            ];
            const practiceI18n = @js($practiceI18n);
            const storageKey = [
                'gramlyze:theory-practice:v1',
                window.location.host,
                window.location.pathname,
                @js($uniqueId),
                @js($locale),
            ].join(':');

            return {
                practiceI18n,
                storageKey,
                questions: @json($questionsData),
                currentIndex: 0,
                currentQuestion: null,
                currentOptions: [],
                selectedOption: null,
                answered: false,
                isCorrect: false,
                correctAnswer: '',
                correctCount: 0,
                answeredIndices: [],
                currentVerbHint: '',
                currentExplanation: '',
                selectedTokens: [],
                tokenBank: [],
                
                init() {
                    if (this.restoreState()) {
                        return;
                    }

                    this.shuffleQuestions();
                    this.loadQuestion();
                    this.persistState();
                },

                questionId(question) {
                    return String(question?.id ?? '');
                },

                questionSignature(questions = this.questions) {
                    return questions.map(question => this.questionId(question)).filter(Boolean).sort().join('|');
                },

                restoreState() {
                    let saved = null;

                    try {
                        const raw = window.localStorage?.getItem(this.storageKey);
                        saved = raw ? JSON.parse(raw) : null;
                    } catch (error) {
                        console.error(error);
                        return false;
                    }

                    if (!saved || saved.signature !== this.questionSignature()) {
                        return false;
                    }

                    const currentById = new Map(this.questions.map(question => [this.questionId(question), question]));
                    const orderedQuestions = (saved.questionIds || [])
                        .map(id => currentById.get(String(id)))
                        .filter(Boolean);

                    if (orderedQuestions.length !== this.questions.length) {
                        return false;
                    }

                    this.questions = orderedQuestions;
                    this.currentIndex = Math.max(0, Math.min(Number(saved.currentIndex) || 0, this.questions.length - 1));
                    this.correctCount = Math.max(0, Number(saved.correctCount) || 0);
                    this.answeredIndices = Array.isArray(saved.answeredIndices)
                        ? saved.answeredIndices.map(index => Number(index)).filter(index => Number.isInteger(index))
                        : [];

                    this.loadQuestion();

                    const current = saved.current && typeof saved.current === 'object' ? saved.current : {};
                    this.selectedOption = typeof current.selectedOption === 'string' ? current.selectedOption : null;
                    this.answered = Boolean(current.answered);
                    this.isCorrect = Boolean(current.isCorrect);
                    this.currentOptions = Array.isArray(current.currentOptions) && current.currentOptions.length
                        ? current.currentOptions
                        : this.currentOptions;
                    this.selectedTokens = Array.isArray(current.selectedTokens) ? current.selectedTokens : [];
                    this.tokenBank = Array.isArray(current.tokenBank) && current.tokenBank.length
                        ? current.tokenBank
                        : this.tokenBank;

                    return true;
                },

                persistState() {
                    try {
                        window.localStorage?.setItem(this.storageKey, JSON.stringify({
                            signature: this.questionSignature(),
                            questionIds: this.questions.map(question => this.questionId(question)),
                            currentIndex: this.currentIndex,
                            correctCount: this.correctCount,
                            answeredIndices: this.answeredIndices,
                            current: {
                                selectedOption: this.selectedOption,
                                answered: this.answered,
                                isCorrect: this.isCorrect,
                                currentOptions: this.currentOptions,
                                selectedTokens: this.selectedTokens,
                                tokenBank: this.tokenBank,
                            },
                            savedAt: new Date().toISOString(),
                        }));
                    } catch (error) {
                        console.error(error);
                    }
                },

                clearStoredState() {
                    try {
                        window.localStorage?.removeItem(this.storageKey);
                    } catch (error) {
                        console.error(error);
                    }
                },
                
                shuffleQuestions() {
                    for (let i = this.questions.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [this.questions[i], this.questions[j]] = [this.questions[j], this.questions[i]];
                    }
                },
                
                loadQuestion() {
                    if (this.questions.length === 0) return;
                    
                    this.currentQuestion = this.questions[this.currentIndex];
                    this.selectedOption = null;
                    this.answered = false;
                    this.isCorrect = false;
                    this.currentExplanation = '';
                    this.selectedTokens = [];
                    this.tokenBank = [];
                    
                    // Get the first marker's correct answer
                    const firstAnswer = this.currentQuestion.answers.find(a => a.marker === 'a1') || this.currentQuestion.answers[0];
                    this.correctAnswer = firstAnswer ? firstAnswer.correct : '';
                    
                    // Get verb hint for a1 marker
                    this.currentVerbHint = this.currentQuestion.verb_hints?.['a1'] || '';
                    
                    // Get explanation hint
                    const hint = this.currentQuestion.hints?.find(h => h.hint);
                    this.currentExplanation = hint?.hint || '';

                    if (this.isComposeQuestion()) {
                        this.correctAnswer = this.composeCorrectText();
                        this.tokenBank = this.shuffledTokens(this.composeTokenValues());
                        this.currentOptions = [];
                        return;
                    }
                    
                    // Mix options: include all question options
                    let options = [...this.currentQuestion.options];
                    
                    // Make sure correct answer is included
                    if (this.correctAnswer && !options.includes(this.correctAnswer)) {
                        options.push(this.correctAnswer);
                    }
                    
                    // Ensure at least 3 options (add fillers if needed)
                    const minOptions = 3;
                    if (options.length < minOptions) {
                        // Get fillers that are not already in options
                        const availableFillers = FILLER_OPTIONS.filter(f => !options.includes(f) && f !== this.correctAnswer);
                        
                        // Shuffle fillers
                        for (let i = availableFillers.length - 1; i > 0; i--) {
                            const j = Math.floor(Math.random() * (i + 1));
                            [availableFillers[i], availableFillers[j]] = [availableFillers[j], availableFillers[i]];
                        }
                        
                        // Add fillers until we have enough options
                        while (options.length < minOptions && availableFillers.length > 0) {
                            options.push(availableFillers.pop());
                        }
                    }
                    
                    // Shuffle options
                    for (let i = options.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [options[i], options[j]] = [options[j], options[i]];
                    }
                    
                    // Limit to 4 options max
                    this.currentOptions = options.slice(0, 4);
                    
                    // Ensure correct answer is in the options
                    if (this.correctAnswer && !this.currentOptions.includes(this.correctAnswer)) {
                        this.currentOptions[Math.floor(Math.random() * this.currentOptions.length)] = this.correctAnswer;
                    }
                },

                isComposeQuestion() {
                    return String(this.currentQuestion?.type || '') === '4';
                },

                composeTokenValues() {
                    const correct = this.currentQuestion?.correct_tokens || [];
                    const distractors = this.currentQuestion?.options || [];
                    const seen = new Set();

                    return [...correct, ...distractors]
                        .map(value => String(value || '').trim())
                        .filter(value => {
                            if (!value || seen.has(value)) return false;
                            seen.add(value);
                            return true;
                        });
                },

                shuffledTokens(tokens) {
                    const items = tokens.map((value, index) => ({ id: `${index}-${value}`, value }));

                    for (let i = items.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [items[i], items[j]] = [items[j], items[i]];
                    }

                    return items;
                },

                selectToken(token) {
                    if (this.answered) return;
                    this.tokenBank = this.tokenBank.filter(item => item.id !== token.id);
                    this.selectedTokens.push(token);
                    this.persistState();
                },

                removeToken(token) {
                    if (this.answered) return;
                    this.selectedTokens = this.selectedTokens.filter(item => item.id !== token.id);
                    this.tokenBank.push(token);
                    this.persistState();
                },

                composeCorrectText() {
                    const text = (this.currentQuestion?.correct_tokens || [])
                        .map(value => String(value || '').trim())
                        .filter(Boolean)
                        .join(' ');

                    if (!text) return '';

                    const punctuation = String(this.currentQuestion?.question || '').trim().endsWith('?') ? '?' : '.';
                    return `${text}${punctuation}`.replace(/\s+([?.!,;:])/g, '$1');
                },

                composeSelectedText() {
                    const text = this.selectedTokens.map(token => token.value).join(' ').trim();

                    if (!text) return '';

                    const punctuation = String(this.currentQuestion?.question || '').trim().endsWith('?') ? '?' : '.';
                    return `${text}${punctuation}`.replace(/\s+([?.!,;:])/g, '$1');
                },

                normalizeAnswer(value) {
                    return String(value || '')
                        .normalize('NFKD')
                        .toLowerCase()
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[\u2018\u2019\u201b\u2032`´ʼ']/g, '')
                        .replace(/[^\p{L}\p{N}]+/gu, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                },
                
                getDisplayText() {
                    if (!this.currentQuestion) return '';
                    let text = this.currentQuestion.question;
                    // Replace markers with blanks, highlight the first one
                    text = text.replace(/\{a1\}/g, '<span class="inline-block px-3 py-0.5 mx-1 rounded bg-primary/20 text-primary font-mono">____</span>');
                    text = text.replace(/\{[a-z]\d+\}/g, '<span class="inline-block px-2 py-0.5 mx-1 rounded bg-muted text-muted-foreground font-mono text-xs">...</span>');
                    return text;
                },
                
                getLevelColor(level) {
                    const colors = {
                        'A1': 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'A2': 'bg-teal-100 text-teal-700 border-teal-200',
                        'B1': 'bg-blue-100 text-blue-700 border-blue-200',
                        'B2': 'bg-indigo-100 text-indigo-700 border-indigo-200',
                        'C1': 'bg-purple-100 text-purple-700 border-purple-200',
                        'C2': 'bg-rose-100 text-rose-700 border-rose-200',
                    };
                    return colors[level] || 'bg-muted text-muted-foreground border-border';
                },

                hasMarkerTags(question) {
                    const q = question || this.currentQuestion;
                    if (!q || !q.marker_tags) return false;
                    return Object.values(q.marker_tags).some(tags => Array.isArray(tags) && tags.length > 0);
                },

                selectAnswer(option) {
                    if (this.answered) return;
                    this.selectedOption = option;
                    this.persistState();
                },
                
                checkAnswer() {
                    if (this.answered) return;

                    if (this.isComposeQuestion()) {
                        if (this.selectedTokens.length === 0) return;

                        this.answered = true;
                        this.isCorrect = this.normalizeAnswer(this.composeSelectedText()) === this.normalizeAnswer(this.correctAnswer);

                        if (this.isCorrect) {
                            this.correctCount++;
                        }

                        this.answeredIndices.push(this.currentIndex);
                        this.persistState();
                        return;
                    }

                    if (!this.selectedOption) return;
                    
                    this.answered = true;
                    this.isCorrect = this.selectedOption === this.correctAnswer;
                    
                    if (this.isCorrect) {
                        this.correctCount++;
                    }
                    
                    this.answeredIndices.push(this.currentIndex);
                    this.persistState();
                },
                
                hasMoreQuestions() {
                    return this.currentIndex < this.questions.length - 1;
                },

                incorrectFeedback() {
                    return this.practiceI18n.incorrect.replace(':answer', this.correctAnswer || '');
                },
                
                nextQuestion() {
                    if (this.hasMoreQuestions()) {
                        this.currentIndex++;
                    } else {
                        // Start over with shuffled questions
                        this.currentIndex = 0;
                        this.correctCount = 0;
                        this.answeredIndices = [];
                        this.clearStoredState();
                        this.shuffleQuestions();
                    }
                    this.loadQuestion();
                    this.persistState();
                }
            };
        }
    </script>
@endif
