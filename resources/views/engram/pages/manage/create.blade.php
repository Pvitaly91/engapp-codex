@extends('layouts.app')

@section('title', 'Створити сторінку')

@section('content')
    @include('engram.pages.manage.partials.form', [
        'heading' => 'Нова сторінка',
        'description' => 'Створіть структуру сторінки та додайте блоки контенту.',
        'formAction' => route('pages.manage.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Створити сторінку',
        'page' => $page,
    ])
@endsection
