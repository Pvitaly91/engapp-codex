@extends('layouts.app')

@section('title', 'AI Grammar Test')

@section('content')
<div class="max-w-xl mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">AI Grammar Test</h1>
        <a href="{{ route('ai-test.form') }}" class="text-sm text-blue-600 underline">Start over</a>
    </div>
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Total: <b>{{ $stats['total'] }}</b></div>
        <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
        <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
        <div>Percent: <b>{{ $percentage }}%</b></div>
    </div>
    <form method="POST" action="{{ route('ai-test.reset') }}" class="mb-4">
        @csrf
        <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
    </form>

    @if(isset($feedback))
        <div class="mb-4">
            @if($feedback['isCorrect'])
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">Correct!</div>
            @else
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">Wrong</div>
                @if(!empty($feedback['explanations']))
                    <div class="bg-blue-50 text-gray-800 text-sm rounded px-3 py-2 space-y-1">
                        @foreach($feedback['explanations'] as $exp)
                            <p>{!! $exp !!}</p>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('ai-test.check') }}" class="space-y-4">
        @csrf
        @php
            $answersCol = collect();
            foreach($question['answers'] as $m => $val){
                $answersCol->push((object)['marker'=>$m, 'option'=>(object)['option'=>$val]]);
            }
            $obj = (object)['question'=>$question['question'], 'verbHints'=>collect(), 'options'=>collect(), 'answers'=>$answersCol];
        @endphp
        @include('components.question-input', [
            'question' => $obj,
            'inputNamePrefix' => 'answers',
            'arrayInput' => true,
            'manualInput' => false,
            'autocompleteInput' => false,
            'builderInput' => true,
        ])
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
            {{ isset($feedback) ? 'Next' : 'Check' }}
        </button>
    </form>
</div>
@endsection
