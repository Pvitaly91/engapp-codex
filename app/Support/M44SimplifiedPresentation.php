<?php

namespace App\Support;

/** Finite editorial grouping of the frozen M44 text; no content or database mutation. */
final class M44SimplifiedPresentation
{
    public const PATH = 'docs/content/m44-simplified-presentation.v1.json';
    public const SHA = '547e9db5647c5708b5f699122a61fb53d10fa7332912829ce07b6703de7dc24e';
    public const FORMS_PATH = 'docs/content/m44-forms-presentation.v2.json';
    public const FORMS_SHA = '0f9019db84e8b8573ceb99f655e9dde083df6bbd6bf0b19a82516e4892e26aac';
    public const CONT_FORMS_PATH = 'docs/content/m44-cont-forms-presentation.v3.json';
    public const CONT_FORMS_SHA = '81ca7d4f60bd3be33fd3ec5a7708cb11397b5b8e2b2795747b8d456f6fadc785';

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
                    $formsProjection = match ($section['id']) {
                        'm44-will-forms' => [self::FORMS_PATH, self::FORMS_SHA],
                        'm44-cont-forms' => [self::CONT_FORMS_PATH, self::CONT_FORMS_SHA],
                        default => null,
                    };
                    if ($formsProjection !== null) {
                        $formsBytes = file_get_contents(base_path($formsProjection[0]));
                        if (hash('sha256', $formsBytes) !== $formsProjection[1]) { return null; }
                        $forms = json_decode($formsBytes, true, flags: JSON_THROW_ON_ERROR);
                        if ($forms['base_sha256'] !== self::SHA || $forms['master_sha256'] !== M44AuthoredFutureFormsPackage::MASTER_SHA
                            || $forms['section_id'] !== $section['id']) { return null; }
                        $plan = $forms;
                    }
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
                        $group['visible_formulas'] = $formula === null ? [] : [$formula];
                        if (isset($group['form_rows'])) {
                            $rowRefs = [];
                            foreach ($group['form_rows'] as &$row) {
                                $key = $row['point'].':'.$row['index'];
                                if (!isset($group['selected'][$key]) || !$row['label']
                                    || !isset($originals[$row['point']]['formula'])
                                    || in_array($row['point'], $group['visible_formulas'], true)) { return null; }
                                $rowRefs[] = $key;
                                $row['formula'] = $originals[$row['point']]['formula'];
                                $row['example'] = $originals[$row['point']]['examples'][$row['index']];
                                $group['visible_formulas'][] = $row['point'];
                            }
                            unset($row);
                            if ($rowRefs !== array_keys($group['selected'])) { return null; }
                        }
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
