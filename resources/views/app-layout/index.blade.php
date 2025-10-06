@extends('layouts.engram')

@section('title', 'App layout — текстові блоки')

@section('content')
    <div class="mb-8 space-y-2">
        <h1 class="text-3xl font-semibold tracking-tight">App layout</h1>
        <p class="text-muted-foreground max-w-2xl">Керуйте текстовими блоками теоретичних сторінок. Кожен запис відповідає одному блоку в шаблоні. Можна створювати декілька мовних версій сторінки.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($pages as $page)
            <a href="{{ route('app-layout.pages.edit', ['slug' => $page->slug]) }}" class="block rounded-2xl border border-border/60 bg-card p-5 shadow-soft transition hover:-translate-y-1 hover:border-primary/40">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $page->title }}</h2>
                        <p class="text-sm text-muted-foreground">{{ $page->slug }}</p>
                    </div>
                    <span class="rounded-xl bg-primary/10 px-3 py-1 text-xs font-medium text-primary">{{ $page->blocks_count }} блоків</span>
                </div>
                @if($page->locales->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($page->locales as $locale)
                            <span class="rounded-lg bg-muted px-2 py-1 text-xs font-medium text-muted-foreground">{{ strtoupper($locale) }}</span>
                        @endforeach
                    </div>
                @endif
            </a>
        @endforeach
    </div>
@endsection
