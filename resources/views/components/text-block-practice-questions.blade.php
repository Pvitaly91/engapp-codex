@props(['questions', 'blockUuid' => null])

@php
    $questions = $questions ?? collect();
    $uniqueId = $blockUuid ? 'practice-' . \Illuminate\Support\Str::slug($blockUuid) : 'practice-' . uniqid();
    
    // Prepare questions data for JavaScript
    $questionsData = $questions->map(function($q) {
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
            <div x-show="answered" class="mb-4">
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
                
                {{-- Next Question Button --}}
                <button 
                    @click="nextQuestion()"
                    class="w-full mt-3 py-2.5 rounded-lg border border-border bg-background text-foreground font-medium text-sm hover:bg-muted/50 transition-colors flex items-center justify-center gap-2"
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
                    
                    // Get the first marker's correct answer
                    const firstAnswer = this.currentQuestion.answers.find(a => a.marker === 'a1') || this.currentQuestion.answers[0];
                    this.correctAnswer = firstAnswer ? firstAnswer.correct : '';
                    
                    // Mix options: include all question options
                    let options = [...this.currentQuestion.options];
                    
                    // Make sure correct answer is included
                    if (this.correctAnswer && !options.includes(this.correctAnswer)) {
                        options.push(this.correctAnswer);
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
