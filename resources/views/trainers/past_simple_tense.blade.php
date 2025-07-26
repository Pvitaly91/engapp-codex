@php
    $subtopics = [
        'irregular_verbs' => 'irregular verbs',
        'travel_story' => 'Travel Story',
        'test4' => 'am, is, are, have, has',
        'test5' => 'Simple Present x Simple Past',
        'test6' => 'Simple Present and Simple Past Test'
    ];
    $currentSub = request('sub', 'test');
@endphp

<div class="flex gap-8">
    @include('trainers.common._sidebar', [
        'subtopics' => $subtopics,
        'currentSub' => $currentSub,
    ])
    
    <section class="flex-1 min-w-0">
        @php
            $viewPath = "trainers.past_simple_tense.$currentSub";
          //  dd($viewPath);
        @endphp

        @if(view()->exists($viewPath))
            @include($viewPath)
        @else
            <div class="p-6 bg-white rounded-xl shadow text-gray-400">
                [ Trainer for subtopic <b>{{ $currentSub }}</b> is not implemented yet ]
            </div>
        @endif
    </section>
</div>
