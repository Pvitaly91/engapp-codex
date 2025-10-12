@extends('layouts.app')

@section('title', 'Редагувати блок — ' . $page->title)

@section('content')
    @include('engram.pages.manage.blocks.partials.form', [
        'heading' => 'Редагування блока',
        'description' => 'Оновіть дані блока сторінки «' . $page->title . '».',
        'formAction' => route('pages.manage.blocks.update', [$page, $block]),
        'formMethod' => 'PUT',
        'submitLabel' => 'Зберегти блок',
        'page' => $page,
        'block' => $block,
    ])
@endsection
