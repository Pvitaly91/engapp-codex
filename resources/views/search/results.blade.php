@extends('layouts.engram')

@section('title', 'Пошук — Gramlyze')

@section('content')
<div class="space-y-6">
    <header class="space-y-2" data-animate>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">Результати пошуку</h1>
        <p class="text-sm text-muted-foreground">
            Знайдено для запиту: <span class="font-semibold text-foreground">"{{ e($query) }}"</span>
        </p>
    </header>

    @if($results->isEmpty())
        <div class="rounded-2xl border border-dashed border-border/70 bg-muted/30 p-12 text-center" data-animate data-animate-delay="80">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <p class="text-lg font-semibold text-foreground">Нічого не знайдено</p>
            <p class="mt-2 text-sm text-muted-foreground">Спробуйте інший запит або перегляньте каталог тестів</p>
            <a href="{{ route('catalog-tests.cards') }}" class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:shadow-lg">
                До каталогу
            </a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-animate data-animate-delay="100">
            @foreach($results as $item)
                <a href="{{ $item['url'] }}" class="group flex flex-col gap-3 rounded-2xl border border-border/70 bg-card p-5 shadow-soft transition hover:-translate-y-1 hover:border-primary/60 hover:shadow-xl">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-base font-semibold text-foreground group-hover:text-primary">{{ $item['title'] }}</h3>
                        <span class="shrink-0 rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                            {{ $item['type'] === 'page' ? 'Теорія' : 'Тест' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-primary">
                        <span>Перейти</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
