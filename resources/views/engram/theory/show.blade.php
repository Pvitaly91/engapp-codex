@extends('layouts.engram')

@section('title', $page->title)

@section('content')
    @php($blocks = $page->textBlocks ?? collect())
    @php($breadcrumbs = $breadcrumbs ?? [])

    @foreach($blocks as $block)
        @includeIf('engram.theory.blocks.' . $block->type, [
            'page' => $page,
            'block' => $block,
            'data' => json_decode($block->body ?? '[]', true),
            'breadcrumbs' => $breadcrumbs,
        ])
    @endforeach
@endsection
