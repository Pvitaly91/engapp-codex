<?php

namespace App\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/** Render-only primitives. Never used to reconstruct a frozen content package. */
final class TheoryComponents
{
    public const KINDS = ['section','usage','form','example','note','table','mistake','summary','disclosure','fragment','group','correction','paragraph'];

    public static function html(array $node): string
    {
        return view('theory.components.node', compact('node'))->render();
    }

    public static function body(mixed $value): HtmlString
    {
        return new HtmlString($value instanceof Htmlable ? $value->toHtml() : e((string) ($value ?? '')));
    }

    public static function present(mixed $value): bool
    {
        return (string) ($value instanceof Htmlable ? $value->toHtml() : ($value ?? '')) !== '';
    }

    /** Technical provenance/accessibility only; callers cannot inject visual classes or styles. */
    public static function attrs(array $attrs = []): HtmlString
    {
        $result = '';
        foreach ($attrs as $name => $value) {
            if (preg_match('/^(?:data-|aria-)[a-z0-9_.:-]+$/D', (string) $name) && (is_scalar($value) || $value === null)) {
                $result .= ' '.e($name).'="'.e(is_bool($value) ? ($value ? 'true' : 'false') : (string) $value).'"';
            }
        }
        return new HtmlString($result);
    }

    /** Technical table wrapping, not a package-specific visual variant. */
    public static function minWidth(mixed $value): HtmlString
    {
        $width = filter_var($value, FILTER_VALIDATE_INT);
        return new HtmlString($width !== false && $width > 0 && $width <= 4096 ? ' style="min-width: '.$width.'px"' : '');
    }

    public static function grid(?string $layout): string
    {
        return match ($layout) {
            'grid2' => 'grid gap-3 sm:grid-cols-2',
            'grid3' => 'grid gap-3 sm:grid-cols-3',
            'inline' => 'flex flex-wrap gap-3 text-sm',
            default => 'space-y-4',
        };
    }

    public static function accent(?string $accent): array
    {
        return match ($accent) {
            'emerald' => ['border'=>'border-emerald-200','bg'=>'bg-emerald-50','text'=>'text-emerald-700','badge'=>'bg-emerald-500'],
            'rose' => ['border'=>'border-rose-200','bg'=>'bg-rose-50','text'=>'text-rose-700','badge'=>'bg-rose-500'],
            'sky' => ['border'=>'border-sky-200','bg'=>'bg-sky-50','text'=>'text-sky-700','badge'=>'bg-sky-500'],
            'blue' => ['border'=>'border-blue-200','bg'=>'bg-blue-50','text'=>'text-blue-700','badge'=>'bg-blue-500'],
            'amber' => ['border'=>'border-amber-200','bg'=>'bg-amber-50','text'=>'text-amber-700','badge'=>'bg-amber-500'],
            'purple' => ['border'=>'border-purple-200','bg'=>'bg-purple-50','text'=>'text-purple-700','badge'=>'bg-purple-500'],
            default => ['border'=>'border-slate-200','bg'=>'bg-slate-50','text'=>'text-slate-700','badge'=>'bg-slate-500'],
        };
    }
}
