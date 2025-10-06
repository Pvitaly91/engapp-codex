<div class="max-w-6xl mx-auto space-y-6" x-data="pageEditor({
    initialBlocks: @json($initialBlocks),
    defaultLocale: '{{ $defaultLocale }}',
})">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $heading }}</h1>
            <p class="text-muted-foreground">{{ $description }}</p>
        </div>
        <a href="{{ route('pages-v2.manage.index') }}" class="inline-flex items-center rounded-xl border border-border px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground">← До списку</a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">
            <div class="font-semibold mb-2">Перевірте форму:</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="rounded-xl border border-success/30 bg-success/10 p-4 text-sm text-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" class="space-y-8">
        @csrf
        @if ($formMethod === 'PUT')
            @method('PUT')
        @endif

        <section class="rounded-2xl border border-border bg-card p-6 shadow-soft space-y-6">
            <header>
                <h2 class="text-xl font-semibold">Основна інформація</h2>
                <p class="text-sm text-muted-foreground">Назва, slug та короткий опис для картки.</p>
            </header>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="space-y-2">
                    <span class="text-sm font-medium">Заголовок</span>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full rounded-xl border border-input bg-background px-3 py-2" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-medium">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required class="w-full rounded-xl border border-input bg-background px-3 py-2" />
                </label>
            </div>

            <label class="space-y-2 block">
                <span class="text-sm font-medium">Короткий опис</span>
                <textarea name="text" rows="3" class="w-full rounded-xl border border-input bg-background px-3 py-2">{{ old('text', $page->text) }}</textarea>
                <p class="text-xs text-muted-foreground">Використовується у списку сторінок.</p>
            </label>
        </section>

        <section class="space-y-4 rounded-2xl border border-border bg-card p-6 shadow-soft">
            <header class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Блоки сторінки</h2>
                    <p class="text-sm text-muted-foreground">Додайте інформаційні блоки, підзаголовок та інші елементи.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="inline-flex items-center rounded-xl border border-border px-3 py-2 text-sm font-medium" @click="addBlock('subtitle')">+ Підзаголовок</button>
                    <button type="button" class="inline-flex items-center rounded-xl bg-primary px-3 py-2 text-sm font-medium text-primary-foreground" @click="addBlock('box')">+ Додати блок</button>
                </div>
            </header>

            <template x-if="!blocks.length">
                <p class="text-sm text-muted-foreground">Поки що немає блоків. Додайте перший, щоб почати.</p>
            </template>

            <div class="space-y-4">
                <template x-for="(block, index) in blocks" :key="block.uid">
                    <div class="rounded-2xl border border-border/70 bg-background p-4 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-muted font-semibold" x-text="index + 1"></span>
                                <span class="font-medium" x-text="block.type === 'subtitle' ? 'Підзаголовок' : 'Контентний блок'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <button type="button" class="rounded-lg border border-border px-3 py-1" @click="duplicateBlock(index)">Дублювати</button>
                                <button type="button" class="rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-1 text-destructive" @click="removeBlock(index)">Видалити</button>
                            </div>
                        </div>

                        <input type="hidden" :name="`blocks[${index}][id]`" x-model="block.id">

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Мова</span>
                                <input type="text" maxlength="8" :name="`blocks[${index}][locale]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model="block.locale">
                            </label>
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Тип</span>
                                <input type="text" maxlength="32" :name="`blocks[${index}][type]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model="block.type">
                            </label>
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Колонка</span>
                                <select :name="`blocks[${index}][column]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model="block.column">
                                    <option value="">—</option>
                                    <option value="left">Ліва</option>
                                    <option value="right">Права</option>
                                </select>
                            </label>
                            <label class="space-y-1 text-sm">
                                <span class="font-medium">Сортування</span>
                                <input type="number" min="0" :name="`blocks[${index}][sort_order]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model.number="block.sort_order">
                            </label>
                            <label class="space-y-1 text-sm md:col-span-2">
                                <span class="font-medium">CSS клас</span>
                                <input type="text" :name="`blocks[${index}][css_class]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model="block.css_class">
                            </label>
                            <label class="space-y-1 text-sm md:col-span-2" x-show="block.type !== 'subtitle'">
                                <span class="font-medium">Заголовок</span>
                                <input type="text" :name="`blocks[${index}][heading]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model="block.heading">
                            </label>
                            <label class="space-y-1 text-sm md:col-span-2">
                                <span class="font-medium">Контент</span>
                                <textarea rows="6" :name="`blocks[${index}][body]`" class="w-full rounded-xl border border-input bg-background px-3 py-2" x-model="block.body"></textarea>
                            </label>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('pages-v2.manage.index') }}" class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground">Скасувати</a>
            <button type="submit" class="rounded-xl bg-primary px-6 py-2 text-sm font-semibold text-primary-foreground">
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
                blocks: (config.initialBlocks || []).map((block, index) => ({
                    ...block,
                    uid: block.uid || `block-${block.id || index}-${Date.now()}`,
                })),
                nextUid: Date.now(),
                addBlock(type = 'box') {
                    this.blocks.push({
                        id: null,
                        uid: `new-${this.nextUid++}`,
                        locale: this.defaultLocale,
                        type: type,
                        column: type === 'subtitle' ? '' : 'left',
                        heading: '',
                        css_class: type === 'subtitle' ? 'gw-subtitle' : '',
                        sort_order: (this.blocks.length + 1) * 10,
                        body: '',
                    });
                },
                removeBlock(index) {
                    this.blocks.splice(index, 1);
                },
                duplicateBlock(index) {
                    const original = this.blocks[index];
                    const clone = JSON.parse(JSON.stringify(original));
                    clone.id = null;
                    clone.uid = `copy-${this.nextUid++}`;
                    clone.sort_order = (this.blocks.length + 1) * 10;
                    this.blocks.splice(index + 1, 0, clone);
                },
            }));
        });
    </script>
@endonce
