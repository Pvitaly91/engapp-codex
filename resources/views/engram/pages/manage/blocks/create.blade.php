@extends('layouts.app')

@section('title', 'Новий блок — ' . $page->title)

@section('content')
    @include('engram.pages.manage.blocks.partials.form', [
        'heading' => 'Новий блок',
        'description' => 'Заповніть дані блока для сторінки «' . $page->title . '».',
        'formAction' => route('pages.manage.blocks.store', $page),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити блок',
        'page' => $page,
        'block' => $block,
    ])
@endsection
