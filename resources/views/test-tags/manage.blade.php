@extends('layouts.app')

@section('title', 'Керування тегами тестів')

@section('content')
    <div class="py-8 space-y-6">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-3xl font-semibold text-slate-800">Керування тегами тестів</h1>
                    <p class="text-slate-500">Додавайте нові теги, змінюйте їх категорії та видаляйте зайві записи.</p>
                </div>
                <a
                    href="{{ route('test-tags.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50"
                >
                    Повернутися до списку
                </a>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-700">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)]">
                <div class="space-y-6">
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-800">Новий тег</h2>
                        <p class="text-sm text-slate-500">Заповніть форму, щоб створити тег і одразу прив'язати його до категорії.</p>
                        <form method="POST" action="{{ route('test-tags.store') }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="form" value="create">
                            <div class="space-y-1">
                                <label for="tag-name" class="text-sm font-medium text-slate-600">Назва тегу</label>
                                <input
                                    type="text"
                                    id="tag-name"
                                    name="name"
                                    value="{{ old('form') === 'create' ? old('name') : '' }}"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring"
                                    required
                                >
                            </div>
                            <div class="space-y-1">
                                <label for="tag-category" class="text-sm font-medium text-slate-600">Категорія</label>
                                <select
                                    id="tag-category"
                                    name="category"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring"
                                >
                                    <option value="">— Без категорії —</option>
                                    @foreach ($categories as $categoryName)
                                        <option value="{{ $categoryName }}" @selected(old('form') === 'create' && old('category') === $categoryName)>{{ $categoryName }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-400">Або створіть нову категорію:</p>
                                <input
                                    type="text"
                                    name="new_category"
                                    value="{{ old('form') === 'create' ? old('new_category') : '' }}"
                                    placeholder="Нова категорія"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring"
                                >
                            </div>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring"
                            >
                                Створити тег
                            </button>
                        </form>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-800">Категорії</h2>
                        <p class="text-sm text-slate-500">Перейменуйте або видаліть категорії разом з їх тегами.</p>
                        <div class="mt-4 space-y-4">
                            @forelse ($tagsByCategory as $categoryName => $tags)
                                @continue($categoryName === null)
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-700">{{ $categoryName }}</h3>
                                            <p class="text-xs text-slate-400">Тегів: {{ $tags->count() }}</p>
                                        </div>
                                        @php($renameFormKey = 'rename-' . md5($categoryName))
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                            <form method="POST" action="{{ route('test-tags.categories.update') }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="form" value="{{ $renameFormKey }}">
                                                <input type="hidden" name="original_category" value="{{ $categoryName }}">
                                                <input
                                                    type="text"
                                                    name="new_name"
                                                    value="{{ old('form') === $renameFormKey ? old('new_name') : '' }}"
                                                    placeholder="Нова назва"
                                                    class="w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring"
                                                    required
                                                >
                                                <button type="submit" class="rounded-lg border border-blue-600 px-4 py-2 text-sm font-medium text-blue-600 transition hover:bg-blue-50">Перейменувати</button>
                                            </form>
                                            <form
                                                method="POST"
                                                action="{{ route('test-tags.categories.destroy') }}"
                                                onsubmit="return confirm('Видалити категорію «{{ $categoryName }}» та всі її теги?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="category" value="{{ $categoryName }}">
                                                <button type="submit" class="rounded-lg border border-rose-500 px-4 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">Видалити</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Категорії відсутні. Створіть тег з новою категорією.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-slate-800">Список тегів</h2>
                        <span class="text-sm text-slate-400">Всього: {{ $tagsByCategory->sum(fn ($tags) => $tags->count()) }}</span>
                    </div>
                    <div class="space-y-6">
                        @foreach ($tagsByCategory as $groupName => $tags)
                            <section class="space-y-3">
                                <header class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-slate-700">{{ $groupName ?? 'Без категорії' }}</h3>
                                    <span class="text-xs text-slate-400">{{ $tags->count() }} тег(ів)</span>
                                </header>
                                <div class="space-y-3">
                                    @foreach ($tags as $tag)
                                        @php($updateFormKey = 'update-' . $tag->id)
                                        @php($selectedCategory = old('form') === $updateFormKey ? old('category') : $tag->category)
                                        <div class="rounded-lg border border-slate-200 p-4 shadow-sm">
                                            <form method="POST" action="{{ route('test-tags.update', $tag) }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="form" value="{{ $updateFormKey }}">
                                                <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center">
                                                    <div class="w-full lg:w-64">
                                                        <label class="block text-xs font-medium text-slate-500">Назва</label>
                                                        <input
                                                            type="text"
                                                            name="name"
                                                            value="{{ old('form') === $updateFormKey ? old('name') : $tag->name }}"
                                                            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring"
                                                            required
                                                        >
                                                    </div>
                                                    <div class="w-full lg:w-64">
                                                        <label class="block text-xs font-medium text-slate-500">Категорія</label>
                                                        <select
                                                            name="category"
                                                            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring"
                                                        >
                                                            <option value="" @selected($selectedCategory === null || $selectedCategory === '')>— Без категорії —</option>
                                                            @foreach ($categories as $categoryOption)
                                                                <option value="{{ $categoryOption }}" @selected($selectedCategory === $categoryOption)>{{ $categoryOption }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input
                                                            type="text"
                                                            name="new_category"
                                                            value="{{ old('form') === $updateFormKey ? old('new_category') : '' }}"
                                                            placeholder="Нова категорія"
                                                            class="mt-2 w-full rounded-lg border border-dashed border-slate-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring"
                                                        >
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring">Зберегти</button>
                                                    <button
                                                        type="submit"
                                                        form="delete-tag-{{ $tag->id }}"
                                                        class="rounded-lg border border-rose-500 px-4 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                                                        onclick="return confirm('Видалити тег «{{ $tag->name }}»?')"
                                                    >
                                                        Видалити
                                                    </button>
                                                </div>
                                            </form>
                                            <form method="POST" action="{{ route('test-tags.destroy', $tag) }}" id="delete-tag-{{ $tag->id }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
