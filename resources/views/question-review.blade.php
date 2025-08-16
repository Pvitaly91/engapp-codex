@extends('layouts.app')

@section('title', 'Question Review')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Question Review</h1>
        <a href="{{ route('question-review-results.index') }}" class="text-sm text-blue-600 underline">Review Results</a>
    </div>
    <form method="POST" action="{{ route('question-review.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="question_id" value="{{ $question->id }}">
        <div class="bg-white shadow rounded-2xl p-4 flex justify-between items-start gap-4">
            <div>
            @php
                $text = $question->question;
                preg_match_all('/\{a(\d+)\}/', $text, $m);
                $repl = [];
                foreach($m[0] as $i => $marker){
                    $num = $m[1][$i];
                    $key = 'a'.$num;
                    $answer = $question->answers->where('marker', $key)->first();
                    $current = $answer?->option?->option ?? $answer?->answer;
                    $select = '<select name="answers['.$key.']" class="border rounded px-2 py-1">';
                    foreach($question->options as $opt){
                        $sel = $opt->option === $current ? 'selected' : '';
                        $select .= '<option value="'.$opt->option.'" '.$sel.'>'.$opt->option.'</option>';
                    }
                    $select .= '</select>';
                    $repl[$marker] = $select;
                }
                echo strtr(e($text), $repl);
            @endphp
            </div>
            <div class="self-start">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Next</button>
            </div>
        </div>
        <div class="bg-white shadow rounded-2xl p-4">
            <div class="font-semibold mb-2">{{ ucfirst($question->category->name) }}</div>
            <div class="flex flex-wrap gap-2">
                @foreach($allTags as $tag)
                    <label class="cursor-pointer">
                        <input type="checkbox" id="tag{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}" class="peer hidden" {{ $question->tags->contains($tag->id) ? 'checked' : '' }}>
                        <span class="px-3 py-1 rounded-full border bg-white peer-checked:bg-blue-600 peer-checked:text-white">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="bg-white shadow rounded-2xl p-4">
            <label for="level" class="font-semibold mb-1 block">Level</label>
            <select name="level" id="level" class="border rounded px-2 py-1">
                <option value="">N/A</option>
                @foreach(['A1','A2','B1','B2','C1','C2'] as $lvl)
                    <option value="{{ $lvl }}" {{ $question->level === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="bg-white shadow rounded-2xl p-4">
            <label for="comment" class="font-semibold mb-1 block">Comment</label>
            <textarea name="comment" id="comment" rows="3" class="w-full border rounded px-2 py-1">{{ old('comment') }}</textarea>
        </div>
    </form>
</div>
@endsection
