@php
    $subtopics = [
        'test6' => 'test6',
        'test7' => 'test7',
        'test8' => 'test8',
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
            $viewPath = "trainers.present_simple_tense.$currentSub";
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
