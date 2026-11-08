@php
    // A render-local decoration; cases, scoring, aliases and stored feedback stay exact.
    $data['author_self_check']['answers'] = array_map(
        [\App\Support\M43NativeHtml::class, 'decorateStoredExamples'],
        $data['author_self_check']['answers']
    );
    foreach ($data['cases'] as $case) {
        $index = $case['source_index'] - 1;
        $data['author_self_check']['prompts'][$index] = \App\Support\M43NativeHtml::decoratePracticePrompt(
            $data['author_self_check']['prompts'][$index], $case['source_index']
        );
    }
@endphp
<div class="m43-native-design" data-m43-practice-scope>
@include('engram.theory.blocks-v3.authored-practice-ui', [
    'practiceScope' => 'm43',
    'practiceFactory' => 'm43PracticeUi',
    'practiceScript' => 'js/m43-practice-ui.js',
])
</div>
