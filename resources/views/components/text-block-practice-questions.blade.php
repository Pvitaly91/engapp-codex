@props(['questions', 'blockUuid' => null])

@php
    $questions = $questions ?? collect();
    $uniqueId = $blockUuid ? 'practice-' . \Illuminate\Support\Str::slug($blockUuid) : 'practice-' . uniqid();
    $isAdmin = (bool) (auth()->user()?->is_admin ?? session('admin_authenticated', false));
    
    // Prepare questions data for JavaScript
    $questionsData = $questions->map(function($q) use ($isAdmin) {
        $matchedTagIds = collect($q->getAttribute('matched_tag_ids') ?? []);

        $tags = $q->tags->map(function($tag) use ($matchedTagIds) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'matched' => $matchedTagIds->contains($tag->id),
            ];
        });

        $markerTags = $isAdmin
            ? collect($q->getAttribute('marker_tags') ?? [])->map(function($tags, $marker) {
                return collect($tags)->map(function($tag) {
                    return [
                        'id' => $tag['id'] ?? null,
                        'name' => $tag['name'] ?? '',
                        'category' => $tag['category'] ?? null,
                    ];
                })->values();
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
        
        return [
            'id' => $q->id,
            'question' => $q->question,
            'level' => $q->level,
            'options' => $q->options->pluck('option')->toArray(),
            'answers' => $q->answers->map(function($a) {
                return [
                    'marker' => $a->marker,
                    'correct' => $a->option->option ?? null,
                ];
            })->toArray(),
            'verb_hints' => $verbHints,
            'hints' => $hints,
            'tags' => $tags->toArray(),
            'marker_tags' => $markerTags->toArray(),
        ];
    })->values()->toArray();
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
                    Quick Practice
                </h4>
                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                    <span x-text="'Q' + (currentIndex + 1) + '/' + questions.length"></span>
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
                        <div class="mt-3 space-y-1" x-show="currentQuestion.tags && currentQuestion.tags.length">
                            <div class="text-[11px] font-semibold text-muted-foreground">Tags (matched highlighted)</div>
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

                        {{-- Marker tags (admin only) --}}
                        @if($isAdmin)
                            <div class="mt-3 space-y-1" x-show="hasMarkerTags(currentQuestion)">
                                <div class="text-[11px] font-semibold text-muted-foreground">Marker tags</div>
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
                                💡 Hint: <span x-text="currentVerbHint"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
            
            {{-- Answer Options --}}
            <div class="space-y-2 mb-4" x-show="!answered">
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
            <div x-show="selectedOption && !answered" class="mb-4">
                <button 
                    @click="checkAnswer()"
                    class="w-full py-2.5 rounded-lg bg-primary text-primary-foreground font-medium text-sm hover:bg-primary/90 transition-colors"
                >
                    Check Answer
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
                                Correct! Well done!
                            </span>
                        </template>
                        <template x-if="!isCorrect">
                            <span class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span>Incorrect. The answer is: <strong x-text="correctAnswer"></strong></span>
                            </span>
                        </template>
                    </div>
                </div>
                
                {{-- Explanation/Hint (shown after answering) --}}
                <template x-if="currentExplanation">
                    <div class="rounded-lg p-3 border border-blue-200 bg-blue-50 text-blue-800">
                        <div class="flex items-start gap-2 text-sm">
                            <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <span class="font-semibold">Explanation:</span>
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
                    <span x-text="hasMoreQuestions() ? 'Next Question' : 'Start Over'"></span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
            
            {{-- Footer --}}
            <p class="text-[10px] text-muted-foreground/70 text-center">
                Practice questions matched by topic tags
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
            
            return {
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
                
                init() {
                    this.shuffleQuestions();
                    this.loadQuestion();
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
                    
                    // Get the first marker's correct answer
                    const firstAnswer = this.currentQuestion.answers.find(a => a.marker === 'a1') || this.currentQuestion.answers[0];
                    this.correctAnswer = firstAnswer ? firstAnswer.correct : '';
                    
                    // Get verb hint for a1 marker
                    this.currentVerbHint = this.currentQuestion.verb_hints?.['a1'] || '';
                    
                    // Get explanation hint
                    const hint = this.currentQuestion.hints?.find(h => h.hint);
                    this.currentExplanation = hint?.hint || '';
                    
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
                },
                
                checkAnswer() {
                    if (!this.selectedOption || this.answered) return;
                    
                    this.answered = true;
                    this.isCorrect = this.selectedOption === this.correctAnswer;
                    
                    if (this.isCorrect) {
                        this.correctCount++;
                    }
                    
                    this.answeredIndices.push(this.currentIndex);
                },
                
                hasMoreQuestions() {
                    return this.currentIndex < this.questions.length - 1;
                },
                
                nextQuestion() {
                    if (this.hasMoreQuestions()) {
                        this.currentIndex++;
                    } else {
                        // Start over with shuffled questions
                        this.currentIndex = 0;
                        this.shuffleQuestions();
                    }
                    this.loadQuestion();
                }
            };
        }
    </script>
@endif
