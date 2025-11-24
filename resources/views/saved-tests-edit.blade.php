@extends('layouts.app')

@section('title', 'Редагувати тест')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Редагувати тест</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
        <form action="{{ route('saved-tests.update', $test->slug) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Назва тесту <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $test->name) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-4">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    Опис тесту
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $test->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="questionPicker(@js($questionSearchRoute), @js($questionRenderRoute), {
                savePayloadKey: '{{ $savePayloadKey }}'
            })" x-init="init()" class="space-y-4">
                @include('components.question-picker-modal', [
                    'questionCount' => $questions->count(),
                    'questionSearchRoute' => $questionSearchRoute,
                    'questionRenderRoute' => $questionRenderRoute,
                    'showShuffle' => $questions->count() > 1,
                    'seederSourceGroups' => $seederSourceGroups ?? [],
                    'sourcesByCategory' => $sourcesByCategory ?? [],
                    'levels' => $levels ?? [],
                    'tagsByCategory' => $tagsByCategory ?? [],
                    'aggregatedTagsByCategory' => $aggregatedTagsByCategory ?? [],
                ])

                <div id="questions-list" data-keep-visible="true" class="space-y-4">
                    @forelse($questions as $q)
                        @include('components.grammar-test-question-item', [
                            'question' => $q,
                            'savePayloadKey' => $savePayloadKey,
                            'manualInput' => false,
                            'autocompleteInput' => false,
                            'builderInput' => false,
                            'autocompleteRoute' => url('/api/search?lang=en'),
                            'checkOneInput' => false,
                            'number' => $loop->iteration,
                        ])
                    @empty
                        <div class="text-sm text-gray-500">Немає питань у цьому тесті. Додайте нові через "Додати питання".</div>
                    @endforelse
                </div>

                <input type="hidden" name="question_uuids" id="questions-order-input" value="{{ htmlentities(json_encode($questions->pluck($savePayloadKey))) }}">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-2xl font-semibold transition">
                    Зберегти зміни
                </button>
                <a href="{{ route('saved-tests.list') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-2xl font-semibold transition">
                    Скасувати
                </a>
            </div>
        </form>
    </div>
</div>

@include('components.question-delete-modal')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const manager = createQuestionsManager();
    manager.init();
});
</script>
<script>
@include('components.question-manager-scripts')
</script>
@endsection
