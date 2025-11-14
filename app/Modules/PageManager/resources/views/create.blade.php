@extends('layouts.app')

@section('title', 'Створити сторінку')

@section('content')
    @include('page-manager::partials.form', [
        'heading' => 'Нова сторінка',
        'description' => 'Створіть структуру сторінки та додайте блоки контенту.',
        'formAction' => route('pages.manage.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити сторінку',
        'page' => $page,
        'categories' => $categories,
    ])
@endsection
