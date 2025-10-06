@extends('layouts.app')

@section('title', 'Сторінки v2 — керування')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Сторінки v2</h1>
                <p class="text-muted-foreground">Керуйте сторінками, редагуйте та оновлюйте блоки контенту.</p>
            </div>
            <a href="{{ route('pages-v2.manage.create') }}" class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-soft">+ Нова сторінка</a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-success/30 bg-success/10 p-4 text-sm text-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
            <table class="min-w-full divide-y divide-border/70 text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Назва</th>
                        <th class="px-4 py-3 text-left font-medium">Slug</th>
                        <th class="px-4 py-3 text-left font-medium">Оновлено</th>
                        <th class="px-4 py-3 text-right font-medium">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    @forelse ($pages as $page)
                        <tr class="hover:bg-muted/40">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('pages-v2.show', $page->slug) }}" class="hover:underline" target="_blank" rel="noopener">{{ $page->title }}</a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $page->slug }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $page->updated_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('pages-v2.manage.edit', $page) }}" class="rounded-lg border border-border px-3 py-1 text-sm">Редагувати</a>
                                    <form action="{{ route('pages-v2.manage.destroy', $page) }}" method="POST" onsubmit="return confirm('Видалити сторінку?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-destructive/40 bg-destructive/10 px-3 py-1 text-sm text-destructive">Видалити</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-muted-foreground">Ще немає сторінок. Створіть першу сторінку.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
