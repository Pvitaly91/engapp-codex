@extends('layouts.app')

@section('title', 'Редагувати категорію')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6 py-8">
        <div>
            <h1 class="text-2xl font-semibold">Редагування категорії</h1>
            <p class="text-sm text-gray-500">Оновіть назву, slug або мову вибраної категорії.</p>
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

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
            @if ($category->tags->isNotEmpty())
                <div class="mb-6 rounded-lg border border-blue-300 bg-blue-50 p-4">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Прикріплені теги</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($category->tags as $tag)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700">
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

            <form method="POST" action="{{ route('pages.manage.categories.update', $category) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="space-y-2 text-sm">
                        <span class="font-medium text-gray-700">Назва</span>
                        <input type="text" name="title" value="{{ old('title', $category->title) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                    </label>
                    <label class="space-y-2 text-sm">
                        <span class="font-medium text-gray-700">Slug</span>
                        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                    </label>
                    <label class="space-y-2 text-sm">
                        <span class="font-medium text-gray-700">Мова</span>
                        <input type="text" name="language" value="{{ old('language', $category->language) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                    </label>
                </div>

                @include('page-manager::partials.tag-selector', [
                    'label' => 'Теги категорії',
                    'description' => 'Позначте категорію тегами, щоб поєднувати теорію з відповідними тестами.',
                    'tagsByCategory' => $tagsByCategory,
                    'selectedTagIds' => (array) old('tags', $category->tags->pluck('id')->all()),
                    'inputName' => 'tags[]',
                    'idPrefix' => 'page-category-edit-' . $category->id,
                ])

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('pages.manage.index', ['tab' => 'categories']) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Оновити категорію</button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-red-100 bg-red-50 p-6 shadow">
            <form method="POST" action="{{ route('pages.manage.categories.destroy', $category) }}" onsubmit="return confirm('Видалити категорію та всі її сторінки?');">
                @csrf
                @method('DELETE')
                <h2 class="text-lg font-semibold text-red-700">Видалення категорії</h2>
                <p class="mt-1 text-sm text-red-600">Цю дію неможливо скасувати. Всі сторінки з цієї категорії також можуть бути видалені.</p>
                <button type="submit" class="mt-3 inline-flex items-center rounded-xl border border-red-300 px-5 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                    Видалити категорію
                </button>
            </form>
        </div>
    </div>
@endsection
