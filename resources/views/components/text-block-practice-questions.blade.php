@props(['questions', 'blockUuid' => null])

@php
    $questions = $questions ?? collect();
@endphp

@if($questions->isNotEmpty())
    <div class="mt-4 pt-4 border-t border-border/40">
        <div 
            x-data="{ expanded: false }"
            class="rounded-xl border border-dashed border-border/60 bg-gradient-to-br from-primary/5 to-transparent p-4"
        >
            <button 
                @click="expanded = !expanded"
                class="flex w-full items-center justify-between text-left"
            >
                <h4 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                    <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Practice Questions
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                        {{ $questions->count() }}
                    </span>
                </h4>
                <svg 
                    class="h-4 w-4 text-muted-foreground transition-transform" 
                    :class="{ 'rotate-180': expanded }"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div x-show="expanded" x-collapse class="mt-4">
                <p class="text-xs text-muted-foreground mb-3">
                    Test your understanding with these related questions:
                </p>
                
                <div class="space-y-2">
                    @foreach($questions as $question)
                        @php
                            // Build the question display text (replace markers with blanks)
                            $displayText = preg_replace('/\{[a-z]\d+\}/', '____', $question->question);
                            $displayText = Str::limit($displayText, 100);
                            
                            // Get level badge color
                            $levelColor = match($question->level) {
                                'A1' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'A2' => 'bg-teal-100 text-teal-700 border-teal-200',
                                'B1' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'B2' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'C1' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'C2' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-muted text-muted-foreground border-border',
                            };
                        @endphp
                        
                        <div class="group flex items-start gap-3 rounded-lg bg-background/60 border border-border/40 p-3 transition-all hover:border-primary/30 hover:shadow-sm">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-foreground/90 font-medium leading-relaxed">
                                    {{ $displayText }}
                                </p>
                                
                                <div class="mt-2 flex items-center gap-2">
                                    @if($question->level)
                                        <span class="inline-flex items-center rounded border px-1.5 py-0.5 text-[10px] font-bold {{ $levelColor }}">
                                            {{ $question->level }}
                                        </span>
                                    @endif
                                    
                                    @if($question->tags->isNotEmpty())
                                        @php $displayTags = $question->tags->take(2); @endphp
                                        @foreach($displayTags as $tag)
                                            <span class="inline-flex items-center rounded bg-muted/60 px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                        @if($question->tags->count() > 2)
                                            <span class="text-[10px] text-muted-foreground">
                                                +{{ $question->tags->count() - 2 }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Practice button - links to a test containing this question --}}
                            <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span 
                                    class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1.5 text-xs font-medium text-primary cursor-default"
                                    title="Question ID: {{ $question->id }}"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Practice
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <p class="mt-3 text-[10px] text-muted-foreground/70 text-center">
                    Questions matched by topic tags • Refresh page for new questions
                </p>
            </div>
        </div>
    </div>
@endif
