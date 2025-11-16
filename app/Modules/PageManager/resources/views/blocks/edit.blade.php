@extends('layouts.app')

@section('title', 'Редагувати блок — ' . $page->title)

@section('content')
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
@endsection
