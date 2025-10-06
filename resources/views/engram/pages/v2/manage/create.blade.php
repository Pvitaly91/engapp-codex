@extends('layouts.engram')

@section('title', 'Створити сторінку')

@section('content')
    @php
        $normalizedBlocks = collect($blockPayloads ?? [])->map(function ($block, $index) {
            return [
                'id' => $block['id'] ?? null,
                'locale' => $block['locale'] ?? 'uk',
                'type' => $block['type'] ?? 'box',
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? '',
                'css_class' => $block['css_class'] ?? '',
                'sort_order' => $block['sort_order'] ?? (($index + 1) * 10),
                'body' => $block['body'] ?? '',
            ];
        })->values()->toArray();
    @endphp

    @include('engram.pages.v2.manage.partials.form', [
        'heading' => 'Нова сторінка',
        'description' => 'Створіть структуру сторінки та додайте блоки контенту.',
        'formAction' => route('pages-v2.manage.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити сторінку',
        'page' => $page,
        'initialBlocks' => $normalizedBlocks,
        'defaultLocale' => app()->getLocale(),
    ])
@endsection
