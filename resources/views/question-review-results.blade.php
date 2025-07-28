@extends('layouts.app')

@section('title', 'Review Results')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Question Review Results</h1>
    @if($results->count())
        <ul class="divide-y">
            @foreach($results as $result)
            <li class="py-3">
                <div class="font-semibold">{{ $result->question->question }}</div>
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
