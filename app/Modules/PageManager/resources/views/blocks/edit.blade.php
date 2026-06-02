@extends('layouts.app')

@section('title', 'Редагувати блок — ' . $page->title)

@section('content')
    <div class="mx-auto max-w-6xl space-y-8">
        @if ($block->tags->isNotEmpty())
            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Прикріплені теги</h2>
                    <p class="text-sm text-gray-600">Теги, призначені для цього блоку</p>
                </header>
                <div class="flex flex-wrap gap-2">
                    @foreach ($block->tags as $tag)
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
            </section>
        @endif

        @include('page-manager::blocks.partials.form', [
            'heading' => 'Редагування блока',
            'description' => 'Оновіть дані блока сторінки «' . $page->title . '».',
            'formAction' => route('pages.manage.blocks.update', [$page, $block]),
            'formMethod' => 'PUT',
            'submitLabel' => 'Зберегти блок',
            'block' => $block,
            'entityTitle' => $page->title,
            'contextLabel' => 'Сторінка',
            'backUrl' => route('pages.manage.edit', $page),
            'backLabel' => '← До блоків сторінки',
            'cancelUrl' => route('pages.manage.edit', $page),
        ])
    </div>
@endsection
