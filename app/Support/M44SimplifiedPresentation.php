<?php

namespace App\Support;

/** Finite editorial grouping of the frozen M44 text; no content or database mutation. */
final class M44SimplifiedPresentation
{
    public const PATH = 'docs/content/m44-simplified-presentation.v1.json';
    public const SHA = '547e9db5647c5708b5f699122a61fb53d10fa7332912829ce07b6703de7dc24e';

    public static function section(array $section): ?array
    {
        try {
            $bytes = file_get_contents(base_path(self::PATH));
            if (hash('sha256', $bytes) !== self::SHA) { return null; }
            $projection = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if ($projection['master_sha256'] !== M44AuthoredFutureFormsPackage::MASTER_SHA) { return null; }
            [$before] = M44AuthoredFutureFormsPackage::load();
            $master = M44AuthoredFutureFormsPackage::authorMaster($before);
            foreach ($master['lessons'] as $lesson) {
                foreach ($lesson['sections'] as $source) {
                    if ($source !== $section) { continue; }
                    $plan = $projection['sections'][$section['id']] ?? null;
                    if (!is_array($plan)) { return null; }
                    $originals = array_column($section['points'], null, 'id');
                    $seen = []; $groups = [];
                    foreach ($plan['groups'] as $group) {
                        if ($group['id'] !== ($group['points'][0] ?? null) || !$group['reason_uk']) { return null; }
                        $group['sources'] = []; $group['examples'] = []; $group['selected'] = [];
                        foreach ($group['points'] as $id) {
                            if (!isset($originals[$id]) || in_array($id, $seen, true)) { return null; }
                            $seen[] = $id; $group['sources'][] = $originals[$id];
                        }
                        foreach ($group['example_refs'] as $ref) {
                            $key = $ref['point'].':'.$ref['index'];
                            if (!in_array($ref['point'], $group['points'], true) || isset($group['selected'][$key])
                                || !isset($originals[$ref['point']]['examples'][$ref['index']])) { return null; }
                            $group['examples'][] = $originals[$ref['point']]['examples'][$ref['index']];
                            $group['selected'][$key] = true;
                        }
                        $formula = $group['formula_from'];
                        if ($formula !== null && (!in_array($formula, $group['points'], true) || !isset($originals[$formula]['formula']))) { return null; }
                        $group['formula'] = $formula === null ? null : $originals[$formula]['formula'];
                        if (!$group['disclosure'] && (count($group['points']) !== 1 || $group['basic_uk'] || $group['example_refs'] || $formula !== null)) { return null; }
                        if ($group['disclosure'] && !$group['basic_uk']) { return null; }
                        $group['source_index'] = array_search($group['id'], array_keys($originals), true);
                        $groups[] = $group;
                    }
                    // Exact coverage in author order: neither missing nor reassigned material.
                    if ($seen !== array_keys($originals)) { return null; }
                    return $groups;
                }
            }
        } catch (\Throwable) { /* The original full native rendering remains available. */ }
        return null;
    }
}
