@extends('layouts.app')

@section('title', 'Редагувати сторінку')

@section('content')
    @include('engram.pages.v2.manage.partials.form', [
        'heading' => 'Редагування: ' . $page->title,
        'description' => 'Оновіть контент блоків та метадані сторінки.',
        'formAction' => route('pages-v2.manage.update', $page),
        'formMethod' => 'PUT',
        'submitLabel' => 'Зберегти зміни',
        'page' => $page,
        'initialBlocks' => $blockPayloads,
        'defaultLocale' => data_get(collect($blockPayloads)->first(), 'locale') ?? ($page->textBlocks->first()->locale ?? app()->getLocale()),
    ])
@endsection
