@php
    $snapshot = $snapshot ?? null;
    $previousSnapshot = $previousSnapshot ?? null;

    $renderQuestionText = function (?array $data) {
        if (! is_array($data)) {
            return null;
        }

        $question = $data['question'] ?? null;

        return $question ? trim(strip_tags($question)) : null;
    };

    $formatList = function (?array $items, callable $map) {
        return collect($items ?? [])->map($map)->filter()->values();
    };
@endphp

@if($snapshot)
    @php
        $questionText = $renderQuestionText($snapshot);
        $options = $formatList($snapshot['options'] ?? [], fn ($option) => $option['option'] ?? null);
        $answers = $formatList($snapshot['answers'] ?? [], function ($answer) {
            if (! isset($answer['marker'])) {
                return null;
            }

            $value = $answer['option']['option'] ?? null;

            return $value ? strtoupper($answer['marker']) . ' — ' . $value : strtoupper($answer['marker']);
        });
        $variants = $formatList($snapshot['variants'] ?? [], fn ($variant) => $variant['text'] ?? null);
        $verbHints = $formatList($snapshot['verb_hints'] ?? [], function ($hint) {
            $value = $hint['option']['option'] ?? null;

            return $value ? strtoupper($hint['marker'] ?? '') . ' — ' . $value : null;
        });
        $hints = $formatList($snapshot['hints'] ?? [], function ($hint) {
            $provider = $hint['provider'] ?? null;
            $locale = $hint['locale'] ?? null;
            $text = $hint['hint'] ?? null;

            if (! $text) {
                return null;
            }

            $meta = collect([$provider, $locale ? strtoupper($locale) : null])
                ->filter()
                ->implode(' · ');

            return $meta ? "$meta — $text" : $text;
        });
        $tags = $formatList($snapshot['tags'] ?? [], fn ($tag) => $tag['name'] ?? null);
    @endphp

    <div class="space-y-4">
        @if($questionText)
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Текст питання</h4>
                <p class="mt-1 text-sm text-stone-800">{{ $questionText }}</p>
            </div>
        @endif

        <dl class="grid gap-3 sm:grid-cols-2">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Level</dt>
                <dd class="mt-1 text-sm font-semibold text-stone-800">{{ $snapshot['level'] ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Difficulty</dt>
                <dd class="mt-1 text-sm text-stone-800">{{ $snapshot['difficulty'] ?? '—' }}</dd>
            </div>
        </dl>

        @if($answers->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Правильні відповіді</h4>
                <ul class="mt-1 space-y-1 text-sm text-stone-800">
                    @foreach($answers as $item)
                        <li>• {{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($options->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Варіанти відповіді</h4>
                <ul class="mt-1 flex flex-wrap gap-2 text-sm text-stone-800">
                    @foreach($options as $item)
                        <li class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($variants->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Варіанти формулювання</h4>
                <ul class="mt-1 space-y-1 text-sm text-stone-800">
                    @foreach($variants as $item)
                        <li>• {{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($verbHints->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Verb hints</h4>
                <ul class="mt-1 space-y-1 text-sm text-stone-800">
                    @foreach($verbHints as $item)
                        <li>• {{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($hints->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Question hints</h4>
                <ul class="mt-1 space-y-1 text-sm text-stone-800">
                    @foreach($hints as $item)
                        <li>• {{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($tags->isNotEmpty())
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Теги</h4>
                <ul class="mt-1 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-wide text-stone-600">
                    @foreach($tags as $tag)
                        <li class="inline-flex items-center rounded-full bg-stone-200 px-2 py-0.5">{{ $tag }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@elseif($previousSnapshot)
    <div class="space-y-3">
        <p class="text-sm text-stone-600">Зміна видаляє питання з технічної черги.</p>
        @php
            $previousText = $renderQuestionText($previousSnapshot);
        @endphp
        @if($previousText)
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-stone-500">Попередній текст питання</h4>
                <p class="mt-1 text-sm text-stone-800">{{ $previousText }}</p>
            </div>
        @endif
    </div>
@else
    <p class="text-sm text-stone-600">Попередній перегляд недоступний для цієї зміни.</p>
@endif
