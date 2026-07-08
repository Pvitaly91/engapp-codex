<?php

namespace App\Support;

use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;

class HtmlResponseContent
{
    /**
     * Replace the rendered body without overwriting Laravel's original View
     * object, which remains available through getOriginalContent().
     */
    public static function replace(Response $response, string $content): void
    {
        $property = new ReflectionProperty(Response::class, 'content');
        $property->setValue($response, $content);
    }
}
