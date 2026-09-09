@php
    $decodeSocialValue = static fn (string $value): string => html_entity_decode(
        trim($value),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );
    $socialTitle = $decodeSocialValue(
        $__env->yieldContent($__env->hasSection('social_title') ? 'social_title' : 'title', __('public.meta.title'))
    );
    $socialDescription = $decodeSocialValue(
        $__env->yieldContent($__env->hasSection('social_description') ? 'social_description' : 'meta_description', __('public.meta.description'))
    );
    $socialUrl = trim($__env->yieldContent('social_url', url()->current()));
    $socialImage = trim($__env->yieldContent('social_image', asset('images/social/gramlyze-og.png')));
    $socialImageAlt = $decodeSocialValue(
        $__env->yieldContent('social_image_alt', 'Gramlyze — English grammar made clear')
    );
    $socialType = trim($__env->yieldContent('social_type', 'website'));
    $socialLocale = str_replace('-', '_', app()->getLocale());
@endphp

<meta property="og:type" content="{{ $socialType }}" />
<meta property="og:site_name" content="Gramlyze" />
<meta property="og:locale" content="{{ $socialLocale }}" />
<meta property="og:title" content="{{ $socialTitle }}" />
<meta property="og:description" content="{{ $socialDescription }}" />
<meta property="og:url" content="{{ $socialUrl }}" />
<meta property="og:image" content="{{ $socialImage }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{ $socialImageAlt }}" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $socialTitle }}" />
<meta name="twitter:description" content="{{ $socialDescription }}" />
<meta name="twitter:image" content="{{ $socialImage }}" />
<meta name="twitter:image:alt" content="{{ $socialImageAlt }}" />
