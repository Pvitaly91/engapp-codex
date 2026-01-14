@php
    $code = isset($exception) ? $exception->getStatusCode() : 400;
    $title = __('public.errors.4xx.title');
    $message = __('public.errors.4xx.text');
    $showSearch = true;
@endphp

@extends('errors.layout')
