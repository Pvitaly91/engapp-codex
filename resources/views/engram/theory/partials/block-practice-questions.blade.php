@php
    /**
     * Display practice questions matched for a text block.
     * 
     * @var \Illuminate\Support\Collection $questions Collection of Question models
     * @var \App\Models\TextBlock $block The text block these questions are associated with
     */
    $questions = $questions ?? collect();
@endphp

@if($questions->isNotEmpty())
    <div class="mt-4 rounded-xl border border-amber-200/60 bg-gradient-to-r from-amber-50/50 to-orange-50/50 p-4">
        <div class="flex items-center gap-2 mb-3">
            <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-500 text-white">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h4 class="text-sm font-semibold text-amber-900">Практичні питання</h4>
            <span class="text-xs text-amber-600">({{ $questions->count() }} {{ trans_choice('питання|питань|питань', $questions->count()) }})</span>
        </div>
        
        <div class="space-y-2">
            @foreach($questions as $index => $question)
                @php
                    // Parse the question text to highlight gaps
                    $questionText = $question->question ?? '';
                    $questionText = preg_replace('/\{a\d+\}/', '<span class="inline-block px-2 py-0.5 rounded bg-amber-200 text-amber-800 font-mono text-xs">___</span>', $questionText);
                @endphp
                
                <div class="flex items-start gap-3 rounded-lg bg-white/80 border border-amber-100 p-3 hover:bg-white transition-colors">
                    <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold mt-0.5">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-foreground/90 leading-relaxed">
                            {!! $questionText !!}
                        </p>
                        @if($question->level)
                            <span class="inline-block mt-1.5 text-[10px] font-medium px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                                {{ $question->level }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <p class="mt-3 text-[10px] text-amber-600/80 italic">
            💡 Перевір свої знання з цього розділу, відповідаючи на ці питання.
        </p>
    </div>
@endif
