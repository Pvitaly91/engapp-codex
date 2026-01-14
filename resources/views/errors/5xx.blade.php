@php
    $code = isset($exception) ? $exception->getStatusCode() : 500;
    $title = __('public.errors.5xx.title');
    $message = __('public.errors.5xx.text');
@endphp

@extends('errors.layout')
