@extends('layouts.app')

@section('title', 'Редагувати сторінку')

@section('content')
    <div class="py-8">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-800">Редагування сторінки</h1>
                <p class="text-slate-500">Оновіть інформацію про сторінку теорії та її теги.</p>
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

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('pages.manage.update', $page) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label for="title" class="block text-sm font-medium text-slate-700">Назва сторінки</label>
                    <input
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $page->title) }}"
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
                        value="{{ old('slug', $page->slug) }}"
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
                            <option value="{{ $category->id }}" @selected(old('page_category_id', $page->page_category_id) == $category->id)>
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
                    >{{ old('text', $page->text) }}</textarea>
                </div>

                <x-tag-selector 
                    :allTags="$allTags" 
                    :selectedTags="old('tags', $page->tags->pluck('id')->toArray())"
                    helpText="Виберіть теги, які відповідають змісту цієї сторінки."
                />

                <div class="flex flex-wrap gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                    >
                        Зберегти зміни
                    </button>
                    <a
                        href="{{ route('pages.manage.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300"
                    >
                        Скасувати
                    </a>
                    @if($page->category && $page->slug)
                        <a
                            href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}"
                            target="_blank"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300"
                        >
                            <i class="fa-solid fa-external-link mr-2"></i>Переглянути
                        </a>
                    @endif
                </div>
            </form>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('pages.manage.destroy', $page) }}" onsubmit="return confirm('Ви впевнені, що хочете видалити цю сторінку?')">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-800">Видалити сторінку</h3>
                            <p class="text-sm text-slate-500">Ця дія незворотна.</p>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-red-700"
                        >
                            Видалити
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
