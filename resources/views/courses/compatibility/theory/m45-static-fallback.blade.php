@if(isset($data['author_section']) && is_array($data['author_section']))
    <div data-m45-static-fallback data-theory-render-fallback="m45-author-identity">
        @include('engram.theory.blocks-v3.m45-section', ['guarded'=>false])
    </div>
@elseif(isset($data['author_practice']) && is_array($data['author_practice']))
    <section id="block-{{ $block->id }}" class="theory-section-card theory-section-body" data-m45-static-fallback data-theory-render-fallback="m45-practice-identity">
        <x-theory-native-header :title="$data['title'] ?? 'Практика'" :level="$block->level ?? null" />
        @foreach($data['author_practice'] as $task)
            <article class="theory-item rounded-xl p-4 space-y-3">
                <h3 class="font-bold">{{ $task['title'] }}</h3>
                <p lang="uk">{{ $task['prompt_uk'] }}</p>
                @if(!empty($task['context_uk']))<p lang="uk">{{ $task['context_uk'] }}</p>@endif
                @foreach($task['controls'] as $control)
                    <p lang="uk">{{ $control['label_uk'] }}</p>
                    @if(isset($control['stimulus_en']))<p lang="en">{{ $control['stimulus_en'] }}</p>@endif
                    @if(isset($control['stimulus_uk']))<p lang="uk">{{ $control['stimulus_uk'] }}</p>@endif
                    @if(!empty($control['options']))
                        <ul lang="uk">@foreach($control['options'] as $option)<li>{{ $option['label_uk'] }}</li>@endforeach</ul>
                    @endif
                    <div class="flex flex-wrap gap-2">@foreach($control['tokens'] ?? [] as $token)<span>{{ $token }}</span>@endforeach</div>
                @endforeach
                <details><summary>Відповідь і пояснення</summary>
                    @foreach($task['feedback']['paragraphs_uk'] as $paragraph)<p lang="uk">{{ $paragraph }}</p>@endforeach
                    {!! \App\Support\M43NativeHtml::examples($task['feedback']['answer_examples']) !!}
                </details>
            </article>
        @endforeach
    </section>
@elseif(($data['m45_v1']['role'] ?? null) === 'hero')
    <section id="block-{{ $block->id }}" class="theory-section-card theory-section-body" data-m45-static-fallback>
        @if(!empty($data['level']))<p>{{ $data['level'] }}</p>@endif
        <p lang="uk">{{ html_entity_decode(strip_tags($data['intro'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</p>
        @foreach($data['rules'] ?? [] as $rule)
            <h3>{{ $rule['label'] ?? '' }}</h3>
            <p lang="uk">{{ html_entity_decode(strip_tags($rule['text'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</p>
            @if(isset($rule['example']))<p lang="en">{{ $rule['example'] }}</p>@endif
        @endforeach
    </section>
@endif
