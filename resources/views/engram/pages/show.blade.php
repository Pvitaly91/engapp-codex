@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    <article class="max-w-none space-y-4">
        @include($page->view)
    </article>
@endsection
