@extends('layouts.engram')

@section('title', 'Теорія')

@section('content')
    @include('engram.pages.partials.page-grid', ['pages' => $pages, 'targetRoute' => $targetRoute])
@endsection
