<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $heading }}</h1>
            <p class="text-sm text-gray-500">{{ $description }}</p>
        </div>
        <a href="{{ route('pages-v2.manage.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">← До списку</a>
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

    @if (session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-6">
        @csrf
        @if (($formMethod ?? 'POST') === 'PUT')
            @method('PUT')
        @endif

        <section class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header>
                <h2 class="text-xl font-semibold">Основна інформація</h2>
                <p class="text-sm text-gray-500">Назва, slug та короткий опис сторінки.</p>
            </header>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium">Заголовок</span>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
            </div>

            <label class="block space-y-2">
                <span class="text-sm font-medium">Короткий опис</span>
                <textarea name="text" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('text', $page->text) }}</textarea>
                <p class="text-xs text-gray-500">Використовується у списку сторінок.</p>
            </label>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('pages-v2.manage.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>
