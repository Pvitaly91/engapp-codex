@extends('layouts.engram')

@section('title', 'Pages v2')

@section('content')
    @include('engram.pages.partials.page-grid', ['pages' => $pages, 'targetRoute' => $targetRoute])
@endsection
