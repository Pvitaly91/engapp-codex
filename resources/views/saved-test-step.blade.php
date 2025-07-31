@extends('layouts.app')

@section('title', $test->name . ' - Step Test')

@section('content')
<div class="max-w-xl mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $test->name }} - Step Mode</h1>
        <a href="{{ route('saved-test.show', $test->slug) }}" class="text-sm text-blue-600 underline">Back</a>
    </div>
    <div class="mb-4 flex gap-4 text-gray-600 text-base">
        <div>Total: <b>{{ $stats['total'] }} / {{ $totalCount }}</b></div>
        <div>Correct: <b class="text-green-700">{{ $stats['correct'] }}</b></div>
        <div>Wrong: <b class="text-red-700">{{ $stats['wrong'] }}</b></div>
        <div>Percent: <b>{{ $percentage }}%</b></div>
    </div>
    <form method="POST" action="{{ route('saved-test.step.reset', $test->slug) }}" class="mb-4">
        @csrf
        <button type="submit" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300 transition text-sm">Reset</button>
    </form>

    @if(isset($feedback))
        <div class="mb-4">
            @if($feedback['isCorrect'])
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">Correct!</div>
            @else
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">Wrong</div>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('saved-test.step.check', $test->slug) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="question_id" value="{{ $question->id }}">
        @php
            $questionText = $question->question;
            preg_match_all('/\{a(\d+)\}/', $questionText, $matches);
            $replacements = [];
            foreach ($matches[0] as $i => $marker) {
                $num = $matches[1][$i];
                $markerKey = 'a' . $num;
                $inputName = "answers[{$markerKey}]";
                $verbHintRow = $question->verbHints->where('marker', $markerKey)->first();
                $verbHint = $verbHintRow?->option?->option;
                $input = '<select name="'.$inputName.'" required class="border rounded px-2 py-1 mx-1">';
                $input .= '<option value="">---</option>';
                foreach($question->options as $opt){
                    $input .= '<option value="'.$opt->option.'">'.$opt->option.'</option>';
                }
                $input .= '</select>';
                if($verbHint){
                    $input .= ' <span class="text-red-700 text-xs font-bold">('.e($verbHint).')</span>';
                }
                $replacements[$marker] = $input;
            }
            $finalQuestion = strtr(e($questionText), $replacements);
        @endphp
        <label class="text-base block" style="white-space:normal">{!! $finalQuestion !!}</label>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold">
            {{ isset($feedback) ? 'Next' : 'Check' }}
        </button>
    </form>
</div>
@endsection
