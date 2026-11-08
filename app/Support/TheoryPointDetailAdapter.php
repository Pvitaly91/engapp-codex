<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/** Finite render-only detail normalization after the existing fragment/identity guards. */
final class TheoryPointDetailAdapter
{
    public static function fragment(array $fragment, ?array $design = null): array
    {
        $type = $fragment['type'] ?? ''; $value = $fragment['value'] ?? '';
        $node = ['kind' => 'fragment', 'id' => $fragment['id'] ?? null, 'items' => []];
        if (in_array($type, ['intro', 'summary-list', 'warning', 'forms-grid', 'comparison-table', 'usage-panels', 'supplement'], true)) {
            // Native fragments own their paragraph typography. The structural
            // section must not introduce an inherited font size or line height.
            $node['tag'] = 'section'; $node['typography'] = 'inherit';
        }
        if ($type === 'm41-author-html' && isset($fragment['m41_existing_design_detail'])) {
            return TheoryAuthoredAdapter::detailFragment($fragment['m41_existing_design_detail'], $fragment['id']);
        }
        if (in_array($type, ['m27-author-html', 'm28-author-html', 'm29-author-html', 'm30-author-html', 'm31-author-html',
            'm32-author-html', 'm33-author-html', 'm34-author-html', 'm35-author-html', 'm36-author-html', 'm37-author-html',
            'm38-author-html', 'm39-author-html', 'm41-author-html', 'm43-author-html', 'm44-author-html'], true)) {
            $node['body_html'] = TheoryHtmlAdapter::nativeFragment((string) $value, $design, '/details/'.$fragment['id']);
        } elseif ($type === 'intro' || $type === 'summary-list') {
            $node['body_html'] = new HtmlString((string) $value); $node['body_role'] = 'paragraph';
            if ($type === 'intro') { $node['body_tone'] = 'muted'; }
        } elseif ($type === 'warning') {
            $node['items'][] = ['kind' => 'note', 'variant' => 'compact', 'html' => new HtmlString((string) $value)];
        } elseif ($type === 'forms-grid') {
            $node['label'] = $value['label'] ?? ''; $node['title'] = $value['title'] ?? '';
            $node['title_role'] = 'subheading'; $node['content_layout'] = 'compact-stack';
            $node['body_html'] = TheoryInlineHtml::render($value['subtitle'] ?? ''); $node['body_role'] = 'paragraph';
        } elseif ($type === 'comparison-table') {
            $node['items'][] = self::example($value);
            $node['items'][] = ['kind' => 'paragraph', 'html' => new HtmlString($value['note'] ?? '')];
        } elseif (in_array($type, ['usage-panels', 'supplement'], true)) {
            if (isset($value['label'])) { $node['title'] = $value['label']; $node['title_role'] = 'label'; }
            $description = $type === 'supplement' ? e($value['description'] ?? '') : ($value['description'] ?? '');
            $node['body_html'] = new HtmlString($description); $node['body_role'] = 'paragraph';
            foreach ($value['examples'] ?? [] as $example) { $node['items'][] = self::example($example); }
            if (!empty($value['note'])) { $node['items'][] = ['kind' => 'note', 'variant' => 'compact', 'html' => new HtmlString($value['note'])]; }
        } else {
            // Unknown data never becomes a payload-selected Blade view or vanishes.
            $node['body_html'] = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return $node;
    }

    private static function example(array $value): array
    {
        return ['kind' => 'example', 'variant' => 'plain', 'en' => TheoryInlineHtml::render($value['en'] ?? ''),
            'uk' => TheoryInlineHtml::render($value['ua'] ?? $value['uk'] ?? '')];
    }
}
