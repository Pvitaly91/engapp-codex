@extends('layouts.app')

@section('title', 'Новий блок — ' . $page->title)

@section('content')
    @include('engram.pages.v2.manage.blocks.partials.form', [
        'heading' => 'Новий блок',
        'description' => 'Заповніть дані блока для сторінки «' . $page->title . '».',
        'formAction' => route('pages-v2.manage.blocks.store', $page),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити блок',
        'page' => $page,
        'block' => $block,
    ])
@endsection
