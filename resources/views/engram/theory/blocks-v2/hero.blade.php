{{-- Hero block is handled in the main template show-v2.blade.php --}}
{{-- This template exists as a fallback for the original hero block type --}}
@php($data = $data ?? json_decode($block->body ?? '[]', true) ?? [])
{{-- Hero content is rendered in the page header area --}}
