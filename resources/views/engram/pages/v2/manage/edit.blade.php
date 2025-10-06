@extends('layouts.app')

@section('title', 'Редагувати сторінку')

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
        'heading' => 'Редагування: ' . $page->title,
        'description' => 'Оновіть контент блоків та метадані сторінки.',
        'formAction' => route('pages-v2.manage.update', $page),
        'formMethod' => 'PUT',
        'submitLabel' => 'Зберегти зміни',
        'page' => $page,
        'initialBlocks' => $normalizedBlocks,
        'defaultLocale' => $page->textBlocks->first()->locale ?? app()->getLocale(),
    ])
@endsection
