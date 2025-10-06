@php
    $columnValue = old('column', $block->column);
@endphp

<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $heading }}</h1>
            <p class="text-sm text-gray-500">{{ $description }}</p>
        </div>
        <a href="{{ route('pages-v2.manage.edit', $page) }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">← До блоків сторінки</a>
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

    <form method="POST" action="{{ $formAction }}" class="space-y-6">
        @csrf
        @if (($formMethod ?? 'POST') === 'PUT')
            @method('PUT')
        @endif

        <section class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="space-y-1">
                <h2 class="text-xl font-semibold">Дані блока</h2>
                <p class="text-sm text-gray-500">Сторінка: <span class="font-medium text-gray-700">{{ $page->title }}</span></p>
            </header>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium">Мова</span>
                    <input type="text" name="locale" maxlength="8" value="{{ old('locale', $block->locale) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Тип</span>
                    <input type="text" name="type" maxlength="32" value="{{ old('type', $block->type) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Колонка</span>
                    <select name="column" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="" @selected($columnValue === null || $columnValue === '')>—</option>
                        <option value="header" @selected($columnValue === 'header')>Шапка</option>
                        <option value="left" @selected($columnValue === 'left')>Ліва колонка</option>
                        <option value="right" @selected($columnValue === 'right')>Права колонка</option>
                    </select>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Порядок</span>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $block->sort_order) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">CSS клас</span>
                    <input type="text" name="css_class" value="{{ old('css_class', $block->css_class) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">Заголовок</span>
                    <input type="text" name="heading" value="{{ old('heading', $block->heading) }}" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2 md:col-span-2">
                    <span class="text-sm font-medium">Контент</span>
                    <textarea name="body" rows="8" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('body', $block->body) }}</textarea>
                </label>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('pages-v2.manage.edit', $page) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>
