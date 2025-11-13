@extends('layouts.app')

@section('title', 'Новий блок категорії — ' . $category->title)

@section('content')
    @include('engram.pages.manage.blocks.partials.form', [
        'heading' => 'Новий блок опису',
        'description' => 'Заповніть дані блока для категорії «' . $category->title . '».',
        'formAction' => route('pages.manage.categories.blocks.store', $category),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити блок',
        'block' => $block,
        'entityTitle' => $category->title,
        'contextLabel' => 'Категорія',
        'backUrl' => route('pages.manage.categories.blocks.index', $category),
        'backLabel' => '← До опису категорії',
        'cancelUrl' => route('pages.manage.categories.blocks.index', $category),
    ])
@endsection
