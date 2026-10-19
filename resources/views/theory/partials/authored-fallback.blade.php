{{-- Failed identity/locale/body guards grant no grading or disclosure authority.
     Plain author fields are escaped; the complete material remains readable. --}}
@if(is_array($data['author_section'] ?? null))
    @include('theory.partials.authored-section', ['guarded' => false, 'compact' => null, 'pointSections' => null])
@elseif(is_array($data['author_practice'] ?? null))
    <section id="block-{{ $block->id }}" class="theory-native-block scroll-mt-24" data-theory-render-fallback="author-practice-identity">
        <div class="theory-section-card rounded-2xl border border-border/60 bg-card">
            <x-theory-native-header :title="$data['title'] ?? ''" :level="$block->level ?? null" fallback="⚡" />
            <div class="theory-section-body p-5 space-y-6">
                @foreach($data['author_practice'] as $task)
                    <article id="{{ $task['id'] ?? '' }}" class="theory-exercise rounded-xl border border-border">
                        <div class="border-b border-border px-4 py-3 text-base leading-relaxed" data-practice-instruction>
                            <h3 class="text-sm font-semibold">{{ $task['title'] ?? '' }}</h3>
                            <p lang="uk">{{ $task['prompt_uk'] ?? '' }}</p>
                            @if(!empty($task['context_uk']))<p lang="uk">{{ $task['context_uk'] }}</p>@endif
                        </div>
                        <div class="p-4 space-y-3">
                            @foreach($task['controls'] ?? [] as $control)
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">{{ $control['label_uk'] ?? $control['label'] ?? '' }}</h4>
                                    @foreach(['en','uk'] as $language)
                                        @if(isset($control['stimulus_'.$language]))<p lang="{{ $language }}">{{ $control['stimulus_'.$language] }}</p>@endif
                                    @endforeach
                                    @foreach($control['options'] ?? [] as $option)<p>{{ $option['label_uk'] ?? $option['label'] ?? '' }}</p>@endforeach
                                    @if(!empty($control['tokens']))<div class="flex flex-wrap gap-2">@foreach($control['tokens'] as $token)<span>{{ $token }}</span>@endforeach</div>@endif
                                </div>
                            @endforeach
                            <details>
                                <summary class="text-sm font-semibold cursor-pointer">Відповідь і пояснення</summary>
                                <div class="space-y-3 mt-3">
                                    @foreach($task['controls'] ?? [] as $control)
                                        @foreach($control['accepted_answers'] ?? [] as $answer)<p lang="en">{{ $answer }}</p>@endforeach
                                        @if(empty($control['accepted_answers']) && isset($control['correct_value']))
                                            @php
                                                $correctOption = collect($control['options'] ?? [])->firstWhere('value', $control['correct_value']);
                                            @endphp
                                            <p>{{ $correctOption['label_uk'] ?? $correctOption['label'] ?? '' }}</p>
                                        @endif
                                    @endforeach
                                    @include('theory.components.node', ['node' => [
                                        'kind' => 'fragment', 'body_html' => \App\Support\TheoryAuthoredAdapter::paragraphs($task['feedback']['paragraphs_uk'] ?? []),
                                        'examples' => array_map([\App\Support\TheoryAuthoredAdapter::class, 'example'], $task['feedback']['answer_examples'] ?? []),
                                    ]])
                                </div>
                            </details>
                        </div>
                    </article>
                @endforeach
                <x-text-block-tags :block="$block" />
                <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
            </div>
        </div>
    </section>
@else
    @php
        $items = [];
        if (isset($data['level'])) { $items[] = ['kind' => 'fragment', 'body_html' => $data['level']]; }
        if (isset($data['intro'])) { $items[] = ['kind' => 'fragment', 'body_html' => html_entity_decode(strip_tags($data['intro']), ENT_QUOTES | ENT_HTML5, 'UTF-8')]; }
        foreach ($data['rules'] ?? [] as $rule) {
            $items[] = ['kind' => 'fragment', 'title' => $rule['label'] ?? '',
                'body_html' => html_entity_decode(strip_tags($rule['text'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'examples' => isset($rule['example']) ? [['kind' => 'example', 'en' => $rule['example']]] : []];
        }
    @endphp
    @include('theory.components.node', ['node' => ['kind' => 'section', 'id' => 'block-'.$block->id,
        'title' => $data['title'] ?? '', 'level' => $block->level ?? null, 'items' => $items, 'footer' => true]])
@endif
