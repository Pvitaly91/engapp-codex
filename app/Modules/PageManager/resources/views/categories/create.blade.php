@extends('layouts.app')

@section('title', 'Створити категорію')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6 py-8">
        <div>
            <h1 class="text-2xl font-semibold">Нова категорія</h1>
            <p class="text-sm text-gray-500">Створіть категорію для групування сторінок.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                <div class="mb-2 font-semibold">Перевірте форму:</div>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
            <form method="POST" action="{{ route('pages.manage.categories.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="space-y-2 text-sm">
                        <span class="font-medium text-gray-700">Назва</span>
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                    </label>
                    <label class="space-y-2 text-sm">
                        <span class="font-medium text-gray-700">Slug</span>
                        <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                    </label>
                    <label class="space-y-2 text-sm">
                        <span class="font-medium text-gray-700">Мова</span>
                        <input type="text" name="language" value="{{ old('language', 'uk') }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                    </label>
                </div>

                @include('page-manager::partials.tag-selector', [
                    'label' => 'Теги категорії',
                    'description' => 'Привʼяжіть нову категорію до наявних тегів, щоб поєднати її з теорією та тестами.',
                    'tagsByCategory' => $tagsByCategory,
                    'selectedTagIds' => (array) old('tags', []),
                    'inputName' => 'tags[]',
                    'idPrefix' => 'page-category-create',
                ])

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('pages.manage.index', ['tab' => 'categories']) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Створити категорію</button>
                </div>
            </form>
        </div>
    </div>
@endsection
