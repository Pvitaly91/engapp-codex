@php
    $category = $category ?? null;
    $active = $active ?? false;
    $lookup = \Illuminate\Support\Str::lower(trim(($category->slug ?? '') . ' ' . ($category->title ?? '')));

    $icon = 'book';

    if (\Illuminate\Support\Str::contains($lookup, [
        'basic-grammar',
        'base',
        'baza',
        'баз',
        'grammar',
        'грамат',
    ])) {
        $icon = 'book-open';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'pronoun',
        'zaim',
        'займен',
        'demonstrative',
        'вказів',
    ])) {
        $icon = 'users';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'imennyky',
        'noun',
        'article',
        'kilkist',
        'quantity',
        'quantifier',
        'артикл',
        'кільк',
        'іменник',
    ])) {
        $icon = 'stack';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'question',
        'negation',
        'pyt',
        'zaperech',
        'пит',
        'запереч',
    ])) {
        $icon = 'message';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'adjective',
        'adverb',
        'prykmet',
        'prysliv',
        'прикмет',
        'прислів',
    ])) {
        $icon = 'sliders';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'some-any',
        'some',
        'any',
    ])) {
        $icon = 'split';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'passive',
        'пасив',
    ])) {
        $icon = 'repeat';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'modal',
        'модал',
    ])) {
        $icon = 'shield';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'future',
        'maibut',
        'майбут',
    ])) {
        $icon = 'spark';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'tense',
        'час',
    ])) {
        $icon = 'clock';
    } elseif (\Illuminate\Support\Str::contains($lookup, [
        'possession',
        'have got',
        'волод',
    ])) {
        $icon = 'key';
    }

    $stroke = $active ? '#ffffff' : 'var(--accent)';
@endphp

<svg
    viewBox="0 0 24 24"
    fill="none"
    class="h-5 w-5"
    style="color: {{ $stroke }};"
    aria-hidden="true"
>
    @if($icon === 'book-open')
        <path d="M4.75 6.75A2.75 2.75 0 0 1 7.5 4.25H12v14.5H7.75A2.75 2.75 0 0 0 5 21.5V7A.25.25 0 0 1 5.25 6.75H12Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
        <path d="M19.25 6.75A2.75 2.75 0 0 0 16.5 4.25H12v14.5h4.25A2.75 2.75 0 0 1 19 21.5V7a.25.25 0 0 0-.25-.25H12Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
        <path d="M8 8h2.25M13.75 8H16M8 11h2.25M13.75 11H16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
    @elseif($icon === 'users')
        <path d="M9 10.25a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM15.5 11.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" stroke="currentColor" stroke-width="1.75" />
        <path d="M4.75 18.25a4.25 4.25 0 0 1 8.5 0M13 18.25a3.5 3.5 0 0 1 7 0" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
    @elseif($icon === 'stack')
        <path d="M12 4.5 5 8l7 3.5L19 8l-7-3.5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
        <path d="M5 11.5 12 15l7-3.5M5 15.25 12 18.75l7-3.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
    @elseif($icon === 'message')
        <path d="M6 6.25h12A1.75 1.75 0 0 1 19.75 8v7A1.75 1.75 0 0 1 18 16.75h-5.25l-3.75 2.75v-2.75H6A1.75 1.75 0 0 1 4.25 15V8A1.75 1.75 0 0 1 6 6.25Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
        <path d="M9.5 9.75a2.25 2.25 0 1 1 4.31 1l-.43.95a1.75 1.75 0 0 1-1.6 1.05h-.03M12 15.25h.01" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
    @elseif($icon === 'sliders')
        <path d="M6 5.25v13.5M12 5.25v13.5M18 5.25v13.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
        <path d="M4.5 9h3M10.5 14h3M16.5 8h3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
        <circle cx="6" cy="9" r="1.75" fill="white" stroke="currentColor" stroke-width="1.5" />
        <circle cx="12" cy="14" r="1.75" fill="white" stroke="currentColor" stroke-width="1.5" />
        <circle cx="18" cy="8" r="1.75" fill="white" stroke="currentColor" stroke-width="1.5" />
    @elseif($icon === 'split')
        <path d="M12 5v14" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
        <path d="M9 8.25H7.5a2.25 2.25 0 1 0 0 4.5H9a2.25 2.25 0 1 1 0 4.5H5.75M15 8.25h1.5a2.25 2.25 0 0 1 0 4.5H15a2.25 2.25 0 1 0 0 4.5h3.25" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
    @elseif($icon === 'repeat')
        <path d="M7 8.5h8.75a2.75 2.75 0 0 1 2.75 2.75V12M17.5 9.75 19 11.25l1.5-1.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M17 15.5H8.25A2.75 2.75 0 0 1 5.5 12.75V12M6.5 14.25 5 12.75l-1.5 1.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
    @elseif($icon === 'shield')
        <path d="M12 4.5 18 7v4.75c0 3.34-2.17 6.35-6 7.75-3.83-1.4-6-4.41-6-7.75V7l6-2.5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
        <path d="M9.25 12.25 11 14l3.75-3.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
    @elseif($icon === 'spark')
        <path d="M12 4.75 13.7 9l4.55.3-3.5 2.9 1.14 4.3L12 14.1l-3.89 2.4 1.14-4.3-3.5-2.9L10.3 9 12 4.75Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
    @elseif($icon === 'clock')
        <circle cx="12" cy="12" r="7.25" stroke="currentColor" stroke-width="1.75" />
        <path d="M12 8v4.25l3 1.75" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
    @elseif($icon === 'key')
        <path d="M14.75 9.25a3.25 3.25 0 1 0-2.99 4.53H13v2h2v1.75h2v-2h1.75v-2H15.9a3.23 3.23 0 0 0-1.15-4.28Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" />
        <circle cx="11.5" cy="9.25" r="1" fill="currentColor" />
    @else
        <path d="M6.25 5.25h9.5A2.25 2.25 0 0 1 18 7.5v9a2.25 2.25 0 0 1-2.25 2.25h-9.5A2.25 2.25 0 0 1 4 16.5v-9a2.25 2.25 0 0 1 2.25-2.25Z" stroke="currentColor" stroke-width="1.75" />
        <path d="M8 9h8M8 12h8M8 15h5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
    @endif
</svg>
