@extends('layouts.app')

@section('title', 'Сторінки — керування')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Сторінки</h1>
                <p class="text-sm text-gray-500">Керуйте сторінками, редагуйте та оновлюйте блоки контенту.</p>
            </div>
            <a href="{{ route('pages.manage.create') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Нова сторінка</a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

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

        @php
            $activeTab = $activeTab ?? 'pages';
            $editingCategory = $editingCategory ?? null;
        @endphp

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex flex-wrap gap-2 px-4 py-3 text-sm font-medium text-gray-600">
                    @foreach (['pages' => 'Сторінки', 'categories' => 'Категорії'] as $tabKey => $tabLabel)
                        <a
                            href="{{ route('pages.manage.index', ['tab' => $tabKey]) }}"
                            class="rounded-xl px-3 py-1 transition @if ($activeTab === $tabKey) bg-white text-gray-900 shadow @else hover:text-gray-900 @endif"
                        >
                            {{ $tabLabel }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="p-4 sm:p-6">
                @if ($activeTab === 'categories')
                    @php
                        $editFormHasErrors = old('_method') === 'PUT';
                    @endphp

                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
                        <div class="space-y-6">
                            @if ($editingCategory)
                                <section class="space-y-4 rounded-xl border border-blue-200 bg-blue-50/60 p-5">
                                    <header class="space-y-1">
                                        <h2 class="text-lg font-semibold">Редагувати категорію</h2>
                                        <p class="text-sm text-blue-800/80">Оновіть назву, slug або мову вибраної категорії.</p>
                                    </header>

                                    <form method="POST" action="{{ route('pages.manage.categories.update', $editingCategory) }}" class="space-y-4">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid gap-4">
                                            <label class="space-y-2 text-sm">
                                                <span class="font-medium text-gray-700">Назва</span>
                                                <input type="text" name="title" value="{{ $editFormHasErrors ? old('title', $editingCategory->title) : $editingCategory->title }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </label>
                                            <label class="space-y-2 text-sm">
                                                <span class="font-medium text-gray-700">Slug</span>
                                                <input type="text" name="slug" value="{{ $editFormHasErrors ? old('slug', $editingCategory->slug) : $editingCategory->slug }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </label>
                                            <label class="space-y-2 text-sm">
                                                <span class="font-medium text-gray-700">Мова</span>
                                                <input type="text" name="language" value="{{ $editFormHasErrors ? old('language', $editingCategory->language) : $editingCategory->language }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                            </label>
                                        </div>

                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('pages.manage.index', ['tab' => 'categories']) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
                                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Оновити категорію</button>
                                        </div>
                                    </form>
                                </section>
                            @endif

                            @php
                                $createTitle = $editFormHasErrors ? '' : old('title');
                                $createSlug = $editFormHasErrors ? '' : old('slug');
                                $createLanguage = $editFormHasErrors ? 'uk' : old('language', 'uk');
                            @endphp

                            <section class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                                <header class="space-y-1">
                                    <h2 class="text-lg font-semibold">Нова категорія</h2>
                                    <p class="text-sm text-gray-600">Створіть категорію для групування сторінок.</p>
                                </header>

                                <form method="POST" action="{{ route('pages.manage.categories.store') }}" class="space-y-4">
                                    @csrf

                                    <div class="grid gap-4">
                                        <label class="space-y-2 text-sm">
                                            <span class="font-medium text-gray-700">Назва</span>
                                            <input type="text" name="title" value="{{ $createTitle }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        </label>
                                        <label class="space-y-2 text-sm">
                                            <span class="font-medium text-gray-700">Slug</span>
                                            <input type="text" name="slug" value="{{ $createSlug }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        </label>
                                        <label class="space-y-2 text-sm">
                                            <span class="font-medium text-gray-700">Мова</span>
                                            <input type="text" name="language" value="{{ $createLanguage }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                                        </label>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Створити категорію</button>
                                    </div>
                                </form>
                            </section>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Категорії сторінок</h2>
                                <span class="text-sm text-gray-500">{{ $categories->count() }} всього</span>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50 text-gray-600">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium">Назва</th>
                                            <th class="px-4 py-3 text-left font-medium">Slug</th>
                                            <th class="px-4 py-3 text-left font-medium">Мова</th>
                                            <th class="px-4 py-3 text-left font-medium">Сторінок</th>
                                            <th class="px-4 py-3 text-right font-medium">Дії</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse ($categories as $category)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 font-medium text-gray-900">{{ $category->title }}</td>
                                                <td class="px-4 py-3 text-gray-600">{{ $category->slug }}</td>
                                                <td class="px-4 py-3 text-gray-600">{{ strtoupper($category->language) }}</td>
                                                <td class="px-4 py-3 text-gray-600">{{ $category->pages_count }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="flex justify-end gap-2">
                                                        <a href="{{ route('pages.manage.index', ['tab' => 'categories', 'edit' => $category->id]) }}" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                                        <form action="{{ route('pages.manage.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Видалити категорію?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-sm text-red-600 hover:bg-red-100">Видалити</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Ще немає категорій. Додайте першу категорію, щоб згрупувати сторінки.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="-mx-4 -my-3 overflow-x-auto sm:mx-0 sm:my-0">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Назва</th>
                                    <th class="px-4 py-3 text-left font-medium">Категорія</th>
                                    <th class="px-4 py-3 text-left font-medium">Slug</th>
                                    <th class="px-4 py-3 text-left font-medium">Оновлено</th>
                                    <th class="px-4 py-3 text-right font-medium">Дії</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($pages as $page)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium">
                                            @if ($page->category)
                                                <a href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}" class="hover:underline" target="_blank" rel="noopener">{{ $page->title }}</a>
                                            @else
                                                {{ $page->title }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ $page->category?->title ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ $page->slug }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ $page->updated_at?->diffForHumans() }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('pages.manage.edit', $page) }}" class="rounded-lg border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Редагувати</a>
                                                <form action="{{ route('pages.manage.destroy', $page) }}" method="POST" onsubmit="return confirm('Видалити сторінку?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-sm text-red-600 hover:bg-red-100">Видалити</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Ще немає сторінок. Створіть першу сторінку.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
