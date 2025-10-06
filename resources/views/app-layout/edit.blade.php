@extends('layouts.engram')

@section('title', 'App layout — ' . $page->title)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('app-layout.index') }}" class="text-sm text-muted-foreground hover:text-foreground">← Назад до списку</a>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $page->title }}</h1>
            <p class="text-sm text-muted-foreground">slug: {{ $page->slug }}</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label for="locale" class="text-sm font-medium text-muted-foreground">Мова:</label>
            <input type="text" name="locale" id="locale" value="{{ $locale }}" class="w-24 rounded-lg border border-input bg-background px-3 py-2 text-sm" maxlength="5" />
            <button class="rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground">Застосувати</button>
        </form>
    </div>

    @if(session('status'))
        <div class="mb-6 rounded-xl border border-primary/40 bg-primary/10 px-4 py-3 text-sm text-primary">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <datalist id="block-area-options">
        <option value="header"></option>
        <option value="left"></option>
        <option value="right"></option>
        <option value="full"></option>
        <option value="main"></option>
    </datalist>
    <datalist id="block-type-options">
        <option value="header"></option>
        <option value="list"></option>
        <option value="formula"></option>
        <option value="chips"></option>
        <option value="hint"></option>
        <option value="table"></option>
        <option value="examples"></option>
        <option value="text"></option>
        <option value="html-snippet"></option>
    </datalist>

    <div class="space-y-6">
        @forelse($blocks as $block)
            <div class="rounded-2xl border border-border/60 bg-card p-5 shadow-soft">
                <form action="{{ route('app-layout.pages.blocks.update', [$page->slug, $block]) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <label class="space-y-1 text-sm font-medium text-muted-foreground">
                            <span>Locale</span>
                            <input type="text" name="locale" value="{{ old('locale', $block->locale) }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" maxlength="5" required />
                        </label>
                        <label class="space-y-1 text-sm font-medium text-muted-foreground">
                            <span>Area</span>
                            <input type="text" name="area" value="{{ old('area', $block->area) }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" list="block-area-options" required />
                        </label>
                        <label class="space-y-1 text-sm font-medium text-muted-foreground">
                            <span>Type</span>
                            <input type="text" name="type" value="{{ old('type', $block->type) }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" list="block-type-options" required />
                        </label>
                        <label class="space-y-1 text-sm font-medium text-muted-foreground">
                            <span>Порядок</span>
                            <input type="number" name="position" value="{{ old('position', $block->position) }}" min="0" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                        </label>
                    </div>
                    <label class="block space-y-2 text-sm font-medium text-muted-foreground">
                        <span>Заголовок блока</span>
                        <input type="text" name="label" value="{{ old('label', $block->label) }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                    </label>
                    <label class="block space-y-2 text-sm font-medium text-muted-foreground">
                        <span>JSON-вміст</span>
                        <textarea name="content" rows="10" class="w-full rounded-xl border border-input bg-background px-3 py-2 font-mono text-xs">{{ old('content', json_encode($block->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                        <p class="text-xs text-muted-foreground">Задайте структуру даних для блока. HTML допускається в значеннях рядків.</p>
                    </label>
                    <div class="flex items-center justify-between gap-4">
                        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Зберегти</button>
                        <button type="submit" form="delete-block-{{ $block->id }}" class="rounded-lg bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground" onclick="return confirm('Видалити блок?');">Видалити</button>
                    </div>
                </form>
                <form id="delete-block-{{ $block->id }}" action="{{ route('app-layout.pages.blocks.destroy', [$page->slug, $block]) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border/70 p-6 text-sm text-muted-foreground">
                Для вибраної мови ще немає жодного блока. Створіть перший за допомогою форми нижче.
            </div>
        @endforelse
    </div>

    <div class="mt-10 rounded-2xl border border-border/60 bg-card p-5 shadow-soft">
        <h2 class="text-lg font-semibold">Створити новий блок</h2>
        <form action="{{ route('app-layout.pages.blocks.store', $page->slug) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="space-y-1 text-sm font-medium text-muted-foreground">
                    <span>Locale</span>
                    <input type="text" name="locale" value="{{ old('locale', $locale) }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" maxlength="5" required />
                </label>
                <label class="space-y-1 text-sm font-medium text-muted-foreground">
                    <span>Area</span>
                    <input type="text" name="area" value="{{ old('area', 'left') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" list="block-area-options" required />
                </label>
                <label class="space-y-1 text-sm font-medium text-muted-foreground">
                    <span>Type</span>
                    <input type="text" name="type" value="{{ old('type', 'text') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" list="block-type-options" required />
                </label>
                <label class="space-y-1 text-sm font-medium text-muted-foreground">
                    <span>Порядок</span>
                    <input type="number" name="position" value="{{ old('position', $blocks->count() + 1) }}" min="0" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
                </label>
            </div>
            <label class="block space-y-2 text-sm font-medium text-muted-foreground">
                <span>Заголовок блока</span>
                <input type="text" name="label" value="{{ old('label') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm" />
            </label>
            <label class="block space-y-2 text-sm font-medium text-muted-foreground">
                <span>JSON-вміст</span>
                <textarea name="content" rows="10" class="w-full rounded-xl border border-input bg-background px-3 py-2 font-mono text-xs">{{ old('content', '{"body": []}') }}</textarea>
                <p class="text-xs text-muted-foreground">Приклад: {"title": "Коли вживати?", "items": ["пункт 1", "пункт 2"]}</p>
            </label>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Додати блок</button>
        </form>
    </div>
@endsection
