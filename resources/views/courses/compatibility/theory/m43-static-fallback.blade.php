{{-- Stored author data remains readable if an identity/hash/locale check fails.
     No DB-selected view, executable HTML, hidden mirror or answer checking. --}}
<section id="block-{{ $block->id }}" class="theory-section-card theory-section-body" data-m43-static-fallback>
    <h2 class="theory-section-title">{{ $data['title'] ?? '' }}</h2>
    @if(is_array($data['author_section'] ?? null))
        @php
            $section = $data['author_section'];
        @endphp
        @if(isset($section['intro_uk']))<p lang="uk">{{ $section['intro_uk'] }}</p>@endif
        @foreach($section['points'] ?? [] as $point)
            <article id="{{ $point['id'] }}" class="space-y-3">
                <h3>{{ $point['title'] }}</h3>
                @if(isset($point['formula']))<p>{{ $point['formula'] }}</p>@endif
                {!! \App\Support\M43NativeHtml::paragraphs($point['paragraphs_uk'] ?? []) !!}
                @if(isset($point['wrong_en']))
                    <p lang="en">✕ {{ $point['wrong_en'] }}</p>
                    @if(isset($point['wrong_uk']))<p lang="uk">{{ $point['wrong_uk'] }}</p>@endif
                    <p lang="en">✓ {{ $point['right_en'] }}</p>
                    <p lang="uk">{{ $point['right_uk'] }}</p>
                @endif
                {!! \App\Support\M43NativeHtml::examples($point['examples'] ?? []) !!}
                @if(isset($point['detail']))
                    <div id="block-{{ $point['detail']['id'] }}" class="space-y-3">
                        <h4>{{ $point['detail']['title'] }}</h4>
                        {!! \App\Support\M43NativeHtml::paragraphs($point['detail']['paragraphs_uk']) !!}
                        {!! \App\Support\M43NativeHtml::examples($point['detail']['examples']) !!}
                    </div>
                @endif
            </article>
        @endforeach
        @if(isset($section['table']))
            @include('engram.theory.blocks-v3.m43-native-table', ['m43StructuredTable' => $section['table']])
        @endif
        {!! \App\Support\M43NativeHtml::paragraphs($section['notes_uk'] ?? []) !!}
        {!! \App\Support\M43NativeHtml::examples($section['note_examples'] ?? []) !!}
    @endif
    @foreach($data['author_practice'] ?? [] as $task)
        <article id="{{ $task['id'] }}" class="space-y-3">
            <h3>{{ $task['title'] }}</h3><p>{{ $task['prompt_uk'] }}</p><p>{{ $task['context_uk'] }}</p>
            @foreach($task['controls'] as $control)
                <div><h4>{{ $control['label_uk'] }}</h4>
                    @if(isset($control['stimulus_en']))<p lang="en">{{ $control['stimulus_en'] }}</p>@endif
                    @foreach($control['options'] ?? [] as $option)<p>{{ $option['label'] }}</p>@endforeach
                    @if(isset($control['tokens']))<p>{{ implode(' | ', $control['tokens']) }}</p>@endif
                </div>
            @endforeach
            <details><summary>Відповідь і пояснення</summary>
                @foreach($task['controls'] as $control)
                    @if(isset($control['accepted_answers']))
                        @foreach($control['accepted_answers'] as $answer)<p lang="en">{{ $answer }}</p>@endforeach
                    @else
                        <p>{{ collect($control['options'])->firstWhere('value', $control['correct_value'])['label'] }}</p>
                    @endif
                @endforeach
                {!! \App\Support\M43NativeHtml::paragraphs($task['feedback']['paragraphs_uk']) !!}
                {!! \App\Support\M43NativeHtml::examples($task['feedback']['answer_examples']) !!}
            </details>
        </article>
    @endforeach
    <x-text-block-tags :block="$block" />
    <x-text-block-practice-questions :questions="$practiceQuestions ?? collect()" :blockUuid="$block->uuid" />
</section>
