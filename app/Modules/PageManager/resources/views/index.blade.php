@extends('layouts.app')

@section('title', 'Сторінки — керування')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Сторінки</h1>
                <p class="text-sm text-gray-500">Керуйте сторінками, редагуйте та оновлюйте блоки контенту.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('pages.manage.export') }}" method="POST" class="inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                        Експорт в JSON
                    </button>
                </form>
                @if ($exportFileExists)
                    <a href="{{ route('pages.manage.export.view') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                        Переглянути JSON
                    </a>
                    <a href="{{ route('pages.manage.export.download') }}" class="inline-flex items-center rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-purple-700">
                        Скачати JSON
                    </a>
                @endif
                <a href="{{ route('pages.manage.create') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">+ Нова сторінка</a>
            </div>
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
                        $emptyCategoryCount = $categories->where('pages_count', 0)->count();
                        $editSelectedTags = $editFormHasErrors
                            ? (array) old('tags', $editingCategory?->tags->pluck('id')->all() ?? [])
                            : ($editingCategory?->tags->pluck('id')->all() ?? []);
                    @endphp

                    <div class="flex flex-col gap-6">
                        <div class="space-y-6">
                            @if ($editingCategory)
                                <section class="space-y-4 rounded-xl border border-blue-200 bg-blue-50/60 p-5">
                                    <header class="space-y-1">
                                        <h2 class="text-lg font-semibold">Редагувати категорію</h2>
                                        <p class="text-sm text-blue-800/80">Оновіть назву, slug або мову вибраної категорії.</p>
                                    </header>

                                    @if ($editingCategory->tags->isNotEmpty())
                                        <div class="rounded-lg border border-blue-300 bg-white p-4">
                                            <h3 class="mb-3 text-sm font-semibold text-gray-900">Прикріплені теги</h3>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($editingCategory->tags as $tag)
                                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-300 bg-blue-50 px-3 py-1.5 text-sm font-medium text-gray-700">
                                                        <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                                        </svg>
                                                        <span>{{ $tag->name }}</span>
                                                        @if (!empty($tag->category))
                                                            <span class="text-xs text-gray-500">({{ $tag->category }})</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('pages.manage.categories.update', $editingCategory) }}" class="space-y-4">
                                        @csrf
                                        @method('PUT')

                                        <div class="grid gap-4 sm:grid-cols-2">
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

                                        @include('page-manager::partials.tag-selector', [
                                            'label' => 'Теги категорії',
                                            'description' => 'Позначте категорію тегами, щоб поєднувати теорію з відповідними тестами.',
                                            'tagsByCategory' => $tagsByCategory,
                                            'selectedTagIds' => $editSelectedTags,
                                            'inputName' => 'tags[]',
                                            'idPrefix' => 'page-category-edit-' . $editingCategory->id,
                                        ])

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
                                $createTags = $editFormHasErrors ? [] : (array) old('tags', []);
                            @endphp

                            <section class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                                <header class="space-y-1">
                                    <h2 class="text-lg font-semibold">Нова категорія</h2>
                                    <p class="text-sm text-gray-600">Створіть категорію для групування сторінок.</p>
                                </header>

                                <form method="POST" action="{{ route('pages.manage.categories.store') }}" class="space-y-4">
                                    @csrf

                                    <div class="grid gap-4 sm:grid-cols-2">
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

                                    @include('page-manager::partials.tag-selector', [
                                        'label' => 'Теги категорії',
                                        'description' => 'Привʼяжіть нову категорію до наявних тегів, щоб поєднати її з теорією та тестами.',
                                        'tagsByCategory' => $tagsByCategory,
                                        'selectedTagIds' => $createTags,
                                        'inputName' => 'tags[]',
                                        'idPrefix' => 'page-category-create',
                                    ])

                                    <div class="flex justify-end">
                                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Створити категорію</button>
                                    </div>
                                </form>
                            </section>
                        </div>

                        <div class="space-y-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-lg font-semibold text-gray-900">Категорії сторінок</h2>
                                        <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                                            {{ $categories->count() }} всього
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                    <label class="relative block w-full sm:w-64">
                                        <span class="sr-only">Пошук категорій</span>
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                            </svg>
                                        </span>
                                        <input
                                            type="search"
                                            placeholder="Пошук категорій..."
                                            class="w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                            data-category-search-input
                                        />
                                    </label>
                                    <form
                                        action="{{ route('pages.manage.categories.destroy-empty') }}"
                                        method="POST"
                                        class="inline-flex"
                                        data-empty-categories-form
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="button"
                                            @if ($emptyCategoryCount === 0) disabled @endif
                                            class="inline-flex items-center rounded-xl border border-red-200 bg-red-50 px-3 py-1 text-sm font-medium text-red-600 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-200 focus:ring-offset-1 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100 disabled:text-gray-400"
                                            data-empty-modal-trigger
                                        >
                                            Видалити порожні ({{ $emptyCategoryCount }})
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="space-y-3 md:hidden">
                                @forelse ($categories as $category)
                                    @php
                                        $categoryHiddenText = $category->textBlocks
                                            ->map(function ($block) {
                                                return trim(strip_tags(($block->heading ?? '') . ' ' . ($block->body ?? '')));
                                            })
                                            ->filter()
                                            ->implode(' ');
                                        $categorySearchText = collect([
                                            $category->title,
                                            $category->slug,
                                            strtoupper($category->language),
                                            $category->pages_count,
                                            $categoryHiddenText,
                                        ])
                                            ->filter()
                                            ->implode(' ');
                                    @endphp
                                    <article
                                        class="space-y-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                                        data-category-search-item
                                        data-search-text="{{ \Illuminate\Support\Str::squish($categorySearchText) }}"
                                        data-search-hidden="{{ \Illuminate\Support\Str::squish($categoryHiddenText) }}"
                                    >
                                        <header class="flex flex-col gap-2">
                                            <h3 class="text-base font-semibold text-gray-900">
                                                <span data-search-highlight>{{ $category->title }}</span>
                                            </h3>
                                            <dl class="grid grid-cols-2 gap-3 text-xs text-gray-500">
                                                <div>
                                                    <dt class="font-medium uppercase tracking-wide text-gray-400">Slug</dt>
                                                    <dd class="text-sm text-gray-700" data-search-highlight>{{ $category->slug }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="font-medium uppercase tracking-wide text-gray-400">Мова</dt>
                                                    <dd class="text-sm text-gray-700" data-search-highlight>{{ strtoupper($category->language) }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="font-medium uppercase tracking-wide text-gray-400">Сторінок</dt>
                                                    <dd class="text-sm text-gray-700" data-search-highlight>{{ $category->pages_count }}</dd>
                                                </div>
                                            </dl>
                                        </header>

                                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3">
                                            <a href="{{ route('pages.manage.categories.blocks.index', $category) }}" class="inline-flex items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">Опис</a>
                                            <a href="{{ route('pages.manage.index', ['tab' => 'categories', 'edit' => $category->id]) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Редагувати</a>
                                            <form action="{{ route('pages.manage.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Видалити категорію?');" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100">Видалити</button>
                                            </form>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 hidden" data-search-snippet></p>
                                    </article>
                                @empty
                                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                                        Ще немає категорій. Додайте першу категорію, щоб згрупувати сторінки.
                                    </div>
                                @endforelse
                                @if ($categories->isNotEmpty())
                                    <div class="hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500" data-category-search-empty>
                                        Немає збігів для вибраного запиту.
                                    </div>
                                @endif
                            </div>

                            <div class="hidden md:block">
                                <div class="-mx-4 overflow-x-auto md:mx-0">
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
                                                @php
                                                    $categoryHiddenText = $category->textBlocks
                                                        ->map(function ($block) {
                                                            return trim(strip_tags(($block->heading ?? '') . ' ' . ($block->body ?? '')));
                                                        })
                                                        ->filter()
                                                        ->implode(' ');
                                                    $categorySearchText = collect([
                                                        $category->title,
                                                        $category->slug,
                                                        strtoupper($category->language),
                                                        $category->pages_count,
                                                        $categoryHiddenText,
                                                    ])
                                                        ->filter()
                                                        ->implode(' ');
                                                @endphp
                                                <tr
                                                    class="hover:bg-gray-50"
                                                    data-category-search-item
                                                    data-search-text="{{ \Illuminate\Support\Str::squish($categorySearchText) }}"
                                                    data-search-hidden="{{ \Illuminate\Support\Str::squish($categoryHiddenText) }}"
                                                >
                                                    <td class="px-4 py-3 font-medium text-gray-900">
                                                        <span data-search-highlight>{{ $category->title }}</span>
                                                        <div class="mt-1 text-xs text-gray-500 hidden" data-search-snippet></div>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-600" data-search-highlight>{{ $category->slug }}</td>
                                                    <td class="px-4 py-3 text-gray-600" data-search-highlight>{{ strtoupper($category->language) }}</td>
                                                    <td class="px-4 py-3 text-gray-600" data-search-highlight>{{ $category->pages_count }}</td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex justify-end gap-2">
                                                            <a href="{{ route('pages.manage.categories.blocks.index', $category) }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm text-indigo-700 hover:bg-indigo-100">Опис</a>
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
                                            @if ($categories->isNotEmpty())
                                                <tr class="hidden" data-category-search-empty>
                                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Немає збігів для вибраного запиту.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-gray-900">Усі сторінки</h2>
                                <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                                    {{ $pages->count() }} всього
                                </span>
                            </div>
                            <label class="relative block w-full sm:w-72">
                                <span class="sr-only">Пошук сторінок</span>
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                </span>
                                <input
                                    type="search"
                                    placeholder="Пошук сторінок..."
                                    class="w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    data-pages-search-input
                                />
                            </label>
                        </div>

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
                                    @php
                                        $pageHiddenText = collect([
                                            strip_tags($page->text ?? ''),
                                            $page->textBlocks->pluck('heading')->implode(' '),
                                            $page->textBlocks->pluck('body')->map(fn ($body) => strip_tags($body ?? ''))->implode(' '),
                                        ])
                                            ->filter()
                                            ->implode(' ');
                                        $pageSearchText = collect([
                                            $page->title,
                                            $page->slug,
                                            $page->category?->title,
                                            $page->category?->slug,
                                            $page->updated_at?->diffForHumans(),
                                            $pageHiddenText,
                                        ])
                                            ->filter()
                                            ->implode(' ');
                                    @endphp
                                    <tr
                                        class="hover:bg-gray-50"
                                        data-pages-search-item
                                        data-search-text="{{ \Illuminate\Support\Str::squish($pageSearchText) }}"
                                        data-search-hidden="{{ \Illuminate\Support\Str::squish($pageHiddenText) }}"
                                    >
                                        <td class="px-4 py-3 font-medium">
                                            @if ($page->category)
                                                <a href="{{ route('pages.show', [$page->category->slug, $page->slug]) }}" class="hover:underline" target="_blank" rel="noopener">
                                                    <span data-search-highlight>{{ $page->title }}</span>
                                                </a>
                                            @else
                                                <span data-search-highlight>{{ $page->title }}</span>
                                            @endif
                                            <div class="mt-1 text-xs text-gray-500 hidden" data-search-snippet></div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500" data-search-highlight>{{ $page->category?->title ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-500" data-search-highlight>{{ $page->slug }}</td>
                                        <td class="px-4 py-3 text-gray-500" data-search-highlight>{{ $page->updated_at?->diffForHumans() }}</td>
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
                                @if ($pages->isNotEmpty())
                                    <tr class="hidden" data-pages-search-empty>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Немає збігів для вибраного запиту.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($activeTab === 'categories')
        <div
            id="delete-empty-categories-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-empty-categories-title"
        >
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 id="delete-empty-categories-title" class="text-lg font-semibold text-gray-900">Видалити порожні категорії</h2>
                    <button type="button" class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600" data-empty-modal-close aria-label="Закрити">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4 px-6 py-5 text-sm text-gray-600">
                    <p>Ця дія видалить усі категорії, у яких немає сторінок. Відновити їх буде неможливо.</p>
                    <p>Ви впевнені, що хочете продовжити?</p>
                </div>
                <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100" data-empty-modal-close>
                        Скасувати
                    </button>
                    <button type="button" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-200 focus:ring-offset-1" data-empty-modal-confirm>
                        Видалити
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const HIGHLIGHT_CLASS = 'rounded bg-yellow-200 px-1 text-gray-900';

            const normalizeSearchValue = (value) => (value || '').toLowerCase().replace(/\s+/g, ' ').trim();

            const escapeHtml = (value) =>
                (value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

            const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\-]/g, '\$&');

            const highlightString = (text, query) => {
                const normalizedQuery = normalizeSearchValue(query);

                if (!normalizedQuery.length) {
                    return escapeHtml(text || '');
                }

                const safeText = text || '';
                const queryPattern = normalizedQuery
                    .split(' ')
                    .filter(Boolean)
                    .map((part) => escapeRegExp(part))
                    .join('\\s+');

                if (!queryPattern.length) {
                    return escapeHtml(safeText);
                }

                const regex = new RegExp(queryPattern, 'gi');
                let highlighted = '';
                let lastIndex = 0;
                let match;

                while ((match = regex.exec(safeText)) !== null) {
                    highlighted += escapeHtml(safeText.slice(lastIndex, match.index));
                    highlighted += `<mark class="${HIGHLIGHT_CLASS}">${escapeHtml(match[0])}</mark>`;
                    lastIndex = match.index + match[0].length;
                }

                highlighted += escapeHtml(safeText.slice(lastIndex));

                return highlighted;
            };

            const buildSnippet = (text, query, radius = 80) => {
                const normalizedQuery = normalizeSearchValue(query);

                if (!normalizedQuery.length) {
                    return '';
                }

                const safeText = text || '';
                const lowerText = safeText.toLowerCase();
                const matchIndex = lowerText.indexOf(normalizedQuery);

                if (matchIndex === -1) {
                    return '';
                }

                const start = Math.max(0, matchIndex - radius);
                const end = Math.min(safeText.length, matchIndex + normalizedQuery.length + radius);
                const snippet = safeText.slice(start, end).trim();
                const prefix = start > 0 ? '&hellip;' : '';
                const suffix = end < safeText.length ? '&hellip;' : '';

                return `${prefix}${highlightString(snippet, query)}${suffix}`;
            };

            const updateHighlightsForItem = (item, rawQuery, normalizedQuery) => {
                const targets = item.querySelectorAll('[data-search-highlight]');
                let hasVisibleMatch = false;

                targets.forEach((target) => {
                    if (!target.dataset.searchOriginalText) {
                        target.dataset.searchOriginalText = target.textContent.trim();
                    }

                    const originalText = target.dataset.searchOriginalText;

                    if (!normalizedQuery.length) {
                        target.textContent = originalText;
                        return;
                    }

                    const normalizedOriginal = normalizeSearchValue(originalText);

                    if (normalizedOriginal.includes(normalizedQuery)) {
                        target.innerHTML = highlightString(originalText, rawQuery);
                        hasVisibleMatch = true;
                    } else {
                        target.textContent = originalText;
                    }
                });

                return hasVisibleMatch;
            };

            const updateSnippetForItem = (item, rawQuery, normalizedQuery, hasVisibleMatch) => {
                const snippet = item.querySelector('[data-search-snippet]');

                if (!snippet) {
                    return false;
                }

                if (!normalizedQuery.length) {
                    snippet.classList.add('hidden');
                    snippet.textContent = '';
                    return false;
                }

                const hiddenText = item.dataset.searchHidden || '';
                const normalizedHidden = normalizeSearchValue(hiddenText);

                if (!hiddenText || !normalizedHidden.includes(normalizedQuery) || hasVisibleMatch) {
                    snippet.classList.add('hidden');
                    snippet.textContent = '';
                    return false;
                }

                const snippetContent = buildSnippet(hiddenText, rawQuery);

                if (!snippetContent) {
                    snippet.classList.add('hidden');
                    snippet.textContent = '';
                    return false;
                }

                snippet.innerHTML = snippetContent;
                snippet.classList.remove('hidden');
                return true;
            };

            const setupLiveSearch = ({ inputSelector, itemSelector, emptySelector }) => {
                const input = document.querySelector(inputSelector);
                if (!input) {
                    return;
                }

                const items = Array.from(document.querySelectorAll(itemSelector));
                const emptyState = emptySelector ? document.querySelector(emptySelector) : null;

                if (!items.length) {
                    input.disabled = true;
                    input.classList.add('cursor-not-allowed', 'bg-gray-100', 'text-gray-400');
                    input.placeholder = 'Немає даних для пошуку';
                    return;
                }

                const applyFilter = () => {
                    const rawQuery = (input.value || '').trim();
                    const normalizedQuery = normalizeSearchValue(rawQuery);
                    let visibleCount = 0;

                    items.forEach((item) => {
                        const text = normalizeSearchValue(item.dataset.searchText || item.textContent || '');
                        const shouldShow = normalizedQuery === '' || text.includes(normalizedQuery);
                        item.classList.toggle('hidden', !shouldShow);

                        if (shouldShow) {
                            visibleCount++;
                        }

                        const hasVisibleMatch = updateHighlightsForItem(item, rawQuery, normalizedQuery);
                        updateSnippetForItem(item, rawQuery, normalizedQuery, hasVisibleMatch);
                    });

                    if (emptyState) {
                        emptyState.classList.toggle('hidden', visibleCount !== 0);
                    }
                };

                input.addEventListener('input', applyFilter);
                applyFilter();
            };

            setupLiveSearch({
                inputSelector: '[data-category-search-input]',
                itemSelector: '[data-category-search-item]',
                emptySelector: '[data-category-search-empty]',
            });

            setupLiveSearch({
                inputSelector: '[data-pages-search-input]',
                itemSelector: '[data-pages-search-item]',
                emptySelector: '[data-pages-search-empty]',
            });

            const modal = document.getElementById('delete-empty-categories-modal');
            const form = document.querySelector('[data-empty-categories-form]');

            if (!modal || !form) {
                return;
            }

            const openButton = form.querySelector('[data-empty-modal-trigger]');
            const cancelButtons = modal.querySelectorAll('[data-empty-modal-close]');
            const confirmButton = modal.querySelector('[data-empty-modal-confirm]');

            const openModal = () => {
                if (modal.classList.contains('hidden') === false) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');

                const focusTarget = modal.querySelector('[data-empty-modal-confirm]');
                if (focusTarget) {
                    focusTarget.focus();
                }
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');

                if (openButton) {
                    openButton.focus();
                }
            };

            if (openButton) {
                openButton.addEventListener('click', function (event) {
                    event.preventDefault();

                    if (openButton.disabled) {
                        return;
                    }

                    openModal();
                });
            }

            cancelButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    closeModal();
                });
            });

            if (confirmButton) {
                confirmButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    closeModal();
                    form.requestSubmit();
                });
            }

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
@endpush
