@extends('layouts.app')

@section('title', 'Review Results')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Question Review Results</h1>
    @if($results->count())
        <ul class="divide-y">
            @foreach($results as $result)
            @php
                $questionText = $result->question->question;
                $orig = $questionText;
                $changed = $questionText;
                $modified = false;
                foreach($result->question->answers as $ans){
                    $marker = '{'.$ans->marker.'}';
                    $origVal = $ans->option->option ?? $ans->answer;
                    $orig = str_replace($marker, '<strong>'.$origVal.'</strong>', $orig);
                    $newVal = $result->answers[$ans->marker] ?? $origVal;
                    if($newVal !== $origVal){ $modified = true; }
                    $changed = str_replace($marker, '<strong>'.$newVal.'</strong>', $changed);
                }
                $origTags = $result->original_tags ?? [];
                $newTags = $result->tags ?? [];
                $tagNames = function($ids) {
                    return \App\Models\Tag::whereIn('id', $ids)->pluck('name')->implode(', ');
                };
                $tagsChanged = array_diff($origTags, $newTags) || array_diff($newTags, $origTags);
            @endphp
            <li class="py-3 {{ $modified ? 'bg-yellow-50' : '' }}">
                <div class="font-semibold">{!! $orig !!}</div>
                @if($modified)
                    <div class="mt-1">{!! $changed !!}</div>
                @endif
                @if(count($origTags))
                    <div class="text-sm">Original tags: {{ $tagNames($origTags) }}</div>
                @endif
                <div class="text-sm">
                    @if($tagsChanged)
                        Updated tags: <span class="font-medium">{{ $tagNames($newTags) }}</span>
                    @else
                        Current tags: {{ $tagNames($newTags) }}
                    @endif
                </div>
                @if($result->comment)
                    <div class="text-sm text-gray-600">Comment: {{ $result->comment }}</div>
                @endif
                <div class="text-xs text-gray-500 flex gap-2">
                    <span>{{ $result->created_at->format('d.m.Y H:i') }}</span>
                    <a href="{{ route('question-review.edit', $result->question_id) }}" class="text-blue-600 underline">Edit</a>
                </div>
            </li>
            @endforeach
        </ul>
        <div class="mt-6">{{ $results->links() }}</div>
    @else
        <div class="text-gray-600">No review results yet.</div>
    @endif
</div>
@endsection
