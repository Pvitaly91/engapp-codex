@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    <article class="max-w-none space-y-4">{!! $page->text !!}</article>
@endsection
