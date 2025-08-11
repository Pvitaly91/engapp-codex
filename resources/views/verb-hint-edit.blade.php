@extends('layouts.app')

@section('title', 'Edit Verb Hint')

@section('content')
<div class="max-w-md mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">Edit Verb Hint</h1>
    <form method="POST" action="{{ route('verb-hints.update', $verbHint->id) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="from" value="{{ $from }}">
        <div>
            <label class="block mb-1" for="hint">Verb Hint</label>
            <input type="text" id="hint" name="hint" value="{{ old('hint', $verbHint->option->option) }}" class="border rounded px-2 py-1 w-full">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection
