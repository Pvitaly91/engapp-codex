@if($questions->isEmpty())
    <div class="rounded-2xl border border-dashed border-stone-200 bg-white p-6 text-center text-sm text-stone-500">
        Цей тест ще не містить питань. Додайте перше питання, щоб розпочати.
    </div>
@else
    @foreach($questions as $question)
        @include('engram.partials.saved-test-tech-question', [
            'question' => $question,
            'iteration' => $loop->iteration,
            'test' => $test,
            'explanationsByQuestionId' => $explanationsByQuestionId,
            'returnUrl' => $returnUrl,
        ])
    @endforeach
@endif
