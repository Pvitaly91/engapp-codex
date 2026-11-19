@php
    // Exact code-generated strings receive render-local decoration, never new answer authority.
    $data['author_self_check']['answers'] = array_map(
        [\App\Support\M44NativeHtml::class, 'decorateStoredExamples'],
        $data['author_self_check']['answers']
    );
    foreach ($data['cases'] as $case) {
        $index = $case['source_index'] - 1;
        $data['author_self_check']['prompts'][$index] = \App\Support\M44NativeHtml::decoratePracticePrompt(
            $data['author_self_check']['prompts'][$index], $case['source_index']
        );
    }
@endphp
<div class="m44-native-design" data-m44-practice-scope>
@include('engram.theory.blocks-v3.authored-practice-ui', [
    'practiceScope' => 'm44',
    'practiceFactory' => 'm44PracticeUi',
    'practiceScript' => 'js/m44-practice-ui.js',
    'm44ReferencePractice' => true,
])
</div>
