@php
    foreach($data['cases'] as $case) {
        $index=$case['source_index']-1;
        $html=$data['author_self_check']['prompts'][$index];
        $badge='<span aria-hidden="true" data-theory-ui class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded bg-blue-500 text-white text-[10px]">'.e($case['source_index']).'</span>';
        $data['author_self_check']['prompts'][$index]=str_replace('<h4>','<h4 class="text-sm font-semibold flex items-center gap-2">'.$badge,$html);
    }
@endphp
@include('engram.theory.blocks-v3.authored-practice-ui', [
    'practiceScope'=>'m45','practiceFactory'=>'m45PracticeUi','practiceScript'=>'js/m45-practice-ui.js','m45ReferencePractice'=>true,
])
