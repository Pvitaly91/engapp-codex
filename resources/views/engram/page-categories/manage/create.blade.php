@extends('layouts.app')

@section('title', 'Створити категорію')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-800">Створення нової категорії</h1>
                <p class="text-slate-500">Додайте нову категорію сторінок теорії з тегами.</p>
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

            <form method="POST" action="{{ route('page-categories.manage.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf

                <div class="space-y-2">
                    <label for="title" class="block text-sm font-medium text-slate-700">Назва категорії</label>
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
                    <p class="text-xs text-slate-400">Наприклад: tenses</p>
                </div>

                <div class="space-y-2">
                    <label for="language" class="block text-sm font-medium text-slate-700">Мова</label>
                    <select
                        id="language"
                        name="language"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring"
                    >
                        <option value="uk" @selected(old('language', 'uk') === 'uk')>Українська</option>
                        <option value="en" @selected(old('language') === 'en')>English</option>
                        <option value="pl" @selected(old('language') === 'pl')>Polski</option>
                    </select>
                </div>

                <x-tag-selector 
                    :allTags="$allTags" 
                    :selectedTags="old('tags', [])"
                    helpText="Виберіть теги, які відповідають темам цієї категорії."
                />

                <div class="flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Створити категорію
                    </button>
                    <a
                        href="{{ route('page-categories.manage.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300"
                    >
                        Скасувати
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
