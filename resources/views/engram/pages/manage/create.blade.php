@extends('layouts.app')

@section('title', 'Створити сторінку')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-800">Створення нової сторінки</h1>
                <p class="text-slate-500">Додайте нову сторінку теорії з тегами.</p>
            </div>

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Будь ласка, виправте помилки:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('pages.manage.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf

                <div class="space-y-2">
                    <label for="title" class="block text-sm font-medium text-slate-700">Назва сторінки</label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >
                </div>

                <div class="space-y-2">
                    <label for="slug" class="block text-sm font-medium text-slate-700">Slug (URL)</label>
                    <input
                        id="slug"
                        name="slug"
                        type="text"
                        value="{{ old('slug') }}"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >
                    <p class="text-xs text-slate-400">Наприклад: present-simple</p>
                </div>

                <div class="space-y-2">
                    <label for="page_category_id" class="block text-sm font-medium text-slate-700">Категорія</label>
                    <select
                        id="page_category_id"
                        name="page_category_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >
                        <option value="">Без категорії</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('page_category_id') == $category->id)>
                                {{ $category->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="text" class="block text-sm font-medium text-slate-700">Текст</label>
                    <textarea
                        id="text"
                        name="text"
                        rows="4"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >{{ old('text') }}</textarea>
                </div>

                <x-tag-selector 
                    :allTags="$allTags" 
                    :selectedTags="old('tags', [])"
                    helpText="Виберіть теги, які відповідають змісту цієї сторінки."
                />

                <div class="flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Створити сторінку
                    </button>
                    <a
                        href="{{ route('pages.manage.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300"
                    >
                        Скасувати
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
