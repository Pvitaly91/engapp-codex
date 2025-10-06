@php
    $initialBlocksJson = json_encode($initialBlocks, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $initialBlocksEncoded = $initialBlocksJson === false ? '' : base64_encode($initialBlocksJson);
@endphp

<div class="max-w-6xl mx-auto space-y-6" x-data="pageEditor({
    defaultLocale: '{{ $defaultLocale }}',
})" x-init="bootstrap('{{ $initialBlocksEncoded }}')">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $heading }}</h1>
            <p class="text-sm text-gray-500">{{ $description }}</p>
        </div>
        <a href="{{ route('pages-v2.manage.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">← До списку</a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <div class="font-semibold mb-2">Перевірте форму:</div>
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

    <form method="POST" action="{{ $formAction }}" class="space-y-8">
        @csrf
        @if ($formMethod === 'PUT')
            @method('PUT')
        @endif

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header>
                <h2 class="text-xl font-semibold">Основна інформація</h2>
                <p class="text-sm text-gray-500">Назва, slug та короткий опис для картки.</p>
            </header>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium">Заголовок</span>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" />
                </label>
            </div>

            <label class="space-y-2 block">
                <span class="text-sm font-medium">Короткий опис</span>
                <textarea name="text" rows="3" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">{{ old('text', $page->text) }}</textarea>
                <p class="text-xs text-gray-500">Використовується у списку сторінок.</p>
            </label>
        </section>

        <section class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow">
            <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Блоки сторінки</h2>
                    <p class="text-sm text-gray-500">Додайте інформаційні блоки, підзаголовок та інші елементи.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="inline-flex items-center rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100" @click="addBlock('subtitle')">+ Підзаголовок</button>
                    <button type="button" class="inline-flex items-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="addBlock('box')">+ Додати блок</button>
                </div>
            </header>

            <template x-if="!blocks.length">
                <p class="text-sm text-gray-500">Поки що немає блоків. Додайте перший, щоб почати.</p>
            </template>

            <div class="space-y-4" x-cloak>
                <template x-for="(block, index) in blocks" :key="block.uid">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white font-semibold text-gray-700" x-text="index + 1"></span>
                                <span class="font-medium text-gray-700" x-text="block.type === 'subtitle' ? 'Підзаголовок' : 'Контентний блок'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <button type="button" class="rounded-lg border border-gray-300 px-3 py-1 hover:bg-white" @click="duplicateBlock(index)">Дублювати</button>
                                <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1 text-red-600 hover:bg-red-100" @click="removeBlock(index)">Видалити</button>
                            </div>
                        </div>

                        <input type="hidden" :name="`blocks[${index}][id]`" x-model="block.id">

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Мова</span>
                                <input type="text" maxlength="8" :name="`blocks[${index}][locale]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model="block.locale">
                            </label>
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Тип</span>
                                <input type="text" maxlength="32" :name="`blocks[${index}][type]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model="block.type">
                            </label>
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Колонка</span>
                                <select :name="`blocks[${index}][column]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model="block.column">
                                    <option value="">—</option>
                                    <option value="header">Шапка</option>
                                    <option value="left">Ліва</option>
                                    <option value="right">Права</option>
                                </select>
                            </label>
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Сортування</span>
                                <input type="number" min="0" :name="`blocks[${index}][sort_order]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model.number="block.sort_order">
                            </label>
                            <label class="space-y-1 text-sm md:col-span-2">
                                <span class="font-medium">CSS клас</span>
                                <input type="text" :name="`blocks[${index}][css_class]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model="block.css_class">
                            </label>
                            <label class="space-y-1 text-sm md:col-span-2" x-show="block.type !== 'subtitle'">
                                <span class="font-medium">Заголовок</span>
                                <input type="text" :name="`blocks[${index}][heading]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model="block.heading">
                            </label>
                            <label class="space-y-1 text-sm md:col-span-2">
                                <span class="font-medium">Контент</span>
                                <textarea rows="6" :name="`blocks[${index}][body]`" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200" x-model="block.body"></textarea>
                            </label>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('pages-v2.manage.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">Скасувати</a>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pageEditor', (config) => ({
                defaultLocale: config.defaultLocale || 'uk',
                blocks: [],
                nextUid: Date.now(),
                bootstrap(encoded) {
                    let parsed = [];

                    if (encoded) {
                        try {
                            parsed = JSON.parse(atob(encoded));
                        } catch (error) {
                            console.warn('Не вдалося завантажити блоки сторінки.', error);
                        }
                    }

                    if (! Array.isArray(parsed)) {
                        parsed = [];
                    }

                    this.blocks = parsed.map((block, index) => this.makeBlock(block, index));
                },
                makeBlock(block, index) {
                    if (typeof block !== 'object' || block === null) {
                        block = {};
                    }

                    const type = block.type ? block.type : 'box';
                    const orderIndex = typeof index === 'number' ? index : this.blocks.length;
                    const hasOwn = (key) => Object.prototype.hasOwnProperty.call(block, key);

                    const blockId = hasOwn('id') && block.id !== undefined ? block.id : null;
                    const locale = block.locale ? block.locale : this.defaultLocale;
                    const column = hasOwn('column') ? block.column : (type === 'subtitle' ? 'header' : 'left');
                    const heading = hasOwn('heading') ? block.heading : '';
                    const cssClass = hasOwn('css_class') ? block.css_class : (type === 'subtitle' ? 'gw-subtitle' : '');
                    const sortOrder = hasOwn('sort_order') && Number.isFinite(Number(block.sort_order))
                        ? Number(block.sort_order)
                        : (orderIndex + 1) * 10;
                    const body = hasOwn('body') ? block.body : '';
                    const uid = hasOwn('uid') && block.uid
                        ? block.uid
                        : `block-${blockId !== null ? blockId : orderIndex}-${Date.now()}-${this.nextUid++}`;

                    return {
                        id: blockId,
                        uid: uid,
                        locale: locale,
                        type: type,
                        column: column,
                        heading: heading,
                        css_class: cssClass,
                        sort_order: sortOrder,
                        body: body,
                    };
                },
                addBlock(type = 'box') {
                    this.blocks.push(
                        this.makeBlock(
                            {
                                id: null,
                                uid: null,
                                type: type,
                                column: type === 'subtitle' ? 'header' : undefined,
                                css_class: type === 'subtitle' ? 'gw-subtitle' : undefined,
                                sort_order: (this.blocks.length + 1) * 10,
                                body: '',
                            },
                            this.blocks.length,
                        ),
                    );
                },
                removeBlock(index) {
                    this.blocks.splice(index, 1);
                },
                duplicateBlock(index) {
                    const original = this.blocks[index];
                    const clone = this.makeBlock(
                        Object.assign({}, original, {
                            id: null,
                            uid: null,
                            sort_order: (this.blocks.length + 1) * 10,
                        }),
                        index + 1,
                    );
                    this.blocks.splice(index + 1, 0, clone);
                },
            }));
        });
    </script>
@endonce
