@extends('layouts.app')

@section('title', 'Редагувати опис категорії — ' . $category->title)

@section('content')
    @include('page-manager::blocks.partials.form', [
        'heading' => 'Редагувати блок опису',
        'description' => 'Оновіть блок для категорії «' . $category->title . '».',
        'formAction' => route('pages.manage.categories.blocks.update', [$category, $block]),
        'formMethod' => 'PUT',
        'submitLabel' => 'Зберегти блок',
        'block' => $block,
        'entityTitle' => $category->title,
        'contextLabel' => 'Категорія',
        'backUrl' => route('pages.manage.categories.blocks.index', $category),
        'backLabel' => '← До опису категорії',
        'cancelUrl' => route('pages.manage.categories.blocks.index', $category),
    ])
@endsection
